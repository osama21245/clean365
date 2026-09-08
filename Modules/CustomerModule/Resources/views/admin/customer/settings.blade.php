@extends('adminmodule::layouts.master')

@section('title',translate('Customer Configuration'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Customer Settings')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=loyalty_point"
                                   class="nav-link {{$web_page=='loyalty_point'?'active':''}}">
                                    {{translate('Loyalty Point')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=wallet"
                                   class="nav-link {{$web_page=='wallet'?'active':''}}">
                                    {{translate('Wallet')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=referral_earning"
                                   class="nav-link {{$web_page=='referral_earning'?'active':''}}">
                                    {{translate('Referral Earning')}}
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if($web_page=='loyalty_point')
                        @php($loyaltyEnabled = $data_values->where('key_name','customer_loyalty_point')->first()?->live_values == '1')
                        @php($loyaltyPercent = $data_values->where('key_name','loyalty_point_percentage_per_booking')->first()?->live_values ?? '')
                        @php($loyaltyValue = $data_values->where('key_name','loyalty_point_value_per_currency_unit')->first()?->live_values ?? '')
                        @php($loyaltyMinTransfer = $data_values->where('key_name','min_loyalty_point_to_transfer')->first()?->live_values ?? '')
                        <div class="card">
                            <div class="card-body p-30">
                                <form id="customer-loyalty-settings-form"
                                      action="{{route('admin.customer.settings', ['web_page' => 'loyalty_point'])}}"
                                      method="POST"
                                      data-ff-validate novalidate>
                                    @csrf
                                    @method('PUT')
                                    <div class="bg-light rounded p-xxl-4 p-3">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <h4 class="mb-0">{{translate('Customer Loyalty Point')}}</h4>
                                            <label class="switcher">
                                                <input class="switcher_input" type="checkbox" value="1"
                                                       name="customer_loyalty_point"
                                                    {{$loyaltyEnabled ? 'checked' : ''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                @include('partials._form-field', [
                                                    'type'        => 'number',
                                                    'name'        => 'loyalty_point_percentage_per_booking',
                                                    'label'       => translate('Percentage of Loyalty Point per Booking Amount'),
                                                    'placeholder' => translate('Enter Percentage'),
                                                    'icon'        => 'percent',
                                                    'min'         => 0,
                                                    'max'         => 100,
                                                    'step'        => 'any',
                                                    'suffix'      => '%',
                                                    'hint'        => translate('On every booking this percent of amount will be added as loyalty point on customer account'),
                                                    'value'       => old('loyalty_point_percentage_per_booking', $loyaltyPercent),
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                            <div class="col-md-4">
                                                @include('partials._form-field', [
                                                    'type'        => 'number',
                                                    'name'        => 'loyalty_point_value_per_currency_unit',
                                                    'label'       => translate(':currency Equal to Loyalty Points', ['currency' => '1 '.currency_code()]),
                                                    'placeholder' => translate('Enter Loyalty Points'),
                                                    'icon'        => 'loyalty',
                                                    'min'         => 0,
                                                    'step'        => 'any',
                                                    'value'       => old('loyalty_point_value_per_currency_unit', $loyaltyValue),
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                            <div class="col-md-4">
                                                @include('partials._form-field', [
                                                    'type'        => 'number',
                                                    'name'        => 'min_loyalty_point_to_transfer',
                                                    'label'       => translate('Minimum Loyalty Points to Transfer into Wallet'),
                                                    'placeholder' => translate('Enter Minimum Points'),
                                                    'icon'        => 'account_balance_wallet',
                                                    'min'         => 0,
                                                    'step'        => 'any',
                                                    'value'       => old('min_loyalty_point_to_transfer', $loyaltyMinTransfer),
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                        <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn--primary demo_check">{{translate('Update')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($web_page=='wallet')
                        @php($walletEnabled = $data_values->where('key_name','customer_wallet')->first()?->live_values == '1')
                        <div class="card">
                            <div class="card-body p-30">
                                <form id="customer-wallet-settings-form"
                                      action="{{route('admin.customer.settings', ['web_page' => 'wallet'])}}"
                                      method="POST"
                                      data-ff-validate novalidate>
                                    @csrf
                                    @method('PUT')
                                    <div class="bg-light rounded p-xxl-4 p-3">
                                        <div class="d-flex align-items-center gap-3 mb-0">
                                            <h4 class="mb-0">{{translate('Customer Wallet')}}</h4>
                                            <label class="switcher">
                                                <input class="switcher_input" type="checkbox" value="1"
                                                       name="customer_wallet"
                                                    {{$walletEnabled ? 'checked' : ''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                        <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn--primary demo_check">{{translate('Update')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($web_page=='referral_earning')
                        @php($referralEnabled = $data_values->where('key_name','customer_referral_earning')->first()?->live_values == '1')
                        @php($referralValue = $data_values->where('key_name','referral_value_per_currency_unit')->first()?->live_values ?? '')
                        <div class="card">
                            <div class="card-body p-30">
                                <form id="customer-referral-settings-form"
                                      action="{{route('admin.customer.settings', ['web_page' => 'referral_earning'])}}"
                                      method="POST"
                                      data-ff-validate novalidate>
                                    @csrf
                                    @method('PUT')
                                    <div class="bg-light rounded p-xxl-4 p-3">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <h4 class="mb-0">{{translate('Customer Referral Earning')}}</h4>
                                            <label class="switcher">
                                                <input class="switcher_input" type="checkbox" value="1"
                                                       name="customer_referral_earning"
                                                    {{$referralEnabled ? 'checked' : ''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                @include('partials._form-field', [
                                                    'type'        => 'number',
                                                    'name'        => 'referral_value_per_currency_unit',
                                                    'label'       => translate('One Referrer Equal to How Much :currency?', ['currency' => currency_code()]),
                                                    'placeholder' => translate('Enter Amount'),
                                                    'icon'        => 'price_change',
                                                    'min'         => 0,
                                                    'step'        => 'any',
                                                    'value'       => old('referral_value_per_currency_unit', $referralValue),
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                        <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn--primary demo_check">{{translate('Update')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
