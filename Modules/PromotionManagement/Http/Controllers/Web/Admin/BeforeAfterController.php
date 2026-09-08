<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PromotionManagement\Entities\BeforeAfter;

class BeforeAfterController extends Controller
{
    use AuthorizesRequests;
    use UploadSizeHelperTrait;

    public function __construct(private BeforeAfter $beforeAfter)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParam = ['search' => $search, 'status' => $status];

        $items = $this->beforeAfter
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->where('is_active', $request['status'] == 'active' ? 1 : 0);
            })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParam);

        return view('promotionmanagement::admin.before-after.create', compact('items', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['before_image', 'after_image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title' => 'required|string|max:190',
            'sort_order' => 'nullable|integer|min:0',
            'before_image' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'after_image' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $item = $this->beforeAfter;
        $item->title = $request['title'];
        $item->sort_order = (int) ($request['sort_order'] ?? 0);
        $item->before_image = file_uploader('before-after/', 'png', $request->file('before_image'));
        $item->after_image = file_uploader('before-after/', 'png', $request->file('after_image'));
        $item->is_active = 1;
        $item->save();

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return back();
    }

    public function edit(string $id): View|Factory|Application|RedirectResponse
    {
        $item = $this->beforeAfter->where('id', $id)->first();

        if (!$item) {
            Toastr::error(translate(DEFAULT_204['key']));
            return redirect()->route('admin.before-after.create');
        }

        return view('promotionmanagement::admin.before-after.edit', compact('item'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['before_image', 'after_image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title' => 'required|string|max:190',
            'sort_order' => 'nullable|integer|min:0',
            'before_image' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'after_image' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $item = $this->beforeAfter->where('id', $id)->first();
        if (!$item) {
            Toastr::error(translate(DEFAULT_204['key']));
            return redirect()->route('admin.before-after.create');
        }

        $item->title = $request['title'];
        $item->sort_order = (int) ($request['sort_order'] ?? 0);
        $item->before_image = file_uploader('before-after/', 'png', $request->file('before_image'), $item->before_image);
        $item->after_image = file_uploader('before-after/', 'png', $request->file('after_image'), $item->after_image);
        $item->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return redirect()->route('admin.before-after.create');
    }

    public function destroy(string $id): RedirectResponse
    {
        $item = $this->beforeAfter->where('id', $id)->first();

        if ($item) {
            file_remover('before-after/', $item->before_image);
            file_remover('before-after/', $item->after_image);
            $item->delete();
        }

        Toastr::success(translate(DEFAULT_DELETE_200['key']));
        return back();
    }

    public function statusUpdate(string $id): JsonResponse
    {
        $item = $this->beforeAfter->where('id', $id)->first();

        if (!$item) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $item->is_active = !$item->is_active;
        $item->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }
}
