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
use Modules\ServiceManagement\Entities\Service;

class RegenerateServiceCatalogImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public string $serviceId,
    ) {
    }

    public function handle(GeminiBlogGenerator $generator): void
    {
        $traceId = (string) Str::uuid();
        $service = Service::with(['category', 'translations', 'category.translations'])->find($this->serviceId);

        if (!$service) {
            Log::warning('RegenerateServiceCatalogImageJob: service not found', [
                'service_id' => $this->serviceId,
                'trace_id' => $traceId,
            ]);

            return;
        }

        $tmp = $generator->createServiceCatalogImage($service, $traceId);
        if (!$tmp) {
            Log::warning('RegenerateServiceCatalogImageJob: generation failed', [
                'service_id' => $service->id,
                'error' => $generator->getLastImageGenerationError(),
                'trace_id' => $traceId,
            ]);

            return;
        }

        $saved = CatalogImageHelper::applyToService($service, $tmp, replaceBoth: true);
        if (is_file($tmp)) {
            @unlink($tmp);
        }

        Log::info('RegenerateServiceCatalogImageJob: completed', [
            'service_id' => $service->id,
            'saved' => $saved,
            'trace_id' => $traceId,
        ]);
    }
}
