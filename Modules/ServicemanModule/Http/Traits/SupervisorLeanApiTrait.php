<?php

namespace Modules\ServicemanModule\Http\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\BookingModule\Entities\Booking;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;

/**
 * Lean API payloads for supervisor-mode mobile app.
 * Strips marketplace/company/wallet/zone-polygon noise.
 */
trait SupervisorLeanApiTrait
{
    protected function mapLeanPaginator(LengthAwarePaginator $paginator, callable $mapper): LengthAwarePaginator
    {
        $paginator->setCollection(
            $paginator->getCollection()->map($mapper)->values()
        );

        return $paginator;
    }

    protected function leanPerson(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image_full_path' => $user->profile_image_full_path ?? null,
            'is_active' => (int) ($user->is_active ?? 0)];
    }

    protected function leanServicemanFromUser(User $user): array
    {
        $person = $this->leanPerson($user);
        $person['serviceman_id'] = $user->serviceman?->id;
        $person['user_type'] = 'provider-serviceman';

        return $person;
    }

    protected function leanServiceman(Serviceman $serviceman): array
    {
        $person = $this->leanPerson($serviceman->user) ?? [
            'id' => null,
            'first_name' => null,
            'last_name' => null,
            'full_name' => '',
            'email' => null,
            'phone' => null,
            'profile_image_full_path' => null,
            'is_active' => 0];

        $person['serviceman_id'] = $serviceman->id;
        $person['user_id'] = $serviceman->user_id;
        $person['provider_id'] = $serviceman->provider_id;

        return $person;
    }

    protected function leanTeamMembers(SupervisorTeam $team): array
    {
        $team->loadMissing('servicemen.user');

        return $team->servicemen->map(function ($serviceman) {
            return [
                'id' => $serviceman->id,
                'name' => trim(($serviceman->user?->first_name ?? '') . ' ' . ($serviceman->user?->last_name ?? '')),
                'phone' => $serviceman->user?->phone,
                'profile_image' => $serviceman->user?->profile_image_full_path ?? null,
                'is_active' => (int) ($serviceman->user?->is_active ?? 0)];
        })->values()->all();
    }

    protected function leanTeam(SupervisorTeam $team): array
    {
        $assignedBookings = $team->relationLoaded('bookings')
            ? $team->bookings
            : $team->bookings()
                ->with('service_address')
                ->select('id', 'readable_id', 'booking_status', 'field_status', 'team_id', 'service_schedule', 'service_address_id', 'service_address_location')
                ->whereNotIn('booking_status', ['canceled'])
                ->latest()
                ->get();

        return [
            'id' => $team->id,
            'name' => $team->name,
            'is_active' => (bool) $team->is_active,
            'members_count' => $team->relationLoaded('servicemen')
                ? $team->servicemen->count()
                : $team->servicemen()->count(),
            'members' => $this->leanTeamMembers($team),
            'assigned_bookings' => $assignedBookings->map(function ($booking) {
                $location = $this->leanLocationPayload($booking);
                return [
                    'id' => $booking->id,
                    'readable_id' => $booking->readable_id,
                    'booking_status' => $booking->booking_status,
                    'field_status' => $booking->field_status,
                    'service_schedule' => $booking->service_schedule,
                    'location' => $location,
                    'coordinates' => ['lat' => $location['lat'], 'lng' => $location['lng']]];
            })->values()->all()];
    }

    protected function leanBookingLocation(Booking $booking): ?string
    {
        $booking->loadMissing('service_address');
        $address = $booking->service_address;
        if (!$address) {
            return null;
        }

        $parts = array_filter([
            $address->address ?? null,
            $address->street ?? null,
            $address->city ?? null]);

        return $parts ? implode(', ', $parts) : ($address->address ?? null);
    }

    protected function leanLocationPayload(Booking $booking): array
    {
        $coords = $this->leanBookingCoordinates($booking) ?? ['lat' => null, 'lng' => null];

        return [
            'text' => $this->leanBookingLocation($booking),
            'lat' => $coords['lat'] ?? null,
            'lng' => $coords['lng'] ?? null];
    }

    protected function leanBookingCoordinates(Booking $booking): ?array
    {
        if ($booking->service_address_location) {
            $location = is_string($booking->service_address_location)
                ? json_decode($booking->service_address_location, true)
                : (array) $booking->service_address_location;

            $latitude = $location['lat'] ?? $location['latitude'] ?? null;
            $longitude = $location['lon'] ?? $location['longitude'] ?? $location['lng'] ?? null;

            if ($latitude !== null && $longitude !== null) {
                return ['lat' => (float) $latitude, 'lng' => (float) $longitude];
            }
        }

        $booking->loadMissing('service_address');
        if ($booking->service_address && $booking->service_address->lat && $booking->service_address->lon) {
            return [
                'lat' => (float) $booking->service_address->lat,
                'lng' => (float) $booking->service_address->lon];
        }

        return null;
    }

    protected function leanServiceTitle(Booking $booking): string
    {
        $booking->loadMissing(['detail.service', 'subCategory']);
        $firstDetail = $booking->detail->first();
        if ($firstDetail?->service?->name) {
            return (string) $firstDetail->service->name;
        }

        return (string) ($booking->subCategory?->name ?? translate('Service'));
    }

    protected function leanBookingListItem(Booking $booking): array
    {
        $booking->loadMissing(['customer', 'team', 'detail.service', 'subCategory', 'service_address']);

        return [
            'id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'booking_status' => $booking->booking_status,
            'field_status' => $booking->field_status,
            'progress_percent' => (int) ($booking->progress_percent ?? 0),
            'service_schedule' => $booking->service_schedule,
            'title' => $this->leanServiceTitle($booking),
            'customer' => [
                'id' => $booking->customer?->id,
                'name' => trim(($booking->customer?->first_name ?? '') . ' ' . ($booking->customer?->last_name ?? '')),
                'phone' => $booking->customer?->phone],
            'property_type' => $booking->service_address?->address_type ?? $booking->service_address?->house ?? null,
            'location_text' => $this->leanBookingLocation($booking),
            'location' => $this->leanLocationPayload($booking),
            'coordinates' => $this->leanBookingCoordinates($booking),
            'team' => $booking->team ? [
                'id' => $booking->team->id,
                'name' => $booking->team->name] : null,
            'is_paid' => (int) ($booking->is_paid ?? 0),
            'total_booking_amount' => (float) ($booking->total_booking_amount ?? 0)];
    }

    protected function leanBookingDetail(Booking $booking): array
    {
        $card = $this->leanBookingListItem($booking);
        $booking->loadMissing(['detail.service', 'team.servicemen.user', 'status_histories']);

        $card['customer_note'] = $booking->customer_note;
        $card['location'] = $this->leanLocationPayload($booking);
        $card['coordinates'] = $this->leanBookingCoordinates($booking);
        $card['payment_method'] = $booking->payment_method;
        $card['services'] = $booking->detail->map(function ($detail) {
            return [
                'id' => $detail->id,
                'service_id' => $detail->service_id,
                'service_name' => $detail->service?->name,
                'quantity' => $detail->quantity,
                'total_cost' => $detail->total_cost];
        })->values()->all();

        if ($booking->team) {
            $booking->team->loadMissing('servicemen.user');
            $card['team'] = [
                'id' => $booking->team->id,
                'name' => $booking->team->name,
                'is_active' => (bool) $booking->team->is_active,
                'members_count' => $booking->team->servicemen->count(),
                'members' => $this->leanTeamMembers($booking->team)];
        }

        $card['status_histories'] = $booking->status_histories->take(20)->map(function ($history) {
            return [
                'booking_status' => $history->booking_status,
                'changed_by' => $history->changed_by,
                'created_at' => $history->created_at];
        })->values()->all();

        return $card;
    }
}
