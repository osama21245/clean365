@extends('providermanagement::layouts.master')

@section('title', translate('Request List'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
        .filter-aside__body {
            block-size: auto;
        }

        .filter-aside__header .filter-aside__close-btn {
            width: 1.5rem;
            height: 1.5rem;
            min-width: 1.5rem;
            border-radius: 50%;
            background-color: var(--bs-primary-dark);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 0.625rem;
            opacity: 1;
            transition: background-color .2s ease, box-shadow .2s ease;
        }

        .filter-aside__header .filter-aside__close-btn:hover,
        .filter-aside__header .filter-aside__close-btn:focus {
            background-color: var(--bs-primary);
            opacity: 1;
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.16);
        }

        .provider-customize-list-page,
        .provider-customize-list-page .row,
        .provider-customize-list-page .col-12,
        .provider-customize-list-page .card,
        .provider-customize-list-page .card-body,
        .provider-customize-list-page .customize-list-table,
        .provider-customize-list-page .select-table-wrap {
            min-width: 0;
        }

        .customize-list-table .table-responsive {
            --customize-action-menu-space: 0px;
            width: 100%;
            max-width: 100%;
            padding-bottom: var(--customize-action-menu-space);
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .customize-list-table .table-actions {
            display: flex !important;
            width: max-content;
            margin-inline: auto;
            position: relative;
        }

        .customize-list-table .table-actions .action-btn.show,
        .customize-list-table .table-actions .action-btn[aria-expanded="true"] {
            color: var(--bs-white);
            border-color: var(--bs-primary);
            background-color: var(--bs-primary);
        }

        .customize-list-table .table-actions .action-btn.show .material-icons,
        .customize-list-table .table-actions .action-btn.show .material-symbols-outlined,
        .customize-list-table .table-actions .action-btn[aria-expanded="true"] .material-icons,
        .customize-list-table .table-actions .action-btn[aria-expanded="true"] .material-symbols-outlined {
            color: var(--bs-white);
        }

        .customize-list-table .table-actions .dropdown-menu {
            position: absolute;
            top: calc(100% + 4px) !important;
            right: 0 !important;
            bottom: auto !important;
            left: auto !important;
            transform: none !important;
        }

        .customize-list-table .table-actions.dropdown-open-up .dropdown-menu {
            top: auto !important;
            bottom: calc(100% + 4px) !important;
        }

        .customize-list-table .table-responsive .dropdown-menu {
            z-index: 1051;
        }

        .customize-list-table .multi-select-table-provider {
            width: 100% !important;
            min-width: 0;
        }

        .customize-list-table .multi-select-table-provider th,
        .customize-list-table .multi-select-table-provider td {
            white-space: normal;
        }

        .customize-list-table .multi-select-table-provider th:last-child,
        .customize-list-table .multi-select-table-provider td:last-child {
            width: 1%;
            white-space: nowrap;
        }

        .customize-list-table .table-actions .dropdown-menu,
        .customize-list-table .table-actions .dropdown-item {
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .provider-customize-list-page .data-table-top {
                flex-direction: column;
                align-items: stretch;
            }

            .provider-customize-list-page .data-table-top > * {
                min-width: 0;
            }

            .provider-customize-list-page .data-table-top .search-form {
                width: 100% !important;
            }

            .provider-customize-list-page .data-table-top > .d-flex {
                width: 100%;
                justify-content: space-between;
            }
        }

    </style>
@endpush

@section('content')
    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title">{{translate('Filter Customized Booking')}}</h3>
            <button type="button" class="btn-close p-1 filter-aside__close-btn"></button>
        </div>
        <form action="{{ route('provider.booking.post.list') }}" method="GET" enctype="multipart/form-data" id="filter-form">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{translate('Select Categories')}}</h4>
                    <div class="mb-30">
                        <select class="category-select theme-input-style w-100" name="category_id" id="category_selector__select">
                            <option value="">{{ translate('Select Category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ ($category->id == ($queryParams['category_id'] ?? '')) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-aside__date_range">
                    <div class="filter-aside__zone_select">
                        <h4 class="mb-2 fw-normal">{{translate('Select Date Range')}}</h4>
                        <div class="mb-30">
                            <select class="date-select theme-input-style w-100" name="select_date" id="select_date">
                                <option value="">{{translate('Select date')}}</option>
                                <option value="today" {{ (isset($queryParams['select_date']) && $queryParams['select_date'] == 'today') ? 'selected' : '' }}> {{translate('Today')}}</option>
                                <option value="this_week" {{ (isset($queryParams['select_date']) && $queryParams['select_date'] == 'this_week') ? 'selected' : '' }}> {{translate('This week')}}</option>
                                <option value="this_month" {{ (isset($queryParams['select_date']) && $queryParams['select_date'] == 'this_month') ? 'selected' : '' }}> {{translate('This Month')}}</option>
                                <option value="custom_range" {{ (isset($queryParams['select_date']) && $queryParams['select_date'] == 'custom_range') ? 'selected' : '' }}> {{translate('Custom Range')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-30 custom-date-range" style="display: none;">
                        <div class="ff-field" data-ff-field>
                            <label for="filter-start-date" class="ff-field__label">
                                <span class="ff-field__label-text">{{ translate('Start Date') }}</span>
                            </label>
                            <div class="ff-field__control">
                                <input type="text"
                                       id="filter-start-date"
                                       name="start_date"
                                       class="form-control ff-field__input"
                                       placeholder="{{ translate('Start Date') }}"
                                       value="{{ $queryParams['start_date'] ?? '' }}"
                                       data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            </div>
                            <div class="ff-field__footer"><div class="ff-field__messages"></div></div>
                        </div>
                    </div>
                    <div class="fw-normal mb-30 custom-date-range" style="display: none;">
                        <div class="ff-field" data-ff-field>
                            <label for="filter-end-date" class="ff-field__label">
                                <span class="ff-field__label-text">{{ translate('End Date') }}</span>
                            </label>
                            <div class="ff-field__control">
                                <input type="text"
                                       id="filter-end-date"
                                       name="end_date"
                                       class="form-control ff-field__input"
                                       placeholder="{{ translate('End Date') }}"
                                       value="{{ $queryParams['end_date'] ?? '' }}"
                                       data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            </div>
                            <div class="ff-field__footer"><div class="ff-field__messages"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize" id="reset-btn" type="button">{{translate('Clear all Filter')}}</button>
                    <button class="btn btn--primary text-capitalize" type="submit">{{translate('Filter')}}</button>
                </div>
            </div>
        </form>
    </div>

    <div class="container-fluid provider-customize-list-page">
        @if(!request()->user()->provider->service_availability && $type == 'new_booking_request')
            <div class="alert alert-primary availability-alert">
                <div class="media gap-3 align-items-center">
                    <div class="alert-close-btn">
                        <span class="material-symbols-outlined close-btn">close</span>
                    </div>
                    <div class="media-body">
                        <h5 class="text-capitalize">{{translate('Attention Please')}}!</h5>
                        <p class="text-dark fs-12">
                            {{translate('The service availability option has been turned off. You will not receive any new customized requests until you turn on the service availability option')}}
                            <span><a class="text-primary"
                                     href="{{route('provider.business-settings.get-business-information')}}">{{translate('Go to settings')}}</a></span>
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title">{{translate('Customized Booking Requests')}}</h2>
                </div>

                <div
                    class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                    <ul class="nav nav--tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{$type=='all'?'active':''}}"
                               href="{{ request()->fullUrlWithQuery(['type' => 'all', 'page' => null]) }}">{{translate('All')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$type=='new_booking_request'?'active':''}}"
                               href="{{ request()->fullUrlWithQuery(['type' => 'new_booking_request', 'page' => null]) }}">{{translate('New Offer Requests')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$type=='placed_offer'?'active':''}}"
                               href="{{ request()->fullUrlWithQuery(['type' => 'placed_offer', 'page' => null]) }}">{{translate('My Bid requests')}}</a>
                        </li>
                    </ul>

                    <div class="d-flex gap-2 fw-medium">
                        <span class="opacity-75">{{translate('Total Customized Booking')}} : </span>
                        <span class="title-color">{{$posts->count()}}</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                            <form action="{{ route('provider.booking.post.list') }}" method="GET"
                                  class="search-form search-form_style-two">
                                <input type="hidden" name="type" value="{{ $type }}">
                                @foreach(['category_id', 'select_date', 'start_date', 'end_date'] as $field)
                                    @if(isset($queryParams[$field]))
                                        <input type="hidden" name="{{$field}}" value="{{ $queryParams[$field] }}">
                                    @endif
                                @endforeach
                                <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                    <input type="search" class="theme-input-style search-form__input fz-10"
                                           name="search"
                                           value="{{$search??''}}"
                                           placeholder="{{translate('Search by customer info')}}">
                                </div>
                                <button type="submit" class="btn btn--primary text-capitalize">
                                    {{translate('Search')}}</button>
                            </form>

                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="dropdown">
                                    <button type="button"
                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        <span class="material-icons">file_download</span> {{translate('download')}}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                        <li><a class="dropdown-item"
                                               href="{{route('provider.booking.post.export', [
                                                    'type' => $type ?? '',
                                                    'search' => $search ?? '',
                                                    'category_id' => $category_id ?? '',
                                                    'select_date' => $select_date ?? '',
                                                    'start_date' => $start_date ?? '',
                                                    'end_date' => $end_date ?? ''
                                               ])}}">{{translate('Excel')}}</a>
                                        </li>
                                    </ul>
                                </div>
                                <button type="button" class="btn text-capitalize filter-btn border px-3">
                                    <span class="material-icons">filter_list</span> {{translate('Filter')}}
                                    <span class="count">{{$filterCounter??0}}</span>
                                </button>
                            </div>
                        </div>

                        <div class="select-table-wrap customize-list-table">
                            <div
                                class="multiple-select-actions gap-3 flex-wrap align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-wrap gap-2 gap-lg-4">
                                    <div class="ms-sm-1">
                                        <input type="checkbox" class="multi-checker">
                                    </div>
                                    <p><span class="checked-count">2</span> {{translate('Item Selected')}}</p>
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-3">
                                    <button class="btn btn--danger" id="multi-ignore">{{translate('Ignore')}}</button>
                                </div>
                            </div>
                            <div class="table-responsive position-relative">
                                @php
                                    $providerBidCustomizeTableColspan = 6;
                                    $hasProviderBidCustomizeFilters = (($type ?? 'all') !== 'all')
                                        || filled($search)
                                        || filled($queryParams['category_id'] ?? null)
                                        || filled($queryParams['select_date'] ?? null)
                                        || filled($queryParams['start_date'] ?? null)
                                        || filled($queryParams['end_date'] ?? null);
                                @endphp
                                @if($bid_offers_visibility_for_providers)
                                    @php
                                        $providerBidCustomizeTableColspan++;
                                    @endphp
                                @endif
                                <table class="table align-middle  multi-select-table multi-select-table-provider w-100">
                                    <thead>
                                    <tr>
                                        @if($type == 'new_booking_request')
                                            <th></th>
                                        @endif
                                        @if($type != 'new_booking_request')
                                            <th>{{translate('Booking ID')}}</th>
                                        @endif
                                        <th>{{translate('Customer Info')}}</th>
                                        <th>{{translate('Booking Request Time')}}</th>
                                        <th>{{translate('Service Time')}}</th>
                                        <th>{{translate('Category')}}</th>
                                        @if($bid_offers_visibility_for_providers)
                                            <th>{{translate('Other Provider Offering')}}</th>
                                        @endif
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($posts as $key=>$post)
                                        <tr>
                                            @if($type == 'new_booking_request')
                                                <td><input type="checkbox" class="multi-check" value="{{$post->id}}">
                                                </td>
                                            @endif
                                            @if($type != 'new_booking_request')
                                                @if($post->booking)
                                                    <td>
                                                        <a href="{{route('provider.booking.details', [$post?->booking->id,'web_page'=>'details'])}}">{{$post?->booking->readable_id}}</a>
                                                    </td>
                                                @else
                                                    <td><small
                                                            class="badge-pill badge-primary">{{translate('Not Booked Yet')}}</small>
                                                    </td>
                                                @endif
                                            @endif
                                            <td>
                                                @if($post->customer)
                                                    <div>
                                                        <div class="customer-name fw-medium">
                                                            {{$post->customer?->first_name.' '.$post->customer?->last_name}}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div><small
                                                            class="disabled">{{translate('Customer not available')}}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{$post->created_at->format('Y-m-d')}}</div>
                                                    <div>{{ format_time_by_business_settings($post->created_at) }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{date('d-m-Y',strtotime($post->booking_schedule))}}</div>
                                                    <div>{{ format_time_by_business_settings($post->booking_schedule) }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($post->category)
                                                    {{$post->category?->name}}
                                                @else
                                                    <div><small class="disabled">
                                                            {{translate('Category not available')}}</small></div>
                                                @endif
                                            </td>
                                            @if($bid_offers_visibility_for_providers)
                                                @php($bids = $post->bids->where('provider_id', '!=', auth()->user()->provider->id))
                                                <td>
                                                    <div class="dropdown-hover">
                                                        <div class="dropdown-hover-toggle"
                                                             data-bs-toggle="dropdown">
                                                            {{ translate(':count Providers', ['count' => $bids->count() ?? 0]) }}
                                                        </div>

                                                        @if($bids->count() > 0)
                                                            <ul class="dropdown-hover-menu">
                                                                @foreach($bids as $bid)
                                                                    <li>
                                                                        <div class="media gap-3">
                                                                            <div class="avatar border rounded"
                                                                                 data-bs-toggle="modal"
                                                                                 data-bs-target="#providerInfoModal--{{$bid->id}}">
                                                                                <img
                                                                                    src="{{onErrorImage(
                                                                                            $bid->provider?->logo,
                                                                                            asset('storage/app/public/provider/logo').'/' . $bid->provider?->logo,
                                                                                            asset('public/assets/admin-module/img/placeholder.png') ,
                                                                                            'provider/logo/')}}"
                                                                                    class="rounded"
                                                                                    alt="{{ translate('logo') }}">
                                                                            </div>
                                                                            <div class="media-body">
                                                                                @if($bid->provider)
                                                                                    <h5 data-bs-toggle="modal"
                                                                                        data-bs-target="#providerInfoModal--{{$bid->id}}">{{$bid->provider->company_name}}</h5>
                                                                                @else
                                                                                    <small>{{translate('Provider not available')}}</small>
                                                                                @endif
                                                                                <div
                                                                                    class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1">
                                                                                            <span
                                                                                                class="text-danger">{{translate('price offered')}}</span>
                                                                                    <h5 class="text-primary">{{with_currency_symbol($bid->offered_price)}}</h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                            <td>
                                                <div class="table-actions dropdown d-flex justify-content-center">
                                                    <button type="button"
                                                            class="table-actions_view action-btn"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-display="static">
                                                        <span class="material-icons">more_horiz</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right dropdown-menu-end">
                                                        <li><a class="dropdown-item"
                                                               href="{{route('provider.booking.post.details', [$post->id])}}">{{translate('View details')}}</a>
                                                        </li>
                                                        @if($post?->bids->contains('provider_id', auth()->user()->provider->id))
                                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                                   data-bs-target="#offerDetailsModal--{{$post['id']}}">{{translate('See My Offer')}}</a>
                                                            </li>
                                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                                   data-bs-target="#withdrawRequestModal--{{$post['id']}}">{{translate('Withdraw the Offer')}}</a>
                                                            </li>

                                                        @endif

                                                        @if(!$post->is_booked && !$post?->bids->contains('provider_id', auth()->user()->provider->id))
                                                            <li>
                                                                <button class="dropdown-item" href="#"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#newBookingModal--{{$post['id']}}">{{translate('Placed Offer')}}</button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item" data-bs-toggle="modal"
                                                                        data-bs-target="#ignoreRequestModal--{{$post['id']}}">{{translate('Ignore/Reject')}}</button>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>


                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $providerBidCustomizeTableColspan,
                                            'variant' => $hasProviderBidCustomizeFilters ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            {!! $posts->links() !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @foreach($posts as $post)
                                        <div class="modal fade" id="offerDetailsModal--{{$post['id']}}" tabindex="-1"
                                             aria-labelledby="offerDetailsModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog offer-detail-modal">
                                                <div class="modal-content">
                                                    <div class="modal-header px-sm-4">
                                                        <h4 class="modal-title text-primary"
                                                            id="offerDetailsModalLabel">{{translate('My Offer Details')}}</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pb-4 px-lg-4">
                                                        <div class="">

                                                            <div class="d-flex gap-4 mb-4">
                                                                <div class="media gap-2 ">
                                                                    <img width="30"
                                                                         src="{{onErrorImage(
                                                                        $post?->sub_category?->image,
                                                                        asset('storage/app/public/category').'/' . $post?->sub_category?->image,
                                                                        asset('public/assets/placeholder.png') ,
                                                                        'category/')}}"
                                                                         alt="{{translate('sub category')}}">
                                                                    <div class="media-body">
                                                                        <h5>{{$post?->service?->name}}</h5>
                                                                        <div
                                                                            class="text-muted fs-12">{{$post?->sub_category?->name}}</div>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <div class=" border-start ps-4">
                                                                        <div
                                                                            class="d-flex gap-2 flex-wrap align-items-center">
                                                                            <span
                                                                                class="text-danger fs-12">{{translate('price offered')}}</span>
                                                                            <h4 class="text-primary">{{$post?->bids->where('provider_id', auth()->user()->provider->id)->first()?->offered_price ?? 0}}</h4>
                                                                        </div>
                                                                        <span
                                                                            class="text-muted fs-12">{{$post->updated_at->diffForHumans()}}</span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <h3 class="text-muted mb-2">{{translate('Description')}}
                                                                :</h3>
                                                            <p>{{$post?->bids?->where('provider_id', auth()->user()->provider->id)?->first()?->provider_note}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="newBookingModal--{{$post['id']}}" tabindex="-1"
                                             aria-labelledby="newBookingModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form
                                                        action="{{route('provider.booking.post.update_status', [$post->id])}}"
                                                        method="GET"
                                                        id="provider-bid-place-offer-form-{{ $post['id'] }}"
                                                        class="provider-bid-place-offer-form"
                                                        data-ff-validate novalidate>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="newBookingModalLabel">{{translate('New Booking Request Form')}}</h5>
                                                            <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="card border">
                                                                <div class="card-body">
                                                                    <div class="d-flex gap-4 mb-4">
                                                                        <div class="media gap-2">
                                                                            <div class="avatar avatar-lg rounded">
                                                                                <img
                                                                                    src="{{onErrorImage(
                                                                                    $post?->customer?->profile_image,
                                                                                    asset('storage/app/public/user/profile_image').'/' . $post?->customer?->profile_image,
                                                                                    asset('public/assets/placeholder.png') ,
                                                                                    'user/profile_image/')}}"
                                                                                    alt="{{translate('image')}}">
                                                                            </div>
                                                                            <div class="media-body">
                                                                                <h5 class="text-primary">{{$post?->customer?->first_name.' '.$post?->customer?->last_name}}</h5>
                                                                                <div class="text-muted fs-12">
                                                                                    @if($post->distance)
                                                                                        {{ translate(':distance away from you', ['distance' => $post->distance]) }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="media gap-2 border-start ps-4">
                                                                                <img width="30"
                                                                                     src="{{onErrorImage(
                                                                                    $post?->sub_category?->image,
                                                                                    asset('storage/app/public/category').'/' . $post?->sub_category?->image,
                                                                                    asset('public/assets/placeholder.png') ,
                                                                                    'category/')}}"
                                                                                     alt="{{translate('image')}}">
                                                                                <div class="media-body">
                                                                                    <h5>{{$post?->service?->name}}</h5>
                                                                                    <div
                                                                                        class="text-muted fs-12">{{$post?->sub_category?->name}}</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                                        <img width="18"
                                                                             src="{{asset('public/assets/provider-module')}}/img/media/edit-info.png"
                                                                             alt="">
                                                                        <h4>{{translate('Service Requirement')}}</h4>
                                                                    </div>

                                                                    @if($post->service_description)
                                                                        <p class="fs-12">{{$post->service_description}}</p>
                                                                    @else
                                                                        <span
                                                                            class="small">{{translate('Not Available')}}</span>
                                                                    @endif

                                                                </div>
                                                            </div>

                                                            <div class="card border mt-3">
                                                                <div class="card-body">
                                                                    <div class="ff-field mb-30" data-ff-field>
                                                                        <label for="offer-price-{{ $post['id'] }}" class="ff-field__label">
                                                                            <span class="ff-field__label-text">{{ translate('Offer Price') }}<span class="ff-field__required">*</span></span>
                                                                        </label>
                                                                        <div class="ff-field__control">
                                                                            <input type="number"
                                                                                   id="offer-price-{{ $post['id'] }}"
                                                                                   name="offered_price"
                                                                                   class="form-control ff-field__input"
                                                                                   placeholder="{{ translate('Enter Offer Price') }}"
                                                                                   min="{{ $post?->service?->min_bidding_price ?? 0 }}"
                                                                                   step="any"
                                                                                   required
                                                                                   data-bs-toggle="tooltip"
                                                                                   data-bs-placement="top"
                                                                                   title="{{ translate('Minimum Offer price :price', ['price' => with_currency_symbol($post?->service?->min_bidding_price ?? 0)]) }}">
                                                                        </div>
                                                                        <div class="ff-field__footer"><div class="ff-field__messages"></div></div>
                                                                    </div>
                                                                    <div class="ff-field ff-field--textarea" data-ff-field>
                                                                        <label for="add-your-note-{{ $post['id'] }}" class="ff-field__label">
                                                                            <span class="ff-field__label-text">{{ translate('Add Your Note') }}</span>
                                                                        </label>
                                                                        <div class="ff-field__control">
                                                                            <textarea name="provider_note"
                                                                                      id="add-your-note-{{ $post['id'] }}"
                                                                                      class="form-control ff-field__input ff-field__textarea"
                                                                                      placeholder="{{ translate('Write a note about your offer (optional)') }}"
                                                                                      rows="3"
                                                                                      maxlength="255"
                                                                                      data-char-count
                                                                                      data-char-count-target="#add-your-note-counter-{{ $post['id'] }}"></textarea>
                                                                        </div>
                                                                        <div class="ff-field__footer">
                                                                            <div class="ff-field__messages"></div>
                                                                            <small id="add-your-note-counter-{{ $post['id'] }}" class="ff-field__counter">0/255</small>
                                                                        </div>
                                                                        <input type="hidden" name="status" value="accept">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="modal-footer d-flex justify-content-end border-0 pt-0">
                                                            <button type="submit"
                                                                    class="btn btn--primary">{{translate('Send Your Offer')}}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="ignoreRequestModal--{{$post['id']}}" tabindex="-1"
                                             aria-labelledby="ignoreRequestModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0 pb-0">
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex flex-column gap-2 align-items-center">
                                                            <img width="75" class="mb-2"
                                                                 src="{{asset('public/assets/provider-module')}}/img/media/ignore-request.png"
                                                                 alt="">
                                                            <h3>{{translate('Are you sure you want to ignore this request')}}
                                                                ?</h3>
                                                            <div
                                                                class="text-muted fs-12">{{translate('You will lose the customer booking request')}}</div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="modal-footer d-flex justify-content-center gap-3 border-0 pt-0 pb-4">
                                                        <button type="button" class="btn btn--secondary"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close">{{translate('Cancel')}}</button>
                                                        <a href="{{route('provider.booking.post.update_status', [$post->id, 'status' => 'ignore'])}}"
                                                           type="button"
                                                           class="btn btn--primary">{{translate('Ignore')}}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="withdrawRequestModal--{{$post['id']}}" tabindex="-1"
                                             aria-labelledby="withdrawRequestModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0 pb-0">
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex flex-column gap-2 align-items-center">
                                                            <img width="75" class="mb-2"
                                                                 src="{{asset('public/assets/provider-module')}}/img/media/withdraw.png"
                                                                 alt="">
                                                            <h3>{{translate('Are you sure you want to withdraw this offer?')}}
                                                                ?</h3>
                                                            <div
                                                                class="text-muted fs-12">{{translate('You offer will be removed for the post')}}</div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="modal-footer d-flex justify-content-center gap-3 border-0 pt-0 pb-4">
                                                        <button type="button" class="btn btn--secondary"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close">{{translate('Cancel')}}</button>
                                                        <a href="{{route('provider.booking.post.withdraw', [$post->id])}}"
                                                           type="button"
                                                           class="btn btn--primary">{{translate('Withdraw Offer')}}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($bid_offers_visibility_for_providers)
                                            @php($bids = $post->bids->where('provider_id', '!=', auth()->user()->provider->id))
                                            @if($bids->count() > 0)
                                                @foreach($bids as $bid)
                                                    <div class="modal fade"
                                                         id="providerInfoModal--{{$bid->id}}" tabindex="-1"
                                                         aria-labelledby="providerInfoModalLabel"
                                                         aria-hidden="true">
                                                        <div class="modal-dialog modal-lg provider-info-modal">
                                                            <div class="modal-content">
                                                                <div class="modal-header px-sm-4">
                                                                    <h4 class="modal-title text-primary"
                                                                        id="providerInfoModalLabel">{{translate('Provider Information')}}</h4>
                                                                    <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body pb-4 px-lg-4">
                                                                    <div
                                                                        class="media flex-column flex-sm-row flex-wrap gap-3">
                                                                        <img width="173" class="radius-10"
                                                                             src="{{onErrorImage(
                                                                                    $bid?->provider?->logo,
                                                                                    asset('storage/app/public/provider/logo').'/' . $bid?->provider?->logo,
                                                                                    asset('public/assets/placeholder.png') ,
                                                                                    'provider/logo/')}}"
                                                                             alt="{{translate('provider image')}}">
                                                                        <div class="media-body">
                                                                            <h5 class="fw-medium mb-1">{{$bid->provider?->company_name}}</h5>
                                                                            <div
                                                                                class="fs-12 d-flex flex-wrap align-items-center gap-2 mt-1">
                                                                                <span
                                                                                    class="common-list_rating d-flex gap-1">
                                                                                    <span
                                                                                        class="material-icons text-primary fs-12">star</span>
                                                                                    {{$bid->provider?->avg_rating}}
                                                                                </span>
                                                                                <span>{{ translate(':count Reviews', ['count' => $bid->provider?->rating_count]) }}</span>
                                                                            </div>

                                                                            <div
                                                                                class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1 mb-3">
                                                                                <span
                                                                                    class="text-danger">{{translate('Price Offered')}}</span>
                                                                                <h4 class="text-primary">{{with_currency_symbol($bid->offered_price)}}</h4>
                                                                            </div>
                                                                            @if($bid->provider_note != null)
                                                                                <h3 class="text-muted mb-2">{{translate('Description')}}
                                                                                    :</h3>
                                                                                <p>{{$bid->provider_note}}</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endif
    @endforeach

