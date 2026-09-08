<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DiscountController extends Controller
{

    protected $discount, $service, $category, $zone, $discount_types;
    use AuthorizesRequests;

    public function __construct(Discount $discount, Service $service, Category $category, Zone $zone, DiscountType $discount_types)
    {
        $this->discountQuery = $discount->ofPromotionTypes('discount');
        $this->discount = $discount;
        $this->service = $service;
        $this->category = $category;
        $this->zone = $zone;
        $this->discount_types = $discount_types;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|Application
    {

        $this->authorize('discount_view');

        $search = $request->has('search') ? $request['search'] : '';
        $type = $request->has('type') ? $request['type'] : 'all';
        $queryParam = ['search' => $search, 'type' => $type];

        $discounts = $this->discountQuery->with(['category_types', 'service_types', 'zone_types', 'additional_service_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('discount_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($type != 'all', function ($query) use ($type) {
                return $query->where(['discount_type' => $type]);
            })->orderBy('created_at', 'desc')->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::admin.discounts.list', compact('discounts', 'search', 'type'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('discount_add');
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->withoutGlobalScope('translate')->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();
        $additional_services = \Modules\ServiceManagement\Entities\AdditionalService::active()->orderBy('sort_order', 'asc')->get();

        return view('promotionmanagement::admin.discounts.create', compact('categories', 'zones', 'services', 'additional_services'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('discount_add');
        $request->validate([
            'discount_type' => 'required|in:category,service,zone,mixed,additional_service',
            'discount_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $amountType = $request['discount_amount_type'];
                    $minPurchaseAmount = $request['min_purchase'];
                    if ($amountType === 'amount' && $value <= 0) {
                        $fail(translate('The discount amount  value must be gather than 0 '));
                    }
                    if ($amountType === 'percent' && $value <= 0) {
                        $fail(translate('The discount percent value must be gather than 0 '));
                    }

                    if ($amountType === 'percent' && $value > 100) {
                        $fail(translate('Discount percent value must be less than 100% '));
                    }
                    if ($amountType !== 'percent' && $value >= $minPurchaseAmount) {
                        $fail(translate('Discount amount should be less than minimum purchase amount'));
                    }
                }
            ],
            'discount_title' => 'required|string',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric|gt:0',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_ids' => 'array',
            'service_ids' => 'array',
            'additional_service_ids' => 'array',
            'zone_ids' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {
            $discount = $this->discount;
            $discount->discount_type = $request['discount_type'];
            $discount->discount_title = $request['discount_title'];
            $discount->discount_amount = $request['discount_amount'];
            $discount->discount_amount_type = $request['discount_amount_type'];
            $discount->min_purchase = $request['min_purchase'];
            $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
            $discount->promotion_type = 'discount';
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->is_active = 1;
            $discount->save();

            if ($request['discount_type'] == 'category') {
                $disTypes = ['category', 'zone'];
            } elseif ($request['discount_type'] == 'service') {
                $disTypes = ['service', 'zone'];
            } elseif ($request['discount_type'] == 'additional_service') {
                $disTypes = ['additional_service', 'zone'];
            } else {
                $disTypes = ['category', 'service', 'additional_service', 'zone'];
            }

            foreach ((array) $disTypes as $disType) {
                $types = [];
                foreach ((array) $request[$disType . '_ids'] as $id) {
                    $types[] = [
                        'discount_id' => $discount['id'],
                        'discount_type' => $disType,
                        'type_wise_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $discount->discount_types()->createMany($types);
            }
        });

        Toastr::success(translate(DISCOUNT_CREATE_200['key']));
        return redirect()->route('admin.discount.list');
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|Application
    {
        $this->authorize('discount_update');
        $discount = $this->discountQuery->with(['category_types', 'service_types', 'zone_types', 'additional_service_types'])->where('id', $id)->first();
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->withoutGlobalScope('translate')->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();
        $additional_services = \Modules\ServiceManagement\Entities\AdditionalService::active()->orderBy('sort_order', 'asc')->get();

        return view('promotionmanagement::admin.discounts.edit', compact('categories', 'zones', 'services', 'additional_services', 'discount'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('discount_update');
        $request->validate([
            'discount_type' => 'required|in:category,service,zone,mixed,additional_service',
            'min_purchase' => 'required|numeric|gt:0',
            'discount_title' => 'required|string',
            'discount_amount_type' => 'required|in:percent,amount',
            'discount_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $amountType = $request['discount_amount_type'];
                    $minPurchaseAmount = $request['min_purchase'];
                    if ($amountType === 'amount' && $value <= 0) {
                        $fail(translate('The discount amount  value must be gather than 0 '));
                    }
                    if ($amountType === 'percent' && $value <= 0) {
                        $fail(translate('The discount percent value must be gather than 0 '));
                    }

                    if ($amountType === 'percent' && $value > 100) {
                        $fail(translate('Discount percent value must be less than 100% '));
                    }
                    if ($amountType !== 'percent' && $value >= $minPurchaseAmount) {
                        $fail(translate('Discount amount should be less than minimum purchase amount'));
                    }
                }
            ],
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_ids' => 'array',
            'service_ids' => 'array',
            'additional_service_ids' => 'array',
            'zone_ids' => 'required|array'
        ]);

        $discount = $this->discountQuery->where(['id' => $id])->first();

        if (isset($discount)) {
            DB::transaction(function () use ($request, $id, $discount) {

                $discount->discount_type = $request['discount_type'];
                $discount->discount_title = $request['discount_title'];
                $discount->discount_amount = $request['discount_amount'];
                $discount->discount_amount_type = $request['discount_amount_type'];
                $discount->min_purchase = $request['min_purchase'];
                $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
                $discount->promotion_type = 'discount';
                $discount->start_date = $request['start_date'];
                $discount->end_date = $request['end_date'];
                $discount->is_active = 1;
                $discount->save();

                $discount->discount_types()->delete();

                if ($request['discount_type'] == 'category') {
                    $disTypes = ['category', 'zone'];
                } elseif ($request['discount_type'] == 'service') {
                    $disTypes = ['service', 'zone'];
                } elseif ($request['discount_type'] == 'additional_service') {
                    $disTypes = ['additional_service', 'zone'];
                } else {
                    $disTypes = ['category', 'service', 'additional_service', 'zone'];
                }

                foreach ((array) $disTypes as $disType) {
                    $types = [];
                    foreach ((array) $request[$disType . '_ids'] as $id) {
                        $types[] = [
                            'discount_id' => $discount['id'],
                            'discount_type' => $disType,
                            'type_wise_id' => $id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    $discount->discount_types()->createMany($types);
                }
            });
        }

        Toastr::success(translate(DISCOUNT_UPDATE_200['key']));
        return redirect()->route('admin.discount.list');
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('discount_delete');
        $discount = $this->discountQuery->where('id', $id)->first();
        if ($discount) {
            $this->discount_types->where(['discount_id' => $id])->delete();
            $discount->delete();
        }
        Toastr::success(translate(DEFAULT_DELETE_200['key']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('discount_manage_status');
        $discount = $this->discountQuery->where('id', $id)->first();
        $this->discountQuery->where('id', $id)->update(['is_active' => !$discount->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request)
    {
        $this->authorize('discount_export');
        $items = $this->discountQuery->with(['category_types', 'service_types', 'zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('discount_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->filled('type') && $request->type !== 'all', function ($query) use ($request) {
                $query->where('discount_type', $request->type);
            })
            ->latest()->get();

        $formatted = $items->map(function ($item, $key) {
            $zones = collect($item->zone_types ?? [])
                ->map(fn($type) => $type->zone?->name)
                ->filter()
                ->implode(', ');

            return [
                translate('SL') => $key + 1,
                translate('title') => $item->discount_title ?? '',
                translate('discount_type') => $item->discount_type ?? '',
                translate('zones') => $zones,
                translate('status') => $item->is_active ? translate('Active') : translate('Inactive')
            ];
        });

        $fileName = 'discounts_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($formatted))->download($fileName);
    }
}
