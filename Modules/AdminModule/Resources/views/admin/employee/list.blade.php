@extends('adminmodule::layouts.master')

@section('title', translate('employee_list'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <style>
        .role-tooltip-trigger {
            display: inline-block;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title">{{translate('employee_list')}}</h2>
                        @can('employee_add')
                            <div>
                                <a href="{{route('admin.employee.create')}}" class="btn btn--primary">
                                    <span class="material-icons">add</span>
                                    {{translate('add_employee')}}
                                </a>
                            </div>
                        @endcan
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='active'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}">
                                    {{translate('active')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='inactive'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}">
                                    {{translate('inactive')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Employees')}}:</span>
                            <span class="title-color">{{$employees->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{ url()->current() }}"
                                      class="search-form search-form_style-two"
                                      method="GET">
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    @if($roleId)
                                        <input type="hidden" name="role_id" value="{{ $roleId }}">
                                    @endif
                                    @foreach($permissionSections as $permissionSection)
                                        <input type="hidden" name="permission_sections[]" value="{{ $permissionSection }}">
                                    @endforeach
                                    <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit"
                                            class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-lg-3 gap-2">
                                    <div class="dropdown">
                                        @can('employee_export')
                                            <button type="button"
                                                    class="btn btn--secondary rounded px-3 text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span class="material-icons">file_download</span>
                                                {{ translate('download') }}
                                            </button>
                                        @endcan
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <a class="dropdown-item"
                                               href="{{ route('admin.employee.download', array_merge(['search' => $search], ['status' => $status], $roleId ? ['role_id' => $roleId] : [], !empty($permissionSections) ? ['permission_sections' => $permissionSections] : [])) }}">
                                                {{translate('excel')}}
                                            </a>
                                        </ul>
                                    </div>
                                    <button type="button" class="btn text-capitalize py-2 h-100 rounded border px-3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                                        <span class="material-icons">filter_list</span> {{translate('Filter')}}
                                        <span class="count">{{$filterCounter??0}}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                @php
                                    $employeeTableColspan = 6;
                                @endphp
                                @can('employee_manage_status')
                                    @php
                                        $employeeTableColspan++;
                                    @endphp
                                @endcan
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Employee_Name')}}</th>
                                        <th>{{translate('Employee_ID')}}</th>
                                        <th>{{translate('Role')}}</th>
                                        <th>{{translate('Permission')}}</th>
                                        @can('employee_manage_status')
                                            <th class="text-center">{{translate('status')}}</th>
                                        @endcan
                                        <th class="text-center">{{translate('action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($employees as $key => $employee)
                                        <tr>
                                            <td data-bs-target="#exampleModal--{{$employee['id']}}"
                                                data-bs-toggle="modal">{{$key+$employees?->firstItem()}}.</td>
                                            <td>
                                                <div data-bs-target="#exampleModal--{{$employee['id']}}"
                                                     data-bs-toggle="modal">{{$employee->first_name}} {{$employee->last_name}}</div>
                                                <a href="mailto:{{$employee->email}}"
                                                   class="fz-12 fw-medium">{{$employee->email}}</a>

                                                <div class="modal fade cursor-auto" tabindex="-1"
                                                     id="exampleModal--{{$employee['id']}}" aria-hidden="true">
                                                    <div class="modal-dialog modal-xl">
                                                        <div class="modal-content">
                                                            <div class="modal-body">
                                                                <div
                                                                    class="d-flex justify-content-between gap-3 mb-4">
                                                                    <h3 class="text-primary">{{translate('Employee Details')}}</h3>
                                                                    <div class="d-flex gap-3 align-items-center">
                                                                        <p class="text-primary font-weight-bold mb-0">{{$employee->is_active? translate('Active'): translate('Inactive')}}</p>
                                                                        @can('employee_manage_status')
                                                                            @if(auth()->id() == $employee->id)
                                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('You cannot change your own status') }}" class="d-inline-block">
                                                                            @endif
                                                                            <label class="switcher {{ auth()->id() == $employee->id ? 'opacity-50 pe-none' : '' }}">
                                                                                <input class="switcher_input"
                                                                                       type="checkbox"
                                                                                       {{$employee->is_active?'checked':''}}
                                                                                       data-status="{{$employee->id}}"
                                                                                       {{ auth()->id() == $employee->id ? 'disabled' : '' }}>
                                                                                <span class="switcher_control"></span>
                                                                            </label>
                                                                            @if(auth()->id() == $employee->id)
                                                                            </span>
                                                                            @endif
                                                                        @endcan
                                                                    </div>
                                                                </div>

                                                                <form>
                                                                    <div class="row gy-3">
                                                                        <div class="col-lg-8">
                                                                            <div
                                                                                class="media align-items-center flex-wrap gap-xl-5 gap-4">
                                                                                <img width="260" src="{{$employee->profile_image_full_path}}"
                                                                                     class="dark-support shadow rounded"
                                                                                     alt="{{translate('profile image')}}">
                                                                                <div class="media-body">
                                                                                    <h3 class="mb-2">{{$employee->first_name . ' ' .  $employee->last_name}}</h3>
                                                                                    <div
                                                                                        class="fs-12 fw-medium text-primary mb-4">
                                                                                        {{isset($employee?->roles[0]) ? $employee?->roles[0]['role_name'] : ''}}
                                                                                    </div>

                                                                                    <ul class="list-info">
                                                                                        <li>
                                                                                            <span
                                                                                                class="material-symbols-outlined">assignment_ind</span>
                                                                                            ID:
                                                                                            #{{$employee->id}}
                                                                                        </li>
                                                                                        <li>
                                                                                            <span
                                                                                                class="material-symbols-outlined">phone_iphone</span>
                                                                                            <a href="tel:{{$employee->phone}}">{{$employee->phone}}</a>
                                                                                        </li>
                                                                                        <li>
                                                                                            <span
                                                                                                class="material-symbols-outlined">mail</span>
                                                                                            <a href="mailto:{{$employee->email}}">{{$employee->email}}</a>
                                                                                        </li>
                                                                                        <li>
                                                                                            <span
                                                                                                class="material-symbols-outlined">map</span>
                                                                                            {{$employee->addresses->value('address') ??  'not found'}}
                                                                                        </li>
                                                                                        <li class="text-uppercase">
                                                                                            <span
                                                                                                class="material-symbols-outlined">credit_card</span>
                                                                                            {{str_replace('_', " " , $employee->identification_type)}}
                                                                                            - {{$employee->identification_number}}
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-4">
                                                                            <div class="p-3 bg-light rounded scrollY" style="--mh:70dvh;">
                                                                                <div class="card border-0 mb-3">
                                                                                    <div
                                                                                        class="card-body d-flex align-items-center gap-2">
                                                                                        <span
                                                                                            class="material-symbols-outlined text-primary">calendar_month</span>
                                                                                        Join: {{$employee->created_at}}
                                                                                    </div>
                                                                                </div>
                                                                                @foreach(SYSTEM_MODULES as $roleName)
                                                                                    @php
                                                                                        $buttonPermission = ['can_add', 'can_update', 'can_delete', 'can_export', 'can_manage_status','can_download','can_assign_serviceman','can_give_feedback','can_take_backup'];
                                                                                            $matchedRoleSectionName = Modules\UserManagement\Entities\EmployeeRoleAccess::where('employee_id', $employee['id'])->where('section_name', $roleName['key'])->first();
                                                                                            $matchingSubmodules = false;
                                                                                    @endphp
                                                                                    @if(isset($roleName['submodules']))
                                                                                        @foreach($roleName['submodules'] as $submodule)
                                                                                            @php
                                                                                                $matchedRoleSection = Modules\UserManagement\Entities\EmployeeRoleAccess::where('employee_id', $employee['id'])->where('section_name', $submodule['key'])->first();
                                                                                                if($matchedRoleSection) {
                                                                                                    $matchingSubmodules = true;
                                                                                                    break;
                                                                                                }
                                                                                            @endphp
                                                                                        @endforeach
                                                                                        @if($matchingSubmodules)
                                                                                            @php $tableRendered = false; @endphp
                                                                                            <div class="card border-0  mb-3">
                                                                                                <div class="card-body">
                                                                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                                                                                        <div class="d-flex align-items-center gap-2">
                                                                                                            <h4>{{ $roleName['value'] }}</h4>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                        @foreach($roleName['submodules'] as $submodule)
                                                                                                            @php
                                                                                                                $matchedRoleSection = Modules\UserManagement\Entities\EmployeeRoleAccess::where('employee_id', $employee['id'])->where('section_name', $submodule['key'])->first();
                                                                                                            @endphp
                                                                                                            @if($matchedRoleSection)
                                                                                                            <div class="">
                                                                                                                <h4 class="mt-4">{{ $submodule['value'] }}</h4>
                                                                                                            </div>
                                                                                                                @if($matchingSubmodules)
                                                                                                                    @php
                                                                                                                        $showManageAccess = false;

                                                                                                                        foreach ($buttonPermission as $permission) {
                                                                                                                            if ($permission === 'can_view') {
                                                                                                                                continue;
                                                                                                                            }

                                                                                                                            if (isset($matchedRoleSection[$permission]) && $matchedRoleSection[$permission] === 1) {
                                                                                                                                $showManageAccess = true;
                                                                                                                                break;
                                                                                                                            }
                                                                                                                        }
                                                                                                                    @endphp
                                                                                                                    @if ($showManageAccess)
                                                                                                                        <div class="">
                                                                                                                            <h5 class="mb-3 mt-2">{{ translate('Manage Access') }}</h5>
                                                                                                                            <div class="d-flex flex-wrap gap-2 align-items-center scrollY">

                                                                                                                                @php $tableRendered = true; @endphp
                                                                                                                                @foreach($buttonPermission as $permission)
                                                                                                                                    @if($matchedRoleSection->$permission)
                                                                                                                                        @php
                                                                                                                                            $permissionWords = explode('_', $permission);
                                                                                                                                            $formattedPermission = implode(' ', $permissionWords);
                                                                                                                                        @endphp
                                                                                                                                        <span class="badge bg-custom title-color">{{ ucwords($formattedPermission) }}</span>
                                                                                                                                    @endif
                                                                                                                                @endforeach
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    @endif
                                                                                                                @endif
                                                                                                            @endif
                                                                                                        @endforeach
                                                                                                </div>
                                                                                            </div>
                                                                                        @endif
                                                                                    @elseif($matchedRoleSectionName)
                                                                                        <div class="card border-0  mb-3">
                                                                                            <div class="card-body">
                                                                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                                                                                    <div class="d-flex align-items-center gap-2">
                                                                                                        <h4>{{ $roleName['value'] }}</h4>
                                                                                                    </div>
                                                                                                </div>
                                                                                                @php
                                                                                                    $showManageAccess = false;

                                                                                                    foreach ($buttonPermission as $permission) {
                                                                                                        if ($permission === 'can_view') {
                                                                                                            continue;
                                                                                                        }

                                                                                                        if (isset($matchedRoleSectionName[$permission]) && $matchedRoleSectionName[$permission] === 1) {
                                                                                                            $showManageAccess = true;
                                                                                                            break;
                                                                                                        }
                                                                                                    }
                                                                                                @endphp
                                                                                                @if ($showManageAccess)
                                                                                                    <h5 class="mb-3 mt-4">{{ translate('Manage Access') }}</h5>
                                                                                                    <div class="d-flex flex-wrap gap-2 align-items-center scrollY">
                                                                                                        @foreach($buttonPermission as $permission)
                                                                                                            @if($matchedRoleSectionName->$permission)
                                                                                                                @php
                                                                                                                    $permissionWords = explode('_', $permission);
                                                                                                                    $formattedPermission = implode(' ', $permissionWords);
                                                                                                                @endphp
                                                                                                                <span class="badge bg-custom title-color">{{ ucwords($formattedPermission) }}</span>
                                                                                                            @endif
                                                                                                        @endforeach
                                                                                                    </div>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <div
                                                                                class="d-flex justify-content-end gap-3">
                                                                                @if(auth()->id() != $employee->id)
                                                                                @can('employee_delete')
                                                                                    <button type="button"
                                                                                            data-remove="{{$employee->id}}"
                                                                                            class="btn btn--danger remove">
                                                                                        {{translate('Delete')}}</button>

                                                                                    <form
                                                                                        action="{{route('admin.employee.delete',[$employee->id])}}"
                                                                                        method="post"
                                                                                        id="delete-{{$employee->id}}"
                                                                                        class="hidden">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                    </form>
                                                                                @endcan
                                                                                @endif
                                                                                @can('employee_update')
                                                                                    <a type="text"
                                                                                       href="{{route('admin.employee.edit', [$employee->id])}}"
                                                                                       class="btn btn-primary">{{translate('edit')}}</a>
                                                                                @endcan
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-wrap max-w-180 mb-0 line-limit-2">
                                                    {{$employee->id}}
                                                </p>
                                            </td>
                                            <td data-bs-target="#exampleModal--{{$employee['id']}}"
                                                data-bs-toggle="modal">
                                                {{ isset($employee?->roles[0]) ? $employee?->roles[0]['role_name'] : '' }}
                                            </td>
                                            <td>
                                                @php
                                                    $output = '';
                                                @endphp

                                                @foreach(SYSTEM_MODULES as $module)
                                                    @php
                                                        $matchedRoleBtn = Modules\UserManagement\Entities\EmployeeRoleAccess::where('employee_id', $employee['id'])->where('section_name', $module['key'])->first();
                                                        $hasMatchingSubmodules = false;
                                                    @endphp

                                                    @if(isset($module['submodules']))
                                                        @foreach($module['submodules'] as $submodule)
                                                            @php
                                                                $matchedRoleSection = Modules\UserManagement\Entities\EmployeeRoleAccess::where('employee_id', $employee['id'])->where('section_name', $submodule['key'])->first();
                                                                if($matchedRoleSection) {
                                                                    $hasMatchingSubmodules = true;
                                                                    break;
                                                                }
                                                            @endphp
                                                        @endforeach

                                                        @if($hasMatchingSubmodules)
                                                            @php
                                                                $output .= $module['value'] . ' (';
                                                                $submodulesOutput = '';
                                                            @endphp

                                                            @foreach($module['submodules'] as $submodule)
                                                                @php
                                                                    $matchedRoleSection = Modules\UserManagement\Entities\EmployeeRoleAccess::where('employee_id', $employee['id'])->where('section_name', $submodule['key'])->first();
                                                                @endphp
                                                                @if($matchedRoleSection)
                                                                    @php
                                                                        $submodulesOutput .= $submodule['value'] . ', ';
                                                                    @endphp
                                                                @endif
                                                            @endforeach

                                                            @php
                                                                $output .= rtrim($submodulesOutput, ', ') . '), ';
                                                            @endphp
                                                        @endif
                                                    @elseif($matchedRoleBtn)
                                                        @php
                                                            $output .= $module['value'] . ', ';
                                                        @endphp
                                                    @endif
                                                @endforeach

                                                <span class="role-tooltip-trigger"
                                                      data-bs-toggle="tooltip"
                                                      data-bs-placement="top"
                                                      data-bs-title="{{ rtrim($output, ', ') }}">
                                                    <h5 class="max-w-353px line-limit-2 min-w-200 mb-0">{{ Str::limit(rtrim($output, ', '), 170) }}</h5>
                                                </span>
                                            </td>

                                        @can('employee_manage_status')
                                                <td>
                                                    @if(auth()->id() == $employee->id)
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('You cannot change your own status') }}" class="d-inline-block">
                                                    @endif
                                                    <label class="switcher mx-auto {{ auth()->id() == $employee->id ? 'opacity-50 pe-none' : '' }}"
                                                           {{ auth()->id() != $employee->id ? 'data-bs-toggle=modal data-bs-target=#deactivateAlertModal' : '' }}>
                                                        <input class="switcher_input"
                                                               type="checkbox"
                                                               {{$employee->is_active?'checked':''}}
                                                               data-status="{{$employee->id}}"
                                                               {{ auth()->id() == $employee->id ? 'disabled' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                    @if(auth()->id() == $employee->id)
                                                    </span>
                                                    @endif
                                                </td>
                                            @endcan
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <div class="dropdown dropdown__style--two">
                                                        <button type="button" class="bg-transparent border-0 title-color"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                            <span class="material-symbols-outlined">more_vert</span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @can('employee_view')
                                                                <a data-bs-target="#exampleModal--{{$employee['id']}}"
                                                                   data-bs-toggle="modal" class="dropdown-item"
                                                                   href="#">{{translate('View Profile')}}</a>
                                                            @endcan
                                                            @can('employee_update')
                                                                <a class="dropdown-item"
                                                                   href="{{route('admin.employee.edit',[$employee->id])}}">{{translate('Edit Employee')}}</a>
                                                            @endcan
                                                            @can('employee_update')
                                                                <a class="dropdown-item {{ auth()->id() == $employee->id ? 'disabled opacity-50' : '' }}"
                                                                   href="{{ auth()->id() != $employee->id ? route('admin.employee.set.permission',[$employee->id]) : '#' }}"
                                                                   {{ auth()->id() == $employee->id ? 'aria-disabled=true tabindex=-1 title='.translate('You cannot set permissions on your own profile').' data-bs-toggle=tooltip data-bs-placement=right' : '' }}>{{translate('Set Permission')}}</a>
                                                            @endcan
                                                            @can('employee_delete')
                                                                @if(auth()->id() == $employee->id)
                                                                <span data-bs-toggle="tooltip" data-bs-placement="right" title="{{ translate('You cannot delete your own profile') }}" class="d-block">
                                                                @endif
                                                                <button type="button" data-delete="{{$employee->id}}"
                                                                        class="dropdown-item delete-action {{ auth()->id() == $employee->id ? 'disabled opacity-50' : '' }}"
                                                                        {{ auth()->id() == $employee->id ? 'disabled' : '' }}>{{translate('Delete Employee')}}
                                                                </button>
                                                                @if(auth()->id() == $employee->id)
                                                                </span>
                                                                @endif
                                                                <form
                                                                    action="{{route('admin.employee.delete',[$employee->id])}}"
                                                                    method="post" id="delete-{{$employee->id}}"
                                                                    class="hidden">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $employeeTableColspan,
                                            'variant' => (filled($search) || (($filterCounter ?? 0) > 0) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                            'showButton' => true,
                                            'buttonTextKey' => 'add_employee',
                                            'buttonUrl' => route('admin.employee.create')])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $employees->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <form action="{{ url()->current() }}" method="GET" class="offcanvas offcanvas-sm-500px offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Filter</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <input type="hidden" name="status" value="{{ $status }}">
           @if($search !== '')
               <input type="hidden" name="search" value="{{ $search }}">
           @endif
           <div class="d-flex flex-column gap-xxl-4 gap-3">
                <div class="bg-light rounded p-20">
                    <h6 class="mb-2 fs-14 fw-normal">Select Role</h6>
                    <select name="role_id" class="js-select h-40 rounded theme-input-style w-100 select2-hidden-accessible">
                        <option value="">{{ translate('Select Role') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ (string)$roleId === (string)$role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-light rounded p-20">
                      <h6 class="mb-20 fs-14 fw-medium">Select Permission</h6>
                      <div class="bg-white rounded p-3">
                            <div class="row gx-3 gy-4">
                                @foreach($moduleFilters as $moduleFilter)
                                    <div class="col-auto col-sm-6">
                                        <div class="d-flex gap-1 align-items-center">
                                            <input class="mb-0" type="checkbox" id="permission-{{ $moduleFilter['key'] }}" name="permission_sections[]" value="{{ $moduleFilter['key'] }}" {{ in_array($moduleFilter['key'], $permissionSections, true) ? 'checked' : '' }}>
                                            <label class="user-select-none fs-12 fw-normal flex-grow-1" for="permission-{{ $moduleFilter['key'] }}">
                                                {{ $moduleFilter['value'] }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                      </div>
                </div>
           </div>
        </div>
        <div class="offcanvas-footer border-0 bg-white p-3 px-sm-4 shadow-sm">
            <div class="d-flex justify-content-between gap-md-3 gap-2">
                <a href="{{ route('admin.employee.index', ['status' => $status, 'search' => $search]) }}" class="btn btn--secondary rounded flex-grow-1">{{ translate('Clear Filter') }}</a>
                <button type="submit" class="btn btn--primary rounded flex-grow-1">{{ translate('Apply') }}</button>
            </div>
        </div>
    </form>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/js/custom.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script>
        "use strict";

        function initEmployeeListTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((tooltipTriggerEl) => {
                bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
            });
        }

        initEmployeeListTooltips();

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{ route('admin.employee.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}');
        })

        $('.delete-action').on('click', function (event) {
            event.stopPropagation();
            let itemId = $(this).data('delete');
            @if(env('APP_ENV')!='demo')
            form_alert('delete-' + itemId, '{{translate('want_to_delete_this_employee')}}?')
            @endif
        })

        $('.remove').on('click', function (event) {
            event.stopPropagation();
            let itemId = $(this).data('remove');
            @if(env('APP_ENV')!='demo')
            form_alert('delete-' + itemId, '{{translate('want_to_delete_this_employee')}}?')
            @endif
        })
    </script>
@endpush
