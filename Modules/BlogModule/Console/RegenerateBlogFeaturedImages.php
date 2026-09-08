<?php

namespace Modules\BlogModule\Console;

use Illuminate\Console\Command;
use Modules\BlogModule\Entities\Article;
use Modules\BlogModule\Jobs\RegenerateBlogFeaturedImageJob;
use Modules\BlogModule\Support\FeaturedImageHelper;

class RegenerateBlogFeaturedImages extends Command
{
    protected $signature = 'app:regenerate-blog-images
                            {--limit=0 : Max articles to process (0 = all matched)}
                            {--slug= : Regenerate one article by slug}
                            {--ai-only : Only AI-generated articles}
                            {--sync : Run inline instead of queue}
                            {--force : Regenerate all matched articles (default: only missing images)}
                            {--missing-only : Only articles without a usable image file (default behavior)}';

    protected $description = 'Regenerate blog featured images (default: articles currently Missing an image).';

    public function handle(): int
    {
        $query = Article::query()
            ->with('storage_featured_image')
            ->whereNotNull('service_id')
            ->orderByDesc('published_at');

        if ($this->option('ai-only')) {
            $query->where('generated_by_ai', true);
        }

        if ($slug = trim((string) $this->option('slug'))) {
            $query->where('slug', $slug);
        }

        $articles = $query->get();

        if (!$this->option('force') && !$slug) {
            $articles = $articles->filter(fn (Article $a) => !FeaturedImageHelper::hasUsableImage($a))->values();
        }

        $limit = max(0, (int) $this->option('limit'));
        if ($limit > 0) {
            $articles = $articles->take($limit)->values();
        }

        if ($articles->isEmpty()) {
            $this->info('No matching articles found (none missing images, or filters excluded all).');

            return self::SUCCESS;
        }

        $this->info("Processing {$articles->count()} article(s)...");

        $sync = (bool) $this->option('sync');
        $ok = 0;
        $failed = 0;

        foreach ($articles as $article) {
            $this->line("- {$article->slug}");

            if ($sync) {
                RegenerateBlogFeaturedImageJob::dispatchSync($article->id);
                $article->refresh();
                if (FeaturedImageHelper::hasUsableImage($article)) {
                    $this->info("  OK → {$article->featured_image_full_path}");
                    $ok++;
                } else {
                    $this->error('  FAILED — still Missing (check laravel.log / Blog automation last error)');
                    $failed++;
                }
            } else {
                RegenerateBlogFeaturedImageJob::dispatch($article->id);
                $ok++;
            }
        }

        if ($sync) {
            $this->info("Done: {$ok} ok, {$failed} failed.");
        } else {
            $this->info("Queued {$ok} job(s). Ensure clean365-queue.service is running.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
