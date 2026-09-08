<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\Product;
use Modules\ServiceManagement\Entities\ProductCategory;

class ProductController extends Controller
{
    public function __construct(
        private Product $product,
        private ProductCategory $productCategory
    ) {
    }

    /**
     * Product categories list
     */
    public function categories(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $categories = $this->productCategory->ofStatus(1)
            ->withCount(['products' => function ($query) {
                $query->ofStatus(1);
            }])
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $categories), 200);
    }

    /**
     * Products list
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'product_category_id' => 'nullable|uuid',
            'badge' => 'nullable|in:new,sale',
            'search' => 'nullable|string',
            'sort_by' => 'nullable|in:latest,price_low_to_high,price_high_to_low,top_rated']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $products = $this->product->ofStatus(1)
            ->with(['category'])
            ->when($request->filled('product_category_id'), function ($query) use ($request) {
                $query->where('product_category_id', $request['product_category_id']);
            })
            ->when($request->filled('badge'), function ($query) use ($request) {
                $query->where('badge', $request['badge']);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                $query->where(function ($q) use ($keys) {
                    foreach ($keys as $key) {
                        $q->orWhere('name', 'LIKE', '%' . $key . '%')
                            ->orWhere('short_description', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->get('sort_by') === 'price_low_to_high', function ($query) {
                $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), price) ASC');
            })
            ->when($request->get('sort_by') === 'price_high_to_low', function ($query) {
                $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), price) DESC');
            })
            ->when($request->get('sort_by') === 'top_rated', function ($query) {
                $query->orderByDesc('rating');
            })
            ->when(!$request->filled('sort_by') || $request->get('sort_by') === 'latest', function ($query) {
                $query->latest();
            })
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $products), 200);
    }

    /**
     * Product details
     */
    public function show(string $id): JsonResponse
    {
        $product = $this->product->ofStatus(1)
            ->with(['category'])
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $product), 200);
    }
}
