<?php

namespace App\Jobs\AdBroadcast;

use App\Models\AdBroadcast;
use App\Models\AdBroadcastDispatch;
use App\Services\AdBroadcast\AdBroadcastFcmPayload;
use App\Services\Firebase\CloudMessaging;
use App\Support\NotificationLocale;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendFcmAdTopicDispatchJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public int $adBroadcastId,
        public string $audienceKey,
        public string $topic,
        public string $dispatchKey,
    ) {}

    public function uniqueId(): string
    {
        return $this->dispatchKey;
    }

    public function handle(CloudMessaging $cloudMessaging, AdBroadcastFcmPayload $fcmPayload): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $broadcast = AdBroadcast::query()->find($this->adBroadcastId);

        if (! $broadcast || ! $broadcast->send_id) {
            return;
        }

        $sendId = $broadcast->send_id;

        $dispatch = AdBroadcastDispatch::query()->firstOrCreate(
            [
                'dispatch_key' => $this->dispatchKey,
            ],
            [
                'ad_broadcast_id' => $this->adBroadcastId,
                'dispatch_type' => 'topic',
                'status' => 'pending',
            ],
        );

        if ($dispatch->status === 'sent') {
            Log::info('ad_broadcast.topic.skip_duplicate', [
                'send_id' => $sendId,
                'dispatch_key' => $this->dispatchKey,
            ]);

            return;
        }

        $imageUrl = $broadcast->publicImageUrl() ?? '';

        $titleMap = NotificationLocale::decodeMap($broadcast->getRawOriginal('title'));
        $descriptionMap = NotificationLocale::decodeMap($broadcast->getRawOriginal('description') ?? '');
        $localized = count($titleMap) >= 2;
        $topicLocale = $this->audienceKey === 'guests' ? 'ar' : NotificationLocale::current();

        $cloudMessaging->setNotification(
            $localized
                ? NotificationLocale::resolve($titleMap, $topicLocale)
                : (string) $broadcast->getRawOriginal('title'),
            $localized
                ? NotificationLocale::resolve($descriptionMap, $topicLocale)
                : (string) ($broadcast->getRawOriginal('description') ?? ''),
            $imageUrl
        );

        $cloudMessaging->setData($cloudMessaging->normalizeDataPayload(
            $fcmPayload->forBroadcast($broadcast, $this->audienceKey)
        ));

        $result = $cloudMessaging->massSendWithFallback($this->topic, [
            'source' => 'dashboard_ad',
            'send_id' => $sendId,
            'audience' => $this->audienceKey,
            'broadcast_to_secondary' => true,
        ]);

        Log::info('ad_broadcast.topic.result', [
            'send_id' => $sendId,
            'topic' => $this->topic,
            'ok' => $result['ok'] ?? false,
        ]);

        DB::transaction(function () use ($dispatch, $result, $broadcast) {
            $dispatch->update([
                'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                'metrics' => $result,
            ]);

            if ($result['ok'] ?? false) {
                AdBroadcast::query()->whereKey($broadcast->id)->increment('topic_dispatches_ok');
            }
        });
    }
}
