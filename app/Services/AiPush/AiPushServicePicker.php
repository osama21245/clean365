<?php

namespace App\Services\AiPush;

use Modules\ServiceManagement\Entities\Service;

class AiPushServicePicker
{
    /**
     * Pick the next active service (round-robin by id) for AI push targeting.
     */
    public function pickNext(AiPushSettings $settings): ?Service
    {
        $query = Service::query()
            ->active()
            ->whereNotNull('category_id')
            ->orderBy('id');

        $lastServiceId = $settings->ai_push_service_id;

        if ($lastServiceId) {
            $next = (clone $query)->where('id', '>', $lastServiceId)->first();
            if ($next) {
                return $next->load(['category', 'subCategory']);
            }
        }

        return $query->first()?->load(['category', 'subCategory']);
    }

    public function advanceCursor(AiPushSettings $settings, Service $service): void
    {
        $settings->update([
            'ai_push_service_id' => $service->id,
        ]);
    }
}
