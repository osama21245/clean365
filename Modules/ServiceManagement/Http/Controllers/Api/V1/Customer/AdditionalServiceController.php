<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\ServiceManagement\Entities\AdditionalService;

class AdditionalServiceController extends Controller
{
    private AdditionalService $additionalService;

    public function __construct(AdditionalService $additionalService)
    {
        $this->additionalService = $additionalService;
    }

    /**
     * Get active additional services with price, features and icons.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $services = $this->additionalService
            ->active()
            ->with(['service_discount'])
            ->orderBy('sort_order', 'asc')
            ->get();

        $services->transform(function ($item) {
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

        return response()->json(response_formatter(DEFAULT_200, $services), 200);
    }
}
