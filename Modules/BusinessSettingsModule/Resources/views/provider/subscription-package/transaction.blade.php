@extends('providermanagement::layouts.master')

@section('title',translate('Subscription Package Details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('My Subscription')}}</h2>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs nav--tabs__style2 scrollbar-w flex-nowrap white-nowrap overflow-x-auto flex-wrap-nowrap">
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('provider/subscription-package/details') ? 'active' : ''}}" href="{{ route('provider.subscription-package.details') }}">{{translate('Package_Details')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('provider/subscription-package/transactions') ? 'active' : ''}}" href="{{ route('provider.subscription-package.transactions') }}">{{translate('Transactions')}}</a>
                    </li>
                </ul>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mb-3 title-color fz-16">{{translate('Filter_Option')}}</div>
                    <form
                            action="{{route('provider.subscription-package.transactions', ['transaction_type'=>request(['transaction_type'])])}}"
                            method="get" class="filter-form">
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 mb-30">
                                <label class="mb-2">{{translate('Date Range')}}</label>
                                <select class="js-select" id="date-range" name="date_range">
                                    <option value="0" disabled selected>{{translate('Select Date_Range')}}</option>
                                    <option value="all_time"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='all_time'?'selected':''}}>{{translate('All_Time')}}</option>
                                    <option value="this_week"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_week'?'selected':''}}>{{translate('This_Week')}}</option>
                                    <option value="this_month"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_month'?'selected':''}}>{{translate('This_Month')}}</option>
                                    <option value="this_year"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year'?'selected':''}}>{{translate('This_Year')}}</option>
                                    <option value="custom_date"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'selected':''}}>{{translate('Custom_Date')}}</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-sm-6 {{request('date_range')=='custom_date'?'':'d-none'}} align-self-end"
                                 id="from-filter__div">
                                <div class="form-floating mb-30">
                                    <input type="text" class="form-control" id="from" name="from"
                                           value="{{request('from')}}" placeholder="{{ translate('From') }}"
                                           data-ff-datepicker readonly autocomplete="off" inputmode="none">
                                    <label for="from">{{translate('From')}}</label>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 {{request('date_range')=='custom_date'?'':'d-none'}} align-self-end"
                                 id="to-filter__div">
                                <div class="form-floating mb-30">
                                    <input type="text" class="form-control" id="to" name="to"
                                           value="{{request('to')}}" placeholder="{{ translate('To') }}"
                                           data-ff-datepicker readonly autocomplete="off" inputmode="none">
                                    <label for="to">{{translate('To')}}</label>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ url()->current() }}" class="btn btn--secondary btn-sm">{{translate('Reset')}}</a>
                                <button type="submit"
                                        class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="mb-0 title-color">{{ translate('Transaction List') }}</h4>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                {{ $transactions->total() }}
                            </span>
                        </div>

                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-md-end">
                            <div class="dropdown">
                                <button type="button"
                                        class="btn btn--secondary text-capitalize dropdown-toggle h-100"
                                        data-bs-toggle="dropdown">
                                    <span class="material-icons">file_download</span> {{translate('download')}}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('provider.subscription-package.transactions.download') }}?search={{ $search }}">
                                            {{translate('Excel')}}
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <form action="{{ url()->current() }}"
                                  class="search-form search-form_style-two"
                                  method="GET">
                                <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                    <input type="search" class="theme-input-style search-form__input" name="search" value="{{ $search }}"
                                           placeholder="{{translate('search by transaction ID')}}">
                                </div>
                                <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-nowrap">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Transaction_ID')}}</th>
                                <th>{{translate('Transaction_Date')}}</th>
                                <th>{{translate('Pricing')}}</th>
                                <th>{{translate('Duration')}}</th>
                                <th>{{translate('Payment Status')}}</th>
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $key => $transaction)
                                    @php
                                        $start = \Carbon\Carbon::parse($transaction?->packageLog?->start_date)->subDay();
                                        $end = \Carbon\Carbon::parse($transaction?->packageLog?->end_date);
                                        $duration = $start->diffInDays($end);
                                    @endphp
                                    <tr>
                                        <td>{{$key+$transactions?->firstItem()}}</td>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ format_time_by_business_settings($transaction->created_at, 'M d, Y', ', ') }}</td>
                                        @if($transaction->trx_type == 'subscription_refund')
                                            <td>{{ with_currency_symbol($transaction->credit) }}</td>
                                        @else
                                            <td>{{ with_currency_symbol($transaction?->packageLog?->package_price) }}</td>
                                        @endif
                                        <td>{{ translate(':count :day', ['count' => (int) $duration, 'day' => translate(\Illuminate\Support\Str::plural('day', (int) $duration))]) }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if( $transaction->trx_type == 'subscription_purchase')
                                                    <div class="fs-12">{{translate('Subscribed')}}</div>
                                                @elseif( $transaction->trx_type == 'subscription_renew')
                                                    <div class="fs-12">{{translate('Renewal')}}</div>
                                                 @elseif( $transaction->trx_type == 'subscription_shift')
                                                    <div class="fs-12">{{translate('Migrate to new plan')}}</div>
                                                 @elseif( $transaction->trx_type == 'subscription_refund')
                                                    <div class="fs-12">{{translate('refunded')}}</div>
                                                @endif
                                                @if($transaction->trx_type != 'subscription_refund')
                                                    <div class="fs-10 c1">{{ translate('Paid By :method', ['method' => $transaction->packageLog?->payment?->payment_method]) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{route('provider.subscription-package.transactions.invoice',[$transaction->id])}}" target="_blank" class="action-btn btn--light-primary" style="--size: 30px">
                                                    <span class="material-icons">print</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    @include('adminmodule::layouts.partials.components._empty-state', [
                                        'colspan' => 7,
                                        'variant' => (
                                            filled($search ?? null)
                                            || (($queryParams['date_range'] ?? 'all_time') !== 'all_time')
                                            || filled($queryParams['from'] ?? null)
                                            || filled($queryParams['to'] ?? null)
                                        ) ? 'search' : 'list'])
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $transactions->links() !!}
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
