<?php

namespace Modules\BookingModule\Services;

use Modules\BookingModule\Entities\Booking;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;

/**
 * Lean customer-app booking payloads (list + detail).
 * Avoids dumping the full Eloquent graph while returning what the UI needs.
 */
class CustomerBookingPresenter
{
    /**
     * Filters accepted by GET /customer/booking?booking_status=
     * - pending / completed / canceled (direct)
     * - active → accepted + ongoing (Arabic: نشطة)
     * - all / accepted / ongoing kept for backward compatibility
     *
     * @return list<string>
     */
    public static function allowedStatusFilters(): array
    {
        return ['all', 'pending', 'active', 'accepted', 'ongoing', 'completed', 'canceled'];
    }

    /**
     * @return list<string>|null  null = no status filter (all)
     */
    public static function statusKeysForFilter(string $filter): ?array
    {
        return match ($filter) {
            'all' => null,
            'active' => ['accepted', 'ongoing'],
            'pending', 'accepted', 'ongoing', 'completed', 'canceled' => [$filter],
            default => [$filter],
        };
    }

    public static function fieldStatusMeta(?string $status): array
    {
        $key = $status ?: null;
        $labels = [
            'assigned' => ['en' => 'Assigned', 'ar' => 'تم التعيين'],
            'on_the_way' => ['en' => 'On the way', 'ar' => 'في الطريق'],
            'arrived' => ['en' => 'Arrived', 'ar' => 'وصل'],
            'in_progress' => ['en' => 'In progress', 'ar' => 'جاري التنفيذ'],
            'completed' => ['en' => 'Completed', 'ar' => 'مكتمل'],
        ];

        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $timeline = array_map(function (array $row) use ($labels, $locale) {
            $k = $row['key'];
            return [
                'key' => $k,
                'label' => $labels[$k][$locale] ?? $row['value'],
            ];
        }, BOOKING_FIELD_STATUSES);

        return [
            'field_status' => $key,
            'field_status_label' => $key ? ($labels[$key][$locale] ?? $key) : null,
            'field_status_timeline' => $timeline,
            'progress_percent' => match ($key) {
                'assigned' => 0,
                'on_the_way' => 20,
                'arrived' => 40,
                'in_progress' => 70,
                'completed' => 100,
                default => (int) 0,
            },
        ];
    }

    public static function packageName(Booking $booking): ?string
    {
        $booking->loadMissing(['detail.service', 'subCategory']);

        $first = $booking->detail->first();
        if ($first) {
            $name = $first->service_name
                ?: $first->service?->name
                ?: null;
            if ($name) {
                return (string) $name;
            }
        }

        if ($booking->subscription_id) {
            $subscription = CustomerServiceSubscription::query()
                ->with(['service' => fn ($q) => $q->withoutGlobalScopes()->withTrashed()])
                ->find($booking->subscription_id);
            if ($subscription?->service?->name) {
                return (string) $subscription->service->name;
            }
        }

        return $booking->subCategory?->name
            ? (string) $booking->subCategory->name
            : null;
    }

    public static function listItem(Booking $booking): array
    {
        $booking->loadMissing(['detail.service', 'subCategory', 'category']);
        $field = self::fieldStatusMeta($booking->field_status);
        $packageName = self::packageName($booking);

        return [
            'id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'booking_status' => $booking->booking_status,
            'field_status' => $field['field_status'],
            'field_status_label' => $field['field_status_label'],
            'progress_percent' => (int) ($booking->progress_percent ?? $field['progress_percent']),
            'package_name' => $packageName,
            'service_name' => $packageName,
            'title' => $packageName,
            'total_booking_amount' => (float) ($booking->total_booking_amount ?? 0),
            'service_schedule' => $booking->service_schedule,
            'payment_status' => $booking->payment_status,
            'is_paid' => (int) ($booking->is_paid ?? 0),
            'category_id' => $booking->category_id,
            'category_name' => $booking->category?->name,
            'sub_category_id' => $booking->sub_category_id,
            'sub_category_name' => $booking->subCategory?->name,
            'subscription_id' => $booking->subscription_id,
            'team_id' => $booking->team_id,
            'is_repeated' => (int) ($booking->is_repeated ?? 0),
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at,
        ];
    }

