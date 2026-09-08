@php
    $locale = session()->get('local', app()->getLocale());
    $direction = session()->get('site_direction', $locale === 'ar' ? 'rtl' : 'ltr');
    if (!in_array($direction, ['ltr', 'rtl'], true)) {
        $direction = $locale === 'ar' ? 'rtl' : 'ltr';
    }
    $nextLocale = $locale === 'ar' ? 'en' : 'ar';
    $nextLocaleLabel = $nextLocale === 'ar' ? translate('Arabic') : translate('English');
    $minutesWord = translate('minutes');
    $heroHeadlineRaw = translate('Professional cleaning for your home in minutes', ['highlight' => '__HIGHLIGHT__']);
    $heroHeadline = str_replace(
        '__HIGHLIGHT__',
        '<span class="c365-login__highlight">' . e($minutesWord) . '</span>',
        e($heroHeadlineRaw)
    );
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
<head>
    <title>{{ translate('Admin Login') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="{{ translate('Clean 365') }} — {{ translate('Admin Sign In') }}"/>
    <meta name="robots" content="nofollow, noindex">
    @php($favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png'))
    <link rel="shortcut icon" href="{{ $favIcon }}"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

    <link href="{{asset('public/assets/admin-module')}}/css/material-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/admin-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css"/>

    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/style.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-brand.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-ui.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/toastr.css">
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/form-unified.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/ff-field-date-fix.css"/>
    {{-- Isolated login CSS must load last to beat global img/layout rules --}}
    <link rel="stylesheet" href="{{ asset('public/assets/login/login.css') }}?v=5"/>
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
        /* Critical layout fallback & #006666 theme override */
        body.c365-admin-login{margin:0;background:#eef2f6}
        body.c365-admin-login .c365-login{display:flex!important;flex-direction:row!important;min-height:100vh!important;min-height:100dvh!important;width:100%!important;overflow:hidden!important;direction:ltr!important}
        body.c365-admin-login .c365-login__visual{position:relative!important;flex:1 1 46%!important;min-width:0!important;min-height:100vh!important;min-height:100dvh!important;overflow:hidden!important;background:linear-gradient(135deg, #006666 0%, #004d4d 100%)!important}
        body.c365-admin-login .c365-login__panel{display:flex!important;flex:0 0 min(540px,48%)!important;width:min(540px,48%)!important;min-height:100vh!important;min-height:100dvh!important;align-items:center!important;justify-content:center!important;padding:2rem 1.5rem!important;background:#f4f7fa!important;overflow-y:auto!important;box-sizing:border-box!important}
        body.c365-admin-login .c365-login__photo{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;max-width:none!important;object-fit:cover!important}
        body.c365-admin-login .c365-login__visual-shade,body.c365-admin-login .c365-login__stars{position:absolute!important;inset:0!important;pointer-events:none!important}
        body.c365-admin-login .c365-login__visual-copy{position:absolute!important;inset:clamp(1.25rem,4vh,2.5rem)!important;z-index:2!important;color:#fff!important;display:flex!important;flex-direction:column!important;justify-content:space-between!important;max-width:100%!important}
        html[dir="rtl"] body.c365-admin-login .c365-login__panel,
        html[dir="rtl"] body.c365-admin-login .c365-login__visual-copy,
        html[dir="rtl"] body.c365-admin-login .c365-login__form{direction:rtl!important;text-align:right!important}
        html[dir="ltr"] body.c365-admin-login .c365-login__panel,
        html[dir="ltr"] body.c365-admin-login .c365-login__visual-copy,
        html[dir="ltr"] body.c365-admin-login .c365-login__form{direction:ltr!important;text-align:left!important}

        /* Buttons & Badges #006666 Overrides */
        body.c365-admin-login .c365-login__submit,
        body.c365-admin-login .c365-login__submit.btn,
        body.c365-admin-login .btn-primary,
        body.c365-admin-login .btn--primary,
        body.c365-admin-login button[type="submit"] {
            background-color: #006666 !important;
            background: #006666 !important;
            border-color: #006666 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 102, 102, 0.35) !important;
        }

        body.c365-admin-login .c365-login__submit:hover,
        body.c365-admin-login button[type="submit"]:hover {
            background-color: #004d4d !important;
            background: #004d4d !important;
            border-color: #004d4d !important;
        }

        body.c365-admin-login .c365-login__eyebrow {
            background-color: #004d4d !important;
            background: rgba(0, 77, 77, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
        }

        body.c365-admin-login .c365-login__feature {
            background: rgba(0, 77, 77, 0.75) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            backdrop-filter: blur(8px) !important;
        }

        body.c365-admin-login .c365-login__feature-icon {
            background-color: #006666 !important;
            color: #ffffff !important;
        }

        .c1, .text-primary, .c365-login__link, .c365-login__brand-accent {
            color: #006666 !important;
        }

        @media(max-width:991px){
            body.c365-admin-login .c365-login{flex-direction:column!important;overflow:auto!important}
            body.c365-admin-login .c365-login__visual{flex:0 0 auto!important;width:100%!important;min-height:38vh!important;height:auto!important;max-height:none!important}
            body.c365-admin-login .c365-login__panel{flex:1 1 auto!important;width:100%!important;min-height:auto!important;padding:1.25rem 1rem 2rem!important}
            body.c365-admin-login .c365-login__visual-copy{position:relative!important;inset:auto!important;padding:1.25rem!important;gap:1.25rem!important;background:linear-gradient(180deg,rgba(0,102,102,.85),rgba(0,77,77,.95))!important}
        }
    </style>
</head>

<body class="c365-admin-login">
<div class="preloader"></div>
<?php
$logo = asset('public/assets/login/clean_logo.png');
$heroImage = asset('public/assets/login/login-hero.jpg');
?>

<div class="c365-login" data-dir="{{ $direction }}">
    <aside class="c365-login__visual" style="background-image:url('{{ $heroImage }}');background-size:cover;background-position:center 45%;">
        <img
            class="c365-login__photo"
            src="{{ $heroImage }}"
            alt=""
            width="800"
            height="1144"
            decoding="async"
            fetchpriority="high"
        >
        <div class="c365-login__visual-shade"></div>
        <div class="c365-login__stars" aria-hidden="true"></div>
        <div class="c365-login__visual-copy">
            <div class="c365-login__intro">
                <p class="c365-login__eyebrow">{{ translate('Clean 365 Admin') }}</p>
                <h2 class="c365-login__headline">{!! $heroHeadline !!}</h2>
                <p class="c365-login__tagline">
                    {{ translate('Our professional team guarantees a comfortable and safe cleaning experience') }}
                </p>
            </div>

            <ul class="c365-login__features" aria-label="{{ translate('Admin dashboard highlights') }}">
                <li class="c365-login__feature">
                    <span class="material-icons c365-login__feature-icon" aria-hidden="true">calendar_month</span>
                    <div>
                        <strong>{{ translate('Booking control') }}</strong>
                        <span>{{ translate('Manage schedules, jobs, and customer requests in one place') }}</span>
                    </div>
                </li>
                <li class="c365-login__feature">
                    <span class="material-icons c365-login__feature-icon" aria-hidden="true">groups</span>
                    <div>
                        <strong>{{ translate('Teams & providers') }}</strong>
                        <span>{{ translate('Coordinate cleaning crews and partner providers easily') }}</span>
                    </div>
                </li>
                <li class="c365-login__feature">
                    <span class="material-icons c365-login__feature-icon" aria-hidden="true">near_me</span>
                    <div>
                        <strong>{{ translate('Live tracking') }}</strong>
                        <span>{{ translate('Follow field progress and service status in real time') }}</span>
                    </div>
                </li>
                <li class="c365-login__feature">
                    <span class="material-icons c365-login__feature-icon" aria-hidden="true">verified</span>
                    <div>
                        <strong>{{ translate('Quality assurance') }}</strong>
                        <span>{{ translate('Review performance, feedback, and service quality reports') }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </aside>

    <main class="c365-login__panel">
        <a
            href="{{ route('admin.auth.lang', ['locale' => $nextLocale]) }}"
            class="c365-login__lang"
            aria-label="{{ translate('Switch language') }} — {{ $nextLocaleLabel }}"
        >
            <span class="material-icons c365-login__lang-icon" aria-hidden="true">translate</span>
            <span class="c365-login__lang-code">{{ strtoupper($nextLocale) }}</span>
        </a>

        <form action="{{ route('admin.auth.login') }}" method="POST" id="login-form" class="c365-login__form" data-ff-validate novalidate>
            @csrf

            <header class="c365-login__brand">
                <img class="c365-login__logo" src="{{ $logo }}" alt="{{ translate('Clean 365') }}" width="412" height="420">
                <h1 class="c365-login__welcome">{{ translate('Welcome back') }}</h1>
                <p class="c365-login__brand-sub">
                    {!! str_replace(
                        'Clean 365',
                        '<strong class="c365-login__brand-accent">Clean 365</strong>',
                        e(translate('Book cleaning services easily with Clean 365'))
                    ) !!}
                </p>
            </header>

            <div class="c365-login__fields">
                <div class="ff-field ff-field--email ff-field--has-icon" data-ff-field>
                    <label for="email" class="ff-field__label">
                        <span class="ff-field__label-text">{{ translate('Email Address') }}<span class="ff-field__required">*</span></span>
                    </label>
                    <div class="ff-field__control">
                        <span class="material-icons ff-field__icon">mail</span>
                        <input type="email"
                               name="email_or_phone"
                               id="email"
                               value="{{ request()->cookie('remember_email') }}"
                               class="form-control ff-field__input"
                               placeholder="{{ translate('ex: admin@admin.com') }}"
                               autocomplete="email"
                               maxlength="100"
                               required>
                    </div>
                    <div class="ff-field__footer">
                        <div class="ff-field__messages"></div>
                    </div>
                </div>

                <div class="ff-field ff-field--password ff-field--has-icon" data-ff-field>
                    <label for="password" class="ff-field__label">
                        <span class="ff-field__label-text">{{ translate('Password') }}<span class="ff-field__required">*</span></span>
                    </label>
                    <div class="ff-field__control">
                        <span class="material-icons ff-field__icon">lock</span>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control ff-field__input"
                               placeholder="{{ translate('Enter Password') }}"
                               autocomplete="current-password"
                               required>
                        <span class="material-icons ff-field__toggle-password togglePassword"
                              data-ff-toggle-password="#password">visibility_off</span>
                    </div>
                    <div class="ff-field__footer">
                        <div class="ff-field__messages"></div>
                    </div>
                </div>

                <div class="c365-login__remember">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="1"
                            {{ request()->cookie('remember_checked') ? 'checked' : '' }} id="rememberMeCheckbox">
                        <label class="form-check-label" for="rememberMeCheckbox">{{ translate('Remember Me') }}</label>
                    </div>
                </div>
            </div>

            @php($recaptcha = business_config('recaptcha', 'third_party'))
            @if(isset($recaptcha) && $recaptcha->is_active)
                <div class="recaptcha d-flex justify-content-center mb-3">
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                </div>
            @endif

            <button class="c365-login__submit btn btn--primary" id="signInBtn" type="submit">
                <span>{{ translate('Sign In') }}</span>
                <span class="material-icons c365-login__submit-icon" aria-hidden="true">login</span>
            </button>

            @if(env('APP_ENV')=='demo')
                <div class="c365-login__demo">
                    <button type="button" class="btn login-copy c365-login__demo-copy" aria-label="{{ translate('Copied successfully') }}">
                        <span class="material-symbols-outlined m-0">content_copy</span>
                    </button>
                    <div class="c365-login__demo-creds">
                        <div>{{ translate('Email :value', ['value' => 'admin@admin.com']) }}</div>
                        <div>{{ translate('Password :value', ['value' => '12345678']) }}</div>
                    </div>
                </div>
            @endif
        </form>
    </main>
</div>


<script src="{{asset('public/assets/admin-module')}}/js/jquery-3.6.0.min.js"></script>
<script src="{{asset('public/assets/admin-module')}}/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('public/assets/admin-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="{{asset('public/assets/admin-module')}}/js/main.js"></script>

<script src="{{asset('public/assets/admin-module')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/admin-module')}}/js/toastr.js"></script>

<script src="{{asset('public/assets/common/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
<script src="{{asset('public/assets/common')}}/js/form-validator.js"></script>
<script src="{{asset('public/assets/common')}}/js/form-character-count.js"></script>
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
                grecaptcha.execute('{{$recaptcha->live_values['site_key']}}', {action: 'submit'}).then(function (token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.querySelector('form').submit();
                });
            });

            window.onerror = function(message) {
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

    @if(env('APP_ENV')=='demo')
        $('.login-copy').on('click', function () {
            copy_cred()
        })

        function copy_cred() {
            $('#email').val('admin@admin.com');
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
