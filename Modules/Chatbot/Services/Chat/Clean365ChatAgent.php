<?php

namespace Modules\Chatbot\Services\Chat;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\BookingModule\Entities\Booking;
use Modules\Chatbot\Entities\ChatbotConversation;
use Modules\Chatbot\Services\Ai\ChatLlmClient;
use Modules\Chatbot\Services\Ai\ConversationLanguage;
use Modules\Chatbot\Services\Pinecone\PineconeService;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ServiceManagement\Services\SubscriptionBookingService;
use Throwable;

/**
 * Clean365 customer chatbot agent.
 * v1: advise + package discovery (Pinecone) + my visits + rebook.
 * Does NOT create new subscriptions / confirm first purchase.
 */
class Clean365ChatAgent
{
    public function __construct(
        protected ChatLlmClient $llm,
        protected PineconeService $pinecone,
        protected SubscriptionBookingService $bookingService,
    ) {}

    /**
     * @param  array{
     *   zone_id?: ?string,
     *   property_id?: ?string,
     *   user_id?: ?string,
     *   booking_id?: ?string,
     *   service_schedule?: ?string,
     *   payment_method?: ?string,
     *   customer_note?: ?string
     * }  $context
     * @return array{normal_answer:bool, message:string, intent:string, items:array, item_ids:array, llm_ok?:bool, llm_provider?:?string, booking?:?array}
     */
    public function reply(ChatbotConversation $chat, string $userMessage, array $context = []): array
    {
        $history = $chat->messages()
            ->orderBy('id')
            ->get(['sender_type', 'content'])
            ->map(fn ($m) => [
                'role' => $m->sender_type === 'user' ? 'user' : 'assistant',
                'content' => (string) $m->content,
            ])
            ->all();

        $lang = ConversationLanguage::detect($history, $userMessage);
        $zoneId = $context['zone_id'] ?? Config::get('zone_id') ?? $chat->zone_id;
        $userId = $context['user_id'] ?? $chat->user_id;

        if ($this->isGreetingOrLowSignal($userMessage)) {
            return $this->directGreeting($lang);
        }

        if ($faq = $this->matchFaq($userMessage, $lang)) {
            return $faq;
        }

        $route = $this->routeTools($history, $userMessage, $lang, (bool) $userId);

        return match ($route['intent']) {
            'my_subscriptions' => $this->handleMySubscriptions($userId, $lang, $context['property_id'] ?? null),
            'rebook_visit' => $this->handleRebook($userId, $userMessage, $lang, $context, $history),
            'booking_status' => $this->handleBookingStatus($userId, $lang),
            'suggest_packages' => $this->handleSuggestPackages($history, $userMessage, $lang, $zoneId, $context['property_id'] ?? null, $userId),
            'out_of_scope_purchase' => $this->handlePurchaseHandoff($history, $userMessage, $lang, $zoneId, $context['property_id'] ?? null, $userId),
            default => $this->handleGeneralHelp($history, $userMessage, $lang),
        };
    }

    public function buildAckPayload(string $userMessage, string $lang = 'ar'): array
    {
        $isEn = ConversationLanguage::isEnglish($lang);
        $lower = mb_strtolower($userMessage);

        if (preg_match('/باق|زيار|remaining|visit|subscribe|اشتراك/u', $lower)) {
            return [
                'message' => $isEn ? 'Checking your packages and visits…' : 'لحظة… أراجع باقاتك ورصيد الزيارات',
                'loading_stages' => $isEn
                    ? ['Looking up subscriptions', 'Preparing reply']
                    : ['أبحث في اشتراكاتك', 'أحضّر الرد'],
            ];
        }

        if (preg_match('/احجز|rebook|book next|الزيارة/u', $lower)) {
            return [
                'message' => $isEn ? 'Preparing your next visit…' : 'لحظة… أحضّر حجز الزيارة الجاية',
                'loading_stages' => $isEn
                    ? ['Checking remaining visits', 'Scheduling']
                    : ['أتحقق من الرصيد', 'أجهّز الموعد'],
            ];
        }

        if (preg_match('/تنظيف|شقة|فيلا|باقة|package|clean|villa|apartment/u', $lower)) {
            return [
                'message' => $isEn ? 'Searching matching packages…' : 'لحظة… أبحث عن الباقات المناسبة',
                'loading_stages' => $isEn
                    ? ['Searching catalog', 'Ranking packages']
                    : ['أبحث في الكتالوج', 'أرتّب الباقات'],
            ];
        }

        return [
            'message' => $isEn ? 'Thinking…' : 'لحظة…',
            'loading_stages' => $isEn
                ? ['Understanding your request', 'Preparing reply']
                : ['أفهم طلبك', 'أحضّر الرد'],
        ];
    }

