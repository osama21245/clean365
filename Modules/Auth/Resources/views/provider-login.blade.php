<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ translate('Provider Login') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="robots" content="nofollow, noindex ">
    @php($favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/admin-module/img/placeholder.png'))
    <link rel="shortcut icon" href="{{ $favIcon }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet" />

    <link href="{{asset('public/assets/provider-module')}}/css/material-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/bootstrap.min.css" />
    <link rel="stylesheet"
        href="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css" />

    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/style.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-brand.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-ui.css" />
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/toastr.css">
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/form-unified.css" />
    <style>
        :root {
            --c1: #006666 !important;
            --c1-rgb: 0, 102, 102 !important;
            --primary: #006666 !important;
            --primary-rgb: 0, 102, 102 !important;
            --bs-primary: #006666 !important;
            --bs-primary-rgb: 0, 102, 102 !important;
            --title-color: #006666 !important;
            --main-color: #006666 !important;
            --theme-color: #006666 !important;
            --brand-color: #006666 !important;
        }

        .login-left,
        .login-wrap .login-left {
            background-color: #006666 !important;
            background: linear-gradient(135deg, #006666 0%, #004d4d 100%) !important;
        }

        .btn-primary,
        .btn--primary,
        #signInBtn,
        .login-wrap button[type="submit"] {
            background-color: #006666 !important;
            background: #006666 !important;
            border-color: #006666 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 102, 102, 0.35) !important;
        }

        #signInBtn:hover,
        .login-wrap button[type="submit"]:hover {
            background-color: #004d4d !important;
            background: #004d4d !important;
            border-color: #004d4d !important;
        }

        .c1,
        .text-primary,
        .login-wrap .c1 {
            color: #006666 !important;
        }
    </style>
</head>

<body class="c365-admin-login">
    <div class="preloader"></div>
    <?php
$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/admin-module/img/placeholder.png');
?>
    <div>
        <form action="{{ route('provider.auth.login') }}" method="POST" id="login-form" data-ff-validate novalidate>
            @csrf
            <div class="login-wrap">
                <div class="login-left d-flex justify-content-center align-items-center">
                    <div
                        class="tf-box d-flex flex-column gap-3 align-items-center justify-content-center p-5 mx-4 mx-sm-5 h-75 text-center">
                        <img class="login-logo mb-2" src="{{ $logo }}" alt="{{ translate('Logo') }}">
                        <h2 class="fw-bold mb-0">
                            {{ translate('Integrated cleaning services') }}
                        </h2>
                        <p class="mb-0 opacity-75 px-3">
                            <strong class="c1">{{ translate('Clean 365') }}</strong>
                            — {{ translate('Supervisor operations dashboard for field teams') }}
                        </p>
                    </div>
                </div>
                <div class="login-right-wrap bg-white">
                    <div class="login-right w-100 m-auto p-3">

                        <div class="d-flex justify-content-between align-items-start gap-2 mb-5 mt-3">
                            <div class="d-flex flex-column gap-2">
                                <h2 class="c1 fw-medium">{{ translate('Supervisor Sign In') }}</h2>
                                <p>{{ translate('Sign in to stay connected') }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="mb-4">
                                <div class="ff-field ff-field--email ff-field--has-icon" data-ff-field>
                                    <label for="email" class="ff-field__label">
                                        <span class="ff-field__label-text">{{ translate('Email Address') }}<span
                                                class="ff-field__required">*</span></span>
                                    </label>
                                    <div class="ff-field__control">
                                        <span class="material-icons ff-field__icon">mail</span>
                                        <input type="email" name="email_or_phone" id="email"
                                            value="{{ request()->cookie('provider_remember_email') }}"
                                            class="form-control ff-field__input"
                                            placeholder="{{ translate('ex: provider@provider.com') }}"
                                            autocomplete="email" maxlength="100" required>
                                    </div>
                                    <div class="ff-field__footer">
                                        <div class="ff-field__messages"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="ff-field ff-field--password ff-field--has-icon" data-ff-field>
                                    <label for="password" class="ff-field__label">
                                        <span class="ff-field__label-text">{{ translate('Password') }}<span
                                                class="ff-field__required">*</span></span>
                                    </label>
                                    <div class="ff-field__control">
                                        <span class="material-icons ff-field__icon">lock</span>
                                        <input type="password" name="password" id="password"
                                            class="form-control ff-field__input"
                                            placeholder="{{ translate('Enter Password') }}"
                                            autocomplete="current-password" required>
                                        <span class="material-icons ff-field__toggle-password togglePassword"
                                            data-ff-toggle-password="#password">visibility_off</span>
                                    </div>
                                    <div class="ff-field__footer">
                                        <div class="ff-field__messages"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="d-flex gap-1 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember_me" value="1"
                                            id="rememberMeCheckbox" {{ request()->cookie('provider_remember_checked') ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="rememberMeCheckbox">{{ translate('Remember Me') }}?</label>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 align-items-center">
                                    <a href="{{ route('provider.auth.reset-password.index') }}"
                                        class="lh-1">{{ translate('Forgot Password') }}?</a>
                                </div>
                            </div>
                        </div>

                        <div class="recaptcha d-flex justify-content-center mb-3 dark-support">
                            @php($recaptcha = business_config('recaptcha', 'third_party'))
                            @if(isset($recaptcha) && $recaptcha->is_active)
                                <div class="recaptcha d-flex justify-content-center mb-4">
                                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                                </div>
                            @endif
                        </div>

                        <div class="d-flex mb-4">
                            <button class="btn flex-grow-1 btn--primary text-capitalize" id="signInBtn"
                                type="submit">{{ translate('Sign In') }}</button>
                        </div>

                        @if(business_config('provider_self_registration', 'provider_config')->live_values ?? 0)
                            <div class="text-center fz-12 pb-4">
                                {{ translate('Want to join as a provider') }}?
                                <a href="{{ route('provider.auth.sign-up') }}"
                                    class="c1 text-decoration-underline">{{ translate('Register Here') }}</a>
                            </div>
                        @endif
                    </div>

                    @if(env('APP_ENV') == 'demo')
                        <div class="login-footer d-flex justify-content-between c1-bg text-light gap-3">
                            <button type="button" class="btn login-copy">
                                <span class="material-symbols-outlined m-0">content_copy</span>
                            </button>
                            <div class="flex-grow-1">
                                <div>{{ translate('Email :value', ['value' => 'provider@provider.com']) }}</div>
                                <div>{{ translate('Password :value', ['value' => '12345678']) }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <script src="{{asset('public/assets/provider-module')}}/js/jquery-3.6.0.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/bootstrap.bundle.min.js"></script>
    <script
        src="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/main.js"></script>


    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script src="{{asset('public/assets/provider-module/js/sweet_alert.js')}}"></script>
    <script src="{{asset('public/assets/provider-module/js/toastr.js')}}"></script>

    <script src="{{asset('public/assets/common/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
    <script src="{{asset('public/assets/common/js/form-validator.js')}}"></script>
    <script src="{{asset('public/assets/common/js/form-character-count.js')}}"></script>
    {!! Toastr::message() !!}

    @php($recaptcha = business_config('recaptcha', 'third_party'))
    @if(isset($recaptcha) && $recaptcha->is_active)
        <script src="https://www.google.com/recaptcha/api.js?render={{$recaptcha->live_values['site_key']}}"></script>
        <script>
            "use strict";
            $('#signInBtn').click(function (e) {
                e.preventDefault();

                var $form = $('#login-form');
                var validator = $form.data('validator');
                if (validator) {
                    if (!$form.valid()) { return; }
                } else {
                    var hasEmpty = false;
                    $form.find('[required]').each(function () {
                        if (!this.value || !this.value.trim()) { hasEmpty = true; return false; }
                    });
                    if (hasEmpty) {
                        toastr.error('{{ translate("Please fill in all required fields") }}');
                        return;
                    }
                }

                if (typeof grecaptcha === 'undefined') {
                    toastr.error('Invalid recaptcha key provided. Please check the recaptcha configuration.');
                    return;
                }

                grecaptcha.ready(function () {
                    grecaptcha.execute('{{$recaptcha->live_values['site_key']}}', { action: 'submit' }).then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        document.querySelector('form').submit();
                    });
                });

                window.onerror = function (message) {
                    var errorMessage = 'An unexpected error occurred.';
                    if (message.includes('Invalid site key')) {
                        errorMessage = 'Invalid site key provided. Please check the site key configuration.';
                    } else if (message.includes('not loaded in api.js')) {
                        errorMessage = 'reCAPTCHA API could not be loaded. Please check the API configuration.';
                    }
                    toastr.error(errorMessage)
                    return true;
                };
            });
        </script>
    @endif

    <script>
        "use strict";

        (function () {
            if (window.FormValidator) {
                FormValidator.register('#login-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) $btn.data('ffOriginalHtml', $btn.html());
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '{{ translate("Signing in...") }}'
                        );
                        form.submit();
                    }
                });
            }

            $('#login-form').on('submit', function (e) {
                var $form = $(this);
                var validator = $form.data('validator');
                if (validator) {
                    if (!$form.valid()) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        return false;
                    }
                    return;
                }
                var hasEmpty = false;
                $form.find('[required]').each(function () {
                    if (!this.value || !this.value.trim()) { hasEmpty = true; return false; }
                });
                if (hasEmpty) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    toastr.error('{{ translate("Please fill in all required fields") }}');
                    return false;
                }
            });
        })();

        @if(env('APP_ENV') == 'demo')
            $('.login-copy').on('click', function () {
                copy_cred()
            })

            function copy_cred() {
                $('#email').val('provider@provider.com');
                $('#password').val('12345678');
                if (window.FormCharCount) {
                    window.FormCharCount.update(document.getElementById('email'));
                }
                toastr.success('{{ translate("Copied successfully") }}', 'Success', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        @endif

        @php($recaptcha = business_config('recaptcha', 'third_party'))
        @if(isset($recaptcha) && $recaptcha->is_active)
            var onloadCallback = function () {
                grecaptcha.render('recaptcha_element', {
                    'sitekey': '{{$recaptcha->live_values['site_key']}}'
                });
            };

            $("#login-form").on('submit', function (e) {
                var response = grecaptcha.getResponse();

                if (response.length === 0) {
                    e.preventDefault();
                    toastr.error("{{ translate('Please complete the reCAPTCHA') }}");
                }
            });
        @endif

        @if ($errors->any())
            @foreach($errors->all() as $error)
                toastr.error('{{$error}}', Error, {
                    CloseButton: true,
                    ProgressBar: true
                });
            @endforeach
        @endif
    </script>
</body>

</html>