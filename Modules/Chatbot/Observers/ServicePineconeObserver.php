<?php

namespace Modules\Chatbot\Observers;

use Modules\Chatbot\Jobs\SyncPackageToPineconeJob;
use Modules\ServiceManagement\Entities\Service;

class ServicePineconeObserver
{
    public function saved(Service $service): void
    {
        $isActivePackage = (int) ($service->is_active ?? 0) === 1
            && (int) ($service->visits_count ?? 0) >= 1;

        SyncPackageToPineconeJob::dispatchFor((string) $service->id, delete: ! $isActivePackage);
    }

    public function deleted(Service $service): void
    {
        SyncPackageToPineconeJob::dispatchFor((string) $service->id, delete: true);
    }

    public function forceDeleted(Service $service): void
    {
        SyncPackageToPineconeJob::dispatchFor((string) $service->id, delete: true);
    }
}