    /**
     * @return array{intent:string, reason:string}
     */
    private function routeTools(array $history, string $userMessage, string $lang, bool $isAuth): array
    {
        if ($this->looksLikePurchase($userMessage)) {
            return ['intent' => 'out_of_scope_purchase', 'reason' => 'purchase'];
        }

        if ($this->looksLikeRebook($userMessage)) {
            return ['intent' => 'rebook_visit', 'reason' => 'rebook'];
        }

        if ($this->looksLikeMySubs($userMessage)) {
            return ['intent' => 'my_subscriptions', 'reason' => 'my_subs'];
        }

        if ($this->looksLikeBookingStatus($userMessage)) {
            return ['intent' => 'booking_status', 'reason' => 'bookings'];
        }

        if ($this->looksLikeHardCatalogSeek($userMessage)) {
            return ['intent' => 'suggest_packages', 'reason' => 'catalog_heuristic'];
        }

        $prompt = Clean365BrandKnowledge::identityPrompt()."\n"
            .Clean365BrandKnowledge::companyFactsPrompt()."\n"
            .ConversationLanguage::llmReplyRule($lang)."\n"
            ."Classify the user message into ONE intent JSON:\n"
            ."{\"intent\":\"suggest_packages|my_subscriptions|rebook_visit|booking_status|faq_general|out_of_scope_purchase|general_help\",\"reason\":\"short\"}\n"
            ."Rules:\n"
            ."- suggest_packages: wants cleaning package recommendations\n"
            ."- my_subscriptions: asks remaining visits / my packages\n"
            ."- rebook_visit: wants to book next visit on existing package\n"
            ."- out_of_scope_purchase: wants to buy/pay/subscribe new package in chat\n"
            ."- Guest auth=".( $isAuth ? 'yes' : 'no' )."\n"
            ."User: {$userMessage}\n";

        $result = $this->llm->complete($prompt, true);
        $intent = (string) ($result['json']['intent'] ?? 'general_help');
        $allowed = ['suggest_packages', 'my_subscriptions', 'rebook_visit', 'booking_status', 'faq_general', 'out_of_scope_purchase', 'general_help'];
        if (! in_array($intent, $allowed, true)) {
            $intent = 'general_help';
        }

        return ['intent' => $intent, 'reason' => (string) ($result['json']['reason'] ?? 'llm')];
    }

    private function handleSuggestPackages(array $history, string $userMessage, string $lang, ?string $zoneId, ?string $propertyId, ?string $userId): array
    {
        $plan = $this->buildSearchPlan($userMessage, $lang, $propertyId);
        $candidates = $this->searchPackages($plan, $propertyId);
        $cards = $this->hydratePackageCards($candidates, $zoneId, $userId, $lang);

        if ($cards === []) {
            $fallback = ConversationLanguage::isEnglish($lang)
                ? 'I could not find matching packages right now. Try specifying property type (apartment/villa/studio) and visit count.'
                : 'ما لقيت باقات مطابقة حالياً. جرّب تحدد نوع العقار (شقة/فيلا/استوديو) وعدد الزيارات.';

            return $this->withLlmMeta([
                'normal_answer' => true,
                'message' => $fallback,
                'intent' => 'suggest_packages',
                'items' => [],
                'item_ids' => [],
            ], ['ok' => true, 'provider' => null]);
        }

        $selection = $this->composePackageReply($history, $userMessage, $lang, $cards);

        return $this->withLlmMeta([
            'normal_answer' => false,
            'message' => $selection['message'],
            'intent' => 'suggest_packages',
            'items' => $selection['items'],
            'item_ids' => array_column($selection['items'], 'id'),
        ], $selection['llm']);
    }

    private function handlePurchaseHandoff(array $history, string $userMessage, string $lang, ?string $zoneId, ?string $propertyId, ?string $userId): array
    {
        $base = $this->handleSuggestPackages($history, $userMessage, $lang, $zoneId, $propertyId, $userId);
        $hint = Clean365BrandKnowledge::purchaseDeepLinkHint($lang);
        $base['message'] = trim(($base['message'] ?? '')."\n\n".$hint);
        $base['intent'] = 'out_of_scope_purchase';

        return $base;
    }

