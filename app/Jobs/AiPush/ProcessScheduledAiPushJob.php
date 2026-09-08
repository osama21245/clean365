<?php

namespace App\Jobs\AiPush;

use App\Services\AiPush\AiPushNotificationDispatcher;
use App\Services\AiPush\AiPushSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs AI generation + ad broadcast publish in the queue so cron/schedule stays fast.
 */
class ProcessScheduledAiPushJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public int $uniqueFor = 900;

    public function __construct(public string $slot = 'interval') {}

    public function uniqueId(): string
    {
        return 'ai-push-scheduled';
    }

    public function handle(AiPushNotificationDispatcher $dispatcher): void
    {
        $settings = AiPushSettings::load();

        if (! $settings->ai_push_enabled) {
            return;
        }

        try {
            $sent = $dispatcher->dispatch($settings, $this->slot);
            Log::info('ai_push.scheduled.completed', [
                'slot' => $this->slot,
                'summaries' => $sent,
            ]);
        } catch (\Throwable $e) {
            Log::error('ai_push.scheduled.failed', [
                'slot' => $this->slot,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
