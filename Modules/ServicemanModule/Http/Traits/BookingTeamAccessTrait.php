<?php

namespace Modules\ServicemanModule\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Modules\BookingModule\Entities\Booking;

trait BookingTeamAccessTrait
{
    protected function applyServicemanBookingAccess(Builder $query, string $servicemanId): Builder
    {
        return $query->where(function ($builder) use ($servicemanId) {
            $builder->where('serviceman_id', $servicemanId)
                ->orWhereHas('team.servicemen', function ($teamQuery) use ($servicemanId) {
                    $teamQuery->where('servicemen.id', $servicemanId);
                });
        });
    }

    protected function servicemanCanAccessBooking(?Booking $booking, string $servicemanId): bool
    {
        if (!$booking) {
            return false;
        }

        if ($booking->serviceman_id === $servicemanId) {
            return true;
        }

        if (!$booking->team_id) {
            return false;
        }

        return $booking->team()
            ->whereHas('servicemen', function ($query) use ($servicemanId) {
                $query->where('servicemen.id', $servicemanId);
            })
            ->exists();
    }

    protected function uploadTaskImages(array $files, string $directory): array
    {
        $uploaded = [];

        foreach ($files as $image) {
            $imageName = file_uploader($directory, APPLICATION_IMAGE_FORMAT, $image);
            $uploaded[] = ['image' => $imageName, 'storage' => getDisk()];
        }

        return $uploaded;
    }

    protected function mergeTaskImages(?array $existing, array $newImages): array
    {
        return array_values(array_merge($existing ?? [], $newImages));
    }
}
