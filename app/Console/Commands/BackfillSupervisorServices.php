<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\ProviderManagement\Entities\Provider;

class BackfillSupervisorServices extends Command
{
    protected $signature = 'clean365:backfill-supervisor-services {--dry-run : List supervisors without changing data}';

    protected $description = 'Subscribe all supervisors to active services in their zone';

    public function handle(): int
    {
        if (!supervisorMode()) {
            $this->warn('Supervisor mode is disabled. Enable SUPERVISOR_MODE to run this command.');

            return self::FAILURE;
        }

        $providers = Provider::query()
            ->where('is_approved', 1)
            ->where('is_active', 1)
            ->whereNotNull('zone_id')
            ->get(['id', 'zone_id', 'company_name']);

        if ($providers->isEmpty()) {
            $this->info('No active supervisors found.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;

        if (!$dryRun) {
            $unsuspended = Provider::query()->where('is_suspended', 1)->update(['is_suspended' => 0]);
            if ($unsuspended > 0) {
                $this->line("Cleared cash-limit suspension for {$unsuspended} supervisor(s).");
            }
        }

        foreach ($providers as $provider) {
            $label = $provider->company_name ?: $provider->id;

            if ($dryRun) {
                $this->line("Would backfill services for: {$label} (zone: {$provider->zone_id})");
                $processed++;
                continue;
            }

            autoSubscribeSupervisorToZoneServices($provider->id, $provider->zone_id);
            $this->line("Backfilled services for: {$label}");
            $processed++;
        }

        $this->info(($dryRun ? 'Would process' : 'Processed') . " {$processed} supervisor(s).");

        return self::SUCCESS;
    }
}
