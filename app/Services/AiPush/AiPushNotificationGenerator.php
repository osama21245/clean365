<?php

namespace App\Services\AiPush;

use App\Support\NotificationLocale;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\BlogModule\Services\Gemini\GeminiVertexClient;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ServiceManagement\Entities\Service;

class AiPushNotificationGenerator
{
    private const CUSTOMER_ANGLES = [
        'maintenance_tip',
        'seasonal_home_care',
        'fast_booking',
        'trust_and_safety',
        'preventive_maintenance',
        'new_service_highlight',
        'home_comfort',
        'loyalty_appreciation',
    ];

    private const PROVIDER_ANGLES = [
        'new_orders_nearby',
        'stay_online',
        'complete_profile',
        'rating_importance',
        'earnings_tip',
        'fast_response',
        'professional_image',
        'weekend_availability',
    ];

    private const SERVICEMAN_ANGLES = [
        'new_jobs_assigned',
        'arrive_on_time',
        'before_after_photos',
        'customer_rating',
        'complete_tasks',
        'stay_available',
    ];

    private const GUEST_ANGLES = [
        'discover_services',
        'easy_registration',
        'first_booking',
        'trust_platform',
        'home_maintenance_intro',
    ];

    /**
     * @return array{title: array<string, string>, description: array<string, string>, topic_key: string}
     */
    public function generateForAudience(string $audience, AiPushSettings $settings, Service $service): array
    {
        return $this->generateForAudiences([$audience], $settings, $service)[$audience];
    }

    /**
     * @param  list<string>  $audiences
     * @return array<string, array{title: array<string, string>, description: array<string, string>, topic_key: string}>
     */
    public function generateForAudiences(array $audiences, AiPushSettings $settings, Service $service): array
    {
        $audiences = array_values(array_unique(array_filter($audiences)));
        if ($audiences === []) {
            return [];
        }

        $angles = [];
        $avoidByAudience = [];
        foreach ($audiences as $audience) {
            $recentTopics = $this->recentTopicsForAudience($settings, $audience);
            $angles[$audience] = $this->pickTopicAngle($audience, $recentTopics);
            $avoidByAudience[$audience] = array_slice($recentTopics, -20);
        }

        $generationLocales = NotificationLocale::primaryPushLocales();
        $context = $this->buildMultiAudienceContext(
            $settings,
            $audiences,
            $angles,
            $avoidByAudience,
            $service,
            $generationLocales,
        );
        $prompt = $this->buildMultiAudiencePrompt($context, $audiences, $generationLocales);
        $payload = $this->requestAiJson($prompt);
        $out = $this->normalizeMultiAudiencePayload($payload, $audiences, $angles);

        $secondaryLocales = NotificationLocale::secondaryPushLocales();
        if ($secondaryLocales !== [] && config('ad_broadcast.push_translate_secondary', true)) {
            $out = $this->translateSecondaryLocales($out, $secondaryLocales);
        }

        return $out;
    }

    /**
     * @param  list<string>  $recentTopics
     */
    private function pickTopicAngle(string $audience, array $recentTopics): string
    {
        $pool = match ($audience) {
            'providers' => self::PROVIDER_ANGLES,
            'servicemen' => self::SERVICEMAN_ANGLES,
            'guests' => self::GUEST_ANGLES,
            default => self::CUSTOMER_ANGLES,
        };

        $candidates = array_values(array_diff($pool, $recentTopics));

        if ($candidates === []) {
            $candidates = $pool;
        }

        shuffle($candidates);

        return $candidates[0];
    }

    private function audienceRoleLabel(string $audience): string
    {
        return match ($audience) {
            'providers' => 'مزود خدمة / مشرف في التطبيق (يستقبل طلبات عمل ويكسب منها)',
            'servicemen' => 'فني ميداني ينفّذ الحجوزات ويحدّث حالة المهمة',
            'guests' => 'زائر لم يسجّل بعد (يشجَّع على استكشاف التطبيق أو التسجيل)',
            default => 'عميل يبحث عن خدمات تنظيف وصيانة منزلية ويحجز مزوّدين',
        };
    }