    private function handleMySubscriptions(?string $userId, string $lang, ?string $propertyId): array
    {
        if (! $userId) {
            return $this->loginRequired($lang, 'my_subscriptions');
        }

        $subs = CustomerServiceSubscription::query()
            ->with(['service:id,name,thumbnail,visits_count,slug,property_id', 'property:id,name'])
            ->where('user_id', $userId)
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->whereIn('status', ['active', 'finished'])
            ->latest()
            ->limit(10)
            ->get();

        if ($subs->isEmpty()) {
            $msg = ConversationLanguage::isEnglish($lang)
                ? 'You have no subscriptions yet. Tell me what cleaning you need and I will recommend a package.'
                : 'ما عندك اشتراكات حالياً. قلّي وش تبي تنظيف وأرشّح لك باقة.';

            return [
                'normal_answer' => true,
                'message' => $msg,
                'intent' => 'my_subscriptions',
                'items' => [],
                'item_ids' => [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        $items = [];
        $lines = [];
        foreach ($subs as $sub) {
            $card = $this->subscriptionCard($sub, $lang);
            $items[] = $card;
            $lines[] = ConversationLanguage::isEnglish($lang)
                ? "• {$card['service_name']}: {$card['remaining_visits']}/{$card['total_visits']} left ({$card['status']})"
                : "• {$card['service_name']}: باقي {$card['remaining_visits']} من {$card['total_visits']} ({$card['status']})";
        }

        $activeSubs = $subs->where('status', 'active');
        if ($activeSubs->count() === 1) {
            $sub = $activeSubs->first();
            $header = ConversationLanguage::isEnglish($lang)
                ? "You have {$sub->remaining_visits} of {$sub->total_visits} visits left on {$sub->service?->name}."
                : "عندك {$sub->remaining_visits} زيارة متبقية من {$sub->total_visits} في باقة {$sub->service?->name}.";
        } else {
            $header = ConversationLanguage::isEnglish($lang)
                ? 'Here are your packages:'
                : 'هذه باقاتك:';
        }

        return [
            'normal_answer' => false,
            'message' => $activeSubs->count() === 1 ? $header : $header."\n".implode("\n", $lines),
            'intent' => 'my_subscriptions',
            'items' => $items,
            'item_ids' => array_column($items, 'id'),
            'llm_ok' => true,
            'llm_provider' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function handleRebook(?string $userId, string $userMessage, string $lang, array $context, array $history): array
    {
        if (! $userId) {
            return $this->loginRequired($lang, 'rebook_visit');
        }

        $active = CustomerServiceSubscription::query()
            ->with(['service:id,name,slug'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('remaining_visits', '>=', 1)
            ->latest()
            ->get();

        if ($active->isEmpty()) {
            $msg = ConversationLanguage::isEnglish($lang)
                ? 'You have no remaining visits. I can recommend a package to renew — purchase is completed in the app.'
                : 'ما عندك زيارات متبقية. أقدر أرشّح لك باقة للتجديد — الشراء يكتمل من التطبيق.';

            $suggest = $this->handleSuggestPackages($history, $userMessage, $lang, $context['zone_id'] ?? Config::get('zone_id'), $context['property_id'] ?? null, $userId);

            return [
                'normal_answer' => false,
                'message' => $msg."\n\n".($suggest['message'] ?? ''),
                'intent' => 'rebook_visit',
                'items' => $suggest['items'] ?? [],
                'item_ids' => $suggest['item_ids'] ?? [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        $schedule = trim((string) ($context['service_schedule'] ?? ''));
        if ($schedule === '') {
            $schedule = $this->extractSchedule($userMessage, $lang, $history);
        }

        if ($schedule === '') {
            $msg = ConversationLanguage::isEnglish($lang)
                ? 'You have remaining visits. Send the date/time for the next visit (e.g. 2026-09-01 10:00) or pass service_schedule in the request.'
                : 'عندك زيارات متبقية. أرسل تاريخ ووقت الزيارة الجاية (مثال: 2026-09-01 10:00) أو أرسل service_schedule مع الطلب.';

            return [
                'normal_answer' => false,
                'message' => $msg,
                'intent' => 'rebook_visit',
                'items' => $active->map(fn ($s) => $this->subscriptionCard($s))->values()->all(),
                'item_ids' => $active->pluck('id')->all(),
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        $bookingId = trim((string) ($context['booking_id'] ?? ''));
        if ($bookingId === '') {
            $bookingId = (string) (Booking::query()
                ->where('customer_id', $userId)
                ->whereNotNull('subscription_id')
                ->whereIn('subscription_id', $active->pluck('id'))
                ->latest()
                ->value('id') ?? '');
        }

        if ($bookingId === '') {
            $msg = ConversationLanguage::isEnglish($lang)
                ? 'I found an active package but no previous booking to rebook from. Open the app to place the first visit, or send booking_id.'
                : 'لقيت باقة نشطة لكن ما في حجز سابق أعيد منه. افتح التطبيق لأول زيارة أو أرسل booking_id.';

            return [
                'normal_answer' => true,
                'message' => $msg,
                'intent' => 'rebook_visit',
                'items' => $active->map(fn ($s) => $this->subscriptionCard($s))->values()->all(),
                'item_ids' => $active->pluck('id')->all(),
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        $payload = [
            'booking_id' => $bookingId,
            'service_schedule' => $schedule,
            'zone_id' => $context['zone_id'] ?? Config::get('zone_id'),
            'payment_method' => $context['payment_method'] ?? 'cash_after_service',
            'customer_note' => $context['customer_note'] ?? null,
        ];
        if (! empty($context['property_id'])) {
            $payload['property_id'] = $context['property_id'];
        }

        try {
            $result = $this->bookingService->rebook($userId, $payload);
        } catch (Throwable $e) {
            Log::warning('chatbot.rebook.exception', ['error' => $e->getMessage()]);
            $result = ['flag' => 'failed', 'message' => 'rebook failed'];
        }

        if (($result['flag'] ?? '') !== 'success') {
            $err = (string) ($result['message'] ?? 'rebook failed');
            $msg = ConversationLanguage::isEnglish($lang)
                ? "Could not rebook: {$err}. If visits ran out, renew the package in the app."
                : "ما قدرت أحجز الزيارة: {$err}. إذا خلص الرصيد جدّد الباقة من التطبيق.";

            return [
                'normal_answer' => true,
                'message' => $msg,
                'intent' => 'rebook_visit',
                'items' => [],
                'item_ids' => [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        /** @var Booking $booking */
        $booking = $result['booking'];
        $sub = $result['subscription'] ?? null;
        $remaining = $sub?->remaining_visits;

        $msg = ConversationLanguage::isEnglish($lang)
            ? 'Your next visit is booked for '.$booking->service_schedule.'.'
            : 'تم حجز زيارتك الجاية لـ '.$booking->service_schedule.'.';

        if ($remaining !== null) {
            $msg .= ConversationLanguage::isEnglish($lang)
                ? " Remaining visits: {$remaining}."
                : " المتبقي من الزيارات: {$remaining}.";
        }

        $items = [];
        if ($sub) {
            $items[] = $this->subscriptionCard($sub);
        }

        return [
            'normal_answer' => false,
            'message' => $msg,
            'intent' => 'rebook_visit',
            'items' => $items,
            'item_ids' => array_column($items, 'id'),
            'booking' => [
                'id' => $booking->id,
                'service_schedule' => (string) $booking->service_schedule,
                'booking_status' => $booking->booking_status,
                'subscription_id' => $booking->subscription_id,
            ],
            'llm_ok' => true,
            'llm_provider' => null,
        ];
    }

    private function handleBookingStatus(?string $userId, string $lang): array
    {
        if (! $userId) {
            return $this->loginRequired($lang, 'booking_status');
        }

        $bookings = Booking::query()
            ->where('customer_id', $userId)
            ->latest()
            ->limit(5)
            ->get(['id', 'booking_status', 'service_schedule', 'subscription_id']);

        if ($bookings->isEmpty()) {
            $msg = ConversationLanguage::isEnglish($lang)
                ? 'You have no bookings yet.'
                : 'ما عندك حجوزات حالياً.';

            return [
                'normal_answer' => true,
                'message' => $msg,
                'intent' => 'booking_status',
                'items' => [],
                'item_ids' => [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        $lines = [];
        $items = [];
        foreach ($bookings as $b) {
            $lines[] = ConversationLanguage::isEnglish($lang)
                ? "• {$b->id}: {$b->booking_status} @ {$b->service_schedule}"
                : "• {$b->id}: {$b->booking_status} — {$b->service_schedule}";
            $items[] = [
                'entity_type' => 'booking',
                'id' => $b->id,
                'booking_status' => $b->booking_status,
                'service_schedule' => (string) $b->service_schedule,
                'subscription_id' => $b->subscription_id,
            ];
        }

        $header = ConversationLanguage::isEnglish($lang) ? 'Recent bookings:' : 'آخر الحجوزات:';

        return [
            'normal_answer' => false,
            'message' => $header."\n".implode("\n", $lines),
            'intent' => 'booking_status',
            'items' => $items,
            'item_ids' => array_column($items, 'id'),
            'llm_ok' => true,
            'llm_provider' => null,
        ];
    }

    private function handleGeneralHelp(array $history, string $userMessage, string $lang): array
    {
        $historyText = collect($history)->slice(-8)->map(
            fn ($m) => strtoupper((string) ($m['role'] ?? '')).': '.($m['content'] ?? '')
        )->implode("\n");

        $prompt = Clean365BrandKnowledge::identityPrompt()."\n"
            .Clean365BrandKnowledge::companyFactsPrompt()."\n"
            .ConversationLanguage::llmReplyRule($lang)."\n"
            ."Reply helpfully as JSON: {\"message\":\"...\"}\n"
            ."History:\n{$historyText}\n"
            ."User: {$userMessage}\n";

        $result = $this->llm->complete($prompt, true);
        $message = trim((string) ($result['text'] ?? ''));
        if ($message === '') {
            $message = Clean365BrandKnowledge::aboutMessage($lang);
        }

        return $this->withLlmMeta([
            'normal_answer' => true,
            'message' => $message,
            'intent' => 'general_help',
            'items' => [],
            'item_ids' => [],
        ], $result);
    }

    /**
     * @return array{search_query:string, limit:int, visits_hint:?int}
     */
    private function buildSearchPlan(string $userMessage, string $lang, ?string $propertyId): array
    {
        $visitsHint = null;
        if (preg_match('/\b([1-9]\d?)\s*(زيار|visit)/ui', $userMessage, $m)) {
            $visitsHint = (int) $m[1];
        }

        $query = $userMessage;
        if ($propertyId) {
            $query .= " property_id:{$propertyId}";
        }

        return [
            'search_query' => $query,
            'limit' => 8,
            'visits_hint' => $visitsHint,
        ];
    }

    /**
     * @param  array{search_query:string, limit:int, visits_hint:?int}  $plan
     * @return list<array{id:string, score?:float}>
     */
    private function searchPackages(array $plan, ?string $propertyId): array
    {
        $fromPinecone = $this->searchPackagesFromPinecone($plan, $propertyId);
        if ($fromPinecone !== []) {
            return $fromPinecone;
        }

        return $this->searchPackagesFromDatabase($plan, $propertyId);
    }

    /**
     * @param  array{search_query:string, limit:int, visits_hint:?int}  $plan
     * @return list<array{id:string, score?:float}>
     */
    private function searchPackagesFromPinecone(array $plan, ?string $propertyId): array
    {
        if (! $this->pinecone->isConfigured()) {
            return [];
        }

        try {
            $topK = max(20, (int) $plan['limit'] * 3);
            $extra = ['fields' => ['*']];
            $response = $this->pinecone->searchRecords($plan['search_query'], $topK, $extra);
            $hits = data_get($response, 'result.hits', data_get($response, 'matches', []));
            if (! is_array($hits)) {
                return [];
            }

            $parsed = [];
            foreach ($hits as $hit) {
                $fields = data_get($hit, 'fields', data_get($hit, 'metadata', []));
                $id = (string) (data_get($fields, 'service_id')
                    ?? data_get($fields, 'entity_id')
                    ?? data_get($hit, '_id')
                    ?? '');
                $id = preg_replace('/^package_/', '', $id) ?? $id;
                if ($id === '') {
                    continue;
                }
                if ($propertyId && (string) data_get($fields, 'property_id', '') !== '' && (string) data_get($fields, 'property_id') !== $propertyId) {
                    continue;
                }
                $parsed[] = [
                    'id' => $id,
                    'score' => (float) (data_get($hit, '_score') ?? data_get($hit, 'score') ?? 0),
                    'visits_count' => (int) data_get($fields, 'visits_count', 0),
                ];
            }

            if ($plan['visits_hint']) {
                usort($parsed, function ($a, $b) use ($plan) {
                    $da = abs(($a['visits_count'] ?: 999) - $plan['visits_hint']);
                    $db = abs(($b['visits_count'] ?: 999) - $plan['visits_hint']);

                    return $da <=> $db;
                });
            }

            return array_slice(array_map(fn ($r) => ['id' => $r['id'], 'score' => $r['score']], $parsed), 0, $plan['limit']);
        } catch (Throwable $e) {
            Log::warning('chatbot.pinecone.search_failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  array{search_query:string, limit:int, visits_hint:?int}  $plan
     * @return list<array{id:string, score?:float}>
     */
    private function searchPackagesFromDatabase(array $plan, ?string $propertyId): array
    {
        $term = trim(preg_replace('/property_id:\S+/', '', $plan['search_query']) ?? $plan['search_query']);
        $like = '%'.$term.'%';

        $query = Service::query()
            ->where('is_active', 1)
            ->where('visits_count', '>=', 1)
            ->when($propertyId, fn ($q) => $q->where(function ($qq) use ($propertyId) {
                $qq->where('property_id', $propertyId)->orWhereNull('property_id');
            }))
            ->when($plan['visits_hint'], fn ($q) => $q->orderByRaw('ABS(visits_count - ?)', [$plan['visits_hint']]))
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->limit($plan['limit']);

        // If LIKE is too narrow, broaden
        $ids = $query->pluck('id')->all();
        if ($ids === []) {
            $ids = Service::query()
                ->where('is_active', 1)
                ->where('visits_count', '>=', 1)
                ->when($propertyId, fn ($q) => $q->where(function ($qq) use ($propertyId) {
                    $qq->where('property_id', $propertyId)->orWhereNull('property_id');
                }))
                ->when($plan['visits_hint'], fn ($q) => $q->orderByRaw('ABS(visits_count - ?)', [$plan['visits_hint']]))
                ->latest()
                ->limit($plan['limit'])
                ->pluck('id')
                ->all();
        }

        return array_map(fn ($id) => ['id' => (string) $id], $ids);
    }

    /**
     * @param  list<array{id:string, score?:float}>  $candidates
     * @return list<array<string, mixed>>
     */
    private function hydratePackageCards(array $candidates, ?string $zoneId, ?string $userId, string $lang = 'ar'): array
    {
        $ids = array_values(array_unique(array_column($candidates, 'id')));
        if ($ids === []) {
            return [];
        }

        $services = Service::query()
            ->with(['category:id,name', 'subCategory:id,name'])
            ->whereIn('id', $ids)
            ->where('is_active', 1)
            ->get()
            ->keyBy('id');

        $subsByService = [];
        if ($userId) {
            $subsByService = CustomerServiceSubscription::query()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->whereIn('service_id', $ids)
                ->get()
                ->keyBy('service_id');
        }

        $cards = [];
        foreach ($ids as $id) {
            $service = $services->get($id);
            if (! $service) {
                continue;
            }
            $cards[] = $this->packageCard($service, $zoneId, $subsByService[$id] ?? null, $lang);
        }

        return $cards;
    }

    private function packageCard(Service $service, ?string $zoneId, ?CustomerServiceSubscription $sub, string $lang = 'ar'): array
    {
        $price = null;
        if ($zoneId) {
            $variation = Variation::withoutGlobalScopes()
                ->where('service_id', $service->id)
                ->where('zone_id', $zoneId)
                ->orderBy('price')
                ->first();
            $price = $variation?->price !== null ? (float) $variation->price : null;
        }

        $scheme = (string) config('chatbot.deep_link_scheme', 'clean365');
        $frontend = rtrim((string) config('chatbot.frontend_url', config('app.url')), '/');
        $slug = (string) ($service->slug ?? $service->id);
        $visitsCount = (int) ($service->visits_count ?? 0);

        $includes = is_array($service->service_includes)
            ? array_values(array_filter($service->service_includes, fn ($v) => is_string($v) && trim($v) !== ''))
            : [];
        if ($includes === []) {
            $includes = $this->defaultPackageFeatures($lang);
        }

        return [
            'entity_type' => 'package',
            'id' => (string) $service->id,
            'name' => (string) $service->name,
            'title' => (string) $service->name,
            'slug' => $slug,
            'visits_count' => $visitsCount,
            'visits_text' => $this->visitsLabel($visitsCount, $lang),
            'property_id' => $service->property_id ? (string) $service->property_id : null,
            'category' => $service->category?->name,
            'sub_category' => $service->subCategory?->name,
            'price' => $price,
            'price_label' => $price !== null ? number_format($price, 0, '.', ',') : null,
            'zone_id' => $zoneId,
            'features' => array_slice($includes, 0, 5),
            'popular' => (bool) ($service->is_featured ?? false) || $visitsCount === 4,
            'is_subscribed' => (bool) $sub,
            'remaining_visits' => $sub?->remaining_visits,
            'deep_link' => "{$scheme}://service/{$slug}".($service->property_id ? '?property_id='.$service->property_id : ''),
            'url' => "{$frontend}/services/{$slug}",
        ];
    }

    private function subscriptionCard(CustomerServiceSubscription $sub, string $lang = 'ar'): array
    {
        $sub->loadMissing(['service:id,name,slug,thumbnail,visits_count', 'property:id,name']);

        $lastBookingId = Booking::query()
            ->where('subscription_id', $sub->id)
            ->latest()
            ->value('id');

        $visitsCount = (int) ($sub->service?->visits_count ?? $sub->total_visits);

        return [
            'entity_type' => 'subscription',
            'id' => (string) $sub->id,
            'service_id' => (string) $sub->service_id,
            'service_name' => (string) ($sub->service?->name ?? ''),
            'title' => (string) ($sub->service?->name ?? ''),
            'slug' => (string) ($sub->service?->slug ?? ''),
            'visits_count' => $visitsCount,
            'visits_text' => $this->visitsLabel($visitsCount, $lang),
            'property_id' => (string) $sub->property_id,
            'property_label' => (string) ($sub->property?->name ?? ''),
            'total_visits' => (int) $sub->total_visits,
            'remaining_visits' => (int) $sub->remaining_visits,
            'used_visits' => (int) $sub->used_visits,
            'status' => (string) $sub->status,
            'last_booking_id' => $lastBookingId ? (string) $lastBookingId : null,
            'actions' => $sub->status === 'active' && $sub->remaining_visits >= 1
                ? ['rebook', 'view_package']
                : ['view_package'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return array{message:string, items:list<array<string, mixed>>, llm:array}
     */
    private function composePackageReply(array $history, string $userMessage, string $lang, array $cards): array
    {
        $catalog = collect($cards)->take(6)->map(fn ($c) => [
            'id' => $c['id'],
            'name' => $c['name'],
            'visits_count' => $c['visits_count'],
            'price' => $c['price'],
            'category' => $c['category'],
            'sub_category' => $c['sub_category'],
            'remaining_visits' => $c['remaining_visits'],
        ])->values()->all();

        $prompt = Clean365BrandKnowledge::identityPrompt()."\n"
            .ConversationLanguage::llmReplyRule($lang)."\n"
            ."Recommend 1-5 packages from CATALOG. Reply JSON: {\"message\":\"...\",\"selected_ids\":[\"...\"]}\n"
            ."Only use ids from CATALOG. Mention visits_count and price when present.\n"
            ."Never say you cannot access the user's account or remaining visits. Recommend packages only.\n"
            .'CATALOG: '.json_encode($catalog, JSON_UNESCAPED_UNICODE)."\n"
            ."User: {$userMessage}\n";

        $result = $this->llm->complete($prompt, true);
        $selected = $result['json']['selected_ids'] ?? [];
        if (! is_array($selected) || $selected === []) {
            $selected = array_column(array_slice($cards, 0, 3), 'id');
        }

        $selected = array_map('strval', $selected);
        $byId = collect($cards)->keyBy('id');
        $items = [];
        foreach ($selected as $id) {
            if ($byId->has($id)) {
                $items[] = $byId->get($id);
            }
        }
        if ($items === []) {
            $items = array_slice($cards, 0, 3);
        }

        $message = trim((string) ($result['text'] ?? ''));
        if ($message === '' || isset($result['json']['selected_ids'])) {
            $message = (string) ($result['json']['message'] ?? $message);
        }
        if ($message === '') {
            $message = ConversationLanguage::isEnglish($lang)
                ? 'Here are packages that match your request:'
                : 'هذه باقات مناسبة لطلبك:';
        }

        return ['message' => $message, 'items' => $items, 'llm' => $result];
    }

    private function extractSchedule(string $userMessage, string $lang, array $history): string
    {
        if (preg_match('/\d{4}-\d{2}-\d{2}[ T]\d{1,2}:\d{2}/', $userMessage, $m)) {
            return str_replace('T', ' ', $m[0]).(substr_count($m[0], ':') === 1 ? ':00' : '');
        }

        $prompt = ConversationLanguage::llmReplyRule($lang)."\n"
            ."Extract a booking datetime from the user message if present.\n"
            ."Return JSON: {\"service_schedule\":\"YYYY-MM-DD HH:MM:SS\"} or {\"service_schedule\":null} if unclear.\n"
            .'Assume Asia/Riyadh. Today is '.now()->toDateString().".\n"
            ."User: {$userMessage}\n";

        $result = $this->llm->complete($prompt, true);
        $schedule = $result['json']['service_schedule'] ?? null;

        return is_string($schedule) && trim($schedule) !== '' ? trim($schedule) : '';
    }

    private function matchFaq(string $userMessage, string $lang): ?array
    {
        $t = mb_strtolower(trim($userMessage));

        if (preg_match('/^(من انت|who are you|what are you)/u', $t)) {
            return [
                'normal_answer' => true,
                'message' => Clean365BrandKnowledge::whoAmIMessage($lang),
                'intent' => 'faq_general',
                'items' => [],
                'item_ids' => [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        if (preg_match('/(وش كلين|what is clean365|عن كلين|about clean)/u', $t)) {
            return [
                'normal_answer' => true,
                'message' => Clean365BrandKnowledge::aboutMessage($lang),
                'intent' => 'faq_general',
                'items' => [],
                'item_ids' => [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        if (preg_match('/(كيف الباق|how (do )?packages|visits work|رصيد الزيار)/u', $t)) {
            $msg = ConversationLanguage::isEnglish($lang)
                ? 'Each package includes a fixed number of visits. After you subscribe, remaining_visits decreases by one each time you book a visit. When it reaches zero the package is finished and you can renew.'
                : 'كل باقة فيها عدد زيارات ثابت. بعد الاشتراك، ينقص المتبقي زيارة مع كل حجز. لما يوصل صفر تخلص الباقة وتقدر تجدّدها.';

            return [
                'normal_answer' => true,
                'message' => $msg,
                'intent' => 'faq_general',
                'items' => [],
                'item_ids' => [],
                'llm_ok' => true,
                'llm_provider' => null,
            ];
        }

        return null;
    }

    private function directGreeting(string $lang): array
    {
        $msg = ConversationLanguage::isEnglish($lang)
            ? 'Hello! I can recommend cleaning packages, show your remaining visits, and rebook your next visit.'
            : 'هلا! أقدر أرشّح لك باقات التنظيف، أوريك رصيد الزيارات، وأحجز لك الزيارة الجاية.';

        return [
            'normal_answer' => true,
            'message' => $msg,
            'intent' => 'greeting',
            'items' => [],
            'item_ids' => [],
            'llm_ok' => true,
            'llm_provider' => null,
        ];
    }

    private function loginRequired(string $lang, string $intent): array
    {
        $isEn = ConversationLanguage::isEnglish($lang);
        $msg = match ($intent) {
            'my_subscriptions' => $isEn
                ? 'Log in to see how many visits you have left on your package.'
                : 'سجّل دخولك عشان أقدر أقول لك كم زيارة متبقية في باقتك.',
            'rebook_visit' => $isEn
                ? 'Log in to book your next visit from your package balance.'
                : 'سجّل دخولك عشان أحجز لك الزيارة الجاية من رصيد باقتك.',
            'booking_status' => $isEn
                ? 'Log in to view your bookings.'
                : 'سجّل دخولك عشان أشوف حجوزاتك.',
            default => $isEn
                ? 'Please log in so I can access your packages and bookings.'
                : 'سجّل دخول عشان أقدر أشوف باقاتك وحجوزاتك.',
        };

        $frontend = rtrim((string) config('chatbot.frontend_url', config('app.url')), '/');

        return [
            'normal_answer' => true,
            'message' => $msg,
            'intent' => $intent,
            'items' => [[
                'entity_type' => 'action',
                'id' => 'login',
                'action' => 'login',
                'label' => $isEn ? 'Log in' : 'تسجيل الدخول',
                'url' => "{$frontend}/login",
            ]],
            'item_ids' => ['login'],
            'llm_ok' => true,
            'llm_provider' => null,
        ];
    }

    private function isGreetingOrLowSignal(string $message): bool
    {
        $t = trim(mb_strtolower($message));

        return (bool) preg_match('/^(هلا|مرحبا|السلام|hi|hello|hey|صباح|مساء)[\s!.]*$/u', $t);
    }

    private function looksLikeHardCatalogSeek(string $message): bool
    {
        return (bool) preg_match('/تنظيف|باقة|شقة|فيلا|استوديو|زيار|clean|package|apartment|villa|studio|visit/ui', $message);
    }

    private function looksLikeMySubs(string $message): bool
    {
        return (bool) preg_match(
            '/باقي|متبقي|رصيد|زيارات?\s*(متبق|باق)|في\s*باقت|اشتراكاتي|باقاتي|remaining|left|my (package|subscription|visit)/ui',
            $message
        );
    }

    /**
     * @return list<string>
     */
    private function defaultPackageFeatures(string $lang): array
    {
        if (ConversationLanguage::isEnglish($lang)) {
            return [
                'Full unit cleaning',
                'Bed making and surface disinfection',
                'Complete bathroom sanitization',
                'Luxury fragrance and hotel-grade supplies',
            ];
        }

        return [
            'تنظيف شامل للوحدة',
            'ترتيب السرير وتعقيم الأسطح',
            'تعقيم وتطهير الحمام بالكامل',
            'معطر فاخر ومواد فندقية',
        ];
    }

    private function visitsLabel(int $count, string $lang): string
    {
        if (ConversationLanguage::isEnglish($lang)) {
            return match (true) {
                $count === 1 => '1 visit',
                $count === 2 => '2 visits',
                default => "{$count} visits / month",
            };
        }

        return match (true) {
            $count === 1 => 'زيارة واحدة',
            $count === 2 => 'زيارتان',
            default => "{$count} زيارات في الشهر",
        };
    }

    private function looksLikeRebook(string $message): bool
    {
        return (bool) preg_match('/احجز|إعادة حجز|الزيارة الجاية|rebook|book (the )?next|schedule (a )?visit/ui', $message);
    }

    private function looksLikeBookingStatus(string $message): bool
    {
        return (bool) preg_match('/حجوزاتي|طلباتي|وين حجزي|my bookings|booking status/ui', $message);
    }

    private function looksLikePurchase(string $message): bool
    {
        return (bool) preg_match('/اشتري|ادفع|اشترك جديد|renew|buy package|subscribe now|purchase/ui', $message);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $llm
     * @return array<string, mixed>
     */
    private function withLlmMeta(array $payload, array $llm): array
    {
        $payload['llm_ok'] = (bool) ($llm['ok'] ?? true);
        $payload['llm_provider'] = $llm['provider'] ?? null;

        return $payload;
    }
}
