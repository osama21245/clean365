@extends('adminmodule::layouts.master')

@section('title',translate('Transaction_Report'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Transaction_Reports')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <div class="statistics-card statistics-card__primary border flex-grow-1 d-flex gap-2 justify-content-between h-100 align-items-center">
                                        <div>
                                            <h2 title="{{with_currency_symbol($adminTotalEarning ?? 0)}}">{{with_compact_currency_symbol($adminTotalEarning ?? 0)}}</h2>
                                            <h3>{{translate('Admin_earning')}}</h3>
                                        </div>
                                        <div class="absolute-img position-static align-self-start"  data-bs-toggle="tooltip" data-bs-title="{{translate('Admin balance means total Earning of the admin')}}">
                                            <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg" class="svg" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="statistics-card statistics-card__info border flex-grow-1 d-flex gap-2 justify-content-between h-100 align-items-center">
                                        <div>
                                            <h2 title="{{with_currency_symbol($commission_earning??0)}}">{{with_compact_currency_symbol($commission_earning??0)}}</h2>
                                            <h3>{{translate('Admin_commission')}}</h3>
                                        </div>
                                        <div class="absolute-img position-static align-self-start"  data-bs-toggle="tooltip" data-bs-title="{{translate('Admin balance means total Earning of the admin')}}">
                                            <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg" class="svg" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="statistics-card statistics-card__ongoing border flex-grow-1 flex-grow-1 d-flex gap-2 justify-content-between h-100 align-items-center">
                                        <div>
                                            <h2 title="{{with_currency_symbol($extra_fee)}}">{{with_compact_currency_symbol($extra_fee)}}</h2>
                                            <h3>{{translate('extra_fee')}}</h3>
                                        </div>
                                        <div class="absolute-img position-static align-self-start"  data-bs-toggle="tooltip" data-bs-title="{{translate('extra fee means the earning from booking extra fee')}}">
                                            <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg" class="svg" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="statistics-card statistics-card__subscribed-providers border flex-grow-1 flex-grow-1 d-flex gap-2 justify-content-between h-100 align-items-center">
                                        <div>
                                            <h2 title="{{with_currency_symbol(($adminAccount->balance_pending??0))}}">{{with_compact_currency_symbol(($adminAccount->balance_pending??0))}}</h2>
                                            <h3>{{translate('Pending_Balance')}}</h3>
                                        </div>
                                        <div class="absolute-img position-static align-self-start"  data-bs-toggle="tooltip" data-bs-title="{{translate('Pending balance means digitally placed booking amount which is yet to disperse')}}">
                                            <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg" class="svg" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="statistics-card statistics-card__canceled border flex-grow-1 flex-grow-1 d-flex gap-2 justify-content-between h-100 align-items-center">
                                        <div>
                                            <h2 title="{{with_currency_symbol($adminAccount->account_payable??0)}}">{{with_compact_currency_symbol($adminAccount->account_payable??0)}}</h2>
                                            <h3>{{translate('Account_Payable')}}</h3>
                                        </div>
                                        <div class="absolute-img position-static align-self-start"  data-bs-toggle="tooltip" data-bs-title="{{translate('Account payable means the booking amount that the admin has to pay to the providers')}}">
                                            <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg" class="svg" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="statistics-card statistics-card__purple border flex-grow-1 flex-grow-1 d-flex gap-2 justify-content-between h-100 align-items-center">
                                        <div>
                                            <h2 title="{{with_currency_symbol($adminAccount->account_receivable??0)}}">{{with_compact_currency_symbol($adminAccount->account_receivable??0)}}</h2>
                                            <h3>{{translate('Account_Receivable')}}</h3>
                                        </div>
                                        <div class="absolute-img position-static align-self-start"  data-bs-toggle="tooltip" data-bs-title="{{translate('Account receivable means the booking commission that the admin will get from the providers for Cash After the Services')}}">
                                            <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg" class="svg" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="mb-3 fz-16">{{translate('Search Data')}}</div>
                            <form action="{{route('admin.report.transaction', ['transaction_type'=>$queryParams['transaction_type']])}}" method="POST" class="filter-form">
                                @csrf
                                @if(!empty($queryParams['search']))
                                    <input type="hidden" name="search" value="{{ $queryParams['search'] }}">
                                @endif
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
                                            'selectClass' => 'js-select provider__select',
                                            'prependOptions' => [
                                                ['value' => 'all', 'label' => translate('Select All')]],
                                            'options'     => $providers->mapWithKeys(function ($p) {
                                                return [$p['id'] => $p['company_name'].' ('.$p['company_phone'].')'];
                                            })->toArray(),
                                            'value'       => $queryParams['provider_ids'] ?? [],
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6 d-none">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'filter_by',
                                            'id'          => 'filter-by',
                                            'label'       => translate('Type'),
                                            'selectClass' => 'js-select type__select',
                                            'options'     => [
                                                'all'          => translate('All'),
                                                'collect_cash' => translate('Collect Cash'),
                                                'withdraw'     => translate('Withdraw'),
                                                'payment'      => translate('Payment'),
                                                'commission'   => translate('Commission')],
                                            'value'       => $queryParams['filter_by'] ?? null,
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
                                        <a href="{{route('admin.report.transaction', ['transaction_type'=>$queryParams['transaction_type']])}}" class="btn btn--secondary btn-sm">{{translate('reset')}}</a>
                                        <button type="submit" class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                                <ul class="nav nav--tabs">
                                    <li class="nav-item">
                                        <a class="nav-link {{!isset($queryParams['transaction_type']) || $queryParams['transaction_type']=='all'?'active':''}}"
                                                href="{{ request()->fullUrlWithQuery(['transaction_type' => 'all', 'page' => null]) }}">{{translate('All')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{isset($queryParams['transaction_type']) && $queryParams['transaction_type']=='debit'?'active':''}}"
                                                href="{{ request()->fullUrlWithQuery(['transaction_type' => 'debit', 'page' => null]) }}">{{translate('Debit')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{isset($queryParams['transaction_type']) && $queryParams['transaction_type']=='credit'?'active':''}}"
                                                href="{{ request()->fullUrlWithQuery(['transaction_type' => 'credit', 'page' => null]) }}">{{translate('Credit')}}</a>
                                    </li>
                                </ul>

                                <div class="d-flex gap-2 fw-medium">
                                    <span class="opacity-75">{{translate('Total_Transactions')}}: </span>
                                    <span class="title-color">{{$filteredTransactions->total()}}</span>
                                </div>
                            </div>

                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}"
                                        class="search-form search-form_style-two"
                                        method="GET">
                                    @foreach(['transaction_type', 'filter_by', 'from', 'to'] as $field)
                                        @if(!empty($queryParams[$field]))
                                            <input type="hidden" name="{{ $field }}" value="{{ $queryParams[$field] }}">
                                        @endif
                                    @endforeach
                                    <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                                value="{{$queryParams['search']??''}}" name="search"
                                                placeholder="{{translate('search by transaction ID')}}">
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
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{route('admin.report.transaction.download').'?'.http_build_query($queryParams)}}">
                                                    {{translate('Excel')}}
                                                </a>
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
                                            <th>{{translate('Transaction_ID')}}</th>
                                            <th>{{translate('Transaction_Date')}}</th>
                                            <th>{{translate('Transaction_To')}}</th>
                                            <th>{{translate('Debit')}}</th>
                                            <th>{{translate('Credit')}}</th>
                                            <th>{{translate('Balance')}}</th>
                                            <th>{{translate('Transaction Type')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($filteredTransactions as $key=>$transaction)
                                            <tr>
                                                <td>{{$filteredTransactions->firstitem()+$key}}</td>
                                                <td>{{$transaction->id}}</td>
                                                <td>
                                                    <div>
                                                        <div>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-M-Y') }}</div>
                                                        <div>{{ format_time_by_business_settings($transaction->created_at) }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if(isset($transaction->to_user) && isset($transaction->to_user->provider))
                                                        <a href="{{route('admin.customer.detail',[$transaction->to_user->id, 'web_page'=>'overview'])}}">
                                                            {{$transaction->to_user->provider->company_name}}
                                                        </a>
                                                        <div class="d-flex fz-10">{{ $transaction->trx_type }}</div>
                                                    @elseif(isset($transaction->to_user))
                                                        <a href="{{route('admin.customer.detail',[$transaction->to_user->id, 'web_page'=>'overview'])}}">
                                                            {{$transaction->to_user->first_name.' '.$transaction->to_user->last_name}}
                                                        </a>
                                                        <div class="d-flex fz-10">{{ $transaction->trx_type }}</div>
                                                    @else
                                                        {{translate('User_Unavailable')}}
                                                    @endif
                                                </td>
                                                <td> -
                                                    @if($transaction->debit > 0)
                                                        <span>{{with_currency_symbol($transaction->debit)}}</span>
                                                    @else
                                                        <span class="disabled">{{with_currency_symbol($transaction->debit)}}</span>
                                                    @endif</td>
                                                <td>+
                                                    @if($transaction->credit > 0)
                                                        <span>{{with_currency_symbol($transaction->credit)}}</span>
                                                    @else
                                                        <span class="disabled">{{with_currency_symbol($transaction->credit)}}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($transaction->balance > 0)
                                                        <span>{{with_currency_symbol($transaction->balance)}}</span>
                                                    @else
                                                        <span class="disabled">{{with_currency_symbol($transaction->balance)}}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span>{{str_replace('_', ' ', $transaction->trx_type)}}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            @include('adminmodule::layouts.partials.components._empty-state', [
                                                'colspan' => 8,
                                                'variant' => (
                                                    filled($queryParams['search'] ?? null)
                                                    || !empty($queryParams['zone_ids'] ?? [])
                                                    || !empty($queryParams['provider_ids'] ?? [])
                                                    || (($queryParams['filter_by'] ?? 'all') !== 'all')
                                                    || (($queryParams['date_range'] ?? 'all_time') !== 'all_time')
                                                    || filled($queryParams['from'] ?? null)
                                                    || filled($queryParams['to'] ?? null)
                                                ) ? 'search' : 'list'])
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $filteredTransactions->links() !!}
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
        "use strict"

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

        $(document).ready(function () {
            $('.zone__select').select2({
                placeholder: "{{translate('Select_zone')}}",
            });
            $('.provider__select').select2({
                placeholder: "{{translate('Select_provider')}}",
            });
            $('.type__select').select2({
                placeholder: "{{translate('Select_Type')}}",
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
                    }
                }
            });
        });
    </script>
@endpush
