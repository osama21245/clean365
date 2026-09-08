<?php

namespace App\Services\AdBroadcast;

use App\Models\AdBroadcast;
use App\Support\NotificationLocale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Persist dashboard / AI push copies into user_inbox_notifications (in-app inbox).
 */
class AdBroadcastInAppNotifier
{
    public function shouldPersist(): bool
    {
        return (bool) config('ad_broadcast.persist_in_app_notifications', true);
    }

    /**
     * @param  list<array{user_id: string|int, token?: string, ui_locale?: string|null}>  $recipients
     */
    public function recordForRecipients(AdBroadcast $broadcast, array $recipients, string $audienceKey): int
    {
        if (! $this->shouldPersist() || $recipients === []) {
            return 0;
        }

        $titleMap = NotificationLocale::decodeMap($broadcast->getRawOriginal('title'));
        $descriptionMap = NotificationLocale::decodeMap($broadcast->getRawOriginal('description') ?? '');

        if ($titleMap === []) {
            return 0;
        }

        $payload = app(AdBroadcastFcmPayload::class)->forBroadcast($broadcast, $audienceKey);
        $dataJson = json_encode($payload, JSON_UNESCAPED_UNICODE) ?: null;
        $titleJson = json_encode($titleMap, JSON_UNESCAPED_UNICODE);
        $descriptionJson = $descriptionMap !== []
            ? json_encode($descriptionMap, JSON_UNESCAPED_UNICODE)
            : null;

        $now = now();
        $rows = [];

        foreach ($recipients as $recipient) {
            $userId = (string) ($recipient['user_id'] ?? '');
            if ($userId === '') {
                continue;
            }

            $rows[] = [
                'user_id' => $userId,
                'type' => AdBroadcastFcmPayload::NOTIFICATION_TYPE,
                'title' => $titleJson,
                'description' => $descriptionJson,
                'url' => null,
                'view' => null,
                'data' => $dataJson,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return 0;
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('user_inbox_notifications')->insert($chunk);
        }

        Log::info('ad_broadcast.in_app_notifications.saved', [
            'ad_broadcast_id' => $broadcast->id,
            'audience' => $audienceKey,
            'count' => count($rows),
        ]);

        return count($rows);
    }
}
