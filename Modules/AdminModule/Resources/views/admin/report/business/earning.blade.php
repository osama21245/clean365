@extends('adminmodule::layouts.master')

@section('title',translate('Earning_Report'))
@php
    $position = business_config('currency_symbol_position', 'business_information')['live_values']??'right';
@endphp

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
        .apexcharts-series:not(:last-child) {opacity: 0;}
        .report-chart-sync-row > [class*=col-] {
            display: flex;
        }

        .report-chart-sync-summary,
        .report-chart-sync-card {
            width: 100%;
            min-height: 0;
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
            min-height: 0;
        }

        .report-chart-sync-title {
            flex: 0 0 auto;
        }

        .report-chart-sync-slot {
            flex: 1 1 auto;
            width: 100%;
            min-height: 240px;
            overflow: hidden;
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
                           class="nav-link">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.earning')}}"
                           class="nav-link active">{{translate('Earning_Report')}}</a>
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

                    <form action="{{route('admin.report.business.earning')}}" method="get" class="filter-form">
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
                                <a href="{{route('admin.report.business.earning')}}" class="btn btn--secondary btn-sm">{{translate('reset')}}</a>
                                <button type="submit" class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row pt-3 g-3 report-chart-sync-row">
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-3 report-chart-sync-summary">
                        <div class="card flex-row gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/earning-total.png" alt="">
                                <div>
                                    @php($adminEarningTotalEarning = array_sum($chart_data['total_earning']))
                                    <h2 class="fz-26" title="{{with_currency_symbol($adminEarningTotalEarning)}}">{{with_compact_currency_symbol($adminEarningTotalEarning)}}</h2>
                                    <div>
                                        <span class="fz-12 text-capitalize">{{translate('total_earning')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card flex-row align-items-center gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar"
                                     src="{{asset('public/assets/admin-module')}}/img/icons/com-earning.png"
                                     alt="">
                                <div>
                                    @php($adminEarningCommission = array_sum($chart_data['commission_earning']))
                                    <h2 class="fz-26" title="{{with_currency_symbol($adminEarningCommission)}}">{{with_compact_currency_symbol($adminEarningCommission)}}</h2>
                                    <span class="fz-12">{{translate('Commission_Earnings')}}</span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <a href="{{route('admin.report.business.commission-earning')}}" class="btn px-2 py-1 btn-outline-primary">
                                    <span class="py-1">{{translate('Details')}}</span>
                                </a>
                            </div>
                        </div>

                        <div class="card flex-row align-items-center gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar"
                                     src="{{asset('public/assets/admin-module')}}/img/icons/sub-ear.png"
                                     alt="">
                                <div>
                                    @php($adminEarningSubscription = array_sum($chart_data['subscription_earning']))
                                    <h2 class="fz-26" title="{{with_currency_symbol($adminEarningSubscription)}}">{{with_compact_currency_symbol($adminEarningSubscription)}}</h2>
                                    <span class="fz-12">{{translate('Subscription Earnings')}}</span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <a href="{{route('admin.report.business.subscription-earning')}}" class="btn px-2 py-1 btn-outline-primary">
                                    <span class="py-1">{{translate('Details')}}</span>
                                </a>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar"
                                     src="{{asset('public/assets/admin-module')}}/img/icons/plat-ear.png" alt="">
                                <div>
                                    @php($adminEarningPlatformFee = array_sum($chart_data['platform_fee']))
                                    <h2 class="fz-26" title="{{with_currency_symbol($adminEarningPlatformFee)}}">{{with_compact_currency_symbol($adminEarningPlatformFee)}}</h2>
                                    <span class="fz-12">{{translate('platform_fee')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card report-chart-sync-card">
                        <div class="card-body ps-0">
                            <h4 class="ps-20 mb-xl-4 report-chart-sync-title">{{translate('Earning_Statistics')}}</h4>
                            <div id="apex_line-chart" class="report-chart-sync-slot"></div>
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
                                       value="{{ request()->get('search') }}" name="search"
                                       placeholder="{{translate('search_by_Booking_ID')}}">
                            </div>
                            <button type="submit"
                                    class="btn btn--primary">{{translate('search')}}</button>
                        </form>
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
                                               href="{{route('admin.report.business.earning.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="align-middle">
                            <tr>
                                <th>{{translate('Reference')}}</th>
                                <th>{{translate('Type')}}</th>
                                <th>{{translate('Booking_Amount')}}</th>

                                <th>{{translate('Total_Service_Discount')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Discount_on_service_by_admin')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Discount_on_service_by_provider')}}</th>
                                <th>{{translate('Total_Coupon_Discount')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Coupon_Discount_on_service_by_admin')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Coupon_Discount_on_service_by_provider')}}</th>
                                <th>{{translate('Total_Campaign_Discount')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Campaign_Discount_on_service_by_admin')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Campaign_Discount_on_service_by_provider')}}</th>

                                <th>{{translate('Subtotal')}}</th>
                                <th>{{translate('VAT_/_Tax')}}</th>
                                <th>{{translate('Admin_Commission')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Provider_Net_Income')}}
                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          data-bs-placement="bottom"
                                          title="{{translate('Provider net income is the amount that come from booking earning (after giving promotional cost)')}}"
                                    >info</span>
                                </th>
                                <th style="min-width: 150px!important;">{{translate('Admin_Net_Income')}}
                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          data-bs-placement="bottom"
                                          title="{{translate('Admin net income is the amount that come from booking commission (after giving promotional cost)')}}"
                                    >info</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($earningRows as $row)
                                <tr>
                                    <td>
                                        @if($row['booking_link'])
                                            <a href="{{$row['booking_link']}}">{{$row['reference']}}</a>
                                        @else
                                            {{$row['reference']}}
                                        @endif
                                    </td>
                                    <td>{{str_replace('_', ' ', $row['entry_type'])}}</td>
                                    <td>{{with_currency_symbol($row['booking_amount'])}}</td>

                                    <td>{{with_currency_symbol($row['total_service_discount'])}}</td>
                                    <td>{{with_currency_symbol($row['discount_by_admin'])}}</td>
                                    <td>{{with_currency_symbol($row['discount_by_provider'])}}</td>
                                    <td>{{with_currency_symbol($row['total_coupon_discount'])}}</td>
                                    <td>{{with_currency_symbol($row['coupon_discount_by_admin'])}}</td>
                                    <td>{{with_currency_symbol($row['coupon_discount_by_provider'])}}</td>
                                    <td>{{with_currency_symbol($row['total_campaign_discount'])}}</td>
                                    <td>{{with_currency_symbol($row['campaign_discount_by_admin'])}}</td>
                                    <td>{{with_currency_symbol($row['campaign_discount_by_provider'])}}</td>

                                    <td>{{with_currency_symbol($row['subtotal'])}}</td>
                                    <td>{{with_currency_symbol($row['tax'])}}</td>
                                    <td>{{with_currency_symbol($row['admin_commission'])}}</td>
                                    <td>{{with_currency_symbol($row['provider_net_income'])}}</td>
                                    <td>{{with_currency_symbol($row['admin_net_income'])}}</td>
                                </tr>
                            @empty
                                @include('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 17,
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
                        {!! $earningRows->links() !!}
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
                <div class="modal-body text-center">
                    <div class="py-4">
                        <h4 class="fw-regular">{{ translate('Total Earning') }}</h4>
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

        function syncAdminEarningChartLayout() {
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

                if (window.innerWidth < 480) {
                    return 280;
                }

                if (window.innerWidth < 768) {
                    return 320;
                }

                return 360;
            }

            if (!summaryStack || !chartCard || !chartBody || !chartTitle || !chartSlot) {
                return 390;
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
                    name: "{{translate('total_earning')}}",
                    data: {{json_encode($chart_data['total_earning'])}}
                },
                {
                    name: "{{translate('commission_earning')}}",
                    data: {{json_encode(array_merge($chart_data['commission_earning']))}}
                },
                {
                    name: "{{translate('subscription_earning')}}",
                    data: {{json_encode(array_merge($chart_data['subscription_earning']))}}
                },
                {
                    name: "{{translate('platform_fee')}}",
                    data: {{json_encode(array_merge($chart_data['platform_fee']))}}
                }
            ],
            chart: {
                height: syncAdminEarningChartLayout(),
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
            colors: ['#1B3A6B', '#2EAD6F', '#4A8BC4', '#7BC99A'],
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
                breakpoint: 992,
                options: {
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        floating: false,
                        offsetY: 0,
                        offsetX: 0
                    }
                }
            }, {
                breakpoint: 768,
                options: {
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        floating: false,
                        offsetY: 0,
                        offsetX: 0
                    },
                    dataLabels: { enabled: false }
                }
            }, {
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        floating: false,
                        fontSize: '11px'
                    },
                    dataLabels: { enabled: false }
                }
            }],
        };

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render().then(function () {
            syncAdminEarningChartHeight();
        });

        let adminEarningChartResizeTimer = null;
        const syncAdminEarningChartHeight = () => {
            clearTimeout(adminEarningChartResizeTimer);
            adminEarningChartResizeTimer = setTimeout(function () {
                const chartHeight = syncAdminEarningChartLayout();
                chart.updateOptions({
                    chart: {
                        height: chartHeight
                    }
                }, false, false);
            }, 150);
        };

        window.addEventListener('resize', syncAdminEarningChartHeight);
        window.addEventListener('orientationchange', syncAdminEarningChartHeight);
        window.addEventListener('load', syncAdminEarningChartHeight);
    </script>
@endpush
