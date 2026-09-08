<?php

namespace Modules\BlogModule\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\BlogModule\Services\Seo\GeminiBlogGenerator;
use Modules\BlogModule\Support\CatalogImageHelper;
use Modules\CategoryManagement\Entities\Category;

class RegenerateCategoryCatalogImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public string $categoryId,
    ) {
    }

    public function handle(GeminiBlogGenerator $generator): void
    {
        $traceId = (string) Str::uuid();
        $category = Category::with('translations')->find($this->categoryId);

        if (!$category) {
            Log::warning('RegenerateCategoryCatalogImageJob: category not found', [
                'category_id' => $this->categoryId,
                'trace_id' => $traceId,
            ]);

            return;
        }

        $tmp = $generator->createCategoryCatalogImage($category, $traceId);
        if (!$tmp) {
            Log::warning('RegenerateCategoryCatalogImageJob: generation failed', [
                'category_id' => $category->id,
                'error' => $generator->getLastImageGenerationError(),
                'trace_id' => $traceId,
            ]);

            return;
        }

        $saved = CatalogImageHelper::applyToCategory($category, $tmp);
        if (is_file($tmp)) {
            @unlink($tmp);
        }

        Log::info('RegenerateCategoryCatalogImageJob: completed', [
            'category_id' => $category->id,
            'saved' => $saved,
            'trace_id' => $traceId,
        ]);
    }
}
