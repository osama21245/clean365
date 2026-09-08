<?php

namespace App\Jobs\AdBroadcast;

use App\Models\AdBroadcast;
use App\Services\AdBroadcast\AdBroadcastTokenQuery;
use App\Support\NotificationLocale;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchAdBroadcastFcmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $adBroadcastId) {}

    public function failed(\Throwable $exception): void
    {
        Log::error('ad_broadcast.dispatch.job_failed', [
            'ad_broadcast_id' => $this->adBroadcastId,
            'message' => $exception->getMessage(),
            'exception' => $exception::class,
        ]);

        $row = AdBroadcast::query()->find($this->adBroadcastId);
        if ($row) {
            $row->forceFill([
                'fcm_status' => AdBroadcast::FCM_FAILED,
                'fcm_summary' => array_merge($row->fcm_summary ?? [], [
                    'dispatch_error' => $exception->getMessage(),
                ]),
            ])->save();
        }
    }

    public function handle(AdBroadcastTokenQuery $tokenQuery): void
    {
        $broadcast = AdBroadcast::query()->find($this->adBroadcastId);

        if (! $broadcast || ! $broadcast->send_id) {
            Log::warning('ad_broadcast.dispatch.missing', ['ad_broadcast_id' => $this->adBroadcastId]);

            return;
        }

        $sendId = $broadcast->send_id;

        Log::info('ad_broadcast.dispatch.start', [
            'send_id' => $sendId,
            'ad_broadcast_id' => $this->adBroadcastId,
        ]);

        $broadcast->forceFill([
            'fcm_status' => AdBroadcast::FCM_PROCESSING,
        ])->save();

        $explicitIds = $broadcast->explicit_user_ids;
        if (is_array($explicitIds) && $explicitIds !== []) {
            $this->dispatchExplicitMulticast($broadcast, $tokenQuery, $sendId);

            return;
        }

        $audiences = $broadcast->audienceKeys();
        $zoneIds = $broadcast->zone_ids;

        $jobs = [];
        $tokenTargetTotal = 0;
        $topicJobsPlanned = 0;
        $fcmSummary = array_merge($broadcast->fcm_summary ?? [], []);
        $localizedPush = $this->usesLocalizedPush($broadcast);

        $tokenAudiences = ['customers', 'providers', 'servicemen'];

        if ($zoneIds === null || $zoneIds === []) {
            if ($localizedPush) {
                $fcmSummary['localized_per_user_locale'] = true;
            }

            foreach ($audiences as $audienceKey) {
                $this->appendGlobalAudienceJobs(
                    $audienceKey,
                    $tokenQuery,
                    $sendId,
                    $localizedPush,
                    $jobs,
                    $tokenTargetTotal,
                    $topicJobsPlanned,
                    $fcmSummary,
                );
            }
        } else {
            // Zone-scoped: FCM topics are customer-{zone_id} / provider-admin-{zone_id} /
            // provider-serviceman-{zone_id}. users has no zone_id column, so multicast
            // cannot reliably geo-filter — topics are the geographic delivery path.
            $fcmSummary['zone_scoped_topics'] = true;

            foreach ($audiences as $audienceKey) {
                if (! in_array($audienceKey, $tokenAudiences, true)) {
                    continue;
                }

                foreach ($zoneIds as $zoneId) {
                    $topic = AdBroadcast::topicFor($audienceKey, (string) $zoneId);
                    if ($topic === null) {
                        continue;
                    }
                    $jobs[] = $this->makeTopicJob($sendId, $audienceKey, $topic, (string) $zoneId);
                    $topicJobsPlanned++;
                }
            }

            if (in_array('guests', $audiences, true)) {
                $fcmSummary['guests_global_topic_with_zone_scope'] = true;
                $jobs[] = $this->makeTopicJob($sendId, 'guests', AdBroadcast::topicFor('guests'));
                $topicJobsPlanned++;
            }
        }

        $broadcast->forceFill([
            'tokens_targeted' => $tokenTargetTotal,
            'topic_dispatches_total' => $topicJobsPlanned,
            'fcm_summary' => $fcmSummary,
        ])->save();

        $this->dispatchBatch($jobs, $sendId);
    }

    public static function hashDispatchKey(string $sendId, string $suffix): string
    {
        return substr(hash('sha256', $sendId.'|'.$suffix), 0, 64);
    }

    protected function dispatchExplicitMulticast(AdBroadcast $broadcast, AdBroadcastTokenQuery $tokenQuery, string $sendId): void
    {
        $jobs = [];
        $tokenRows = $tokenQuery->forUserIds($broadcast->explicit_user_ids ?? []);
        $tokenTargetTotal = $tokenRows->count();

        $this->appendMulticastChunks('explicit', $sendId, $tokenRows, $jobs);

        $fcmSummary = array_merge($broadcast->fcm_summary ?? [], [
            'explicit_user_targeting' => true,
            'explicit_ids_requested' => count(array_unique(array_map('strval', $broadcast->explicit_user_ids ?? []))),
        ]);

        $broadcast->forceFill([
            'tokens_targeted' => $tokenTargetTotal,
            'topic_dispatches_total' => 0,
            'fcm_summary' => $fcmSummary,
        ])->save();

        $this->dispatchBatch($jobs, $sendId);
    }

    private function usesLocalizedPush(AdBroadcast $broadcast): bool
    {
        return count(NotificationLocale::decodeMap($broadcast->getRawOriginal('title'))) >= 2;
    }

    /**
     * @param  list<object>  $jobs
     */
    private function appendGlobalAudienceJobs(
        string $audienceKey,
        AdBroadcastTokenQuery $tokenQuery,
        string $sendId,
        bool $localizedPush,
        array &$jobs,
        int &$tokenTargetTotal,
        int &$topicJobsPlanned,
        array &$fcmSummary,
    ): void {
        if ($audienceKey === 'guests') {
            $topic = AdBroadcast::topicFor('guests');
            if ($topic) {
                $fcmSummary['guests_topic_default'] = $localizedPush;
                $jobs[] = $this->makeTopicJob($sendId, 'guests', $topic);
                $topicJobsPlanned++;
            }

            return;
        }

        if (! in_array($audienceKey, ['customers', 'providers', 'servicemen'], true)) {
            return;
        }

        $tokenRows = config('ad_broadcast.prefer_fcm_token_multicast', true)
            ? $tokenQuery->forAudienceAll($audienceKey)
            : collect();

        if ($tokenRows->isNotEmpty()) {
            $tokenTargetTotal += $tokenRows->count();
            $fcmSummary['fcm_token_multicast_'.$audienceKey] = true;

            if ($localizedPush) {
                $fcmSummary['per_ui_locale_'.$audienceKey] = true;
            }

            $this->appendMulticastChunks($audienceKey, $sendId, $tokenRows, $jobs);

            if (config('ad_broadcast.skip_topic_when_tokens_sent', true)) {
                $fcmSummary['topic_skipped_'.$audienceKey] = 'multicast_per_fcm_token';

                return;
            }
        }

        $topic = AdBroadcast::topicFor($audienceKey);
        if (config('ad_broadcast.always_send_global_topic', true) && $topic) {
            $fcmSummary['topic_fallback_'.$audienceKey] = $tokenRows->isEmpty()
                ? 'no_fcm_tokens'
                : 'topic_also_enabled';
            $jobs[] = $this->makeTopicJob($sendId, $audienceKey, $topic);
            $topicJobsPlanned++;
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{user_id: string, token: string, ui_locale: string|null}>  $tokenRows
     * @param  list<object>  $jobs
     */
    private function appendMulticastChunks(
        string $audienceKey,
        string $sendId,
        $tokenRows,
        array &$jobs,
    ): void {
        $chunkSize = max(1, (int) config('ad_broadcast.chunk_size', 500));
        $chunkIndex = 0;

        foreach ($tokenRows->chunk($chunkSize) as $chunk) {
            $dispatchKey = static::hashDispatchKey($sendId, 'multicast|'.$audienceKey.'|'.$chunkIndex);
            $jobs[] = new SendFcmAdMulticastChunkJob(
                $this->adBroadcastId,
                $audienceKey,
                $chunkIndex,
                $dispatchKey,
                $chunk->values()->all(),
            );
            $chunkIndex++;
        }
    }

    private function makeTopicJob(string $sendId, string $audienceKey, string $topic, ?string $zoneId = null): SendFcmAdTopicDispatchJob
    {
        $suffix = $zoneId ? 'topic|'.$audienceKey.'|'.$zoneId : 'topic|'.$audienceKey;
        $dispatchKey = static::hashDispatchKey($sendId, $suffix);

        return new SendFcmAdTopicDispatchJob(
            $this->adBroadcastId,
            $audienceKey,
            $topic,
            $dispatchKey,
        );
    }

    /**
     * @param  list<object>  $jobs
     */
    private function dispatchBatch(array $jobs, string $sendId): void
    {
        $adBroadcastId = $this->adBroadcastId;

        if ($jobs === []) {
            $row = AdBroadcast::query()->find($adBroadcastId);
            if ($row) {
                $row->forceFill([
                    'fcm_status' => AdBroadcast::FCM_COMPLETED,
                    'fcm_summary' => array_merge($row->fcm_summary ?? [], [
                        'message' => 'no_fcm_jobs_generated',
                    ]),
                ])->save();
            }

            Log::info('ad_broadcast.dispatch.empty', ['send_id' => $sendId]);

            return;
        }

        $queue = config('ad_broadcast.queue');

        $batch = Bus::batch($jobs)
            ->then(function () use ($adBroadcastId, $sendId) {
                Log::info('ad_broadcast.dispatch.batch_then', ['send_id' => $sendId, 'ad_broadcast_id' => $adBroadcastId]);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($sendId, $adBroadcastId) {
                Log::error('ad_broadcast.dispatch.batch_catch', [
                    'send_id' => $sendId,
                    'ad_broadcast_id' => $adBroadcastId,
                    'error' => $e->getMessage(),
                ]);
            })
            ->finally(function (Batch $batch) use ($adBroadcastId) {
                $row = AdBroadcast::query()->find($adBroadcastId);
                if (! $row) {
                    return;
                }

                $partial = $batch->failedJobs > 0 || $batch->hasFailures();
                $row->forceFill([
                    'fcm_status' => $partial ? AdBroadcast::FCM_PARTIAL : AdBroadcast::FCM_COMPLETED,
                    'fcm_summary' => array_merge($row->fcm_summary ?? [], [
                        'batch_finished_at' => now()->toIso8601String(),
                        'batch_failed_jobs' => $batch->failedJobs,
                        'batch_total_jobs' => $batch->totalJobs,
                    ]),
                ])->save();
            })
            ->allowFailures();

        if (is_string($queue) && $queue !== '') {
            $batch = $batch->onQueue($queue);
        }

        $batchInstance = $batch->dispatch();

        AdBroadcast::query()->whereKey($adBroadcastId)->update([
            'laravel_batch_id' => $batchInstance->id,
        ]);
    }
}
