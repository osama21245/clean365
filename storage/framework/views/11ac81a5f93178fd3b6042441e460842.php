<?php
$booking = \Modules\BookingModule\Entities\Booking::get();
$pending_booking_count = \Modules\BookingModule\Entities\Booking::where('booking_status', 'pending')->count();

$offline_booking_count = \Modules\BookingModule\Entities\Booking::whereIn('booking_status', ['pending', 'accepted'])
    ->where('payment_method', 'offline_payment')->where('is_paid', 0)->count();

$accepted_booking_count = \Modules\BookingModule\Entities\Booking::where('booking_status', 'accepted')->count();
$pending_providers = \Modules\ProviderManagement\Entities\Provider::ofApproval(2)->count();
$denied_providers = \Modules\ProviderManagement\Entities\Provider::ofApproval(0)->count();
$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/placeholder.png');
$sidebarLogo = file_exists(base_path('public/assets/login/clean_logo.png'))
    ? asset('public/assets/login/clean_logo.png')
    : $logo;
?>

<aside class="aside">
    <div class="aside-header">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="logo d-flex gap-2 align-items-center">
            <img class="main-logo c365-brand-logo onerror-image" src="<?php echo e($sidebarLogo); ?>"
                alt="<?php echo e(translate('Clean 365')); ?>">
        </a>

        <button class="toggle-menu-button aside-toggle border-0 bg-transparent p-0 dark-color">
            <span class="material-icons">menu</span>
        </button>
    </div>


    <div class="aside-body" data-trigger="scrollbar">
        <div class="user-profile c365-user-card media gap-3 align-items-center my-3">
            <div class="avatar">
                <img class="avatar-img rounded-circle aspect-square object-fit-cover"
                    src="<?php echo e(auth()->user()->profile_image_full_path); ?>" alt="<?php echo e(translate('Profile Image')); ?>">
            </div>
            <div class="media-body ">
                <h5 class="card-title"><?php echo e(\Illuminate\Support\Str::limit(auth()->user()->email, 15)); ?></h5>
                <span class="card-text"><?php echo e(auth()->user()->user_type); ?></span>
            </div>
        </div>

        <ul class="nav">
            <li class="nav-category"><?php echo e(translate('main')); ?></li>

            <li>
                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="<?php echo e(request()->is('admin/dashboard') ? 'active-menu' : ''); ?>">
                    <span class="material-icons" title="<?php echo e(translate('dashboard')); ?>">dashboard</span>
                    <span class="link-title"><?php echo e(translate('dashboard')); ?></span>
                </a>
            </li>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('booking_view')): ?>
                <li class="nav-category" title="<?php echo e(translate('Booking Management')); ?>">
                    <?php echo e(translate('Booking Management')); ?>

                </li>
                <li class="has-sub-item <?php echo e(request()->is('admin/booking/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#" class="<?php echo e(request()->is('admin/booking/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Bookings">calendar_month</span>
                        <span class="link-title"><?php echo e(translate('bookings')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <?php if(Route::has('admin.booking.post.list')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.booking.post.list', ['type' => 'all'])); ?>"
                                    class="<?php echo e(request()->is('admin/booking/post') || request()->is('admin/booking/post/details*') ? 'active-menu' : ''); ?>">
                                    <span class="link-title"><?php echo e(translate('Customized Requests')); ?>

                                        <span
                                            class="count"><?php echo e(\Modules\BidModule\Entities\Post::where('is_booked', 0)->count() ?? 0); ?></span>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if(false && Route::has('admin.booking.list.verification')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.booking.list.verification', ['booking_status' => 'pending', 'type' => 'pending'])); ?>"
                                    class="<?php echo e(request()->is('admin/booking/list/verification') && request()->query('booking_status') == 'pending' ? 'active-menu' : ''); ?>"><span
                                        class="link-title"><?php echo e(translate('Verify Requests')); ?> <span
                                            class="count">0</span></span></a>
                            </li>
                        <?php endif; ?>
                        <li><a href="<?php echo e(route('admin.booking.list', ['booking_status' => 'pending', 'service_type' => 'all'])); ?>"
                                class="<?php echo e(request()->is('admin/booking/list') && request()->query('booking_status') == 'pending' ? 'active-menu' : ''); ?>"><span
                                    class="link-title"><?php echo e(translate('Booking Requests')); ?> <span
                                        class="count"><?php echo e($pending_booking_count); ?></span></span></a>
                        </li>

                        <?php if(Route::has('admin.booking.offline.payment')): ?>
                            <li><a href="<?php echo e(route('admin.booking.offline.payment')); ?>"
                                    class="<?php echo e(request()->is('admin/booking/list/offline-payment') && request()->query('booking_status') == 'pending' ? 'active-menu' : ''); ?>"><span
                                        class="link-title"><?php echo e(translate('Offline Payment List')); ?> <span
                                            class="count"><?php echo e($offline_booking_count); ?></span></span></a>
                            </li>
                        <?php endif; ?>

                        <li><a href="<?php echo e(route('admin.booking.list', ['booking_status' => 'accepted', 'service_type' => 'all'])); ?>"
                                class="<?php echo e(request()->is('admin/booking/list') && request()->query('booking_status') == 'accepted' ? 'active-menu' : ''); ?>"><span
                                    class="link-title"><?php echo e(translate('Accepted')); ?> <span
                                        class="count"><?php echo e($accepted_booking_count); ?></span></span></a>
                        </li>
                        <li><a href="<?php echo e(route('admin.booking.list', ['booking_status' => 'ongoing', 'service_type' => 'all'])); ?>"
                                class="<?php echo e(request()->is('admin/booking/list') && request()->query('booking_status') == 'ongoing' ? 'active-menu' : ''); ?>"><span
                                    class="link-title"><?php echo e(translate('Ongoing')); ?> <span
                                        class="count"><?php echo e($booking->where('booking_status', 'ongoing')->count()); ?></span></span></a>
                        </li>
                        <li><a href="<?php echo e(route('admin.booking.list', ['booking_status' => 'completed', 'service_type' => 'all'])); ?>"
                                class="<?php echo e(request()->is('admin/booking/list') && request()->query('booking_status') == 'completed' ? 'active-menu' : ''); ?>"><span
                                    class="link-title"><?php echo e(translate('Completed')); ?> <span
                                        class="count"><?php echo e($booking->where('booking_status', 'completed')->count()); ?></span></span></a>
                        </li>
                        <li><a href="<?php echo e(route('admin.booking.list', ['booking_status' => 'canceled', 'service_type' => 'all'])); ?>"
                                class="<?php echo e(request()->is('admin/booking/list') && request()->query('booking_status') == 'canceled' ? 'active-menu' : ''); ?>"><span
                                    class="link-title"><?php echo e(translate('Canceled')); ?> <span
                                        class="count"><?php echo e($booking->where('booking_status', 'canceled')->count()); ?></span></span></a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider_view', 'provider_add', 'onboarding_request_view', 'withdraw_view', 'withdraw_add', 'serviceman_view', 'serviceman_add', 'team_view', 'team_add'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Provider Management')); ?>">
                    <?php echo e(translate('Provider Management')); ?>

                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('onboarding_request_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.provider.onboarding_request', ['status' => 'onboarding'])); ?>"
                        class="<?php echo e(request()->is('admin/provider/onboarding*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Onboarding Request')); ?>">description</span>
                        <span class="link-title"><?php echo e(translate('Onboarding Request')); ?> <span
                                class="count"><?php echo e($pending_providers + $denied_providers); ?></span></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider_view', 'provider_add'])): ?>
            <li
                class="has-sub-item  <?php echo e((request()->is('admin/provider/list') || request()->is('admin/provider/create') || request()->is('admin/provider/details*') || request()->is('admin/provider/edit*') || request()->is('admin/provider/collect-cash*')) ? 'sub-menu-opened' : ''); ?>">
                <a href="#"
                    class="<?php echo e((request()->is('admin/provider/list') || request()->is('admin/provider/create') || request()->is('admin/provider/details*') || request()->is('admin/provider/edit*') || request()->is('admin/provider/collect-cash*')) ? 'active-menu' : ''); ?>">
                    <span class="material-icons" title="Providers">engineering</span>
                    <span class="link-title"><?php echo e(translate('providers')); ?></span>
                </a>
                <ul class="nav sub-menu">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_view')): ?>
                        <li>
                            <a href="<?php echo e(route('admin.provider.list', ['status' => 'all'])); ?>"
                                class="<?php echo e((request()->is('admin/provider/list')) ? 'active-menu' : ''); ?>"><?php echo e(translate('Provider List')); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_add')): ?>
                        <li><a href="<?php echo e(route('admin.provider.create')); ?>"
                                class="<?php echo e((request()->is('admin/provider/create')) ? 'active-menu' : ''); ?>"><?php echo e(translate('Add New Provider')); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['serviceman_view', 'serviceman_add', 'team_view', 'team_add'])): ?>
                <li
                    class="has-sub-item <?php echo e((request()->is('admin/serviceman*') || request()->is('admin/team*') || request()->is('admin/task*') || request()->is('admin/live-map*')) ? 'sub-menu-opened' : ''); ?>">
                    <a href="#"
                        class="<?php echo e((request()->is('admin/serviceman*') || request()->is('admin/team*') || request()->is('admin/task*') || request()->is('admin/live-map*')) ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Field Operations')); ?>">groups</span>
                        <span class="link-title"><?php echo e(translate('Field Operations')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('serviceman_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.serviceman.list', ['status' => 'all'])); ?>"
                                    class="<?php echo e(request()->is('admin/serviceman*') ? 'active-menu' : ''); ?>"><?php echo e(translate('Servicemen')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('team_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.team.list', ['status' => 'all'])); ?>"
                                    class="<?php echo e(request()->is('admin/team*') ? 'active-menu' : ''); ?>"><?php echo e(translate('Teams')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['serviceman_view', 'provider_view'])): ?>
                            <li>
                                <a href="<?php echo e(route('admin.live-map.index')); ?>"
                                    class="<?php echo e(request()->is('admin/live-map*') ? 'active-menu' : ''); ?>"><?php echo e(translate('Live Map')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            


            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['wallet_add', 'wallet_view', 'customer_view', 'customer_add', 'point_view', 'newsletter_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Customer Management')); ?>">
                    <?php echo e(translate('Customer Management')); ?>

                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['customer_view', 'customer_add'])): ?>
                <li
                    class="has-sub-item <?php echo e(request()->is('admin/customer/list') || request()->is('admin/customer/create') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#"
                        class="<?php echo e(request()->is('admin/customer/list') || request()->is('admin/customer/detail*') || request()->is('admin/customer/edit/*') || request()->is('admin/customer/create') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Customers">person_outline</span>
                        <span class="link-title"><?php echo e(translate('customers')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customer_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.customer.index')); ?>"
                                    class="<?php echo e(request()->is('admin/customer/list') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Customer List')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customer_add')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.customer.create')); ?>"
                                    class="<?php echo e(request()->is('admin/customer/create') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Add New Customer')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            

            

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('newsletter_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.customer.newsletter.index')); ?>"
                        class="<?php echo e(request()->is('admin/customer/newsletter/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Subscribed Newsletter')); ?>">email</span>
                        <span class="link-title"><?php echo e(translate('Subscribed Newsletter')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['discount_view', 'discount_add', 'coupon_view', 'coupon_add', 'bonus_view', 'bonus_add', 'campaign_view', 'campaign_add', 'advertisement_view', 'advertisement_add', 'banner_add', 'banner_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Promotion Management')); ?>">
                    <?php echo e(translate('Promotion Management')); ?>

                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['discount_view', 'discount_add'])): ?>
                <li class="has-sub-item <?php echo e(request()->is('admin/discount/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#" class="<?php echo e(request()->is('admin/discount/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('discounts')); ?>">redeem</span>
                        <span class="link-title"><?php echo e(translate('discounts')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.discount.list')); ?>"
                                    class="<?php echo e(request()->is('admin/discount/list') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Discount List')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_add')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.discount.create')); ?>"
                                    class="<?php echo e(request()->is('admin/discount/create') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Add New Discount')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['coupon_view', 'coupon_add'])): ?>
                <li class="has-sub-item <?php echo e(request()->is('admin/coupon/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#" class="<?php echo e(request()->is('admin/coupon/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('coupons')); ?>">sell</span>
                        <span class="link-title"><?php echo e(translate('coupons')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('coupon_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.coupon.list')); ?>"
                                    class="<?php echo e(request()->is('admin/coupon/list') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Coupon List')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('coupon_add')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.coupon.create')); ?>"
                                    class="<?php echo e(request()->is('admin/coupon/create') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Add New Coupon')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            
            
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['banner_add', 'banner_view'])): ?>
                <li>
                    <a href="<?php echo e(route('admin.banner.create')); ?>"
                        class="<?php echo e(request()->is('admin/banner/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Promotional Banners')); ?>">flag</span>
                        <span class="link-title"><?php echo e(translate('Promotional Banners')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['push_notification_view', 'push_notification_add', 'notification_message_view', 'notification_message_add', 'notification_message_update', 'notification_channel_view', 'notification_channel_add'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Notification Management')); ?>">
                    <?php echo e(translate('Notification Management')); ?>

                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['push_notification_add', 'push_notification_view'])): ?>
                <li>
                    <a href="<?php echo e(route('admin.push-notification.create')); ?>"
                        class="<?php echo e(request()->is('admin/push-notification/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Push Notification')); ?>">send</span>
                        <span class="link-title"><?php echo e(translate('Send Notifications')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('push_notification_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.ad-broadcast.index')); ?>"
                        class="<?php echo e(request()->is('admin/ad-broadcast*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('AI Push Notifications')); ?>">auto_awesome</span>
                        <span class="link-title"><?php echo e(translate('AI Push Notifications')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['notification_message_view', 'notification_message_add', 'notification_message_update'])): ?>
                <li>
                    <a href="<?php echo e(route('admin.configuration.get-notification-setting', ['type' => 'customers'])); ?>"
                        class="<?php echo e(request()->is('admin/configuration/get-notification-setting') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Push Notification')); ?>">notifications</span>
                        <span class="link-title"> <?php echo e(translate('Push Notification')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['notification_channel_view', 'notification_channel_add'])): ?>
                <li>
                    <a href="<?php echo e(route('admin.business-settings.notification-channel', ['notification_type' => 'user'])); ?>"
                        class="<?php echo e(request()->is('admin/business-settings/notification-channel') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Push Notification')); ?>">notifications_active</span>
                        <span class="link-title"> <?php echo e(translate('Notification Channel')); ?></span>

                    </a>
                </li>
            <?php endif; ?>


            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service_view', 'service_add', 'zone_add', 'zone_view', 'category_view', 'category_add', 'package_view', 'package_add'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Service Management')); ?>">
                    <?php echo e(translate('Service Management')); ?>

                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['zone_add', 'zone_view'])): ?>
                <li>
                    <a href="<?php echo e(route('admin.zone.create')); ?>" class="<?php echo e(request()->is('admin/zone/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Service Zones')); ?>">map</span>
                        <span class="link-title"><?php echo e(translate('Service Zones Setup')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['category_add', 'category_view'])): ?>
                <li
                    class="has-sub-item <?php echo e((request()->is('admin/category/*') || request()->is('admin/sub-category/*')) ? 'sub-menu-opened' : ''); ?>">
                    <a href="#"
                        class="<?php echo e((request()->is('admin/category/*') || request()->is('admin/sub-category/*')) ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Services')); ?>">category</span>
                        <span class="link-title"><?php echo e(translate('Services')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="<?php echo e(route('admin.category.create')); ?>"
                                class="<?php echo e(request()->is('admin/category/*') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Services Setup')); ?>

                            </a>
                        </li>
                        <!-- <li>
                                <a href="<?php echo e(route('admin.sub-category.create')); ?>"
                                    class="<?php echo e(request()->is('admin/sub-category/*') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Sub Services Setup')); ?>

                                </a>
                            </li> -->
                    </ul>
                </li>
            <?php endif; ?>
            <?php if(Route::has('admin.additional-service.index')): ?>
                <li>
                    <a href="<?php echo e(route('admin.additional-service.index')); ?>"
                        class="<?php echo e(request()->is('admin/additional-service/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('الخدمات الإضافية')); ?>">add_to_photos</span>
                        <span class="link-title"><?php echo e(translate('الخدمات الإضافية')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service_view', 'service_add'])): ?>
                <li class="has-sub-item <?php echo e(request()->is('admin/service/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#" class="<?php echo e(request()->is('admin/service/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Packages">design_services</span>
                        <span class="link-title"><?php echo e(translate('packages')); ?></span>
                    </a>
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.service.index')); ?>"
                                    class="<?php echo e(request()->is('admin/service/list') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Package List')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_add')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.service.create')); ?>"
                                    class="<?php echo e(request()->is('admin/service/create') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Add New Package')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.service.request.list')); ?>"
                                    class="<?php echo e(request()->is('admin/service/request/list*') ? 'active-menu' : ''); ?>">
                                    <span class="link-title"><?php echo e(translate('New Package Requests')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            

            
            <li class="has-sub-item <?php echo e(request()->is('admin/property/*') ? 'sub-menu-opened' : ''); ?>">
                <a href="#" class="<?php echo e(request()->is('admin/property/*') ? 'active-menu' : ''); ?>">
                    <span class="material-icons" title="Properties">apartment</span>
                    <span class="link-title"><?php echo e(translate('properties')); ?></span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="<?php echo e(route('admin.property.list')); ?>"
                            class="<?php echo e(request()->is('admin/property/list') ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('property_list')); ?>

                        </a>
                    </li>
                </ul>
            </li>

            
            <li
                class="has-sub-item <?php echo e((request()->is('admin/product/*') || request()->is('admin/product-category/*')) ? 'sub-menu-opened' : ''); ?>">
                <a href="#"
                    class="<?php echo e((request()->is('admin/product/*') || request()->is('admin/product-category/*')) ? 'active-menu' : ''); ?>">
                    <span class="material-icons" title="Products">storefront</span>
                    <span class="link-title"><?php echo e(translate('products')); ?></span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="<?php echo e(route('admin.product.list')); ?>"
                            class="<?php echo e(request()->is('admin/product/list') || request()->is('admin/product/edit/*') ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('product_list')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product.create')); ?>"
                            class="<?php echo e(request()->is('admin/product/create') ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('add_new_product')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-category.list')); ?>"
                            class="<?php echo e(request()->is('admin/product-category/*') ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('product_categories')); ?>

                        </a>
                    </li>
                </ul>
            </li>

            
            <li class="has-sub-item <?php echo e(request()->is('admin/product-order/*') ? 'sub-menu-opened' : ''); ?>">
                <a href="#" class="<?php echo e(request()->is('admin/product-order/*') ? 'active-menu' : ''); ?>">
                    <span class="material-icons" title="Store Orders">receipt_long</span>
                    <span class="link-title"><?php echo e(translate('store_orders')); ?></span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'all'])); ?>"
                            class="<?php echo e(request()->is('admin/product-order/list') && request()->query('order_status', 'all') == 'all' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('all')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'pending'])); ?>"
                            class="<?php echo e(request()->query('order_status') == 'pending' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('pending')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'confirmed'])); ?>"
                            class="<?php echo e(request()->query('order_status') == 'confirmed' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('confirmed')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'processing'])); ?>"
                            class="<?php echo e(request()->query('order_status') == 'processing' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('processing')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'out_for_delivery'])); ?>"
                            class="<?php echo e(request()->query('order_status') == 'out_for_delivery' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('out_for_delivery')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'delivered'])); ?>"
                            class="<?php echo e(request()->query('order_status') == 'delivered' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('delivered')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.product-order.list', ['order_status' => 'canceled'])); ?>"
                            class="<?php echo e(request()->query('order_status') == 'canceled' ? 'active-menu' : ''); ?>">
                            <?php echo e(translate('canceled')); ?>

                        </a>
                    </li>
                </ul>
            </li>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role_view', 'role_add', 'employee_add', 'employee_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Employee Management')); ?>"><?php echo e(translate('Employee Management')); ?>

                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role_view', 'role_add'])): ?>
            <li>
                <a href="<?php echo e(route('admin.role.index')); ?>" class="<?php echo e(request()->is('admin/role/*') ? 'active-menu' : ''); ?>">
                    <span class="material-icons" title="Employee">settings</span>
                    <span class="link-title"><?php echo e(translate('Employee Role Setup')); ?></span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.employee.index')); ?>"
                        class="<?php echo e(request()->is('admin/employee/list') || request()->is('admin/employee/edit/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Employee List')); ?>">list</span>
                        <span class="link-title"><?php echo e(translate('Employee List')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee_add')): ?>
                <li>
                    <a href="<?php echo e(route('admin.employee.create')); ?>"
                        class="<?php echo e(request()->is('admin/employee/create') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Add New Employee')); ?>">add</span>
                        <span class="link-title"><?php echo e(translate('Add New Employee')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['transaction_view', 'report_view', 'analytics_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Transaction Report and Analytics Management')); ?>">
                    <?php echo e(translate('Transaction Reports & Analytics')); ?>

                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transaction_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.transaction.list', ['trx_type' => 'all'])); ?>"
                        class="<?php echo e(request()->is('admin/transaction/list') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Customers">article</span>
                        <span class="link-title"><?php echo e(translate('All Transactions')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_view')): ?>
                <li class="has-sub-item <?php echo e(request()->is('admin/report/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#" class="<?php echo e(request()->is('admin/report/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Customers">event_note</span>
                        <span class="link-title"><?php echo e(translate('Reports')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="<?php echo e(route('admin.report.transaction', ['transaction_type' => 'all'])); ?>"
                                class="<?php echo e(request()->is('admin/report/transaction') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Transaction Reports')); ?>

                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('admin.report.business.overview')); ?>"
                                class="<?php echo e(request()->is('admin/report/business*') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Business Reports')); ?>

                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('admin.report.booking')); ?>"
                                class="<?php echo e(request()->is('admin/report/booking') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Booking Reports')); ?>

                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('admin.report.provider')); ?>"
                                class="<?php echo e(request()->is('admin/report/provider') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Provider Reports')); ?>

                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('analytics_view')): ?>
                <li class="has-sub-item <?php echo e(request()->is('admin/analytics/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#" class="<?php echo e(request()->is('admin/analytics/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Customers">analytics</span>
                        <span class="link-title"><?php echo e(translate('Analytics')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="<?php echo e(route('admin.analytics.search.keyword')); ?>"
                                class="<?php echo e(request()->is('admin/analytics/search/keyword') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Keyword Search')); ?>

                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('admin.analytics.search.customer')); ?>"
                                class="<?php echo e(request()->is('admin/analytics/search/customer') ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Customer Search')); ?>

                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>


            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['business_view', 'subscription_package_view', 'subscriber_view', 'subscription_settings_view', 'page_view', 'landing_view', 'error_logs_view', 'cron_job_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('Business Setup')); ?>"><?php echo e(translate('Business Setup')); ?></li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('business_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.business-settings.get-business-information')); ?>"
                        class="<?php echo e(request()->is('admin/business-settings/get-business-information') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Push Notification')); ?>">settings</span>
                        <span class="link-title"> <?php echo e(translate('Business Settings')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blog_view')): ?>
                <li
                    class="has-sub-item <?php echo e(request()->is('admin/blog/*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#"
                        class="<?php echo e(request()->is('admin/blog/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Blog')); ?>">rss_feed</span>
                        <span class="link-title"><?php echo e(translate('Blog')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="<?php echo e(route('admin.blog.list', ['tab' => 'list'])); ?>"
                                class="<?php echo e(request()->is('admin/blog/*') && request('tab', 'list') !== 'automation' ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Articles')); ?>

                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('admin.blog.list', ['tab' => 'automation'])); ?>"
                                class="<?php echo e(request()->is('admin/blog/*') && request('tab') === 'automation' ? 'active-menu' : ''); ?>">
                                <?php echo e(translate('Blog Automation')); ?>

                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['page_view', 'landing_view'])): ?>
                <li
                    class="has-sub-item <?php echo e(request()->is('admin/business-page-setup/*') || request()->is('admin/social-media/*') || request()->is('admin/business-settings/get-landing-information*') ? 'sub-menu-opened' : ''); ?>">
                    <a href="#"
                        class="<?php echo e(request()->is('admin/business-page-setup/*') || request()->is('admin/social-media/*') || request()->is('admin/business-settings/get-landing-information*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Business pages">article</span>
                        <span class="link-title"><?php echo e(translate('Page & Media')); ?></span>
                    </a>
                    <ul class="nav sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('page_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.business-page-setup.list')); ?>"
                                    class="<?php echo e(request()->is('admin/business-page-setup*') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Business Pages')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('page_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.social-media.index')); ?>"
                                    class="<?php echo e(request()->is('admin/social-media/*') ? 'active-menu' : ''); ?>">
                                    <?php echo e(translate('Social Media')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('landing_view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.business-settings.get-landing-information', ['web_page' => 'text_setup'])); ?>"
                                    class="<?php echo e(request()->is('admin/business-settings/get-landing-information') ? 'active-menu' : ''); ?>">
                                    <span class="link-title"><?php echo e(translate('Landing Page Settings')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('error_logs_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.business-settings.seo.setting', ['page_type' => 'error_logs'])); ?>"
                        class="<?php echo e(request()->is('admin/business-settings/seo-setting') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Business 404 Logs">error</span>
                        <span class="link-title"><?php echo e(translate('404 Logs')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            


            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['language_view', 'gallery_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('System Setup')); ?>"><?php echo e(translate('System Setup')); ?></li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.configuration.language_setup')); ?>"
                        class="<?php echo e(request()->is('admin/configuration/language-setup') || request()->is('admin/language/translate/*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Language Setup')); ?>">language</span>
                        <span class="link-title"><?php echo e(translate('Language Setup')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gallery_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.business-settings.get-gallery-setup')); ?>"
                        class="<?php echo e(request()->is('admin/business-settings/get-gallery-setup*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="Page Settings">collections_bookmark</span>
                        <span class="link-title"><?php echo e(translate('Gallery')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['firebase_view', 'payment_method_view', 'configuration_view', 'ai_configuration_view'])): ?>
                <li class="nav-category" title="<?php echo e(translate('3rd Party Setup')); ?>"><?php echo e(translate('3rd Party Setup')); ?></li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('firebase_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.configuration.third-party', 'firebase-configuration')); ?>"
                        class="<?php echo e(request()->is('admin/configuration/third-party/firebase-*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Push Notification')); ?>">notifications</span>
                        <span class="link-title"><?php echo e(translate('Firebase')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.configuration.third-party', ['webPage' => 'payment_config', 'type' => 'digital_payment'])); ?>"
                        class="<?php echo e(request()->is('admin/configuration/third-party/payment_config*') || request()->is('admin/configuration/offline*') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('Payment Methods')); ?>">payment</span>
                        <span class="link-title"><?php echo e(translate('Payment Methods')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ai_configuration_view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.configuration.ai-configuration')); ?>"
                        class="<?php echo e(request()->is('admin/configuration/ai-configuration') ? 'active-menu' : ''); ?>">
                        <span class="material-icons" title="<?php echo e(translate('AI Configuration')); ?>">auto_awesome</span>
                        <span class="link-title"><?php echo e(translate('AI Configuration')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_view')): ?>
                    <li>
                        <a href="<?php echo e(route('admin.configuration.third-party', 'map-api')); ?>" class="<?php echo e((request()->is('admin/configuration/third-party/*') || request()->is('admin/configuration/ai-settings/*'))
                && !request()->is('admin/configuration/third-party/firebase-*')
                && !request()->is('admin/configuration/third-party/payment_config*') ? 'active-menu' : ''); ?>">
                            <span class="material-icons" title="<?php echo e(translate('Other Configuration')); ?>">settings</span>
                            <span class="link-title"><?php echo e(translate('Other Configuration')); ?></span>
                        </a>
                    </li>
            <?php endif; ?>

        </ul>
    </div>
</aside><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/layouts/partials/_aside.blade.php ENDPATH**/ ?>