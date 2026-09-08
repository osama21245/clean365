@extends('adminmodule::layouts.master')

@section('title',translate('Loyalty_Point_Transaction_Report'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Loyalty_Point_Report')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 fz-16 fw-bold text-dark">{{translate('Filter Data')}}</div>

                            <form
                                action="{{route('admin.customer.loyalty-point.report', ['transaction_type'=>$queryParams['transaction_type']])}}"
                                method="POST" class="filter-form">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'zone_ids[]',
                                            'id'          => 'zone_selector__select',
                                            'label'       => translate('Zones'),
                                            'multiple'    => true,
                                            'selectClass' => 'js-select zone__select',
                                            'prependOptions' => [
                                                ['value' => 'all', 'label' => translate('Select All')]],
                                            'options'     => collect($zones)->pluck('name', 'id')->toArray(),
                                            'value'       => $queryParams['zone_ids'] ?? [],
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        @include('partials._form-field', [
                                            'type'        => 'select',
                                            'name'        => 'customer_ids[]',
                                            'id'          => 'customer_selector__select',
                                            'label'       => translate('Customers'),
                                            'multiple'    => true,
                                            'selectClass' => 'js-select customer__select',
                                            'prependOptions' => [
                                                ['value' => 'all', 'label' => translate('Select All')]],
                                            'options'     => collect($customers)->mapWithKeys(function ($c) {
                                                return [$c['id'] => $c['first_name'].' '.$c['last_name'].' ('.$c['phone'].')'];
                                            })->toArray(),
                                            'value'       => $queryParams['customer_ids'] ?? [],
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
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}}" id="from-filter__div">
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
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}}" id="to-filter__div">
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
                                </div>
                                <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                    <a href="{{ url()->current() }}" class="btn btn--secondary">{{translate('Reset')}}</a>
                                    <button type="submit" class="btn btn--primary">{{translate('Filter')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-sm-row flex-wrap gap-3 mb-4">
                                <div class="statistics-card statistics-card__total-orders border flex-grow-1">
                                    <h2>{{$totalDebit}}</h2>
                                    <h3>{{translate('Debit')}}</h3>
                                    <div class="absolute-img" data-bs-toggle="tooltip"
                                         data-bs-title="{{translate('Total spent points')}}">
                                        <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg"
                                             class="svg" alt="">
                                    </div>
                                </div>

                                <div class="statistics-card statistics-card__ongoing border flex-grow-1">
                                    <h2>{{$totalCredit}}</h2>
                                    <h3>{{translate('Credit')}}</h3>
                                    <div class="absolute-img" data-bs-toggle="tooltip"
                                         data-bs-title="{{translate('Total earned points')}}">
                                        <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg"
                                             class="svg" alt="">
                                    </div>
                                </div>

                                <div class="statistics-card statistics-card__subscribed-providers border flex-grow-1">
                                    <h2>{{($totalCredit - $totalDebit)}}</h2>
                                    <h3>{{translate('Balance')}}</h3>
                                    <div class="absolute-img" data-bs-toggle="tooltip"
                                         data-bs-title="{{translate('Available points')}}">
                                        <img src="{{asset('public/assets/admin-module')}}/img/icons/info.svg"
                                             class="svg" alt="">
                                    </div>
                                </div>
                            </div>

                            <div
                                class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                                <ul class="nav nav--tabs">
                                    <li class="nav-item">
                                        <a class="nav-link {{!isset($transactionType) || $transactionType=='all'?'active':''}}"
                                           href="{{url()->current()}}?transaction_type=all">{{translate('All')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{isset($transactionType) && $transactionType=='debit'?'active':''}}"
                                           href="{{url()->current()}}?transaction_type=debit">{{translate('Debit')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{isset($transactionType) && $transactionType=='credit'?'active':''}}"
                                           href="{{url()->current()}}?transaction_type=credit">{{translate('Credit')}}</a>
                                    </li>
                                </ul>

                                <div class="d-flex gap-2 fw-medium">
                                    <span class="opacity-75">{{translate('Total_Transactions')}}: </span>
                                    <span class="title-color">{{$filteredTransactions->total()}}</span>
                                </div>
                            </div>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="all-tab-pane">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="{{url()->current()}}"
                                              class="search-form search-form_style-two"
                                              method="GET">
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$queryParams['search']??''}}" name="search"
                                                       placeholder="{{translate('search by customer info')}}">
                                            </div>

                                            <input type="hidden" name="transaction_type"
                                                   value="{{ request('transaction_type') }}">

                                            <button type="submit"
                                                    class="btn btn--primary">{{translate('search')}}</button>
                                        </form>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            @can('point_export')
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                        <span
                                                            class="material-icons">file_download</span> {{translate('download')}}
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <li>
                                                            <a class="dropdown-item"
                                                               href="{{route('admin.customer.loyalty-point.report.download').'?'.http_build_query($queryParams)}}">
                                                                {{translate('Excel')}}
                                                            </a>
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
                                                <th>{{translate('Transaction_ID')}}</th>
                                                <th>{{translate('Customer')}}</th>
                                                <th>{{translate('Transaction_Date')}}</th>
                                                <th>{{translate('Debit')}}</th>
                                                <th>{{translate('Credit')}}</th>
                                                <th>{{translate('Balance')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($filteredTransactions as $key=>$transaction)
                                                <tr>
                                                    <td>{{$filteredTransactions->firstitem()+$key}}</td>
                                                    <td>{{$transaction->id}}</td>
                                                    <td>
                                                        @if(isset($transaction->user))
                                                            <a href="{{route('admin.customer.detail',[$transaction->user->id, 'web_page'=>'overview'])}}">
                                                                {{$transaction->user->first_name.' '.$transaction->user->last_name}}
                                                            </a>
                                                        @else
                                                            <span
                                                                class="badge badge-pill badge-danger">{{translate('User_available')}}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ format_time_by_business_settings($transaction->created_at, 'd-M-Y') }}</td>

                                                    <td>
                                                        <div class="d-flex align-items-center gap-1">
                                                            -
                                                            @if($transaction->debit > 0)
                                                                <span>{{with_currency_symbol($transaction->debit)}}</span>
                                                            @else
                                                                <span
                                                                    class="disabled">{{with_currency_symbol($transaction->debit)}}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-1">
                                                            +
                                                            @if($transaction->credit > 0)
                                                                <span>{{with_currency_symbol($transaction->credit)}}</span>
                                                            @else
                                                                <span
                                                                    class="disabled">{{with_currency_symbol($transaction->credit)}}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($transaction->balance > 0)
                                                            <span>{{with_currency_symbol($transaction->balance)}}</span>
                                                        @else
                                                            <span
                                                                class="disabled">{{with_currency_symbol($transaction->balance)}}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                @include('adminmodule::layouts.partials.components._empty-state', [
                                                    'colspan' => 7,
                                                    'variant' => (
                                                        filled($queryParams['search'] ?? null)
                                                        || !empty($queryParams['zone_ids'] ?? [])
                                                        || !empty($queryParams['customer_ids'] ?? [])
                                                        || (($queryParams['transaction_type'] ?? 'all') !== 'all')
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

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#customer_selector__select').on('change', function () {
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
            $('.customer__select').select2({
                placeholder: "{{translate('Select_Customer')}}",
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

                //hide 'from' & 'to' div
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
