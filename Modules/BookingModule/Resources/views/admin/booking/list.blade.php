@extends('adminmodule::layouts.master')

@section('title', translate('Booking_List'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
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

        .booking-status-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 991.98px) {
            .booking-status-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .booking-status-grid {
                grid-template-columns: 1fr;
            }
        }

        .booking-status-card {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1rem 1.125rem;
            border-radius: 0.875rem;
            border: 1px solid transparent;
            text-decoration: none !important;
            background: #fff;
            box-shadow: 0 4px 18px rgba(15, 36, 64, 0.08);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            color: inherit;
            min-height: 92px;
        }

        .booking-status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 36, 64, 0.12);
            color: inherit;
        }

        .booking-status-card.is-active {
            border-color: currentColor;
            box-shadow: 0 8px 28px rgba(15, 36, 64, 0.14);
        }

        .booking-status-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #fff;
        }

        .booking-status-card__icon .material-icons {
            font-size: 1.5rem;
        }

        .booking-status-card__meta {
            min-width: 0;
        }

        .booking-status-card__label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            opacity: 0.75;
            margin-bottom: 0.15rem;
        }

        .booking-status-card__count {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .booking-status-card--accepted {
            color: #1b3a6b;
        }

        .booking-status-card--accepted .booking-status-card__icon {
            background: linear-gradient(135deg, #1b3a6b 0%, #2d5f9a 100%);
        }

        .booking-status-card--accepted.is-active {
            background: linear-gradient(135deg, rgba(27, 58, 107, 0.08) 0%, rgba(45, 95, 154, 0.12) 100%);
        }

        .booking-status-card--ongoing {
            color: #0f7a4a;
        }

        .booking-status-card--ongoing .booking-status-card__icon {
            background: linear-gradient(135deg, #0b6b41 0%, #19a463 100%);
        }

        .booking-status-card--ongoing.is-active {
            background: linear-gradient(135deg, rgba(15, 122, 74, 0.08) 0%, rgba(25, 164, 99, 0.12) 100%);
        }

        .booking-status-card--completed {
            color: #0f766e;
        }

        .booking-status-card--completed .booking-status-card__icon {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        }

        .booking-status-card--completed.is-active {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.08) 0%, rgba(20, 184, 166, 0.12) 100%);
        }

        .booking-status-card--canceled {
            color: #b42318;
        }

        .booking-status-card--canceled .booking-status-card__icon {
            background: linear-gradient(135deg, #b42318 0%, #f04438 100%);
        }

        .booking-status-card--canceled.is-active {
            background: linear-gradient(135deg, rgba(180, 35, 24, 0.08) 0%, rgba(240, 68, 56, 0.12) 100%);
        }

        .booking-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
        }

        .booking-toolbar__search {
            flex: 1 1 280px;
            min-width: 240px;
        }

        .booking-toolbar__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            align-items: center;
        }

        .booking-quick-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            align-items: center;
        }

        .booking-quick-filters .form-select,
        .booking-quick-filters .form-control {
            min-width: 140px;
            height: 45px;
        }

        .booking-active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .booking-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(27, 58, 107, 0.08);
            color: #1b3a6b;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .booking-filter-chip a {
            color: inherit;
            line-height: 1;
        }
    </style>
@endpush

