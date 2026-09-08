<?php

namespace Modules\CategoryManagement\Http\Controllers\Web\Admin;

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
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\BlogModule\Jobs\RegenerateCategoryCatalogImageJob;
use Modules\BlogModule\Services\Seo\GeminiBlogGenerator;
use Modules\BlogModule\Support\CatalogImageHelper;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;


class CategoryController extends Controller
{

    private Variation $variation;
    private Zone $zone;
    private Category $category;

    use AuthorizesRequests;
    use StatusToggleGuardTrait;
    use UploadSizeHelperTrait;

    public function __construct(Category $category, Zone $zone, Variation $variation)
    {
        $this->category = $category;
        $this->zone = $zone;
        $this->variation = $variation;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('category_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParams = ['search' => $search, 'status' => $status];

        $categories = $this->category->withCount([
            'children',
            'zones' => function ($query) {
                $query->withoutGlobalScope('translate');
            }
        ])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($status != 'all', function ($query) use ($status) {
                $query->ofStatus($status == 'active' ? 1 : 0);
            })
            ->ofType('main')
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        $zones = $this->zone->where('is_active', 1)->withoutGlobalScope('translate')->get();
        $icons = collect(File::files(public_path('assets/images/')))
            ->map(fn($file) => $file->getFilename())
            ->values();

        return view('categorymanagement::admin.create', compact('categories', 'zones', 'search', 'status', 'icons'));
    }

    public function getTable(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $queryParams = ['search' => $search, 'status' => $status];

        $categories = Category::withCount([
            'children',
            'zones' => function ($query) {
                $query->withoutGlobalScope('translate');
            }
        ])
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', "%$key%");
                }
            })
            ->when($status != 'all', function ($query) use ($status) {
                $query->ofStatus($status == 'active' ? 1 : 0);
            })
            ->ofType('main')
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        $totalCategory = $categories->total();
        $categories->withPath(route('admin.category.create'));

        // Fallback logic: If current page has no data, go back one page
        if ($categories->isEmpty() && $page > 1) {
            $page = $page - 1;
            $request->merge(['page' => $page]);

            $categories = Category::withCount([
                'children',
                'zones' => function ($query) {
                    $query->withoutGlobalScope('translate');
                }
            ])
                ->when($search, function ($query) use ($search) {
                    $keys = explode(' ', $search);
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', "%$key%");
                    }
                })
                ->when($status != 'all', function ($query) use ($status) {
                    $query->ofStatus($status == 'active' ? 1 : 0);
                })
                ->ofType('main')
                ->latest()
                ->paginate(pagination_limit())
                ->appends($queryParams);
        }

        return response()->json([
            'view' => view('categorymanagement::admin.partials._table', compact('categories', 'search', 'status', 'totalCategory'))->render(),
            'totalCategory' => $totalCategory,
            'offset' => ($categories->currentPage() - 1) * $categories->perPage(),
            'page' => $categories->currentPage()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('category_add');

        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate(
            [
                'name' => 'required|unique:categories',
                'name.0' => 'required|unique:categories,name|max:191',
                'zone_ids' => 'required|array',
                'image' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
                'description' => 'nullable|string|max:2000',
                'starting_price' => 'nullable|numeric|min:0',
                'include_title' => 'nullable|array',
                'include_title.*' => 'nullable|string|max:255',
                'include_icon' => 'nullable|array',
                'include_icon.*' => 'nullable|string|max:191'
            ],
            [
                'name.0.required' => translate('default_name_is_required'),
                'name.0.max' => translate('Maximum 191 characters are allowed')
            ]
        );

        $category = $this->category;
        $category->name = $request->name[array_search('default', $request->lang)];
        $category->image = file_uploader('category/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
        $category->parent_id = 0;
        $category->position = 1;
        $category->description = $request->description;
        $category->starting_price = $request->starting_price;
        $category->includes = $this->buildIncludesPayload($request);
        $category->save();
        $category->zones()->sync($request->zone_ids);

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        $data = [];

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    $data[] = array(
                        'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                        'translationable_id' => $category->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $category->name,
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    $data[] = array(
                        'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                        'translationable_id' => $category->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    );
                }
            }
        }
        if (count($data)) {
            Translation::insert($data);
        }

        Toastr::success(translate(CATEGORY_STORE_200['key']));
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return View|Factory|Application|RedirectResponse
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|Application|RedirectResponse
    {
        $this->authorize('category_update');
        $category = $this->category->withoutGlobalScope('translate')->with([
            'zones' => function ($query) {
                $query->withoutGlobalScope('translate');
            }
        ])->ofType('main')->where('id', $id)->first();
        if (isset($category)) {
            $zones = $this->zone->where('is_active', 1)->withoutGlobalScope('translate')->get();
            $icons = collect(File::files(public_path('assets/images/')))
                ->map(fn($file) => $file->getFilename())
                ->values();
            return view('categorymanagement::admin.edit', compact('category', 'zones', 'icons'));
        }

        Toastr::error(translate(DEFAULT_204['key']));
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
        $this->authorize('category_update');

        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate(
            [
                'name' => 'required|unique:categories,name,' . $id,
                'name.0' => 'required|max:191|unique:categories,name,' . $id,
                'zone_ids' => 'required|array',
                'image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
                'description' => 'nullable|string|max:2000',
                'starting_price' => 'nullable|numeric|min:0',
                'include_title' => 'nullable|array',
                'include_title.*' => 'nullable|string|max:255',
                'include_icon' => 'nullable|array',
                'include_icon.*' => 'nullable|string|max:191'
            ],
            [
                'name.0.required' => translate('default_name_is_required'),
                'name.0.max' => translate('Maximum 191 characters are allowed')
            ]
        );

        $category = $this->category->withoutGlobalScope('translate')->ofType('main')->where('id', $id)->first();
        if (!$category) {
            return response()->json(response_formatter(CATEGORY_204), 204);
        }

        $oldName = $category->getRawOriginal('name');

        $category->name = $request->name[array_search('default', $request->lang)];

        if ($request->has('image')) {
            $category->image = file_uploader('category/', APPLICATION_IMAGE_FORMAT, $request->file('image'), $category->image);
        }

        $category->parent_id = 0;
        $category->position = 1;
        $category->description = $request->description;
        $category->starting_price = $request->starting_price;
        $category->includes = $this->buildIncludesPayload($request);
        $category->update();

        $category->zones()->sync($request->zone_ids);

        foreach ($request->lang as $index => $key) {
            if ($key == 'default') {
                continue;
            }

            $langValue = $request->name[$index] ?? null;

            if (empty($langValue) || $langValue === $oldName) {
                $langValue = $category->name;
            }

            Translation::updateOrInsert(
                [
                    'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                    'translationable_id' => $category->id,
                    'locale' => $key,
                    'key' => 'name'
                ],
                ['value' => $langValue]
            );
        }

        Toastr::success(translate(CATEGORY_UPDATE_200['key']));
        return redirect()->route('admin.category.create');
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
        $this->authorize('category_delete');
        $category = $this->category->ofType('main')->where('id', $id)->first();
        if (isset($category)) {
            if ($this->hasBlockingBookingsForCategory($category->id)) {
                Toastr::error(translate($this->getBlockedByBookingsActionMessage('category', 'delete')));
                return back();
            }

            file_remover('category/', $category->image);
            $category->zones()->sync([]);
            $category->translations()->delete();
            $category->delete();
            Toastr::success(translate(CATEGORY_DESTROY_200['key']));
            return back();
        }
        Toastr::success(translate(CATEGORY_204['key']));
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
        $this->authorize('category_manage_status');
        $category = $this->category->where('id', $id)->first();

        if (!$category) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        if ((int) $category->is_active === 1 && $this->hasBlockingBookingsForCategory($category->id)) {
            return $this->blockedByBookingsStatusToggleResponse('category');
        }

        $this->category->where('id', $id)->update(['is_active' => !$category->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function featuredUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('category_manage_status');
        $category = $this->category->where('id', $id)->first();
        $this->category->where('id', $id)->update(['is_featured' => !$category->is_featured]);

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function childes(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:all,active,inactive',
            'id' => 'required|uuid'
        ]);

        $childes = $this->category->when($request['status'] != 'all', function ($query) use ($request) {
            return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
        })->ofType('sub')->with(['zones'])->where('parent_id', $request['id'])->orderBY('name', 'asc')->paginate(pagination_limit());

        return response()->json(response_formatter(DEFAULT_200, $childes), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @return JsonResponse
     */
    public function ajaxChildes(Request $request, $id): JsonResponse
    {
        $categories = $this->category->ofStatus(1)->ofType('sub')->where('parent_id', $id)->orderBY('name', 'asc')->get();
        $category = $this->category->where('id', $id)->with(['zones'])->first();
        $zones = $category->zones;

        session()->put('category_wise_zones', $zones);

        $variants = $this->variation->where(['service_id' => $request['service_id']])->get();

        return response()->json([
            'template' => view('categorymanagement::admin.partials._childes-selector', compact('categories'))->render(),
            'template_for_zone' => view('servicemanagement::admin.partials._category-wise-zone', compact('zones'))->render(),
            'template_for_variant' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render(),
            'template_for_update_variant' => view('servicemanagement::admin.partials._update-variant-data', compact('zones', 'variants'))->render()
        ], 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function ajaxChildesOnly(Request $request, $id): JsonResponse
    {
        $categories = $this->category->ofStatus(1)->ofType('sub')->where('parent_id', $id)->orderBY('name', 'asc')->get();
        $subCategoryId = $request->sub_category_id ?? null;

        return response()->json([
            'template' => view('categorymanagement::admin.partials._childes-selector', compact('categories', 'subCategoryId'))->render()
        ], 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('category_export');
        $status = $request->input('status', 'all');
        $items = $this->category->withCount(['children', 'zones'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($status != 'all', function ($query) use ($status) {
                $query->ofStatus($status == 'active' ? 1 : 0);
            })
            ->ofType('main')
            ->latest()->latest()->get();

        $formatted = $items->map(function ($item, $key) {
            return [
                translate('SL') => $key + 1,
                translate('Category_Name') => $item->name,
                translate('Sub_Category_Count') => $item->children_count,
                translate('Zone_Count') => $item->zones_count,
                translate('Status') => $item->is_active ? translate('active') : translate('Inactive'),
                translate('Is_Featured') => $item->is_featured ? translate('Yes') : translate('No')
            ];
        });

        $fileName = 'categories_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($formatted))->download($fileName);
    }

    /**
     * Build category card includes: [{title, icon}, ...]
     */
    private function buildIncludesPayload(Request $request): array
    {
        $includes = [];
        if (!$request->filled('include_title')) {
            return $includes;
        }

        foreach ($request->include_title as $key => $title) {
            if ($title === null || trim((string) $title) === '') {
                continue;
            }
            $includes[] = [
                'title' => trim((string) $title),
                'icon' => $request->include_icon[$key] ?? null
            ];
        }

        return $includes;
    }

    /**
     * Replace category image with a Gemini catalog image (no text overlays).
     */
    public function regenerateAiImage(Request $request, string $id, GeminiBlogGenerator $generator): RedirectResponse
    {
        $this->authorize('category_update');

        $category = $this->category->with('translations')->find($id);
        if (!$category) {
            Toastr::error(translate(DEFAULT_204['key']));

            return back();
        }

        $sync = $request->boolean('sync', true);

        if (!$sync) {
            RegenerateCategoryCatalogImageJob::dispatch($category->id);
            Toastr::success(translate('AI image regeneration queued. Refresh in a minute.'));

            return back();
        }

        $tmp = $generator->createCategoryCatalogImage($category);
        if (!$tmp) {
            Toastr::error(translate('AI image generation failed') . ': ' . ($generator->getLastImageGenerationError() ?: translate('unknown error')));

            return back();
        }

        $saved = CatalogImageHelper::applyToCategory($category, $tmp);
        if (is_file($tmp)) {
            @unlink($tmp);
        }

        if (!$saved) {
            Toastr::error(translate('AI image generated but could not be saved') . ': ' . (CatalogImageHelper::getLastError() ?: translate('unknown error')));

            return back();
        }

        Toastr::success(translate('Category image replaced with AI catalog image'));

        return back();
    }
}
