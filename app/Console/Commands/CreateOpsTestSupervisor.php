<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;

class CreateOpsTestSupervisor extends Command
{
    protected $signature = 'clean365:create-ops-test-supervisor
                            {--email=ops.test@clean365.sa : Supervisor login email}
                            {--phone=+966500000999 : Supervisor phone}
                            {--password=Clean365Ops@2026 : Plain password}';

    protected $description = 'Create or reset a temporary supervisor (provider-admin) for Ops API testing';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $phone = (string) $this->option('phone');
        $password = (string) $this->option('password');

        $zoneId = Zone::query()->value('id');
        if (!$zoneId) {
            $this->error('No zone found. Create a zone first.');

            return self::FAILURE;
        }

        $user = User::query()
            ->where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        if (!$user) {
            $user = new User();
            $user->id = (string) Str::uuid();
        }

        $user->first_name = 'Ops';
        $user->last_name = 'Tester';
        $user->email = $email;
        $user->phone = $phone;
        $user->password = bcrypt($password);
        $user->user_type = 'provider-admin';
        $user->is_active = 1;
        $user->is_email_verified = 1;
        $user->is_phone_verified = 1;
        $user->save();

        $provider = Provider::query()->where('user_id', $user->id)->first();
        if (!$provider) {
            $provider = new Provider();
            $provider->id = (string) Str::uuid();
            $provider->user_id = $user->id;
        }

        $provider->company_name = 'Ops Test Supervisor';
        $provider->company_phone = $phone;
        $provider->company_email = $email;
        $provider->company_address = 'Riyadh';
        $provider->contact_person_name = 'Ops Tester';
        $provider->contact_person_phone = $phone;
        $provider->contact_person_email = $email;
        $provider->zone_id = $zoneId;
        $provider->is_active = 1;
        $provider->is_approved = 1;
        $provider->is_suspended = 0;
        $provider->save();

        if (function_exists('supervisorMode') && supervisorMode() && function_exists('autoSubscribeSupervisorToZoneServices')) {
            autoSubscribeSupervisorToZoneServices($provider->id, $provider->zone_id);
        }

        $this->info('Ops test supervisor ready.');
        $this->line('email: ' . $email);
        $this->line('phone: ' . $phone);
        $this->line('password: ' . $password);
        $this->line('user_id: ' . $user->id);
        $this->line('provider_id: ' . $provider->id);
        $this->line('zone_id: ' . $zoneId);

        return self::SUCCESS;
    }
}
