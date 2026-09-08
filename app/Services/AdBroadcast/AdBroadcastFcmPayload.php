<?php

namespace App\Services\AdBroadcast;

use App\Models\AdBroadcast;

class AdBroadcastFcmPayload
{
    public const NOTIFICATION_TYPE = 'dashboard_ad';

    public const SOURCE = 'dashboard_ad';

    /**
     * @return array<string, string>
     */
    public function forBroadcast(AdBroadcast $broadcast, string $audienceKey): array
    {
        return [
            'notification_type' => self::NOTIFICATION_TYPE,
            'content_target' => $broadcast->effectiveContentTarget(),
            'category_id' => $this->stringId($broadcast->category_id),
            'subcategory_id' => $this->stringId($broadcast->subcategory_id),
            'service_id' => $this->stringId($broadcast->service_id),
            'send_id' => (string) $broadcast->send_id,
            'sent_at' => (string) now()->getTimestamp(),
            'source' => self::SOURCE,
            'audience' => $audienceKey,
        ];
    }

    protected function stringId(mixed $id): string
    {
        if ($id === null || $id === '') {
            return '';
        }

        return (string) $id;
    }
}
