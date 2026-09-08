@extends('auth::layouts.master')

@section('title', translate('Account Verification'))

@section('content')
    <div class="register-form dark-support"
         data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <form id="verification-verify-otp-form"
                          action="{{ route('provider.auth.verification.verify-otp') }}"
                          method="POST"
                          data-ff-validate novalidate>
                        @csrf
                        <div class="card p-4">
                            <h4 class="mb-30">{{ translate('Verify OTP') }}</h4>
                            <p class="mb-20">{{ translate('Enter the temporary code we sent to confirm your account') }}</p>

                            <div class="row g-3">
                                <div class="col-lg-10">
                                    <div class="ff-field ff-field--text ff-field--has-icon" data-ff-field>
                                        <label for="otp" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('One-Time Code') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">pin</span>
                                            <input type="text"
                                                   name="otp"
                                                   id="otp"
                                                   value="{{ old('otp') }}"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Enter The Code') }}"
                                                   inputmode="numeric"
                                                   autocomplete="one-time-code"
                                                   maxlength="10"
                                                   required>
                                            <input type="hidden" name="identity" value="{{ session('identity') }}">
                                            <input type="hidden" name="identity_type" value="{{ session('identity_type') }}">
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn--primary">{{ translate('Verify OTP') }}</button>
                                </div>
                            </div>
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
            if (!window.FormValidator) return;
            FormValidator.register('#verification-verify-otp-form', {
                submitHandler: function (form) {
                    var $btn = $(form).find('button[type="submit"]');
                    if ($btn.prop('disabled')) return false;
                    if (!$btn.data('ffOriginalHtml')) $btn.data('ffOriginalHtml', $btn.html());
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        '{{ translate("Verifying...") }}'
                    );
                    form.submit();
                }
            });
        })();
    </script>
@endpush
