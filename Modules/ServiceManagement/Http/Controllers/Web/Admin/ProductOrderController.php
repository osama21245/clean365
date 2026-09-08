<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ServiceManagement\Entities\ProductOrder;

class ProductOrderController extends Controller
{
    public function __construct(private ProductOrder $productOrder)
    {
    }

    public function index(Request $request): View|Factory|Application
    {
        $request->validate([
            'order_status' => 'nullable|in:all,pending,confirmed,processing,out_for_delivery,delivered,canceled']);

        $search = $request->has('search') ? $request['search'] : '';
        $orderStatus = $request->has('order_status') ? $request['order_status'] : 'all';
        $queryParam = ['search' => $search, 'order_status' => $orderStatus];

        $orders = $this->productOrder->with(['customer', 'details'])
            ->ofStatus($orderStatus)
            ->when($request->filled('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                $query->where(function ($q) use ($keys) {
                    foreach ($keys as $key) {
                        $q->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhere('delivery_address', 'LIKE', '%' . $key . '%')
                            ->orWhere('coupon_code', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('customer', function ($customerQuery) use ($key) {
                                $customerQuery->where('first_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $key . '%')
                                    ->orWhere('email', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParam);

        $statusCounts = [
            'all' => $this->productOrder->count(),
            'pending' => $this->productOrder->where('order_status', 'pending')->count(),
            'confirmed' => $this->productOrder->where('order_status', 'confirmed')->count(),
            'processing' => $this->productOrder->where('order_status', 'processing')->count(),
            'out_for_delivery' => $this->productOrder->where('order_status', 'out_for_delivery')->count(),
            'delivered' => $this->productOrder->where('order_status', 'delivered')->count(),
            'canceled' => $this->productOrder->where('order_status', 'canceled')->count()];

        return view('servicemanagement::admin.product-order.list', compact('orders', 'search', 'orderStatus', 'statusCounts'));
    }

    public function show(string $id): View|Factory|Application
    {
        $order = $this->productOrder->with(['customer', 'details'])->where('id', $id)->firstOrFail();

        return view('servicemanagement::admin.product-order.details', compact('order'));
    }

    public function statusUpdate(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'order_status' => 'required|in:pending,confirmed,processing,out_for_delivery,delivered,canceled']);

        $order = $this->productOrder->where('id', $id)->firstOrFail();
        $order->order_status = $request['order_status'];
        $order->save();

        Toastr::success(translate('order_status_updated_successfully'));
        return back();
    }
}
