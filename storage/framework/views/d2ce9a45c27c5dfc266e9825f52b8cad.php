<?php $__env->startPush('css_or_js'); ?>

    <style>
        /* Loader overlay */
        .search-loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(4px);
            background-color: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .loader-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2ead6f;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

<?php $__env->stopPush(); ?>
<header class="header fixed-top c365-topbar">
    <div class="container-fluid h-100">
        <div class="c365-topbar__inner">
            <div class="c365-topbar__left">
                <button class="c365-topbar__menu toggle-menu-button aside-toggle border-0 bg-transparent p-0">
                    <span class="material-icons">menu</span>
                </button>
                <div class="c365-topbar__titles">
                    <h1 class="c365-topbar__heading"><?php echo $__env->yieldContent('page_heading', translate('Dashboard')); ?></h1>
                    <?php if (! empty(trim($__env->yieldContent('page_subheading')))): ?>
                        <p class="c365-topbar__sub"><?php echo $__env->yieldContent('page_subheading'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="c365-topbar__right header-right">
                <ul class="nav justify-content-end align-items-center gap-2 m-0">
                    <li class="nav-item max-sm-m-0">
                        <button type="button" id="modalOpener"
                            class="c365-search-trigger title-color bg--secondary border-0 rounded align-items-center py-2 px-2 px-md-3 d-flex gap-2"
                            data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            <span class="material-symbols-outlined c365-search-trigger__icon">search</span>
                            <span class="c365-search-trigger__label d-none d-md-block"><?php echo e(translate('Search')); ?></span>
                            <span class="c365-search-trigger__kbd d-none d-md-inline-flex">Ctrl+K</span>
                        </button>
                    </li>
                    <li class="nav-item max-sm-m-0">
                        <div class="hs-unfold">
                            <div>
                                <?php
                                    $local = session()->has('local') ? session('local') : null;
                                    $lang = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first();
                                ?>
                                <?php if($lang): ?>
                                    <div class="topbar-text dropdown d-flex">
                                        <a class="topbar-link dropdown-toggle d-flex align-items-center title-color gap-1 justify-content-between lagn-drop-btn"
                                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            data-bs-offset="0,20">
                                            <?php $__currentLoopData = $lang['live_values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(is_null($local) && $data['default']): ?>
                                                    <?php $local = $data['code']; ?>
                                                <?php endif; ?>

                                                <?php if($data['code'] == $local): ?>
                                                    <?php $language = collect(LANGUAGES)->where('code', $data['code'])->first(); ?>
                                                    <span class="material-icons">language</span>
                                                    <?php if($language): ?>
                                                        <span class="d-none d-md-block"><?php echo e($language['nativeName']); ?></span>
                                                        <span class="fz-10 d-none d-md-block">(<?php echo e($data['code']); ?>)</span>
                                                    <?php else: ?>
                                                        <span class="d-none d-md-block">(<?php echo e($data['code']); ?>)</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </a>
                                        <ul class="dropdown-menu lang-menu">
                                            <?php $__currentLoopData = $lang['live_values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($data['status'] == 1): ?>
                                                    <?php $language = collect(LANGUAGES)->where('code', $data['code'])->first(); ?>
                                                    <li>
                                                        <a class="dropdown-item d-flex gap-2 align-items-center py-2 justify-content-between"
                                                            href="<?php echo e(route('admin.lang', [$data['code']])); ?>">
                                                            <?php if($language): ?>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <span class="text-capitalize"><?php echo e($language['nativeName']); ?></span>
                                                                    <span class="fz-10">(<?php echo e($data['code']); ?>)</span>
                                                                </div>
                                                                <?php if($local == $data['code']): ?>
                                                                    <span class="material-symbols-outlined text-muted">check_circle</span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-capitalize"><?php echo e($data['code']); ?></span>
                                                            <?php endif; ?>

                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php
                        $nav_pending_bookings = \Modules\BookingModule\Entities\Booking::where('booking_status', 'pending')->count();
                        $nav_pending_providers = \Modules\ProviderManagement\Entities\Provider::ofApproval(2)->count();
                        $nav_offline_payments = \Modules\BookingModule\Entities\Booking::whereIn('booking_status', ['pending', 'accepted'])
                            ->where('payment_method', 'offline_payment')->where('is_paid', 0)->count();
                        $nav_custom_requests = class_exists('\Modules\BidModule\Entities\Post') ? \Modules\BidModule\Entities\Post::where('is_booked', 0)->count() : 0;

                        $nav_total_notifications = $nav_pending_bookings + $nav_pending_providers + $nav_offline_payments + $nav_custom_requests;

                        $recent_notifications = [];

                        if ($nav_pending_bookings > 0) {
                            $recent_notifications[] = [
                                'icon' => 'calendar_month',
                                'color' => 'text-primary',
                                'title' => translate('New Pending Bookings'),
                                'subtitle' => translate(':count bookings waiting for confirmation', ['count' => $nav_pending_bookings]),
                                'url' => route('admin.booking.list', ['booking_status' => 'pending', 'service_type' => 'all']),
                                'time' => translate('Action Required')
                            ];
                        }
                        if ($nav_pending_providers > 0) {
                            $recent_notifications[] = [
                                'icon' => 'person_add',
                                'color' => 'text-warning',
                                'title' => translate('Pending Provider Approvals'),
                                'subtitle' => translate(':count provider applications pending review', ['count' => $nav_pending_providers]),
                                'url' => Route::has('admin.provider.onboarding_request') ? route('admin.provider.onboarding_request', ['status' => 'onboarding']) : route('admin.provider.list'),
                                'time' => translate('Review Required')
                            ];
                        }
                        if ($nav_offline_payments > 0) {
                            $recent_notifications[] = [
                                'icon' => 'payments',
                                'color' => 'text-info',
                                'title' => translate('Offline Payment Verification'),
                                'subtitle' => translate(':count offline payments pending verification', ['count' => $nav_offline_payments]),
                                'url' => Route::has('admin.booking.list.verification') ? route('admin.booking.list.verification', ['booking_status' => 'pending', 'type' => 'pending']) : route('admin.booking.list', ['booking_status' => 'pending', 'service_type' => 'all']),
                                'time' => translate('Verification')
                            ];
                        }
                        if ($nav_custom_requests > 0) {
                            $recent_notifications[] = [
                                'icon' => 'request_quote',
                                'color' => 'text-success',
                                'title' => translate('Customized Service Requests'),
                                'subtitle' => translate(':count customized service requests submitted', ['count' => $nav_custom_requests]),
                                'url' => Route::has('admin.booking.post.list') ? route('admin.booking.post.list', ['type' => 'all']) : '#',
                                'time' => translate('Custom Request')
                            ];
                        }
                    ?>
                    <li>
                        <div class="notifications dropdown pe--12 position-relative">
                            <a href="#" class="header-icon count-btn" data-bs-toggle="dropdown" aria-expanded="false"
                                title="<?php echo e(translate('Notifications')); ?>">
                                <span class="material-icons">notifications</span>
                                <?php if($nav_total_notifications > 0): ?>
                                    <span class="count" id="notification_count"><?php echo e($nav_total_notifications); ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg p-0 shadow-lg border-0 mt-2"
                                style="min-width: 320px; max-width: 360px; z-index: 1050;">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between rounded-top"
                                    style="background-color: #f8f9fa;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="material-icons"
                                            style="color: #006666 !important;">notifications_active</span>
                                        <h6 class="mb-0 fw-bold" style="color: #333;">
                                            <?php echo e(translate('Notifications & Alerts')); ?>

                                        </h6>
                                    </div>
                                    <?php if($nav_total_notifications > 0): ?>
                                        <span class="badge rounded-pill bg-danger px-2 py-1"><?php echo e($nav_total_notifications); ?>

                                            <?php echo e(translate('New')); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-list overflow-auto" style="max-height: 350px;">
                                    <?php $__empty_1 = true; $__currentLoopData = $recent_notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <a href="<?php echo e($notif['url']); ?>"
                                            class="dropdown-item p-3 border-bottom text-wrap d-flex align-items-start gap-3"
                                            style="white-space: normal;">
                                            <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="background-color: rgba(0,102,102,0.1); width: 38px; height: 38px;">
                                                <span class="material-icons"
                                                    style="color: #006666; font-size: 20px;"><?php echo e($notif['icon']); ?></span>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-bold text-dark fs-13 mb-1"><?php echo e($notif['title']); ?></div>
                                                <div class="text-muted fs-12 mb-1"><?php echo e($notif['subtitle']); ?></div>
                                                <small class="text-muted fz-10"><?php echo e($notif['time']); ?></small>
                                            </div>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="p-4 text-center text-muted">
                                            <span
                                                class="material-icons fz-36 opacity-50 d-block mb-2">notifications_none</span>
                                            <p class="mb-0 fs-13"><?php echo e(translate('No new notifications or pending issues')); ?>

                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if($nav_total_notifications > 0): ?>
                                    <div class="p-2 text-center border-top rounded-bottom"
                                        style="background-color: #f8f9fa;">
                                        <a href="<?php echo e(route('admin.booking.list', ['booking_status' => 'pending', 'service_type' => 'all'])); ?>"
                                            class="fs-12 fw-bold text-decoration-none" style="color: #006666;">
                                            <?php echo e(translate('View All Pending Tasks')); ?> &rarr;
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="user mt-n1">
                            <a href="#" class="header-icon user-icon" data-bs-toggle="dropdown">
                                <img width="30" height="30" src="<?php echo e(auth()->user()->profile_image_full_path); ?>"
                                    class="rounded-circle aspect-square object-fit-cover"
                                    alt="<?php echo e(translate('profile_image')); ?>">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="<?php echo e(route('admin.profile_update')); ?>"
                                    class="dropdown-item-text media gap-3 align-items-center">
                                    <div class="avatar">
                                        <img class="avatar-img rounded-circle aspect-square object-fit-cover" width="50"
                                            height="50" src="<?php echo e(auth()->user()->profile_image_full_path); ?>"
                                            alt="<?php echo e(translate('profile-image')); ?>">
                                    </div>
                                    <div class="media-body ">
                                        <h5 class="card-title"><?php echo e(Str::limit(auth()->user()?->first_name, 20)); ?></h5>
                                        <span class="card-text"><?php echo e(Str::limit(auth()->user()?->email, 20)); ?></span>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="<?php echo e(route('admin.profile_update')); ?>">
                                    <span class="text-truncate"
                                        title="<?php echo e(translate('Settings')); ?>"><?php echo e(translate('Settings')); ?></span>
                                </a>
                                <a class="dropdown-item admin-logout">
                                    <span class="text-truncate cursor-pointer"
                                        title="<?php echo e(translate('Sign Out')); ?>"><?php echo e(translate('Sign_Out')); ?></span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<div class="modal fade removeSlideDown" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-content__search border-0 <?php echo e(env('APP_ENV') == 'demo' ? 'mt-5' : ''); ?>">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex gap-2 align-items-center rounded bg-card py-2 px-3">
                    <form class="flex-grow-1" id="searchForm" action="<?php echo e(route('admin.search.routing')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="d-flex align-items-center global-search-container">
                            <span class="material-symbols-outlined">search</span>
                            <input class="form-control flex-grow-1 border-0 search-input" id="searchInput" name="search"
                                type="search" placeholder="<?php echo e(translate('Search')); ?>" aria-label="Search" autofocus
                                autocomplete="off">
                        </div>
                    </form>
                    <button class="border-0 rounded-3 px-2 py-1" type="button"
                        data-bs-dismiss="modal"><?php echo e(translate('Esc')); ?></button>
                </div>

                <div class="bg-card p-4 rounded-3 min-h-350">
                    <div class="search-result" id="searchResults">
                        <div id="searchLoaderOverlay" class="search-loader-overlay">
                            <div class="loader-spinner"></div>
                        </div>
                        <div class="text-center text-muted py-5">
                            <?php echo e(translate('It appears that you have not yet searched.')); ?>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/layouts/partials/_header.blade.php ENDPATH**/ ?>