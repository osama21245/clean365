@extends('providermanagement::layouts.master')

@section('title',translate('Business_overview_Report'))
@php
    $position = business_config('currency_symbol_position', 'business_information')['live_values'] ?? 'right';
@endphp

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
        .report-chart-sync-row > [class*=col-] {
            display: flex;
        }

        .report-chart-sync-summary,
        .report-chart-sync-card {
            width: 100%;
        }

        .report-chart-sync-summary {
            display: flex;
            flex-direction: column;
        }

        .report-chart-sync-summary > .card {
            width: 100%;
        }

        .report-chart-sync-card .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .report-chart-sync-title {
            flex: 0 0 auto;
        }

        .report-chart-sync-slot {
            flex: 1 1 auto;
            width: 100%;
            min-height: 240px;
        }

        @media (max-width: 1199.98px) {
            .report-chart-sync-row > [class*=col-] {
                display: block;
            }

            .report-chart-sync-card {
                height: auto !important;
            }

            .report-chart-sync-slot {
                min-height: 280px;
                height: auto !important;
            }
        }

        @media (max-width: 575.98px) {
            .report-chart-sync-slot {
                min-height: 240px;
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
                        <h2 class="page-title">{{translate('Business_Reports')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.overview')}}" class="nav-link active">{{translate('Overview')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.earning')}}" class="nav-link">{{translate('Earning_Report')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.expense')}}" class="nav-link">{{translate('Expense_Report')}}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 fz-16 fw-bold text-dark">{{translate('Search Data')}}</div>
                            <form action="{{route('provider.report.business.overview')}}" method="GET" class="filter-form">
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

                    <div class="row g-2 pt-2 report-chart-sync-row">
                        @php($total_earning = 0)
                        @php($total_tax = 0)
                        @foreach($amounts as $key=>$item)
                            @php($total_tax += $item['service_tax'])
                            @php($total_earning = $item['provider_earning'])
                        @endforeach
                        <div class="col-xl-3">
                            <div class="d-flex flex-wrap gap-2 report-chart-sync-summary">
                                <div class="card flex-row justify-content-center gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/net_profit.png" alt="">
                                    <div class="text-start">
                                        @php($providerOverviewNetProfit = array_sum($chartData['earnings']) - array_sum($chartData['expenses']))
                                        <h2 class="fz-26" title="{{with_currency_symbol($providerOverviewNetProfit)}}">{{with_compact_currency_symbol($providerOverviewNetProfit)}}</h2>
                                        <span class="fz-12">{{translate('Net_Profit')}}</span>
                                    </div>
                                </div>

                                <div class="card py-4 px-3 flex-grow-1">
                                    <div class="d-flex justify-content-center gap-4 flex-wrap mb-20 py-1">
                                        <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/total_expense.png" alt="">
                                        <div class="text-start">
                                            @php($providerOverviewTotalExpense = array_sum($chartData['expenses']))
                                            <h2 class="fz-26" title="{{with_currency_symbol($providerOverviewTotalExpense)}}">{{with_compact_currency_symbol($providerOverviewTotalExpense)}}</h2>
                                            <span class="fz-12">{{translate('Total_Expense')}}</span>
                                        </div>
                                    </div>
                                    <div class="tabs-slide-wrap overview__expense-wrap position-relative">
                                        <div class="tabs-inner d-flex gap-2 flex-nowrap text-nowrap">
                                            <div class="overview_expenses tabs-slide_items d-flex align-items-center jsutif-content-center">
                                                <div class="d-flex flex-column align-items-center gap-2 fz-12 bg-light rounded p-10px w-100">
                                                    <span class="fw-bold text-danger" title="{{with_currency_symbol($totalPromotionalCost['campaign'])}}">{{with_compact_currency_symbol($totalPromotionalCost['campaign'])}}</span>
                                                    <span class="opacity-50">{{translate('Campaign')}}</span>
                                                </div>
                                            </div>
                                            <div class="overview_expenses tabs-slide_items d-flex align-items-center jsutif-content-center">
                                                <div class="d-flex flex-column align-items-center gap-2 fz-12 bg-light rounded p-10px w-100">
                                                    <span class="c1 fw-bold" title="{{with_currency_symbol($totalPromotionalCost['discount'])}}">{{with_compact_currency_symbol($totalPromotionalCost['discount'])}}</span>
                                                    <span class="opacity-50">{{translate('Normal_Discount')}}</span>
                                                </div>
                                            </div>
                                            <div class="overview_expenses tabs-slide_items d-flex align-items-center jsutif-content-center">
                                                <div class="d-flex flex-column align-items-center gap-2 fz-12 bg-light rounded p-10px w-100">
                                                    <span class="text-success fw-bold" title="{{with_currency_symbol($totalPromotionalCost['coupon'])}}">{{with_compact_currency_symbol($totalPromotionalCost['coupon'])}}</span>
                                                    <span class="opacity-50">{{translate('Coupon_Discount')}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arrow-area">
                                            <div class="button-prev align-items-center">
                                                <button type="button"
                                                    class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle p-2 d-center">
                                                    <span class="material-symbols-outlined fs-5 lh-1 m-0">chevron_left</span>
                                                </button>
                                            </div>
                                            <div class="button-next align-items-center">
                                                <button type="button"
                                                    class="btn btn-click-next ms-auto border-0 btn-primary rounded-circle p-2 d-center">
                                                    <span class="material-symbols-outlined fs-5 lh-1 m-0">chevron_right</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card flex-row justify-content-center gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/commission_earning.png" alt="">
                                    <div class="text-start">
                                        <h2 class="fz-26" title="{{with_currency_symbol($total_tax)}}">{{with_compact_currency_symbol($total_tax)}}</h2>
                                        <span class="fz-12">{{translate('Total_Tax_Collected')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9">
                            <div class="card report-chart-sync-card">
                                <div class="card-body ps-0">
                                    <h4 class="ps-20 report-chart-sync-title">{{translate('Earning_Statistics')}}</h4>
                                    <div id="apex_line-chart" class="report-chart-sync-slot"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <div></div>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        <button type="button"
                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li><a class="dropdown-item" href="{{route('provider.report.business.overview.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('SL')}}</th>
                                            <th>{{translate('Duration')}}</th>
                                            <th>{{translate('Tax')}}</th>
                                            <th>{{translate('Total_Earning')}}</th>
                                            <th>{{translate('Total_Expenses')}}</th>
                                            <th>{{translate('Net_Profit')}}</th>
                                            <th class="text--end">{{translate('Net_Profit_Rate')}} </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($amounts as $item)
                                        @php($total_earning = $item['provider_earning'])
                                        @php($total_expense = $item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider'])

                                        @php($net_profit = $total_earning)
                                        @php($net_profit_rate = $total_earning!=0 ? ($net_profit*100)/$total_earning : $net_profit*100)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                @if($deterministic == 'month')
                                                    {{DateTime::createFromFormat('!m', $item['month'])->format('F')}}
                                                @elseif($deterministic == 'week')
                                                    {{$chartData['timeline'][$loop->index] ?? data_get($item, 'day', '')}}
                                                @else
                                                    {{$item[$deterministic]}}
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($item['service_tax'])}}</td>
                                            <td>{{with_currency_symbol($item['provider_earning'])}}</td>
                                            <td>{{with_currency_symbol($total_expense)}}</td>
                                            <td>{{with_currency_symbol($net_profit)}}</td>
                                            <td class="text--end"><span class="text-success">{{with_currency_symbol($net_profit_rate)}} %</span></td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 7,
                                            'variant' => (
                                                !empty($queryParams['zone_ids'] ?? [])
                                                || !empty($queryParams['category_ids'] ?? [])
                                                || !empty($queryParams['sub_category_ids'] ?? [])
                                                || (($queryParams['date_range'] ?? 'all_time') !== 'all_time')
                                                || filled($queryParams['from'] ?? null)
                                                || filled($queryParams['to'] ?? null)
                                            ) ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formulaModal" tabindex="-1" aria-labelledby="formulaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="{{asset('public/assets/admin-module')}}/img/media/formula.png" class="dark-support" alt="">
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
            $('.category__select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
            $('.sub-category__select').select2({
                placeholder: "{{translate('Select_sub_category')}}",
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

        function syncProviderOverviewChartLayout() {
            const summaryStack = document.querySelector('.report-chart-sync-summary');
            const chartCard = document.querySelector('.report-chart-sync-card');
            const chartBody = chartCard ? chartCard.querySelector('.card-body') : null;
            const chartTitle = chartBody ? chartBody.querySelector('.report-chart-sync-title') : null;
            const chartSlot = document.querySelector('.report-chart-sync-slot');

            if (window.innerWidth < 1200) {
                if (chartCard) {
                    chartCard.style.removeProperty('height');
                }

                if (chartSlot) {
                    chartSlot.style.removeProperty('height');
                }

                return window.innerWidth < 576 ? 240 : 346;
            }

            if (!summaryStack || !chartCard || !chartBody || !chartTitle || !chartSlot) {
                return 346;
            }

            const bodyStyles = window.getComputedStyle(chartBody);
            const titleStyles = window.getComputedStyle(chartTitle);
            const paddingY = (parseFloat(bodyStyles.paddingTop) || 0) + (parseFloat(bodyStyles.paddingBottom) || 0);
            const titleSpace = chartTitle.offsetHeight
                + (parseFloat(titleStyles.marginTop) || 0)
                + (parseFloat(titleStyles.marginBottom) || 0);
            const cardHeight = Math.round(summaryStack.getBoundingClientRect().height);
            const chartHeight = Math.max(cardHeight - paddingY - titleSpace, 220);

            chartCard.style.height = `${cardHeight}px`;
            chartSlot.style.height = `${chartHeight}px`;

            return chartHeight;
        }

        var options = {
            series: [
                {
                    name: "{{translate('Earnings')}}",
                    data: {{json_encode($chartData['earnings'])}}
                },
                {
                    name: "{{translate('Expenses')}}",
                    data: {{json_encode($chartData['expenses'])}}
                }
            ],
            chart: {
                height: syncProviderOverviewChartLayout(),
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
                    show: true
                }
            },
            colors: ['#1B3A6B', '#2EAD6F'],
            dataLabels: {
                enabled: true,
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
                borderColor: '#CAD2FF',
                strokeDashArray: 5,
            },
            markers: {
                size: 1
            },
            theme: {
                mode: 'light',
            },
            xaxis: {
                categories: {{json_encode($chartData['timeline'])}}
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
                position: 'top',
                horizontalAlign: 'center',
                floating: true,
                offsetY: 0,
                offsetX: 0
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 200,
                left: 10
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        floating: false,
                        offsetY: 0,
                        offsetX: 0
                    }
                }
            }],
        };

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render().then(function () {
            syncProviderOverviewChartHeight();
        });

        let providerOverviewChartResizeTimer = null;
        const syncProviderOverviewChartHeight = () => {
            clearTimeout(providerOverviewChartResizeTimer);
            providerOverviewChartResizeTimer = setTimeout(function () {
                const chartHeight = syncProviderOverviewChartLayout();
                chart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncProviderOverviewChartHeight);
        window.addEventListener('orientationchange', syncProviderOverviewChartHeight);
        window.addEventListener('load', syncProviderOverviewChartHeight);
    </script>
@endpush
