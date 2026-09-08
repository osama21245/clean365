<?php

namespace App\Services\AdBroadcast;

use App\Dto\PublishAdBroadcastData;
use App\Jobs\AdBroadcast\DispatchAdBroadcastFcmJob;
use App\Models\AdBroadcast;
use App\Support\NotificationLocale;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;

class AdBroadcastPublisher
{
    public function __construct(
        protected AdBroadcastRecipientEstimator $estimator,
        protected AdBroadcastTokenQuery $tokenQuery,
        protected AdBroadcastContentTargetValidator $contentTargetValidator,
    ) {}

    /**
     * @throws ValidationException
     */
    public function publish(PublishAdBroadcastData $data): AdBroadcast
    {
        if ($data->explicitUserIds !== null && $data->explicitUserIds !== []) {
            return $this->publishExplicitAudience($data);
        }

        $audiences = array_values(array_unique($data->audiences));
        sort($audiences);

        $allowed = (array) config('ad_broadcast.audiences', ['customers', 'providers', 'servicemen', 'guests']);
        $audiences = array_values(array_intersect($audiences, $allowed));
        if ($audiences === []) {
            throw ValidationException::withMessages([
                'audiences' => translate('Select at least one audience'),
            ]);
        }

        $zoneIds = $data->zoneIds;
        if ($zoneIds !== null && $zoneIds !== []) {
            $zoneIds = array_values(array_unique(array_map('strval', $zoneIds)));
            if (count($zoneIds) > (int) config('ad_broadcast.max_zones', 50)) {
                throw ValidationException::withMessages([
                    'zone_ids' => translate('Too many zones selected'),
                ]);
            }

            $found = Zone::query()->whereIn('id', $zoneIds)->count();
            if ($found !== count($zoneIds)) {
                throw ValidationException::withMessages([
                    'zone_ids' => translate('Invalid zones'),
                ]);
            }
        } else {
            $zoneIds = null;
        }

        $this->enforcePublishRateLimit($data->publisherUserId);

        $estimate = $this->estimator->estimate($audiences, $zoneIds);

        $contentTarget = $this->contentTargetValidator->resolve(
            $data->contentMode,
            $data->parentCategoryId,
            $data->childCategoryId,
            $data->serviceId,
        );

        $broadcast = AdBroadcast::query()->create(array_merge([
            'send_id' => Str::uuid()->toString(),
            'audience' => implode(',', $audiences),
            'title' => $this->storageText($data->title),
            'description' => $this->storageText($data->description),
            'image_path' => $data->imagePathForRecord(),
            'user_id' => $data->publisherUserId,
            'zone_ids' => $zoneIds,
            'explicit_user_ids' => null,
            'fcm_status' => AdBroadcast::FCM_PENDING,
            'recipient_estimate' => $estimate,
        ], $contentTarget->toBroadcastAttributes()));

        $this->dispatchFcmJob($broadcast->id);

        return $broadcast->fresh();
    }

    /**
     * @throws ValidationException
     */
    protected function publishExplicitAudience(PublishAdBroadcastData $data): AdBroadcast
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $data->explicitUserIds ?? []))));

        if ($ids === []) {
            throw ValidationException::withMessages([
                'explicit_user_ids' => translate('Invalid users'),
            ]);
        }

        $max = (int) config('ad_broadcast.max_explicit_users', 5000);
        if (count($ids) > $max) {
            throw ValidationException::withMessages([
                'explicit_user_ids' => translate('Too many users'),
            ]);
        }

        $found = User::query()->whereIn('id', $ids)->count();
        if ($found !== count($ids)) {
            throw ValidationException::withMessages([
                'explicit_user_ids' => translate('Invalid users'),
            ]);
        }

        $this->enforcePublishRateLimit($data->publisherUserId);

        $estimate = $this->tokenQuery->forUserIds($ids)->count();

        $contentTarget = $this->contentTargetValidator->resolve(
            $data->contentMode,
            $data->parentCategoryId,
            $data->childCategoryId,
            $data->serviceId,
        );

        $broadcast = AdBroadcast::query()->create(array_merge([
            'send_id' => Str::uuid()->toString(),
            'audience' => 'explicit',
            'title' => $this->storageText($data->title),
            'description' => $this->storageText($data->description),
            'image_path' => $data->imagePathForRecord(),
            'user_id' => $data->publisherUserId,
            'zone_ids' => null,
            'explicit_user_ids' => $ids,
            'fcm_status' => AdBroadcast::FCM_PENDING,
            'recipient_estimate' => $estimate,
        ], $contentTarget->toBroadcastAttributes()));

        $this->dispatchFcmJob($broadcast->id);

        return $broadcast->fresh();
    }

    protected function enforcePublishRateLimit(?string $publisherUserId): void
    {
        if ($publisherUserId === null || $publisherUserId === '') {
            return;
        }

        $executed = RateLimiter::attempt(
            'ad-broadcast-publish:'.$publisherUserId,
            (int) config('ad_broadcast.rate_limit_per_minute', 20),
            fn (): bool => true,
            60,
        );

        if (! $executed) {
            throw ValidationException::withMessages([
                'title' => translate('Too many broadcasts. Please wait a minute.'),
            ]);
        }
    }

    /**
     * @param  string|array<string, string>|null  $text
     */
    private function storageText(string|array|null $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        if (is_array($text)) {
            $sanitized = NotificationLocale::decodeMap($text);

            return $sanitized === [] ? null : NotificationLocale::encodeForStorage($sanitized);
        }

        return $text;
    }

    protected function dispatchFcmJob(int $broadcastId): void
    {
        $queue = config('ad_broadcast.queue');
        $pendingDispatch = DispatchAdBroadcastFcmJob::dispatch($broadcastId);
        if (is_string($queue) && $queue !== '') {
            $pendingDispatch->onQueue($queue);
        }
    }
}
