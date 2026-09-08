<?php

namespace Modules\Chatbot\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Chatbot\Services\Pinecone\PackageCatalogSyncer;

class SyncPackageToPineconeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $serviceId,
        public bool $delete = false,
    ) {}

    public static function dispatchFor(string $serviceId, bool $delete = false, int $delaySeconds = 5): void
    {
        $job = new self($serviceId, $delete);

        if (config('ai-automation.pinecone_dispatch_sync') || config('chatbot.pinecone_dispatch_sync')) {
            dispatch_sync($job);

            return;
        }

        if ($delete) {
            dispatch($job)->afterResponse();

            return;
        }

        dispatch($job)->delay(now()->addSeconds(max(0, $delaySeconds)));
    }

    public function handle(PackageCatalogSyncer $syncer): void
    {
        if ($this->delete) {
            $syncer->deleteServiceById($this->serviceId);

            return;
        }

        $syncer->syncServiceById($this->serviceId);
    }
}
