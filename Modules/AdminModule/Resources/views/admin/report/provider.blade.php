@extends('adminmodule::layouts.master')

@section('title', translate('Provider_Report'))

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
                        <h2 class="page-title">{{translate('Provider_Reports')}}</h2>
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

                            <form action="{{url()->current()}}" method="POST" class="filter-form">
                                @csrf
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
                                            'name'        => 'provider_ids[]',
                                            'id'          => 'provider_selector__select',
                                            'label'       => translate('Provider'),
                                            'multiple'    => true,
                                            'selectClass' => 'provider-select js-select',
                                            'prependOptions' => [
                                                ['value' => 'all', 'label' => translate('Select All')]],
                                            'options'     => $providers->mapWithKeys(function ($p) {
                                                return [$p['id'] => $p['company_name'].' ('.$p['company_phone'].')'];
                                            })->toArray(),
                                            'value'       => $queryParams['provider_ids'] ?? [],
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'sub_category_ids[]',
                                            'id'          => 'sub_category_selector__select',
                                            'label'       => translate('Sub Category'),
                                            'multiple'    => true,
                                            'selectClass' => 'sub-category-select js-select',
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
                                        <a href="{{url()->current()}}" class="btn btn--secondary btn-sm">{{translate('reset')}}</a>
                                        <button type="submit" class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                                    </div>
                                </div>
                            </form>
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
                                               value="{{$search??''}}" name="search"
                                               placeholder="{{translate('search by provider info')}}">
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
                                                   href="{{route('admin.report.provider.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Provider_Info')}}</th>
                                        <th>{{translate('Subscribed_Sub_Categories')}}</th>
                                        <th>{{translate('Service Men')}}</th>
                                        <th>{{translate('Total_Bookings')}}</th>
                                        <th>{{translate('Total_Earnings')}}</th>
                                        <th>{{translate('Commission_Given')}}</th>
                                        <th>{{translate('Completion_Rate')}}</th>
                                        <th>{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($filtered_providers as $key=>$provider)
                                        <tr>
                                            <td>{{$filtered_providers->firstitem()+$key}}</td>
                                            <td>
                                                <h5 class="fw-medium mb-1">
                                                    <a href="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}">
                                                        {{$provider['company_name']}}
                                                    </a>
                                                </h5>
                                                <span class="common-list_rating d-flex align-items-center gap-1">
                                                    <span class="material-icons">star</span>
                                                    {{$provider['avg_rating']}}
                                                </span>
                                            </td>
                                            <td>{{$provider->subscribed_services_count}}</td>
                                            <td>{{$provider->servicemen_count}}</td>
                                            <td>{{$provider->bookings_count}}</td>
                                            <td>{{with_currency_symbol($provider?->owner?->account?->received_balance +  + $provider?->owner?->account?->total_withdrawn)}}</td>
                                            <td>
                                                @php($commissions = [])
                                                @foreach($provider?->owner?->transactions_for_from_user ?? [] as $transaction)
                                                    @php($commissions[] = $transaction['debit'] + $transaction['credit'])
                                                @endforeach
                                                <br/>
                                                {{ with_currency_symbol(array_sum($commissions)) }}
                                            </td>
                                            <td>
                                                @if($provider->bookings_count == 0)
                                                    0%
                                                @elseif($provider->incomplete_bookings_count == 0)
                                                    100%
                                                @else
                                                    @php($completion_rate = 100 - ($provider->incomplete_bookings_count*100)/$provider->bookings_count )
                                                    {{ number_format($completion_rate, 2) }}%
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}"
                                                   class="action-btn btn--light-primary" style="--size: 30px"><span class="material-icons m-0">visibility</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 9,
                                            'variant' => (
                                                filled($search ?? null)
                                                || !empty($queryParams['zone_ids'] ?? [])
                                                || !empty($queryParams['provider_ids'] ?? [])
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
                                {!! $filtered_providers->links() !!}
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
    <script>
        "use strict";

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

        $('#zone_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#provider_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#sub_category_selector__select').on('change', function() {
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
            $('.provider-select').select2({
                placeholder: "{{translate('Select_provider')}}",
            });
            $('.sub-category-select').select2({
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
    </script>
@endpush
