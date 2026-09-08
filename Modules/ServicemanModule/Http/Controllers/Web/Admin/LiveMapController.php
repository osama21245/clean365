<?php

namespace Modules\ServicemanModule\Http\Controllers\Web\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;

class LiveMapController extends Controller
{
    public function index(): View
    {
        $agents = $this->baseAgentQuery()
            ->with([
                'provider:id,user_id,coordinates',
                'serviceman:id,user_id,provider_id',
                'serviceman.provider:id,coordinates',
            ])
            ->get(['id', 'user_type', 'latitude', 'longitude', 'last_seen_at']);

        $agents = $agents->filter(fn (User $u) => $this->resolveCoords($u) !== null)->values();
        $busyUserIds = $this->busyUserIds($agents->pluck('id'));
        $onlineIds = $agents->filter(fn (User $u) => $u->isOnline())->pluck('id');

        $stats = [
            'online_available' => $onlineIds->diff($busyUserIds)->count(),
            'online_busy' => $onlineIds->intersect($busyUserIds)->count(),
            'offline' => $agents->count() - $onlineIds->count(),
            'total' => $agents->count(),
        ];

        $zones = Zone::ofStatus(1)->latest()->get(['id', 'name']);
        $googleMapsApiKey = business_config('google_map', 'third_party')?->live_values['map_api_key_client'] ?? '';

        return view('servicemanmodule::Admin.LiveMap.index', compact('zones', 'stats', 'googleMapsApiKey'));
    }

    public function api(Request $request): JsonResponse
    {
        $query = $this->baseAgentQuery()
            ->select([
                'id', 'first_name', 'last_name', 'phone', 'user_type',
                'latitude', 'longitude', 'last_seen_at', 'profile_image', 'is_active',
            ])
            ->with([
                'provider:id,user_id,zone_id,company_name,coordinates',
                'provider.zone:id,name',
                'serviceman:id,user_id,provider_id',
                'serviceman.provider:id,zone_id,company_name,coordinates',
                'serviceman.provider.zone:id,name',
            ]);

        if ($request->filled('role')) {
            if ($request->role === 'supervisor') {
                $query->where('user_type', 'provider-admin');
            } elseif ($request->role === 'serviceman') {
                $query->where('user_type', 'provider-serviceman');
            }
        }

        if ($request->filled('zone_id')) {
            $zoneId = $request->zone_id;
            $query->where(function ($q) use ($zoneId) {
                $q->where(function ($supervisor) use ($zoneId) {
                    $supervisor->where('user_type', 'provider-admin')
                        ->whereHas('provider', fn ($p) => $p->where('zone_id', $zoneId));
                })->orWhere(function ($serviceman) use ($zoneId) {
                    $serviceman->where('user_type', 'provider-serviceman')
                        ->whereHas('serviceman.provider', fn ($p) => $p->where('zone_id', $zoneId));
                });
            });
        }

        if ($request->filled('status')) {
            if (in_array($request->status, ['online', 'online_available', 'online_busy'], true)) {
                $query->isOnline();
            } elseif ($request->status === 'offline') {
                $query->where(function ($q) {
                    $q->whereNull('last_seen_at')
                        ->orWhere('last_seen_at', '<', User::onlineSince());
                });
            }
        }

        $agents = $query->get();
        $busyUserIds = $this->busyUserIds($agents->pluck('id'))->all();
        $statusFilter = $request->status;

        $data = $agents->map(function (User $agent) use ($busyUserIds) {
            $coords = $this->resolveCoords($agent);
            if (!$coords) {
                return null;
            }

            $isOnline = $agent->isOnline();
            $isBusy = in_array($agent->id, $busyUserIds, true);

            if ($isOnline && $isBusy) {
                $status = 'online_busy';
            } elseif ($isOnline) {
                $status = 'online_available';
            } else {
                $status = 'offline';
            }

            $providerId = null;
            $servicemanId = null;
            $profileUrl = null;

            $zoneName = '-';
            $companyName = '-';
            if ($agent->user_type === 'provider-admin' && $agent->provider) {
                $zoneName = $agent->provider->zone?->name ?? '-';
                $companyName = $agent->provider->company_name ?? '-';
                $providerId = $agent->provider->id;
                $profileUrl = route('admin.provider.details', ['id' => $providerId, 'web_page' => 'overview']);
            } elseif ($agent->user_type === 'provider-serviceman' && $agent->serviceman) {
                $zoneName = $agent->serviceman->provider?->zone?->name ?? '-';
                $companyName = $agent->serviceman->provider?->company_name ?? '-';
                $servicemanId = $agent->serviceman->id;
                $profileUrl = route('admin.serviceman.show', ['id' => $servicemanId]);
            }

            return [
                'id' => $agent->id,
                'name' => trim(($agent->first_name ?? '') . ' ' . ($agent->last_name ?? '')) ?: translate('Unknown'),
                'phone' => $agent->phone,
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'location_source' => $coords['source'],
                'role' => $agent->user_type === 'provider-admin' ? 'supervisor' : 'serviceman',
                'role_label' => $agent->user_type === 'provider-admin'
                    ? translate('Supervisor')
                    : translate('Serviceman'),
                'provider_id' => $providerId,
                'serviceman_id' => $servicemanId,
                'profile_url' => $profileUrl,
                'zone' => $zoneName,
                'company' => $companyName,
                'status' => $status,
                'last_seen_at' => $agent->last_seen_at
                    ? $agent->last_seen_at->diffForHumans()
                    : translate('never'),
                'avatar' => $agent->profile_image_full_path,
            ];
        })->filter()->values();

        if (in_array($statusFilter, ['online_available', 'online_busy'], true)) {
            $data = $data->where('status', $statusFilter)->values();
        }

        $stats = [
            'online_available' => $data->where('status', 'online_available')->count(),
            'online_busy' => $data->where('status', 'online_busy')->count(),
            'offline' => $data->where('status', 'offline')->count(),
            'total' => $data->count(),
        ];

        $pendingBookings = $this->pendingBookingMarkers($request);

        return response()->json([
            'agents' => $data->values(),
            'pending_bookings' => $pendingBookings,
            'stats' => $stats,
        ]);
    }

