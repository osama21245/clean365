<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use App\Traits\StatusToggleGuardTrait;
use App\Traits\UploadSizeHelperTrait;
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
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ReviewModule\Entities\Review;
use Modules\ReviewModule\Entities\ReviewReply;
use Modules\ServiceManagement\Entities\Faq;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Property;
use Modules\PromotionManagement\Entities\Discount;
use Modules\ServiceManagement\Entities\Tag;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;
use Modules\BlogModule\Jobs\GenerateSeoArticleForServiceJob;
use Modules\BlogModule\Jobs\RegenerateServiceCatalogImageJob;
use Modules\BlogModule\Services\Seo\GeminiBlogGenerator;
use Modules\BlogModule\Support\CatalogImageHelper;
use Modules\BlogModule\Support\BlogAutomationSettings;

use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;

class ServiceController extends Controller
{
    private Review $review;
    private ReviewReply $reviewReply;
    private Faq $faq;
    private Variation $variation;
    private Zone $zone;
    private Category $category;
    private Booking $booking;
    private Service $service;
    private Provider $provider;
    private Property $property;

    use AuthorizesRequests;
    use StatusToggleGuardTrait;
    use UploadSizeHelperTrait;

    public function __construct(Service $service, Booking $booking, Category $category, Zone $zone, Variation $variation, Faq $faq, Review $review, ReviewReply $reviewReply, Provider $provider, Property $property)
    {
        $this->service = $service;
        $this->booking = $booking;
        $this->category = $category;
        $this->zone = $zone;
        $this->variation = $variation;
        $this->faq = $faq;
        $this->review = $review;
        $this->reviewReply = $reviewReply;
        $this->provider = $provider;
        $this->property = $property;

    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('service_add');
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();
        $icons = collect(File::files(public_path('assets/images/')))

            ->map(function ($file) {
                return $file->getFilename();
            });
        $properties = $this->property->ofStatus(1)->latest()->get();

        session()->forget('variations');

        return view('servicemanagement::admin.create', compact('categories', 'zones', 'icons', 'properties'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|Application
    {
        $this->authorize('service_view');
        $request->validate([
            'status' => 'in:active,inactive,all',
            'zone_id' => 'uuid'
        ]);

        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParam = ['search' => $search, 'status' => $status];

        $services = $this->service->with(['category.zonesBasicInfo'])->withCount('bookings')->latest()
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->has('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })->when($request->has('sub_category_id'), function ($query) use ($request) {
                return $query->where('sub_category_id', $request->sub_category_id);
            })->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                if ($request['status'] == 'active') {
                    return $query->where(['is_active' => 1]);
                } else {
                    return $query->where(['is_active' => 0]);
                }
            })->when($request->has('zone_id'), function ($query) use ($request) {
                return $query->whereHas('category.zonesBasicInfo', function ($queryZone) use ($request) {
                    $queryZone->where('zone_id', $request['zone_id']);
                });
            })->paginate(pagination_limit())->appends($queryParam);

        return view('servicemanagement::admin.list', compact('services', 'search', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('service_add');

        $check = $this->validateUploadedFile($request, ['cover_image', 'thumbnail']);
        if ($check !== true) {
            return $check;
        }

        $variations = session('variations');
        session()->forget('variations');

        $request->validate(
            [
                'name' => 'required|max:191',
                'name.0' => 'required|max:191',
                'category_id' => 'required|uuid',
                'sub_category_id' => 'nullable|uuid',
                'property_id' => 'required|uuid|exists:properties,id,is_active,1',

                'cover_image' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
                'thumbnail' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
                'description' => 'required',
                'description.0' => 'required',
                'short_description' => 'required',
                'short_description.0' => 'required',
                'tax' => 'required|numeric|min:0|max:100',
                'min_bidding_price' => 'required|numeric|min:0|not_in:0',
                'service_includes' => 'nullable|array',
                'service_includes.*' => 'nullable|string|max:255',
                'suitable_title' => 'nullable|array',
                'suitable_title.*' => 'nullable|string|max:255',

                'suitable_icon' => 'nullable|array',
                'suitable_icon.*' => 'nullable|string',
                'visits_count' => 'required|integer|min:1',
                'has_discount' => 'nullable|in:0,1',
                'discount_amount_type' => 'required_if:has_discount,1|nullable|in:percent,amount',
                'discount_amount' => 'required_if:has_discount,1|nullable|numeric|min:0',
                'min_purchase' => 'required_if:has_discount,1|nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'discount_start_date' => 'required_if:has_discount,1|nullable|date',
                'discount_end_date' => 'required_if:has_discount,1|nullable|date|after_or_equal:discount_start_date'
            ]

        );


        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }
        $suitableFor = [];

        if ($request->filled('suitable_title')) {

            foreach ($request->suitable_title as $key => $title) {

                if (!empty($title)) {

                    $suitableFor[] = [
                        'title' => $title,
                        'icon' => $request->suitable_icon[$key] ?? null
                    ];
                }
            }
        }
        $service = $this->service;
        $service->suitable_for = $suitableFor;

        $service->name = $request->name[array_search('default', $request->lang)];
        $service->category_id = $request->category_id;
        $service->sub_category_id = $request->sub_category_id ?: null;
        $service->property_id = $request->property_id;

        $service->short_description = $request->short_description[array_search('default', $request->lang)];
        $service->description = $request->description[array_search('default', $request->lang)];
        $service->cover_image = file_uploader('service/', 'png', $request->file('cover_image'));
        $service->thumbnail = file_uploader('service/', 'png', $request->file('thumbnail'));
        $service->tax = $request->tax;
        $service->min_bidding_price = $request->min_bidding_price;
        $service->service_includes = $request->service_includes;
        $service->visits_count = $request->visits_count;

        $service->save();
        $service->tags()->sync($tagIds);

        $data = $request->all();

        $variationFormat = [];
        if ($variations) {
            $zones = $this->zone->ofStatus(1)->latest()->get();
            foreach ($variations as $item) {
                foreach ($zones as $zone) {
                    $variationFormat[] = [
                        'variant' => $item['variant'],
                        'variant_key' => $item['variant_key'],
                        'zone_id' => $zone->id,
                        'price' => $data[$item['variant_key'] . '_' . $zone->id . '_price'] ?? 0,
                        'service_id' => $service->id
                    ];
                }
            }
        }

        $service->variations()->createMany($variationFormat);
        $this->syncServiceDiscount($service, $request);

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $service->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'
                        ],
                        ['value' => $service->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'
                        ],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $service->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(SERVICE_STORE_200['key']));

