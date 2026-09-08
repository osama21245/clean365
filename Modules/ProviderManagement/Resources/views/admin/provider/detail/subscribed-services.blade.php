@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            @include('providermanagement::admin.provider.detail._tabs')

            <div class="tab-content">
                <div class="tab-pane fade show active" id="subscribed-tab-pane">
                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=all">{{translate('All')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='subscribed'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=subscribed">{{translate('Subscribed')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='unsubscribed'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=unsubscribed">{{translate('Unsubscribed')}}</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Sub_Categories')}}:</span>
                            <span class="title-color">{{$subCategories->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form
                                            action="{{url()->current()}}?web_page=subscribed_services&status={{$status}}"
                                            class="search-form search-form_style-two"
                                            method="POST">
                                            @csrf
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$search??''}}" name="search"
                                                       placeholder="{{translate('search_here')}}">
                                            </div>
                                            <button type="submit" class="btn btn--primary">
                                                {{translate('search')}}
                                            </button>
                                        </form>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-center align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{translate('Sub_Category_Name')}}</th>
                                                <th>{{translate('Services')}}</th>
                                                <th>{{translate('Subscribe_/_Unsubscribe')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($subCategories as $sub_category)
                                                <tr>
                                                    <td>
                                                        <div data-bs-toggle="modal"
                                                             data-bs-target="#showServiceModal">{{Str::limit($sub_category->sub_category?$sub_category->sub_category->name:'', 30)}}</div>
                                                    </td>
                                                    <td>{{$sub_category->sub_category?$sub_category->sub_category->services_count:0}}</td>
                                                    <td>
                                                        @can('provider_manage_status')
                                                            <label class="switcher">
                                                                <input class="switcher_input status-update"
                                                                       type="checkbox"
                                                                       id="subscribed-status-{{$sub_category->id}}"
                                                                       {{$sub_category->is_subscribed == 1 ? 'checked' : ''}}
                                                                       data-status="{{$sub_category->id}}">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @empty
                                                @include('adminmodule::layouts.partials.components._empty-state', [
                                                    'variant' => (filled($search ?? null) || (($status ?? 'all') !== 'all')) ? 'search' : 'list'])
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $subCategories->links() !!}
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
    <script>
        "use strict"

        $('.status-update').on('click', function () {
            let $this = $(this);
            let itemId = $(this).data('status');
            let initialState = $this.prop('checked');
            let route = '{{ route('admin.provider.sub_category.update_subscription', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}', true, initialState ? 1 : 0, 'subscribed-status-' + itemId);
        })
    </script>
@endpush
