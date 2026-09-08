<?php

namespace Modules\BlogModule\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BlogModule\Entities\Article;
use Modules\BlogModule\Jobs\GenerateSeoArticleForServiceJob;
use Modules\BlogModule\Services\Gemini\GeminiVertexClient;

class BlogAutomationHealth
{
    public const SCHEDULE_INTERVAL = 'hourly';

    private const JOB_CLASS = GenerateSeoArticleForServiceJob::class;

    public function snapshot(int $todayCount, int $monthCount): array
    {
        $enabled = BlogAutomationSettings::enabled();
        $dailyLimit = BlogAutomationSettings::dailyLimit();
        $monthlyLimit = BlogAutomationSettings::monthlyLimit();
        $dailyLimitReached = $todayCount >= $dailyLimit;
        $monthlyLimitReached = $monthCount >= $monthlyLimit;

        $blockedReason = $this->blockedReason($enabled, $dailyLimitReached, $monthlyLimitReached);
        $checks = $this->buildChecks($enabled, $dailyLimitReached, $monthlyLimitReached);

        $lastArticle = Article::query()
            ->where('generated_by_ai', true)
            ->latest('created_at')
            ->first(['id', 'slug', 'title', 'created_at', 'published_at']);

        $lastError = Cache::get('blog_automation:last_error');

        return [
            'overall' => $this->overallStatus($enabled, $blockedReason, $checks, $lastError),
            'schedule_label' => translate('Every hour at minute :00'),
            'next_run_at' => $blockedReason ? null : $this->nextRunAt(),
            'next_run_blocked_reason' => $blockedReason,
            'last_schedule_at' => $this->parseCachedTime(Cache::get('blog_automation:last_schedule_at')),
            'last_schedule_message' => Cache::get('blog_automation:last_schedule_message'),
            'last_success_at' => $this->parseCachedTime(Cache::get('blog_automation:last_success_at')),
            'last_error' => is_array($lastError) ? $lastError : null,
            'last_ai_article' => $lastArticle,
            'pending_jobs' => $this->pendingBlogJobsCount(),
            'failed_jobs' => $this->failedBlogJobsCount(),
            'checks' => $checks,
        ];
    }

    public function nextRunAt(): Carbon
    {
        return now()->copy()->startOfHour()->addHour();
    }

    public static function recordScheduleRun(string $message): void
    {
        $ttl = now()->addDays(90);

        Cache::put('blog_automation:last_schedule_at', now()->toIso8601String(), $ttl);
        Cache::put('blog_automation:last_schedule_message', $message, $ttl);
    }

    public static function recordSuccess(string $slug): void
    {
        $ttl = now()->addDays(90);

        Cache::put('blog_automation:last_success_at', now()->toIso8601String(), $ttl);
        Cache::forget('blog_automation:last_error');
        Cache::put('blog_automation:last_success_slug', $slug, $ttl);
    }

    public static function recordError(string $message): void
    {
        Cache::put('blog_automation:last_error', [
            'message' => $message,
            'at' => now()->toIso8601String(),
        ], now()->addDays(30));
    }

    private function blockedReason(bool $enabled, bool $dailyLimitReached, bool $monthlyLimitReached): ?string
    {
        if (!$enabled) {
            return translate('Automation is disabled');
        }

        if ($dailyLimitReached) {
            return translate('Daily article limit reached');
        }

        if ($monthlyLimitReached) {
            return translate('Monthly article limit reached');
        }

        return null;
    }

