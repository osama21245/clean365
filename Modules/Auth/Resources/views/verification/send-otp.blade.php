@extends('auth::layouts.master')

@section('title', translate('Account Verification'))

@section('content')
    @php
        $emailVerification = (int) login_setup('email_verification')?->value ?? 0;
        $phoneVerification = (int) login_setup('phone_verification')?->value ?? 0;
        $isUserVerified = $user?->is_phone_verified || $user?->is_email_verified;
        $firebaseOtpConfig = business_config('firebase_otp_verification', 'third_party');
        $firebaseOtpStatus = (int) $firebaseOtpConfig?->live_values['status'] ?? null;
    @endphp

    <div class="register-form dark-support"
         data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <form id="verification-send-otp-form"
                          action="{{ route('provider.auth.verification.send-otp') }}"
                          method="POST"
                          data-ff-validate novalidate>
                        @csrf
                        <div class="card p-4">
                            <h4 class="mb-30">{{ translate('Verify Your Account') }}</h4>
                            <h6 class="mb-2">{{ translate('Verify your account in two easy steps') }}</h6>
                            <ul>
                                <li>{{ translate('Fill in your account email or phone below') }}</li>
                                <li>{{ translate('We will send you a temporary code') }}</li>
                                <li>{{ translate('Use the code to verify your account on our secure website') }}</li>
                            </ul>

                            <div class="row g-3">
                                @if($phoneVerification && !$isUserVerified)
                                    <div class="col-lg-10">
                                        <div class="ff-field ff-field--tel" data-ff-field>
                                            <label for="identity" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Phone Number') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="ff-field__control">
                                                <input type="tel"
                                                       name="identity"
                                                       id="identity"
                                                       value="{{ $user?->phone }}"
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
                                    </div>
                                    @if($firebaseOtpConfig && $firebaseOtpStatus)
                                        <div class="col-lg-10">
                                            <div id="recaptcha-container-provider-registration" class="my-2"></div>
                                        </div>
                                    @endif
                                @elseif($emailVerification && !$isUserVerified)
                                    <div class="col-lg-10">
                                        <div class="ff-field ff-field--email ff-field--has-icon" data-ff-field>
                                            <label for="identity" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Email Address') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="ff-field__control">
                                                <span class="material-icons ff-field__icon">mail</span>
                                                <input type="email"
                                                       name="identity"
                                                       id="identity"
                                                       value="{{ $user?->email }}"
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
                                    </div>
                                @endif

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
    @include('auth::_firebase-script')
    <script>
        "use strict";
        (function () {
            if (!window.FormValidator) return;
            FormValidator.register('#verification-send-otp-form', {
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
