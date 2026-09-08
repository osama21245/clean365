<?php

namespace App\Jobs\AdBroadcast;

use App\Models\AdBroadcast;
use App\Models\AdBroadcastDispatch;
use App\Services\AdBroadcast\AdBroadcastFcmPayload;
use App\Services\AdBroadcast\AdBroadcastInAppNotifier;
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
use Modules\UserManagement\Entities\User;

class SendFcmAdMulticastChunkJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * @param  list<array{user_id: string, token: string, ui_locale?: string|null}>  $recipients
     */
    public function __construct(
        public int $adBroadcastId,
        public string $audienceKey,
        public int $chunkIndex,
        public string $dispatchKey,
        public array $recipients,
    ) {}

    public function uniqueId(): string
    {
        return $this->dispatchKey;
    }

    public function handle(
        CloudMessaging $cloudMessaging,
        AdBroadcastFcmPayload $fcmPayload,
        AdBroadcastInAppNotifier $inAppNotifier,
    ): void {
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
                'dispatch_type' => 'multicast',
                'status' => 'pending',
            ],
        );

        if ($dispatch->status === 'sent') {
            Log::info('ad_broadcast.multicast.skip_duplicate', [
                'send_id' => $sendId,
                'dispatch_key' => $this->dispatchKey,
            ]);

            return;
        }

        if ($this->recipients === []) {
            $dispatch->update(['status' => 'sent', 'metrics' => ['empty' => true]]);

            return;
        }

        $inAppNotifier->recordForRecipients($broadcast, $this->recipients, $this->audienceKey);

        $titleMap = NotificationLocale::decodeMap($broadcast->getRawOriginal('title'));
        $descriptionMap = NotificationLocale::decodeMap($broadcast->getRawOriginal('description') ?? '');
        $localized = count($titleMap) >= 2;

        $imageUrl = $broadcast->publicImageUrl() ?? '';
        $dataPayload = $fcmPayload->forBroadcast($broadcast, $this->audienceKey);

        $totalSuccesses = 0;
        $totalFailures = 0;
        $invalidUserIds = [];
        $localeMetrics = [];

        $groups = $localized
            ? $this->groupRecipientsByLocale()
            : ['_' => $this->recipients];

        foreach ($groups as $localeKey => $groupRecipients) {
            $tokens = array_column($groupRecipients, 'token');

            $title = $localized
                ? NotificationLocale::resolve($titleMap, $localeKey === '_' ? null : $localeKey)
                : (string) $broadcast->title;
            $body = $localized
                ? NotificationLocale::resolve($descriptionMap, $localeKey === '_' ? null : $localeKey)
                : (string) ($broadcast->description ?? '');

            $result = $cloudMessaging->sendMulticastWithReport(
                $title,
                $body,
                $imageUrl,
                $dataPayload,
                $tokens,
            );

            $totalSuccesses += $result['successes'];
            $totalFailures += $result['failures'];
            $localeMetrics[$localeKey] = [
                'successes' => $result['successes'],
                'failures' => $result['failures'],
            ];

            $report = $result['report'];
            foreach ($report->getItems() as $idx => $item) {
                if (! isset($groupRecipients[$idx]) || $item->isSuccess()) {
                    continue;
                }

                if ($item->messageWasSentToUnknownToken() || $item->messageTargetWasInvalid()) {
                    $invalidUserIds[] = (string) $groupRecipients[$idx]['user_id'];
                }
            }
        }

        $invalidUserIds = array_values(array_unique($invalidUserIds));

        DB::transaction(function () use ($dispatch, $totalSuccesses, $totalFailures, $broadcast, $localeMetrics, $localized) {
            $ok = $totalSuccesses > 0 || $totalFailures === 0;

            $dispatch->update([
                'status' => $ok ? 'sent' : 'failed',
                'metrics' => [
                    'successes' => $totalSuccesses,
                    'failures' => $totalFailures,
                    'localized' => $localized,
                    'by_locale' => $localeMetrics,
                ],
            ]);

            AdBroadcast::query()->whereKey($broadcast->id)->increment('tokens_success', $totalSuccesses);
            AdBroadcast::query()->whereKey($broadcast->id)->increment('tokens_failure', $totalFailures);
        });

        if ($invalidUserIds !== []) {
            User::query()->whereIn('id', $invalidUserIds)->update(['fcm_token' => null]);
        }

        Log::info('ad_broadcast.multicast.chunk', [
            'send_id' => $sendId,
            'audience' => $this->audienceKey,
            'chunk' => $this->chunkIndex,
            'successes' => $totalSuccesses,
            'failures' => $totalFailures,
            'tokens_invalid_cleared' => count($invalidUserIds),
            'localized' => $localized,
        ]);
    }

    /**
     * @return array<string, list<array{user_id: string, token: string, ui_locale?: string|null}>>
     */
    private function groupRecipientsByLocale(): array
    {
        $groups = [];

        foreach ($this->recipients as $row) {
            $locale = NotificationLocale::normalize($row['ui_locale'] ?? null);
            $groups[$locale][] = $row;
        }

        return $groups;
    }
}