    /**
     * Active field agents. Location may come from live GPS or provider HQ fallback.
     */
    private function baseAgentQuery()
    {
        return User::query()
            ->isFieldAgent()
            ->ofStatus(1);
    }

    /**
     * Prefer live GPS on users; fall back to provider HQ coordinates.
     *
     * @return array{lat: float, lng: float, source: string}|null
     */
    private function resolveCoords(User $agent): ?array
    {
        if ($agent->latitude !== null && $agent->longitude !== null
            && $agent->latitude !== '' && $agent->longitude !== '') {
            return [
                'lat' => (float) $agent->latitude,
                'lng' => (float) $agent->longitude,
                'source' => 'live',
            ];
        }

        $providerCoords = null;
        if ($agent->user_type === 'provider-admin') {
            $providerCoords = $agent->provider?->coordinates;
        } elseif ($agent->user_type === 'provider-serviceman') {
            $providerCoords = $agent->serviceman?->provider?->coordinates;
        }

        if (is_string($providerCoords)) {
            $providerCoords = json_decode($providerCoords, true);
        }

        if (is_array($providerCoords)) {
            $lat = $providerCoords['latitude'] ?? $providerCoords['lat'] ?? null;
            $lng = $providerCoords['longitude'] ?? $providerCoords['lng'] ?? null;
            if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
                return [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'source' => 'hq',
                ];
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, string>  $userIds
     * @return Collection<int, string>
     */
    private function busyUserIds(Collection $userIds): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        $busyStatuses = config('tracking.busy_field_statuses', ['on_the_way', 'arrived', 'in_progress']);

        $activeBookings = Booking::query()
            ->whereNotIn('booking_status', ['completed', 'canceled'])
            ->whereIn('field_status', $busyStatuses)
            ->get(['id', 'provider_id', 'serviceman_id', 'team_id']);

        $busy = collect();

        $providerIds = $activeBookings->pluck('provider_id')->filter()->unique();
        if ($providerIds->isNotEmpty()) {
            $supervisorUserIds = Provider::query()
                ->whereIn('id', $providerIds)
                ->pluck('user_id');
            $busy = $busy->merge($supervisorUserIds);
        }

        $servicemanIds = $activeBookings->pluck('serviceman_id')->filter()->unique();
        if ($servicemanIds->isNotEmpty()) {
            $directServicemanUserIds = Serviceman::query()
                ->whereIn('id', $servicemanIds)
                ->pluck('user_id');
            $busy = $busy->merge($directServicemanUserIds);
        }

        $teamIds = $activeBookings->pluck('team_id')->filter()->unique();
        if ($teamIds->isNotEmpty()) {
            $teamServicemanIds = \DB::table('supervisor_team_servicemen')
                ->whereIn('team_id', $teamIds)
                ->pluck('serviceman_id');

            if ($teamServicemanIds->isNotEmpty()) {
                $teamUserIds = Serviceman::query()
                    ->whereIn('id', $teamServicemanIds)
                    ->pluck('user_id');
                $busy = $busy->merge($teamUserIds);
            }
        }

        return $busy->unique()->intersect($userIds)->values();
    }

    private function pendingBookingMarkers(Request $request): Collection
    {
        $query = Booking::query()
            ->where('booking_status', 'pending')
            ->with(['customer:id,first_name,last_name,phone,profile_image'])
            ->latest()
            ->limit(200);

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        return $query->get()->map(function (Booking $booking) {
            $coords = $this->extractBookingCoords($booking);
            if (!$coords) {
                return null;
            }

            $customerName = trim(($booking->customer?->first_name ?? '') . ' ' . ($booking->customer?->last_name ?? ''));
            $customerId = $booking->customer_id ?: $booking->customer?->id;

            return [
                'id' => $booking->id,
                'readable_id' => $booking->readable_id,
                'customer_id' => $customerId,
                'customer_name' => $customerName !== '' ? $customerName : translate('Customer'),
                'customer_phone' => $booking->customer?->phone,
                'customer_avatar' => $booking->customer?->profile_image_full_path,
                'profile_url' => $customerId
                    ? route('admin.customer.detail', [$customerId, 'web_page' => 'overview'])
                    : null,
                'booking_url' => route('admin.booking.details', [$booking->id]),
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'created_at' => $booking->created_at?->diffForHumans(),
                'status' => 'pending_booking',
                'role' => 'customer',
                'role_label' => translate('Customer'),
            ];
        })->filter()->values();
    }

    private function extractBookingCoords(Booking $booking): ?array
    {
        $location = $booking->service_address_location;
        if (is_string($location)) {
            $location = json_decode($location, true);
        }

        if (is_array($location)) {
            $lat = $location['lat'] ?? $location['latitude'] ?? null;
            $lng = $location['lng'] ?? $location['lon'] ?? $location['longitude'] ?? null;
            if ($lat !== null && $lng !== null) {
                return ['lat' => (float) $lat, 'lng' => (float) $lng];
            }
        }

        return null;
    }
}
