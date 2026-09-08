@extends('adminmodule::layouts.master')

@section('title',translate('Earning_Report'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('/public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Earning Details')}}</h2>
            </div>

            <div class="mb-4">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.subscription-earning')}}"
                           class="nav-link">{{translate('Subscription Earning')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.commission-earning')}}"
                           class="nav-link active">{{translate('Commission Earning')}}</a>
                    </li>
                </ul>
            </div>

            <div class="row g-4">
                <div class="col-lg-7 col-xl-8 col-xxl-9">
                    <div class="card h-100 commission-donut-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h4>{{translate('Earning Statistics')}}</h4>
                                <div>
                                    @php($adminCommissionTotal = array_sum($chartData))
                                    <h4 class="">
                                        <span class="c1 fw-semibold" title="{{ with_currency_symbol($adminCommissionTotal) }}">{{ with_compact_currency_symbol($adminCommissionTotal) }}</span>
                                        <small class="opacity-75 fw-normal">(Total Earning)</small>
                                    </h4>
                                </div>
                                <form id="barFilterForm" method="GET" action="{{url()->current()}}">
                                    <div class="select-wrap d-flex flex-wrap gap-10">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'bar_filter',
                                            'id'          => 'bar_filter',
                                            'selectClass' => 'js-select',
                                            'options'     => [
                                                '1' => translate('All Times'),
                                                '2' => translate('Last Month'),
                                                '3' => translate('This Year'),
                                                '4' => translate('Last Year'),
                                                '5' => translate('This Month')],
                                            'value'       => $barFilter,
                                            'extraAttrs'  => 'onchange="document.getElementById(\'barFilterForm\').submit();"',
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                </form>
                            </div>
                            <div id="apex-bar-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-xl-4 col-xxl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h4>{{translate('Providers')}}</h4>
                                    <small>Based on activity</small>
                                </div>
                                <form id="filterForm" method="GET" action="{{url()->current()}}">
                                    <div class="select-wrap d-flex flex-wrap gap-10">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'filter',
                                            'id'          => 'provider_filter',
                                            'selectClass' => 'js-select',
                                            'options'     => [
                                                '1' => translate('All Times'),
                                                '2' => translate('Last Month'),
                                                '3' => translate('This Year'),
                                                '4' => translate('Last Year'),
                                                '5' => translate('This Month')],
                                            'value'       => $filter,
                                            'extraAttrs'  => 'onchange="document.getElementById(\'filterForm\').submit();"',
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                </form>
                            </div>
                            <div class="position-relative">
                            <div class="total--subscriptions">
                                <h3 class="fw-bold fs-14 mb-1">{{ $providers['active_provider'] +  $providers['inactive_provider']}}</h3>
                                <div class="fs-12">{{ translate('Commission')}} <br> {{ translate('Based Providers')}}</div>
                            </div>
                                <div id="apex-pie-chart" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}"
                              class="search-form search-form_style-two"
                              method="GET">
                            <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                <input type="search" class="theme-input-style search-form__input"
                                       value="{{$search??''}}" name="search"
                                       placeholder="{{translate('Search the booking id')}}">
                            </div>
                            <button type="submit"
                                    class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="dropdown">
                                <button type="button"
                                        class="btn btn--secondary text-capitalize dropdown-toggle"
                                        data-bs-toggle="dropdown">
                                    <span class="material-icons">file_download</span> {{translate('download')}}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <li><a class="dropdown-item" href="{{ route('admin.report.business.commission.download',['search'=>$search]) }}">{{translate('Excel')}}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="align-middle">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th class="text-center">{{translate('Bokking ID')}}</th>
                                <th class="text-center">{{translate('Booking Date')}}</th>
                                <th class="text-end">{{translate('Booking Amount')}}</th>
                                <th class="text-end">{{translate('Commission')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($commissionEarningList as $key => $earning)
                                <tr>
                                    <td>{{$key+$commissionEarningList?->firstItem()}}</td>
                                    <td class="text-center"><a href="">{{ $earning->booking_id }}</a></td>
                                    <td class="text-center">{{ $earning->created_at }}</td>
                                    <td class="text-end">{{ with_currency_symbol($earning->booking->total_booking_amount) }}</td>
                                    <td class="text-end">{{ with_currency_symbol($earning->admin_commission) }}</td>
                                </tr>
                            @empty
                                @include('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 5,
                                    'variant' => (
                                        filled($search ?? null)
                                        || (($barFilter ?? 1) != 1)
                                        || (($filter ?? 1) != 1)
                                    ) ? 'search' : 'list'])
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $commissionEarningList->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')


    <script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>
    <script>
        "use strict"

        const currencyDecimalPoint = <?php echo json_encode((int)(business_config('currency_decimal_point', 'business_information')['live_values'] ?? 2)); ?>;

        function formatChartAmount(value) {
            return Number(value).toLocaleString(undefined, {
                minimumFractionDigits: currencyDecimalPoint,
                maximumFractionDigits: currencyDecimalPoint,
            });
        }

        function pieChart(activeProviders, inactiveProviders) {
            const options = {
                labels: [
                    `Inactive Providers`,
                    `Active Providers`
                ],
                series: [inactiveProviders, activeProviders],
                colors: [ "#1B3A6B", "#2EAD6F"],
                chart: {
                    width: 460,
                    height: 300,
                    type: 'donut',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                        }
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                responsive: [{
                    breakpoint: 1680,
                    options: {
                        chart: {
                            width: 340,
                            height: 250,
                        },
                    }
                },{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300,
                            height: 230,
                        },
                    }
                }],
                legend: {
                    position: 'bottom',
                    offsetY: -5,
                    // height: 30,
                },
            };

            const chart = new ApexCharts(document.querySelector("#apex-pie-chart"), options);
            chart.render();
        }
        pieChart(<?php echo json_encode($providers['active_provider']); ?>, <?php echo json_encode($providers['inactive_provider']); ?>);

        function barChart(categories, chartData, formattedChartData) {
            const colors = ["#1B3A6B", "#2EAD6F", "#4A8BC4", "#7BC99A", "#F3C278"];
            const options2 = {
                series: [{
                    name : '{{ translate('total') }}',
                    data: chartData
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                colors: colors,
                plotOptions: {
                    bar: {
                        columnWidth: '10px',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return formatChartAmount(value);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (value, { dataPointIndex }) {
                            return formattedChartData[dataPointIndex] ?? value;
                        }
                    }
                }
            };

            const chart2 = new ApexCharts(document.querySelector("#apex-bar-chart"), options2);
            chart2.render();
        }

        barChart(
            <?php echo json_encode($categories); ?>,
            <?php echo json_encode($chartData); ?>,
            <?php echo json_encode(array_map(fn($amount) => with_currency_symbol($amount), $chartData)); ?>
        );

        function walletSlider() {
            const swiper = new Swiper('.wallet-slider', {
                slidesPerView: 'auto',
                spaceBetween: 20,
                freeMode: true,
                navigation: {
                    nextEl: '.swiper-next',
                    prevEl: '.swiper-prev',
                },
            });
        }
        walletSlider()

    </script>
@endpush
