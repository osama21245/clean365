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
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ServiceManagement\Entities\Product;
use Modules\ServiceManagement\Entities\ProductCategory;

class ProductController extends Controller
{
    use UploadSizeHelperTrait;

    public function __construct(
        private Product $product,
        private ProductCategory $productCategory
    ) {
    }

    public function index(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $queryParam = ['search' => $search];

        $products = $this->product->with('category')->latest()
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

        return view('servicemanagement::admin.product.list', compact('products', 'search'));
    }

    public function create(): View|Factory|Application
    {
        $categories = $this->productCategory->ofStatus(1)->latest()->get();

        return view('servicemanagement::admin.product.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['thumbnail']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name' => 'required|array',
            'name.0' => 'required|max:191',
            'short_description' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'badge' => 'nullable|in:new,sale',
            'product_category_id' => 'required|uuid',
            'rating' => 'nullable|numeric|min:0|max:5',
            'thumbnail' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'lang' => 'required|array']);

        $defaultIndex = array_search('default', $request->lang);

        $product = $this->product;
        $product->name = $request->name[$defaultIndex];
        $product->short_description = $request->short_description[$defaultIndex] ?? null;
        $product->price = $request->price;
        $product->sale_price = $request->sale_price;
        $product->badge = $request->badge;
        $product->product_category_id = $request->product_category_id;
        $product->rating = $request->rating ?? 0;
        $product->thumbnail = file_uploader('product/', 'png', $request->file('thumbnail'));
        $product->is_active = 1;
        $product->save();

        $this->saveTranslations($request, $product);

        Toastr::success(translate('product_added_successfully'));
        return redirect()->route('admin.product.list');
    }

    public function edit(string $id): View|Factory|Application
    {
        $product = $this->product->withoutGlobalScopes()->with('translations')->where('id', $id)->firstOrFail();
        $categories = $this->productCategory->ofStatus(1)->latest()->get();

        return view('servicemanagement::admin.product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['thumbnail']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name' => 'required|array',
            'name.0' => 'required|max:191',
            'short_description' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'badge' => 'nullable|in:new,sale',
            'product_category_id' => 'required|uuid',
            'rating' => 'nullable|numeric|min:0|max:5',
            'thumbnail' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'lang' => 'required|array']);

        $product = $this->product->where('id', $id)->firstOrFail();
        $defaultIndex = array_search('default', $request->lang);

        $product->name = $request->name[$defaultIndex];
        $product->short_description = $request->short_description[$defaultIndex] ?? null;
        $product->price = $request->price;
        $product->sale_price = $request->sale_price;
        $product->badge = $request->badge;
        $product->product_category_id = $request->product_category_id;
        $product->rating = $request->rating ?? 0;

        if ($request->hasFile('thumbnail')) {
            file_remover('product/', $product->thumbnail);
            $product->thumbnail = file_uploader('product/', 'png', $request->file('thumbnail'));
        }

        $product->save();
        $this->saveTranslations($request, $product);

        Toastr::success(translate('product_updated_successfully'));
        return redirect()->route('admin.product.list');
    }

    public function statusUpdate(string $id): RedirectResponse
    {
        $product = $this->product->where('id', $id)->firstOrFail();
        $product->is_active = !$product->is_active;
        $product->save();

        Toastr::success(translate('product_status_updated_successfully'));
        return back();
    }

    public function destroy(string $id): RedirectResponse
    {
        $product = $this->product->where('id', $id)->firstOrFail();
        file_remover('product/', $product->thumbnail);
        $product->translations()->delete();
        $product->delete();

        Toastr::success(translate('product_deleted_successfully'));
        return back();
    }

    private function saveTranslations(Request $request, Product $product): void
    {
        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }

            if ($defaultLang == $key && !($request->name[$index] ?? null)) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'Modules\ServiceManagement\Entities\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'name'],
                    ['value' => $product->name]
                );
            } elseif (!empty($request->name[$index])) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'Modules\ServiceManagement\Entities\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'name'],
                    ['value' => $request->name[$index]]
                );
            }

            if (!empty($request->short_description[$index] ?? null)) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'Modules\ServiceManagement\Entities\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'short_description'],
                    ['value' => $request->short_description[$index]]
                );
            }
        }
    }
}