@section('content')
    @php
        $currentStatus = $queryParams['booking_status'] ?? 'accepted';
        $statusTabs = [
            'accepted' => [
                'label' => translate('Accepted'),
                'icon' => 'task_alt',
            ],
            'ongoing' => [
                'label' => translate('Ongoing'),
                'icon' => 'autorenew',
            ],
            'completed' => [
                'label' => translate('Completed'),
                'icon' => 'verified',
            ],
            'canceled' => [
                'label' => translate('Canceled'),
                'icon' => 'cancel',
            ],
        ];
        $statusTabQuery = request()->except(['booking_status', 'page']);
        $statusTabQuery['service_type'] = $queryParams['service_type'] ?? 'all';
        $hasActiveFilters = ($filterCounter ?? 0) > 0 || filled($queryParams['search'] ?? null);
    @endphp

    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title">{{ translate('Filter_your_Booking') }}</h3>
            <button type="button" class="btn-close p-1 filter-aside__close-btn"></button>
        </div>
        <form action="{{ route('admin.booking.list') }}" method="GET"
            enctype="multipart/form-data" id="filter-form">
            <input type="hidden" name="booking_status" value="{{ $queryParams['booking_status'] ?? 'accepted' }}">
            <input type="hidden" name="service_type" value="{{ $queryParams['service_type'] ?? 'all' }}">
            <input type="hidden" name="search" value="{{ $queryParams['search'] ?? '' }}" id="filter-search">
            <input type="hidden" name="provider_assigned" value="{{ $queryParams['provider_assigned'] ?? '' }}">
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__date_range">
                    <h4 class="fw-normal mb-4">{{ translate('Select_Date_Range') }}</h4>
                    <div class="mb-30">
                        <div class="form-floating default-calendar-none">
                            <input type="text" class="form-control" placeholder="{{ translate('start_date') }}"
                                id="start_date" name="start_date" value="{{ $queryParams['start_date'] }}"
                                data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            <label for="start_date">{{ translate('Start_Date') }}</label>
                        </div>
                    </div>
                    <div class="fw-normal mb-30">
                        <div class="form-floating default-calendar-none">
                            <input type="text" class="form-control" placeholder="{{ translate('end_date') }}"
                                id="end_date" name="end_date" value="{{ $queryParams['end_date'] }}"
                                data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            <label for="end_date">{{ translate('End_Date') }}</label>
                        </div>
                    </div>
                </div>

                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{ translate('Select_Categories') }}</h4>
                    <div class="mb-30">
                        <select class="category-select theme-input-style w-100" name="category_ids[]" multiple="multiple"
                            id="category_selector__select">
                            <option value="all">{{ translate('Select All') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ in_array($category->id, $queryParams['category_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{ translate('Select_Sub_Categories') }}</h4>
                    <div class="mb-30">
                        <select class="subcategory-select theme-input-style w-100" name="sub_category_ids[]"
                            multiple="multiple" id="sub_category_selector__select">
                            <option value="all">{{ translate('Select All') }}</option>
                            @foreach ($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}"
                                    {{ in_array($subCategory->id, $queryParams['sub_category_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $subCategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-aside__zone_select">
                    <h4 class="mb-2 fw-normal">{{ translate('Select_Zones') }}</h4>
                    <div class="mb-30">
                        <select class="zone-select theme-input-style w-100" name="zone_ids[]" multiple="multiple"
                            id="zone_selector__select">
                            <option value="all">{{ translate('Select All') }}</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}"
                                    {{ in_array($zone->id, $queryParams['zone_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns p-20">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize" id="reset-btn"
                        type="button">{{ translate('Clear_all_Filter') }}</button>
                    <button class="btn btn--primary text-capitalize" type="submit">{{ translate('Filter') }}</button>
                </div>
            </div>
        </form>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-wrap d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <div>
                            <h2 class="page-title mb-1">{{ translate('Bookings') }}</h2>
                            <p class="mb-0 opacity-75 fz-12">
                                {{ translate('Switch status tabs, search, and filter bookings in one place') }}
                            </p>
                        </div>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{ translate('Showing') }}:</span>
                            <span class="title-color">{{ $bookings->total() }}</span>
                        </div>
                    </div>

                    <div class="booking-status-grid mb-4">
                        @foreach($statusTabs as $statusKey => $statusMeta)
                            <a class="booking-status-card booking-status-card--{{ $statusKey }} {{ $currentStatus === $statusKey ? 'is-active' : '' }}"
                               href="{{ route('admin.booking.list', array_merge($statusTabQuery, ['booking_status' => $statusKey])) }}">
                                <span class="booking-status-card__icon">
                                    <span class="material-icons">{{ $statusMeta['icon'] }}</span>
                                </span>
                                <span class="booking-status-card__meta">
                                    <span class="booking-status-card__label">{{ $statusMeta['label'] }}</span>
                                    <span class="booking-status-card__count">{{ $statusCounts[$statusKey] ?? 0 }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Repeat Booking temporarily hidden
                    <div class="mb-30">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a class="nav-link {{ request('service_type') === 'all' || !request('service_type') ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['service_type' => 'all', 'page' => null]) }}">
                                    {{ translate('All Booking') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('service_type') === 'regular' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['service_type' => 'regular', 'page' => null]) }}">
                                    {{ translate('Regular Booking') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('service_type') === 'repeat' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['service_type' => 'repeat', 'page' => null]) }}">
                                    {{ translate('Repeat Booking') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    --}}

                    <div class="card">
                        <div class="card-body">
                            <div class="booking-toolbar mb-3">
                                <form
                                    action="{{ route('admin.booking.list') }}"
                                    class="search-form search-form_style-two booking-toolbar__search" method="GET">
                                    <input type="hidden" name="booking_status" value="{{ $queryParams['booking_status'] ?? 'accepted' }}">
                                    <input type="hidden" name="service_type" value="{{ $queryParams['service_type'] ?? 'all' }}">
                                    <input type="hidden" name="provider_assigned" value="{{ $queryParams['provider_assigned'] ?? '' }}">
                                    @foreach(['zone_ids', 'category_ids', 'sub_category_ids', 'start_date', 'end_date'] as $field)
                                        @if(isset($queryParams[$field]))
                                            @if(is_array($queryParams[$field]))
                                                @foreach($queryParams[$field] as $value)
                                                    <input type="hidden" name="{{ $field }}[]" value="{{ $value }}">
                                                @endforeach
                                            @else
                                                <input type="hidden" name="{{ $field }}" value="{{ $queryParams[$field] }}">
                                            @endif
                                        @endif
                                    @endforeach
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="{{ $queryParams['search'] ?? '' }}" name="search"
                                            placeholder="{{ translate('Search by booking ID, customer, phone, or provider') }}">
                                    </div>
                                    <button type="submit"
                                        class="btn btn--primary">{{ translate('search') }}</button>
                                </form>

                                <div class="booking-toolbar__actions">
                                    <div class="booking-quick-filters">
                                        <select class="form-select custom-select" name="provider_assigned" id="providerAssigned">
                                            <option value="" {{ empty($queryParams['provider_assigned']) || ($queryParams['provider_assigned'] ?? '') == 'all' ? 'selected' : '' }}>{{ translate('Provider') }}: {{ translate('All') }}</option>
                                            <option value="assigned" {{ ($queryParams['provider_assigned'] ?? '') == 'assigned' ? 'selected' : '' }}>{{ translate('Assigned') }}</option>
                                            <option value="unassigned" {{ ($queryParams['provider_assigned'] ?? '') == 'unassigned' ? 'selected' : '' }}>{{ translate('Unassigned') }}</option>
                                        </select>
                                    </div>

                                    @can('booking_export')
                                        <div class="dropdown">
                                            <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle h-45"
                                                data-bs-toggle="dropdown">
                                                <span class="material-icons">file_download</span>
                                                {{ translate('download') }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                        href="{{ route('admin.booking.download', $queryParams) }}">{{ translate('excel') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan
                                    <button type="button" class="btn text-capitalize filter-btn border px-3 h-45">
                                        <span class="material-icons">filter_list</span> {{ translate('Filter') }}
                                        <span class="count">{{ $filterCounter ?? 0 }}</span>
                                    </button>
                                </div>
                            </div>

                            @if($hasActiveFilters)
                                <div class="booking-active-filters mb-3">
                                    <span class="opacity-75 fz-12">{{ translate('Active filters') }}:</span>
                                    @if(filled($queryParams['search'] ?? null))
                                        <span class="booking-filter-chip">
                                            {{ translate('Search') }}: {{ $queryParams['search'] }}
                                        </span>
                                    @endif
                                    @if(in_array($queryParams['provider_assigned'] ?? '', ['assigned', 'unassigned'], true))
                                        <span class="booking-filter-chip">
                                            {{ translate('Provider') }}: {{ translate(ucfirst($queryParams['provider_assigned'])) }}
                                        </span>
                                    @endif
                                    @if(!empty($queryParams['start_date']) || !empty($queryParams['end_date']))
                                        <span class="booking-filter-chip">
                                            {{ translate('Date') }}: {{ $queryParams['start_date'] ?? '...' }} → {{ $queryParams['end_date'] ?? '...' }}
                                        </span>
                                    @endif
                                    @if(!empty($queryParams['category_ids']))
                                        <span class="booking-filter-chip">{{ translate('Categories') }}</span>
                                    @endif
                                    @if(!empty($queryParams['sub_category_ids']))
                                        <span class="booking-filter-chip">{{ translate('Sub Categories') }}</span>
                                    @endif
                                    @if(!empty($queryParams['zone_ids']))
                                        <span class="booking-filter-chip">{{ translate('Zones') }}</span>
                                    @endif
                                    <a href="{{ route('admin.booking.list', ['booking_status' => $currentStatus, 'service_type' => 'all']) }}"
                                       class="btn btn-sm btn--secondary">{{ translate('Clear all') }}</a>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table id="example" class="table align-middle tr-hover">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Booking_ID') }}</th>
                                            <th>{{ translate('Booking_Date') }}</th>
                                            <th>{{ translate('Where_Service_will_be_Provided') }}</th>
                                            <th>{{ translate('Schedule_Date') }}</th>
                                            <th>{{ translate('Customer_Info') }}</th>
                                            <th>{{ translate('Provider_Info') }}</th>
                                            <th>{{ translate('Total_Amount') }}</th>
                                            <th>{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($bookings as $key => $booking)
                                            <tr>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >{{ $key + $bookings?->firstItem() }}</td>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >
                                                    @if($booking->is_repeated)
                                                        <a href="{{ route('admin.booking.repeat_details', [$booking->id, 'web_page' => 'details']) }}">
                                                            {{ $booking->readable_id }}
                                                        </a>
                                                        <img width="34" height="34"
                                                             src="{{ asset('public/assets/admin-module/img/icons/repeat.svg') }}"
                                                             class="rounded-circle repeat-icon"
                                                             alt="{{ translate('repeat') }}">
                                                    @else
                                                    <a href="{{ route('admin.booking.details', [$booking->id, 'web_page' => 'details']) }}">
                                                        {{ $booking->readable_id }}</a>
                                                    @endif
                                                </td>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >
                                                    <div>{{ \Carbon\Carbon::parse($booking->created_at)->format('d-M-Y') }}</div>
                                                    <div>{{ format_time_by_business_settings($booking->created_at) }}</div>
                                                </td>
                                                <td>
                                                    @if($booking->service_location == 'provider')
                                                        {{ translate('Provider Location') }}
                                                    @else
                                                        {{ translate('Customer Location') }}
                                                    @endif
                                                </td>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >
                                                    @if($booking->is_repeated)
                                                        @if(empty($booking->nextService))
                                                            <div>{{ \Carbon\Carbon::parse($booking?->lastRepeat?->service_schedule)->format('d-M-Y') }}</div>
                                                            <div>{{ format_time_by_business_settings($booking?->lastRepeat?->service_schedule) }}</div>
                                                        @else
                                                            <span>{{translate('Next upcoming')}}</span>
                                                            <div>{{ \Carbon\Carbon::parse($booking?->nextService?->service_schedule)->format('d-M-Y') }}</div>
                                                            <div>{{ format_time_by_business_settings($booking?->nextService?->service_schedule) }}</div>
                                                        @endif
                                                    @else
                                                        <div>{{ \Carbon\Carbon::parse($booking->service_schedule)->format('d-M-Y') }}</div>
                                                        <div>{{ format_time_by_business_settings($booking->service_schedule) }}</div>
                                                    @endif
                                                </td>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >
                                                    @php($customer_name = $booking?->service_address?->contact_person_name ?? $booking?->customer?->first_name . ' ' . $booking?->customer?->last_name)
                                                    @php($customer_phone = $booking?->service_address?->contact_person_number ?? $booking?->customer?->phone)
                                                    <div>
                                                        @if ($booking->customer)
                                                            <a
                                                                href="{{ route('admin.customer.detail', [$booking?->customer?->id, 'web_page' => 'overview']) }}">
                                                                {{ Str::limit($customer_name, 30) }}
                                                            </a>
                                                        @else
                                                            <span>
                                                                {{ Str::limit($customer_name, 30) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    {{ $customer_phone }}
                                                </td>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >
                                                    @if(isset($booking->provider))
                                                        <div>
                                                            <a href="{{route('admin.provider.details',[$booking->provider_id, 'web_page'=>'overview'])}}">{{ $booking->provider->company_name }}</a>
                                                        </div>
                                                        <span class="text-light-gray">{{ $booking->provider->company_phone }}</span>
                                                    @else
                                                        <span class="badge badge badge-danger radius-50">
                                                            {{ translate('unassigned') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td
                                                    @if($booking->is_repeated)
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                                    @endif
                                                >{{ with_currency_symbol($booking->total_booking_amount) }}</td>
                                                <td>
                                                    <div class="table-actions d-flex gap-2">
                                                        @if($booking->is_repeated)
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                        class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                        style="--size: 30px" data-bs-toggle="dropdown">
                                                                    <span class="material-icons">visibility</span>
                                                                </button>
                                                                <ul
                                                                    class="dropdown-menu border-none dropdown-menu-lg dropdown-menu-right">
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            href="{{ route('admin.booking.repeat_details', [$booking->id, 'web_page' => 'details']) }}">
                                                                                <span
                                                                                    class="material-icons">visibility</span>
                                                                            {{ translate('Full_Booking_Details') }}
                                                                        </a>
                                                                    </li>
                                                                    @if($booking->nextServiceId && $booking['booking_status'] != 'pending')
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            href="{{ route('admin.booking.repeat_single_details', [$booking->nextServiceId, 'web_page' => 'details'])}}">
                                                                                <span
                                                                                    class="material-icons">visibility</span>
                                                                            {{ translate('Ongoing_Booking_Details') }}
                                                                        </a>
                                                                    </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                        class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                        style="--size: 30px" data-bs-toggle="dropdown">
                                                                    <span class="material-icons">download</span>
                                                                </button>
                                                                <ul
                                                                    class="dropdown-menu border-none dropdown-menu-lg dropdown-menu-right">
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            target="_blank"
                                                                            href="{{ route('admin.booking.full_repeat_invoice', [$booking->id]) }}">
                                                                                <span
                                                                                    class="material-icons">download</span>
                                                                            {{ translate('Full invoice') }}
                                                                        </a>
                                                                    </li>
                                                                    @if($booking->nextServiceId && $booking['booking_status'] != 'pending')
                                                                        <li class="mx-2">
                                                                            <a
                                                                                class="dropdown-item d-flex align-items-center gap-1"
                                                                                target="_blank"
                                                                                href="{{ route('admin.booking.single_invoice', [$booking->nextServiceId]) }}">
                                                                                    <span
                                                                                        class="material-icons">download</span>
                                                                                {{ translate('Ongoing Booking invoice') }}
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <a href="{{ route('admin.booking.details', [$booking->id, 'web_page' => 'details']) }}"
                                                                type="button"
                                                                class="action-btn tooltip-hide btn--light-primary fw-medium text-capitalize fz-14"
                                                                style="--size: 30px">
                                                                <span class="material-icons">visibility</span>
                                                            </a>
                                                            <a href="{{ route('admin.booking.invoice', [$booking->id]) }}"
                                                                type="button" target="_blank"
                                                                class="action-btn tooltip-hide btn--light-primary fw-medium text-capitalize fz-14"
                                                                style="--size: 30px">
                                                                <span class="material-icons">download</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            @include('adminmodule::layouts.partials.components._empty-state', [
                                                'colspan' => 10,
                                                'variant' => $hasActiveFilters ? 'search' : 'list'])
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $bookings->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
            initSingleDatePicker('#start_date');
            initSingleDatePicker('#end_date');
        });
    </script>
    <script>
        (function($) {
            "use strict";

            $('#category_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('#sub_category_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('#zone_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('.category-select').select2({
                placeholder: "{{ translate('Select Category') }}"
            });
            $('.subcategory-select').select2({
                placeholder: "{{ translate('Select Subcategory') }}"
            });
            $('.zone-select').select2({
                placeholder: "{{ translate('Select Zone') }}"
            });

            function updateQueryParam(key, value) {
                var url = new URL(window.location.href);
                if (!value || value === 'all') {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }

            $('#providerAssigned').change(function() {
                updateQueryParam('provider_assigned', $(this).val());
            });
        })(jQuery);
    </script>

    <script>
        $(document).ready(function() {
            $('#reset-btn').on('click', function(e) {
                e.preventDefault();
                window.location.href = '{{ route('admin.booking.list', ['booking_status' => $currentStatus, 'service_type' => 'all']) }}';
            });
            $('#filter-form').on('submit', function(e) {
                const from = $('#start_date').val();
                const to = $('#end_date').val();
                let hasError = false;

                if ((from && !to) || (!from && to)) {
                    toastr.error('{{ translate('Both start date and end date are required') }}');
                    hasError = true;
                }

                if (!hasError && from && to) {
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
            });

        });
    </script>
@endpush
