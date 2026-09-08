<?php

namespace Modules\ServiceManagement\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\ServiceManagement\Entities\AdditionalService;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;
use Modules\ServiceManagement\Entities\CustomerServiceSubscriptionVisit;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;

use Modules\UserManagement\Entities\UserAddress;

class CustomerServiceSubscriptionService
{
    /**
     * Ensure list/detail API payloads include service, property, and zone price.
     *
     * @param  CustomerServiceSubscription|Collection<int, CustomerServiceSubscription>  $subscriptions
     * @return CustomerServiceSubscription|Collection<int, CustomerServiceSubscription>
     */
    public static function presentForApi($subscriptions, ?string $zoneId = null)
    {
        $zoneId = self::resolveZoneId($zoneId);

        if ($subscriptions instanceof CustomerServiceSubscription) {
            return self::enrichOne($subscriptions, $zoneId);
        }

        $items = $subscriptions instanceof Collection
            ? $subscriptions
            : collect($subscriptions);

        return $items->map(fn(CustomerServiceSubscription $sub) => self::enrichOne($sub, $zoneId));
    }

    public static function enrichOne(CustomerServiceSubscription $subscription, ?string $zoneId = null): CustomerServiceSubscription
    {
        $zoneId = self::resolveZoneId($zoneId);

        $subscription->loadMissing([
            'service' => fn($q) => $q->withoutGlobalScopes()->withTrashed(),
            'property',
            'additional_service' => fn($q) => $q->withoutGlobalScopes(),
        ]);

        if (!$subscription->service) {
            $addService = $subscription->additional_service
                ?? AdditionalService::withoutGlobalScopes()->find($subscription->service_id);

            if ($addService) {
                $price = round((float) ($addService->price ?? 0), 2);
                $subscription->apiPrice = $price;
                $subscription->setAttribute('price', $price);
                $subscription->setRelation('service', (new Service())->newFromBuilder([
                    'id' => $addService->id,
                    'name' => $addService->name,
                    'thumbnail' => $addService->icon ?? null,
                    'cover_image' => $addService->icon ?? null,
                    'visits_count' => null,
                    'price' => $price,
                    'min_bidding_price' => $price,
                    'property_id' => null,
                    'is_active' => (int) ($addService->is_active ?? 1),
                ]));
                // Expose full-path helpers used by clients when present on AdditionalService.
                $subscription->service->setAttribute('icon_full_path', $addService->icon_full_path ?? null);
                $subscription->service->setAttribute('thumbnail_full_path', $addService->icon_full_path ?? null);
                $subscription->service->setAttribute('cover_image_full_path', $addService->icon_full_path ?? null);

                self::resolveAddress($subscription);
                return $subscription;
            }
        }

        if ($subscription->service) {
            $price = self::resolveServicePrice($subscription->service, $zoneId);
            $subscription->apiPrice = $price;
            $subscription->setAttribute('price', $price);
            $subscription->service->setAttribute('price', $price);
        } else {
            $subscription->apiPrice = 0.0;
            $subscription->setAttribute('price', 0.0);
        }

        if (!$subscription->property && $subscription->property_id) {
            $subscription->load([
                'property' => fn($q) => $q->withoutGlobalScopes(),
            ]);
        }

        self::resolveAddress($subscription);

        return $subscription;
    }

    private static function resolveAddress(CustomerServiceSubscription $subscription): void
    {
        if (!$subscription->relationLoaded('address') || !$subscription->address) {
            $userAddress = null;
            if ($subscription->property_id) {
                $userAddress = UserAddress::where('property_id', $subscription->property_id)
                    ->where('user_id', $subscription->user_id)
                    ->first();
            }

            if (!$userAddress && $subscription->user_id) {
                $userAddress = UserAddress::where('user_id', $subscription->user_id)->latest()->first();
            }

            if ($userAddress) {
                $subscription->setRelation('address', $userAddress);
            } elseif ($subscription->property) {
                $subscription->setRelation('address', (object) [
                    'id' => null,
                    'user_id' => $subscription->user_id,
                    'property_id' => $subscription->property_id,
                    'address' => $subscription->property->address ?? null,
                ]);
            }
        }
    }

    public static function resolveServicePrice(?Service $service, ?string $zoneId = null): float
    {
        if (!$service) {
            return 0.0;
        }

        $zoneId = self::resolveZoneId($zoneId);

        $variation = null;
        if ($zoneId) {
            $variation = Variation::withoutGlobalScopes()
                ->where('service_id', $service->id)
                ->where('zone_id', $zoneId)
                ->orderBy('price')
                ->first();
        }

        if (!$variation) {
            $variation = Variation::withoutGlobalScopes()
                ->where('service_id', $service->id)
                ->orderBy('price')
                ->first();
        }

        $price = $variation?->price
            ?? $service->min_bidding_price
            ?? $service->getAttribute('price')
            ?? 0;

        return round((float) $price, 2);
    }

    public static function resolveZoneId(?string $zoneId = null): ?string
    {
        if ($zoneId) {
            return $zoneId;
        }

        $fromConfig = Config::get('zone_id');
        if ($fromConfig) {
            return (string) $fromConfig;
        }

        $request = request();
        if (!$request) {
            return null;
        }

        return $request->header('zoneId')
            ?: $request->header('zoneid')
            ?: $request->input('zone_id');
    }

    /**
     * Consume one visit per subscribed service when a booking is completed.
     * Skips if the visit was already consumed at confirm/rebook time.
     */
    public static function consumeVisitsOnBookingCompleted(Booking $booking): void
    {
        if ($booking->is_guest || !$booking->customer_id) {
            return;
        }

        $booking->loadMissing(['detail', 'service_address']);

        $propertyId = $booking->service_address?->property_id;

        $serviceIds = $booking->detail->pluck('service_id')->filter()->unique()->values();
        if ($serviceIds->isEmpty()) {
            return;
        }

        foreach ($serviceIds as $serviceId) {
            self::consumeOneVisit(
                userId: $booking->customer_id,
                serviceId: $serviceId,
                propertyId: $propertyId,
                bookingId: $booking->id
            );
        }
    }

    public static function consumeOneVisit(string $userId, string $serviceId, ?string $propertyId, string $bookingId): bool
    {
        if (CustomerServiceSubscriptionVisit::where('booking_id', $bookingId)->where('service_id', $serviceId)->exists()) {
            return false;
        }

        return DB::transaction(function () use ($userId, $serviceId, $propertyId, $bookingId) {
            $query = CustomerServiceSubscription::query()
                ->where([
                    'user_id' => $userId,
                    'service_id' => $serviceId,
                    'status' => 'active'
                ]);

            if (!empty($propertyId)) {
                $query->where(function ($q) use ($propertyId) {
                    $q->where('property_id', $propertyId)
                        ->orWhereNull('property_id');
                });
            }

            /** @var CustomerServiceSubscription|null $subscription */
            $subscription = $query->lockForUpdate()->first();

            if (!$subscription || $subscription->remaining_visits < 1) {
                return false;
            }

            $subscription->remaining_visits = max(0, $subscription->remaining_visits - 1);
            if ($subscription->remaining_visits === 0) {
                $subscription->status = 'finished';
            }
            $subscription->save();

            CustomerServiceSubscriptionVisit::create([
                'subscription_id' => $subscription->id,
                'booking_id' => $bookingId,
                'service_id' => $serviceId
            ]);

            return true;
        });
    }
}
