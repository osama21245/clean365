<?php

namespace Modules\BookingModule\Http\Traits;

use Carbon\Carbon;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;

trait BookingScopes
{

    public function scopeOfBookingStatus($query, $status): void
    {
        $query->where('booking_status', '=', $status);
    }
    public function scopeOfRepeatBookingStatus($query, $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->where('is_repeated', '=', $status);
    }

    public function scopeSearch($query, $keywords, array $searchColumns): mixed
    {
        return $query->when($keywords && $searchColumns, function ($query) use ($keywords, $searchColumns) {
            $keys = explode(' ', $keywords);
            $query->where(function ($query) use ($keys, $searchColumns) {
                foreach ($keys as $key) {
                    foreach ($searchColumns as $column) {
                        $query->orWhere($column, 'LIKE', '%' . $key . '%');
                    }
                }
            });
        });
    }

    public function scopeFilterByZoneId($query, $zoneId): mixed
    {
        return $query->when($zoneId, function ($query) use ($zoneId) {
            $query->where('zone_id', $zoneId);
        });
    }

    public function scopeFilterByZoneIds($query, $zoneIds): mixed
    {
        return $query->when($zoneIds, function ($query) use ($zoneIds) {
            $query->whereIn('zone_id', $zoneIds);
        });
    }

    public function scopeFilterByCategoryIds($query, $categoryIds): mixed
    {
        return $query->when($categoryIds, function ($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        });
    }

    public function scopeFilterBySubcategoryIds($query, $subCategoryIds): mixed
    {
        return $query->when($subCategoryIds, function ($query) use ($subCategoryIds) {
            $query->whereIn('sub_category_id', $subCategoryIds);
        });
    }

    public function scopeFilterByDateRange($query, $fromDate, $toDate): mixed
    {
        return $query->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
            if (!($fromDate instanceof Carbon)) {
                $fromDate = Carbon::parse($fromDate);
            }
            if (!($toDate instanceof Carbon)) {
                $toDate = Carbon::parse($toDate);
            }

            if ($fromDate->equalTo($toDate)) {
                $query->whereDate('created_at', $fromDate->startOfDay());
            } else {
                $query->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()]);
            }
        });
    }

    public function scopeAdminPendingBookings($query, $maxBookingAmount = null): mixed
    {
        // Max-amount verification removed — always treat pending as pending.
        return $query->where('booking_status', 'pending');
    }

    public function scopeAdminAcceptedBookings($query, $maxBookingAmount = null): mixed
    {
        // Max-amount verification removed — always treat accepted as accepted.
        return $query->where('booking_status', 'accepted');
    }

    public function scopeProviderPendingBookings($query, Provider $provider, $maxBookingAmount = null)
    {
        $providerId = $provider->id;

        // Internal supervisors (employees): any booking in zone — no subscribed-service / package gates.
        if (supervisorMode()) {
            return $query
                ->ofBookingStatus('pending')
                ->where('zone_id', $provider->zone_id)
                ->where(function ($query) use ($provider) {
                    $query->whereNull('provider_id')->orWhere('provider_id', $provider->id);
                });
        }

        $packageSubscriber = PackageSubscriber::where('provider_id', $providerId)->first();
        $endDate = optional($packageSubscriber)->package_end_date;
        $canceled = optional($packageSubscriber)->is_canceled;
        $packageEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
        $currentDate = Carbon::now()->subDay();
        $isPackageEnded = $packageEndDate ? $currentDate->diffInDays($packageEndDate, false) : null;
        $scheduleBookingEligibility = nextBookingEligibility($providerId);

        if ($packageSubscriber) {
            if ($isPackageEnded > 0 && $scheduleBookingEligibility && !$canceled) {
                if ($provider->service_availability && (!$provider->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $zone_id = $provider->zone_id;
                    $subscribedSubCategories = SubscribedService::where(['provider_id' => $provider->id])->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

                    return $query
                        ->ofBookingStatus('pending')
                        ->whereIn('sub_category_id', $subscribedSubCategories)
                        ->where('zone_id', $zone_id)
                        ->where(function ($query) use ($provider) {
                            $query->whereNull('provider_id')->orWhere('provider_id', $provider->id);
                        });
                } else {
                    return $query->whereNull('id');
                }
            } else {
                return $query->whereRaw('1 = 0'); // This ensures no results are returned
            }
        }

        if ($provider->service_availability && (!$provider->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
            $zone_id = $provider->zone_id;
            $subscribedSubCategories = SubscribedService::where(['provider_id' => $provider->id])->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

            return $query
                ->ofBookingStatus('pending')
                ->whereIn('sub_category_id', $subscribedSubCategories)
                ->where('zone_id', $zone_id)
                ->where(function ($query) use ($provider) {
                    $query->whereNull('provider_id')->orWhere('provider_id', $provider->id);
                });
        }

        return $query->whereNull('id');
    }

    public function scopeProviderAcceptedBookings($query, $provider_id, $maxBookingAmount = null): mixed
    {
        return $query
            ->ofBookingStatus('accepted')
            ->where(function ($query) use ($provider_id) {
                $query->where('provider_id', $provider_id)
                    ->orWhereHas('repeat', function ($subQuery) use ($provider_id) {
                        $subQuery->where('provider_id', $provider_id);
                    });
            });
    }
}
