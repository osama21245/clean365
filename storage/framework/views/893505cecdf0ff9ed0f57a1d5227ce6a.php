<!DOCTYPE html>
<?php
    $site_direction = session()->get('site_direction');
?>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e($site_direction); ?>">

<head>
    <title><?php echo $__env->yieldContent('title'); ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php ($favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/placeholder.png')); ?>
    <link rel="shortcut icon" href="<?php echo e($favIcon); ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    
    <link href="<?php echo e(asset('public/assets/admin-module')); ?>/css/material-icons.css" rel="stylesheet">
    <link rel="stylesheet"
        href="<?php echo e(asset('public/assets/provider-module')); ?>/plugins/webfonts/uicons-regular-rounded.css" />
    <link rel="stylesheet"
        href="<?php echo e(asset('public/assets/provider-module')); ?>/plugins/webfonts/uicons-solid-rounded.css" />

    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/bootstrap.min.css" />
    <link rel="stylesheet"
        href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/perfect-scrollbar/perfect-scrollbar.min.css" />


    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/apex/apexcharts.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.css" />

    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/toastr.css">

    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/style.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/dev.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/common/css/form-unified.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/common/css/ff-field-date-fix.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/dev-tahir.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/common')); ?>/css/common.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/clean365-brand.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/clean365-ui.css" />
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/css/clean365-shell.css" />

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

    <?php echo $__env->yieldPushContent('css_or_js'); ?>
</head>

<body>
    <script>
        localStorage.theme && document.querySelector('body').setAttribute("data-bs-theme", localStorage.theme);
    </script>

    <div class="offcanvas-overlay"></div>


    <div class="preloader"></div>


    <?php echo $__env->make('adminmodule::layouts.partials._header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <?php echo $__env->make('adminmodule::layouts.partials._aside', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <?php echo $__env->make('adminmodule::layouts.partials._settings-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <main class="main-area">
        <?php echo $__env->yieldContent('content'); ?>


        <?php echo $__env->make('adminmodule::layouts.partials._footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(env('APP_ENV') == 'demo'): ?>
            <div class="alert alert--message-2 alert-dismissible fade show" id="demo-reset-warning">
                <img width="28" class="align-self-start" src="<?php echo e(asset('public/assets/admin-module/img/info-2.png')); ?>"
                    alt="">
                <div class="w-0 flex-grow-1">
                    <h6><?php echo e(translate('warning') . '!'); ?></h6>
                    <span class="warning-message">
                        <?php echo e(translate('though_it_is_a_demo_site') . '.' . translate('_our_system_automatically_reset_after_one_hour_&_that_is_why_you_logged_out') . '.'); ?>

                    </span>
                </div>
                <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('adminmodule::layouts.partials._status-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('adminmodule::layouts.partials.image-view-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('adminmodule::layouts.partials._delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </main>


    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/main.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/helper.js"></script>


    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.js"></script>

    <script src="<?php echo e(asset('public/assets/common')); ?>/js/common-image-upload.js"></script>
    <span class="global-payment-failure-text d-none" data-title="<?php echo e(translate('Payment failed')); ?>"
        data-message="<?php echo e(translate('Online payment failed. Please try again.')); ?>"></span>
    <script src="<?php echo e(asset('public/assets/common')); ?>/js/common.js"></script>

    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/sweet_alert.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/toastr.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/dev.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/keyword-highlight.js"></script>

    <?php ($defaultCountryCode = strtolower(\Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'country_code')->first()?->live_values ?? 'us')); ?>
    <span class="system-default-country-code" data-value="<?php echo e($defaultCountryCode); ?>"
        data-initial-country="<?php echo e($defaultCountryCode); ?>"></span>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/libs/intl-tel-input/css/intlTelInput.css')); ?>" />
    <script src="<?php echo e(asset('public/assets/libs/intl-tel-input/js/intlTelInput.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/libs/intl-tel-input/js/utils.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/libs/intl-tel-input/js/intlTelInout-validation.js')); ?>"></script>

    <script src="<?php echo e(asset('public/assets/common/js/file-size-type-validation.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/common/js/form-character-count.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/common/plugins/jquery-validation/jquery.validate.min.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/common/js/form-validator.js')); ?>"></script>

    <?php echo Toastr::message(); ?>


    <audio id="audio-element">
        <source src="<?php echo e(asset('public/assets/provider-module')); ?>/sound/notification.mp3" type="audio/mpeg">
    </audio>

    <script>
        "use strict";
        $(document).ready(function () {
            $('.js-select').select2();
        });

        <?php if($errors->any()): ?>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                toastr.error('<?php echo e($error); ?>', Error, {
                    CloseButton: true,
                    ProgressBar: true
                });
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

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
                title: "<?php echo e(translate('Are you sure')); ?>?",
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
                title: "<?php echo e(translate('Are you sure')); ?>?",
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
                title: "<?php echo e(translate('Are you sure')); ?>?",
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
                            const errorMessage = xhr.responseJSON?.errors?.[0]?.message ?? xhr.responseJSON?.message ?? '<?php echo e(translate('Something went wrong')); ?>';
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
                url: '<?php echo e(route('admin.get_updated_data')); ?>',
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
            if ('<?php echo e(env('APP_ENV') == 'demo'); ?>') {
                event.preventDefault();
                demo_mode()
            }
        });

        $('.admin-logout').on('click', function (event) {
            Swal.fire({
                title: "<?php echo e(translate('Are you sure')); ?>?",
                text: "<?php echo e(translate('want_to_logout')); ?>",
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
                    location.href = "<?php echo e(route('admin.auth.logout')); ?>"
                }
            })
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
        $(document).ready(function () {
            $('#searchForm input[name="search"]').keyup(function () {
                var searchKeyword = $(this).val().trim();
                if (searchKeyword.length >= 2) {
                    $('#searchResults').empty().html('<div class="text-center text-muted py-5"><?php echo e(translate('Searching....')); ?></div>');
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
                    $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('Write a minimum of two characters.')); ?></div>');
                }
            });
        });

        $(document).ready(function () {
            $("#staticBackdrop").on("shown.bs.modal", function () {
                $(this).find("#searchForm input[type=search]").val('');
                $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('Loading recent searches')); ?>...</div>');
                $(this).find("#searchForm input[type=search]").focus();
                $.ajax({
                    type: 'GET',
                    url: '<?php echo e(route('admin.recent.search')); ?>',
                    success: function (response) {
                        $('#searchResults').html(response.htmlView);
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('Error loading recent searches')); ?>.</div>');
                    }
                });
            });
        });

        document.addEventListener('keydown', function (event) {
            const isMac = navigator.platform.toUpperCase().includes('MAC');

            if (
                (isMac && event.metaKey && event.key.toLowerCase() === 'k') ||
                (!isMac && event.ctrlKey && event.key.toLowerCase() === 'k')
            ) {
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
                    url: '<?php echo e(route("admin.provider.subscription-package.renew.ajax")); ?>',
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>',
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
                    url: '<?php echo e(route("admin.provider.subscription-package.shift.ajax")); ?>',
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>',
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
                    url: '<?php echo e(route("admin.provider.subscription-package.purchase.ajax")); ?>',
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>',
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
        let globalSelectedItem;
        let initialState;
        let userConfirmed = false;
        $(document).on('change', '.update-status-modal', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            globalSelectedItem = $(this);
            initialState = globalSelectedItem.prop('checked');
            globalSelectedItem.prop('checked', !initialState);
            let confirmationTitleText = initialState
                ? globalSelectedItem.data('on-title')
                : globalSelectedItem.data('off-title');

            let confirmationDescriptionText = initialState
                ? globalSelectedItem.data('on-description')
                : globalSelectedItem.data('off-description');

            let imgSrc = initialState
                ? globalSelectedItem.data('on-image')
                : globalSelectedItem.data('off-image');

            $('.confirmation-title-text').text(confirmationTitleText);
            $('.confirmation-description-text').text(confirmationDescriptionText);
            $('#confirmChangeModal img').attr('src', imgSrc);
            $('#confirmChange').text(globalSelectedItem.data('confirm-button-text'));
            $('#cancelChange').text(globalSelectedItem.data('cancel-button-text'));

            showModal();
        });
        $('#confirmChange').on('click', function () {
            userConfirmed = true;
            if (globalSelectedItem) {
                const route = globalSelectedItem.attr('data-url');
                if (route) {
                    updateStatus(route, globalSelectedItem);
                }
                const isRadio = globalSelectedItem.attr('type') === 'radio';

                if (isRadio) {
                    $('#radio-option-1').prop('checked', globalSelectedItem.attr('id') === 'radio-option-1');
                    $('#radio-option-2').prop('checked', globalSelectedItem.attr('id') === 'radio-option-2');
                }

                if (!globalSelectedItem.hasClass('no-visual')) {
                    globalSelectedItem.prop('checked', initialState);
                }
            }

            hideModal();
        });

        $('.cancel-change').on('click', function () {
            resetCheckboxState();
            hideModal();
        });

        $('#confirmChangeModal').on('hidden.bs.modal', function () {
            if (!userConfirmed) {
                resetCheckboxState();
            }
            userConfirmed = false;
        });

        function showModal() {
            $('#confirmChangeModal').modal('show');
        }
        function hideModal() {
            $('#confirmChangeModal').modal('hide');
        }
        function resetCheckboxState() {
            if (globalSelectedItem) {
                globalSelectedItem.prop('checked', !initialState);
            }
        }
        function updateStatus(route, inputElement = null) {
            const data = {
                _token: '<?php echo e(csrf_token()); ?>'
            };

            if (inputElement) {
                const key = inputElement.attr('data-name');
                data[key] = inputElement.val();
            }
            $.ajax({
                url: route,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (data) {
                    if (data?.message) {
                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    } else if (data?.error) {
                        resetCheckboxState();
                        const isRadio = globalSelectedItem.attr('type') === 'radio';

                        if (isRadio) {
                            $('#radio-option-1').prop('checked', globalSelectedItem.attr('id') !== 'radio-option-1');
                            $('#radio-option-2').prop('checked', globalSelectedItem.attr('id') !== 'radio-option-2');
                        }
                        toastr.error(data.error, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function (data) {
                    resetCheckboxState();
                    const isRadio = globalSelectedItem.attr('type') === 'radio';

                    if (isRadio) {
                        $('#radio-option-1').prop('checked', globalSelectedItem.attr('id') !== 'radio-option-1');
                        $('#radio-option-2').prop('checked', globalSelectedItem.attr('id') !== 'radio-option-2');
                    }

                    if (data.responseJSON && data.responseJSON.response_code === 'ai_404') {
                        toastr.error(data.responseJSON.message || 'AI configuration not found.');
                    } else {
                        toastr.error('Something went wrong! Please try again.');
                    }
                }
            });
        }

        $(document).on('click', '.delete-content', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var globalSelectedItem = $(this);
            let confirmationTitleText = globalSelectedItem.data('title');
            let confirmationDescriptionText = globalSelectedItem.data('description');
            let imgSrc = globalSelectedItem.data('image');
            let url = globalSelectedItem.data('url');

            $('.confirmation-title-text').text(confirmationTitleText);
            $('.confirmation-description-text').text(confirmationDescriptionText);
            $('#showDeleteContentModal img').attr('src', imgSrc);
            $('#proceedDeleteButton').text(globalSelectedItem.data('confirm-button-text'));
            $('#proceedDeleteButton').attr('data-url', url);
            $('#cancelButton').text(globalSelectedItem.data('cancel-button-text'));

            showDeleteContentModal();
        });

        function showDeleteContentModal() {
            $('#showDeleteContentModal').modal('show');
        }

        function hideDeleteContentModal() {
            $('#showDeleteContentModal').modal('hide');
        }

        function deleteContent(route) {
            $.ajax({
                url: route,
                type: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    _method: 'DELETE'
                },
                dataType: 'json',
                success: function (data) {
                    toastr.success(data.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                },
                error: function (error) {
                    console.log(error)
                    toastr.error('Something went wrong! Please try again.');
                }
            });
        }

        $('#proceedDeleteButton').on('click', function () {
            const route = $(this).data('url');
            if (route) {
                deleteContent(route);
            }
            hideDeleteContentModal();
        });

        $('#cancelButton').on('click', function () {
            hideDeleteContentModal();
        });
    </script>



    </script>

    <?php echo $__env->yieldPushContent('script'); ?>
</body>

</html><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/layouts/new-master.blade.php ENDPATH**/ ?>