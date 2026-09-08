@extends('adminmodule::layouts.master')

@section('title',translate('service_list'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title">{{translate('service_list')}}</h2>
                        <div>
                            @can('service_add')
                                <a href="{{route('admin.service.create')}}" class="btn btn--primary">
                                    <span class="material-icons">add</span>
                                    {{translate('add_service')}}
                                </a>
                            @endcan
                        </div>
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
                            <span class="opacity-75">{{translate('Total_Services')}}:</span>
                            <span class="title-color">{{$services->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
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
                                                       placeholder="{{translate('search_here')}}">
                                            </div>
                                            <button type="submit"
                                                    class="btn btn--primary">{{translate('search')}}</button>
                                        </form>
                                    </div>

                                    <div class="table-responsive">
                                        @php
                                            $serviceTableColspan = 6;
                                        @endphp
                                        @can('service_manage_status')
                                            @php
                                                $serviceTableColspan++;
                                            @endphp
                                        @endcan
                                        @canany(['service_delete', 'service_update'])
                                            @php
                                                $serviceTableColspan++;
                                            @endphp
                                        @endcanany
                                        <table id="example" class="table align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('name')}}</th>
                                                <th>{{translate('category')}}</th>
                                                <th>{{translate('zones')}}</th>
                                                <th>{{translate('Minimum Bidding Price')}}</th>
                                                <th>{{translate('badge')}}</th>
                                                @can('service_manage_status')
                                                    <th>{{translate('status')}}</th>
                                                @endcan
                                                @canany(['service_delete', 'service_update'])
                                                    <th>{{translate('action')}}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($services as $key=>$service)
                                                <tr>
                                                    <td>{{$services->firstitem()+$key}}</td>
                                                    <td>
                                                        <a href="{{route('admin.service.detail',[$service->id])}}">
                                                            {{Str::limit($service->name, 50)}}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if($service->category)
                                                            {{$service->category->name}}
                                                        @else
                                                            <div class="d-flex">
                                                                <span>{{ translate('Unavailable') }}</span>
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Update the service category')}}">info
                                                                </i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($service->category)
                                                            @if(count($service->category->zonesBasicInfo) > 0)
                                                             {{implode(', ',$service->category->zonesBasicInfo->pluck('name')->toArray())}}
                                                            @else
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('This category is not under any zone. Kindly update the category with zone')}}">info
                                                                </i>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{with_currency_symbol($service->min_bidding_price)}}

                                                        @if($service->min_bidding_price == 0)
                                                            <i class="text-warning material-icons px-1"
                                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                                               title="{{translate('Update the minimum bidding price')}}"
                                                            >warning</i>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(count($service->badge) > 0)
                                                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                                                @foreach($service->badge as $b)
                                                                    <span class="badge d-inline-flex align-items-center gap-1 py-1 px-2 fw-medium"
                                                                          style="background-color: {{ $b['bg_color'] }}; color: {{ $b['text_color'] }}; border: 1px solid {{ $b['border_color'] }}; border-radius: 12px; font-size: 11px;">
                                                                        <span class="material-icons" style="font-size: 13px;">{{ $b['icon'] }}</span>
                                                                        {{ app()->getLocale() == 'ar' ? $b['name_ar'] : $b['name'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="badge text-muted" style="background-color: #f5f5f5; border-radius: 12px; font-size: 11px;">
                                                                {{ translate('standard') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    @can('service_manage_status')
                                                        <td>
                                                            <label class="switcher mx-auto" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input status-update"
                                                                       type="checkbox"
                                                                       id="service-status-{{$service->id}}"
                                                                       {{ $service->is_active ? 'checked' : '' }} data-status="{{ $service->id }}">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['service_delete', 'service_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                @can('service_update')
                                                                    <a href="{{route('admin.service.edit',[$service->id])}}"
                                                                       class="action-btn btn--light-primary demo_check"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                @endcan
                                                                @can('service_delete')
                                                                    <button type="button"
                                                                            data-id="delete-{{$service->id}}"
                                                                            data-message="{{translate('want_to_delete_this_service')}}?"
                                                                            class="action-btn btn--danger {{ env('APP_ENV')!='demo' ? 'form-alert' : 'demo_check'}}"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.service.delete',[$service->id])}}"
                                                                        method="post" id="delete-{{$service->id}}"
                                                                        class="hidden">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @empty
                                                @include('adminmodule::layouts.partials.components._empty-state', [
                                                    'colspan' => $serviceTableColspan,
                                                    'variant' => (filled($search) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                                    'showButton' => true,
                                                    'buttonTextKey' => 'add_service',
                                                    'buttonUrl' => route('admin.service.create')])
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $services->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script>
        "use strict"

        $(document).ready(function () {
            $('.js-select').select2();

            $('.status-update').on('click', function () {
                let $this = $(this);
                let itemId = $(this).data('status');
                let initialState = $this.prop('checked');
                let route = '{{ route('admin.service.status-update', ['id' => ':itemId']) }}';
                let message = initialState
                    ? '{{ translate('If you turn it on, this service will be visible on the website.') }}'
                    : '{{ translate('If you turn it off, this service will not be visible on the website.') }}';
                route = route.replace(':itemId', itemId);
                route_alert_reload(route, message, true, initialState ? 1 : 0, 'service-status-' + itemId);
            });
        });
    </script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
@endpush
