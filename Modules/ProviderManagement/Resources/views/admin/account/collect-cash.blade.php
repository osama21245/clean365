@extends('adminmodule::layouts.master')

@section('title',translate('Collect_Cash'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='overview'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'overview'])}}">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscribed_services'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'subscribed_services'])}}">{{translate('Subscribed_Services')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bookings'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'bookings'])}}">{{translate('Bookings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='serviceman_list'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'serviceman_list'])}}">{{translate('Service_Man_List')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='settings'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'settings'])}}">{{translate('Settings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bank_info'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'bank_information'])}}">{{translate('Bank_Information')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='reviews'?'active':''}}"
                           href="{{route('admin.provider.details',[$provider_id, 'web_page'=>'reviews'])}}">{{translate('Reviews')}}</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="overview-tab-pane">
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form id="admin-collect-cash-form"
                                  action="{{route('admin.provider.collect_cash.store')}}"
                                  method="POST"
                                  data-ff-validate novalidate>
                                @csrf
                                <input type="hidden" name="provider_id" value="{{$provider_id}}">
                                <div class="bg-light rounded p-xxl-4 p-3">
                                    <h4 class="mb-3">{{translate('Collect Cash')}}</h4>
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            @include('partials._form-field', [
                                                'type'        => 'number',
                                                'name'        => 'amount',
                                                'label'       => translate('Amount').' ('.currency_symbol().')',
                                                'placeholder' => translate('Enter Amount to Collect'),
                                                'icon'        => 'payments',
                                                'required'    => true,
                                                'min'         => 1,
                                                'step'        => 'any',
                                                'value'       => old('amount'),
                                                'wrapClass'   => 'mb-0'])
                                        </div>
                                    </div>
                                </div>

                                @can('provider_update')
                                    <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                        <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn--primary demo_check">{{translate('Submit')}}</button>
                                    </div>
                                @endcan
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}"
                                      class="search-form search-form_style-two"
                                      method="GET">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        {{translate('search')}}
                                    </button>
                                </form>

                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('Transaction_Id')}}</th>
                                        <th>{{translate('Transaction_Date')}}</th>
                                        <th>{{translate('Transaction_From')}}</th>
                                        <th>{{translate('Transaction_To')}}</th>
                                        <th>{{translate('Debit')}}</th>
                                        <th>{{translate('Credit')}}</th>
                                        <th>{{translate('Balance')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td>{{$transaction->id}}</td>
                                            <td>{{ format_time_by_business_settings($transaction->created_at, 'd-M-Y') }}</td>
                                            <td>
                                                @if($transaction?->from_user?->provider)
                                                    {{Str::limit($transaction->from_user->provider->company_name, 30)}}
                                                @else
                                                    {{Str::limit($transaction?->from_user?->first_name.' '.$transaction?->from_user?->last_name, 30)}}
                                                @endif
                                            </td>
                                            <td>{{Str::limit($transaction->to_user?$transaction->to_user->first_name.' '.$transaction->to_user->last_name:'', 30)}}</td>
                                            <td>{{with_currency_symbol($transaction->debit)}}</td>
                                            <td>{{with_currency_symbol($transaction->credit)}}</td>
                                            <td>{{with_currency_symbol($transaction->balance)}}</td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'variant' => filled($search ?? null) ? 'search' : 'list'])
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
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        (function () {
            var $form = $('#admin-collect-cash-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#admin-collect-cash-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '{{ translate("Submitting...") }}'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
@endpush
