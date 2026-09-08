<?php

namespace Modules\BlogModule\Console;

use Illuminate\Console\Command;
use Modules\BlogModule\Entities\Article;
use Modules\BlogModule\Services\Gemini\GeminiVertexClient;
use Modules\BlogModule\Services\Seo\GeminiBlogGenerator;
use Modules\BlogModule\Support\FeaturedImageHelper;

class TestBlogImageGeneration extends Command
{
    protected $signature = 'app:test-blog-image
                            {--slug= : Optional article slug to attach the test image to}
                            {--save : Save the generated image onto the article}';

    protected $description = 'Diagnose Gemini/OpenAI/Pollinations blog image generation and print the exact error.';

    public function handle(GeminiBlogGenerator $generator, GeminiVertexClient $client): int
    {
        $this->info('Image generation diagnostics');
        $this->line('provider: '.config('services.image_generation.provider'));
        $this->line('gemini_only: '.(config('services.image_generation.gemini_only') ? 'yes' : 'no'));
        $imageKey = $client->resolveImageApiKey();
        $imageKeyKind = $imageKey === '' ? 'none'
            : (str_starts_with($imageKey, 'AIza') ? 'AIza (AI Studio)'
                : (str_starts_with($imageKey, 'AQ.') ? 'AQ (Vertex/Express)' : 'other'));

        $this->line('gemini text key: '.($client->resolveTextApiKey() !== '' ? 'yes' : 'NO'));
        $this->line('gemini image key: '.($imageKey !== '' ? 'yes' : 'NO')." [{$imageKeyKind}]");
        $this->line('openai key: '.((string) config('services.image_generation.openai.api_key') !== '' ? 'yes' : 'NO'));
        $this->line('image model: '.config('services.gemini.image_model'));
        $this->line('image endpoint: '.config('services.gemini.image_endpoint_style'));
        $this->line('image base url: '.$client->imageBaseUrl());
        $this->newLine();

        $article = null;
        $slug = trim((string) $this->option('slug'));
        if ($slug !== '') {
            $article = Article::with('service')->where('slug', $slug)->first();
            if (!$article) {
                $this->error("Article not found: {$slug}");

                return self::FAILURE;
            }
        } else {
            $article = Article::with('service')
                ->whereNotNull('service_id')
                ->latest('published_at')
                ->first();
        }

        if (!$article?->service) {
            $this->error('No article with a service found to build a prompt.');

            return self::FAILURE;
        }

        $this->line("Using article: {$article->slug}");
        $prompt = $generator->buildRepairFeaturedImageScenePrompt($article->service, $article);
        $this->line('Prompt length: '.strlen($prompt));

        $tmp = $generator->createImageFromPrompt($prompt, 'test-blog-image');

        if (!$tmp) {
            $this->error('Generation FAILED: '.($generator->getLastImageGenerationError() ?: 'unknown error'));

            return self::FAILURE;
        }

        $size = is_file($tmp) ? filesize($tmp) : 0;
        $this->info("Generation OK — temp file {$tmp} ({$size} bytes)");

        if ($this->option('save')) {
            $saved = FeaturedImageHelper::saveTempFile($article, $tmp, deletePrevious: true);
            @unlink($tmp);
            $article->refresh();
            if ($saved && FeaturedImageHelper::hasUsableImage($article)) {
                $this->info('Saved: '.$article->featured_image_full_path);
            } else {
                $this->error('Generated OK but save to storage FAILED');

                return self::FAILURE;
            }
        } else {
            @unlink($tmp);
            $this->comment('Not saved (pass --save to attach to the article).');
        }

        return self::SUCCESS;
    }
}
