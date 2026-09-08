<?php

namespace App\Console\Commands;

use App\Jobs\AiPush\ProcessScheduledAiPushJob;
use App\Services\AiPush\AiPushNotificationDispatcher;
use App\Services\AiPush\AiPushNotificationScheduler;
use App\Services\AiPush\AiPushSettings;
use Illuminate\Console\Command;
use Modules\BlogModule\Services\Gemini\GeminiVertexClient;

class SendScheduledAiPushNotificationCommand extends Command
{
    protected $signature = 'ai-push:send-scheduled
                            {--force : Send now regardless of schedule (for testing)}';

    protected $description = 'Send AI-generated FCM push notification when due (configured in admin Ad Broadcasts).';

    public function handle(
        AiPushNotificationScheduler $scheduler,
        AiPushNotificationDispatcher $dispatcher,
    ): int {
        $settings = AiPushSettings::load();

        if (! $this->option('force') && ! $scheduler->isDue($settings)) {
            $next = $scheduler->nextRunAt($settings);
            $this->line('AI push not due.'.($next ? ' Next run ~ '.$next->toDateTimeString() : ''));

            return self::SUCCESS;
        }

        $slot = $this->option('force') ? 'interval' : $scheduler->dueSlot($settings);

        if (! $settings->ai_push_enabled) {
            $this->warn('AI push is disabled in admin Ad Broadcast settings.');

            return self::SUCCESS;
        }

        $hasGemini = app(GeminiVertexClient::class)->resolveTextApiKey() !== '';
        $hasOpenAi = (string) config('services.image_generation.openai.api_key') !== '';
        if (! $hasGemini && ! $hasOpenAi) {
            $this->error('Set GEMINI_API_KEY and/or OPENAI_API_KEY in .env.');

            return self::FAILURE;
        }

        $runSync = $this->option('force') || config('ad_broadcast.ai_push_run_sync', true);

        if (! $runSync) {
            $queue = config('ad_broadcast.ai_push_queue');
            $pending = ProcessScheduledAiPushJob::dispatch($slot);
            if (is_string($queue) && $queue !== '') {
                $pending->onQueue($queue);
            }
            $this->info('AI push queued (slot: '.$slot.'). Run: php artisan queue:work');

            return self::SUCCESS;
        }

        try {
            $sent = $dispatcher->dispatch($settings, $slot);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($sent as $line) {
            $this->info('Queued: '.$line);
        }

        return self::SUCCESS;
    }
}
