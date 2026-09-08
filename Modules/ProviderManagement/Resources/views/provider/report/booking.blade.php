@extends('providermanagement::layouts.master')

@section('title',translate('Booking_Report'))
@php
    $position = business_config('currency_symbol_position', 'business_information')['live_values'] ?? 'right';
@endphp

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
        .booking-summary-column,
        .booking-chart-column {
            display: flex;
        }

        .booking-summary-stack,
        .booking-summary-card,
        .booking-chart-card {
            width: 100%;
        }

        .booking-chart-card,
        .booking-chart-card .card-body {
            height: 100%;
        }

        .booking-chart-card .card-body {
            display: flex;
            flex-direction: column;
        }

        #apex_column-chart {
            flex: 1 1 auto;
            min-height: 280px;
        }

        .booking-total-card__content,
        .booking-total-card__breakdown,
        .booking-total-card__item {
            min-width: 0;
        }

        .booking-total-card__value {
            font-size: clamp(1.85rem, 2.8vw, 2.6rem);
            line-height: 1.1;
            overflow-wrap: anywhere;
        }

        .booking-total-card__breakdown {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem 0.75rem;
        }

        .booking-total-card__item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
        }

        .booking-total-card__amount {
            font-size: clamp(1rem, 1.4vw, 1.35rem);
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .booking-total-card__label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        @media (min-width: 1200px) {
            .booking-summary-stack {
                display: flex;
                flex-direction: column;
                flex-wrap: nowrap;
                gap: 0.5rem;
                height: 100%;
            }

            .booking-summary-card {
                flex: 1 1 0;
            }
        }

        @media (max-width: 1199.98px) {
            .booking-summary-stack {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .booking-summary-card {
                flex: 1 1 320px;
            }
        }

        @media (max-width: 575.98px) {
            .booking-total-card__breakdown {
                grid-template-columns: 1fr;
            }

            .booking-total-card__item {
                align-items: flex-start;
                text-align: left;
            }

            .booking-total-card__label {
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Booking_Reports')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 fz-16 fw-bold text-dark">{{translate('Search Data')}}</div>
                            <form action="{{route('provider.report.booking')}}" method="POST" class="filter-form">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'zone_ids[]',
                                            'id'          => 'zone_ids',
                                            'label'       => translate('Zone'),
                                            'multiple'    => true,
                                            'selectClass' => 'js-select zone__select',
                                            'options'     => collect($zones)->pluck('name', 'id')->toArray(),
                                            'value'       => $queryParams['zone_ids'] ?? [],
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'category_ids[]',
                                            'id'          => 'category_ids',
                                            'label'       => translate('Category'),
                                            'multiple'    => true,
                                            'selectClass' => 'js-select category__select',
                                            'options'     => collect($categories)->pluck('name', 'id')->toArray(),
                                            'value'       => $queryParams['category_ids'] ?? [],
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'sub_category_ids[]',
                                            'id'          => 'sub_category_ids',
                                            'label'       => translate('Sub Category'),
                                            'multiple'    => true,
                                            'selectClass' => 'js-select sub-category__select',
                                            'options'     => collect($subCategories)->pluck('name', 'id')->toArray(),
                                            'value'       => $queryParams['sub_category_ids'] ?? [],
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'date_range',
                                            'id'          => 'date-range',
                                            'label'       => translate('Date Range'),
                                            'selectClass' => 'js-select',
                                            'options'     => [
                                                'all_time'              => translate('All Time'),
                                                'this_week'             => translate('This Week'),
                                                'last_week'             => translate('Last Week'),
                                                'this_month'            => translate('This Month'),
                                                'last_month'            => translate('Last Month'),
                                                'last_15_days'          => translate('Last 15 Days'),
                                                'this_year'             => translate('This Year'),
                                                'last_year'             => translate('Last Year'),
                                                'last_6_month'          => translate('Last 6 Month'),
                                                'this_year_1st_quarter' => translate('This Year 1st Quarter'),
                                                'this_year_2nd_quarter' => translate('This Year 2nd Quarter'),
                                                'this_year_3rd_quarter' => translate('This Year 3rd Quarter'),
                                                'this_year_4th_quarter' => translate('This Year 4th Quarter'),
                                                'custom_date'           => translate('Custom Date')],
                                            'optionNull'  => translate('Date Range'),
                                            'value'       => $queryParams['date_range'] ?? '',
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end" id="from-filter__div">
                                        @include('partials._form-field', [
                                            'type'       => 'text',
                                            'name'       => 'from',
                                            'id'         => 'from',
                                            'label'      => translate('From'),
                                            'icon'       => 'calendar_month',
                                            'value'      => $queryParams['from'] ?? '',
                                            'extraAttrs' => 'data-ff-datepicker readonly autocomplete="off" inputmode="none"',
                                            'wrapClass'  => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end" id="to-filter__div">
                                        @include('partials._form-field', [
                                            'type'       => 'text',
                                            'name'       => 'to',
                                            'id'         => 'to',
                                            'label'      => translate('To'),
                                            'icon'       => 'calendar_month',
                                            'value'      => $queryParams['to'] ?? '',
                                            'extraAttrs' => 'data-ff-datepicker readonly autocomplete="off" inputmode="none"',
                                            'wrapClass'  => 'mb-0'])
                                    </div>
                                </div>
                                <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                    <a href="{{ url()->current() }}" class="btn btn--secondary">{{translate('Reset')}}</a>
                                    <button type="submit" class="btn btn--primary">{{translate('Filter')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row g-2 pt-2">
                        <div class="col-xl-3 booking-summary-column">
                            <div class="d-flex flex-wrap gap-2 booking-summary-stack">
                                <div class="card p-30 flex-grow-1 booking-summary-card">
                                    <div class="d-flex gap-2 justify-content-center align-items-center flex-wrap">
                                        <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/total_booking.png" alt="">
                                        <div class="">
                                            <h2 class="fz-26">{{$bookingsCount['total_bookings']}}</h2>
                                            <span class="fz-12">{{translate('Total_Bookings')}}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-30">
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="fw-semibold text-danger">{{$bookingsCount['canceled']}}</span>
                                            <span class="opacity-50">{{translate('Canceled')}}</span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="fw-semibold text-success">{{$bookingsCount['accepted']}}</span>
                                            <span class="opacity-50">{{translate('Accepted')}}</span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="c1 fw-semibold">{{$bookingsCount['ongoing']}}</span>
                                            <span class="opacity-50">{{translate('On_Going')}}</span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="fw-semibold text-success">{{$bookingsCount['completed']}}</span>
                                            <span class="opacity-50">{{translate('Completed')}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-30 flex-grow-1 booking-summary-card booking-total-card">
                                    <div class="d-flex gap-2 align-items-center justify-content-center flex-wrap">
                                        <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/booking_amount.png" alt="">
                                        <div class="text-center flex-grow-1 booking-total-card__content">
                                            <h2 class="booking-total-card__value" title="{{with_currency_symbol($bookingAmount['total_booking_amount'])}}">{{with_compact_currency_symbol($bookingAmount['total_booking_amount'])}}</h2>
                                            <span class="fz-12">{{translate('Total_Booking_Amount')}}</span>
                                        </div>
                                    </div>
                                    <div class="booking-total-card__breakdown mt-30">
                                        <div class="booking-total-card__item fz-12">
                                            <span class="text-danger fw-semibold booking-total-card__amount" title="{{with_currency_symbol($bookingAmount['total_unpaid_booking_amount'])}}">{{with_compact_currency_symbol($bookingAmount['total_unpaid_booking_amount'])}}</span>
                                            <span class="opacity-50 booking-total-card__label">{{translate('Due_Amount')}}
                                                <i class="material-symbols-outlined" data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{translate('Digitally paid but yet to disburse the amount')}}"
                                                >info</i>
                                            </span>
                                        </div>
                                        <div class="booking-total-card__item fz-12">
                                            <span class="text-success fw-semibold booking-total-card__amount" title="{{with_currency_symbol($bookingAmount['total_paid_booking_amount'])}}">{{with_compact_currency_symbol($bookingAmount['total_paid_booking_amount'])}}</span>
                                            <span class="opacity-50 booking-total-card__label">{{translate('Already_Settled')}}
                                                <i class="material-symbols-outlined" data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{translate('Digitally paid & already disbursed the amount')}}"
                                                >info</i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 booking-chart-column">
                            <div class="card booking-chart-card">
                                <div class="card-body ps-0">
                                    <h4 class="ps-20">{{translate('Booking_Statistics')}}</h4>
                                    <div id="apex_column-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two" method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{array_key_exists('search', $queryParams)?$queryParams['search']:''}}" name="search"
                                               placeholder="{{translate('search_by_Booking_ID')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div>
                                        <select class="js-select booking-status__select" name="booking_status" id="booking-status">
                                            <option value="" selected disabled>{{translate('Booking_status')}}</option>
                                            <option value="all">{{translate('All')}}</option>
                                            @foreach(BOOKING_STATUSES as $booking_status)
                                                @if($booking_status['key'] != 'pending')
                                                    <option value="{{$booking_status['key']}}" {{ $booking_status['key'] == $queryParams['booking_status'] ? 'selected' : '' }}>{{$booking_status['value']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="dropdown">
                                        <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li>
                                                <a class="dropdown-item" href="{{route('provider.report.booking.download').'?'.http_build_query($queryParams)}}">
                                                    {{translate('Excel')}}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Customer_Info')}}</th>
                                        <th>{{translate('Booking_Amount')}}</th>
                                        <th>{{translate('Service_Discount')}}</th>
                                        <th>{{translate('Coupon_Discount')}}</th>
                                        <th>{{translate('VAT_/_Tax')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($filteredBookings as $key=>$booking)
                                        <tr>
                                            <td>{{ $filteredBookings->firstitem()+$key }}</td>
                                            <td>
                                                <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}">
                                                    {{$booking['readable_id']}}
                                                </a>
                                            </td>
                                            <td>
                                                @php($customer_name = $booking?->service_address?->contact_person_name ?? $booking?->customer?->first_name . ' ' . $booking?->customer?->last_name)
                                                @php($customer_phone = $booking?->service_address?->contact_person_number ?? $booking?->customer?->phone)
                                                @if(isset($booking->customer))
                                                    <div>{{$customer_name}}</div>
                                                    @if($customer_phone)
                                                        <a class="fz-12" href="tel:{{$customer_phone}}">{{$customer_phone}}</a>
                                                    @endif
                                                @elseif($booking->is_guest == 1)
                                                    <span class="fw-medium badge badge badge-danger radius-50">{{translate('Guest Customer')}}
                                                    </span>
                                                    <p class="fz-12">{{$customer_name}} {{$customer_phone}}</p>
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($booking['total_booking_amount'])}}</td>
                                            <td>
                                                @if($booking['total_campaign_discount_amount'] > $booking['total_discount_amount'])
                                                    <p class="mb-1">{{with_currency_symbol($booking['total_campaign_discount_amount'])}}</p>
                                                    <span class="fw-medium badge badge badge-info radius-50">{{translate('Campaign')}}</span>
                                                @else
                                                    {{with_currency_symbol($booking['total_discount_amount'])}}
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($booking['total_coupon_discount_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking['total_tax_amount'])}}</td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}"
                                                        class="btn btn--light-primary action-btn"><span class="material-icons m-0">visibility</span>
                                                     </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 8,
                                            'variant' => (
                                                filled($queryParams['search'] ?? null)
                                                || !empty($queryParams['zone_ids'] ?? [])
                                                || !empty($queryParams['category_ids'] ?? [])
                                                || !empty($queryParams['sub_category_ids'] ?? [])
                                                || (($queryParams['booking_status'] ?? 'all') !== 'all')
                                                || (($queryParams['date_range'] ?? 'all_time') !== 'all_time')
                                                || filled($queryParams['from'] ?? null)
                                                || filled($queryParams['to'] ?? null)
                                            ) ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $filteredBookings->links() !!}
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

<script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>

    <script>
        "use strict";
        const currencySymbol = "{{ currency_symbol() }}";
        const currencyPosition = "{{ $position }}";

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
            $('.zone__select').select2({
                placeholder: "{{translate('Select_zone')}}",
            });
            $('.provider__select').select2({
                placeholder: "{{translate('Select_provider')}}",
            });
            $('.category__select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
            $('.sub-category__select').select2({
                placeholder: "{{translate('Select_sub_category')}}",
            });
            $('.booking-status__select').select2({
                placeholder: "{{translate('Booking_status')}}",
            });
        });

        $(document).ready(function () {
            initSingleDatePicker('#from');
            initSingleDatePicker('#to');

            $('#date-range').on('change', function() {
                if(this.value === 'custom_date') {
                    $('#from-filter__div').removeClass('d-none');
                    $('#to-filter__div').removeClass('d-none');
                }

                if(this.value !== 'custom_date') {
                    $('#from-filter__div').addClass('d-none');
                    $('#to-filter__div').addClass('d-none');
                    $('#from').val('');
                    $('#to').val('');
                }
            });

            $('.filter-form').on('submit', function (e) {
                let dateRange = $('#date-range').val();

                if (dateRange === 'custom_date') {
                    let from = $('#from').val();
                    let to = $('#to').val();
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
        });

        $(document).ready(function () {
            $('#booking-status').on('change', function() {
                let url = new URL(window.location.href);
                url.searchParams.set('booking_status', this.value);
                url.searchParams.delete('page');
                location.href = url.toString();
            });
        });

        function getBookingChartHeight() {
            if (window.innerWidth < 1200) {
                return window.innerWidth < 576 ? 280 : 320;
            }

            const leftColumn = document.querySelector('.booking-summary-column');
            const chartBody = document.querySelector('.booking-chart-card .card-body');
            const chartTitle = chartBody ? chartBody.querySelector('h4') : null;

            if (!leftColumn || !chartBody || !chartTitle) {
                return 320;
            }

            const bodyStyles = window.getComputedStyle(chartBody);
            const titleStyles = window.getComputedStyle(chartTitle);
            const paddingY = parseFloat(bodyStyles.paddingTop) + parseFloat(bodyStyles.paddingBottom);
            const titleSpace = chartTitle.offsetHeight
                + parseFloat(titleStyles.marginTop)
                + parseFloat(titleStyles.marginBottom);

            return Math.max(leftColumn.offsetHeight - paddingY - titleSpace, 280);
        }

        var options = {
            series: [{
                name: '{{translate('Total_Booking')}}',
                data: {{json_encode($chartData['booking_amount'])}}
            }, {
                name: '{{translate('Commission')}}',
                data: {{json_encode($chartData['admin_commission'])}}
            }, {
                name: '{{translate('VAT_/_Tax')}}',
                data: {{json_encode($chartData['tax_amount'])}}
            }],
            chart: {
                type: 'bar',
                height: getBookingChartHeight()
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '12px',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: {{json_encode($chartData['timeline'])}},
            },
            yaxis: {
                title: {
                    text: '{{currency_symbol()}}'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return currencyPosition === 'left'
                            ? currencySymbol + val
                            : val + currencySymbol;
                    }
                }
            },
            legend: {
                show: false
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex_column-chart"), options);
        chart.render();

        let bookingChartResizeTimer = null;
        window.addEventListener('resize', function () {
            clearTimeout(bookingChartResizeTimer);
            bookingChartResizeTimer = setTimeout(function () {
                chart.updateOptions({
                    chart: {
                        height: getBookingChartHeight()
                    }
                }, false, false);
            }, 150);
        });
    </script>
@endpush
