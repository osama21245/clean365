@extends('auth::layouts.master')

@section('title', translate('Reset Password'))

@section('content')
    @php($forgetPasswordVerificationMethod = (business_config('forget_password_verification_method', 'business_information'))->live_values ?? 'email')

    <div class="register-form dark-support"
         data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <form id="reset-password-send-otp-form"
                          action="{{ route('provider.auth.reset-password.send-otp') }}"
                          method="POST"
                          data-ff-validate novalidate>
                        @csrf
                        <div class="card p-4">
                            <h4 class="mb-30">{{ translate('Forgot Your Password') }}?</h4>
                            <h6 class="mb-2">
                                {{ translate('Change your password in three easy steps.') }}
                                {{ translate('This helps to keep your new password secure.') }}
                            </h6>
                            <ul>
                                <li>{{ translate('Fill in your account email or phone below') }}</li>
                                <li>{{ translate('We will send you a temporary code') }}</li>
                                <li>{{ translate('Use the code to change your password on our secure website') }}</li>
                            </ul>

                            <div class="row g-3">
                                <div class="col-lg-10">
                                    @if($forgetPasswordVerificationMethod == 'phone')
                                        <div class="ff-field ff-field--tel" data-ff-field>
                                            <label for="identity" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Phone Number') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="ff-field__control">
                                                <input type="tel"
                                                       name="identity"
                                                       id="identity"
                                                       value="{{ old('identity') }}"
                                                       class="form-control ff-field__input"
                                                       placeholder="{{ translate('Enter Your Phone Number') }}"
                                                       autocomplete="tel"
                                                       required>
                                            </div>
                                            <div class="ff-field__footer">
                                                <div class="ff-field__messages"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="identity_type" value="phone">
                                    @else
                                        <div class="ff-field ff-field--email ff-field--has-icon" data-ff-field>
                                            <label for="identity" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Email Address') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="ff-field__control">
                                                <span class="material-icons ff-field__icon">mail</span>
                                                <input type="email"
                                                       name="identity"
                                                       id="identity"
                                                       value="{{ old('identity') }}"
                                                       class="form-control ff-field__input"
                                                       placeholder="{{ translate('Enter Your Email Address') }}"
                                                       autocomplete="email"
                                                       maxlength="100"
                                                       required>
                                            </div>
                                            <div class="ff-field__footer">
                                                <div class="ff-field__messages"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="identity_type" value="email">
                                    @endif
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn--primary">{{ translate('Send OTP') }}</button>
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
            FormValidator.register('#reset-password-send-otp-form', {
                submitHandler: function (form) {
                    var $btn = $(form).find('button[type="submit"]');
                    if ($btn.prop('disabled')) return false;
                    if (!$btn.data('ffOriginalHtml')) $btn.data('ffOriginalHtml', $btn.html());
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        '{{ translate("Sending...") }}'
                    );
                    form.submit();
                }
            });
        })();
    </script>
@endpush
