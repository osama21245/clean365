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
                           class="nav-link active">{{translate('Subscription Earning')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.commission-earning')}}"
                           class="nav-link">{{translate('Commission Earning')}}</a>
                    </li>
                </ul>
            </div>

            <div class="mb-4">
                <div class="earning-details-card-wrapper">
                    <div class="card left-card">
                        <div class="card-body py-3">
                            <img class="position-absolute left-0 bottom-0" src="{{asset('/public/assets/admin-module/img/icons/mask-shape.png')}}" alt="">
                            <div class="text-center position-relative">
                                <img src="{{asset('/public/assets/admin-module/img/icons/wallet.png')}}" alt="">
                                <h3 class="text-primary fw-bold fs-4 mb-2 mt-4" title="{{ with_currency_symbol($subscriptionTotal) }}">{{ with_compact_currency_symbol($subscriptionTotal) }}</h3>
                                <div class="fw-medium">{{translate('Total Earning')}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card slider-card">
                        <div class="card-body">
                            <div class="wallet-slider">
                                <div class="swiper-prev">
                                    <span class="material-symbols-outlined">west</span>
                                </div>
                                <div class="swiper-next">
                                    <span class="material-symbols-outlined">east</span>
                                </div>
                                <div class="swiper-wrapper">
                                    @foreach($subscriptionPackages as $package)
                                    <div class="swiper-slide">
                                        <div class="wallet-slider-card">
                                            <img src="{{asset('/public/assets/admin-module/img/icons/wallet.png')}}" alt="">
                                            <div class="fw-medium mt-2 mb-1">{{ $package->name }}</div>
                                            @php($adminSubPackagePrice = $package->subscriberPackageLogs->sum('package_price'))
                                            <h4 class="fw-bold" title="{{ with_currency_symbol($adminSubPackagePrice) }}">{{ with_compact_currency_symbol($adminSubPackagePrice) }}</h4>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7 col-xl-8 col-xxl-9">
                    <div class="card h-100 subscription-donut-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h4>{{translate('Subscription Based Earning Statistics')}}</h4>
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
                                <h3 class="fw-bold mb-2">{{ $pieChartData['totalSubscribers'] }}</h3>
                                <div>{{ translate('Total') }} <br> {{ translate('Subscription') }}</div>
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
                                       placeholder="{{translate('Search package name')}}">
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
                                    <li><a class="dropdown-item" href="{{ route('admin.report.business.subscription.download',['search'=>$search]) }}">{{translate('Excel')}}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="align-middle">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Provider')}}</th>

                                <th>{{translate('Current Package')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Package Duration')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Total Subscription Used')}}</th>
                                <th>{{translate('Total Subscription Earning')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Total VAT / Tax')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Net Earning')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($packagesSubscribers as $key => $subscriber)
                                <tr>
                                    <td>{{$key+$packagesSubscribers?->firstItem()}}</td>
                                    <td>
                                        <a href="">
                                            {{ $subscriber?->provider?->company_name }}
                                        </a>
                                    </td>
                                    <td>{{ $subscriber?->package?->name }}</td>
                                    <td>{{ $subscriber?->package?->duration }} days</td>
                                    <td>{{ $subscriber?->logs->count() }} time</td>
                                    <td>{{ with_currency_symbol($subscriber?->logs->sum('package_price') ) }}</td>
                                    <td>{{ with_currency_symbol($subscriber?->logs->sum('vat_amount')) }}</td>
                                    <td>{{ with_currency_symbol($subscriber?->logs->sum('package_price') - $subscriber?->logs->sum('vat_amount')) }}</td>
                                </tr>
                            @empty
                                @include('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 8,
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
                        {!! $packagesSubscribers->links() !!}
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

        function pieChart(activeSubscribers, inactiveSubscribers) {
            const options = {
                labels: [
                    `Inactive Subscribers`,
                    `Active Subscribers`
                ],
                series: [inactiveSubscribers, activeSubscribers],
                colors: ["#1B3A6B", "#2EAD6F"],
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
                },
            };

            const chart = new ApexCharts(document.querySelector("#apex-pie-chart"), options);
            chart.render();
        }

        pieChart(
            <?php echo $pieChartData['activeSubscribers']; ?>,
            <?php echo $pieChartData['inactiveSubscribers']; ?>
        );


        function barChart(categories, chartData, formattedChartData) {
            const colors = ["#1B3A6B", "#2EAD6F", "#4A8BC4", "#7BC99A", "#F3C278"];
            const options2 = {
                series: [{
                    name: '{{ translate('total') }}',
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
