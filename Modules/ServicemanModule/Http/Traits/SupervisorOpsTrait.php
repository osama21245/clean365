<?php

namespace Modules\ServicemanModule\Http\Traits;

use Modules\BookingModule\Entities\Booking;
use Modules\ServicemanModule\Entities\SupervisorTeam;

trait SupervisorOpsTrait
{
    protected function fieldStatusKeys(): array
    {
        return array_column(BOOKING_FIELD_STATUSES, 'key');
    }

    protected function progressPercentForFieldStatus(?string $status): int
    {
        return match ($status) {
            'assigned' => 0,
            'on_the_way' => 20,
            'arrived' => 40,
            'in_progress' => 70,
            'completed' => 100,
            default => 0,
        };
    }

    protected function applyProgressFromFieldStatus(Booking $booking, string $fieldStatus): void
    {
        $booking->progress_percent = $this->progressPercentForFieldStatus($fieldStatus);
    }

    protected function canTransitionFieldStatus(?string $from, string $to): bool
    {
        $order = $this->fieldStatusKeys();
        if (!in_array($to, $order, true)) {
            return false;
        }

        if ($from === null || $from === '') {
            return $to === 'assigned';
        }

        $fromIndex = array_search($from, $order, true);
        $toIndex = array_search($to, $order, true);

        if ($fromIndex === false || $toIndex === false) {
            return false;
        }

        // Allow same status or move forward one-or-more steps (not backward).
        return $toIndex >= $fromIndex;
    }

    protected function syncBookingStatusFromFieldStatus(Booking $booking, string $fieldStatus): void
    {
        if ($fieldStatus === 'in_progress' && $booking->booking_status !== 'completed') {
            $booking->booking_status = 'ongoing';
        }

        if ($fieldStatus === 'completed') {
            $booking->booking_status = 'completed';
        }

        if (in_array($fieldStatus, ['assigned', 'on_the_way', 'arrived'], true)
            && in_array($booking->booking_status, ['pending', 'accepted'], true)) {
            $booking->booking_status = 'accepted';
        }
    }

    protected function deriveTeamStatus(SupervisorTeam $team): string
    {
        if (!$team->is_active) {
            return 'unavailable';
        }

        $active = $team->bookings()
            ->whereIn('field_status', ['on_the_way', 'arrived', 'in_progress'])
            ->whereNotIn('booking_status', ['completed', 'canceled'])
            ->latest('service_schedule')
            ->first();

        if (!$active) {
            return 'available';
        }

        if ($active->field_status === 'on_the_way') {
            return 'on_the_way';
        }

        return 'in_progress';
    }

    protected function getBookingCoordinatesForOps(Booking $booking): ?array
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

    protected function locationText(Booking $booking): ?string
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

    protected function packageLabel(Booking $booking): ?string
    {
        if (!$booking->subscription_id) {
            return null;
        }

        // UI shows package chip on job cards (e.g. باقة شهرية).
        return translate('Monthly package');
    }

    protected function serviceTitle(Booking $booking): string
    {
        $booking->loadMissing(['detail.service', 'subCategory']);
        $firstDetail = $booking->detail->first();
        if ($firstDetail?->service?->name) {
            return (string) $firstDetail->service->name;
        }

        return (string) ($booking->subCategory?->name ?? translate('Service'));
    }

    protected function attendanceSummary(Booking $booking): array
    {
        $booking->loadMissing(['team.servicemen', 'attendances']);

        $memberIds = $booking->team?->servicemen?->pluck('id')->all() ?? [];
        $total = count($memberIds);
        if ($total === 0) {
            return ['attended' => 0, 'total' => 0];
        }

        $attended = $booking->attendances
            ->where('is_attended', true)
            ->whereIn('serviceman_id', $memberIds)
            ->unique('serviceman_id')
            ->count();

        return [
            'attended' => $attended,
            'total' => $total];
    }