    public static function detail(Booking $booking): array
    {
        $booking->loadMissing([
            'detail.service',
            'category',
            'subCategory',
            'customer',
            'provider.owner',
            'serviceman.user',
            'team.servicemen.user',
            'service_address',
            'status_histories.user',
        ]);

        $field = self::fieldStatusMeta($booking->field_status);
        $packageName = self::packageName($booking);

        $address = $booking->service_address_location
            ? (is_string($booking->service_address_location)
                ? json_decode($booking->service_address_location)
                : $booking->service_address_location)
            : $booking->service_address;

        $coords = self::coordsFromAddress($address);

        $services = $booking->detail->map(function ($line) {
            return [
                'id' => $line->id,
                'service_id' => $line->service_id,
                'service_name' => $line->service_name ?: $line->service?->name,
                'variant_key' => $line->variant_key ?? null,
                'quantity' => (int) ($line->quantity ?? 1),
                'service_cost' => (float) ($line->service_cost ?? 0),
                'total_cost' => (float) ($line->total_cost ?? $line->service_cost ?? 0),
                'thumbnail' => $line->service?->thumbnail_full_path ?? null,
            ];
        })->values()->all();

        return [
            'id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'booking_status' => $booking->booking_status,
            'field_status' => $field['field_status'],
            'field_status_label' => $field['field_status_label'],
            'field_status_timeline' => $field['field_status_timeline'],
            'progress_percent' => (int) ($booking->progress_percent ?? $field['progress_percent']),
            'package_name' => $packageName,
            'service_name' => $packageName,
            'title' => $packageName,
            'total_booking_amount' => (float) ($booking->total_booking_amount ?? 0),
            'total_discount_amount' => (float) ($booking->total_discount_amount ?? 0),
            'total_tax_amount' => (float) ($booking->total_tax_amount ?? 0),
            'service_schedule' => $booking->service_schedule,
            'payment_method' => $booking->payment_method,
            'payment_status' => $booking->payment_status,
            'is_paid' => (int) ($booking->is_paid ?? 0),
            'customer_note' => $booking->customer_note,
            'category_id' => $booking->category_id,
            'category_name' => $booking->category?->name,
            'sub_category_id' => $booking->sub_category_id,
            'sub_category_name' => $booking->subCategory?->name,
            'subscription_id' => $booking->subscription_id,
            'services' => $services,
            'location' => [
                'text' => is_object($address)
                    ? ($address->address ?? $address->address_label ?? null)
                    : (is_array($address) ? ($address['address'] ?? null) : null),
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'address' => $address,
            ],
            'coordinates' => $coords,
            'team' => self::teamPayload($booking),
            'provider' => $booking->provider ? [
                'id' => $booking->provider->id,
                'company_name' => $booking->provider->company_name,
                'company_phone' => $booking->provider->company_phone,
                'logo' => $booking->provider->logo_full_path ?? null,
            ] : null,
            'serviceman' => $booking->serviceman ? [
                'id' => $booking->serviceman->id,
                'name' => trim(($booking->serviceman->user?->first_name ?? '') . ' ' . ($booking->serviceman->user?->last_name ?? '')),
                'phone' => $booking->serviceman->user?->phone,
                'profile_image' => $booking->serviceman->user?->profile_image_full_path ?? null,
            ] : null,
            'photos' => [
                'before' => $booking->before_images_full_path ?? [],
                'during' => $booking->during_images_full_path ?? [],
                'after' => $booking->after_images_full_path ?? [],
            ],
            'status_histories' => $booking->status_histories->map(function ($h) {
                return [
                    'booking_status' => $h->booking_status ?? null,
                    'changed_by' => trim(($h->user?->first_name ?? '') . ' ' . ($h->user?->last_name ?? '')),
                    'created_at' => $h->created_at,
                ];
            })->values()->all(),
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at,
        ];
    }

    public static function teamPayload(Booking $booking): ?array
    {
        $booking->loadMissing(['team.servicemen.user']);
        if (!$booking->team) {
            return null;
        }

        return [
            'id' => $booking->team->id,
            'name' => $booking->team->name,
            'members_count' => $booking->team->servicemen->count(),
            'members' => $booking->team->servicemen->map(function ($serviceman) {
                return [
                    'id' => $serviceman->id,
                    'name' => trim(($serviceman->user?->first_name ?? '') . ' ' . ($serviceman->user?->last_name ?? '')),
                    'phone' => $serviceman->user?->phone,
                    'profile_image' => $serviceman->user?->profile_image_full_path ?? null,
                ];
            })->values()->all(),
        ];
    }

    /**
     * @return array{lat: ?float, lng: ?float}
     */
    private static function coordsFromAddress(mixed $address): array
    {
        if (!$address) {
            return ['lat' => null, 'lng' => null];
        }

        $arr = is_object($address) ? (array) $address : (is_array($address) ? $address : []);
        $lat = $arr['lat'] ?? $arr['latitude'] ?? null;
        $lng = $arr['lng'] ?? $arr['lon'] ?? $arr['longitude'] ?? null;

        return [
            'lat' => $lat !== null && $lat !== '' ? (float) $lat : null,
            'lng' => $lng !== null && $lng !== '' ? (float) $lng : null,
        ];
    }
}
