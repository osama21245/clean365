<?php

namespace Modules\BlogModule\Console;

use Illuminate\Console\Command;
use Modules\BlogModule\Entities\Article;
use Modules\BlogModule\Jobs\GenerateSeoArticleForServiceJob;
use Modules\BlogModule\Support\BlogAutomationHealth;
use Modules\BlogModule\Support\BlogAutomationSettings;
use Modules\ServiceManagement\Entities\Service;

class GenerateSeoArticles extends Command
{
    protected $signature = 'app:generate-seo-articles
                            {--limit=1 : Max jobs to dispatch now}
                            {--force : Run even if automation is off and ignore daily/monthly article caps}';

    protected $description = 'Dispatch AI blog generation jobs per service. Use --force for a mandatory run (ignores automation toggle and limits).';

    public function handle(): int
    {
        if (!$this->option('force') && !BlogAutomationSettings::enabled()) {
            BlogAutomationHealth::recordScheduleRun('Automation disabled — no jobs dispatched.');
            $this->info('AI blog automation disabled.');

            return self::SUCCESS;
        }

        $limit = max(0, (int) $this->option('limit'));
        if ($limit === 0) {
            BlogAutomationHealth::recordScheduleRun('Limit is 0 — no jobs dispatched.');
            $this->info('Limit is 0; nothing dispatched.');

            return self::SUCCESS;
        }

        $services = Service::with('category')->latest('id')->get();

        $servicesWithoutMonthlyArticle = $services->filter(function (Service $service) {
            return !Article::where('service_id', $service->id)
                ->where('generated_by_ai', true)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->exists();
        })->values();

        $recycle = $servicesWithoutMonthlyArticle->isEmpty();
        $servicesToProcess = $recycle ? $services->shuffle() : $servicesWithoutMonthlyArticle;

        if ($recycle) {
            $this->info('All services already have an AI article this month; dispatching recycle run (random services).');
        }

        $dispatched = 0;
        foreach ($servicesToProcess as $service) {
            if ($dispatched >= $limit) {
                break;
            }

            GenerateSeoArticleForServiceJob::dispatch(
                $service->id,
                (bool) $this->option('force'),
                $recycle
            );
            $dispatched++;
        }

        $message = $recycle
            ? "Dispatched {$dispatched} recycle job(s)."
            : "Dispatched {$dispatched} job(s) for services without a monthly AI article.";

        BlogAutomationHealth::recordScheduleRun($message);
        $this->info("Dispatched {$dispatched} AI SEO article job(s).");

        return self::SUCCESS;
    }
}