        if (BlogAutomationSettings::enabled()) {
            GenerateSeoArticleForServiceJob::dispatch($service->id);
        }

        return redirect()->route('admin.service.index');
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function show(Request $request, string $id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('service_view');
        $service = $this->service
            ->where('id', $id)
            ->with([
                'category' => function ($query) {
                    $query->ofStatus(1);
                },
                'subCategory' => function ($query) {
                    $query->ofStatus(1);
                },
                'category.zones',
                'category.children',
                'variations.zone',
                'reviews'
            ])
            ->withCount(['bookings'])
            ->first();

        $service->total_review_count = $service?->reviews?->avg('review_rating') ?? 0;

        $ongoing = $this->booking
            ->whereHas('detail', function ($query) use ($id) {
                return $query->where('service_id', $id);
            })
            ->where(['booking_status' => 'ongoing'])
            ->count();

        $canceled = $this->booking
            ->whereHas('detail', function ($query) use ($id) {
                return $query->where('service_id', $id);
            })
            ->where(['booking_status' => 'canceled'])
            ->count();

        $faqs = $this->faq->latest()->where('service_id', $id)->get();

        $search = $request->has('review_search') ? $request['review_search'] : '';
        $webPage = $request->has('review_page') || $request->has('review_search') ? 'review' : 'general';
        $queryParam = ['search' => $search, 'web_page' => $webPage];

        $reviews = $this->review->with(['customer', 'booking'])
            ->where('service_id', $id)
            ->when($request->has('review_search') && !empty($request['review_search']), function ($query) use ($request) {
                $keys = explode(' ', $request['review_search']);
                foreach ($keys as $key) {
                    $query->where('review_comment', 'LIKE', '%' . $key . '%')
                        ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()->paginate(pagination_limit(), ['*'], 'review_page')->appends($queryParam);

        $rating_group_count = DB::table('reviews')
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        if (isset($service)) {
            $service['ongoing_count'] = $ongoing;
            $service['canceled_count'] = $canceled;
            return view('servicemanagement::admin.detail', compact('service', 'faqs', 'reviews', 'rating_group_count', 'webPage', 'search'));
        }

        Toastr::error(translate(DEFAULT_204['key']));
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('service_update');
        $service = $this->service->withoutGlobalScope('translate')->where('id', $id)->with(['category.children', 'category.zones', 'variations'])->first();
        if (isset($service)) {
            $editingVariants = $service->variations->pluck('variant_key')->unique()->toArray();
            session()->put('editing_variants', $editingVariants);
            $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
            $properties = $this->property->ofStatus(1)->latest()->get();

            $category = $this->category->where('id', $service->category_id)->with(['zones'])->first();
            $zones = $category->zones ?? [];
            session()->put('category_wise_zones', $zones);

            $tagNames = [];
            if ($service->tags) {
                foreach ($service->tags as $tag) {
                    $tagNames[] = $tag['tag'];
                }
            }

            session()->forget('variations');
            $icons = collect(File::files(public_path('assets/images')))
                ->map(fn($file) => $file->getFilename());
            $discount = $this->findServiceOwnedDiscount($service->id);

            return view('servicemanagement::admin.edit', compact('categories', 'zones', 'service', 'tagNames', 'icons', 'properties', 'discount'));
        }

        Toastr::info(translate(DEFAULT_204['key']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse|RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $this->authorize('service_update');

        $check = $this->validateUploadedFile($request, ['cover_image', 'thumbnail']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name' => 'required|max:191',
            'name.0' => 'required|max:191',
            'category_id' => 'required|uuid',
            'sub_category_id' => 'nullable|uuid',
            'description' => 'required',
            'description.0' => 'required',
            'short_description' => 'required',
            'short_description.0' => 'required',
            'tax' => 'required|numeric|min:0',
            'variants' => 'required|array',
            'min_bidding_price' => 'required|numeric|min:0|not_in:0',
            'property_id' => 'required|uuid|exists:properties,id,is_active,1',

            'cover_image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'thumbnail' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'service_includes' => 'nullable|array',
            'service_includes.*' => 'nullable|string|max:255',
            'suitable_title' => 'nullable|array',
            'suitable_title.*' => 'nullable|string|max:255',
            'visits_count' => 'required|integer|min:1',

            'suitable_icon' => 'nullable|array',
            'suitable_icon.*' => 'nullable|string',
            'has_discount' => 'nullable|in:0,1',
            'discount_amount_type' => 'required_if:has_discount,1|nullable|in:percent,amount',
            'discount_amount' => 'required_if:has_discount,1|nullable|numeric|min:0',
            'min_purchase' => 'required_if:has_discount,1|nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'discount_start_date' => 'required_if:has_discount,1|nullable|date',
            'discount_end_date' => 'required_if:has_discount,1|nullable|date|after_or_equal:discount_start_date'
        ]);

        $service = $this->service->find($id);
        if (!isset($service)) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }
        $suitableFor = [];

        if ($request->filled('suitable_title')) {

            foreach ($request->suitable_title as $key => $title) {

                if (!empty($title)) {

                    $suitableFor[] = [
                        'title' => $title,
                        'icon' => $request->suitable_icon[$key] ?? null
                    ];
                }
            }
        }
        $service->service_includes = $request->service_includes;
        $service->suitable_for = $suitableFor;
        $service->name = $request->name[array_search('default', $request->lang)];
        $service->category_id = $request->category_id;
        $service->sub_category_id = $request->sub_category_id ?: null;
        $service->short_description = $request->short_description[array_search('default', $request->lang)];
        ;
        $service->description = $request->description[array_search('default', $request->lang)];
        $service->property_id = $request->property_id;

        if ($request->has('cover_image')) {
            $service->cover_image = file_uploader('service/', 'png', $request->file('cover_image'));
        }

        if ($request->has('thumbnail')) {
            $service->thumbnail = file_uploader('service/', 'png', $request->file('thumbnail'));
        }
        $service->visits_count = $request->visits_count;

        $service->tax = $request->tax;
        $service->min_bidding_price = $request->min_bidding_price;
        $service->save();
        $service->tags()->sync($tagIds);

        $service->variations()->delete();

        $data = $request->all();

        $variationFormat = [];
        $zones = $this->zone->latest()->get();
        foreach ($data['variants'] as $item) {
            foreach ($zones as $zone) {
                $variationFormat[] = [
                    'variant' => str_replace('_', ' ', $item),
                    'variant_key' => $item,
                    'zone_id' => $zone->id,
                    'price' => $data[$item . '_' . $zone->id . '_price'] ?? 0,
                    'service_id' => $service->id
                ];
            }
        }

        $service->variations()->createMany($variationFormat);
        session()->forget('variations');
        session()->forget('editing_variants');

        $this->syncServiceDiscount($service, $request);

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $service->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'
                        ],
                        ['value' => $service->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'
                        ],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $service->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }


        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return redirect()->route('admin.service.index');

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
        $this->authorize('service_delete');
        $service = $this->service->where('id', $id)->first();
        if (isset($service)) {
            if ($this->hasBlockingBookingsForService($service->id)) {
                Toastr::error(translate($this->getBlockedByBookingsActionMessage('service', 'delete')));
                return back();
            }

            foreach (['thumbnail', 'cover_image'] as $item) {
                file_remover('service/', $service[$item]);
            }
            $service->translations()->delete();
            $service->variations()->delete();
            $service->delete();

            Toastr::success(translate(DEFAULT_DELETE_200['key']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['key']));
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
        $this->authorize('service_manage_status');
        $service = $this->service->where('id', $id)->first();

        if (!$service) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        if ((int) $service->is_active === 1 && $this->hasBlockingBookingsForService($service->id)) {
            return $this->blockedByBookingsStatusToggleResponse('service');
        }

        $this->service->where('id', $id)->update(['is_active' => !$service->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function reviewStatusUpdate(Request $request, $id): JsonResponse
    {
        $review = $this->review->where('id', $id)->first();
        $this->review->where('id', $id)->update(['is_active' => !$review->is_active]);

        foreach (['service_id' => $review->service_id, 'provider_id' => $review->provider_id] as $key => $value) {
            $ratingGroupCount = DB::table('reviews')->where($key, $value)->where('is_active', 1)
                ->select('review_rating', DB::raw('count(*) as total'))
                ->groupBy('review_rating')
                ->get();

            $totalRating = 0;
            $ratingCount = 0;
            foreach ($ratingGroupCount as $count) {
                $totalRating += round($count->review_rating * $count->total, 2);
                $ratingCount += $count->total;
            }

            $query = collect([]);
            if ($key == 'service_id') {
                $query = $this->service->where(['id' => $value]);
            } elseif ($key == 'provider_id') {
                $query = $this->provider->where(['id' => $value]);
            }

            // Check if $ratingCount is greater than 0 before calculating the average rating
            if ($ratingCount > 0) {
                $avgRating = round($totalRating / $ratingCount, 2);
            } else {
                $avgRating = 0; // Handle cases where there are no ratings
            }

            $query->update([
                'rating_count' => $ratingCount,
                'avg_rating' => $avgRating
            ]);
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }


    public function ajaxAddVariant(Request $request): JsonResponse
    {
        $variation = [
            'variant' => $request['name'],
            'variant_key' => str_replace(' ', '-', $request['name']),
            'price' => $request['price']
        ];

        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
        $existingData = session()->has('variations') ? session('variations') : [];
        $editingVariants = session()->has('editing_variants') ? session('editing_variants') : [];

        if (!self::searchForKey($request['name'], $existingData) && !in_array(str_replace(' ', '-', $request['name']), $editingVariants)) {
            $existingData[] = $variation;
            session()->put('variations', $existingData);
        } else {
            return response()->json(['flag' => 0, 'message' => translate('already_exist')]);
        }

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render()]);
    }

    public function ajaxRemoveVariant(Request $request)
    {
        $variant_key = $request->input('variant_key', '');
        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
        $existingData = session()->has('variations') ? session('variations') : [];

        $filtered = collect($existingData)->filter(function ($values) use ($variant_key) {
            return $values['variant_key'] != $variant_key;
        })->values()->toArray();

        session()->put('variations', $filtered);

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render()]);
    }

    public function ajaxDeleteDbVariant(Request $request, $service_id)
    {
        $variant_key = $request->input('variant_key', '');
        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : $this->zone->ofStatus(1)->latest()->get();
        $this->variation->where(['variant_key' => $variant_key, 'service_id' => $service_id])->delete();
        $variants = $this->variation->where(['service_id' => $service_id])->get();

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::admin.partials._update-variant-data', compact('zones', 'variants'))->render()]);
    }

    function searchForKey($variant, $array): int|string|null
    {
        foreach ($array as $key => $val) {
            if ($val['variant'] === $variant) {
                return true;
            }
        }
        return false;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('service_export');
        $items = $this->service->with(['category.zonesBasicInfo'])->latest()
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->has('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })->when($request->has('sub_category_id'), function ($query) use ($request) {
                return $query->where('sub_category_id', $request->sub_category_id);
            })->when($request->has('zone_id'), function ($query) use ($request) {
                return $query->whereHas('category.zonesBasicInfo', function ($queryZone) use ($request) {
                    $queryZone->where('zone_id', $request['zone_id']);
                });
            })->latest()->get();

        $formatted = $items->map(function ($item, $key) {
            $zones = $item->category?->zonesBasicInfo?->pluck('name')->implode(', ') ?? '';
            return [
                translate('SL') => $key + 1,
                translate('Name') => $item->name,
                translate('Category') => $item->category?->name,
                translate('Zones') => $zones,
                translate('Minimum Bidding Price') => with_currency_symbol($item->min_bidding_price ?? 0),
                translate('Status') => $item->is_active ? translate('active') : translate('Inactive')
            ];
        });

        $fileName = 'services_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($formatted))->download($fileName);
    }

    public function reviewsDownload(Request $request)
    {
        $items = $this->review->with(['customer', 'booking', 'reviewReply'])
            ->when($request->has('review_search') && !empty($request['review_search']), function ($query) use ($request) {
                $keys = explode(' ', $request['review_search']);
                foreach ($keys as $key) {
                    $query->where('review_comment', 'LIKE', '%' . $key . '%')
                        ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->where('service_id', $request->service_id)
            ->latest()
            ->get();

        $formatted = $items->map(function ($item, $key) {
            return [
                translate('SL') => $key + 1,
                translate('Review ID') => $item->readable_id == 0 ? 'N/A' : $item->readable_id,
                translate('Reviewer') => $item->customer ? trim(($item->customer->first_name ?? '') . ' ' . ($item->customer->last_name ?? '')) : translate('Customer_not_available'),
                translate('Booking ID') => $item->booking?->readable_id ?? 'N/A',
                translate('Date') => $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d-M-Y') : '',
                translate('Ratings') => $item->review_rating,
                translate('Reviews') => filled($item->review_comment) ? $item->review_comment : translate('No review yet'),
                translate('Reply') => filled($item->reviewReply?->reply) ? $item->reviewReply->reply : translate('No reply yet'),
                translate('Status') => $item->is_active ? translate('active') : translate('Inactive')
            ];
        });

        $fileName = 'service_reviews_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($formatted))->download($fileName);
    }
    private function findServiceOwnedDiscount(string $serviceId): ?Discount
    {
        return Discount::query()
            ->where('promotion_type', 'discount')
            ->where('discount_type', 'service')
            ->whereHas('discount_types', function ($query) use ($serviceId) {
                $query->where('discount_type', 'service')
                    ->where('type_wise_id', $serviceId);
            })
            ->latest()
            ->first();
    }

    private function syncServiceDiscount(Service $service, Request $request): void
    {
        $hasDiscount = (string) $request->input('has_discount', '0') === '1';
        $discount = $this->findServiceOwnedDiscount($service->id);

        if (!$hasDiscount) {
            if ($discount) {
                $discount->is_active = 0;
                $discount->save();
            }
            return;
        }

        $amountType = $request->input('discount_amount_type', 'percent');
        $amount = (float) $request->input('discount_amount', 0);
        $minPurchase = (float) $request->input('min_purchase', 0);
        $maxDiscount = $amountType === 'percent'
            ? (float) ($request->input('max_discount_amount') ?: 0)
            : 0;

        if ($amountType === 'percent' && $amount > 100) {
            $amount = 100;
        }

        DB::transaction(function () use ($service, $request, $discount, $amountType, $amount, $minPurchase, $maxDiscount) {
            if (!$discount) {
                $discount = new Discount();
            }

            $discount->discount_type = 'service';
            $discount->discount_title = ($service->name ?: 'Service') . ' Discount';
            $discount->discount_amount = $amount;
            $discount->discount_amount_type = $amountType;
            $discount->min_purchase = $minPurchase;
            $discount->max_discount_amount = $maxDiscount;
            $discount->promotion_type = 'discount';
            $discount->start_date = $request->input('discount_start_date');
            $discount->end_date = $request->input('discount_end_date');
            $discount->is_active = 1;
            $discount->save();

            $discount->discount_types()->delete();

            $types = [
                [
                    'discount_id' => $discount->id,
                    'discount_type' => 'service',
                    'type_wise_id' => $service->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            $zoneIds = $this->zone->ofStatus(1)->pluck('id');
            foreach ($zoneIds as $zoneId) {
                $types[] = [
                    'discount_id' => $discount->id,
                    'discount_type' => 'zone',
                    'type_wise_id' => $zoneId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $discount->discount_types()->createMany($types);
        });
    }

    /**
     * Replace service thumbnail + cover with a Gemini catalog image (no text overlays).
     */
    public function regenerateAiImage(Request $request, string $id, GeminiBlogGenerator $generator): RedirectResponse
    {
        $this->authorize('service_update');

        $service = $this->service->with(['category', 'translations', 'category.translations'])->find($id);
        if (!$service) {
            Toastr::error(translate(DEFAULT_204['key']));

            return back();
        }

        $sync = $request->boolean('sync', true);

        if (!$sync) {
            RegenerateServiceCatalogImageJob::dispatch($service->id);
            Toastr::success(translate('AI image regeneration queued. Refresh in a minute.'));

            return back();
        }

        $tmp = $generator->createServiceCatalogImage($service);
        if (!$tmp) {
            Toastr::error(translate('AI image generation failed') . ': ' . ($generator->getLastImageGenerationError() ?: translate('unknown error')));

            return back();
        }

        $saved = CatalogImageHelper::applyToService($service, $tmp, replaceBoth: true);
        if (is_file($tmp)) {
            @unlink($tmp);
        }

        if (!$saved) {
            Toastr::error(translate('AI image generated but could not be saved') . ': ' . (CatalogImageHelper::getLastError() ?: translate('unknown error')));

            return back();
        }

        Toastr::success(translate('Service images replaced with AI catalog image'));

        return back();
    }
}
