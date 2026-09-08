@extends('auth::layouts.master')

@section('title', translate('Reset Password'))

@section('content')
    <div class="register-form dark-support"
         data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <form id="reset-password-change-form"
                          action="{{ route('provider.auth.reset-password.change-password') }}"
                          method="POST"
                          data-ff-validate novalidate>
                        @csrf
                        <div class="card p-4">
                            <h4 class="mb-30">{{ translate('Change Password') }}</h4>
                            <p class="mb-20">{{ translate('Create a new password for your account') }}</p>

                            <div class="row g-3">
                                <div class="col-lg-10">
                                    <div class="ff-field ff-field--password ff-field--has-icon" data-ff-field>
                                        <label for="password" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('New Password') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">lock</span>
                                            <input type="password"
                                                   name="password"
                                                   id="password"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Enter New Password') }}"
                                                   minlength="8"
                                                   autocomplete="new-password"
                                                   required>
                                            <span class="material-icons ff-field__toggle-password togglePassword"
                                                  data-ff-toggle-password="#password">visibility_off</span>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages">
                                                <small class="ff-field__hint">{{ translate('Minimum 8 characters') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-10">
                                    <div class="ff-field ff-field--password ff-field--has-icon" data-ff-field>
                                        <label for="confirm_password" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Confirm New Password') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">lock</span>
                                            <input type="password"
                                                   name="confirm_password"
                                                   id="confirm_password"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Re-Enter New Password') }}"
                                                   minlength="8"
                                                   autocomplete="new-password"
                                                   data-ff-match="#password"
                                                   required>
                                            <span class="material-icons ff-field__toggle-password togglePassword"
                                                  data-ff-toggle-password="#confirm_password">visibility_off</span>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn--primary">{{ translate('Change Password') }}</button>
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
            FormValidator.register('#reset-password-change-form', {
                rules: {
                    password: { minlength: 8 },
                    confirm_password: { minlength: 8, equalTo: '#password' }
                },
                submitHandler: function (form) {
                    var $btn = $(form).find('button[type="submit"]');
                    if ($btn.prop('disabled')) return false;
                    if (!$btn.data('ffOriginalHtml')) $btn.data('ffOriginalHtml', $btn.html());
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        '{{ translate("Saving...") }}'
                    );
                    form.submit();
                }
            });
        })();
    </script>
@endpush
