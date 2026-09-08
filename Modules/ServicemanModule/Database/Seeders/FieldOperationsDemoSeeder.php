<?php

namespace Modules\ServicemanModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\ServicemanModule\Entities\SupervisorTeamServiceman;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;

class FieldOperationsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::query()->where('is_active', 1)->where('is_approved', 1)->first();
        if (!$provider) {
            $this->command?->warn('FieldOperationsDemoSeeder: no active approved provider found. Skipping.');
            return;
        }

        $zone = Zone::query()->first();
        $customer = User::query()->where('user_type', 'customer')->where('is_active', 1)->first();

        $servicemanProfiles = [
            ['first_name' => 'Ahmed', 'last_name' => 'Ali', 'email' => 'demo-serviceman-1@clean365.test', 'phone' => '+966500000001'],
            ['first_name' => 'Omar', 'last_name' => 'Hassan', 'email' => 'demo-serviceman-2@clean365.test', 'phone' => '+966500000002'],
            ['first_name' => 'Khalid', 'last_name' => 'Saeed', 'email' => 'demo-serviceman-3@clean365.test', 'phone' => '+966500000003'],
            ['first_name' => 'Youssef', 'last_name' => 'Nasser', 'email' => 'demo-serviceman-4@clean365.test', 'phone' => '+966500000004']
        ];

        $servicemanIds = [];

        foreach ($servicemanProfiles as $profile) {
            $user = User::query()->where('email', $profile['email'])->first();

            if (!$user) {
                $user = User::create([
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'email' => $profile['email'],
                    'phone' => $profile['phone'],
                    'password' => Hash::make('12345678'),
                    'user_type' => 'provider-serviceman',
                    'is_active' => 1,
                    'identification_type' => 'nid',
                    'identification_number' => 'DEMO-' . strtoupper(Str::random(6)),
                    'identification_image' => [['image' => 'demo-id.png', 'storage' => 'public']],
                    'profile_image' => 'demo-profile.png'
                ]);
            }

            $serviceman = Serviceman::query()->where('user_id', $user->id)->first();
            if (!$serviceman) {
                $serviceman = Serviceman::create([
                    'user_id' => $user->id,
                    'provider_id' => null
                ]);
            }

            $servicemanIds[] = $serviceman->id;
        }

        $team = SupervisorTeam::query()
            ->where('provider_id', $provider->id)
            ->where('name', 'Demo Field Team')
            ->first();

        if (!$team) {
            $team = SupervisorTeam::create([
                'provider_id' => $provider->id,
                'name' => 'Demo Field Team',
                'is_active' => true
            ]);
        }

        foreach ($servicemanIds as $servicemanId) {
            SupervisorTeamServiceman::query()->firstOrCreate([
                'team_id' => $team->id,
                'serviceman_id' => $servicemanId
            ]);
        }

        Serviceman::query()->whereIn('id', $servicemanIds)->update(['provider_id' => $provider->id]);

        $bookingStatuses = ['accepted', 'ongoing'];
        foreach ($bookingStatuses as $index => $bookingStatus) {
            $existing = Booking::query()
                ->where('provider_id', $provider->id)
                ->where('team_id', $team->id)
                ->where('booking_status', $bookingStatus)
                ->first();

            if ($existing) {
                continue;
            }

            Booking::create([
                'customer_id' => $customer?->id,
                'provider_id' => $provider->id,
                'zone_id' => $zone?->id ?? $provider->zone_id,
                'booking_status' => $bookingStatus,
                'is_paid' => 1,
                'payment_method' => 'cash',
                'total_booking_amount' => 250,
                'total_tax_amount' => 0,
                'total_discount_amount' => 0,
                'service_schedule' => now()->addDays($index + 1),
                'team_id' => $team->id
            ]);
        }

        $this->command?->info('FieldOperationsDemoSeeder: created/updated 4 demo servicemen, 1 team, and task-ready bookings.');
        $this->command?->info('Demo serviceman login: demo-serviceman-1@clean365.test / 12345678');
    }
}
