@extends('providermanagement::layouts.master')

@section('title',translate('Earning_Report'))
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
                                <a href="{{route('provider.report.business.overview')}}"
                                   class="nav-link">{{translate('Overview')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.earning')}}"
                                   class="nav-link active">{{translate('Earning_Report')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('provider.report.business.expense')}}"
                                   class="nav-link">{{translate('Expense_Report')}}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 fz-16 fw-bold text-dark">{{translate('Search Data')}}</div>
                            <form action="{{route('provider.report.business.earning')}}" method="GET" class="filter-form">
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
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end"
                                        id="from-filter__div">
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
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end"
                                        id="to-filter__div">
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
                        <div class="col-xl-3">
                            <div class="d-flex flex-wrap gap-2 report-chart-sync-summary">
                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar"
                                         src="{{asset('public/assets/admin-module')}}/img/icons/net_profit.png" alt="">
                                    <div class="text-center">
                                        @php($providerEarningNetProfit = array_sum($chartData['net_profit']))
                                        <h2 class="fz-26" title="{{with_currency_symbol($providerEarningNetProfit)}}">{{with_compact_currency_symbol($providerEarningNetProfit)}}</h2>
                                        <span class="fz-12">{{translate('Net_Profit')}}</span>
                                    </div>
                                </div>

                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar"
                                         src="{{asset('public/assets/admin-module')}}/img/icons/commission_earning.png"
                                         alt="">
                                    <div class="text-center">
                                        @php($providerEarningTotalEarning = array_sum($chartData['total_earning']))
                                        <h2 class="fz-26" title="{{with_currency_symbol($providerEarningTotalEarning)}}">{{with_compact_currency_symbol($providerEarningTotalEarning)}}</h2>
                                        <span class="fz-12">{{translate('Total_Earnings')}}</span>
                                    </div>
                                </div>

                                <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                                    <img width="35" class="avatar"
                                         src="{{asset('public/assets/admin-module')}}/img/icons/total_expenses.png"
                                         alt="">
                                    <div class="text-center">
                                        @php($providerEarningTotalExpense = array_sum($chartData['total_expense']))
                                        <h2 class="fz-26" title="{{with_currency_symbol($providerEarningTotalExpense)}}">{{with_compact_currency_symbol($providerEarningTotalExpense)}}</h2>
                                        <span class="fz-12">{{translate('Total_Expenses')}}</span>
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
                                            <li><a class="dropdown-item"
                                                   href="{{route('provider.report.business.earning.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="align-middle">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Booking_Amount')}}</th>

                                        <th>{{translate('Total_Service_Discount')}}</th>
                                        <th>{{translate('Provider_Paid_From_Total_Service_Discount')}}</th>
                                        <th>{{translate('Total_Coupon_Discount')}}</th>
                                        <th>{{translate('Provider_Paid_From_Total_Coupon_Discount')}}</th>
                                        <th>{{translate('Total_Campaign_Discount')}}</th>
                                        <th>{{translate('Provider_Paid_From_Total_Campaign_Discount')}}</th>

                                        <th>{{translate('Subtotal')}}</th>
                                        <th>{{translate('VAT_/_Tax')}}</th>
                                        <th>{{translate('Admin_Commission')}}</th>
                                        <th>{{translate('Provider_Net_Income')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($bookings as $key=>$booking)
                                        @php($provider_total_expense = ($booking->booking_details_amounts->discount_by_provider??0) + ($booking->booking_details_amounts->coupon_discount_by_provider??0) + ($booking->booking_details_amounts->campaign_discount_by_provider??0))
                                        @php($provider_total_earning = $booking->booking_details_amounts->provider_earning??0)
                                        @php($provider_net_profit = $provider_total_earning)

                                        <tr>
                                            <td>{{$bookings->firstitem()+$key}}</td>
                                            <td>{{$booking['readable_id']}}</td>
                                            <td>{{with_currency_symbol($booking['total_booking_amount'])}}</td>

                                            <td>{{with_currency_symbol($booking['total_discount_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking->booking_details_amounts->discount_by_provider??0)}}</td>
                                            <td>{{with_currency_symbol($booking['total_coupon_discount_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking->booking_details_amounts->coupon_discount_by_provider??0)}}</td>
                                            <td>{{with_currency_symbol($booking['total_campaign_discount_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking->booking_details_amounts->campaign_discount_by_provider??0)}}</td>

                                            <td>{{with_currency_symbol($booking['total_booking_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking['total_tax_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking->booking_details_amounts->admin_commission??0)}}</td>
                                            <td>{{with_currency_symbol($provider_net_profit)}}</td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
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
                                {!! $bookings->links() !!}
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
                    <img src="{{asset('public/assets/admin-module')}}/img/media/formula.png" class="dark-support"
                         alt="">
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

        function syncProviderEarningChartLayout() {
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

                return window.innerWidth < 576 ? 240 : 274;
            }

            if (!summaryStack || !chartCard || !chartBody || !chartTitle || !chartSlot) {
                return 274;
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
                    name: "{{translate('net_profit')}}",
                    data: {{json_encode($chartData['net_profit'])}}
                },
                {
                    name: "{{translate('total_earning')}}",
                    data: {{json_encode($chartData['total_earning'])}}
                },
                {
                    name: "{{translate('total_expense')}}",
                    data: {{json_encode($chartData['total_expense'])}}
                }],
            chart: {
                height: syncProviderEarningChartLayout(),
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
            colors: ['#1B3A6B', '#2EAD6F', '#4A8BC4'],
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
        };

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render().then(function () {
            syncProviderEarningChartHeight();
        });

        let providerEarningChartResizeTimer = null;
        const syncProviderEarningChartHeight = () => {
            clearTimeout(providerEarningChartResizeTimer);
            providerEarningChartResizeTimer = setTimeout(function () {
                const chartHeight = syncProviderEarningChartLayout();
                chart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncProviderEarningChartHeight);
        window.addEventListener('orientationchange', syncProviderEarningChartHeight);
        window.addEventListener('load', syncProviderEarningChartHeight);
    </script>
@endpush
