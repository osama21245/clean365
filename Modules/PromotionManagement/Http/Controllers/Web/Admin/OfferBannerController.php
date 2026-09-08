<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\OfferBanner;
use Modules\ServiceManagement\Entities\Service;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OfferBannerController extends Controller
{
    private OfferBanner $offerBanner;
    private Category $category;
    private Service $service;

    use AuthorizesRequests;
    use UploadSizeHelperTrait;

    public function __construct(OfferBanner $offerBanner, Category $category, Service $service)
    {
        $this->offerBanner = $offerBanner;
        $this->category = $category;
        $this->service = $service;
    }

    /**
     * Display a listing of offer banners.
     */
    public function create(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $resourceType = $request->has('resource_type') ? $request['resource_type'] : 'all';
        $queryParam = ['search' => $search, 'resource_type' => $resourceType];

        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $services = $this->service->active()->latest()->get();

        $offerBanners = $this->offerBanner->with(['service', 'category'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%')
                            ->orWhere('subtitle', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('resource_type') && $request['resource_type'] != 'all', function ($query) use ($request) {
                return $query->where(['resource_type' => $request['resource_type']]);
            })->latest()->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::admin.offer-banners.create', compact('offerBanners', 'services', 'categories', 'resourceType', 'search'));
    }

    /**
     * Store a newly created offer banner.
     */
    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title' => 'required|string|max:190',
            'subtitle' => 'nullable|string',
            'original_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'tag' => 'nullable|string|max:190',
            'discount_badge' => 'nullable|string|max:190',
            'coupon_code' => 'nullable|string|max:190',
            'service_id' => 'required_if:resource_type,service|nullable|uuid',
            'category_id' => 'required_if:resource_type,category|nullable|uuid',
            'resource_type' => 'required|in:service,category,link',
            'image' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $banner = new OfferBanner();
        $banner->title = $request['title'];
        $banner->subtitle = $request['subtitle'];
        $banner->original_price = $request['original_price'];
        $banner->offer_price = $request['offer_price'];
        $banner->tag = $request['tag'];
        $banner->discount_badge = $request['discount_badge'];
        $banner->coupon_code = $request['coupon_code'];
        $banner->redirect_link = $request['redirect_link'];
        $banner->resource_type = $request['resource_type'];

        if ($request['resource_type'] != 'link') {
            $resourceId = $request['resource_type'] == 'service' ? $request['service_id'] : $request['category_id'];
        } else {
            $resourceId = null;
        }
        $banner->resource_id = $resourceId;
        $banner->image = file_uploader('offer_banner/', 'png', $request->file('image'));
        $banner->is_active = 1;
        $banner->save();

        Toastr::success(translate('Offer banner created successfully'));
        return back();
    }

    /**
     * Show edit form.
     */
    public function edit(string $id): View|Factory|Application
    {
        $banner = $this->offerBanner->with(['service', 'category'])->where('id', $id)->firstOrFail();
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $services = $this->service->active()->latest()->get();

        return view('promotionmanagement::admin.offer-banners.edit', compact('categories', 'services', 'banner'));
    }

    /**
     * Update offer banner.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title' => 'required|string|max:190',
            'subtitle' => 'nullable|string',
            'original_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'tag' => 'nullable|string|max:190',
            'discount_badge' => 'nullable|string|max:190',
            'coupon_code' => 'nullable|string|max:190',
            'resource_type' => 'required|in:service,category,link',
            'service_id' => 'required_if:resource_type,service|nullable|uuid',
            'category_id' => 'required_if:resource_type,category|nullable|uuid',
            'image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $banner = $this->offerBanner->where(['id' => $id])->firstOrFail();
        $banner->title = $request['title'];
        $banner->subtitle = $request['subtitle'];
        $banner->original_price = $request['original_price'];
        $banner->offer_price = $request['offer_price'];
        $banner->tag = $request['tag'];
        $banner->discount_badge = $request['discount_badge'];
        $banner->coupon_code = $request['coupon_code'];
        $banner->redirect_link = $request['redirect_link'];
        $banner->resource_type = $request['resource_type'];

        if ($request['resource_type'] != 'link') {
            $resourceId = $request['resource_type'] == 'service' ? $request['service_id'] : $request['category_id'];
        } else {
            $resourceId = null;
        }
        $banner->resource_id = $resourceId;

        if ($request->hasFile('image')) {
            $banner->image = file_uploader('offer_banner/', 'png', $request->file('image'), $banner->image);
        }

        $banner->save();

        Toastr::success(translate('Offer banner updated successfully'));
        return redirect()->route('admin.offer-banner.create');
    }

    /**
     * Delete offer banner.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $banner = $this->offerBanner->where('id', $id)->first();

        if (isset($banner)) {
            file_remover('offer_banner/', $banner['image']);
            $banner->delete();
        }
        Toastr::success(translate('Offer banner deleted successfully'));
        return back();
    }

    /**
     * Update status.
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $banner = $this->offerBanner->where('id', $id)->firstOrFail();
        $banner->is_active = !$banner->is_active;
        $banner->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }
}