    /**
     * @return list<array{key: string, label: string, status: string, detail: string}>
     */
    private function buildChecks(bool $enabled, bool $dailyLimitReached, bool $monthlyLimitReached): array
    {
        $queueConnection = (string) config('queue.default', 'sync');
        $gemini = app(GeminiVertexClient::class);
        $hasTextKey = $gemini->resolveTextApiKey() !== '';
        $hasImageKey = $gemini->resolveImageApiKey() !== '';
        $jobsTable = Schema::hasTable('jobs');
        $failedJobsTable = Schema::hasTable('failed_jobs');
        $pending = $this->pendingBlogJobsCount();
        $failed = $this->failedBlogJobsCount();
        $lastSchedule = $this->parseCachedTime(Cache::get('blog_automation:last_schedule_at'));

        $checks = [
            $this->check(
                'automation',
                translate('AI automation enabled'),
                $enabled ? 'ok' : 'muted',
                $enabled ? translate('Automatic hourly generation is on') : translate('Turn on the checkbox below to enable')
            ),
            $this->check(
                'gemini_text',
                translate('Gemini text API key'),
                $hasTextKey ? 'ok' : 'error',
                $hasTextKey ? translate('Configured') : translate('Missing — set GEMINI_TEXT_API_KEY or GEMINI_API_KEY in .env')
            ),
            $this->check(
                'gemini_image',
                translate('Gemini image API key'),
                $hasImageKey ? 'ok' : 'warning',
                $hasImageKey ? translate('Configured') : translate('Missing — featured images may use service fallback')
            ),
            $this->check(
                'queue',
                translate('Queue connection'),
                $queueConnection === 'sync' ? 'warning' : 'ok',
                $queueConnection === 'sync'
                    ? translate('QUEUE_CONNECTION=sync (use database + worker in production)')
                    : translate('Using :connection driver', ['connection' => $queueConnection])
            ),
            $this->check(
                'jobs_table',
                translate('Jobs table'),
                $jobsTable ? 'ok' : 'error',
                $jobsTable ? translate('Ready') : translate('Run queue migrations (jobs table missing)')
            ),
            $this->check(
                'failed_jobs_table',
                translate('Failed jobs table'),
                $failedJobsTable ? 'ok' : 'warning',
                $failedJobsTable ? translate('Ready') : translate('Run failed_jobs migration to track failures')
            ),
            $this->check(
                'scheduler',
                translate('Laravel scheduler (cron)'),
                $this->schedulerStatus($enabled, $lastSchedule),
                $this->schedulerDetail($enabled, $lastSchedule)
            ),
            $this->check(
                'queue_worker',
                translate('Queue worker'),
                $this->queueWorkerStatus($queueConnection, $jobsTable, $pending, $failed),
                $this->queueWorkerDetail($queueConnection, $jobsTable, $pending, $failed)
            ),
            $this->check(
                'limits',
                translate('Usage limits'),
                ($dailyLimitReached || $monthlyLimitReached) ? 'warning' : 'ok',
                ($dailyLimitReached || $monthlyLimitReached)
                    ? translate('A limit is reached — scheduled runs will skip until reset')
                    : translate('Within daily and monthly limits')
            ),
        ];

        return $checks;
    }

    private function schedulerStatus(bool $enabled, ?Carbon $lastSchedule): string
    {
        if (!$enabled) {
            return 'muted';
        }

        if (!$lastSchedule) {
            return 'warning';
        }

        return $lastSchedule->diffInMinutes(now()) <= 90 ? 'ok' : 'warning';
    }

    private function schedulerDetail(bool $enabled, ?Carbon $lastSchedule): string
    {
        if (!$enabled) {
            return translate('Enable automation to use hourly schedule');
        }

        if (!$lastSchedule) {
            return translate('No schedule run recorded yet — add cron: * * * * * php artisan schedule:run');
        }

        return translate('Last tick :time (:ago)', [
            'time' => $lastSchedule->format('Y-m-d H:i'),
            'ago' => $lastSchedule->diffForHumans(),
        ]);
    }

    private function queueWorkerStatus(string $queueConnection, bool $jobsTable, int $pending, int $failed): string
    {
        if ($queueConnection === 'sync') {
            return 'muted';
        }

        if (!$jobsTable) {
            return 'error';
        }

        if ($failed > 0) {
            return 'error';
        }

        if ($pending > 0) {
            return 'warning';
        }

        return 'ok';
    }

    private function queueWorkerDetail(string $queueConnection, bool $jobsTable, int $pending, int $failed): string
    {
        if ($queueConnection === 'sync') {
            return translate('Not required when using sync queue');
        }

        if (!$jobsTable) {
            return translate('Cannot queue jobs without jobs table');
        }

        if ($failed > 0) {
            return translate(':count failed blog job(s) — check failed_jobs or logs', ['count' => $failed]);
        }

        if ($pending > 0) {
            return translate(':count job(s) waiting — ensure clean365-queue.service is running', ['count' => $pending]);
        }

        return translate('No pending blog jobs');
    }

    /**
     * @param  list<array{key: string, label: string, status: string, detail: string}>  $checks
     */
    private function overallStatus(bool $enabled, ?string $blockedReason, array $checks, mixed $lastError): string
    {
        if (!$enabled) {
            return 'disabled';
        }

        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                return 'error';
            }
        }

        if (is_array($lastError) && !empty($lastError['message'])) {
            return 'warning';
        }

        foreach ($checks as $check) {
            if ($check['status'] === 'warning') {
                return 'warning';
            }
        }

        if ($blockedReason) {
            return 'paused';
        }

        return 'healthy';
    }

    private function check(string $key, string $label, string $status, string $detail): array
    {
        return compact('key', 'label', 'status', 'detail');
    }

    private function parseCachedTime(mixed $value): ?Carbon
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function pendingBlogJobsCount(): int
    {
        if (!Schema::hasTable('jobs')) {
            return 0;
        }

        try {
            return (int) DB::table('jobs')
                ->where('payload', 'like', '%'.class_basename(self::JOB_CLASS).'%')
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function failedBlogJobsCount(): int
    {
        if (!Schema::hasTable('failed_jobs')) {
            return 0;
        }

        try {
            return (int) DB::table('failed_jobs')
                ->where('payload', 'like', '%'.class_basename(self::JOB_CLASS).'%')
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
