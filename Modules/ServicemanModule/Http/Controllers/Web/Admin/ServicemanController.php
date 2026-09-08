<?php

namespace Modules\ServicemanModule\Http\Controllers\Web\Admin;

use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServicemanController extends Controller
{
    use UploadSizeHelperTrait;

    private User $employee;
    private User $servicemanUser;
    private Serviceman $serviceman;
    private Provider $provider;

    public function __construct(Serviceman $serviceman, User $servicemanUser, User $employee, Provider $provider)
    {
        $this->serviceman = $serviceman;
        $this->employee = $employee;
        $this->servicemanUser = $servicemanUser;
        $this->provider = $provider;
    }

    public function index(Request $request): Renderable
    {
        $request->validate([
            'status' => 'in:active,inactive,all',
            'provider_id' => 'nullable|uuid']);

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $providerId = $request->get('provider_id');
        $queryParam = ['status' => $status, 'search' => $search, 'provider_id' => $providerId];

        $servicemen = $this->servicemanUser->with(['serviceman.provider'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    foreach (explode(' ', $search) as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('identification_number', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($status != 'all', function ($query) use ($status) {
                $query->where('is_active', ($status == 'active') ? 1 : 0);
            })
            ->whereHas('serviceman', function ($query) use ($providerId) {
                $query->when($providerId, fn($q) => $q->where('provider_id', $providerId));
            })
            ->where(['user_type' => 'provider-serviceman'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParam);

        $providers = $this->getActiveProviders();

        return view('servicemanmodule::Admin.Serviceman.list', compact('servicemen', 'search', 'status', 'providers', 'providerId'));
    }

    public function create(): Renderable
    {
        return view('servicemanmodule::Admin.Serviceman.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['profile_image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'profile_image' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'identity_type' => 'required|in:passport,driving_license,nid,trade_license',
            'identity_number' => 'required',
            'identity_images' => 'required|array',
            'identity_images.*' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))]);

        if (!$request->has('identity_images') || count($request->identity_images) < 1) {
            Toastr::error(translate('Identification_image_is_required'));
            return back()->withInput();
        }

        if (User::where('email', $request['email'])->first()) {
            Toastr::error(translate('Email already taken'));
            return back()->withInput();
        }
        if (User::where('phone', $request['phone'])->first()) {
            Toastr::error(translate('Phone already taken'));
            return back()->withInput();
        }

        $identityImages = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('serviceman/identity/', APPLICATION_IMAGE_FORMAT, $image);
            $identityImages[] = ['image' => $imageName, 'storage' => getDisk()];
        }

        DB::transaction(function () use ($request, $identityImages) {
            $employee = $this->employee;
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->profile_image = file_uploader('serviceman/profile/', APPLICATION_IMAGE_FORMAT, $request->file('profile_image'));
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            $employee->identification_image = $identityImages;
            $employee->password = bcrypt($request->password);
            $employee->user_type = 'provider-serviceman';
            $employee->is_active = 1;
            $employee->save();

            $serviceman = $this->serviceman;
            $serviceman->provider_id = null;
            $serviceman->user_id = $employee->id;
            $serviceman->save();
        });

        Toastr::success(translate(SERVICE_STORE_200['key']));
        return redirect()->route('admin.serviceman.list', ['status' => 'all']);
    }

    public function show(string $id): View|RedirectResponse
    {
        $serviceman = $this->serviceman::with(['user.addresses', 'provider', 'teams'])->find($id);

        if (!$serviceman) {
            Toastr::error(translate(DEFAULT_404['key']));
            return back();
        }

        return view('servicemanmodule::Admin.Serviceman.details', compact('serviceman'));
    }

    public function edit(string $id): View|RedirectResponse
    {
        $serviceman = $this->serviceman::with(['user', 'provider'])->find($id);

        if (!$serviceman) {
            Toastr::error(translate(DEFAULT_404['key']));
            return redirect()->route('admin.serviceman.list');
        }

        return view('servicemanmodule::Admin.Serviceman.edit', compact('serviceman'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $servicemanRecord = $this->serviceman->find($id);
        $employee = $this->employee::whereHas('serviceman', function ($query) use ($id) {
            $query->where(['id' => $id]);
        })->first();

        if (!isset($employee) || !$servicemanRecord) {
            Toastr::error(translate('you_can _not_change_this_user_info'));
            return back()->withInput();
        }

        $check = $this->validateUploadedFile($request, ['profile_image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'password' => '',
            'confirm_password' => !is_null($request->password) ? 'required|min:8|same:password' : '',
            'profile_image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'identity_type' => 'in:passport,driving_license,nid,trade_license',
            'identity_number' => 'required',
            'identity_images' => 'array',
            'identity_images.*' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))]);

        if (User::where('email', $request['email'])->where('id', '!=', $employee->id)->exists()) {
            Toastr::error(translate('Email already taken'));
            return back()->withInput();
        }
        if (User::where('phone', $request['phone'])->where('id', '!=', $employee->id)->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back()->withInput();
        }

        $identityImages = [];
        if ($request->has('identity_images')) {
            foreach ($request['identity_images'] as $image) {
                $imageName = file_uploader('serviceman/identity/', APPLICATION_IMAGE_FORMAT, $image);
                $identityImages[] = ['image' => $imageName, 'storage' => getDisk()];
            }
        }

        DB::transaction(function () use ($request, $identityImages, $employee, $servicemanRecord) {
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            if ($request->has('profile_image')) {
                $employee->profile_image = file_uploader('serviceman/profile/', APPLICATION_IMAGE_FORMAT, $request->file('profile_image'));
            }
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            if (count($identityImages)) {
                $employee->identification_image = array_merge($employee->identification_image ?? [], $identityImages);
            }
            if (!is_null($request->password)) {
                $employee->password = bcrypt($request->password);
            }
            $employee->user_type = 'provider-serviceman';
            $employee->save();
        });

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return redirect()->route('admin.serviceman.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        $serviceman = $this->serviceman->find($id);
        if ($serviceman) {
            $serviceman->delete();
        }

        Toastr::success(translate(DEFAULT_DELETE_200['key']));
        return redirect(route('admin.serviceman.list', ['status' => 'all']));
    }

    public function statusUpdate(string $id): JsonResponse
    {
        $serviceman = $this->employee->where('id', $id)->first();
        if ($serviceman) {
            $this->employee->where('id', $id)->update(['is_active' => !$serviceman->is_active]);
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    public function download(Request $request): string|StreamedResponse
    {
        $request->validate([
            'status' => 'in:active,inactive,all',
            'provider_id' => 'nullable|uuid']);

        $providerId = $request->get('provider_id');

        $items = $this->servicemanUser->with(['serviceman.provider'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    foreach (explode(' ', $request['search']) as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('identification_number', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request['status'] != 'all', function ($query) use ($request) {
                $query->where('is_active', ($request['status'] == 'active') ? 1 : 0);
            })
            ->whereHas('serviceman', function ($query) use ($providerId) {
                $query->when($providerId, fn($q) => $q->where('provider_id', $providerId));
            })
            ->where(['user_type' => 'provider-serviceman'])
            ->latest()
            ->get();

        $rowIndex = 0;
        $fileName = 'servicemen_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($items))->download($fileName, function ($serviceman) use (&$rowIndex) {
            $rowIndex++;
            $name = trim(($serviceman->first_name ?? '') . ' ' . ($serviceman->last_name ?? ''));
            $contact = trim(($serviceman->email ?? '') . ($serviceman->phone ? "\n" . $serviceman->phone : ''));

            return [
                translate('SL') => $rowIndex,
                translate('Name') => $name,
                translate('Supervisor') => $serviceman->serviceman?->provider?->company_name ?? '-',
                translate('Contact_Info') => $contact,
                translate('Status') => $serviceman->is_active ? translate('Active') : translate('Inactive')];
        });
    }

    public function remove_image(Request $request): JsonResponse
    {
        $serviceman = $this->serviceman->find($request['provider_id']);
        if (!$serviceman) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        if ($request['image_type'] == 'identity_image') {
            file_remover('serviceman/identity/', $request['image_name']);
            $identityImages = $serviceman->user->identification_image ?? [];
            foreach ($identityImages as $key => $image) {
                if (is_array($image) && ($image['image'] ?? null) == $request['image_name']) {
                    unset($identityImages[$key]);
                } elseif (is_string($image) && $image == $request['image_name']) {
                    unset($identityImages[$key]);
                }
            }
            $serviceman->user->identification_image = array_values($identityImages);
            $serviceman->user->save();
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    private function getActiveProviders()
    {
        return $this->provider->ofApproval(1)->ofStatus(1)->orderBy('company_name')->get(['id', 'company_name']);
    }
}