    /**
     * @param  list<string>  $locales
     * @return array<string, string>
     */
    private function translationsByLocale(object $model, string $field, array $locales, ?int $limit = null): array
    {
        $out = [];
        $rows = Translation::query()
            ->where('translationable_type', $model::class)
            ->where('translationable_id', $model->getKey())
            ->where('key', $field)
            ->whereIn('locale', $locales)
            ->get()
            ->keyBy('locale');

        foreach ($locales as $locale) {
            $value = trim(strip_tags((string) ($rows->get($locale)?->value ?? '')));
            if ($limit !== null) {
                $value = Str::limit($value, $limit, '');
            }
            if ($value !== '') {
                $out[$locale] = $value;
            }
        }

        if ($out === []) {
            $plain = trim(strip_tags((string) ($model->{$field} ?? '')));
            if ($limit !== null) {
                $plain = Str::limit($plain, $limit, '');
            }
            if ($plain !== '') {
                $out['en'] = $plain;
            }
        }

        return $out;
    }

    /**
     * Gemini first. OpenAI only if ad_broadcast.ai_push_openai_fallback is enabled.
     *
     * @return array<string, mixed>
     */
    private function requestAiJson(string $prompt, bool $translation = false): array
    {
        $vertex = app(GeminiVertexClient::class);
        $openAiKey = (string) config('services.image_generation.openai.api_key');
        $allowOpenAi = (bool) config('ad_broadcast.ai_push_openai_fallback', false);
        $geminiError = null;

        if ($vertex->resolveTextApiKey() !== '' || $vertex->resolveAiStudioApiKey() !== '') {
            try {
                return $this->requestGeminiJson($prompt, $translation);
            } catch (\Throwable $e) {
                $geminiError = $e;
                Log::warning('AI push: Gemini failed'.($allowOpenAi ? ', trying OpenAI.' : '.'), [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($allowOpenAi && $openAiKey !== '') {
            try {
                return $this->requestOpenAiJson($prompt, $openAiKey, $translation);
            } catch (\Throwable $e) {
                $parts = array_filter([
                    $geminiError ? 'Gemini: '.$geminiError->getMessage() : null,
                    'OpenAI: '.$e->getMessage(),
                ]);

                throw new \RuntimeException(implode(' | ', $parts), 0, $e);
            }
        }

        if ($geminiError) {
            throw $geminiError;
        }

        throw new \RuntimeException(
            $allowOpenAi
                ? 'Neither GEMINI API key nor OPENAI_API_KEY is configured.'
                : 'Gemini API key is not configured (OpenAI fallback is disabled).'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requestGeminiJson(string $prompt, bool $translation = false): array
    {
        $vertex = app(GeminiVertexClient::class);
        if ($translation) {
            $models = array_map(
                fn (string $m) => $vertex->normalizeModelId($m),
                (array) config('services.gemini.push_translate_models', ['gemini-3.6-flash', 'gemini-flash-latest'])
            );
        } else {
            $model = $vertex->normalizeModelId((string) config('services.gemini.text_model', 'gemini-3.6-flash'));
            $fallbacks = array_map(
                fn (string $m) => $vertex->normalizeModelId($m),
                (array) config('services.gemini.text_models', [])
            );
            $models = array_values(array_unique(array_filter([$model, ...$fallbacks])));
        }
        $models = array_values(array_unique(array_filter($models)));

        $merged = $vertex->generateText(
            $prompt,
            $models,
            $translation ? $this->pushTranslationGenerationConfig($vertex) : $this->pushGenerationConfig($vertex),
            $translation ? 'Gemini failed for push translation' : 'Gemini failed for push notification'
        );

        $text = $vertex->extractTextFromChunks([$merged]);
        if ($text === '') {
            $text = trim((string) data_get($merged, 'candidates.0.content.parts.0.text', ''));
        }

        $decoded = $this->decodeJsonPayload($text);
        if (is_array($decoded)) {
            return $decoded;
        }

        $repairPrompt = "The following text must be fixed into ONE valid JSON object only (no markdown, no commentary):\n\n".$text;
        $repairMerged = $vertex->generateText(
            $repairPrompt,
            $models,
            $translation ? $this->pushTranslationGenerationConfig($vertex) : $this->pushGenerationConfig($vertex),
            'Gemini JSON repair failed for push'
        );
        $repairText = $vertex->extractTextFromChunks([$repairMerged]);
        $decoded = $this->decodeJsonPayload($repairText);
        if (is_array($decoded)) {
            return $decoded;
        }

        Log::warning('AI push: Gemini response is not valid JSON.', [
            'snippet' => Str::limit($text, 800),
            'repair_snippet' => Str::limit($repairText, 400),
            'models' => $models,
        ]);

        throw new \RuntimeException('Gemini push response is not valid JSON.');
    }

    /**
     * @return array<string, mixed>
     */
    private function pushGenerationConfig(GeminiVertexClient $vertex): array
    {
        $config = [
            'maxOutputTokens' => max(8192, (int) config('services.gemini.push_max_output_tokens', 8192)),
            'temperature' => (float) config('services.gemini.push_temperature', 0.35),
            'topP' => (float) config('services.gemini.text_top_p', 1),
        ];

        if (! $vertex->textUsesAiPlatform()) {
            $config['responseMimeType'] = 'application/json';
        } elseif (config('services.gemini.use_response_mime_type', false)) {
            $config['responseMimeType'] = 'application/json';
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    private function pushTranslationGenerationConfig(GeminiVertexClient $vertex): array
    {
        $config = [
            'maxOutputTokens' => max(2048, (int) config('services.gemini.push_translate_max_output_tokens', 4096)),
            'temperature' => (float) config('services.gemini.push_translate_temperature', 0.2),
            'topP' => (float) config('services.gemini.text_top_p', 1),
        ];

        if (! $vertex->textUsesAiPlatform()) {
            $config['responseMimeType'] = 'application/json';
        } elseif (config('services.gemini.use_response_mime_type', false)) {
            $config['responseMimeType'] = 'application/json';
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestOpenAiJson(string $prompt, string $apiKey, bool $translation = false): array
    {
        $lastException = null;

        foreach (['gpt-4o-mini', 'gpt-4o'] as $model) {
            try {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'response_format' => ['type' => 'json_object'],
                        'temperature' => $translation ? 0.2 : 1.0,
                        'max_tokens' => $translation ? 2000 : 500,
                    ]);

                if (! $response->successful()) {
                    $lastException = $response->toException();
                    if (in_array($response->status(), [400, 404], true)) {
                        continue;
                    }

                    throw $lastException;
                }

                $decoded = $this->decodeJsonPayload(trim((string) data_get($response->json(), 'choices.0.message.content', '')));
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (RequestException $e) {
                $lastException = $e;
                if (in_array($e->response?->status(), [400, 404], true)) {
                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException(
            'OpenAI failed for push notification.',
            previous: $lastException
        );
    }

    /**
     * @return list<string>
     */
    public function recentTopicsForAudience(AiPushSettings $settings, string $audience): array
    {
        $stored = $settings->ai_push_recent_topics;

        if (! is_array($stored)) {
            return [];
        }

        if (isset($stored[$audience]) && is_array($stored[$audience])) {
            return array_values(array_filter($stored[$audience]));
        }

        if (array_is_list($stored)) {
            return array_values(array_filter($stored));
        }

        return [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function appendRecentTopic(array $stored, string $audience, string $topicKey): array
    {
        $byAudience = $this->normalizeRecentTopicsStorage($stored);

        $list = $byAudience[$audience] ?? [];
        $list[] = $topicKey;
        $byAudience[$audience] = array_slice(array_values(array_unique($list)), -30);

        return $byAudience;
    }

    /**
     * @return array<string, list<string>>
     */
    private function normalizeRecentTopicsStorage(mixed $stored): array
    {
        $base = [
            'customers' => [],
            'providers' => [],
            'servicemen' => [],
            'guests' => [],
        ];

        if (! is_array($stored)) {
            return $base;
        }

        if (array_is_list($stored)) {
            foreach (array_keys($base) as $key) {
                $base[$key] = array_values(array_filter($stored));
            }

            return $base;
        }

        foreach (array_keys($base) as $key) {
            if (isset($stored[$key]) && is_array($stored[$key])) {
                $base[$key] = array_values(array_filter($stored[$key]));
            }
        }

        return $base;
    }

    /**
     * @param  list<string>  $audiences
     * @param  array<string, string>  $angles
     * @param  array<string, list<string>>  $avoidByAudience
     * @return array<string, mixed>
     */
    private function buildMultiAudienceContext(
        AiPushSettings $settings,
        array $audiences,
        array $angles,
        array $avoidByAudience,
        Service $service,
        ?array $locales = null,
    ): array {
        $service->loadMissing(['category', 'subCategory']);
        $pushLocales = $locales ?? NotificationLocale::primaryPushLocales();

        $audienceBlocks = [];
        foreach ($audiences as $audience) {
            $audienceBlocks[$audience] = [
                'role' => $this->audienceRoleLabel($audience),
                'topic_angle' => $angles[$audience] ?? 'maintenance_tip',
                'avoid_topic_keys' => $avoidByAudience[$audience] ?? [],
            ];
        }

        $subcategory = $service->subCategory;
        $parentCategory = $service->category;

        return [
            'brand' => config('app.name', 'Clean365'),
            'push_locales' => $pushLocales,
            'audiences' => $audienceBlocks,
            'today' => now()->translatedFormat('l، d F Y'),
            'admin_prompt' => trim((string) ($settings->ai_push_prompt ?? '')),
            'target_service' => [
                'id' => $service->id,
                'names' => $this->translationsByLocale($service, 'name', $pushLocales),
                'descriptions' => $this->translationsByLocale($service, 'description', $pushLocales, 300),
                'subcategory_names' => $subcategory ? $this->translationsByLocale($subcategory, 'name', $pushLocales) : [],
                'category_names' => $parentCategory ? $this->translationsByLocale($parentCategory, 'name', $pushLocales) : [],
            ],
            'random_seed' => random_int(1000, 999999),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  list<string>  $audiences
     */
    private function buildMultiAudiencePrompt(array $context, array $audiences, ?array $locales = null): string
    {
        $locales = $locales ?? ($context['push_locales'] ?? NotificationLocale::primaryPushLocales());
        if (! is_array($locales)) {
            $locales = NotificationLocale::primaryPushLocales();
        }

        $leafKeys = ['topic_key'];
        foreach ($locales as $locale) {
            $leafKeys[] = 'title_'.$locale;
            $leafKeys[] = 'description_'.$locale;
        }

        $shape = [];
        foreach ($audiences as $audience) {
            $shape[$audience] = array_fill_keys($leafKeys, 'string');
        }

        $roleRules = [];
        foreach ($audiences as $audience) {
            $roleRules[] = '- '.$audience.': '.$this->audiencePromptRules($audience);
        }

        $brand = config('app.name', 'Clean365');

        return "You write mobile push notifications for the {$brand} home services app in Saudi Arabia.\n"
            ."Return ONLY valid JSON (no markdown, no prose outside JSON).\n"
            .'Top-level keys MUST be exactly: '.implode(', ', $audiences).".\n"
            .'Each top-level value is an OBJECT (not a dotted key) with these string fields: '.implode(', ', $leafKeys).".\n"
            ."JSON shape example (replace strings with real copy):\n"
            .json_encode($shape, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
            ."Rules:\n"
            .implode("\n", $this->localeRulesForPrompt($locales))."\n"
            ."- topic_key per audience: short English snake_case slug (unique vs avoid_topic_keys for that audience).\n"
            ."- Follow admin_prompt when provided (adapt per audience role).\n"
            ."- Use topic_angle per audience; combine with random_seed for variety.\n"
            ."- MUST focus on target_service. Tapping opens that service (service_id in payload).\n"
            ."- No HTML. Plain text only.\n"
            ."Audience roles:\n"
            .implode("\n", $roleRules)."\n"
            ."Context JSON:\n"
            .json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  list<string>  $audiences
     * @param  array<string, string>  $angles
     * @return array<string, array{title: array<string, string>, description: array<string, string>, topic_key: string}>
     */
    private function normalizeMultiAudiencePayload(array $payload, array $audiences, array $angles): array
    {
        $out = [];
        foreach ($audiences as $audience) {
            $block = $payload[$audience] ?? [];
            if (! is_array($block)) {
                throw new \RuntimeException("AI response missing audience block: {$audience}");
            }
            $out[$audience] = $this->normalizePayload($block, $angles[$audience] ?? $audience);
        }

        return $out;
    }

    /**
     * @return array{title: array<string, string>, description: array<string, string>, topic_key: string}
     */
    private function normalizePayload(array $payload, string $fallbackTopicKey): array
    {
        $locales = NotificationLocale::pushLocales();
        $titles = [];
        $descriptions = [];

        foreach ($locales as $locale) {
            $t = trim((string) ($payload['title_'.$locale] ?? data_get($payload, 'title.'.$locale, '')));
            $d = trim((string) ($payload['description_'.$locale] ?? data_get($payload, 'description.'.$locale, '')));

            if ($t !== '') {
                $titles[$locale] = Str::limit($t, 255, '');
            }
            if ($d !== '') {
                $descriptions[$locale] = Str::limit($d, 255, '');
            }
        }

        if ($titles === [] && isset($payload['title']) && is_string($payload['title'])) {
            $titles['ar'] = Str::limit(trim($payload['title']), 255, '');
        }
        if ($descriptions === [] && isset($payload['description']) && is_string($payload['description'])) {
            $descriptions['ar'] = Str::limit(trim($payload['description']), 255, '');
        }

        $fallbackTitle = $titles['ar'] ?? $titles['en'] ?? (string) reset($titles);
        $fallbackDescription = $descriptions['ar'] ?? $descriptions['en'] ?? (string) reset($descriptions);

        foreach ($locales as $locale) {
            if (! isset($titles[$locale]) || $titles[$locale] === '') {
                $titles[$locale] = $fallbackTitle;
            }
            if (! isset($descriptions[$locale]) || $descriptions[$locale] === '') {
                $descriptions[$locale] = $fallbackDescription;
            }
        }

        if ($fallbackTitle === '' || $fallbackDescription === '') {
            throw new \RuntimeException('AI returned empty push notification text.');
        }

        $topicKey = Str::slug(trim((string) ($payload['topic_key'] ?? $fallbackTopicKey)), '_');

        return [
            'title' => NotificationLocale::decodeMap($titles),
            'description' => NotificationLocale::decodeMap($descriptions),
            'topic_key' => $topicKey !== '' ? $topicKey : $fallbackTopicKey,
        ];
    }

    private function audiencePromptRules(string $audience): string
    {
        return match ($audience) {
            'providers' => 'provider-admins only — orders, responsiveness, profile, earnings; CTA: افتح التطبيق، فعّل التواجد',
            'servicemen' => 'field technicians only — assigned jobs, punctuality, photos, ratings; no provider-admin dashboard tools',
            'guests' => 'unregistered visitors only — discover services or sign up; no provider earnings/tools',
            default => 'customers/homeowners only — book cleaning/maintenance; CTA: احجز الآن',
        };
    }

    /**
     * @param  list<string>  $locales
     * @return list<string>
     */
    private function localeRulesForPrompt(array $locales): array
    {
        $rules = [];
        foreach ($locales as $locale) {
            $rules[] = "- title_{$locale} and description_{$locale}: {$this->localeLabel($locale)}; title max 60 chars, description max 220 chars; same meaning across locales, natural native wording (not literal translation).";
        }

        return $rules;
    }

    private function localeLabel(string $locale): string
    {
        return match ($locale) {
            'en' => 'English',
            'ar' => 'Arabic (Saudi friendly)',
            default => strtoupper($locale),
        };
    }

    /**
     * @param  array<string, array{title: array<string, string>, description: array<string, string>, topic_key: string}>  $contentByAudience
     * @param  list<string>  $targetLocales
     * @return array<string, array{title: array<string, string>, description: array<string, string>, topic_key: string}>
     */
    private function translateSecondaryLocales(array $contentByAudience, array $targetLocales): array
    {
        $source = [];
        foreach ($contentByAudience as $audience => $content) {
            $source[$audience] = [
                'title' => $content['title'],
                'description' => $content['description'],
            ];
        }

        $leafKeys = [];
        foreach ($targetLocales as $locale) {
            $leafKeys[] = 'title_'.$locale;
            $leafKeys[] = 'description_'.$locale;
        }

        $shape = [];
        foreach (array_keys($contentByAudience) as $audience) {
            $shape[$audience] = array_fill_keys($leafKeys, 'string');
        }

        $localeLabels = [];
        foreach ($targetLocales as $locale) {
            $localeLabels[$locale] = $this->localeLabel($locale);
        }

        $prompt = "Translate mobile push notification copy into additional locales.\n"
            ."Return ONLY valid JSON (no markdown).\n"
            .'Top-level keys MUST be exactly: '.implode(', ', array_keys($contentByAudience)).".\n"
            .'Each value is an object with: '.implode(', ', $leafKeys).".\n"
            .'JSON shape: '.json_encode($shape, JSON_UNESCAPED_UNICODE)."\n"
            .'Target locales: '.json_encode($localeLabels, JSON_UNESCAPED_UNICODE)."\n"
            ."Rules: native wording (not literal); title ≤60 chars; description ≤220 chars; keep meaning of source.\n"
            ."Source JSON:\n"
            .json_encode($source, JSON_UNESCAPED_UNICODE);

        try {
            $payload = $this->requestAiJson($prompt, translation: true);
        } catch (\Throwable $e) {
            Log::warning('AI push: secondary locale translation failed, using primary fallback.', [
                'message' => $e->getMessage(),
            ]);

            return $this->fillMissingLocaleMaps($contentByAudience, $targetLocales);
        }

        foreach ($contentByAudience as $audience => $content) {
            $block = $payload[$audience] ?? [];
            if (! is_array($block)) {
                continue;
            }

            foreach ($targetLocales as $locale) {
                $title = trim((string) ($block['title_'.$locale] ?? ''));
                $description = trim((string) ($block['description_'.$locale] ?? ''));

                if ($title !== '') {
                    $contentByAudience[$audience]['title'][$locale] = Str::limit($title, 255, '');
                }
                if ($description !== '') {
                    $contentByAudience[$audience]['description'][$locale] = Str::limit($description, 255, '');
                }
            }
        }

        return $this->fillMissingLocaleMaps($contentByAudience, $targetLocales);
    }

    /**
     * @param  array<string, array{title: array<string, string>, description: array<string, string>, topic_key: string}>  $contentByAudience
     * @param  list<string>  $requiredLocales
     * @return array<string, array{title: array<string, string>, description: array<string, string>, topic_key: string}>
     */
    private function fillMissingLocaleMaps(array $contentByAudience, array $requiredLocales): array
    {
        foreach ($contentByAudience as $audience => $content) {
            $fallbackTitle = $content['title']['ar'] ?? $content['title']['en'] ?? (string) reset($content['title']);
            $fallbackDescription = $content['description']['ar'] ?? $content['description']['en'] ?? (string) reset($content['description']);

            foreach ($requiredLocales as $locale) {
                if (! isset($content['title'][$locale]) || $content['title'][$locale] === '') {
                    $contentByAudience[$audience]['title'][$locale] = $fallbackTitle;
                }
                if (! isset($content['description'][$locale]) || $content['description'][$locale] === '') {
                    $contentByAudience[$audience]['description'][$locale] = $fallbackDescription;
                }
            }
        }

        return $contentByAudience;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonPayload(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```\s*$/', '', $raw) ?? $raw;
        $raw = trim($raw);

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
