@extends('adminmodule::layouts.new-master')

@section('title',translate('Dashboard'))

@section('page_heading', translate('Dashboard'))
@section('page_subheading', translate('Track your business operations and performance.'))

@push('css_or_js')
    <style>
        .dashboard-sync-row > .dashboard-sync-col {
            display: flex;
        }

        .dashboard-sync-card {
            width: 100%;
            min-height: 0;
        }

        .dashboard-chart-card .card-body,
        .dashboard-transactions-card .card-body {
            min-height: 0;
        }

        .dashboard-chart-card .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .dashboard-chart-header {
            flex: 0 0 auto;
        }

        .dashboard-chart-slot {
            flex: 1 1 auto;
            width: 100%;
            min-height: 240px;
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .dashboard-sync-row > .dashboard-sync-col {
                display: block;
            }

            .dashboard-sync-card {
                height: auto !important;
            }

            .dashboard-chart-slot {
                height: auto !important;
            }
        }

        /* Hero card & Buttons #006666 theme override */
        .c365-hero-card,
        .c365-dashboard .c365-hero-card {
            background: linear-gradient(135deg, #006666 0%, #004d4d 100%) !important;
            background-color: #006666 !important;
            color: #ffffff !important;
        }

        .c365-hero-card .c365-hero-card__label,
        .c365-hero-card .c365-hero-card__value,
        .c365-hero-card .c365-hero-card__icon span {
            color: #ffffff !important;
        }

        .option-select-btn li label input:checked + span,
        .option-select-btn li label span.active,
        .option-select-btn span {
            background-color: #006666 !important;
            border-color: #006666 !important;
            color: #ffffff !important;
        }
    </style>
@endpush

@section('content')
    @can('dashboard')
    <div class="main-content c365-dashboard">
        <div class="container-fluid">
            @if(access_checker('dashboard'))
                @php
                    $isCompanyDashboard = (bool) data_get($data[0], 'top_cards.is_company_dashboard', false);
                    $totalEarning = $isCompanyDashboard
                        ? data_get($data[0], 'top_cards.total_subscription_earning', 0)
                        : (data_get($data[0], 'top_cards.total_commission_earning', 0) + data_get($data[0], 'top_cards.total_fee_earning', 0) + data_get($data[0], 'top_cards.total_subscription_earning', 0));
                @endphp
                <div class="row g-3 c365-dash-metrics">
                    <div class="col-lg-5 col-md-12">
                        <div class="c365-hero-card">
                            <div>
                                <div class="c365-hero-card__label">{{ $isCompanyDashboard ? translate('Subscription revenue') : translate('total_earning') }}</div>
                                <h2 class="c365-hero-card__value">{{with_currency_symbol($totalEarning)}}</h2>
                            </div>
                            <div class="c365-hero-card__icon"><span class="material-icons">account_balance_wallet</span></div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-12">
                        <div class="row g-3 h-100">
                            <div class="col-sm-4">
                                <div class="c365-metric-card">
                                    <div class="c365-metric-card__icon"><span class="material-icons">{{ $isCompanyDashboard ? 'card_membership' : 'trending_up' }}</span></div>
                                    <div class="c365-metric-card__label">{{ $isCompanyDashboard ? translate('Active subscriptions') : translate('commission_earning') }}</div>
                                    <p class="c365-metric-card__value">
                                        @if($isCompanyDashboard)
                                            {{ (int) data_get($data[0], 'top_cards.active_subscriptions', 0) }}
                                        @else
                                            {{with_currency_symbol(data_get($data[0], 'top_cards.total_commission_earning', 0))}}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="c365-metric-card">
                                    <div class="c365-metric-card__icon"><span class="material-icons">{{ $isCompanyDashboard ? 'event_available' : 'receipt_long' }}</span></div>
                                    <div class="c365-metric-card__label">{{ $isCompanyDashboard ? translate('Package bookings') : translate('Total Fee Earning') }}</div>
                                    <p class="c365-metric-card__value">
                                        @if($isCompanyDashboard)
                                            {{ (int) data_get($data[0], 'top_cards.package_bookings', 0) }}
                                        @else
                                            {{with_currency_symbol(data_get($data[0], 'top_cards.total_fee_earning', 0))}}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="c365-metric-card">
                                    <div class="c365-metric-card__icon"><span class="material-icons">groups</span></div>
                                    <div class="c365-metric-card__label">{{ $isCompanyDashboard ? translate('Supervisors') : translate('providers') }}</div>
                                    <p class="c365-metric-card__value">{{$data[0]['top_cards']['total_provider']}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 dashboard-sync-row">
                    <div class="col-lg-9 dashboard-sync-col">
                        <div class="card earning-statistics dashboard-sync-card dashboard-chart-card">
                            <div class="card-body ps-0">
                                <div class="ps-20 d-flex flex-wrap align-items-center justify-content-between gap-3 dashboard-chart-header">
                                    <h4>{{ $isCompanyDashboard ? translate('Revenue statistics') : translate('earning_statistics') }}</h4>
                                    <div
                                        class="position-relative index-2 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                        <ul class="option-select-btn">
                                            <li>
                                                <label>
                                                    <input type="radio" name="statistics" hidden checked>
                                                    <span class="d-flex align-items-center border shadow-none h-36">{{translate('Yearly')}}</span>
                                                </label>
                                            </li>
                                        </ul>

                                        <div class="select-wrap d-flex flex-wrap gap-10">
                                            <select class="js-select update-chart">
                                                @php
                                                    $from_year = date('Y');
                                                    $to_year = $from_year - 10;
                                                @endphp
                                                @while($from_year != $to_year)
                                                    <option
                                                        value="{{$from_year}}" {{session()->has('dashboard_earning_graph_year') && session('dashboard_earning_graph_year') == $from_year?'selected':''}}>
                                                        {{$from_year}}
                                                    </option>
                                                    @php $from_year--; @endphp
                                                @endwhile
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div id="apex_line-chart" class="dashboard-chart-slot"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 dashboard-sync-col">
                        <div class="card recent-transactions w-100 dashboard-sync-card dashboard-transactions-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-10">
                                    <h4 class="mb-3">{{translate('recent_transactions')}}</h4>
                                    <a href="{{route('admin.transaction.list', ['trx_type'=>'all'])}}"
                                       class="btn-link">{{translate('view_all')}}</a>
                                </div>
                                @if(isset($data[2]['recent_transactions']) && count($data[2]['recent_transactions']) > 0)
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <img src="{{asset('public/assets/admin-module')}}/img/icons/arrow-up.png"
                                             alt="">
                                        <p class="opacity-75">{{ translate(':count transactions this month', ['count' => $data[2]['this_month_trx_count']]) }}</p>
                                    </div>
                                @endif
                                <div class="events w-100">
                                    @foreach($data[2]['recent_transactions'] as $transaction)
                                        <div class="event">
                                            <div class="knob"></div>
                                            <div class="d-flex align-items-center gap-1 justify-content-between">
                                                <div class="title">
                                                    @if($transaction->debit>0)
                                                        <h5 class="text-break">{{ translate(':amount debited', ['amount' => with_currency_symbol($transaction->debit)]) }}</h5>
                                                    @else
                                                        <h5 class="text-break">{{ translate(':amount credited', ['amount' => with_currency_symbol($transaction->credit)]) }}</h5>
                                                    @endif

                                                    <p class="m-0 fs-13 d-flex align-items-center gap-1">
                                                       <span class="material-symbols-outlined fs-5 cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="Provider">
                                                         person
                                                       </span>
                                                        @if($transaction?->from_user?->provider)
                                                            {{Str::limit($transaction->from_user->provider->company_name, 30)}}
                                                        @else
                                                            {{Str::limit($transaction?->from_user?->first_name.' '.$transaction?->from_user?->last_name, 30)}}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="description">
                                                    <p class="fs-12">{{ format_time_by_business_settings($transaction->created_at, 'd M') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                        <!-- <div class="d-flex flex-column justify-content-center align-items-center height-80p w-100">
                                            <div class="recent-transaction-no-data text-center">
                                                <img src="{{ asset('public/assets/admin-module/img/icons/no-transaction.svg') }}" alt=""> <br>
                                                <p class="fs-16 text-dark-icon">{{ translate('No Recent Transactions') }}</p>
                                            </div>
                                        </div> -->
                                    <div class="line"></div>
                                </div>

                                @if(count($data[2]['recent_transactions']) < 1)

                                <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100">
                                    <div class="recent-transaction-no-data text-center">
                                        <img src="{{ asset('public/assets/admin-module/img/icons/no-transaction.svg') }}" alt=""> <br>
                                        <p class="fs-16 text-dark-icon">{{ translate('No Recent Transactions') }}</p>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="card top-providers">
                            <div class="card-header d-flex justify-content-between gap-10">
                                <h5>{{ $isCompanyDashboard ? translate('Top supervisors') : translate('top_providers') }}</h5>
                                <a href="{{route('admin.provider.list')}}"
                                   class="btn-link">{{translate('view_all')}}</a>
                            </div>
                            <div class="card-body">
                                <ul class="common-list">
                                    @foreach($data[4]['top_providers'] as $provider)
                                        <li class="provider-redirect"
                                            data-route="{{route('admin.provider.details',[$provider->id])}}?web_page=overview">
                                            <div class="media gap-3">
                                                <div class="avatar avatar-lg">
                                                    <img class="avatar-img rounded-circle" src="{{ $provider->logo_full_path }}" alt="{{ translate('logo') }}">
                                                </div>
                                                <div class="media-body ">
                                                    <h5>{{\Illuminate\Support\Str::limit($provider->company_name,20)}}</h5>
                                                    <span class="common-list_rating d-flex gap-1">
                                                        <span class="material-icons">star</span>
                                                        {{$provider->avg_rating}}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 col-sm-6">
                        <div class="card recent-activities">
                            <div class="card-header d-flex justify-content-between gap-10">
                                <h5>{{translate('recent_bookings')}}</h5>
                                <a href="{{route('admin.booking.list', ['booking_status'=>'pending', 'service_type' => 'all'])}}"
                                   class="btn-link">{{translate('view_all')}}</a>
                            </div>
                            <div class="card-body">
                                <ul class="common-list">
                                    @foreach($data[3]['bookings'] as $booking)
                                        <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer recent-booking-redirect"
                                            data-route="@if($booking->is_repeated) {{ route('admin.booking.repeat_details', [$booking->id]) }}?web_page=details @else {{ route('admin.booking.details', [$booking->id]) }}?web_page=details @endif">
                                        <div class="media align-items-center gap-3">
                                                <div class="avatar avatar-lg">
                                                    <img class="avatar-img rounded"
                                                         src="{{ $booking->detail[0]->service?->thumbnail_full_path }}"
                                                         alt="{{ translate('provider-logo') }}">
                                                </div>
                                                <div class="media-body ">
                                                    <h5 class="d-flex align-items-center">{{translate('Booking')}}# {{$booking->readable_id}}
                                                        @if($booking->is_repeated)
                                                            <img src="{{ asset('public/assets/admin-module/img/icons/repeat.svg') }}"
                                                                 class="rounded-circle repeat-icon m-1" alt="{{ translate('repeat') }}">
                                                        @endif
                                                    </h5>
                                                    <p>{{ format_time_by_business_settings($booking->created_at, 'd-m-Y', ', ') }}</p>
                                                </div>
                                            </div>
                                            <span
                                                class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">{{$booking->booking_status}}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="card top-providers">
                            <div class="card-header d-flex flex-column gap-10">
                                <h5>{{ translate('booking_statistics - :date', ['date' => date('M, Y')]) }}</h5>
                            </div>
                            <div class="card-body booking-statistics-info">
                                @if(isset($data[5]['zone_wise_bookings']))
                                    <ul class="common-list after-none gap-10 d-flex flex-column">
                                        @foreach($data[5]['zone_wise_bookings'] as $booking)
                                            <li>
                                                <div
                                                    class="mb-2 d-flex align-items-center justify-content-between gap-10 flex-wrap">
                                                    <span
                                                        class="zone-name">{{$booking->zone?$booking->zone->name:translate('zone_not_available')}}</span>
                                                    <span
                                                        class="booking-count">{{ translate(':count bookings', ['count' => $booking->total]) }}</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{$booking->total}}%"
                                                         aria-valuenow="{{$booking->total}}" aria-valuemin="0"
                                                         aria-valuemax="100"></div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <span class="opacity-50">{{translate('No Bookings Found')}}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- New Dynamic Sections: Top Demanded Services & Top Customers --}}
                <div class="row g-4 mt-1">
                    {{-- Top Demanded Services / Packages Section --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ translate('الخدمات والباقات الأكثر طلباً') }}</h5>
                                @if(Route::has('admin.service.index'))
                                    <a href="{{ route('admin.service.index') }}" class="btn-link">{{ translate('view_all') }}</a>
                                @endif
                            </div>
                            <div class="card-body">
                                @if(isset($top_services) && count($top_services) > 0)
                                    <ul class="common-list gap-10 d-flex flex-column">
                                        @foreach($top_services as $item)
                                            @php
                                                $serviceObj = $item->service ?? $item;
                                                $name = $item->service_name ?? $serviceObj->name ?? translate('Unknown Service');
                                                $reqCount = $item->total_requests ?? $item->booking_details_count ?? 0;
                                                $thumb = $serviceObj->thumbnail_full_path ?? asset('public/assets/placeholder.png');
                                            @endphp
                                            <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between py-2 border-bottom">
                                                <div class="media align-items-center gap-3">
                                                    <div class="avatar avatar-lg">
                                                        <img class="avatar-img rounded" src="{{ $thumb }}" alt="{{ $name }}" onerror="this.src='{{ asset('public/assets/placeholder.png') }}'">
                                                    </div>
                                                    <div class="media-body">
                                                        <h6 class="mb-1 text-capitalize fw-bold">{{ $name }}</h6>
                                                        <span class="text-muted small">{{ translate('Requests') }}: {{ $reqCount }}</span>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill py-2 px-3 badge-primary">{{ translate(':count bookings', ['count' => $reqCount]) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-4">
                                        <span class="material-icons opacity-50 fz-36 mb-2">category</span>
                                        <span class="opacity-50">{{ translate('No Services Found') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Top Customers Section --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ translate('العملاء الأكثر طلباً') }}</h5>
                                @if(Route::has('admin.customer.index'))
                                    <a href="{{ route('admin.customer.index') }}" class="btn-link">{{ translate('view_all') }}</a>
                                @endif
                            </div>
                            <div class="card-body">
                                @if(isset($top_customers) && count($top_customers) > 0)
                                    <ul class="common-list gap-10 d-flex flex-column">
                                        @foreach($top_customers as $customer)
                                            <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between py-2 border-bottom">
                                                <div class="media align-items-center gap-3">
                                                    <div class="avatar avatar-lg">
                                                        <img class="avatar-img rounded-circle" src="{{ $customer->profile_image_full_path }}" alt="{{ $customer->first_name }}" onerror="this.src='{{ asset('public/assets/placeholder.png') }}'">
                                                    </div>
                                                    <div class="media-body">
                                                        <h6 class="mb-1 fw-bold">{{ trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) ?: ($customer->email ?? translate('Customer')) }}</h6>
                                                        <span class="text-muted small">{{ $customer->phone ?? $customer->email }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge rounded-pill py-2 px-3 badge-primary">{{ translate(':count bookings', ['count' => $customer->bookings_count ?? 0]) }}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-4">
                                        <span class="material-icons opacity-50 fz-36 mb-2">person</span>
                                        <span class="opacity-50">{{ translate('No Customers Found') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="text-center">
                                    {{translate('welcome_to_admin_panel')}}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @else
        <div class="main-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body dashboard-empty d-center">
                        <div class="text-center">
                            <img src="{{asset('/public/assets/empty-dashboard.png')}}" alt="">
                            <h3 class="p-2 mt-3">{{ translate('Welcome to :business', ['business' => business_config('business_name', 'business_information')?->live_values]) }}</h3>
                            <p class="">{{ translate('Get started by using the left menu to manage your tasks and tools.') }}</p>
                            <h6 class="">{{ translate('Happy working') }}!</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

@endsection


@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>

    <script>
        'use strict';

        $('.js-select.update-chart').on('change', function() {
            var selectedYear = $(this).val();
            localStorage.setItem('selectedYear', selectedYear); // Store the selected year in local storage
            update_chart(selectedYear);
        });

        // On page load, check if a year is stored in local storage
        $(document).ready(function() {
            var storedYear = localStorage.getItem('selectedYear');
            if (storedYear) {
                $('.js-select.update-chart').val(storedYear); // Set the select to the stored year
                update_chart(storedYear); // Update the chart with the stored year
            }
        });

        function syncAdminDashboardChartLayout() {
            const chartCard = document.querySelector('.dashboard-chart-card');
            const transactionsCard = document.querySelector('.dashboard-transactions-card');
            const chartBody = chartCard ? chartCard.querySelector('.card-body') : null;
            const chartHeader = chartBody ? chartBody.querySelector('.dashboard-chart-header') : null;
            const chartSlot = document.querySelector('.dashboard-chart-slot');

            if (!chartCard || !transactionsCard || !chartBody || !chartHeader || !chartSlot) {
                return 386;
            }

            if (window.innerWidth < 992) {
                chartCard.style.removeProperty('height');
                transactionsCard.style.removeProperty('height');
                chartSlot.style.removeProperty('height');
                return window.innerWidth < 576 ? 320 : 386;
            }

            chartCard.style.removeProperty('height');
            transactionsCard.style.removeProperty('height');
            chartSlot.style.removeProperty('height');

            const chartNaturalHeight = Math.round(chartCard.getBoundingClientRect().height);
            const transactionsNaturalHeight = Math.round(transactionsCard.getBoundingClientRect().height);
            const targetHeight = Math.max(chartNaturalHeight, transactionsNaturalHeight);
            const bodyStyles = window.getComputedStyle(chartBody);
            const headerStyles = window.getComputedStyle(chartHeader);
            const paddingY = (parseFloat(bodyStyles.paddingTop) || 0) + (parseFloat(bodyStyles.paddingBottom) || 0);
            const headerSpace = chartHeader.offsetHeight
                + (parseFloat(headerStyles.marginTop) || 0)
                + (parseFloat(headerStyles.marginBottom) || 0);
            const chartHeight = Math.max(targetHeight - paddingY - headerSpace, 260);

            chartCard.style.height = `${targetHeight}px`;
            transactionsCard.style.height = `${targetHeight}px`;
            chartSlot.style.height = `${chartHeight}px`;

            return chartHeight;
        }

        var options = {
            series: [
                {
                    name: "{{ $isCompanyDashboard ? translate('Total revenue') : translate('total_earnings') }}",
                    data: @json($chart_data['total_earning'])
                },
                {
                    name: "{{ $isCompanyDashboard ? translate('Package subscription revenue') : translate('admin_commission') }}",
                    data: @json($chart_data['commission_earning'])
                }
            ],
            chart: {
                height: syncAdminDashboardChartLayout(),
                type: 'line',
                dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 0.2
                },
                toolbar: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    offsetX: 0,
                    formatter: function (value) {
                        return Math.abs(value)
                    }
                },
            },
            colors: ['#006666', '#2EAD6F'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
            },
            grid: {
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                borderColor: '#D4E4F7',
                strokeDashArray: 5,
            },
            markers: {
                size: 1
            },
            theme: {
                mode: 'light',
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                floating: false,
                offsetY: -10,
                offsetX: 0,
                itemMargin: {
                    horizontal: 10,
                    vertical: 10
                },
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 200,
                left: 10
            },
        };

        if (localStorage.getItem('dir') === 'rtl') {
            options.yaxis.labels.offsetX = -20;
        }

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render().then(function () {
            syncAdminDashboardChartHeight();
        });

        function update_chart(year) {
            var url = '{{route('admin.update-dashboard-earning-graph')}}?year=' + year;

            $.getJSON(url, function (response) {
                chart.updateSeries([{
                    name: "{{ $isCompanyDashboard ? translate('Total revenue') : translate('total_earning') }}",
                    data: response.total_earning
                }, {
                    name: "{{ $isCompanyDashboard ? translate('Package subscription revenue') : translate('admin_commission') }}",
                    data: response.commission_earning
                }]);
                syncAdminDashboardChartHeight();
            });
        }

        let adminDashboardChartResizeTimer = null;
        const syncAdminDashboardChartHeight = () => {
            clearTimeout(adminDashboardChartResizeTimer);
            adminDashboardChartResizeTimer = setTimeout(function () {
                const chartHeight = syncAdminDashboardChartLayout();
                chart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncAdminDashboardChartHeight);
        window.addEventListener('orientationchange', syncAdminDashboardChartHeight);
        window.addEventListener('load', syncAdminDashboardChartHeight);


        $(".provider-redirect").on('click', function(){
            location.href = $(this).data('route');
        });

        $(".recent-booking-redirect").on('click', function(){
            location.href = $(this).data('route');
        });

        (function abbreviateBusinessSummaryNumbers() {
            function abbreviate(num) {
                var abs = Math.abs(num);
                if (abs >= 1.0e+12) return (num / 1.0e+12).toFixed(abs % 1.0e+12 === 0 ? 0 : 2).replace(/\.?0+$/, '') + 'T';
                if (abs >= 1.0e+9)  return (num / 1.0e+9 ).toFixed(abs % 1.0e+9  === 0 ? 0 : 2).replace(/\.?0+$/, '') + 'B';
                if (abs >= 1.0e+6)  return (num / 1.0e+6 ).toFixed(abs % 1.0e+6  === 0 ? 0 : 2).replace(/\.?0+$/, '') + 'M';
                if (abs >= 1.0e+3)  return (num / 1.0e+3 ).toFixed(abs % 1.0e+3  === 0 ? 0 : 2).replace(/\.?0+$/, '') + 'K';
                return num.toString();
            }
            $('.business-summary > h2').each(function () {
                var $el = $(this);
                var raw = $.trim($el.text());
                var match = raw.match(/^(\D*)([\d.]+)(\D*)$/);
                if (!match) return;
                var prefix = match[1];
                var numeric = parseFloat(match[2].replace(/,/g, ''));
                var suffix = match[3];
                if (isNaN(numeric)) return;
                $el.attr('title', raw).text(prefix + abbreviate(numeric) + suffix);
            });
        })();
    </script>
@endpush
