@extends('adminmodule::layouts.master')

@section('title',translate('Business_overview_Report'))
@php
    $position = business_config('currency_symbol_position', 'business_information')['live_values']??'right';
@endphp
@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
        .net-profit-tooltip {
            --bs-tooltip-max-width: none;
        }

        .net-profit-tooltip .tooltip-inner {
            white-space: nowrap;
        }

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

        @media (max-width: 991.98px) {
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
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Business_Reports')}}</h2>
            </div>

            <div class="mb-4">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.overview')}}"
                           class="nav-link active">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.earning')}}"
                           class="nav-link">{{translate('Earning_Report')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.expense')}}"
                           class="nav-link">{{translate('Expense_Report')}}</a>
                    </li>
                </ul>
            </div>

            @php
                $dateRangeOptions = [
                    'all_time'               => translate('All Time'),
                    'this_week'              => translate('This Week'),
                    'last_week'              => translate('Last Week'),
                    'this_month'             => translate('This Month'),
                    'last_month'             => translate('Last Month'),
                    'last_15_days'           => translate('Last 15 Days'),
                    'this_year'              => translate('This Year'),
                    'last_year'              => translate('Last Year'),
                    'last_6_month'           => translate('Last 6 Months'),
                    'this_year_1st_quarter'  => translate('This Year 1st Quarter'),
                    'this_year_2nd_quarter'  => translate('This Year 2nd Quarter'),
                    'this_year_3rd_quarter'  => translate('This Year 3rd Quarter'),
                    'this_year_4th_quarter'  => translate('This Year 4th Quarter'),
                    'custom_date'            => translate('Custom Date')];
                $isCustomRange = array_key_exists('date_range', $queryParams) && $queryParams['date_range'] == 'custom_date';
            @endphp

            <div class="card">
                <div class="card-body">
                    <div class="mb-3 fz-16">{{translate('Search Data')}}</div>

                    <form action="{{route('admin.report.business.overview')}}" method="GET" class="filter-form">
                        <div class="row g-3">
                            <div class="col-lg-4 col-sm-6">
                                @include('partials._form-field', [
                                    'type'        => 'select',
                                    'name'        => 'zone_ids[]',
                                    'id'          => 'zone_selector__select',
                                    'label'       => translate('Zone'),
                                    'multiple'    => true,
                                    'selectClass' => 'js-select zone__select',
                                    'prependOptions' => [
                                        ['value' => 'all', 'label' => translate('Select All')]],
                                    'options'     => $zones->pluck('name', 'id')->toArray(),
                                    'value'       => $queryParams['zone_ids'] ?? [],
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                @include('partials._form-field', [
                                    'type'        => 'select',
                                    'name'        => 'category_ids[]',
                                    'id'          => 'category_selector__select',
                                    'label'       => translate('Category'),
                                    'multiple'    => true,
                                    'selectClass' => 'js-select category__select',
                                    'prependOptions' => [
                                        ['value' => 'all', 'label' => translate('Select All')]],
                                    'options'     => $categories->pluck('name', 'id')->toArray(),
                                    'value'       => $queryParams['category_ids'] ?? [],
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                @include('partials._form-field', [
                                    'type'        => 'select',
                                    'name'        => 'sub_category_ids[]',
                                    'id'          => 'sub_category_selector__select',
                                    'label'       => translate('Sub Category'),
                                    'multiple'    => true,
                                    'selectClass' => 'js-select sub-category__select',
                                    'prependOptions' => [
                                        ['value' => 'all', 'label' => translate('Select All')]],
                                    'options'     => $sub_categories->pluck('name', 'id')->toArray(),
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
                                    'optionNull'  => translate('Select Date Range'),
                                    'options'     => $dateRangeOptions,
                                    'value'       => $queryParams['date_range'] ?? null,
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-lg-4 col-sm-6 align-self-end {{ $isCustomRange ? '' : 'd-none' }}" id="from-filter__div">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'from',
                                    'id'          => 'from',
                                    'label'       => translate('From'),
                                    'icon'        => 'calendar_month',
                                    'value'       => $queryParams['from'] ?? '',
                                    'extraAttrs'  => 'data-ff-datepicker readonly autocomplete="off" inputmode="none"',
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-lg-4 col-sm-6 align-self-end {{ $isCustomRange ? '' : 'd-none' }}" id="to-filter__div">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'to',
                                    'id'          => 'to',
                                    'label'       => translate('To'),
                                    'icon'        => 'calendar_month',
                                    'value'       => $queryParams['to'] ?? '',
                                    'extraAttrs'  => 'data-ff-datepicker readonly autocomplete="off" inputmode="none"',
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{route('admin.report.business.overview')}}" class="btn btn--secondary btn-sm">{{translate('reset')}}</a>
                                <button type="submit" class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row gy-3 pt-4 report-chart-sync-row">
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-3 report-chart-sync-summary">
                        <div class="card flex-row gap-4 p-30 flex-wrap align-items-center">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/net_profit.png" alt="">
                            <div class="">
                                @php($adminOverviewNetProfit = array_sum($chart_data['earnings']) - array_sum($chart_data['expenses']))
                                <h2 class="fz-26" title="{{with_currency_symbol($adminOverviewNetProfit)}}">{{with_compact_currency_symbol($adminOverviewNetProfit)}}</h2>
                                <span class="fz-12">{{translate('Net_Profit')}}</span>
                            </div>
                            <div class="ms--auto align-self-start"
                                 data-bs-toggle="tooltip"
                                 data-bs-placement="top"
                                 data-bs-custom-class="net-profit-tooltip"
                                 data-bs-title="{{ translate('Net Profit = Total Earning - Total Expense') }}">
                                <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg"
                                     class="svg" alt="">
                            </div>
                        </div>
                        <div class="card flex-row gap-4 p-30 flex-wrap align-items-center">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/commission_earning.png"
                                 alt="">
                            <div class="">
                                @php($adminOverviewTotalEarning = array_sum($chart_data['earnings']))
                                <h2 class="fz-26" title="{{with_currency_symbol($adminOverviewTotalEarning)}}">{{with_compact_currency_symbol($adminOverviewTotalEarning)}}</h2>
                                <span class="fz-12">{{translate('total_earning')}}</span>
                            </div>
                        </div>
                        <div class="card flex-row gap-4 p-30 flex-wrap align-items-center">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/expense-bonus.png" alt="">
                            <div class="">
                                @php($adminOverviewTotalExpense = array_sum($chart_data['expenses']))
                                <h2 class="fz-26" title="{{with_currency_symbol($adminOverviewTotalExpense)}}">{{with_compact_currency_symbol($adminOverviewTotalExpense)}}</h2>
                                <span class="fz-12">{{translate('total_expense')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card report-chart-sync-card">
                        <div class="card-body ps-0">
                            <h4 class="ps-20 report-chart-sync-title">{{translate('Earning_Statistics')}}</h4>
                            <div id="apex_line-chart" class="report-chart-sync-slot"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <div></div>
                        @can('report_export')
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="dropdown">
                                    <button type="button"
                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        <span class="material-icons">file_download</span> {{translate('download')}}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                        <li><a class="dropdown-item"
                                               href="{{route('admin.report.business.overview.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-nowrap">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Duration')}}</th>
                                <th>{{translate('total_earning')}}</th>
                                <th>{{translate('Total_Expenses')}}</th>
                                <th>{{translate('Net_Profit')}}</th>
                                <th class="text--end">{{translate('Net_Profit_Rate')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($amounts as $item)
                                @php($total_earning = data_get($item, 'admin_commission', 0) + data_get($item, 'earning', 0))
                                @php($total_expense = data_get($item, 'discount_by_admin', 0) + data_get($item, 'coupon_discount_by_admin', 0) + data_get($item, 'campaign_discount_by_admin', 0) + data_get($item, 'bonus', 0) + data_get($item, 'referral_discount', 0) + data_get($item, 'referral_earning', 0))

                                @php($net_profit = $total_earning-$total_expense)
                                @php($net_profit_rate = $total_earning!=0 ? ($net_profit*100)/$total_earning : $net_profit*100)

                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>
                                        @if($deterministic == 'month')
                                            {{DateTime::createFromFormat('!m', $item['month'])->format('F')}}
                                        @elseif($deterministic == 'week')
                                            {{$chart_data['timeline'][$loop->index] ?? data_get($item, 'day', '')}}
                                        @else
                                            {{$item[$deterministic]}}
                                        @endif
                                    </td>
                                    <td>{{with_currency_symbol($total_earning)}}</td>
                                    <td>{{with_currency_symbol($total_expense)}}</td>
                                    <td>{{with_currency_symbol($net_profit)}}</td>
                                    <td class="text--end"><span class="text-success">{{with_currency_symbol($net_profit_rate)}} %</span>
                                    </td>
                                </tr>
                            @empty
                                @include('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 6,
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

@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script src="{{asset('public/assets/admin-module/js/moment.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/js/daterangepicker.min.js')}}"></script>
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

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#category_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#sub_category_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });


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

            initSingleDatePicker('#from');
            initSingleDatePicker('#to');

            $('#date-range').on('change', function () {
                if (this.value === 'custom_date') {
                    $('#from-filter__div').removeClass('d-none');
                    $('#to-filter__div').removeClass('d-none');
                }

                if (this.value !== 'custom_date') {
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

        function syncAdminOverviewChartLayout() {
            const summaryStack = document.querySelector('.report-chart-sync-summary');
            const chartCard = document.querySelector('.report-chart-sync-card');
            const chartBody = chartCard ? chartCard.querySelector('.card-body') : null;
            const chartTitle = chartBody ? chartBody.querySelector('.report-chart-sync-title') : null;
            const chartSlot = document.querySelector('.report-chart-sync-slot');

            if (window.innerWidth < 992) {
                if (chartCard) {
                    chartCard.style.removeProperty('height');
                }

                if (chartSlot) {
                    chartSlot.style.removeProperty('height');
                }

                return window.innerWidth < 576 ? 240 : 290;
            }

            if (!summaryStack || !chartCard || !chartBody || !chartTitle || !chartSlot) {
                return 290;
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
                    data: {{json_encode($chart_data['earnings'])}}
                },
                {
                    name: "{{translate('Expenses')}}",
                    data: {{json_encode($chart_data['expenses'])}}
                }
            ],
            chart: {
                height: syncAdminOverviewChartLayout(),
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
            colors: ['#6F8AED', '#CAD2FF'],
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
                categories: {{json_encode($chart_data['timeline'])}}
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
            syncAdminOverviewChartHeight();
        });

        let adminOverviewChartResizeTimer = null;
        const syncAdminOverviewChartHeight = () => {
            clearTimeout(adminOverviewChartResizeTimer);
            adminOverviewChartResizeTimer = setTimeout(function () {
                const chartHeight = syncAdminOverviewChartLayout();
                chart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncAdminOverviewChartHeight);
        window.addEventListener('orientationchange', syncAdminOverviewChartHeight);
        window.addEventListener('load', syncAdminOverviewChartHeight);
    </script>
@endpush
