@extends('providermanagement::layouts.master')

@section('title',translate('Bank Info'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Provider Bank Information')}}</h2>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex gap-2 align-items-center mb-4">
                                <img width="20" src="{{asset('/public/assets/admin-module/img/icons/card.png')}}"
                                     alt="">
                                <h5 class="mb-0">{{translate('Account Details')}}</h5>
                                <span class="material-symbols-outlined" data-bs-toggle="tooltip"
                                      data-bs-placement="bottom" title="{{translate('Please update your account details with accurate information. This information will be used by the admin for processing withdrawal request transaction')}}">info</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-xl-5">
                                    <div class="provider-bank-card d-flex justify-content-between gap-3 p-4 border align-items-start flex-wrap">
                                        <div class="">
                                            <div class="d-flex info gap-2 align-items-center mb-4">
                                                <span class="material-icons">person</span>
                                                {{ translate('Holder Name: :name', ['name' => $provider->bank_detail->acc_holder_name ?? '']) }}
                                            </div>

                                            <div class="d-flex flex-column info gap-2">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('Bank Name')}}</span>:
                                                    <span>{{$provider->bank_detail->bank_name??''}}</span>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('Branch Name')}}</span>:
                                                    <span>{{$provider->bank_detail->branch_name??''}}</span>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('Account Name')}}</span>:
                                                    <span>{{$provider->bank_detail->acc_holder_name??''}}</span>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('Routing Number')}}</span>:
                                                    <span>{{$provider->bank_detail->routing_number??''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary d-flex gap-2"
                                                data-bs-toggle="modal" data-bs-target="#bankInfoModal"
                                                data-bs-whatever="@mdo">{{translate('Edit')}}
                                            <span class="material-symbols-outlined m-0">edit</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bankInfoModal" tabindex="-1" aria-labelledby="bankInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="bankInfoModalLabel">{{translate('General Information')}}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="provider-bank-info-form"
                          action="{{route('provider.update_bank_info')}}"
                          method="post"
                          enctype="multipart/form-data"
                          data-ff-validate novalidate>
                        @csrf
                        @method('put')
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
                            <div class="col-12">
                                @include('partials._form-field', [
                                    'type'        => 'text',
                                    'name'        => 'routing_number',
                                    'label'       => translate('Routing Number'),
                                    'placeholder' => translate('Enter Routing Number'),
                                    'icon'        => 'monitoring',
                                    'required'    => true,
                                    'maxlength'   => 40,
                                    'charCount'   => true,
                                    'value'       => old('routing_number', $provider->bank_detail->routing_number ?? ''),
                                    'wrapClass'   => 'mb-0'])
                            </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                            <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary demo_check">{{translate('Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        (function () {
            var $form = $('#provider-bank-info-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () {
                            window.FormCharCount.update(this);
                        });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#provider-bank-info-form', {
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
