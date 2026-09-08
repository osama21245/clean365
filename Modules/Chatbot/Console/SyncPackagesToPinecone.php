<?php

namespace Modules\Chatbot\Console;

use Illuminate\Console\Command;
use Modules\Chatbot\Services\Pinecone\PackageCatalogSyncer;
use Modules\ServiceManagement\Entities\Service;

class SyncPackagesToPinecone extends Command
{
    protected $signature = 'pinecone:sync-packages
        {--sync : Run upserts synchronously}
        {--property= : Filter by property_id}
        {--category= : Filter by category_id}';

    protected $description = 'Sync Clean365 packages (services with visits_count) to Pinecone';

    public function handle(PackageCatalogSyncer $syncer): int
    {
        $query = Service::query()
            ->where('is_active', 1)
            ->where('visits_count', '>=', 1)
            ->when($this->option('property'), fn ($q, $id) => $q->where('property_id', $id))
            ->when($this->option('category'), fn ($q, $id) => $q->where('category_id', $id));

        $count = 0;
        $query->orderBy('id')->chunkById(50, function ($services) use ($syncer, &$count) {
            foreach ($services as $service) {
                $syncer->syncServiceById((string) $service->id);
                $count++;
                $this->line("Synced package {$service->id}");
            }
        });

        $this->info("Done. Synced {$count} packages.");

        return self::SUCCESS;
    }
}
