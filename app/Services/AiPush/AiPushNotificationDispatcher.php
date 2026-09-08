<?php

namespace App\Services\AiPush;

use App\Dto\PublishAdBroadcastData;
use App\Services\AdBroadcast\AdBroadcastPublisher;
use App\Support\NotificationLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\UserManagement\Entities\User;

class AiPushNotificationDispatcher
{
    public function __construct(
        protected AiPushNotificationGenerator $generator,
        protected AdBroadcastPublisher $publisher,
        protected AiPushServicePicker $servicePicker,
        protected AiPushNotificationScheduler $scheduler,
    ) {}

    /**
     * @return list<string> Sent broadcast summaries per audience
     */
    public function dispatch(?AiPushSettings $settings = null, ?string $slotTime = null): array
    {
        $settings = $settings ?? AiPushSettings::load();

        $service = $this->servicePicker->pickNext($settings);

        if (! $service) {
            throw new \RuntimeException('No active services found for AI push notifications.');
        }

        $audiences = $this->normalizeAudiences((array) ($settings->ai_push_audiences ?? ['customers']));
        $recentTopics = $settings->ai_push_recent_topics ?? [];
        $sentSummaries = [];
        $serviceName = (string) $service->name;

        try {
            $contentByAudience = $this->generator->generateForAudiences($audiences, $settings, $service);

            foreach ($audiences as $audience) {
                $content = $contentByAudience[$audience] ?? null;
                if ($content === null) {
                    throw new \RuntimeException("AI did not return content for audience: {$audience}");
                }

                $this->publisher->publish(new PublishAdBroadcastData(
                    audiences: [$audience],
                    zoneIds: null,
                    title: $content['title'],
                    description: $content['description'],
                    storedImagePath: null,
                    resendStoragePath: null,
                    publisherUserId: $this->resolvePublisherUserId(),
                    explicitUserIds: null,
                    contentMode: 'service',
                    parentCategoryId: null,
                    childCategoryId: null,
                    serviceId: (string) $service->id,
                ));

                $recentTopics = $this->generator->appendRecentTopic($recentTopics, $audience, $content['topic_key']);
                $previewTitle = NotificationLocale::resolve($content['title'], 'ar');
                $sentSummaries[] = $this->audienceLabel($audience).' ('.$serviceName.'): '.$previewTitle;
            }

            $this->servicePicker->advanceCursor($settings, $service);

            if ($slotTime !== null && $slotTime !== 'interval') {
                $this->scheduler->markSlotSent($settings, $slotTime);
            } else {
                $settings->update(['ai_push_last_sent_at' => now()]);
            }

            $settings->update([
                'ai_push_recent_topics' => $recentTopics,
                'ai_push_last_error' => null,
                'ai_push_last_title' => implode(' | ', $sentSummaries),
            ]);

            $settings->updateCache();

            return $sentSummaries;
        } catch (\Throwable $e) {
            Log::error('AI push notification failed', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
                'slot' => $slotTime,
            ]);

            $failureUpdates = [
                'ai_push_last_error' => Str::limit($e->getMessage(), 2000),
            ];

            if ($slotTime === null || $slotTime === 'interval') {
                $failureUpdates['ai_push_last_sent_at'] = now();
            } elseif ($this->scheduler->isSlotStale($slotTime)) {
                $this->scheduler->markSlotMissed($settings, $slotTime);
            }

            $settings->update($failureUpdates);
            $settings->updateCache();

            throw $e;
        }
    }

    /**
     * @param  list<string>  $audiences
     * @return list<string>
     */
    private function normalizeAudiences(array $audiences): array
    {
        $allowed = ['customers', 'providers', 'servicemen', 'guests'];
        $filtered = array_values(array_unique(array_intersect($audiences, $allowed)));

        return $filtered !== [] ? $filtered : ['customers'];
    }

    private function audienceLabel(string $audience): string
    {
        return match ($audience) {
            'providers' => translate('Providers'),
            'servicemen' => translate('Servicemen'),
            'guests' => translate('Guests'),
            default => translate('Customers'),
        };
    }

    private function resolvePublisherUserId(): ?string
    {
        $admin = User::query()->where('user_type', 'super-admin')->value('id');

        if ($admin) {
            return (string) $admin;
        }

        $id = User::query()->value('id');

        return $id ? (string) $id : null;
    }
}
