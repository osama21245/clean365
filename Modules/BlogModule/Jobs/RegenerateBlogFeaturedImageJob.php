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
use Modules\BlogModule\Support\FeaturedImageHelper;
use Modules\ServiceManagement\Entities\Service;

class RegenerateBlogFeaturedImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public string $articleId,
    ) {
    }

    public function backoff(): array
    {
        return [30, 90, 180];
    }

    public function handle(GeminiBlogGenerator $generator): void
    {
        $traceId = (string) Str::uuid();

        $article = Article::with(['service.category', 'service.translations', 'service.category.translations'])
            ->find($this->articleId);

        if (!$article) {
            Log::warning('RegenerateBlogFeaturedImageJob: article not found', [
                'article_id' => $this->articleId,
                'trace_id' => $traceId,
            ]);

            return;
        }

        $service = $article->service;
        if (!$service instanceof Service) {
            BlogAutomationHealth::recordError("Article {$article->slug} has no linked service for image generation.");
            Log::warning('RegenerateBlogFeaturedImageJob: article has no service', [
                'article_id' => $article->id,
                'trace_id' => $traceId,
            ]);

            return;
        }

        $scenePrompt = $generator->buildRepairFeaturedImageScenePrompt($service, $article);
        $tmp = $generator->createImageFromPrompt($scenePrompt, $traceId);
        $saved = false;

        if ($tmp) {
            $saved = FeaturedImageHelper::saveTempFile($article, $tmp, deletePrevious: true);
            if (is_string($tmp) && is_file($tmp)) {
                @unlink($tmp);
            }
        }

        if (!$saved) {
            $saved = FeaturedImageHelper::copyFromService($article, $service);
        }

        $article->refresh();

        if (!FeaturedImageHelper::hasUsableImage($article)) {
            $err = $generator->getLastImageGenerationError()
                ?: 'Image generation and service fallback both failed';
            BlogAutomationHealth::recordError("{$article->slug}: {$err}");

            Log::warning('RegenerateBlogFeaturedImageJob: no usable image', [
                'article_id' => $article->id,
                'slug' => $article->slug,
                'error' => $err,
                'trace_id' => $traceId,
            ]);

            return;
        }

        BlogAutomationHealth::recordSuccess($article->slug);

        Log::info('RegenerateBlogFeaturedImageJob: completed', [
            'article_id' => $article->id,
            'slug' => $article->slug,
            'new_image' => $article->featured_image,
            'url' => $article->featured_image_full_path,
            'trace_id' => $traceId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        BlogAutomationHealth::recordError($exception->getMessage());
    }
}
