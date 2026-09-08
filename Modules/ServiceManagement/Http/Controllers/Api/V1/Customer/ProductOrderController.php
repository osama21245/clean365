<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\Product;
use Modules\ServiceManagement\Entities\ProductOrder;
use Modules\ServiceManagement\Entities\ProductOrderDetail;

class ProductOrderController extends Controller
{
    private const FREE_DELIVERY_MIN = 200;
    private const DELIVERY_FEE = 15;

    private bool $isCustomerLoggedIn;
    private mixed $customerUserId;

    public function __construct(
        private Product $product,
        private ProductOrder $productOrder,
        private ProductOrderDetail $productOrderDetail,
        Request $request
    ) {
        $user = $request->user('api') ?? auth('api')->user();
        $this->isCustomerLoggedIn = (bool) $user;
        $this->customerUserId = $this->isCustomerLoggedIn ? $user->id : $request['guest_id'];
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'order_status' => 'nullable|in:pending,confirmed,processing,out_for_delivery,delivered,canceled,all',
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $orders = $this->productOrder
            ->with(['details'])
            ->where('customer_id', $this->customerUserId)
            ->when($request->filled('order_status') && $request['order_status'] !== 'all', function ($query) use ($request) {
                $query->where('order_status', $request['order_status']);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $orders), 200);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $order = $this->productOrder
            ->with(['details'])
            ->where('customer_id', $this->customerUserId)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $order), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid',
            'items.*.quantity' => 'required|integer|min:1',
            'address' => 'required|array',
            'address.lat' => 'required|numeric',
            'address.lng' => 'required|numeric',
            'address.label' => 'required|string|max:500',
            'coupon_code' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:1000',
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $productIds = collect($request['items'])->pluck('product_id')->unique()->values();
        $products = $this->product->ofStatus(1)->whereIn('id', $productIds)->get()->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            return response()->json(response_formatter(DEFAULT_404, null, [
                ['code' => 'product', 'message' => translate('One or more products not found or inactive')]
            ]), 404);
        }

        $detailsData = [];
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($request['items'] as $item) {
            $product = $products[$item['product_id']];
            $quantity = (int) $item['quantity'];
            $unitPrice = $this->resolveUnitPrice($product);
            $lineTotal = $unitPrice * $quantity;

            $detailsData[] = [
                'product_id' => $product->id,
                'product_name' => $product->getRawOriginal('name') ?: $product->name,
                'thumbnail' => $product->thumbnail,
                'product_price' => $product->price,
                'sale_price' => $product->sale_price,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $lineTotal];

            $subtotal += $lineTotal;
            $totalQuantity += $quantity;
        }

        $discountAmount = 0;
        $deliveryFee = $subtotal >= self::FREE_DELIVERY_MIN ? 0 : self::DELIVERY_FEE;
        $total = max(0, $subtotal - $discountAmount + $deliveryFee);

        try {
            DB::beginTransaction();

            $order = $this->productOrder->create([
                'customer_id' => $this->customerUserId,
                'order_status' => 'pending',
                'delivery_lat' => $request['address']['lat'],
                'delivery_lng' => $request['address']['lng'],
                'delivery_address' => $request['address']['label'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'coupon_code' => $request['coupon_code'] ?? null,
                'payment_method' => $request['payment_method'] ?? 'cash',
                'note' => $request['note'] ?? null,
                'total_quantity' => $totalQuantity]);

            foreach ($detailsData as $detail) {
                $detail['product_order_id'] = $order->id;
                $this->productOrderDetail->create($detail);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(response_formatter(DEFAULT_FAIL_200), 200);
        }

        $order->load('details');

        return response()->json(response_formatter(DEFAULT_STORE_200, $order), 200);
    }

    private function resolveUnitPrice(Product $product): float
    {
        if (!is_null($product->sale_price) && $product->sale_price > 0 && $product->sale_price < $product->price) {
            return (float) $product->sale_price;
        }

        return (float) $product->price;
    }
}
