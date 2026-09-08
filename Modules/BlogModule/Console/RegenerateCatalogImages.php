<?php

namespace Modules\BlogModule\Console;

use Illuminate\Console\Command;
use Modules\BlogModule\Jobs\RegenerateCategoryCatalogImageJob;
use Modules\BlogModule\Jobs\RegenerateServiceCatalogImageJob;
use Modules\BlogModule\Services\Seo\GeminiBlogGenerator;
use Modules\BlogModule\Support\CatalogImageHelper;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class RegenerateCatalogImages extends Command
{
    protected $signature = 'app:regenerate-catalog-images
                            {--type=all : all|service|category}
                            {--id= : Only one service or category UUID}
                            {--limit=0 : Max items (0 = no limit)}
                            {--missing-only : Only items without an image}
                            {--sync : Run inline instead of queue}
                            {--force : Regenerate even when an image exists}';

    protected $description = 'Generate Gemini catalog images for services and/or categories (no text overlays, Clean365 brand colors).';

    public function handle(GeminiBlogGenerator $generator): int
    {
        $type = strtolower((string) ($this->option('type') ?: 'all'));
        if (!in_array($type, ['all', 'service', 'category'], true)) {
            $this->error('--type must be all, service, or category');

            return self::FAILURE;
        }

        $ok = 0;
        $fail = 0;

        if ($type === 'all' || $type === 'service') {
            [$o, $f] = $this->processServices($generator, $type === 'service');
            $ok += $o;
            $fail += $f;
        }

        if ($type === 'all' || $type === 'category') {
            [$o, $f] = $this->processCategories($generator, $type === 'category');
            $ok += $o;
            $fail += $f;
        }

        $this->info("Done. OK={$ok} FAILED={$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function processServices(GeminiBlogGenerator $generator, bool $idIsServiceOnly): array
    {
        $query = Service::query()->with(['category', 'translations', 'category.translations']);
        $id = trim((string) $this->option('id'));

        if ($id !== '') {
            if ($idIsServiceOnly || Service::whereKey($id)->exists()) {
                $query->whereKey($id);
            } else {
                return [0, 0];
            }
        }

        if ($this->option('missing-only') && !$this->option('force')) {
            $query->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('thumbnail')->orWhere('thumbnail', '');
                })->orWhere(function ($inner) {
                    $inner->whereNull('cover_image')->orWhere('cover_image', '');
                });
            });
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $services = $query->latest()->get();
        $this->info('Services to process: '.$services->count());

        $ok = 0;
        $fail = 0;

        foreach ($services as $service) {
            $this->line("Service: {$service->id} — {$service->name}");

            if (!$this->option('sync')) {
                RegenerateServiceCatalogImageJob::dispatch($service->id);
                $this->comment('  queued');
                $ok++;

                continue;
            }

            $tmp = $generator->createServiceCatalogImage($service, 'cli-service-'.$service->id);
            if (!$tmp) {
                $this->error('  FAILED — '.($generator->getLastImageGenerationError() ?: 'unknown'));
                $fail++;

                continue;
            }

            $saved = CatalogImageHelper::applyToService($service, $tmp, replaceBoth: true);
            if (is_file($tmp)) {
                @unlink($tmp);
            }

            if ($saved) {
                $this->info('  OK');
                $ok++;
            } else {
                $this->error('  FAILED — save: '.(CatalogImageHelper::getLastError() ?: 'unknown'));
                $fail++;
            }
        }

        return [$ok, $fail];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function processCategories(GeminiBlogGenerator $generator, bool $idIsCategoryOnly): array
    {
        $query = Category::query()->with('translations');
        $id = trim((string) $this->option('id'));

        if ($id !== '') {
            if ($idIsCategoryOnly || (!Service::whereKey($id)->exists() && Category::whereKey($id)->exists())) {
                $query->whereKey($id);
            } else {
                return [0, 0];
            }
        }

        if ($this->option('missing-only') && !$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            });
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $categories = $query->latest()->get();
        $this->info('Categories to process: '.$categories->count());

        $ok = 0;
        $fail = 0;

        foreach ($categories as $category) {
            $this->line("Category: {$category->id} — {$category->name}");

            if (!$this->option('sync')) {
                RegenerateCategoryCatalogImageJob::dispatch($category->id);
                $this->comment('  queued');
                $ok++;

                continue;
            }

            $tmp = $generator->createCategoryCatalogImage($category, 'cli-category-'.$category->id);
            if (!$tmp) {
                $this->error('  FAILED — '.($generator->getLastImageGenerationError() ?: 'unknown'));
                $fail++;

                continue;
            }

            $saved = CatalogImageHelper::applyToCategory($category, $tmp);
            if (is_file($tmp)) {
                @unlink($tmp);
            }

            if ($saved) {
                $this->info('  OK');
                $ok++;
            } else {
                $this->error('  FAILED — save: '.(CatalogImageHelper::getLastError() ?: 'unknown'));
                $fail++;
            }
        }

        return [$ok, $fail];
    }
}
