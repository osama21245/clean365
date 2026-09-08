@extends('adminmodule::layouts.master')

@section('title',translate('Expense_Report'))
@php
    $position = business_config('currency_symbol_position', 'business_information')['live_values']??'right';
@endphp

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
    <style>
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

                    <div class="mb-4">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{route('admin.report.business.overview')}}"
                                   class="nav-link">{{translate('Overview')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.report.business.earning')}}"
                                   class="nav-link">{{translate('Earning_Report')}}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.report.business.expense')}}"
                                   class="nav-link active">{{translate('Expense_Report')}}</a>
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

                            <form action="{{route('admin.report.business.expense')}}" method="get" class="filter-form">
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
                                        <a href="{{route('admin.report.business.expense')}}" class="btn btn--secondary btn-sm">{{translate('reset')}}</a>
                                        <button type="submit" class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-4 mt-4">
                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/total_expenses.png"
                                 alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['total_expense'])}}">{{with_compact_currency_symbol($total_promotional_cost['total_expense'])}}</h2>
                                <span class="fz-12">{{translate('Total_Expenses')}}</span>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/discount.png" alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['discount'])}}">{{with_compact_currency_symbol($total_promotional_cost['discount'])}}</h2>
                                <span class="fz-12">{{translate('Normal_Service_Discount')}}</span>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/campaign_discount.png"
                                 alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['campaign'])}}">{{with_compact_currency_symbol($total_promotional_cost['campaign'])}}</h2>
                                <span class="fz-12">{{translate('Campaign_Discount')}}</span>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/coupon_discount.png"
                                 alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['coupon'])}}">{{with_compact_currency_symbol($total_promotional_cost['coupon'])}}</h2>
                                <span class="fz-12">{{translate('Coupon_Discount')}}</span>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/extra_discount.png"
                                 alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['bonus'])}}">{{with_compact_currency_symbol($total_promotional_cost['bonus'])}}</h2>
                                <span class="fz-12 text-capitalize">{{translate('bonus_discount')}}</span>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/extra_discount.png"
                                 alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['referral_discount'])}}">{{with_compact_currency_symbol($total_promotional_cost['referral_discount'])}}</h2>
                                <span class="fz-12 text-capitalize">{{translate('referral_discount')}}</span>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30 flex-wrap flex-grow-1">
                            <img width="35" class="avatar"
                                 src="{{asset('public/assets/admin-module')}}/img/icons/extra_discount.png"
                                 alt="">
                            <div>
                                <h2 class="fz-26" title="{{with_currency_symbol($total_promotional_cost['referral_earning'])}}">{{with_compact_currency_symbol($total_promotional_cost['referral_earning'])}}</h2>
                                <span class="fz-12 text-capitalize">{{translate('referral_earning')}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body ps-0">
                            <h4 class="ps-20">{{translate('Booking_Expense_Statistics')}}</h4>
                            <div id="apex_column-chart"></div>
                        </div>
                    </div>

                    <div class="card mt-4">
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
                                               value="{{request('search')}}" name="search"
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
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                       href="{{route('admin.report.business.expense.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a>
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
                                        <th class="text--start">{{translate('Reference')}}</th>
                                        <th class="text--start">{{translate('Type')}}</th>
                                        <th class="text--end">{{translate('Normal_Discount')}}</th>
                                        <th class="text--end">{{translate('Coupon_Discount')}}</th>
                                        <th class="text--end">{{translate('Campaign_Discount')}}</th>
                                        <th class="text--end">{{translate('Total_Expense')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($filtered_expenses as $key=>$amount)
                                        <tr>
                                            <td>{{$filtered_expenses->firstitem()+$key}}</td>
                                            <td class="text--start">{{$amount['reference']}}</td>
                                            <td class="text--start">{{str_replace('_', ' ', $amount['expense_type'])}}</td>
                                            <td class="text--end">{{with_currency_symbol($amount['normal_discount'])}}</td>
                                            <td class="text--end">{{with_currency_symbol($amount['coupon_discount'])}}</td>
                                            <td class="text--end">{{with_currency_symbol($amount['campaign_discount'])}}</td>
                                            <td class="text--end">{{with_currency_symbol($amount['total_expense'])}}</td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 7,
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
                                {!! $filtered_expenses->links() !!}
                            </div>
                        </div>
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

        var options = {
            series: [
                {
                    name: '{{translate('normal_discount')}}',
                    data: {{json_encode($chart_data['normal_discount'])}}
                },
                {
                    name: '{{translate('campaign_discount')}}',
                    data: {{json_encode($chart_data['campaign_discount'])}}
                },
                {
                    name: '{{translate('coupon_discount')}}',
                    data: {{json_encode($chart_data['coupon_discount'])}}
                },
                {
                    name: '{{translate('expenses')}}',
                    data: {{json_encode($chart_data['expenses'])}}
                },
                {
                    name: '{{translate('bonus')}}',
                    data: {{json_encode($chart_data['bonus'])}}
                }, {
                    name: '{{translate('referral_discount')}}',
                    data: {{json_encode($chart_data['referral_discount'])}}
                }, {
                    name: '{{translate('referral_earning')}}',
                    data: {{json_encode($chart_data['referral_earning'])}}
                }],
            chart: {
                type: 'bar',
                height: 460
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
                categories: {{json_encode($chart_data['timeline'])}}
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
                show: true,
                position: 'bottom',
                horizontalAlign: 'center'
            },
            responsive: [{
                breakpoint: 1200,
                options: {
                    chart: { height: 400 },
                    plotOptions: { bar: { columnWidth: '46%' } },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        itemMargin: {
                            horizontal: 8,
                            vertical: 4
                        }
                    }
                }
            }, {
                breakpoint: 992,
                options: {
                    chart: { height: 360 },
                    plotOptions: { bar: { columnWidth: '58%' } },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '11px',
                        itemMargin: {
                            horizontal: 6,
                            vertical: 4
                        }
                    }
                }
            }, {
                breakpoint: 768,
                options: {
                    chart: { height: 320 },
                    plotOptions: { bar: { columnWidth: '72%' } },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '11px',
                        itemMargin: {
                            horizontal: 6,
                            vertical: 4
                        }
                    }
                }
            }, {
                breakpoint: 480,
                options: {
                    chart: { height: 280 },
                    plotOptions: { bar: { columnWidth: '85%' } },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '10px',
                        itemMargin: {
                            horizontal: 4,
                            vertical: 4
                        }
                    },
                    yaxis: { title: { text: '' } }
                }
            }],
        };

        var chart = new ApexCharts(document.querySelector("#apex_column-chart"), options);
        chart.render();
    </script>
@endpush
