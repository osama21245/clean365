@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Bank_Information')}}</h2>
            </div>

            @include('providermanagement::admin.provider.detail._tabs')

            <div class="card">
                <div class="border-bottom d-flex gap-3 flex-wrap justify-content-between align-items-center px-4 py-3">
                    <div class="d-flex gap-2 align-items-center">
                        <span class="material-symbols-outlined">account_balance</span>

                        <h3>{{translate('Bank_Information')}}</h3>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                    </div>
                </div>
                <div class="card-body p-30">
                    <div class="row justify-content-center">
                        <div class="col-sm-10 col-md-10 col-lg-7 col-xl-6 col-xxl-5">
                            <div class="card bank-info-card bg-bottom bg-contain bg-img"
                                 style="background-image: url('{{asset('public/assets/admin-module')}}/img/media/bank-info-card-bg.png');">
                                <div class="border-bottom p-3">
                                    <h4 class="fw-semibold">{{ translate('Holder Name: :name', ['name' => Str::limit($provider->bank_detail->acc_holder_name ?? translate('Unavailable'), 50)]) }}
                                    </h4>
                                </div>
                                <div class="card-body position-relative flex-wrap d-flex align-items-start justify-content-between gap-1">
                                    <ul class="list-unstyled d-flex flex-column gap-4">
                                        <li>
                                            <h3 class="mb-2">{{translate('Bank_Name')}}:</h3>
                                            <div>{{ $provider->bank_detail->bank_name ?? translate('Unavailable') }}</div>
                                        </li>
                                        <li>
                                            <h3 class="mb-2">{{translate('Branch_Name')}}:</h3>
                                            <div>{{ $provider->bank_detail->branch_name ?? translate('Unavailable') }}</div>
                                        </li>
                                        <li>
                                            <h3 class="mb-2">{{translate('Account_Number')}}:</h3>
                                            <div>{{ $provider->bank_detail->acc_no ?? translate('Unavailable') }}</div>
                                        </li>
                                    </ul>
                                    <img width="78" height="53" class="bank-card-img position-static"
                                         src="{{asset('public/assets/admin-module')}}/img/media/bank-card.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateBankInfo" tabindex="-1" aria-labelledby="updateBankInfoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="admin-provider-bank-info-form"
                      action="{{route('admin.provider.account.update',[$provider->id])}}"
                      method="post"
                      data-ff-validate novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateBankInfoLabel">{{translate('Update Account Information')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="bg-light rounded p-xxl-4 p-3">
                            <h4 class="mb-3">{{translate('Account Details')}}</h4>
                            <div class="row g-3">
                            <div class="col-12">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'bank_name',
                                    'label'       => translate('Bank Name'),
                                    'placeholder' => translate('Enter Bank Name'),
                                    'icon'        => 'account_balance',
                                    'required'    => true,
                                    'maxlength'   => 120,
                                    'charCount'   => true,
                                    'value'       => old('bank_name', $provider->bank_detail->bank_name ?? ''),
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-12">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'branch_name',
                                    'label'       => translate('Branch Name'),
                                    'placeholder' => translate('Enter Branch Name'),
                                    'icon'        => 'store',
                                    'required'    => true,
                                    'maxlength'   => 120,
                                    'charCount'   => true,
                                    'value'       => old('branch_name', $provider->bank_detail->branch_name ?? ''),
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-12">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'acc_no',
                                    'label'       => translate('Account Number'),
                                    'placeholder' => translate('Enter Account Number'),
                                    'icon'        => 'pin',
                                    'required'    => true,
                                    'maxlength'   => 40,
                                    'charCount'   => true,
                                    'value'       => old('acc_no', $provider->bank_detail->acc_no ?? ''),
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            <div class="col-12">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'acc_holder_name',
                                    'label'       => translate('Account Holder Name'),
                                    'placeholder' => translate('Enter Account Holder Name'),
                                    'icon'        => 'account_circle',
                                    'required'    => true,
                                    'maxlength'   => 120,
                                    'charCount'   => true,
                                    'value'       => old('acc_holder_name', $provider->bank_detail->acc_holder_name ?? ''),
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            </div>
                        </div>
                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                            <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary demo_check">{{translate('Submit')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        (function () {
            var $form = $('#admin-provider-bank-info-form');
            if (!$form.length) return;

            if (window.FormValidator) {
                FormValidator.register('#admin-provider-bank-info-form', {
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
