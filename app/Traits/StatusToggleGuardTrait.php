<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;

trait StatusToggleGuardTrait
{
    protected function blockedActionResponse(
        string $message,
        string $responseCode = 'action_blocked_400',
        string $errorCode = 'action_blocked'
    ): JsonResponse {
        $translatedMessage = translate($message);

        return response()->json([
            'response_code' => $responseCode,
            'message' => $translatedMessage,
            'content' => null,
            'errors' => [
                [
                    'error_code' => $errorCode,
                    'message' => $translatedMessage]]], 400);
    }

    protected function getBlockingBookingStatuses(): array
    {
        return ['accepted', 'ongoing', 'pending'];
    }

    protected function hasBlockingBookingsForService(string $serviceId): bool
    {
        return Booking::whereIn('booking_status', $this->getBlockingBookingStatuses())
            ->whereHas('detail', function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->exists();
    }

    protected function hasBlockingBookingsForCategory(string $categoryId): bool
    {
        return Booking::where('category_id', $categoryId)
            ->whereIn('booking_status', $this->getBlockingBookingStatuses())
            ->exists();
    }

    protected function hasBlockingBookingsForSubCategory(string $subCategoryId): bool
    {
        return Booking::where('sub_category_id', $subCategoryId)
            ->whereIn('booking_status', $this->getBlockingBookingStatuses())
            ->exists();
    }

    protected function hasBlockingBookingsForZone(string $zoneId): bool
    {
        return Booking::where('zone_id', $zoneId)
            ->whereIn('booking_status', $this->getBlockingBookingStatuses())
            ->exists();
    }

    protected function hasProvidersInZone(string $zoneId): bool
    {
        return Provider::where('zone_id', $zoneId)->exists();
    }

    protected function blockedStatusToggleResponse(string $message): JsonResponse
    {
        return $this->blockedActionResponse($message, 'status_change_blocked_400', 'status_change_blocked');
    }

    protected function getBlockingStatusesText(): string
    {
        return collect($this->getBlockingBookingStatuses())
            ->map(fn ($status) => str_replace('_', ' ', $status))
            ->implode(' or ');
    }

    protected function getBlockedByBookingsActionMessage(string $entityLabel, string $action = 'disable'): string
    {
        return sprintf(
            'Cannot %s this %s because it has %s bookings.',
            $action,
            $entityLabel,
            $this->getBlockingStatusesText()
        );
    }

    protected function getBlockedByRelationActionMessage(string $entityLabel, string $relationLabel, string $action = 'disable'): string
    {
        return sprintf(
            'Cannot %s this %s because %s exist in this %s.',
            $action,
            $entityLabel,
            $relationLabel,
            $entityLabel
        );
    }

    protected function blockedByBookingsActionResponse(
        string $entityLabel,
        string $action = 'disable',
        string $responseCode = 'action_blocked_400',
        string $errorCode = 'action_blocked'
    ): JsonResponse {
        return $this->blockedActionResponse(
            $this->getBlockedByBookingsActionMessage($entityLabel, $action),
            $responseCode,
            $errorCode
        );
    }

    protected function blockedByRelationActionResponse(
        string $entityLabel,
        string $relationLabel,
        string $action = 'disable',
        string $responseCode = 'action_blocked_400',
        string $errorCode = 'action_blocked'
    ): JsonResponse {
        return $this->blockedActionResponse(
            $this->getBlockedByRelationActionMessage($entityLabel, $relationLabel, $action),
            $responseCode,
            $errorCode
        );
    }

    protected function blockedByBookingsStatusToggleResponse(string $entityLabel): JsonResponse
    {
        return $this->blockedByBookingsActionResponse(
            $entityLabel,
            'disable',
            'status_change_blocked_400',
            'status_change_blocked'
        );
    }

    protected function blockedByRelationStatusToggleResponse(string $entityLabel, string $relationLabel): JsonResponse
    {
        return $this->blockedByRelationActionResponse(
            $entityLabel,
            $relationLabel,
            'disable',
            'status_change_blocked_400',
            'status_change_blocked'
        );
    }
}
