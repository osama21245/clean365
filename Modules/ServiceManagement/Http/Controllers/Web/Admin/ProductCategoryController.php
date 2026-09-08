<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ServiceManagement\Entities\ProductCategory;

class ProductCategoryController extends Controller
{
    use UploadSizeHelperTrait;

    public function __construct(private ProductCategory $productCategory)
    {
    }

    public function index(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $queryParam = ['search' => $search];

        $categories = $this->productCategory->withCount('products')->latest()
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->paginate(pagination_limit())
            ->appends($queryParam);

        return view('servicemanagement::admin.product-category.list', compact('categories', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name' => 'required|max:191',
            'image' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))]);

        $category = $this->productCategory;
        $category->name = $request->name;
        $category->image = $request->hasFile('image')
            ? file_uploader('product-category/', 'png', $request->file('image'))
            : null;
        $category->is_active = 1;
        $category->save();

        Toastr::success(translate('product_category_added_successfully'));
        return back();
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name' => 'required|max:191',
            'image' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))]);

        $category = $this->productCategory->where('id', $id)->firstOrFail();
        $category->name = $request->name;

        if ($request->hasFile('image')) {
            file_remover('product-category/', $category->image);
            $category->image = file_uploader('product-category/', 'png', $request->file('image'));
        }

        $category->save();

        Toastr::success(translate('product_category_updated_successfully'));
        return back();
    }

    public function statusUpdate(string $id): RedirectResponse
    {
        $category = $this->productCategory->where('id', $id)->firstOrFail();
        $category->is_active = !$category->is_active;
        $category->save();

        Toastr::success(translate('product_category_status_updated_successfully'));
        return back();
    }

    public function destroy(string $id): RedirectResponse
    {
        $category = $this->productCategory->withCount('products')->where('id', $id)->firstOrFail();

        if ($category->products_count > 0) {
            Toastr::error(translate('remove_products_first'));
            return back();
        }

        file_remover('product-category/', $category->image);
        $category->delete();

        Toastr::success(translate('product_category_deleted_successfully'));
        return back();
    }
}
