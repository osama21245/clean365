<?php

namespace Modules\ServicemanModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\User;

/**
 * Seed dummy live GPS for supervisors (provider-admin) and servicemen across Saudi cities.
 *
 * php artisan db:seed --class="Modules\\ServicemanModule\\Database\\Seeders\\SaudiFieldAgentLocationsSeeder"
 */
class SaudiFieldAgentLocationsSeeder extends Seeder
{
    /**
     * Major KSA city centers (approx).
     *
     * @var list<array{name: string, lat: float, lng: float}>
     */
    private array $cities = [
        ['name' => 'Riyadh', 'lat' => 24.7136, 'lng' => 46.6753],
        ['name' => 'Jeddah', 'lat' => 21.4858, 'lng' => 39.1925],
        ['name' => 'Dammam', 'lat' => 26.4207, 'lng' => 50.0888],
        ['name' => 'Makkah', 'lat' => 21.3891, 'lng' => 39.8579],
        ['name' => 'Madinah', 'lat' => 24.5247, 'lng' => 39.5692],
        ['name' => 'Khobar', 'lat' => 26.2172, 'lng' => 50.1971],
        ['name' => 'Tabuk', 'lat' => 28.3838, 'lng' => 36.5550],
        ['name' => 'Abha', 'lat' => 18.2164, 'lng' => 42.5053],
        ['name' => 'Taif', 'lat' => 21.2703, 'lng' => 40.4158],
        ['name' => 'Hail', 'lat' => 27.5114, 'lng' => 41.7208],
        ['name' => 'Najran', 'lat' => 17.5656, 'lng' => 44.2289],
        ['name' => 'Jazan', 'lat' => 16.8892, 'lng' => 42.5706],
        ['name' => 'Buraidah', 'lat' => 26.3260, 'lng' => 43.9750],
        ['name' => 'Yanbu', 'lat' => 24.0895, 'lng' => 38.0618],
        ['name' => 'Khamis Mushait', 'lat' => 18.3000, 'lng' => 42.7333],
    ];

    public function run(): void
    {
        $agents = User::query()
            ->whereIn('user_type', ['provider-admin', 'provider-serviceman'])
            ->where('is_active', 1)
            ->orderBy('created_at')
            ->get();

        if ($agents->isEmpty()) {
            $this->command?->warn('SaudiFieldAgentLocationsSeeder: no active field agents found.');
            return;
        }

        $updatedUsers = 0;
        $updatedProviders = 0;

        foreach ($agents as $index => $user) {
            $city = $this->cities[$index % count($this->cities)];
            [$lat, $lng] = $this->jitter($city['lat'], $city['lng'], $index);

            $user->forceFill([
                'latitude' => (string) $lat,
                'longitude' => (string) $lng,
                'last_seen_at' => now()->subSeconds(random_int(0, 900)),
            ])->save();
            $updatedUsers++;

            if ($user->user_type === 'provider-admin') {
                $provider = Provider::query()->where('user_id', $user->id)->first();
                if ($provider) {
                    $coords = $provider->coordinates;
                    if (is_string($coords)) {
                        $coords = json_decode($coords, true);
                    }
                    $hasCoords = is_array($coords)
                        && (($coords['latitude'] ?? $coords['lat'] ?? null) !== null)
                        && (($coords['longitude'] ?? $coords['lng'] ?? null) !== null);

                    if (!$hasCoords) {
                        $provider->coordinates = [
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'city' => $city['name'],
                        ];
                        $provider->save();
                        $updatedProviders++;
                    }
                }
            }

            $this->command?->line(sprintf(
                '  %s %-18s → %s (%.5f, %.5f)',
                $user->user_type === 'provider-admin' ? 'supervisor' : 'serviceman',
                trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email,
                $city['name'],
                $lat,
                $lng
            ));
        }

        $this->command?->info("SaudiFieldAgentLocationsSeeder: updated {$updatedUsers} agents, {$updatedProviders} provider HQ coords.");
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function jitter(float $lat, float $lng, int $seed): array
    {
        // ~±2.5km jitter so agents are not stacked on the same pin.
        mt_srand($seed + 365);
        $dLat = (mt_rand(-250, 250) / 10000);
        $dLng = (mt_rand(-250, 250) / 10000);
        mt_srand();

        return [round($lat + $dLat, 6), round($lng + $dLng, 6)];
    }
}
