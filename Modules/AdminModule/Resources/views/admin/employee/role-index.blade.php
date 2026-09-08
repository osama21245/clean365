@extends('adminmodule::layouts.master')

@section('title', translate('role_settings'))

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
                    <div class="page-title-wrap d-flex justify-content-between gap-2 mb-3">
                        <h2 class="page-title">{{translate('Employee_Role_List')}}</h2>
                        @can('role_add')
                            <a href="{{route('admin.role.create')}}" class="btn btn--primary">
                                <span class="material-symbols-outlined">add</span>
                                {{translate('add_Role')}}
                            </a>
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
                            <span class="opacity-75">{{translate('Total Employee Roles')}}:</span>
                            <span class="title-color">{{$roles->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{ url()->current() }}"
                                      class="search-form search-form_style-two"
                                      method="GET">
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_by_role_name')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        {{translate('search')}}
                                    </button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        @can('employee_export')
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span class="material-icons">file_download</span> {{ translate('download') }}
                                            </button>
                                        @endcan
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <a class="dropdown-item"
                                               href="{{route('admin.role.download')}}?search={{$search}}&status={{$status}}">
                                                {{translate('excel')}}
                                            </a>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                @php
                                    $roleTableColspan = 3;
                                @endphp
                                @can('role_manage_status')
                                    @php
                                        $roleTableColspan++;
                                    @endphp
                                @endcan
                                @canany(['role_delete', 'role_update'])
                                    @php
                                        $roleTableColspan++;
                                    @endphp
                                @endcanany
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('role_name')}}</th>
                                        <th>{{translate('Modules')}}</th>
                                        @can('role_manage_status')
                                            <th>{{translate('status')}}</th>
                                        @endcan
                                        @canany(['role_delete', 'role_update'])
                                            <th class="text-center">{{translate('action')}}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($roles as $key => $item)
                                        @php
                                            $output = '';
                                        @endphp
                                        @foreach(SYSTEM_MODULES as $module)
                                            @php
                                                $matchedRoleBtn = Modules\UserManagement\Entities\RoleAccess::where('role_id', $item->id)->where('section_name', $module['key'])->first();
                                                $hasMatchingSubmodules = false;
                                            @endphp

                                            @if(isset($module['submodules']))
                                                @foreach($module['submodules'] as $submodule)
                                                    @php
                                                        $matchedRoleSection = Modules\UserManagement\Entities\RoleAccess::where('role_id', $item->id)->where('section_name', $submodule['key'])->first();
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
                                                            $matchedRoleSection = Modules\UserManagement\Entities\RoleAccess::where('role_id', $item->id)->where('section_name', $submodule['key'])->first();
                                                        @endphp
                                                        @if($matchedRoleSection)
                                                            @php
                                                                $submodulesOutput .= $submodule['value'] . ', ';
                                                            @endphp
                                                        @endif
                                                    @endforeach

                                                    @php
                                                        $output .= rtrim($submodulesOutput, ', ') . ') ';
                                                    @endphp
                                                @endif
                                            @elseif($matchedRoleBtn)
                                                @php
                                                    $output .= $module['value'] . ', ';
                                                @endphp
                                            @endif
                                        @endforeach
                                        <tr>
                                            <td>{{$key+$roles?->firstItem()}}</td>
                                            <td>{{$item->role_name}}</td>
                                            <td>
                                                <span class="role-tooltip-trigger"
                                                      data-bs-toggle="tooltip"
                                                      data-bs-placement="top"
                                                      data-bs-title="{{ rtrim($output, ', ') }}">
                                                    {{ Str::limit(rtrim($output, ', '), 150) }}
                                                </span>
                                            </td>
                                        @can('role_manage_status')
                                                <td>
                                                    @php $isOwnRole = auth()->user()->roles->contains('id', $item->id); @endphp
                                                    @if($isOwnRole)
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('You cannot change your own role status') }}" class="d-inline-block">
                                                    @endif
                                                    <label class="switcher {{ $isOwnRole ? 'opacity-50 pe-none' : '' }}">
                                                        <input class="switcher_input status_updated" type="checkbox"
                                                               {{$item->is_active?'checked':''}} data-status="{{$item->id}}"
                                                               {{ $isOwnRole ? 'disabled' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                    @if($isOwnRole)
                                                    </span>
                                                    @endif
                                                </td>
                                            @endcan
                                            @canany(['role_delete', 'role_update'])
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        @can('role_update')
                                                            @if($isOwnRole)
                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('You cannot edit your own role') }}" class="d-inline-block">
                                                            @endif
                                                            <a href="{{route('admin.role.edit',[$item->id])}}"
                                                               class="action-btn btn--light-primary {{ $isOwnRole ? 'opacity-50 pe-none' : '' }}"
                                                               style="--size: 30px">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                            @if($isOwnRole)
                                                            </span>
                                                            @endif
                                                        @endcan
                                                        @can('role_delete')
                                                            @if($isOwnRole)
                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('You cannot delete your own role') }}" class="d-inline-block">
                                                            @endif
                                                            <button type="button" data-delete="{{$item->id}}"
                                                                    class="action-btn btn--danger {{ $isOwnRole ? 'opacity-50' : '' }}" style="--size: 30px"
                                                                    {{ $isOwnRole ? 'disabled' : '' }}>
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            @if($isOwnRole)
                                                            </span>
                                                            @endif
                                                        @endcan
                                                    </div>
                                                    <form action="{{route('admin.role.delete',[$item->id])}}"
                                                          method="post" id="delete-{{$item->id}}"
                                                          class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $roleTableColspan,
                                            'variant' => (filled($search) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                            'showButton' => true,
                                            'buttonTextKey' => 'add_Role',
                                            'buttonUrl' => route('admin.role.create')])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $roles->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/js/custom.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
    <script>
        "use strict";

        function initRoleIndexTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((tooltipTriggerEl) => {
                bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
            });
        }

        initRoleIndexTooltips();

        $('.status_updated').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{ route('admin.role.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}');
        })

        $('.action-btn.btn--danger').on('click', function () {
            let itemId = $(this).data('delete');
            @if(env('APP_ENV')!='demo')
            form_alert('delete-' + itemId, '{{translate('want_to_delete_this_role')}}?')
            @endif
        })
    </script>
@endpush
