@extends('adminmodule::layouts.master')

@section('title',translate('discounts'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('discounts')}}</h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$type=='all'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['type' => 'all', 'page' => null]) }}">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$type=='category'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['type' => 'category', 'page' => null]) }}">
                                    {{translate('category_wise')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$type=='service'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['type' => 'service', 'page' => null]) }}">
                                    {{translate('service_wise')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$type=='additional_service'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['type' => 'additional_service', 'page' => null]) }}">
                                    {{translate('Additional Services')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$type=='mixed'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['type' => 'mixed', 'page' => null]) }}">
                                    {{translate('mixed')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Discount')}}:</span>
                            <span class="title-color">{{$discounts->total()}}</span>
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
                                            <input type="hidden" name="type" value="{{ $type }}">
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

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            @can('discount_export')
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                    <span
                                                        class="material-icons">file_download</span> {{translate('download')}}
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <a class="dropdown-item"
                                                           href="{{route('admin.discount.download')}}?search={{$search}}&type={{ $type }}">
                                                            {{translate('excel')}}
                                                        </a>
                                                    </ul>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        @php
                                            $discountTableColspan = 4;
                                        @endphp
                                        @can('discount_manage_status')
                                            @php
                                                $discountTableColspan++;
                                            @endphp
                                        @endcan
                                        @canany(['discount_delete', 'discount_update'])
                                            @php
                                                $discountTableColspan++;
                                            @endphp
                                        @endcanany
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th>{{translate('Sl')}}</th>
                                                <th>{{translate('title')}}</th>
                                                <th>{{translate('discount_type')}}</th>
                                                <th>{{translate('zones')}}</th>
                                                @can('discount_manage_status')
                                                    <th>{{translate('status')}}</th>
                                                @endcan
                                                @canany(['discount_delete', 'discount_update'])
                                                    <th>{{translate('action')}}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($discounts as $key => $discount)
                                                <tr>
                                                    <td>{{$key+$discounts->firstItem()}}</td>
                                                    <td>{{$discount->discount_title}}</td>
                                                    <td>{{$discount->discount_type}}</td>
                                                    <td>
                                                        @foreach($discount->zone_types as $type)
                                                            {{$type->zone?$type->zone->name.',':''}}
                                                        @endforeach
                                                    </td>
                                                    @can('discount_manage_status')
                                                        <td>
                                                            <label class="switcher" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input"
                                                                       data-status="{{$discount->id}}"
                                                                       type="checkbox" {{$discount->is_active?'checked':''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['discount_delete', 'discount_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                @can('discount_update')
                                                                    <a href="{{route('admin.discount.edit',[$discount->id])}}"
                                                                       class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                @endcan
                                                                @can('discount_delete')
                                                                    <button type="button"
                                                                            data-id="{{$discount->id}}"
                                                                            class="action-btn btn--danger delete_section"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.discount.delete',[$discount->id])}}"
                                                                        method="post" id="delete-{{$discount->id}}"
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
                                                    'colspan' => $discountTableColspan,
                                                    'variant' => (filled($search) || (($type ?? 'all') !== 'all')) ? 'search' : 'list'])
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $discounts->links() !!}
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
        "use Strict"

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{ route('admin.discount.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert(route, '{{ translate('want_to_update_status') }}');
        })

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '{{ translate('want_to_delete_this_discount') }}');
        })

        $(document).ready(function () {
            $('.js-select').select2();
        });
    </script>

    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
@endpush
