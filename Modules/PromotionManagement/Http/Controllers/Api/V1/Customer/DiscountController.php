<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PromotionManagement\Entities\Discount;
use Modules\ServiceManagement\Entities\AdditionalService;
use Modules\ServiceManagement\Entities\Service;

class DiscountController extends Controller
{
    /**
     * Get all active discounted items (Services/Packages, Additional Services, and Category-wise discounts)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function offers(Request $request): JsonResponse
    {
        $zoneId = config('zone_id');

        // 1. Fetch active discounted services/packages (both direct service-wise and category-wise discounts)
        $services = Service::with([
            'category.zonesBasicInfo',
            'variations',
            'service_discount',
            'category.category_discount'
        ])
            ->where(function ($query) {
                $query->whereHas('service_discount')
                    ->orWhereHas('category.category_discount');
            })
            ->active()
            ->get();

        // Format variations and calculated prices for services
        $services->transform(function ($service) use ($zoneId) {
            $filteredVariations = $service->variations;
            if ($zoneId) {
                $filteredVariations = $service->variations->where('zone_id', $zoneId);
            }
            $defaultPrice = $filteredVariations->first() ? $filteredVariations->first()->price : 0;

            // Calculate active discount (direct service discount or inherited category discount)
            $activeDiscount = $service->service_discount?->first()?->discount
                ?? $service->category?->category_discount?->first()?->discount;

            $discountAmount = 0;
            $discountType = null;
            $discountedPrice = $defaultPrice;

            if ($activeDiscount) {
                $discountType = $activeDiscount->discount_amount_type;
                if ($discountType === 'percent') {
                    $discountAmount = ($defaultPrice * $activeDiscount->discount_amount) / 100;
                    if ($activeDiscount->max_discount_amount > 0 && $discountAmount > $activeDiscount->max_discount_amount) {
                        $discountAmount = $activeDiscount->max_discount_amount;
                    }
                } else {
                    $discountAmount = $activeDiscount->discount_amount;
                }
                $discountedPrice = max(0, $defaultPrice - $discountAmount);
            }

            $service['default_price'] = round($defaultPrice, 2);
            $service['discount_amount'] = round($discountAmount, 2);
            $service['discount_type'] = $discountType;
            $service['discounted_price'] = round($discountedPrice, 2);

            return $service;
        });

        // 2. Fetch active discounted additional services
        $activeAddServiceDiscountIds = \Modules\PromotionManagement\Entities\DiscountType::whereIn('discount_type', ['additional_service', 'service'])
            ->whereHas('discount', function ($query) {
                $query->where('promotion_type', 'discount')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })
            ->pluck('type_wise_id')
            ->toArray();

        $additionalServices = AdditionalService::active()
            ->whereIn('id', $activeAddServiceDiscountIds)
            ->with(['service_discount'])
            ->orderBy('sort_order', 'asc')
            ->get();

        $additionalServices->transform(function ($item) {
            $discountAmount = 0;
            $discountType = null;
            $discountedPrice = $item->price;

            $activeDiscount = $item->service_discount?->first()?->discount;
            if ($activeDiscount) {
                $discountType = $activeDiscount->discount_amount_type;
                if ($discountType === 'percent') {
                    $discountAmount = ($item->price * $activeDiscount->discount_amount) / 100;
                    if ($activeDiscount->max_discount_amount > 0 && $discountAmount > $activeDiscount->max_discount_amount) {
                        $discountAmount = $activeDiscount->max_discount_amount;
                    }
                } else {
                    $discountAmount = $activeDiscount->discount_amount;
                }
                $discountedPrice = max(0, $item->price - $discountAmount);
            }

            $item->discount_amount = round($discountAmount, 2);
            $item->discount_type = $discountType;
            $item->discounted_price = round($discountedPrice, 2);

            return $item;
        });

        // 3. Fetch active discounts breakdown
        $activeDiscounts = Discount::ofPromotionTypes('discount')
            ->ofStatus(1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->with(['category_types', 'service_types', 'additional_service_types'])
            ->get();

        return response()->json(response_formatter(DEFAULT_200, [
            'service_discount' => $services,
            'services' => $services,
            'additional_services' => $additionalServices,
            'active_discounts' => $activeDiscounts
        ]), 200);
    }
}