@endsection


@push('script')
    <script src="{{asset('public/assets/admin-module/js/moment.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/js/daterangepicker.min.js')}}"></script>
    <script>
        function initSingleDatePicker(selector) {
            const $input = $(selector);

            $input.daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            if ($input.val()) {
                const selectedDate = moment($input.val(), 'YYYY-MM-DD', true);

                if (selectedDate.isValid()) {
                    $input.data('daterangepicker').setStartDate(selectedDate);
                    $input.data('daterangepicker').setEndDate(selectedDate);
                    $input.val(selectedDate.format('YYYY-MM-DD'));
                }
            }

            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
        }

        $(document).ready(function () {
            initSingleDatePicker('#filter-start-date');
            initSingleDatePicker('#filter-end-date');
        });
    </script>
    <script>
        (function ($) {
            "use strict";

            $('#category_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('.category-select').select2({
                placeholder: "{{translate('Select Category')}}"
            });
            $('.date-select').select2({
                placeholder: "{{translate('Select date range')}}"
            });

            if (window.FormValidator && typeof window.FormValidator.register === 'function') {
                $('form.provider-bid-place-offer-form').each(function () {
                    var formId = '#' + $(this).attr('id');
                    window.FormValidator.register(formId, {
                        submitHandler: function (form) {
                            var $btn = $(form).find('button[type="submit"]');
                            if ($btn.prop('disabled')) return false;
                            if (!$btn.data('ffOriginalHtml')) {
                                $btn.data('ffOriginalHtml', $btn.html());
                            }
                            $btn.prop('disabled', true).html(
                                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' +
                                "{{ translate('Submitting...') }}"
                            );
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                });
            }

            $('#multi-ignore').on('click', function () {
                var request_ids = [];
                $('input:checkbox.multi-check').each(function () {
                    if (this.checked) {
                        request_ids.push($(this).val());
                    }
                });

                Swal.fire({
                    title: "{{translate('Are You Sure')}}?",
                    text: "{{translate('Do you really want to ignore the selected requests')}}?",
                    type: 'warning',
                    showCloseButton: true,
                    showCancelButton: true,
                    cancelButtonColor: 'var(--bs-secondary)',
                    confirmButtonColor: 'var(--bs-primary)',
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Yes',
                    reverseButtons: true

                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{route('provider.booking.post.multi-ignore')}}",
                            data: {
                                post_ids: request_ids,
                            },
                            type: 'post',
                            success: function (response) {
                                toastr.success(response.message)
                                setTimeout(location.reload.bind(location), 1000);
                            },
                            error: function () {

                            }
                        });
                    }
                })

            });

            $('#reset-btn').on('click', function(e) {
                e.preventDefault();
                window.location.href = '{{ url()->current() }}';
            });

            function getActionMenuHeight($menu) {
                const originalStyle = $menu.attr('style');

                $menu.css({
                    display: 'block',
                    visibility: 'hidden'
                });

                const height = $menu.outerHeight();

                if (originalStyle === undefined) {
                    $menu.removeAttr('style');
                } else {
                    $menu.attr('style', originalStyle);
                }

                return height;
            }

            function resetActionDropdowns() {
                $('.customize-list-table .table-responsive').css('--customize-action-menu-space', '0px');
                $('.customize-list-table .table-actions').removeClass('dropdown-open-up');
            }

            $('.customize-list-table .table-actions [data-bs-toggle="dropdown"]').on('show.bs.dropdown', function () {
                const $button = $(this);
                const $actions = $button.closest('.table-actions');
                const $tableWrap = $button.closest('.table-responsive');
                const $menu = $actions.find('.dropdown-menu').first();
                const menuGap = 4;

                resetActionDropdowns();

                const menuHeight = getActionMenuHeight($menu);
                const buttonRect = this.getBoundingClientRect();
                const tableRect = $tableWrap[0].getBoundingClientRect();
                const roomAbove = buttonRect.top - tableRect.top;
                const roomBelow = tableRect.bottom - buttonRect.bottom;

                if (roomAbove >= menuHeight + menuGap) {
                    $actions.addClass('dropdown-open-up');
                    return;
                }

                const requiredSpace = Math.max(0, menuHeight + menuGap - roomBelow);
                $tableWrap.css('--customize-action-menu-space', Math.ceil(requiredSpace) + 'px');
            });

            $('.customize-list-table .table-actions [data-bs-toggle="dropdown"]').on('hidden.bs.dropdown', function () {
                resetActionDropdowns();
            });

            function toggleDateFields() {
                const selectedValue = $('#select_date').val();
                if (selectedValue === 'custom_range') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            }

            toggleDateFields();

            $('#select_date').change(function() {
                toggleDateFields();
            });

            $('#filter-form').on('submit', function(e) {
                let dateRange = $('#select_date').val();

                if (dateRange === 'custom_range') {
                    const from = $('[name="start_date"]').val();
                    const to = $('[name="end_date"]').val();
                    let hasError = false;

                    if (to === '') {
                        toastr.error('{{ translate('please select to date') }}');
                        hasError = true;
                    }

                    if (from === '') {
                        toastr.error('{{ translate('please select from date') }}');
                        hasError = true;
                    }

                    if (!hasError) {
                        let fromDate = moment(from, 'YYYY-MM-DD', true);
                        let toDate = moment(to, 'YYYY-MM-DD', true);

                        if (!fromDate.isValid() || !toDate.isValid() || fromDate.isAfter(toDate)) {
                            toastr.error('{{ translate('From date must be earlier than To date') }}');
                            hasError = true;
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                        return;
                    }
                }
            });

        })(jQuery);
    </script>
@endpush
