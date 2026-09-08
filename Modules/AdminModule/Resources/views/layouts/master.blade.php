<!DOCTYPE html>
@php
    $site_direction = session()->get('site_direction');
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{$site_direction}}">

<head>
    <title>@yield('title')</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/placeholder.png'))
    <link rel="shortcut icon" href="{{ $favIcon }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">


    <link href="{{asset('public/assets/admin-module')}}/css/material-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/bootstrap.min.css" />
    <link rel="stylesheet"
        href="{{asset('public/assets/admin-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css" />


    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css" />

    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/toastr.css">

    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/style.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/dev.css" />
    <link rel="stylesheet" href="{{asset('public/assets/common/css/form-unified.css')}}" />
    <link rel="stylesheet" href="{{asset('public/assets/common/css/ff-field-date-fix.css')}}" />
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/common.css" />
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/view-guideline.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-brand.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-ui.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/clean365-shell.css" />

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
            --aside-bg: #006666 !important;
            --sidebar-bg: #006666 !important;
        }

        /* Sidebar background & dark blue elements override to #006666 */
        aside.aside,
        .aside,
        .aside-header,
        .aside-body,
        .sidebar,
        .main-sidebar,
        body[data-bs-theme="dark"] .aside,
        [data-bs-theme="dark"] aside {
            background-color: #006666 !important;
            background: #006666 !important;
        }

        .aside-header {
            background-color: #006666 !important;
            background: #006666 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
        }

        /* Top Header & Notifications #006666 overrides */
        .header .count,
        #message_count,
        .header-icon .count,
        .messages .count,
        .c365-topbar .count {
            background-color: #006666 !important;
            background: #006666 !important;
            color: #ffffff !important;
        }

        .c365-search-trigger:hover,
        .c365-search-trigger:focus {
            border-color: #006666 !important;
        }

        .user-profile.c365-user-card,
        .c365-user-card {
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .user-profile.c365-user-card .card-title,
        .user-profile.c365-user-card .card-text {
            color: #ffffff !important;
        }

        .aside .nav .nav-category {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .aside .nav li a {
            color: #ffffff !important;
        }

        .aside .nav li a .material-icons,
        .aside .nav li a .link-title {
            color: #ffffff !important;
        }

        .aside .nav li a:hover,
        .aside .nav li a.active-menu,
        .aside .nav li.sub-menu-opened>a {
            background-color: #004d4d !important;
            color: #ffffff !important;
        }

        .aside .nav .sub-menu {
            background-color: #005555 !important;
        }

        .aside .nav .sub-menu li a:hover,
        .aside .nav .sub-menu li a.active-menu {
            background-color: #003d3d !important;
            color: #ffffff !important;
        }

        /* Primary buttons, badges, background cards */
        .btn-primary,
        .bg-primary,
        .badge-primary,
        .badge--primary,
        .badge-soft-primary,
        .bg--primary,
        span.badge-primary,
        .recent-activities .badge,
        .common-list .badge {
            background-color: #006666 !important;
            border-color: #006666 !important;
            color: #ffffff !important;
        }

        .text-primary,
        .text--primary {
            color: #006666 !important;
        }

        .border-primary {
            border-color: #006666 !important;
        }

        /* Dashboard dark revenue card & Hero card */
        .c365-hero-card,
        .c365-dashboard .c365-hero-card,
        .subscription-revenue-card,
        [class*="revenue-card"] {
            background: linear-gradient(135deg, #006666 0%, #004d4d 100%) !important;
            background-color: #006666 !important;
            color: #ffffff !important;
        }

        .c365-hero-card .c365-hero-card__label,
        .c365-hero-card .c365-hero-card__value,
        .c365-hero-card .c365-hero-card__icon span {
            color: #ffffff !important;
        }

        .option-select-btn li label input:checked+span,
        .option-select-btn li label span.active,
        .option-select-btn span {
            background-color: #006666 !important;
            border-color: #006666 !important;
            color: #ffffff !important;
        }
    </style>

    @stack('css_or_js')
</head>

<body>
    <script>
        localStorage.theme && document.querySelector('body').setAttribute("data-bs-theme", localStorage.theme);
    </script>

    <div class="offcanvas-overlay"></div>


    <div class="preloader"></div>


    @include('adminmodule::layouts.partials._header')


    @include('adminmodule::layouts.partials._aside')


    @include('adminmodule::layouts.partials._settings-sidebar')


    <main class="main-area">
        @yield('content')


        @include('adminmodule::layouts.partials._footer')

        @if(env('APP_ENV') == 'demo')
            <div class="alert alert--message-2 alert-dismissible fade show" id="demo-reset-warning">
                <img width="28" class="align-self-start" src="{{ asset('public/assets/admin-module/img/info-2.png') }}"
                    alt="">
                <div class="w-0 flex-grow-1">
                    <h6>{{ translate('warning') . '!'}}</h6>
                    <span class="warning-message">
                        {{translate('though_it_is_a_demo_site') . '.' . translate('_our_system_automatically_reset_after_one_hour_&_that_is_why_you_logged_out') . '.'}}
                    </span>
                </div>
                <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @include('adminmodule::layouts.partials._status-modal')

        <!--image showing-->
        <div class="modal fade custom-confirmation-modal" id="imageShowingMOdal" tabindex="-1"
            aria-labelledby="imageShowingMOdalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body py-3 px-sm-4 px-3">
                        <button type="button" class="btn-close bg-light rounded-full" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                        <div class="image-display-container">
                            <!-- Push Inside any images -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <script src="{{asset('public/assets/admin-module')}}/js/jquery-3.6.0.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/main.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/helper.js"></script>
    <span class="global-payment-failure-text d-none" data-title="{{ translate('Payment failed') }}"
        data-message="{{ translate('Online payment failed. Please try again.') }}"></span>
    <script src="{{asset('public/assets/common')}}/js/common.js"></script>

    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/sweet_alert.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/toastr.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/dev.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/keyword-highlight.js"></script>

    @php($defaultCountryCode = strtolower(\Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'country_code')->first()?->live_values ?? 'us'))
    <span class="system-default-country-code" data-value="{{ $defaultCountryCode }}"
        data-initial-country="{{ $defaultCountryCode }}"></span>
    <link rel="stylesheet" href="{{asset('public/assets/libs/intl-tel-input/css/intlTelInput.css')}}" />
    <script src="{{ asset('public/assets/libs/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ asset('public/assets/libs/intl-tel-input/js/utils.js') }}"></script>
    <script src="{{ asset('public/assets/libs/intl-tel-input/js/intlTelInout-validation.js') }}"></script>

    <script src="{{ asset('public/assets/common/js/file-size-type-validation.js') }}"></script>
    <script src="{{ asset('public/assets/common/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('public/assets/common/js/form-character-count.js') }}"></script>
    <script src="{{ asset('public/assets/common/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('public/assets/common/js/form-validator.js') }}"></script>

    {!! Toastr::message() !!}

    <audio id="audio-element">
        <source src="{{asset('public/assets/provider-module')}}/sound/notification.mp3" type="audio/mpeg">
    </audio>

    <script>
        "use strict";
        $(document).ready(function () {
            $('.js-select').select2();
        });

        @if ($errors->any())
            @foreach($errors->all() as $error)
                toastr.error('{{$error}}', Error, {
                    CloseButton: true,
                    ProgressBar: true
                });
            @endforeach
        @endif

        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);

        $('.form-alert').on('click', function () {
            let id = $(this).data('id');
            let message = $(this).data('message');
            form_alert(id, message)
        });

        function form_alert(id, message) {
            Swal.fire({
                title: "{{translate('Are you sure')}}?",
                text: message,
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#' + id).submit()
                }
            })
        }

        $('.route-alert').on('change', function (event) {
            event.preventDefault();
            let $this = $(this);
            let initialState = $this.prop('checked'); // Save initial state

            let route = $(this).data('route');
            let message = $(this).data('message');

            route_alert(route, message, $this, initialState)
        });

        function route_alert(route, message, $this = false, initialState = false) {
            Swal.fire({
                title: "{{translate('Are you sure')}}?",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        dataType: 'json',
                        success: function (data) {
                            toastr.success(data.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                    });
                } else {
                    $this.prop('checked', !initialState);
                }
            })
        }

        $('.route-alert-reload').on('click', function () {
            let route = $(this).data('route');
            let message = $(this).data('message');
            route_alert_reload(route, message, true);
        });

        function route_alert_reload(route, message, reload, status = null, id = null) {
            Swal.fire({
                title: "{{translate('Are you sure')}}?",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        dataType: 'json',
                        data: {},
                        beforeSend: function () {

                        },
                        success: function (data) {
                            if (reload) {
                                setTimeout(location.reload.bind(location), 1000);
                            }
                            toastr.success(data.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON?.errors?.[0]?.message ?? xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}';
                            toastr.error(errorMessage, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            if (status === 1) $(`#${id}`).prop('checked', false);
                            if (status === 0) $(`#${id}`).prop('checked', true);
                        },
                        complete: function () {

                        },
                    });
                } else {
                    if (status === 1) $(`#${id}`).prop('checked', false);
                    if (status === 0) $(`#${id}`).prop('checked', true);
                }
            })
        }

        var audio = document.getElementById("audio-element");

        function playAudio(status) {
            status ? audio.play() : audio.pause();
        }

        setInterval(function () {
            $.get({
                url: '{{ route('admin.get_updated_data') }}',
                dataType: 'json',
                success: function (response) {
                    let data = response.data;
                    document.getElementById("message_count").innerHTML = data.message;
                },
            });
        }, 10000);


        $("#search-form__input").on("keyup", function () {
            var value = this.value.toLowerCase().trim();
            $(".show-search-result a").show().filter(function () {
                return $(this).text().toLowerCase().trim().indexOf(value) == -1;
            }).hide();
        });

        function demo_mode() {
            toastr.info('This function is disable for demo mode', {
                CloseButton: true,
                ProgressBar: true
            });
        }

        $('.demo_check').on('click', function (event) {
            if ('{{env('APP_ENV') == 'demo'}}') {
                event.preventDefault();
                demo_mode()
            }
        });

        $('.admin-logout').on('click', function (event) {
            Swal.fire({
                title: "{{translate('Are you sure')}}?",
                text: "{{translate('want_to_logout')}}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = "{{route('admin.auth.logout')}}"
                }
            })
        });

        $(document).ready(function () {
            $('#searchForm input[name="search"]').keyup(function () {
                var searchKeyword = $(this).val().trim();

                if (searchKeyword.length >= 2) {
                    $('#searchResults').empty().html('<div class="text-center text-muted py-5">{{translate('Searching....')}}</div>');
                    $.ajax({
                        type: 'POST',
                        url: $('#searchForm').attr('action'),
                        data: { search: searchKeyword, _token: $('input[name="_token"]').val() },
                        success: function (response) {
                            var resultHtml = '';
                            $('#searchResults').empty().html(response.htmlView);

                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('Write a minimum of two characters.')}}</div>');
                }
            });
        });

        $(document).ready(function () {
            $("#staticBackdrop").on("shown.bs.modal", function () {
                $(this).find("#searchForm input[type=search]").val('');
                $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('Loading recent searches')}}...</div>');
                $(this).find("#searchForm input[type=search]").focus();
                $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.recent.search') }}',
                    success: function (response) {
                        $('#searchResults').html(response.htmlView);
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('Error loading recent searches')}}.</div>');
                    }
                });
            });
        });
        $(document).ready(function () {
            const platform = navigator.platform;
            let shortcutText = '';
            let isMac = false;

            if (platform.toLowerCase().includes('mac')) {
                shortcutText = 'Cmd+K';
                isMac = true;
            } else if (platform.toLowerCase().includes('linux') || platform.toLowerCase().includes('win')) {
                shortcutText = 'Ctrl+K';
                isMac = false;
            } else {
                shortcutText = 'Ctrl+K';
                isMac = false;
            }
            $('.ctrlplusk').text(shortcutText);
        });

        document.addEventListener('keydown', function (event) {
            if (event.ctrlKey && event.key === 'k') {
                event.preventDefault();
                document.getElementById('modalOpener').click();
            }
        });

        $('#searchForm').submit(function (event) {
            event.preventDefault();
        });

        $(document).ready(function () {
            $('.admin-renew-package').on('click', function () {
                var packageId = $(this).data('id');
                var providerId = $(this).data('provider');

                $.ajax({
                    url: '{{ route("admin.provider.subscription-package.renew.ajax") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: packageId,
                        providerId: providerId
                    },
                    success: function (response) {
                        $('.admin-append-renew').html(response);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });

        $(document).ready(function () {
            $('.admin-shift-package').on('click', function () {
                var packageId = $(this).data('id');
                var providerId = $(this).data('provider');

                $.ajax({
                    url: '{{ route("admin.provider.subscription-package.shift.ajax") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: packageId,
                        providerId: providerId
                    },
                    success: function (response) {
                        $('.admin-append-shift').html(response);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });

        $(document).ready(function () {
            $('.admin-purchase-package').on('click', function () {
                var packageId = $(this).data('id');
                var providerId = $(this).data('provider');

                $.ajax({
                    url: '{{ route("admin.provider.subscription-package.purchase.ajax") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: packageId,
                        providerId: providerId
                    },
                    success: function (response) {
                        $('.admin-append-purchase').html(response);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });

    </script>

    <script>
        function updateCharacterCount(item) {
            let str = item.val() || "";
            let maxCharacterCount = item.data("max-character");
            let characterCount = str.length;

            if (characterCount > maxCharacterCount) {
                item.val(str.substring(0, maxCharacterCount));
                characterCount = maxCharacterCount;
            }

            item.closest(".character-count")
                .find(".char-count")
                .text(characterCount + "/" + maxCharacterCount);
        }

        $(document).on("keyup change", ".character-count-field", function () {
            updateCharacterCount($(this));
        });

        $(".character-count-field").each(function () {
            updateCharacterCount($(this));
        });

        $(document).on("reset", "form", function () {
            let form = this;

            setTimeout(function () {
                $(form).find(".character-count-field").each(function () {
                    updateCharacterCount($(this));
                });
            }, 0);
        });
    </script>

    @stack('script')
</body>

</html>