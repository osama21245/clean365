<?php

namespace Modules\Chatbot\Services\Pinecone;

use Illuminate\Support\Facades\Log;
use Modules\ServiceManagement\Entities\Service;
use Throwable;

class PackageCatalogSyncer
{
    public function __construct(
        protected PineconeService $pinecone,
        protected PackageCatalogRecordBuilder $builder,
    ) {}

    public function syncServiceById(string $serviceId): void
    {
        $service = Service::with(['category', 'subCategory'])
            ->where('id', $serviceId)
            ->first();

        if (! $service || ! (int) $service->is_active || (int) ($service->visits_count ?? 0) < 1) {
            $this->deleteServiceById($serviceId);

            return;
        }

        if (! $this->pinecone->isConfigured()) {
            return;
        }

        try {
            $this->pinecone->upsertRecord($this->builder->buildPackage($service));
        } catch (Throwable $e) {
            Log::warning('Pinecone package upsert failed', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleteServiceById(string $serviceId): void
    {
        if (! $this->pinecone->isConfigured()) {
            return;
        }

        try {
            $this->pinecone->deleteRecord('package_'.$serviceId);
        } catch (Throwable $e) {
            Log::warning('Pinecone package delete failed', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
