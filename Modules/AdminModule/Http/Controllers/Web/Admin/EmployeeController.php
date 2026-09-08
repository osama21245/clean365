<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin;


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
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Http\Requests\ProviderStoreRequest;
use Modules\UserManagement\Entities\EmployeeRoleAccess;
use Modules\UserManagement\Entities\EmployeeRoleSection;
use Modules\UserManagement\Entities\Role;
use Modules\UserManagement\Entities\RoleAccess;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use function bcrypt;
use function file_remover;
use function file_uploader;
use function response;
use function response_formatter;

class EmployeeController extends Controller
{
    protected User $employee;
    protected UserAddress $address;
    protected Role $role;
    protected Zone $zone;
    protected EmployeeRoleSection $employeeRoleSection;
    protected EmployeeRoleAccess $employeeRoleAccess;
    protected RoleAccess $roleAccess;

    use AuthorizesRequests;
    use UploadSizeHelperTrait;

    public function __construct(User $employee, UserAddress $address, Role $role, Zone $zone, EmployeeRoleSection $employeeRoleSection, EmployeeRoleAccess $employeeRoleAccess, RoleAccess $roleAccess)
    {
        $this->employee = $employee;
        $this->address = $address;
        $this->role = $role;
        $this->zone = $zone;
        $this->employeeRoleSection = $employeeRoleSection;
        $this->employeeRoleAccess = $employeeRoleAccess;
        $this->roleAccess = $roleAccess;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): Application|Factory|View
    {
        $this->authorize('employee_add');
        $roles = $this->role->where(['is_active' => 1])->get();
        $zones = $this->zone->where(['is_active' => 1])->get();

        return view('adminmodule::admin.employee.create', compact('roles', 'zones'));
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View
    {
        $this->authorize('employee_view');
        $search = (string) $request->input('search', '');
        $status = $request->input('status', 'all');
        $roleId = $request->input('role_id');
        $permissionSections = collect($request->input('permission_sections', []))
            ->filter()
            ->values()
            ->all();

        $roles = $this->role->where(['is_active' => 1])->get();
        $moduleFilters = collect(SYSTEM_MODULES)->map(function ($module) {
            $sectionNames = [$module['key']];

            foreach ($module['submodules'] ?? [] as $submodule) {
                $sectionNames[] = $submodule['key'];
            }

            return [
                'key' => $module['key'],
                'value' => $module['value'],
                'section_names' => $sectionNames];
        });

        $selectedSectionNames = $moduleFilters
            ->whereIn('key', $permissionSections)
            ->pluck('section_names')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $queryParams = array_filter([
            'search' => $search !== '' ? $search : null,
            'status' => $status,
            'role_id' => $roleId]);

        if (!empty($permissionSections)) {
            $queryParams['permission_sections'] = $permissionSections;
        }

        $filterCounter = collect([$roleId])->filter()->count() + count($permissionSections);

        $employees = $this->employee->OfType(['admin-employee'])->with(['roles', 'zones', 'addresses'])
            ->when($search !== '', function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('roles', function ($roleQuery) use ($key) {
                                $roleQuery->where('role_name', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($roleId, function ($query) use ($roleId) {
                return $query->whereHas('roles', function ($roleQuery) use ($roleId) {
                    $roleQuery->where('roles.id', $roleId);
                });
            })
            ->when(!empty($selectedSectionNames), function ($query) use ($selectedSectionNames) {
                return $query->whereHas('module_access', function ($accessQuery) use ($selectedSectionNames) {
                    $accessQuery->whereIn('section_name', $selectedSectionNames);
                });
            })
            ->when($status != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.employee.list', compact(
            'employees',
            'status',
            'search',
            'roles',
            'roleId',
            'permissionSections',
            'moduleFilters',
            'filterCounter'
        ));
    }


    /**
     * Store a newly created resource in storage.
     * @param ProviderStoreRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('employee_add');

        $check = $this->validateUploadedFile($request, ['profile_image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|unique:users,phone',
            'password' => 'required',
            'profile_image' => 'required|image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'identity_type' => 'required|in:passport,driving_license,nid,trade_license',
            'identity_number' => 'required',
            'identity_images' => 'required|array',
            'identity_images.*' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'role_id' => 'required|uuid',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'address' => 'required|string'
        ]);

        if (!$request->modules){
            Toastr::error(translate('Please select at latest one module'));
            return back();
        }

        $identityImages = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('employee/identity/', APPLICATION_IMAGE_FORMAT, $image);
            $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
        }

        DB::transaction(function () use ($request, $identityImages) {

            $employee = $this->employee;
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->profile_image = file_uploader('employee/profile/', APPLICATION_IMAGE_FORMAT, $request->file('profile_image'));
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            $employee->identification_image = $identityImages;
            $employee->password = bcrypt($request->password);
            $employee->user_type = 'admin-employee';
            $employee->is_active = 1;
            $employee->save();

            $employee->zones()->sync($request['zone_ids']);

            $address = $this->address;
            $address->user_id = $employee->id;
            $address->address = $request->address;
            $address->save();


            $employeeRoleSection = $this->employeeRoleSection;
            $employeeRoleSection->employee_id = $employee->id;
            $employeeRoleSection->role_id = $request->role_id;
            $employeeRoleSection->save();

            foreach ($request->modules as $section => $values) {
                if (isset($values['access_role'])) {
                    foreach ($values['access_role'] as $key => $value) {
                        EmployeeRoleAccess::create([
                            'employee_id' => $employee->id,
                            'role_id' => $request->role_id,
                            'section_name' => $key,
                            'can_add' => isset($values['can_add']) ? 1 : 0,
                            'can_update' => isset($values['can_update']) ? 1 : 0,
                            'can_delete' => isset($values['can_delete']) ? 1 : 0,
                            'can_export' => isset($values['can_export']) ? 1 : 0,
                            'can_manage_status' => isset($values['can_manage_status']) ? 1 : 0,
                            'can_assign_serviceman' => isset($values['can_assign_serviceman']) ? 1 : 0,
                            'can_give_feedback' => isset($values['can_give_feedback']) ? 1 : 0,
                            'can_take_backup' => isset($values['can_take_backup']) ? 1 : 0,
                            'can_change_status' => isset($values['can_change_status']) ? 1 : 0]);
                    }
                }
            }
        });

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return redirect('/admin/employee/list');
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $id): Application|Factory|View
    {
        $this->authorize('employee_update');
        $roleAccess = $this->employeeRoleAccess->where('employee_id', $id)->get();
        $employee = $this->employee->with(['roles', 'zones', 'addresses'])->where(['id' => $id, 'user_type' => 'admin-employee'])->first();
        $roles = $this->role->where(['is_active' => 1])->get();
        $zones = $this->zone->where(['is_active' => 1])->get();

        return view('adminmodule::admin.employee.edit', compact('roleAccess','roles', 'zones', 'employee'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function setPermission(string $id): Application|Factory|View
    {
        $this->authorize('employee_update');
        $roleAccess = $this->employeeRoleAccess->where('employee_id', $id)->get();
        $employee = $this->employee->with(['roles', 'zones', 'addresses'])->where(['id' => $id, 'user_type' => 'admin-employee'])->first();
        $roles = $this->role->where(['is_active' => 1])->get();
        $zones = $this->zone->where(['is_active' => 1])->get();
        return view('adminmodule::admin.employee.set-permission', compact('roleAccess', 'roles', 'zones', 'employee'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return Redirector|Application|RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): Application|RedirectResponse|Redirector
    {
        $this->authorize('employee_update');

        // Self-protection: an employee cannot change their own role or permissions
        if (auth()->id() == $id) {
            $currentEmployee = $this->employee->where(['id' => $id, 'user_type' => 'admin-employee'])->first();
            if ($currentEmployee) {
                $currentRoleId = $currentEmployee->roles->first()?->id;
                // Force the submitted role_id back to the current one — cannot be changed
                $request->merge(['role_id' => $currentRoleId]);
                // Wipe out modules from the request — permissions cannot be changed
                $request->request->remove('modules');
            }
        }

        $check = $this->validateUploadedFile($request, ['profile_image']);
        if ($check !== true) {
            return $check;
        }

        $employee = $this->employee->where(['id' => $id, 'user_type' => 'admin-employee'])->first();

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => !is_null($request->password) ? 'string|min:8' : '',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : '',
            'profile_image' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'identity_type' => 'required|in:passport,driving_license,nid,trade_license',
            'identity_number' => 'required',
            'identity_images' => 'array',
            'identity_images.*' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'role_id' => 'required|uuid',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'address' => 'required|string'
        ]);

        if (!$request->modules && auth()->id() != $id) {
            Toastr::error(translate('Please select at latest one module'));
            return back();
        }

        if (User::where('email', $request['email'])->where('id', '!=', $employee->id)->exists()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['phone'])->where('id', '!=', $employee->id)->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        $identityImages = [];
        if ($request->has('identity_images')) {
            foreach ($request['identity_images'] as $image) {
                $imageName = file_uploader('employee/identity/', APPLICATION_IMAGE_FORMAT, $image);
                $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
            }

            $employee->identification_image = array_merge($identityImages, $employee->identification_image);
        }
        DB::transaction(function () use ($id, $employee, $request, $identityImages) {
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            if ($request->has('profile_image')) {
                $employee->profile_image = file_uploader('employee/profile/', APPLICATION_IMAGE_FORMAT, $request->file('profile_image'), $employee->profile_image);;
            }
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            if (!is_null($request->password)) {
                $employee->password = bcrypt($request->password);
            }
            $employee->user_type = 'admin-employee';
            $employee->password = !is_null($request->password) ? bcrypt($request->password) : $employee->password;
            $employee->save();

            $employee->roles()->sync([$request['role_id']]);
            $employee->zones()->sync($request['zone_ids']);

            $address = $this->address->where('user_id', $id)->first();
            $address->address = $request->address;
            $address->save();

            $employeeRoleSection = $this->employeeRoleSection->where('employee_id', $id)->first();
            $employeeRoleSection->employee_id = $id;
            $employeeRoleSection->role_id = $request->role_id;
            $employeeRoleSection->save();

            $employeeRoleAccess = $this->employeeRoleAccess->where('employee_id', $id)->first();
            if ($employeeRoleAccess) {
                $existingRoleId = $employeeRoleAccess->role_id;

                if ($existingRoleId !== $request->role_id) {
                    $this->employeeRoleAccess
                        ->where('employee_id', $id)
                        ->where('role_id', $existingRoleId)
                        ->delete();
                }
            }

            $requestedSections = [];

            if ($request->modules) {
                foreach ($request->modules as $section => $values) {
                    if (isset($values['access_role'])) {
                        foreach ($values['access_role'] as $key => $value) {

                            $requestedSections[] = $key; // collect for delete later

                            $accessData = [
                                'employee_id' => $employee->id,
                                'role_id' => $request->role_id,
                                'section_name' => $key,
                                'can_add' => isset($values['can_add']) ? 1 : 0,
                                'can_update' => isset($values['can_update']) ? 1 : 0,
                                'can_delete' => isset($values['can_delete']) ? 1 : 0,
                                'can_export' => isset($values['can_export']) ? 1 : 0,
                                'can_manage_status' => isset($values['can_manage_status']) ? 1 : 0,
                                'can_assign_serviceman' => isset($values['can_assign_serviceman']) ? 1 : 0,
                                'can_give_feedback' => isset($values['can_give_feedback']) ? 1 : 0,
                                'can_take_backup' => isset($values['can_take_backup']) ? 1 : 0,
                                'can_change_status' => isset($values['can_change_status']) ? 1 : 0];

                            $existingAccess = $this->employeeRoleAccess->where('employee_id', $employee->id)
                                ->where('role_id', $request->role_id)
                                ->where('section_name', $key)
                                ->first();
                            $roleAccess = $this->roleAccess->where('role_id', $request->role_id)->with('role')->get();
                            foreach ($roleAccess as $access){
                                if ($access->can_add == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_add'] == 1) {
                                    $message = "Permission 'add' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_update == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_update'] == 1) {
                                    $message = "Permission 'update' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_delete == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_delete'] == 1) {
                                    $message = "Permission 'delete' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_export == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_export'] == 1) {
                                    $message = "Permission 'export' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_manage_status == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_manage_status'] == 1) {
                                    $message = "Permission 'status' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_assign_serviceman == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_assign_serviceman'] == 1) {
                                    $message = "Permission 'assign serviceman' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_give_feedback == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_give_feedback'] == 1) {
                                    $message = "Permission 'give feedback' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                                if ($access->can_take_backup == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_take_backup'] == 1) {
                                    $message = "Permission 'take backup' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                    $this->alertMessage($message);
                                }
                            }
                            if ($existingAccess && empty($message)) {
                                $existingAccess->update($accessData);
                            } else {
                                $this->employeeRoleAccess->create($accessData);
                            }
                        }
                    }
                }

                // --- DELETE OLD SECTIONS NOT IN REQUEST ---
                $this->employeeRoleAccess
                    ->where('employee_id', $employee->id)
                    ->where('role_id', $request->role_id)
                    ->whereNotIn('section_name', $requestedSections)
                    ->delete();
            }

            if(empty($message)) {
                Toastr::success(translate(DEFAULT_UPDATE_200['key']));
            }
        });
        return redirect('/admin/employee/list');
    }
    function alertMessage ($message){
        Toastr::error(translate($message));
        return redirect('/admin/employee/list');
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
        $this->authorize('employee_delete');
        $user = $this->employee->where('id', $id)->first();
        if (isset($user)) {
            file_remover('employee/profile_image/', $user->profile_image);
            foreach ($user->identification_image as $image_name) {
                file_remover('employee/identity/', $image_name);
            }
            $user->delete();

            $this->employeeRoleAccess->where('employee_id', $id)->delete();
            $this->employeeRoleSection->where('employee_id', $id)->delete();

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
        $this->authorize('employee_manage_status');
        $user = $this->employee->where('id', $id)->first();
        $this->employee->where('id', $id)->update(['is_active' => !$user->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_image(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|uuid',
            'image_name' => 'required|string',
            'image_type' => 'required|in:logo,identity_image'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $employee = $this->employee->where('id', $request['employee_id'])->first();
        if ($request['image_type'] == 'identity_image') {
            file_remover('employee/identity/', $request['image_name']);
            $identityImages = $employee->identification_image ?? [];
            foreach ($identityImages as $key => $image) {
                if (is_array($image) && ($image['image'] ?? null) == $request['image_name']) {
                    unset($identityImages[$key]);
                } elseif (is_string($image) && $image == $request['image_name']) {
                    unset($identityImages[$key]);
                }
            }
            $employee->identification_image = array_values($identityImages);
            $employee->save();
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws AuthorizationException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('employee_export');
        $search = (string) $request->input('search', '');
        $status = $request->input('status', 'all');
        $roleId = $request->input('role_id');
        $permissionSections = collect($request->input('permission_sections', []))
            ->filter()
            ->values()
            ->all();

        $moduleFilters = collect(SYSTEM_MODULES)->map(function ($module) {
            $sectionNames = [$module['key']];

            foreach ($module['submodules'] ?? [] as $submodule) {
                $sectionNames[] = $submodule['key'];
            }

            return [
                'key' => $module['key'],
                'section_names' => $sectionNames];
        });

        $selectedSectionNames = $moduleFilters
            ->whereIn('key', $permissionSections)
            ->pluck('section_names')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $items = $this->employee->OfType(['admin-employee'])->with(['roles', 'zones', 'addresses', 'module_access'])
            ->when($search !== '', function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('roles', function ($roleQuery) use ($key) {
                                $roleQuery->where('role_name', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($roleId, function ($query) use ($roleId) {
                return $query->whereHas('roles', function ($roleQuery) use ($roleId) {
                    $roleQuery->where('roles.id', $roleId);
                });
            })
            ->when(!empty($selectedSectionNames), function ($query) use ($selectedSectionNames) {
                return $query->whereHas('module_access', function ($accessQuery) use ($selectedSectionNames) {
                    $accessQuery->whereIn('section_name', $selectedSectionNames);
                });
            })
            ->when($status != 'all', function ($query) use ($status) {
                return $query->ofStatus($status === 'active' ? 1 : 0);
            })
            ->latest()->get();

        $canManageStatus = auth()->user()?->can('employee_manage_status');
        $exportRows = $items->values()->map(function ($employee, $index) use ($canManageStatus) {
            $row = [
                translate('SL')            => $index + 1,
                translate('Employee_Name') => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                translate('Email')         => $employee->email ?? '',
                translate('Employee_ID')   => $employee->id,
                translate('Role')          => $employee?->roles?->first()?->role_name ?? '',
                translate('Permission')    => $this->formatEmployeePermissions($employee)];

            if ($canManageStatus) {
                $row[translate('status')] = $employee->is_active ? translate('Active') : translate('Inactive');
            }

            return $row;
        });

        $fileName = 'employees_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($exportRows))->download($fileName);
    }

    private function formatEmployeePermissions(User $employee): string
    {
        $permissions = [];

        foreach (SYSTEM_MODULES as $module) {
            $matchedRoleBtn = $employee->module_access->firstWhere('section_name', $module['key']);

            if (isset($module['submodules'])) {
                $matchedSubmodules = collect($module['submodules'])
                    ->filter(function ($submodule) use ($employee) {
                        return $employee->module_access->contains('section_name', $submodule['key']);
                    })
                    ->pluck('value')
                    ->implode(', ');

                if ($matchedSubmodules !== '') {
                    $permissions[] = $module['value'] . ' (' . $matchedSubmodules . ')';
                }

                continue;
            }

            if ($matchedRoleBtn) {
                $permissions[] = $module['value'];
            }
        }

        return implode(', ', $permissions);
    }

    public function ajaxRoleAccess(Request $request)
    {
        $roleAccess = $this->roleAccess->where('role_id', $request->role_id)->get();
        $view = view('adminmodule::layouts.partials.employee-role-access', compact('roleAccess'))->render();
        return response()->json(['html' => $view], 200);
    }

    public function ajaxEmployeeRoleAccess(Request $request)
    {
        $employeeRoleSection = $this->employeeRoleSection->where('employee_id', $request->id)->where('role_id', $request->role_id)->first();
        $isSelf = auth()->id() == $request->id;
        if ($employeeRoleSection) {
            $roleAccess = $this->employeeRoleAccess->where('employee_id', $request->id)->where('role_id', $request->role_id)->get();
            $view = view('adminmodule::layouts.partials.employee-update-access', compact('roleAccess', 'isSelf'))->render();
            return response()->json(['html' => $view], 200);
        }

        $roleAccess = $this->roleAccess->where('role_id', $request->role_id)->get();
        $view = view('adminmodule::layouts.partials.employee-role-access', compact('roleAccess'))->render();
        return response()->json(['html' => $view], 200);
    }

}
