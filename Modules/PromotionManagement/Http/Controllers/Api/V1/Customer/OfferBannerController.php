<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\OfferBanner;

class OfferBannerController extends Controller
{
    private OfferBanner $offerBanner;

    public function __construct(OfferBanner $offerBanner)
    {
        $this->offerBanner = $offerBanner;
    }

    /**
     * Display a listing of active offer banners for customer API.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $banners = $this->offerBanner->with(['service', 'category'])->ofStatus(1)
            ->with([
                'service' => function ($query) {
                    $query->where('is_active', 1);
                }
            ])
            ->with([
                'category' => function ($query) {
                    $query->where('is_active', 1);
                }
            ])
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        foreach ($banners as $key => $item) {
            if ($item->resource_type == 'service' && is_null($item->service)) {
                unset($banners[$key]);
            }
            if ($item->resource_type == 'category' && is_null($item->category)) {
                unset($banners[$key]);
            }
        }

        return response()->json(response_formatter(DEFAULT_200, $banners), 200);
    }
}
