@extends('providermanagement::layouts.master')

@section('title',translate('Transaction_Reports'))

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
                            <div class="mb-3 fw-bold text-dark fz-16">{{translate('Search Data')}}</div>
                            <form
                                action="{{route('provider.report.transaction', ['transaction_type'=>$queryParams['transaction_type']])}}"
                                method="POST"
                                class="filter-form">
                                @csrf
                                <div class="row g-3">
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

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="tabs-slide-wrap position-relative">
                                <div class="tabs-inner d-flex gap-3 flex-nowrap text-nowrap">
                                    <div class="statistics-card statistics-card__total-orders border tabs-slide_items">
                                        @php($providerBalance = $account_info->received_balance + $account_info->total_withdrawn)
                                        <h2 title="{{with_currency_symbol($providerBalance)}}">{{with_compact_currency_symbol($providerBalance)}}</h2>
                                        <h3>{{translate('provider_Balance')}}</h3>
                                        <div class="absolute-img" data-bs-toggle="tooltip"
                                             data-bs-title="{{translate('provider balance means total Earning of booking')}}">
                                            <img src="{{asset('public/assets/provider-module')}}/img/icons/info.svg"
                                                 class="svg" alt="">
                                        </div>
                                    </div>

                                    <div class="statistics-card border tabs-slide_items">
                                        <h2 title="{{with_currency_symbol(($account_info->balance_pending??0))}}">{{with_compact_currency_symbol(($account_info->balance_pending??0))}}</h2>
                                        <h3>{{translate('Pending_Balance')}}</h3>
                                        <div class="absolute-img" data-bs-toggle="tooltip"
                                             data-bs-title="{{translate('Pending balance means the amount requested for withdraw to admin')}}">
                                            <img src="{{asset('public/assets/provider-module')}}/img/icons/info.svg"
                                                 class="svg" alt="">
                                        </div>
                                    </div>

                                    <div class="statistics-card statistics-card__subscribed-providers border tabs-slide_items">
                                        <h2 title="{{with_currency_symbol($account_info->total_withdrawn)}}">{{with_compact_currency_symbol($account_info->total_withdrawn)}}</h2>
                                        {{-- <h3>{{translate('Already_withdrawn')}}</h3> --}}
                                        <h3>{{translate('Commission_Given')}}</h3>
                                        <div class="absolute-img" data-bs-toggle="tooltip"
                                             data-bs-title="{{translate('Total withdrawn means the amount provider has already withdrawn from admin which was got from digitally paid booking')}}">
                                            <img src="{{asset('public/assets/provider-module')}}/img/icons/info.svg"
                                                 class="svg" alt="">
                                        </div>
                                    </div>

                                    <div class="statistics-card statistics-card__canceled border tabs-slide_items">
                                        <h2 title="{{with_currency_symbol($account_info->account_payable??0)}}">{{with_compact_currency_symbol($account_info->account_payable??0)}}</h2>
                                        <h3>{{translate('Account_Payable')}}</h3>
                                        <div class="absolute-img" data-bs-toggle="tooltip"
                                             data-bs-title="{{translate('Account payable means the admin commission for CAS bookings that is yet to pay')}}">
                                            <img src="{{asset('public/assets/provider-module')}}/img/icons/info.svg"
                                                 class="svg" alt="">
                                        </div>
                                    </div>

                                    <div class="statistics-card statistics-card__ongoing border tabs-slide_items">
                                        <h2 title="{{with_currency_symbol($account_info->account_receivable??0)}}">{{with_compact_currency_symbol($account_info->account_receivable??0)}}</h2>
                                        <h3>{{translate('Account_Receivable')}}</h3>
                                        <div class="absolute-img" data-bs-toggle="tooltip"
                                             data-bs-title="{{translate('Account receivable means booking earning by digitally paid bookings that is yet to collect from admin')}}">
                                            <img src="{{asset('public/assets/provider-module')}}/img/icons/info.svg"
                                                 class="svg" alt="">
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

                            <div
                                class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3 mt-4">
                                <ul class="nav nav--tabs">
                                    <li class="nav-item">
                                        <a class="nav-link {{!isset($queryParams['transaction_type']) || $queryParams['transaction_type']=='all'?'active':''}}"
                                           href="{{url()->current()}}?transaction_type=all">{{translate('All')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{isset($queryParams['transaction_type']) && $queryParams['transaction_type']=='debit'?'active':''}}"
                                           href="{{url()->current()}}?transaction_type=debit">{{translate('Debit')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{isset($queryParams['transaction_type']) && $queryParams['transaction_type']=='credit'?'active':''}}"
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
                                                       value="{{$queryParams['search']?? ''}}" name="search"
                                                       placeholder="{{translate('search by transaction ID')}}">
                                            </div>
                                            <button type="submit"
                                                    class="btn btn--primary">{{translate('search')}}</button>
                                        </form>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
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
                                                           href="{{route('provider.report.transaction.download').'?'.http_build_query($queryParams)}}">
                                                            {{translate('Excel')}}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
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
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($filteredTransactions as $key=>$transaction)
                                                @php($isAdminTransaction = in_array($transaction?->to_user?->user_type, ADMIN_USER_TYPES ?? []))
                                                <tr>
                                                    <td>{{$filteredTransactions->firstitem()+$key}}</td>
                                                    <td>{{$transaction->id}}</td>
                                                    <td>{{ format_time_by_business_settings($transaction->created_at, 'd-M-Y') }}</td>
                                                    <td>
                                                        @if(isset($transaction->to_user))
                                                            {{ isset($transaction->to_user->provider) ? $transaction->to_user->provider->company_name :  $transaction->to_user->first_name.' '.$transaction->to_user->last_name }}
                                                            <div
                                                                class="d-flex fz-10">{{ ucwords(str_replace('_', ' ', $transaction->trx_type)) }}</div>
                                                        @else
                                                            {{translate('User_available')}}
                                                        @endif
                                                    </td>
                                                    <td> -
                                                        @if($transaction->debit > 0)
                                                            <span>{{with_currency_symbol($transaction->debit)}}</span>
                                                        @else
                                                            <span
                                                                class="disabled">{{with_currency_symbol($transaction->debit)}}</span>
                                                        @endif</td>
                                                    <td>+
                                                        @if($transaction->credit > 0)
                                                            <span>{{with_currency_symbol($transaction->credit)}}</span>
                                                        @else
                                                            <span
                                                                class="disabled">{{with_currency_symbol($transaction->credit)}}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($isAdminTransaction)
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                                                {{ translate('Hidden') }}
                                                            </span>
                                                        @elseif($transaction->balance > 0)
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

        $(document).ready(function () {
            $('.zone__select').select2({
                placeholder: "{{translate('Select_zone')}}",
            });
            $('.provider__select').select2({
                placeholder: "{{translate('Select_provider')}}",
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
