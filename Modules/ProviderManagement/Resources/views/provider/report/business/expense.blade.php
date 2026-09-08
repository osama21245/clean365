@extends('providermanagement::layouts.master')

@section('title',translate('Expense_Report'))
@php
    $position = business_config('currency_symbol_position', 'business_information')['live_values'] ?? 'right';
@endphp

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
        .expense-report-row > [class*=col-] {
            display: flex;
        }

        .expense-report-summary-stack,
        .expense-report-chart-card {
            width: 100%;
        }

        .expense-report-summary-stack {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .expense-report-summary-stack > .card {
            flex: 1 1 0;
        }

        .expense-report-chart-card .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .expense-report-chart-title {
            flex: 0 0 auto;
        }

        .expense-report-chart-slot {
            flex: 1 1 auto;
            width: 100%;
            min-height: 260px;
        }

        @media (max-width: 1199.98px) {
            .expense-report-row > [class*=col-] {
                display: block;
            }

            .expense-report-summary-stack {
                flex-wrap: wrap;
            }

            .expense-report-chart-slot {
                min-height: 280px;
            }

            .expense-report-chart-card {
                height: auto !important;
            }
        }

        @media (max-width: 575.98px) {
            .expense-report-chart-slot {
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
                        <h2 class="page-title">{{translate('Business Reports')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.overview')}}" class="nav-link">{{translate('Overview')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.earning')}}" class="nav-link">{{translate('Earning_Report')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.expense')}}" class="nav-link active">{{translate('Expense_Report')}}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 fz-16 fw-bold text-dark">{{translate('Search Data')}}</div>
                            <form action="{{route('provider.report.business.expense')}}" method="GET" class="filter-form">
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

                    <div class="row g-2 pt-2 expense-report-row">
                        <div class="col-xl-3 expense-report-summary-column">
                            <div class="expense-report-summary-stack">
                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/total_expenses.png" alt="">
                                    <div class="text-center">
                                        <h2 class="fz-26" title="{{with_currency_symbol($totalPromotionalCost['total_expense'])}}">{{with_compact_currency_symbol($totalPromotionalCost['total_expense'])}}</h2>
                                        <span class="fz-12">{{translate('Total_Expenses')}}</span>
                                    </div>
                                </div>

                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/discount.png" alt="">
                                    <div class="text-center">
                                        <h2 class="fz-26" title="{{with_currency_symbol($totalPromotionalCost['discount'])}}">{{with_compact_currency_symbol($totalPromotionalCost['discount'])}}</h2>
                                        <span class="fz-12">{{translate('Normal_Service_Discount')}}</span>
                                    </div>
                                </div>

                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/campaign_discount.png" alt="">
                                    <div class="text-center">
                                        <h2 class="fz-26" title="{{with_currency_symbol($totalPromotionalCost['campaign'])}}">{{with_compact_currency_symbol($totalPromotionalCost['campaign'])}}</h2>
                                        <span class="fz-12">{{translate('Campaign_Discount')}}</span>
                                    </div>
                                </div>

                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/coupon_discount.png" alt="">
                                    <div class="text-center">
                                        <h2 class="fz-26" title="{{with_currency_symbol($totalPromotionalCost['coupon'])}}">{{with_compact_currency_symbol($totalPromotionalCost['coupon'])}}</h2>
                                        <span class="fz-12">{{translate('Coupon_Discount')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 expense-report-chart-column">
                            <div class="card expense-report-chart-card">
                                <div class="card-body ps-0">
                                    <h4 class="ps-20 expense-report-chart-title">{{translate('Expense_Statistics')}}</h4>
                                    <div id="apex_column-chart" class="expense-report-chart-slot"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2">
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
                                               value="{{array_key_exists('search', $queryParams)?$queryParams['search']:''}}" name="search"
                                               placeholder="{{translate('search_by_Booking_ID')}}">
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
                                            <li><a class="dropdown-item" href="{{route('provider.report.business.expense.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a></li>
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
                                            <th>{{translate('Normal_Discount')}}</th>
                                            <th>{{translate('Coupon_Discount')}}</th>
                                            <th>{{translate('Campaign_Discount')}}</th>
                                            <th class="text--end">{{translate('Total_Expense')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($filteredBookingAmounts as $key=>$amount)
                                        @php($total_expense = $amount['discount_by_provider']+$amount['coupon_discount_by_provider']+$amount['campaign_discount_by_provider'])
                                        <tr>
                                            <td>{{$filteredBookingAmounts->firstitem()+$key}}</td>
                                            <td>{{$amount->booking->readable_id}}</td>
                                            <td>{{with_currency_symbol($amount['discount_by_provider'])}}</td>
                                            <td>{{with_currency_symbol($amount['coupon_discount_by_provider'])}}</td>
                                            <td>{{with_currency_symbol($amount['campaign_discount_by_provider'])}}</td>
                                            <td class="text--end">{{with_currency_symbol($total_expense)}}</td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 6,
                                            'variant' => (
                                                filled($queryParams['search'] ?? null)
                                                || !empty($queryParams['zone_ids'] ?? [])
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
                            <div class="d-flex justify-content-end">
                                {!! $filteredBookingAmounts->links() !!}
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

        function syncExpenseChartLayout() {
            const summaryStack = document.querySelector('.expense-report-summary-stack');
            const chartCard = document.querySelector('.expense-report-chart-card');
            const chartBody = document.querySelector('.expense-report-chart-card .card-body');
            const chartTitle = chartBody ? chartBody.querySelector('.expense-report-chart-title') : null;
            const chartSlot = document.querySelector('.expense-report-chart-slot');

            if (window.innerWidth < 1200) {
                if (chartCard) {
                    chartCard.style.removeProperty('height');
                }

                if (chartSlot) {
                    chartSlot.style.removeProperty('height');
                }

                return window.innerWidth < 576 ? 260 : 300;
            }

            if (!summaryStack || !chartCard || !chartBody || !chartTitle || !chartSlot) {
                return 360;
            }

            const bodyStyles = window.getComputedStyle(chartBody);
            const titleStyles = window.getComputedStyle(chartTitle);
            const paddingY = (parseFloat(bodyStyles.paddingTop) || 0) + (parseFloat(bodyStyles.paddingBottom) || 0);
            const titleSpace = chartTitle.offsetHeight
                + (parseFloat(titleStyles.marginTop) || 0)
                + (parseFloat(titleStyles.marginBottom) || 0);
            const cardHeight = Math.round(summaryStack.getBoundingClientRect().height);
            const chartHeight = Math.max(cardHeight - paddingY - titleSpace, 280);

            chartCard.style.height = `${cardHeight}px`;
            chartSlot.style.height = `${chartHeight}px`;

            return chartHeight;
        }

     var options = {
          series: [{
                name: '{{translate('Total_Expense')}}',
                data: {{json_encode($chartData['expenses'])}}
            }],
            chart: {
                type: 'bar',
                height: syncExpenseChartLayout()
            },
            plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '52%',
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
                categories: {{json_encode($chartData['timeline'])}}
            },
            yaxis: {
                title: {
                    text: '{{ currency_symbol() }} '
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
            responsive: [{
                breakpoint: 1200,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '46%'
                        }
                    }
                }
            }, {
                breakpoint: 992,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '58%'
                        }
                    }
                }
            }, {
                breakpoint: 768,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '72%'
                        }
                    }
                }
            }, {
                breakpoint: 480,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '85%'
                        }
                    },
                    yaxis: {
                        title: {
                            text: ''
                        }
                    }
                }
            }],
        };

        var chart = new ApexCharts(document.querySelector("#apex_column-chart"), options);
        chart.render().then(function () {
            syncExpenseChartHeight();
        });

        let expenseChartResizeTimer = null;
        const syncExpenseChartHeight = () => {
            clearTimeout(expenseChartResizeTimer);
            expenseChartResizeTimer = setTimeout(function () {
                const chartHeight = syncExpenseChartLayout();
                chart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncExpenseChartHeight);
        window.addEventListener('orientationchange', syncExpenseChartHeight);
        window.addEventListener('load', syncExpenseChartHeight);
</script>
@endpush