    protected function transformJobCard(Booking $booking): array
    {
        $booking->loadMissing([
            'customer',
            'team.servicemen.user',
            'detail.service',
            'subCategory',
            'service_address',
            'attendances']);

        $attendance = $this->attendanceSummary($booking);
        $coords = $this->getBookingCoordinatesForOps($booking);

        return [
            'id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'title' => $this->serviceTitle($booking),
            'field_status' => $booking->field_status,
            'booking_status' => $booking->booking_status,
            'is_seen' => !empty($booking->supervisor_seen_at),
            'supervisor_seen_at' => $booking->supervisor_seen_at,
            'package_label' => $this->packageLabel($booking),
            'progress_percent' => (int) ($booking->progress_percent ?? 0),
            'attendance' => $attendance,
            'schedule_at' => $booking->service_schedule,
            'customer' => [
                'id' => $booking->customer?->id,
                'name' => trim(($booking->customer?->first_name ?? '') . ' ' . ($booking->customer?->last_name ?? '')),
                'phone' => $booking->customer?->phone],
            'property_type' => $booking->service_address?->address_type ?? $booking->service_address?->house ?? null,
            'location_text' => $this->locationText($booking),
            'location' => [
                'text' => $this->locationText($booking),
                'lat' => $coords['lat'] ?? null,
                'lng' => $coords['lng'] ?? null],
            'coordinates' => $coords,
            'team' => $booking->team ? [
                'id' => $booking->team->id,
                'name' => $booking->team->name,
                'members_count' => $booking->team->servicemen->count(),
                'members' => $booking->team->servicemen->map(function ($serviceman) {
                    return [
                        'id' => $serviceman->id,
                        'name' => trim(($serviceman->user?->first_name ?? '') . ' ' . ($serviceman->user?->last_name ?? '')),
                        'phone' => $serviceman->user?->phone,
                        'profile_image' => $serviceman->user?->profile_image_full_path ?? null];
                })->values()->all()] : null];
    }

    protected function transformJobDetail(Booking $booking): array
    {
        $card = $this->transformJobCard($booking);
        $booking->loadMissing(['executionNotes.user', 'attendances']);

        $card['customer_note'] = $booking->customer_note;
        $card['attendance'] = $this->attendanceSummary($booking);
        $card['field_status_timeline'] = $this->fieldStatusKeys();
        $before = $booking->before_images_full_path ?? [];
        $during = $booking->during_images_full_path ?? [];
        $after = $booking->after_images_full_path ?? [];
        $card['photos'] = [
            'before' => $before,
            'during' => $during,
            'after' => $after,
            'counts' => [
                'before' => is_countable($before) ? count($before) : 0,
                'during' => is_countable($during) ? count($during) : 0,
                'after' => is_countable($after) ? count($after) : 0]];
        $card['execution_notes'] = $booking->executionNotes->map(function ($note) {
            return [
                'id' => $note->id,
                'note' => $note->note,
                'user_id' => $note->user_id,
                'user_name' => trim(($note->user?->first_name ?? '') . ' ' . ($note->user?->last_name ?? '')),
                'created_at' => $note->created_at];
        })->values()->all();

        if ($card['team']) {
            $card['team']['attendance'] = $card['attendance'];
        }

        return $card;
    }

    protected function transformTeamCard(SupervisorTeam $team): array
    {
        $team->loadMissing(['servicemen.user']);
        $status = $this->deriveTeamStatus($team);

        $activeJob = $team->bookings()
            ->whereNotIn('booking_status', ['completed', 'canceled'])
            ->whereIn('field_status', ['assigned', 'on_the_way', 'arrived', 'in_progress'])
            ->latest('service_schedule')
            ->with(['detail.service', 'subCategory', 'service_address', 'customer', 'attendances'])
            ->first();

        return [
            'id' => $team->id,
            'name' => $team->name,
            'is_active' => (bool) $team->is_active,
            'status' => $status,
            'technicians_count' => $team->servicemen->count(),
            'members' => $team->servicemen->map(function ($serviceman) {
                return [
                    'id' => $serviceman->id,
                    'name' => trim(($serviceman->user?->first_name ?? '') . ' ' . ($serviceman->user?->last_name ?? '')),
                    'phone' => $serviceman->user?->phone,
                    'profile_image' => $serviceman->user?->profile_image_full_path ?? null];
            })->values()->all(),
            'current_job' => $activeJob ? $this->transformJobCard($activeJob) : null];
    }
}
