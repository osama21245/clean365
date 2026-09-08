<?php

namespace App\Services\Firebase;

use App\Support\FirebaseCredentials;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class CloudMessaging
{
    protected ?Messaging $messaging = null;

    protected CloudMessage $cloudMessage;

    protected array $deviceTokens = [];

    protected bool $dashboardFallbackEnabled;

    protected bool $retryTransientOnce;

    protected ?Messaging $secondaryMessaging = null;

    /** @var array<string, mixed>|string|null */
    protected array|string|null $secondaryCredentials = null;

    public function __construct()
    {
        $this->cloudMessage = CloudMessage::new();
        $this->dashboardFallbackEnabled = (bool) config('services.firebase_dashboard_push.fallback_enabled', true);
        $this->retryTransientOnce = (bool) config('services.firebase_dashboard_push.retry_transient_once', true);
        $this->secondaryCredentials = FirebaseCredentials::resolve(
            (string) config('services.firebase_dashboard_push.secondary_credentials', '')
        );

        try {
            $messaging = app('firebase.messaging');
            $this->messaging = $messaging instanceof Messaging ? $messaging : null;
        } catch (\Throwable $th) {
            Log::error('FCM primary messaging unavailable: '.$th->getMessage());
            $this->messaging = null;
        }
    }

    public function setTokens(array $deviceTokens): static
    {
        $this->deviceTokens = $deviceTokens;

        return $this;
    }

    public function setNotification(string $title, string $body = '', string $imageUrl = ''): static
    {
        $this->cloudMessage = $this->buildPushMessage($title, $body, $imageUrl, []);

        return $this;
    }

    public function setData(array $data): static
    {
        $this->cloudMessage = $this->cloudMessage->withData($data);

        return $this;
    }

    public function setTopic(string $topic): static
    {
        $this->cloudMessage = $this->cloudMessage->toTopic($topic);

        return $this;
    }

    public function send()
    {
        if ($this->deviceTokens === [] || $this->messaging === null) {
            return;
        }

        try {
            $report = $this->messaging->sendMulticast($this->cloudMessage, $this->deviceTokens);

            $failedTokens = [];
            foreach ($report->getItems() as $index => $item) {
                if ($item->isFailure()) {
                    $failedTokens[] = $this->deviceTokens[$index];
                }
            }

            $secondaryMessaging = $this->secondaryMessaging();
            if ($failedTokens !== [] && $this->dashboardFallbackEnabled && $secondaryMessaging) {
                $secondaryMessaging->sendMulticast($this->cloudMessage, $failedTokens);
            }
        } catch (\Throwable $th) {
            $readableDeviceTokens = implode(',', $this->deviceTokens);

            Log::error("FCM failed with tokens: [$readableDeviceTokens] \n\n $th");
        }
    }

    /**
     * Multicast to registration tokens with full Kreait report (for metrics / token hygiene).
     *
     * @param  array<string, string>  $data  FCM data keys must be string values
     * @param  list<string>  $tokens
     * @return array{report: \Kreait\Firebase\Messaging\MulticastSendReport, successes: int, failures: int}
     */
    public function sendMulticastWithReport(
        string $title,
        string $body,
        string $imageUrl,
        array $data,
        array $tokens
    ): array {
        $message = $this->buildPushMessage($title, $body, $imageUrl, $data);

        if ($tokens === [] || $this->messaging === null) {
            $empty = \Kreait\Firebase\Messaging\MulticastSendReport::withItems([]);

            return [
                'report' => $empty,
                'successes' => 0,
                'failures' => 0,
            ];
        }

        $primaryReport = $this->messaging->sendMulticast($message, $tokens);

        $failedTokens = [];
        $failedTokenIndices = [];

        foreach ($primaryReport->getItems() as $index => $item) {
            if ($item->isFailure()) {
                $failedTokens[] = $tokens[$index];
                $failedTokenIndices[] = $index;
            }
        }

        $finalSuccesses = $primaryReport->successes()->count();
        $finalFailures = $primaryReport->failures()->count();
        $mergedReportItems = $primaryReport->getItems();

        $secondaryMessaging = $this->secondaryMessaging();
        if ($failedTokens !== [] && $this->dashboardFallbackEnabled && $secondaryMessaging) {
            $secondaryReport = $secondaryMessaging->sendMulticast($message, $failedTokens);

            foreach ($secondaryReport->getItems() as $subIndex => $subItem) {
                $originalIndex = $failedTokenIndices[$subIndex];
                if ($subItem->isSuccess()) {
                    $finalSuccesses++;
                    $finalFailures--;
                    $mergedReportItems[$originalIndex] = $subItem;
                } else {
                    $mergedReportItems[$originalIndex] = $subItem;
                }
            }
        }

        $finalReport = \Kreait\Firebase\Messaging\MulticastSendReport::withItems($mergedReportItems);

        return [
            'report' => $finalReport,
            'successes' => $finalSuccesses,
            'failures' => $finalFailures,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function buildPushMessage(string $title, string $body, string $imageUrl, array $data): CloudMessage
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body, $imageUrl))
            ->withData($this->normalizeDataPayload($data));

        if (config('services.firebase_dashboard_push.apns_enabled', true)) {
            $sound = (string) config('services.firebase_dashboard_push.apns_sound', 'default');
            $apnsConfig = $sound === 'default'
                ? ApnsConfig::new()->withDefaultSound()
                : ApnsConfig::new()->withSound($sound);

            $message = $message->withApnsConfig($apnsConfig);
        }

        return $message;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public function normalizeDataPayload(array $data): array
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[(string) $key] = match (true) {
                $value === null => '',
                is_scalar($value) => (string) $value,
                default => (string) json_encode($value),
            };
        }

        return $normalized;
    }

    public function massSend(string $topic)
    {
        $this->massSendWithFallback($topic);
    }

    /**
     * @param  array<string, scalar|null>  $context
     * @return array<string, mixed>
     */
    public function massSendWithFallback(string $topic, array $context = []): array
    {
        $this->setTopic($topic);
        $broadcastToSecondary = (bool) ($context['broadcast_to_secondary'] ?? false);

        $attempts = [];
        $classification = 'unknown';
        $finalStatus = 'failed';
        $usedFallback = false;

        if ($this->messaging === null) {
            $primaryAttempt = [
                'ok' => false,
                'project' => 'primary',
                'classification' => 'unknown',
                'error_message' => 'Primary Firebase messaging is not configured',
            ];
        } else {
            $primaryAttempt = $this->attemptTopicSend($this->messaging, 'primary');
        }

        $attempts[] = $primaryAttempt;

        if ($primaryAttempt['ok']) {
            $finalStatus = 'sent';
            $classification = 'success';

            if ($broadcastToSecondary && $this->dashboardFallbackEnabled) {
                $secondaryMessaging = $this->secondaryMessaging();

                if ($secondaryMessaging) {
                    $usedFallback = true;
                    $secondaryAttempt = $this->attemptTopicSend($secondaryMessaging, 'secondary');
                    $attempts[] = $secondaryAttempt;

                    if ($secondaryAttempt['ok']) {
                        $classification = 'success_on_both_projects';
                    } else {
                        $classification = $secondaryAttempt['classification'];
                        $finalStatus = 'failed';
                    }
                }
            }
        } else {
            $classification = $primaryAttempt['classification'];

            if ($this->messaging !== null && $classification === 'transient' && $this->retryTransientOnce) {
                $retryAttempt = $this->attemptTopicSend($this->messaging, 'primary_retry');
                $attempts[] = $retryAttempt;

                if ($retryAttempt['ok']) {
                    $finalStatus = 'sent';
                    $classification = 'success_after_retry';
                } else {
                    $classification = $retryAttempt['classification'];
                }
            }

            if ($finalStatus !== 'sent' && $classification === 'fallback_eligible' && $this->dashboardFallbackEnabled) {
                $secondaryMessaging = $this->secondaryMessaging();

                if ($secondaryMessaging) {
                    $usedFallback = true;
                    $secondaryAttempt = $this->attemptTopicSend($secondaryMessaging, 'secondary');
                    $attempts[] = $secondaryAttempt;

                    if ($secondaryAttempt['ok']) {
                        $finalStatus = 'sent';
                        $classification = 'success_via_fallback';
                    } else {
                        $classification = $secondaryAttempt['classification'];
                    }
                }
            }
        }

        $logContext = array_merge($context, [
            'topic' => $topic,
            'attempts' => $attempts,
            'classification' => $classification,
            'final_status' => $finalStatus,
            'used_fallback' => $usedFallback,
        ]);

        if ($finalStatus === 'sent') {
            Log::info('FCM topic send completed.', $logContext);
        } else {
            Log::error('FCM topic send failed.', $logContext);
        }

        return [
            'ok' => $finalStatus === 'sent',
            'topic' => $topic,
            'classification' => $classification,
            'final_status' => $finalStatus,
            'used_fallback' => $usedFallback,
            'attempts' => $attempts,
        ];
    }

    /**
     * @return array{ok: bool, project: string, classification: string, error_message: ?string}
     */
    protected function attemptTopicSend(Messaging $messaging, string $project): array
    {
        try {
            $messaging->send($this->cloudMessage);

            return [
                'ok' => true,
                'project' => $project,
                'classification' => 'success',
                'error_message' => null,
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'project' => $project,
                'classification' => $this->classifyException($exception),
                'error_message' => $exception->getMessage(),
            ];
        }
    }

    protected function classifyException(\Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (
            str_contains($message, 'senderid mismatch')
            || str_contains($message, 'sender id mismatch')
            || str_contains($message, 'unauthorized')
            || str_contains($message, 'mismatch sender')
            || str_contains($message, 'mismatched credential')
            || str_contains($message, 'project not permitted')
        ) {
            return 'fallback_eligible';
        }

        if (
            str_contains($message, 'unregistered')
            || str_contains($message, 'not registered')
            || str_contains($message, 'invalid registration')
            || str_contains($message, 'invalid argument')
            || str_contains($message, 'malformed')
            || str_contains($message, 'invalid token')
        ) {
            return 'terminal';
        }

        if (
            str_contains($message, 'unavailable')
            || str_contains($message, 'deadline exceeded')
            || str_contains($message, 'internal')
            || str_contains($message, 'timed out')
            || str_contains($message, 'timeout')
            || str_contains($message, 'temporar')
        ) {
            return 'transient';
        }

        return 'unknown';
    }

    protected function secondaryMessaging(): ?Messaging
    {
        if ($this->secondaryMessaging !== null) {
            return $this->secondaryMessaging;
        }

        if ($this->secondaryCredentials === null) {
            return null;
        }

        try {
            $this->secondaryMessaging = (new Factory)
                ->withServiceAccount($this->secondaryCredentials)
                ->createMessaging();
        } catch (\Throwable $th) {
            Log::error('FCM secondary messaging unavailable: '.$th->getMessage());

            return null;
        }

        return $this->secondaryMessaging;
    }
}
