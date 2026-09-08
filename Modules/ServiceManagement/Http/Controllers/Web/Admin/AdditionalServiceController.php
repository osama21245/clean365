<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Display a listing of additional services.
     *
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        $services = $this->additionalService
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('features', 'LIKE', "%{$search}%");
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('is_active', $status === 'active' ? 1 : 0);
            })
            ->orderBy('sort_order', 'asc')
            ->paginate(pagination_limit())
            ->appends($request->all());

        return view('servicemanagement::admin.additional-services.index', compact('services', 'search', 'status'));
    }

    /**
     * Store a newly created additional service.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'price' => 'nullable|numeric|min:0',
            'feature_titles' => 'nullable|array',
            'feature_prices' => 'nullable|array',
            'feature_icons.*' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'icon' => 'nullable|image|max:2048',
        ]);

        $iconName = null;
        if ($request->hasFile('icon')) {
            $iconName = file_uploader('additional_service/', APPLICATION_IMAGE_FORMAT, $request->file('icon'));
        }

        $features = $this->parseFeaturesWithIcons($request);

        $service = new AdditionalService();
        $service->name = $request->name;
        $service->price = $request->price ?? 0;
        $service->features = $features;
        $service->sort_order = $request->sort_order ?? 0;
        $service->is_active = true;
        if ($iconName) {
            $service->icon = $iconName;
        }
        $service->save();

        Toastr::success(translate('تمت إضافة الخدمة بنجاح'));
        return back();
    }

    /**
     * Show edit form for specified resource.
     *
     * @param string $id
     * @return Renderable
     */
    public function edit(string $id): Renderable
    {
        $service = $this->additionalService->findOrFail($id);

        return view('servicemanagement::admin.additional-services.edit', compact('service'));
    }

    /**
     * Update specified resource in storage.
     *
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $service = $this->additionalService->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:191',
            'price' => 'nullable|numeric|min:0',
            'feature_titles' => 'nullable|array',
            'feature_prices' => 'nullable|array',
            'feature_icons.*' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'icon' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            $service->icon = file_uploader('additional_service/', APPLICATION_IMAGE_FORMAT, $request->file('icon'), $service->icon);
        }

        $service->name = $request->name;
        $service->price = $request->price ?? 0;
        $service->features = $this->parseFeaturesWithIcons($request);
        $service->sort_order = $request->sort_order ?? 0;
        $service->save();

        Toastr::success(translate('تم تحديث البيانات بنجاح'));
        return redirect()->route('admin.additional-service.index');
    }

    /**
     * Toggle status of specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function statusUpdate(string $id): JsonResponse
    {
        $service = $this->additionalService->findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Remove specified resource from storage.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        $service = $this->additionalService->findOrFail($id);
        $service->delete();

        Toastr::success(translate('تم الحذف بنجاح'));
        return back();
    }

    /**
     * Helper to parse feature titles, prices and uploaded icons for each feature.
     */
    private function parseFeaturesWithIcons(Request $request): array
    {
        $titles = $request->input('feature_titles', []);
        if (!is_array($titles)) {
            return [];
        }

        $prices = $request->input('feature_prices', []);
        $uploadedFiles = $request->file('feature_icons', []);
        $existingIcons = $request->input('feature_existing_icons', []);

        $features = [];

        foreach ($titles as $index => $title) {
            if (is_null($title) || $title === '') {
                continue;
            }

            $iconName = $existingIcons[$index] ?? null;

            if (isset($uploadedFiles[$index]) && $uploadedFiles[$index]->isValid()) {
                $iconName = file_uploader('additional_service/', APPLICATION_IMAGE_FORMAT, $uploadedFiles[$index]);
            }

            $featurePrice = isset($prices[$index]) && is_numeric($prices[$index]) ? (float) $prices[$index] : 0;

            // Split title by lines if multiple features are entered separated by newlines
            $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $title)));

            foreach ($lines as $line) {
                if ($line !== '') {
                    $features[] = [
                        'title' => $line,
                        'price' => $featurePrice,
                        'icon' => $iconName,
                    ];
                }
            }
        }

        return $features;
    }
}
