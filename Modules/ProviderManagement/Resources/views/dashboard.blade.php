@extends('providermanagement::layouts.new-master')

@section('title',translate('Dashboard'))

@section('page_heading', translate('Dashboard'))
@section('page_subheading', translate('Manage teams, bookings, and field operations.'))

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
    <div class="main-content c365-dashboard">
        <div class="container-fluid">
            <div class="row g-3 c365-dash-metrics">
                <div class="col-lg-5 col-md-12">
                    <div class="c365-hero-card">
                        <div>
                            <div class="c365-hero-card__label">{{translate('total_earning')}}</div>
                            <h2 class="c365-hero-card__value">{{with_currency_symbol($data[0]['top_cards']['total_earning'])}}</h2>
                        </div>
                        <div class="c365-hero-card__icon"><span class="material-icons">payments</span></div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12">
                    <div class="row g-3 h-100">
                        <div class="col-sm-4">
                            <div class="c365-metric-card">
                                <div class="c365-metric-card__icon"><span class="material-icons">subscriptions</span></div>
                                <div class="c365-metric-card__label">{{translate('total_subscription')}}</div>
                                <p class="c365-metric-card__value">{{$data[0]['top_cards']['total_subscribed_services']}}</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="c365-metric-card">
                                <div class="c365-metric-card__icon"><span class="material-icons">engineering</span></div>
                                <div class="c365-metric-card__label">{{translate('total_service_man')}}</div>
                                <p class="c365-metric-card__value">{{$data[0]['top_cards']['total_service_man']}}</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="c365-metric-card">
                                <div class="c365-metric-card__icon"><span class="material-icons">task_alt</span></div>
                                <div class="c365-metric-card__label">{{translate('total_booking_served')}}</div>
                                <p class="c365-metric-card__value">{{$data[0]['top_cards']['total_booking_served']}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 c365-promo-card" data-bg-img="{{asset('public/assets/provider-module')}}/img/media/create-ads-bg.png">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="media align-items-center gap-3">
                            <img width="84" src="{{asset('public/assets/provider-module')}}/img/media/create-ads.png"
                                 alt="">
                            <div class="media-body">
                                <h4 class="mb-2">{{translate('Want To Get Highlighted?')}}</h4>
                                <p>{{translate('Create ads to get highlighted on the app and web browser')}}</p>
                            </div>
                        </div>

                        <a class="text-white btn btn--primary" href="{{route('provider.advertisements.ads-create')}}">{{translate('Create Ads')}}</a>
                    </div>
                </div>
            </div>

            <div class="row g-4 dashboard-sync-row">
                <div class="col-lg-9 dashboard-sync-col">
                    <div class="card earning-statistics dashboard-sync-card dashboard-chart-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 dashboard-chart-header">
                                <h4 class="c1">{{translate('Earning_Statistics')}}</h4>
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
                                        <select class="js-select" onchange="update_chart(this.value)">
                                            @php($from_year=date('Y'))
                                            @php($to_year=$from_year-10)
                                            @while($from_year!=$to_year)
                                                <option
                                                    value="{{$from_year}}" {{session()->has('dashboard_earning_graph_year') && session('dashboard_earning_graph_year') == $from_year?'selected':''}}>
                                                    {{$from_year}}
                                                </option>
                                                @php($from_year--)
                                            @endwhile
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="apex-line-chart-container dashboard-chart-slot">
                                <div id="apex_line-chart">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 dashboard-sync-col">
                    <div class="card recent-transactions dashboard-sync-card dashboard-transactions-card">
                        <div class="card-body">
                            <h4 class="mb-3 c1">{{translate('Recent_Transactions')}}</h4>
                            @if(isset($data[6]['recent_transactions']) && count($data[6]['recent_transactions']) > 0)
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <img src="{{asset('public/assets/provider-module')}}/img/icons/arrow-up.png" alt="">
                                    <p class="opacity-75">{{ translate(':count transactions this month', ['count' => $data[6]['this_month_trx_count']]) }}</p>
                                </div>
                            @endif
                            <div class="events">
                                @if($data[6]['this_month_trx_count'] > 0)
                                    @foreach($data[6]['recent_transactions'] as $transaction)
                                        <div class="event">
                                            <div class="knob"></div>
                                            <div class="title">
                                                @if($transaction->debit>0)
                                                    <h5>{{ translate(':amount debited', ['amount' => with_currency_symbol($transaction->debit)]) }}</h5>
                                                @else
                                                    <h5>{{ translate(':amount credited', ['amount' => with_currency_symbol($transaction->credit)]) }}</h5>
                                                @endif
                                            </div>
                                            <div class="description">
                                                <p>{{ format_time_by_business_settings($transaction->created_at, 'd M') }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="line"></div>
                            </div>
                            @if(count($data[6]['recent_transactions']) < 1)
                                <div class="d-flex flex-column justify-content-center align-items-center height-80p w-100">
                                    <div class="recent-transaction-no-data text-center">
                                        <img src="{{ asset('public/assets/admin-module/img/icons/no-transaction.svg') }}" alt=""> <br>
                                        <p class="fs-16 text-dark-icon">{{ translate('No Recent Transactions') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="card top-providers h-100">
                        <div class="card-header d-flex justify-content-between gap-10 pb-0">
                            <div class="">
                                <h5 class="c1 mb-3">{{translate('Recent_Bookings_Activity')}}</h5>

                                <ul class="nav nav--tabs custom-activate-tab gap-2 gap-sm-4">
                                    <li class="nav-item cursor-pointer">
                                        <a class="nav-link px-0 active" data-bs-toggle="tab" id="normal-tab"
                                           data-bs-target="#normal-bookings">{{translate('Normal_Bookings')}}</a>
                                    </li>
                                    <li class="nav-item cursor-pointer">
                                        <a class="nav-link px-0" data-bs-toggle="tab" id="customize-tab"
                                           data-bs-target="#customize-bookings">{{translate('Customize_Booking')}}</a>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{route('provider.booking.list', ['booking_status'=>'pending', 'service_type'=>'all'])}}" id="view-all-link"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="normal-bookings">
                                <div class="card-body">
                                    <div class="row gy-5">
                                        <div class="@if(count($data[3]['recent_bookings']) < 1) col-lg-12 @else col-lg-6 @endif">
                                            <ul class="common-list pe-xl-3">
                                                @if(count($data[3]['recent_bookings']) < 1)
                                                <div class="py-5 text-center">
                                                    <div class="opacity-75">{{translate('No_recent_bookings_are_available')}}</div>
                                                </div>
                                                @endif
                                                @foreach($data[3]['recent_bookings'] as $key=>$booking)
                                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer booking-item"
                                                        data-route="@if($booking->is_repeated) {{ route('provider.booking.repeat_details', [$booking->id]) }}?web_page=details @else {{ route('provider.booking.details', [$booking->id]) }}?web_page=details @endif">
                                                        <div class="media align-items-center gap-3">
                                                            <div class="avatar avatar-lg">
                                                                <img class="avatar-img rounded"
                                                                     src="{{$booking->detail[0]->service?->thumbnail_full_path}}"
                                                                     alt="{{ translate('booking thumbnail') }}">
                                                            </div>
                                                            <div class="media-body ">
                                                                <h5>{{translate('Booking')}}
                                                                    # {{$booking['readable_id']}}
                                                                    @if($booking['is_repeated'])
                                                                        <img width="17" height="17"
                                                                             src="{{ asset('public/assets/admin-module/img/icons/repeat.svg') }}"
                                                                             class="rounded-circle repeat-icon" alt="{{ translate('repeat') }}">
                                                                    @endif
                                                                </h5>
                                                                <p>{{ format_time_by_business_settings($booking->created_at, 'd-M-y') }}</p>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="badge py-2 px-3 badge-info">{{ $booking['booking_status'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @if(count($data[3]['recent_bookings']) > 0)
                                        <div class="col-lg-6 border-lg-start">
                                            <div class="d-flex justify-content-center">
                                                <div id="apex-donut-chart"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade" id="customize-bookings">
                                <div class="card-body">
                                    <div class="row gy-5">
                                        <div class="@if(count($data[7]['customized_bookings']) < 1) col-lg-12 @else col-lg-6 @endif">
                                            <ul class="common-list pe-xl-3">
                                                @if(count($data[7]['customized_bookings']) < 1)
                                                <div class="py-5 text-center">
                                                    <div class="opacity-75">{{translate('No_recent_customized_bookings_are_available')}}</div>
                                                </div>
                                                @endif
                                                @foreach($data[7]['customized_bookings'] as $key=>$customBooking)
                                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer">
                                                        <div class="media align-items-center gap-3">
                                                            <div class="avatar avatar-lg">
                                                                <img class="avatar-img rounded"
                                                                     src="{{$customBooking?->service?->thumbnail_full_path}}"
                                                                     alt="{{ translate('booking thumbnail') }}">
                                                            </div>
                                                            <div class="media-body ">
                                                                <h5>{{$customBooking?->service?->name}}</h5>
                                                                <span>{{$customBooking?->sub_category?->name}}</span>
                                                                <p>{{ format_time_by_business_settings($customBooking->created_at, 'd-M-y') }}</p>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="badge py-2 px-3 badge-info">{{ $customBooking['booking_status'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @if(count($data[7]['customized_bookings']) > 0)
                                        <div class="col-lg-6 border-lg-start">
                                            <div class="d-flex justify-content-center">
                                                <div id="apex-donut-chart2"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card recent-activities h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('My_Subscriptions')}}</h5>
                            <a href="{{route('provider.sub_category.subscribed')}}"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">
                                @if(count($data[4]['subscriptions']) < 1)
                                    <span
                                        class="opacity-75">{{translate('No_subscribed_services_are_available')}}</span>
                                @endif
                                @foreach($data[4]['subscriptions'] as $key=>$subscription)
                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-auto">
                                        <div class="media gap-10">
                                            <div class="avatar avatar-lg">
                                                <img class="avatar-img rounded"
                                                     src="{{$subscription->sub_category->image_full_path}}"
                                                     alt="">
                                            </div>
                                            <div class="media-body">
                                                <h5>{{ Str::limit($subscription->sub_category?$subscription->sub_category->name:'', 20) }}</h5>
                            <p>{{ translate(':count Services', ['count' => $subscription['services_count']]) }}</p>
                                            </div>
                                        </div>
                                        <span
                            class="">{{ translate(':count Bookings Completed', ['count' => $subscription['completed_booking_count']]) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card top-providers h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('Serviceman_List')}}</h5>
                            <a href="{{route('provider.serviceman.list')}}?status=all"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">
                                @if(count($data[5]['serviceman_list']) < 1)
                                    <span class="opacity-75">{{translate('No_active_servicemen_are_available')}}</span>
                                @endif
                                @foreach($data[5]['serviceman_list'] as $key=>$serviceman)
                                    <li class="@if($key==0) pt-0 @endif">
                                        <div class="media gap-3">
                                            <div class="avatar avatar-lg">
                                                <a href="{{route('provider.serviceman.show', [$serviceman['id']])}}">
                                                    <img class="object-fit rounded-circle"
                                                         src="{{$serviceman->user['profile_image_full_path']}}"
                                                         alt="">
                                                </a>
                                            </div>
                                            <div class="media-body ">
                                                <a href="{{route('provider.serviceman.show', [$serviceman['id']])}}">
                                                    <h5>{{Str::limit($serviceman->user['first_name'],30) }}</h5>
                                                </a>
                                                <p>{{Str::limit($serviceman->user['phone'],30) }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guidline Offcanvas -->
    <div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel">
        <div class="offcanvas-header bg-light p-20">
            <h3 class="mb-0">{{ translate('Business Information Setup Guideline') }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-20 bg-white">
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                        <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="fi fi-rr-angle-up"></i>
                        </div>
                        <span class="fw-bold text-start text-dark">{{ translate('Basic Information') }}</span>
                    </button>
                </div>
                <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                    <div class="card card-body">
                        <h5 class="mb-2">Company Data</h5>
                        <p class="fs-12">
                            {{ translate('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio tellus, laoreet pharetra auctor eget, fringilla nec lectus. Nullam in feugiat est. Nam in interdum ligula, non elementum purus. Aenean eu lectus diam. Cras elementum neque sed nibh consequat, nec gravida purus vehicula. Morbi  Learn more.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-12 p-sm-20 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_02" aria-expanded="true">
                        <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                            <i class="fi fi-rr-angle-up"></i>
                        </div>
                        <span class="fw-bold text-start text-dark">{{ translate('General Setup') }}</span>
                    </button>

                </div>

                <div class="collapse mt-3" id="collapseGeneralSetup_02">
                    <div class="card card-body">
                        <p class="fs-12">
                            <strong>{{ translate('company_Name') }}:</strong>
                            {{ translate('the_company_name_often_serves_as_the_primary_identifier_for_your_business_as_a_legal_entity.') }}
                        </p>
                        <p class="fs-12">
                            <strong>{{ translate('email') }}:</strong>
                            {{ translate('a_company_email_system_often_provides_centralized_management_and_archiving_of_business_communications.') }}
                        </p>
                        <p class="fs-12">
                            <strong>{{ translate('phone') }}:</strong>
                            {{ translate('a_phone_number_provides_customers_and_partners_with_a_direct_and_immediate_way_to_reach_your_business_for_urgent_inquiries,_support_needs,_or_quick_questions.') }}
                        </p>
                        <p class="fs-12">
                            <strong>{{ translate('country') }}:</strong>
                            {{ translate('country_name_field_when_setting_up_a_business_is_essential_for_a_multitude_of_reasons,_touching_upon_legal,_operational,_financial,_and_marketing_aspects.') }}
                        </p>
                        <p class="fs-12">
                            <strong>{{ translate('address') }}:</strong>
                            {{ translate('an_address_is_legally_required_in_every_country_and_builds_trust_with_your_customers_online._') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{asset('public/assets/provider-module')}}/plugins/apex/apexcharts.min.js"></script>

    <script>
        "use strict";

        function syncProviderDashboardChartLayout() {
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
                    name: "{{translate('total_earnings')}}",
                    data: @json($chart_data['total_earning'])
                }
            ],
            chart: {
                height: syncProviderDashboardChartLayout(),
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
                        return value;
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
                floating: true,
                offsetY: -10,
                offsetX: 0
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

        let providerDashboardChart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        providerDashboardChart.render().then(function () {
            syncProviderDashboardChartHeight();
        });

        function update_chart(year) {
            var url = '{{route('provider.update-dashboard-earning-graph')}}?year=' + year;

            $.getJSON(url, function (response) {
                providerDashboardChart.updateSeries([{
                        name: "{{translate('total_earnings')}}",
                        data: response.total_earning
                    }]);
                syncProviderDashboardChartHeight();
            });
        }

        let providerDashboardChartResizeTimer = null;
        const syncProviderDashboardChartHeight = () => {
            clearTimeout(providerDashboardChartResizeTimer);
            providerDashboardChartResizeTimer = setTimeout(function () {
                const chartHeight = syncProviderDashboardChartLayout();
                providerDashboardChart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncProviderDashboardChartHeight);
        window.addEventListener('orientationchange', syncProviderDashboardChartHeight);
        window.addEventListener('load', syncProviderDashboardChartHeight);

        $(document).ready(function () {
            let routeName = '{{ route('provider.booking.details', ['id' => ':id']) }}';

            $(".booking-item").on('click', function(){
                location.href = $(this).data('route');
            });

            $('.custom-activate-tab li a').on('click', function (e) {
                e.preventDefault();
                $('.custom-activate-tab li a').removeClass('active');
                $(this).addClass('active');
            })
        });
    </script>
    <script>
        var options = {
            series: [{{$booking_counts['normal_booking_count']}}, {{$booking_counts['post_count']}}],
            chart: {
                type: 'donut',
                // width: '200',
            },
            labels: ["{{ translate('Total Normal Bookings') }}", "{{ translate('Total Customized Bookings') }}"],
            colors: ['#006666', '#2EAD6F'],
            legend: {
                position: 'bottom'
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex-donut-chart"), options);
        var chart2 = new ApexCharts(document.querySelector("#apex-donut-chart2"), options);
        chart.render();
        chart2.render();
    </script>
    <script>
        $(document).ready(function() {
            $('#normal-tab').click(function() {
                var target = $(this).attr('data-bs-target');
                $('#view-all-link').attr('href', "{{route('provider.booking.list', ['booking_status'=>'pending'])}}");
            });
        });
        $(document).ready(function() {
            $('#customize-tab').click(function() {
                var target = $(this).attr('data-bs-target');
                $('#view-all-link').attr('href', "{{route('provider.booking.post.list', ['type'=>'all'])}}");
            });
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
