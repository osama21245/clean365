<?php

namespace Modules\Chatbot\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Chatbot\Entities\ChatbotConversation;
use Modules\Chatbot\Entities\ChatbotMessage;
use Modules\Chatbot\Services\Ai\ConversationLanguage;
use Modules\Chatbot\Services\Chat\Clean365ChatAgent;

class ChatbotController extends Controller
{
    /* ------------------------------------------------------------------
     | Guest endpoints
     |------------------------------------------------------------------*/

    public function ack(Request $request, Clean365ChatAgent $agent): JsonResponse
    {
        return $this->handleAck($request, $agent, isGuest: true);
    }

    public function message(Request $request, Clean365ChatAgent $agent): JsonResponse
    {
        return $this->handleMessage($request, $agent, isGuest: true);
    }

    public function createGuestConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guest_token' => 'required|string|max:100',
            'conversation_uuid' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $this->errorProcessor($validator)], 403);
        }

        try {
            $guestToken = trim((string) $request->input('guest_token', $request->header('X-Guest-Token', '')));
            $requestedUuid = trim((string) $request->input('conversation_uuid', ''));
            $uuid = $requestedUuid !== '' ? $requestedUuid : (string) Str::uuid();
            $zoneId = $this->resolveZoneId($request);

            $existing = ChatbotConversation::query()
                ->where('uuid', $uuid)
                ->where('guest_token', $guestToken)
                ->first();

            if ($existing) {
                return response()->json([
                    'conversation_uuid' => $existing->uuid,
                    'guest_token' => $existing->guest_token,
                    'zone_id' => $existing->zone_id,
                    'created' => false,
                ]);
            }

            $conversation = ChatbotConversation::create([
                'uuid' => $uuid,
                'guest_token' => $guestToken,
                'user_id' => null,
                'zone_id' => $zoneId,
                'status' => 'open',
                'last_message_at' => now(),
                'meta' => ['channel' => 'customer_chatbot_guest'],
            ]);

            return response()->json([
                'conversation_uuid' => $conversation->uuid,
                'guest_token' => $conversation->guest_token,
                'zone_id' => $conversation->zone_id,
                'created' => true,
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'errors' => [['code' => 'conversation_create_failed', 'message' => 'Failed to create conversation']],
            ], 500);
        }
    }

    public function messages(Request $request, string $uuid): JsonResponse
    {
        $guestToken = trim((string) $request->input('guest_token', $request->header('X-Guest-Token', '')));
        if ($guestToken === '') {
            return response()->json([
                'errors' => [['code' => 'guest_token', 'message' => 'guest_token is required']],
            ], 403);
        }

        $conversation = ChatbotConversation::query()
            ->where('uuid', $uuid)
            ->where('guest_token', $guestToken)
            ->whereNull('user_id')
            ->first();

        if (! $conversation) {
            return response()->json([
                'errors' => [['code' => 'conversation_not_found', 'message' => 'Conversation not found']],
            ], 404);
        }

        return $this->paginateMessages($request, $conversation);
    }

    public function guestConversations(Request $request): JsonResponse
    {
        $guestToken = trim((string) $request->input('guest_token', $request->header('X-Guest-Token', '')));
        if ($guestToken === '') {
            return response()->json([
                'errors' => [['code' => 'guest_token', 'message' => 'guest_token is required']],
            ], 403);
        }

        try {
            $perPage = min(max((int) $request->input('per_page', 30), 1), 50);

            $paginator = ChatbotConversation::query()
                ->where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->withCount('messages')
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->paginate($perPage);

            $paginator->setCollection(
                $paginator->getCollection()
                    ->map(function (ChatbotConversation $conversation): array {
                        $recent = $conversation->messages()
                            ->orderByDesc('id')
                            ->limit(8)
                            ->get(['id', 'sender_type', 'content', 'meta']);

                        $lastAi = $recent->first(function ($m) {
                            return $m->sender_type === 'ai'
                                && ! ((bool) data_get($m->meta, 'interim', false));
                        });
                        $lastUser = $recent->firstWhere('sender_type', 'user');
                        $preview = $lastAi?->content ?? $lastUser?->content;

                        return [
                            'uuid' => $conversation->uuid,
                            'zone_id' => $conversation->zone_id,
                            'status' => $conversation->status,
                            'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                            'messages_count' => (int) $conversation->messages_count,
                            'preview' => $preview ? Str::limit(strip_tags((string) $preview), 90) : null,
                            'created_at' => $conversation->created_at?->toIso8601String(),
                            'updated_at' => $conversation->updated_at?->toIso8601String(),
                        ];
                    })
                    ->values()
            );

            return response()->json([
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'errors' => [['code' => 'conversations_failed', 'message' => 'Failed to list conversations']],
            ], 500);
        }
    }

    /* ------------------------------------------------------------------
     | Authenticated user endpoints (Passport auth:api)
     |------------------------------------------------------------------*/

    public function userAck(Request $request, Clean365ChatAgent $agent): JsonResponse
    {
        if (! Auth::guard('api')->user()) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'Unauthorized']]], 401);
        }

        return $this->handleAck($request, $agent, isGuest: false);
    }

    public function userMessage(Request $request, Clean365ChatAgent $agent): JsonResponse
    {
        if (! Auth::guard('api')->user()) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'Unauthorized']]], 401);
        }

        return $this->handleMessage($request, $agent, isGuest: false);
    }

    public function userConversations(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (! $user) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'Unauthorized']]], 401);
        }

        try {
            $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

            $paginator = ChatbotConversation::query()
                ->where('user_id', $user->id)
                ->withCount('messages')
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->paginate($perPage);

            $paginator->setCollection(
                $paginator->getCollection()->map(function (ChatbotConversation $conversation): array {
                    $recent = $conversation->messages()->orderByDesc('id')->limit(5)->get();
                    $lastAi = $recent->first(function ($m) {
                        return $m->sender_type === 'ai'
                            && ! ((bool) data_get($m->meta, 'interim', false));
                    });
                    $lastUser = $recent->firstWhere('sender_type', 'user');

                    return [
                        'uuid' => $conversation->uuid,
                        'zone_id' => $conversation->zone_id,
                        'status' => $conversation->status,
                        'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                        'messages_count' => (int) $conversation->messages_count,
                        'preview' => $lastAi?->content ?? $lastUser?->content,
                        'meta' => $this->redactMeta($conversation->meta),
                        'created_at' => $conversation->created_at?->toIso8601String(),
                        'updated_at' => $conversation->updated_at?->toIso8601String(),
                    ];
                })
            );

            return response()->json($paginator);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'errors' => [['code' => 'conversations_fetch_failed', 'message' => 'Failed to fetch conversations']],
            ], 500);
        }
    }

    public function userConversationMessages(Request $request, string $uuid): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (! $user) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'Unauthorized']]], 401);
        }

        $conversation = ChatbotConversation::query()
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return response()->json([
                'errors' => [['code' => 'conversation_not_found', 'message' => 'Conversation not found']],
            ], 404);
        }

        return $this->paginateMessages($request, $conversation);
    }

    public function claimGuest(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (! $user) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'Unauthorized']]], 401);
        }

        $validator = Validator::make($request->all(), [
            'guest_token' => 'required|string|max:100',
            'conversation_uuid' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $this->errorProcessor($validator)], 403);
        }

        try {
            $guestToken = trim((string) $request->input('guest_token'));
            $conversationUuid = trim((string) $request->input('conversation_uuid', ''));

            $query = ChatbotConversation::query()
                ->where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->where('status', 'open');

            if ($conversationUuid !== '') {
                $query->where('uuid', $conversationUuid);
            }

            $claimed = 0;
            $uuids = [];

            $query->orderBy('id')->each(function (ChatbotConversation $conversation) use ($user, &$claimed, &$uuids) {
                $meta = is_array($conversation->meta) ? $conversation->meta : [];
                $meta['channel'] = 'customer_chatbot_user';
                $meta['claimed_from_guest'] = true;

                $conversation->user_id = $user->id;
                $conversation->meta = $meta;
                $conversation->save();

                $claimed++;
                $uuids[] = $conversation->uuid;
            });

            return response()->json([
                'claimed' => $claimed,
                'conversation_uuids' => $uuids,
                'user_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'errors' => [['code' => 'claim_failed', 'message' => 'Failed to claim guest conversation']],
            ], 500);
        }
    }

    /* ------------------------------------------------------------------
     | Shared handlers
     |------------------------------------------------------------------*/

    private function handleAck(Request $request, Clean365ChatAgent $agent, bool $isGuest): JsonResponse
    {
        $rules = [
            'message' => 'required|string|max:2000',
            'conversation_uuid' => 'required|string|max:100',
        ];
        if ($isGuest) {
            $rules['guest_token'] = 'required|string|max:100';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $this->errorProcessor($validator)], 403);
        }

        try {
            $conversation = $isGuest
                ? $this->resolveGuestConversation($request)
                : $this->resolveUserConversation($request);

            $message = trim((string) $request->input('message'));
            $lang = ConversationLanguage::detect([], $message);
            $ack = $agent->buildAckPayload($message, $lang);

            return response()->json([
                'conversation_uuid' => $conversation->uuid,
                'guest_token' => $conversation->guest_token,
                'user_id' => $conversation->user_id,
                'zone_id' => $conversation->zone_id,
                'message' => (string) ($ack['message'] ?? ''),
                'intent' => 'ack',
                'message_id' => null,
                'interim' => true,
                'loading_stages' => is_array($ack['loading_stages'] ?? null) ? $ack['loading_stages'] : [],
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::warning('clean365.chatbot.ack.failed', ['error' => $e->getMessage(), 'guest' => $isGuest]);

            return response()->json([
                'errors' => [['code' => 'chatbot_ack_failed', 'message' => 'Failed to create ack message']],
            ], 500);
        }
    }

    private function handleMessage(Request $request, Clean365ChatAgent $agent, bool $isGuest): JsonResponse
    {
        $rules = [
            'message' => 'required|string|max:2000',
            'conversation_uuid' => 'required|string|max:100',
            'property_id' => 'nullable|uuid',
            'booking_id' => 'nullable|uuid',
            'service_schedule' => 'nullable|string|max:40',
            'payment_method' => 'nullable|string|max:50',
            'customer_note' => 'nullable|string|max:500',
        ];
        if ($isGuest) {
            $rules['guest_token'] = 'required|string|max:100';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $this->errorProcessor($validator)], 403);
        }

        try {
            $conversation = $isGuest
                ? $this->resolveGuestConversation($request)
                : $this->resolveUserConversation($request);

            $authUser = Auth::guard('api')->user();
            $userMessage = trim((string) $request->input('message'));

            ChatbotMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'user',
                'content' => $userMessage,
                'content_type' => 'text',
                'meta' => $authUser && ! $isGuest ? ['user_id' => $authUser->id] : null,
            ]);

            $context = [
                'zone_id' => $conversation->zone_id ?: $this->resolveZoneId($request),
                'property_id' => $request->input('property_id'),
                'user_id' => $isGuest ? null : ($authUser?->id),
                'booking_id' => $request->input('booking_id'),
                'service_schedule' => $request->input('service_schedule'),
                'payment_method' => $request->input('payment_method'),
                'customer_note' => $request->input('customer_note'),
            ];

            $reply = $agent->reply($conversation, $userMessage, $context);

            $aiMessage = ChatbotMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'ai',
                'content' => (string) ($reply['message'] ?? ''),
                'content_type' => 'text',
                'intent' => (string) ($reply['intent'] ?? 'general_help'),
                'meta' => [
                    'normal_answer' => (bool) ($reply['normal_answer'] ?? true),
                    'item_ids' => $reply['item_ids'] ?? [],
                    'items' => $reply['items'] ?? [],
                    'booking' => $reply['booking'] ?? null,
                    'interim' => false,
                    'llm_ok' => array_key_exists('llm_ok', $reply) ? (bool) $reply['llm_ok'] : true,
                    'llm_provider' => $reply['llm_provider'] ?? null,
                ],
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'conversation_uuid' => $conversation->uuid,
                'guest_token' => $conversation->guest_token,
                'user_id' => $conversation->user_id,
                'zone_id' => $conversation->zone_id,
                'intent' => (string) ($reply['intent'] ?? 'general_help'),
                'normal_answer' => (bool) ($reply['normal_answer'] ?? true),
                'message' => (string) ($reply['message'] ?? ''),
                'message_id' => $aiMessage->id,
                'items' => $reply['items'] ?? [],
                'item_ids' => $reply['item_ids'] ?? [],
                'booking' => $reply['booking'] ?? null,
                'interim' => false,
                'llm_ok' => array_key_exists('llm_ok', $reply) ? (bool) $reply['llm_ok'] : true,
                'llm_provider' => $reply['llm_provider'] ?? null,
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::warning('clean365.chatbot.message.failed', ['error' => $e->getMessage(), 'guest' => $isGuest]);

            return response()->json([
                'errors' => [['code' => 'chatbot_message_failed', 'message' => 'Failed to process chat message']],
            ], 500);
        }
    }

    private function paginateMessages(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 50), 1), 100);
        $paginator = $conversation->messages()->orderBy('id')->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(function (ChatbotMessage $row): array {
                $meta = is_array($row->meta) ? $row->meta : [];

                return [
                    'id' => $row->id,
                    'sender_type' => $row->sender_type,
                    'content' => $row->content,
                    'content_type' => $row->content_type,
                    'intent' => $row->intent,
                    'interim' => (bool) data_get($meta, 'interim', false),
                    'items' => data_get($meta, 'items', []),
                    'item_ids' => data_get($meta, 'item_ids', []),
                    'booking' => data_get($meta, 'booking'),
                    'meta' => $this->redactMeta($meta),
                    'created_at' => $row->created_at?->toIso8601String(),
                ];
            })
        );

        return response()->json($paginator);
    }

    private function resolveGuestConversation(Request $request): ChatbotConversation
    {
        $guestToken = trim((string) $request->input('guest_token', $request->header('X-Guest-Token', '')));
        if ($guestToken === '') {
            $guestToken = (string) Str::uuid();
        }

        $conversationUuid = trim((string) $request->input('conversation_uuid', ''));
        $zoneId = $this->resolveZoneId($request);

        if ($conversationUuid !== '') {
            $existing = ChatbotConversation::query()
                ->where('uuid', $conversationUuid)
                ->where('guest_token', $guestToken)
                ->where('status', 'open')
                ->first();

            if ($existing) {
                if (! $existing->zone_id && $zoneId) {
                    $existing->zone_id = $zoneId;
                    $existing->save();
                }

                return $existing;
            }

            try {
                return ChatbotConversation::create([
                    'uuid' => $conversationUuid,
                    'guest_token' => $guestToken,
                    'user_id' => null,
                    'zone_id' => $zoneId,
                    'status' => 'open',
                    'last_message_at' => now(),
                    'meta' => ['channel' => 'customer_chatbot_guest'],
                ]);
            } catch (\Throwable $e) {
                $again = ChatbotConversation::query()
                    ->where('uuid', $conversationUuid)
                    ->where('guest_token', $guestToken)
                    ->first();
                if ($again) {
                    return $again;
                }
                throw $e;
            }
        }

        return ChatbotConversation::create([
            'uuid' => (string) Str::uuid(),
            'guest_token' => $guestToken,
            'user_id' => null,
            'zone_id' => $zoneId,
            'status' => 'open',
            'last_message_at' => now(),
            'meta' => ['channel' => 'customer_chatbot_guest'],
        ]);
    }

    private function resolveUserConversation(Request $request): ChatbotConversation
    {
        $user = Auth::guard('api')->user();
        $conversationUuid = trim((string) $request->input('conversation_uuid', ''));
        $zoneId = $this->resolveZoneId($request);

        if ($conversationUuid !== '') {
            $existing = ChatbotConversation::query()
                ->where('uuid', $conversationUuid)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if ($existing) {
                if (! $existing->zone_id && $zoneId) {
                    $existing->zone_id = $zoneId;
                    $existing->save();
                }

                return $existing;
            }

            return ChatbotConversation::create([
                'uuid' => $conversationUuid,
                'guest_token' => null,
                'user_id' => $user->id,
                'zone_id' => $zoneId,
                'status' => 'open',
                'last_message_at' => now(),
                'meta' => ['channel' => 'customer_chatbot_user'],
            ]);
        }

        $latest = ChatbotConversation::query()
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            return $latest;
        }

        return ChatbotConversation::create([
            'uuid' => (string) Str::uuid(),
            'guest_token' => null,
            'user_id' => $user->id,
            'zone_id' => $zoneId,
            'status' => 'open',
            'last_message_at' => now(),
            'meta' => ['channel' => 'customer_chatbot_user'],
        ]);
    }

    private function resolveZoneId(Request $request): ?string
    {
        $zoneId = $request->header('zoneid')
            ?: $request->input('zone_id')
            ?: Config::get('zone_id');

        $zoneId = is_string($zoneId) ? trim($zoneId) : null;

        return $zoneId !== '' ? $zoneId : null;
    }

    private function redactMeta(mixed $meta): ?array
    {
        if (! is_array($meta)) {
            return null;
        }

        unset($meta['internal'], $meta['debug']);

        return $meta;
    }

    private function errorProcessor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $code => $messages) {
            foreach ($messages as $message) {
                $errors[] = ['code' => $code, 'message' => $message];
            }
        }

        return $errors;
    }
}
