<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ServiceManagement\Entities\Property;

class PropertyController extends Controller
{
    public function __construct(private Property $property)
    {
    }

    public function index(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $queryParam = ['search' => $search];

        $properties = $this->property->withCount(['packages', 'addresses', 'services'])->latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%')
                            ->orWhere('address', 'LIKE', '%' . $key . '%')
                            ->orWhere('description', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->paginate(pagination_limit())
            ->appends($queryParam);

        return view('servicemanagement::admin.property.list', compact('properties', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:191',
            'description' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $property = $this->property;
        $property->name = $request->name;
        $property->description = $request->description;
        $property->address = $request->address ?? '';
        $property->is_active = 1;
        $property->save();

        Toastr::success(translate('property_added_successfully'));
        return back();
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:191',
            'description' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $property = $this->property->where('id', $id)->firstOrFail();
        $property->name = $request->name;
        $property->description = $request->description;
        if ($request->has('address')) {
            $property->address = $request->address;
        }
        $property->save();

        Toastr::success(translate('property_updated_successfully'));
        return back();
    }

    public function statusUpdate(string $id): RedirectResponse
    {
        $property = $this->property->where('id', $id)->firstOrFail();
        $property->is_active = !$property->is_active;
        $property->save();

        Toastr::success(translate('property_status_updated_successfully'));
        return back();
    }

    public function destroy(string $id): RedirectResponse
    {
        $property = $this->property->withCount(['packages', 'addresses', 'services'])->where('id', $id)->firstOrFail();

        if ($property->packages_count > 0 || $property->addresses_count > 0 || $property->services_count > 0) {
            Toastr::error(translate('property_has_related_data'));
            return back();
        }

        $property->delete();

        Toastr::success(translate('property_deleted_successfully'));
        return back();
    }
}
