<?php

namespace Modules\BlogModule\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\BlogModule\Entities\Article;
use Modules\BlogModule\Services\Seo\GeminiBlogGenerator;
use Modules\BlogModule\Support\BlogAutomationHealth;
use Modules\BlogModule\Support\BlogAutomationSettings;
use Modules\BlogModule\Support\FeaturedImageHelper;
use Modules\ServiceManagement\Entities\Service;

class GenerateSeoArticleForServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 5;

    public function __construct(
        public string $serviceId,
        public bool $force = false,
        public bool $recycle = false,
    ) {
    }

    public function backoff(): array
    {
        return [30, 90, 180, 300];
    }

    public function handle(GeminiBlogGenerator $generator): void
    {
        $traceId = (string) Str::uuid();

        if (!$this->force && !BlogAutomationSettings::enabled()) {
            Log::info('GenerateSeoArticleForServiceJob: aborted — automation off', [
                'service_id' => $this->serviceId,
                'trace_id' => $traceId,
            ]);

            return;
        }

        if (!$this->force && $this->limitReached()) {
            Log::info('GenerateSeoArticleForServiceJob: aborted — daily/monthly limit', [
                'service_id' => $this->serviceId,
                'trace_id' => $traceId,
            ]);

            return;
        }

        $service = Service::with(['category', 'translations', 'category.translations'])->find($this->serviceId);

        if (!$service) {
            Log::warning('GenerateSeoArticleForServiceJob: service not found', [
                'service_id' => $this->serviceId,
                'trace_id' => $traceId,
            ]);

            return;
        }

        if (!$this->force && !$this->recycle && $this->serviceAlreadyHasAiArticleThisMonth($service->id)) {
            Log::info('GenerateSeoArticleForServiceJob: skipped — AI article exists this month', [
                'service_id' => $service->id,
                'trace_id' => $traceId,
            ]);

            return;
        }

        $generated = $generator->generate($service, BlogAutomationSettings::prompt(), $traceId);
        $bodies = $this->appendServiceLinksToBodies(
            $generated['body_ar'] ?? '',
            $generated['body_en'] ?? '',
            $service->id
        );

        $slugSource = trim((string) ($generated['slug'] ?? ''));
        if ($slugSource === '') {
            $slugSource = (string) ($generated['title_ar'] ?? $generated['title_en'] ?? 'article');
        }

        $article = Article::create([
            'title' => [
                'ar' => $generated['title_ar'] ?? '',
                'en' => $generated['title_en'] ?? '',
            ],
            'slug' => Article::generateUniqueSlug($slugSource),
            'excerpt' => [
                'ar' => strip_tags((string) ($generated['excerpt_ar'] ?? '')),
                'en' => strip_tags((string) ($generated['excerpt_en'] ?? '')),
            ],
            'body' => [
                'ar' => $bodies['ar'],
                'en' => $bodies['en'],
            ],
            'meta_title' => [
                'ar' => $generated['meta_title_ar'] ?? ($generated['title_ar'] ?? ''),
                'en' => $generated['meta_title_en'] ?? ($generated['title_en'] ?? ''),
            ],
            'meta_description' => [
                'ar' => Str::limit(strip_tags($bodies['ar']), 300, ''),
                'en' => Str::limit(strip_tags($bodies['en']), 300, ''),
            ],
            'meta_keywords' => [
                'ar' => $generated['keywords_ar'] ?? [],
                'en' => $generated['keywords_en'] ?? [],
            ],
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'generated_by_ai' => true,
            'is_active' => true,
            'published_at' => now(),
        ]);

        $imagePrompt = (string) ($generated['image_prompt'] ?? '');
        $savedImage = false;

        if ($imagePrompt !== '') {
            $tmp = $generator->createImageFromPrompt($imagePrompt, $traceId);
            if ($tmp) {
                $savedImage = FeaturedImageHelper::saveTempFile($article, $tmp);
                if (is_string($tmp) && is_file($tmp)) {
                    @unlink($tmp);
                }
            }
        }

        if (!$savedImage) {
            $repairPrompt = $generator->buildRepairFeaturedImageScenePrompt($service, $article);
            $tmp = $generator->createImageFromPrompt($repairPrompt, $traceId);
            if ($tmp) {
                $savedImage = FeaturedImageHelper::saveTempFile($article, $tmp);
                if (is_string($tmp) && is_file($tmp)) {
                    @unlink($tmp);
                }
            }
        }

        if (!$savedImage && !$article->featured_image) {
            FeaturedImageHelper::copyFromService($article, $service);
        }

        $article->refresh();
        if (!FeaturedImageHelper::hasUsableImage($article)) {
            $imgErr = $generator->getLastImageGenerationError()
                ?: 'Image generation and service fallback both failed';
            BlogAutomationHealth::recordError("{$article->slug}: {$imgErr}");
            Log::warning('GenerateSeoArticleForServiceJob: article saved without usable featured image', [
                'article_id' => $article->id,
                'slug' => $article->slug,
                'error' => $imgErr,
                'trace_id' => $traceId,
            ]);
        } else {
            BlogAutomationHealth::recordSuccess($article->slug);
        }

        Log::info('GenerateSeoArticleForServiceJob: completed', [
            'article_id' => $article->id,
            'service_id' => $service->id,
            'slug' => $article->slug,
            'has_featured_image' => (bool) $article->fresh()->featured_image,
            'trace_id' => $traceId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        BlogAutomationHealth::recordError($exception->getMessage());

        Log::error('GenerateSeoArticleForServiceJob: failed', [
            'service_id' => $this->serviceId,
            'error' => $exception->getMessage(),
        ]);
    }

    private function serviceAlreadyHasAiArticleThisMonth(string $serviceId): bool
    {
        return Article::where('service_id', $serviceId)
            ->where('generated_by_ai', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->exists();
    }

    private function limitReached(): bool
    {
        $todayCount = Article::where('generated_by_ai', true)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount >= BlogAutomationSettings::dailyLimit()) {
            return true;
        }

        $monthCount = Article::where('generated_by_ai', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return $monthCount >= BlogAutomationSettings::monthlyLimit();
    }

    /**
     * @return array{ar:string,en:string}
     */
    private function appendServiceLinksToBodies(string $bodyAr, string $bodyEn, string $serviceId): array
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', env('FRONTEND_URL', config('app.url'))), '/');
        $arLink = "{$frontendUrl}/ar/services/{$serviceId}";
        $enLink = "{$frontendUrl}/en/services/{$serviceId}";

        return [
            'ar' => trim($bodyAr) . "\n\n" . $this->servicePageCtaHtml($arLink, 'الانتقال إلى الخدمة', 'rtl'),
            'en' => trim($bodyEn) . "\n\n" . $this->servicePageCtaHtml($enLink, 'View service', 'ltr'),
        ];
    }

    private function servicePageCtaHtml(string $href, string $label, string $dir): string
    {
        $hex = (string) config('services.image_generation.brand_primary_hex', '#008080');
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            $hex = '#008080';
        }

        $style = 'display:inline-block;padding:0.75rem 1.5rem;background-color:' . $hex
            . ';color:#ffffff;text-decoration:none;border-radius:0.5rem;font-weight:600;'
            . 'font-size:1rem;line-height:1.25;text-align:center;';

        $safeHref = e($href);
        $safeLabel = e($label);
        $safeDir = $dir === 'ltr' ? 'ltr' : 'rtl';

        return '<p style="margin-top:1.5rem;text-align:center;" dir="' . $safeDir . '">'
            . '<a href="' . $safeHref . '" target="_blank" rel="noopener" style="' . $style . '">' . $safeLabel . '</a>'
            . '</p>';
    }
}
