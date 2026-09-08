@extends('providermanagement::layouts.master')

@section('title',translate('Booking_Request'))

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
    </style>
@endpush

@section('content')
    @if(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values && Request()->user()->provider->is_suspended == 1)
        <div class="alert alert-danger">
            <div class="media gap-3 align-items-center">
                <div class="alert-close-btn">
                    <span class="material-symbols-outlined">close</span>
                </div>
                <div class="media-body">
                    <h5 class="text-capitalize">{{translate('Attention Please')}}!</h5>
                    <p class="text-dark fs-12">
                        {{ translate('Your limit to hold cash is exceeded.') }} {{ translate('Your account has been suspended until you pay the due.') }} {{ translate('You will not receive any new booking requests from now.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
    @if(!request()->user()->provider->service_availability && $queryParams['booking_status'] == 'pending')
        <div class="alert alert-primary availability-alert">
            <div class="media gap-3 align-items-center">
                <div class="alert-close-btn">
                    <span class="material-symbols-outlined close-btn">close</span>
                </div>
                <div class="media-body">
                    <h5 class="text-capitalize">{{translate('Attention Please')}}!</h5>
                    <p class="text-dark fs-12">
                        {{translate('The service availability option has been turned off. You will not receive any new booking requests until you turn on the service availability option')}}
                        <span><a class="text-primary"
                                 href="{{route('provider.business-settings.get-business-information')}}">{{translate('Go to settings')}}</a></span>
                    </p>
                </div>
            </div>
        </div>
    @endif
    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title">{{translate('Filter_your_Bookings')}}</h3>
            <button type="button" class="btn-close p-1 filter-aside__close-btn"></button>
        </div>
        <form action="{{ route('provider.booking.list') }}"
              id="filterForm"
              method="GET"
              enctype="multipart/form-data">
            <input type="hidden" name="booking_status" value="{{ $queryParams['booking_status'] ?? 'pending' }}">
            <input type="hidden" name="service_type" value="{{ $queryParams['service_type'] ?? 'all' }}">
            <input type="hidden" name="search" value="{{ $queryParams['search'] ?? '' }}">
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__date_range">
                    <h4 class="fw-normal mb-4">{{translate('Select_Date_Range')}}</h4>
                    <div class="mb-30">
                        {{--@dd($queryParams)--}}
                        <div class="form-floating">
                            <input type="text" class="form-control start_date" placeholder="{{translate('Start Date')}}"
                                   id="start_date" name="start_date"
                                   value="{{$queryParams['start_date']}}"
                                   data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            <label for="start_date">{{translate('Start_Date')}}</label>
                        </div>
                    </div>
                    <div class="fw-normal mb-30">
                        <div class="form-floating">
                            <input type="text" class="form-control end_date" placeholder="{{translate('End Date')}}"
                                   id="end_date" name="end_date"
                                   value="{{$queryParams['end_date']}}"
                                   data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            <label for="end_date">{{translate('End_Date')}}</label>
                        </div>
                    </div>
                </div>

                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{translate('Select_Categories')}}</h4>
                    <div class="mb-30">
                        <select class="category-select theme-input-style w-100" name="category_ids[]"
                                multiple="multiple">
                            @foreach($categories as $category)
                                <option
                                    value="{{$category->id}}" {{in_array($category->id,$queryParams['category_ids']??[])?'selected':''}}>
                                    {{$category->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{translate('Select_Sub_Categories')}}</h4>
                    <div class="mb-30">
                        <select class="sub-category-select theme-input-style w-100" name="sub_category_ids[]"
                                multiple="multiple">
                            @foreach($subCategories as $sub_category)
                                <option
                                    value="{{$sub_category->id}}" {{in_array($sub_category->id,$queryParams['sub_category_ids']??[])?'selected':''}}>
                                    {{$sub_category->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns p-20">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize fz-14" id="clearFilterButton"
                            type="button">{{translate('Clear_all_Filter')}}</button>
                    <button class="btn btn--primary text-capitalize fz-14"
                            type="submit">{{translate('Filter')}}</button>
                </div>
            </div>
        </form>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Booking_Request')}}</h2>
                        <div class="d-flex justify-content-end">
                            <span class="opacity-75">{{ translate('Total_Request') }}:</span>
                            <span class="title-color">{{ $bookings->total() }}</span>
                        </div>
                    </div>
                    {{-- <hr class="mt-1"> --}}
                    <div class="mt-20 mb-20">
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
                            <li class="nav-item text">
                                <a class="nav-link {{ request('service_type') === 'repeat' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['service_type' => 'repeat', 'page' => null]) }}">
                                    {{ translate('Repeat Booking') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">

                                <form action="{{ route('provider.booking.list') }}"
                                      class="search-form search-form_style-two" method="GET">
                                    <input type="hidden" name="booking_status" value="{{ $queryParams['booking_status'] ?? 'pending' }}">
                                    <input type="hidden" name="service_type" value="{{ $queryParams['service_type'] ?? 'all' }}">
                                    @foreach(['category_ids', 'sub_category_ids', 'start_date', 'end_date'] as $field)
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
                                               value="{{$queryParams['search']??''}}" name="search"
                                               placeholder="{{translate('Search Here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span>
                                            {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li><a class="dropdown-item"
                                                   href="{{route('provider.booking.download', $queryParams)}}">{{translate('excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <button type="button" class="btn text-capitalize filter-btn border px-3">
                                        <span class="material-icons">filter_list</span> {{translate('Filter')}}
                                        <span class="count">{{$filterCounter??0}}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{ translate('Where_Service_will_be_Provided') }}</th>
                                        <th>{{translate('Customer_Info')}}</th>
                                        <th>{{translate('Total_Amount')}}</th>
                                        <th>{{translate('Payment_Status')}}</th>
                                        <th>{{translate('Schedule_Date')}}</th>
                                        <th>{{translate('Booking_Date')}}</th>
                                        <th>{{translate('status')}}</th>
                                        <th>{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($bookings as $key=>$booking)
                                        <tr
                                            @if($booking->is_repeated)
                                                data-bs-custom-class="review-tooltip custom"
                                            data-bs-toggle="tooltip"
                                            data-bs-html="true"
                                            data-bs-placement="bottom"
                                            data-bs-title="{{ translate('This is a repeat booking.') }} <br> {{ translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)]) }} <br> {{ translate('Check the details') }}"
                                            @endif
                                        >
                                            <td>{{$key+$bookings?->firstItem()}}</td>
                                            <td>
                                                @if($booking->is_repeated)
                                                    <a href="{{ route('provider.booking.repeat_details', [$booking->id, 'web_page' => 'details']) }}">
                                                        {{ $booking->readable_id }}
                                                    </a>
                                                    <img width="17" height="17"
                                                         src="{{ asset('public/assets/admin-module/img/icons/repeat.svg') }}"
                                                         class="rounded-circle repeat-icon"
                                                         alt="{{ translate('repeat') }}">
                                                @else
                                                    <a href="{{ route('provider.booking.details', [$booking->id, 'web_page' => 'details']) }}">
                                                        {{ $booking->readable_id }}</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if($booking->service_location == 'provider')
                                                    {{ translate('Your Location') }}
                                                @else
                                                    {{ translate('Customer Location') }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($booking->customer))
                                                    {{Str::limit($booking?->customer?->first_name, 30)}} <br/>
                                                    {{$booking?->customer?->phone}}
                                                @else
                                                    {{Str::limit($booking?->service_address?->contact_person_name, 30)}}
                                                    <br/>
                                                    {{$booking?->service_address?->contact_person_number}}
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($booking->total_booking_amount)}}</td>
                                            <td>
                                                <span
                                                    class="badge badge badge-success">
                                                    {{ translate('paid') }}
                                                </span>
                                            </td>
                                            <td>
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
                                            <td>{{ format_time_by_business_settings($booking->created_at, 'd-M-Y') }}</td>
                                            <td>
                                                <span class="badge badge badge-success">
                                                    {{ucfirst($booking->booking_status)}}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="table-actions gap-2">
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
                                                                        href="{{ route('provider.booking.repeat_details', [$booking->id, 'web_page' => 'details']) }}">
                                                                            <span
                                                                                class="material-icons">visibility</span>
                                                                        {{ translate('Full_Booking_Details') }}
                                                                    </a>
                                                                </li>
                                                                @if($booking->nextServiceId && $booking['booking_status'] != 'pending')
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            href="{{ route('provider.booking.repeat_single_details', [$booking->nextServiceId, 'web_page' => 'details'])}}">
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
                                                                        href="{{ route('provider.booking.full_repeat_invoice', [$booking->id]) }}">
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
                                                                            href="{{ route('provider.booking.single_invoice', [$booking->nextServiceId]) }}">
                                                                                <span
                                                                                    class="material-icons">download</span>
                                                                            {{ translate('Ongoing Booking invoice') }}
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <a href="{{ route('provider.booking.details', [$booking->id, 'web_page' => 'details']) }}"
                                                           type="button"
                                                           class="action-btn tooltip-hide btn--light-primary fw-medium text-capitalize fz-14"
                                                           style="--size: 30px">
                                                            <span class="material-icons">visibility</span>
                                                        </a>
                                                        <a href="{{ route('provider.booking.invoice', [$booking->id]) }}"
                                                           type="button" target="_blank"
                                                           class="action-btn tooltip-hide btn--light-primary fw-medium text-capitalize fz-14"
                                                           style="--size: 30px">
                                                            <span class="material-icons">download</span>
                                                        </a>
                                                    @endif
                                                    @if($booking->booking_status == 'pending' && !supervisorMode())
                                                        <button
                                                            type="button"
                                                            class="action-btn btn--light-success fw-medium text-capitalize fz-14 {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                                            style="--size: 30px"
                                                            title="{{ translate('Accept') }}"
                                                            data-id="accept-{{$booking['id']}}"
                                                            data-message=""
                                                            data-title="{{translate('Are you sure to accept the booking request?')}}">
                                                            <span class="material-icons">check</span>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="action-btn btn--light-danger fw-medium text-capitalize fz-14 {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                                            style="--size: 30px"
                                                            title="{{ translate('Ignore') }}"
                                                            data-id="cancel-{{$booking['id']}}"
                                                            data-message="{{translate('Once you ignore the request, it will be no longer on your booking request list.')}}?"
                                                            data-title="{{translate('Are you sure to ignore the booking request?')}}">
                                                            <span class="material-icons">close</span>
                                                        </button>
                                                        <form
                                                            action="{{route('provider.booking.ignore',[$booking['id']])}}"
                                                            method="post" id="cancel-{{$booking['id']}}"
                                                            class="hidden">
                                                            @csrf
                                                            @method('GET')
                                                        </form>

                                                        <form
                                                            action="{{route('provider.booking.accept',[$booking['id']])}}"
                                                            method="post" id="accept-{{$booking['id']}}"
                                                            class="hidden">
                                                            @csrf
                                                            @method('GET')
                                                            <input type="hidden" name="booking_status" value="accepted">
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 10,
                                            'variant' => (
                                                filled($queryParams['search'] ?? null)
                                                || (($filterCounter ?? 0) > 0)
                                                || (($queryParams['service_type'] ?? 'all') !== 'all')
                                            ) ? 'search' : 'list'])
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
        "use strict";

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

            $('#filterForm').on('submit', function (e) {
                let from = $('#start_date').val();
                let to = $('#end_date').val();
                let hasError = false;

                if (from === '' && to === '') {
                    return;
                }

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
                }
            });
        });

        $(document).ready(function () {
            $('.close-btn').on('click', function () {
                $('.availability-alert').hide();
            });
        });

        $(document).ready(function () {
            $('.category-select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
            $('.sub-category-select').select2({
                placeholder: "{{translate('Select_sub_category')}}",
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            // $('#clearFilterButton').on('click', function () {
            //     let form = $('#filterForm');
            //     form[0].reset();
            //     $('.start_date').removeAttr('value');
            //     $('.end_date').removeAttr('value');
            //     $('.category-select').val(null).trigger('change');
            //     $('.sub-category-select').val(null).trigger('change');
            // });

            $('#clearFilterButton').on('click', function() {
                let bookingStatus = '{{ request()->booking_status }}';
                let serviceType = '{{ request()->service_type }}';
                let searchValue = @json($queryParams['search'] ?? '');

                let params = new URLSearchParams({
                    booking_status: bookingStatus,
                    service_type: serviceType,
                });
                if (searchValue) {
                    params.set('search', searchValue);
                }

                window.location.href = `{{ route('provider.booking.list') }}?${params.toString()}`;
            });
        });
    </script>

@endpush
