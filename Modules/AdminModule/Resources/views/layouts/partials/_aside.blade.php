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
        <a href="{{route('admin.dashboard')}}" class="logo d-flex gap-2 align-items-center">
            <img class="main-logo c365-brand-logo onerror-image" src="{{ $sidebarLogo }}"
                alt="{{ translate('Clean 365') }}">
        </a>

        <button class="toggle-menu-button aside-toggle border-0 bg-transparent p-0 dark-color">
            <span class="material-icons">menu</span>
        </button>
    </div>


    <div class="aside-body" data-trigger="scrollbar">
        <div class="user-profile c365-user-card media gap-3 align-items-center my-3">
            <div class="avatar">
                <img class="avatar-img rounded-circle aspect-square object-fit-cover"
                    src="{{auth()->user()->profile_image_full_path }}" alt="{{ translate('Profile Image') }}">
            </div>
            <div class="media-body ">
                <h5 class="card-title">{{\Illuminate\Support\Str::limit(auth()->user()->email, 15)}}</h5>
                <span class="card-text">{{auth()->user()->user_type}}</span>
            </div>
        </div>

        <ul class="nav">
            <li class="nav-category">{{translate('main')}}</li>

            <li>
                <a href="{{route('admin.dashboard')}}"
                    class="{{request()->is('admin/dashboard') ? 'active-menu' : ''}}">
                    <span class="material-icons" title="{{translate('dashboard')}}">dashboard</span>
                    <span class="link-title">{{translate('dashboard')}}</span>
                </a>
            </li>

            @can('booking_view')
                <li class="nav-category" title="{{translate('Booking Management')}}">
                    {{translate('Booking Management')}}
                </li>
                <li class="has-sub-item {{request()->is('admin/booking/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#" class="{{request()->is('admin/booking/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Bookings">calendar_month</span>
                        <span class="link-title">{{translate('bookings')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @if(Route::has('admin.booking.post.list'))
                            <li>
                                <a href="{{route('admin.booking.post.list', ['type' => 'all'])}}"
                                    class="{{request()->is('admin/booking/post') || request()->is('admin/booking/post/details*') ? 'active-menu' : ''}}">
                                    <span class="link-title">{{translate('Customized Requests')}}
                                        <span
                                            class="count">{{\Modules\BidModule\Entities\Post::where('is_booked', 0)->count() ?? 0}}</span>
                                    </span>
                                </a>
                            </li>
                        @endif
                        @if(false && Route::has('admin.booking.list.verification'))
                            <li>
                                <a href="{{route('admin.booking.list.verification', ['booking_status' => 'pending', 'type' => 'pending'])}}"
                                    class="{{request()->is('admin/booking/list/verification') && request()->query('booking_status') == 'pending' ? 'active-menu' : ''}}"><span
                                        class="link-title">{{translate('Verify Requests')}} <span
                                            class="count">0</span></span></a>
                            </li>
                        @endif
                        <li><a href="{{route('admin.booking.list', ['booking_status' => 'pending', 'service_type' => 'all'])}}"
                                class="{{request()->is('admin/booking/list') && request()->query('booking_status') == 'pending' ? 'active-menu' : ''}}"><span
                                    class="link-title">{{translate('Booking Requests')}} <span
                                        class="count">{{$pending_booking_count}}</span></span></a>
                        </li>

                        @if(Route::has('admin.booking.offline.payment'))
                            <li><a href="{{route('admin.booking.offline.payment')}}"
                                    class="{{request()->is('admin/booking/list/offline-payment') && request()->query('booking_status') == 'pending' ? 'active-menu' : ''}}"><span
                                        class="link-title">{{translate('Offline Payment List')}} <span
                                            class="count">{{$offline_booking_count}}</span></span></a>
                            </li>
                        @endif

                        <li><a href="{{route('admin.booking.list', ['booking_status' => 'accepted', 'service_type' => 'all'])}}"
                                class="{{request()->is('admin/booking/list') && request()->query('booking_status') == 'accepted' ? 'active-menu' : ''}}"><span
                                    class="link-title">{{translate('Accepted')}} <span
                                        class="count">{{$accepted_booking_count}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status' => 'ongoing', 'service_type' => 'all'])}}"
                                class="{{request()->is('admin/booking/list') && request()->query('booking_status') == 'ongoing' ? 'active-menu' : ''}}"><span
                                    class="link-title">{{translate('Ongoing')}} <span
                                        class="count">{{$booking->where('booking_status', 'ongoing')->count()}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status' => 'completed', 'service_type' => 'all'])}}"
                                class="{{request()->is('admin/booking/list') && request()->query('booking_status') == 'completed' ? 'active-menu' : ''}}"><span
                                    class="link-title">{{translate('Completed')}} <span
                                        class="count">{{$booking->where('booking_status', 'completed')->count()}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status' => 'canceled', 'service_type' => 'all'])}}"
                                class="{{request()->is('admin/booking/list') && request()->query('booking_status') == 'canceled' ? 'active-menu' : ''}}"><span
                                    class="link-title">{{translate('Canceled')}} <span
                                        class="count">{{$booking->where('booking_status', 'canceled')->count()}}</span></span></a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany(['provider_view', 'provider_add', 'onboarding_request_view', 'withdraw_view', 'withdraw_add', 'serviceman_view', 'serviceman_add', 'team_view', 'team_add'])
                <li class="nav-category" title="{{translate('Provider Management')}}">
                    {{translate('Provider Management')}}
                </li>
            @endcanany
            @can('onboarding_request_view')
                <li>
                    <a href="{{route('admin.provider.onboarding_request', ['status' => 'onboarding'])}}"
                        class="{{request()->is('admin/provider/onboarding*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Onboarding Request')}}">description</span>
                        <span class="link-title">{{translate('Onboarding Request')}} <span
                                class="count">{{$pending_providers + $denied_providers}}</span></span>
                    </a>
                </li>
            @endcan
            @canany(['provider_view', 'provider_add'])
            <li
                class="has-sub-item  {{(request()->is('admin/provider/list') || request()->is('admin/provider/create') || request()->is('admin/provider/details*') || request()->is('admin/provider/edit*') || request()->is('admin/provider/collect-cash*')) ? 'sub-menu-opened' : ''}}">
                <a href="#"
                    class="{{(request()->is('admin/provider/list') || request()->is('admin/provider/create') || request()->is('admin/provider/details*') || request()->is('admin/provider/edit*') || request()->is('admin/provider/collect-cash*')) ? 'active-menu' : ''}}">
                    <span class="material-icons" title="Providers">engineering</span>
                    <span class="link-title">{{translate('providers')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('provider_view')
                        <li>
                            <a href="{{route('admin.provider.list', ['status' => 'all'])}}"
                                class="{{(request()->is('admin/provider/list')) ? 'active-menu' : ''}}">{{translate('Provider List')}}</a>
                        </li>
                    @endcan
                    @can('provider_add')
                        <li><a href="{{route('admin.provider.create')}}"
                                class="{{(request()->is('admin/provider/create')) ? 'active-menu' : ''}}">{{translate('Add New Provider')}}</a>
                        </li>
                    @endcan
                </ul>
            </li>
            @endcan

            @canany(['serviceman_view', 'serviceman_add', 'team_view', 'team_add'])
                <li
                    class="has-sub-item {{(request()->is('admin/serviceman*') || request()->is('admin/team*') || request()->is('admin/task*') || request()->is('admin/live-map*')) ? 'sub-menu-opened' : ''}}">
                    <a href="#"
                        class="{{(request()->is('admin/serviceman*') || request()->is('admin/team*') || request()->is('admin/task*') || request()->is('admin/live-map*')) ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Field Operations')}}">groups</span>
                        <span class="link-title">{{translate('Field Operations')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('serviceman_view')
                            <li>
                                <a href="{{route('admin.serviceman.list', ['status' => 'all'])}}"
                                    class="{{request()->is('admin/serviceman*') ? 'active-menu' : ''}}">{{translate('Servicemen')}}</a>
                            </li>
                        @endcan
                        @can('team_view')
                            <li>
                                <a href="{{route('admin.team.list', ['status' => 'all'])}}"
                                    class="{{request()->is('admin/team*') ? 'active-menu' : ''}}">{{translate('Teams')}}</a>
                            </li>
                        @endcan
                        @canany(['serviceman_view', 'provider_view'])
                            <li>
                                <a href="{{route('admin.live-map.index')}}"
                                    class="{{request()->is('admin/live-map*') ? 'active-menu' : ''}}">{{translate('Live Map')}}</a>
                            </li>
                        @endcanany
                    </ul>
                </li>
            @endcanany
            {{-- Withdraws taps (hidden)
            @canany(['withdraw_view', 'withdraw_add'])
            <li
                class="has-sub-item  {{request()->is('admin/withdraw/method*')||request()->is('admin/withdraw/method/create')||request()->is('admin/withdraw/method/edit*') || request()->is('admin/withdraw/request*') ?'sub-menu-opened':''}}">
                <a href="#"
                    class="{{request()->is('admin/withdraw/method*')||request()->is('admin/withdraw/method/create')||request()->is('admin/withdraw/method/edit*') || request()->is('admin/withdraw/request*') ?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('Withdraw Methods')}}">payments</span>
                    <span class="link-title">{{translate('Withdraws')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('withdraw_view')
                    <li>
                        <a href="{{route('admin.withdraw.request.list', ['status'=>'all'])}}"
                            class="{{request()->is('admin/withdraw/request*')?'active-menu':''}}">
                            {{translate('Withdraw Requests')}}
                        </a>
                    </li>
                    @endcan
                    @can('withdraw_add')
                    <li>
                        <a href="{{route('admin.withdraw.method.list')}}"
                            class="{{request()->is('admin/withdraw/method*')||request()->is('admin/withdraw/method/create')||request()->is('admin/withdraw/method/edit*')?'active-menu':''}}">
                            {{translate('Withdraw method setup')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
            --}}


            @canany(['wallet_add', 'wallet_view', 'customer_view', 'customer_add', 'point_view', 'newsletter_view'])
                <li class="nav-category" title="{{translate('Customer Management')}}">
                    {{translate('Customer Management')}}
                </li>
            @endcanany

            @canany(['customer_view', 'customer_add'])
                <li
                    class="has-sub-item {{request()->is('admin/customer/list') || request()->is('admin/customer/create') ? 'sub-menu-opened' : ''}}">
                    <a href="#"
                        class="{{request()->is('admin/customer/list') || request()->is('admin/customer/detail*') || request()->is('admin/customer/edit/*') || request()->is('admin/customer/create') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Customers">person_outline</span>
                        <span class="link-title">{{translate('customers')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('customer_view')
                            <li>
                                <a href="{{route('admin.customer.index')}}"
                                    class="{{request()->is('admin/customer/list') ? 'active-menu' : ''}}">
                                    {{translate('Customer List')}}
                                </a>
                            </li>
                        @endcan
                        @can('customer_add')
                            <li>
                                <a href="{{route('admin.customer.create')}}"
                                    class="{{request()->is('admin/customer/create') ? 'active-menu' : ''}}">
                                    {{translate('Add New Customer')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Customer Wallet tab (hidden)
            @canany(['wallet_add','wallet_view'])
            <li class="has-sub-item {{request()->is('admin/customer/wallet*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/customer/wallet*')?'active-menu':''}}">
                    <span class="material-icons" title="Customers">wallet</span>
                    <span class="link-title">{{translate('Customer Wallet')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('wallet_add')
                    <li>
                        <a href="{{route('admin.customer.wallet.add-fund')}}"
                            class="{{request()->is('admin/customer/wallet/add-fund')?'active-menu':''}}">
                            {{translate('Add Fund to Wallet')}}
                        </a>
                    </li>
                    @endcan
                    @can('wallet_view')
                    <li>
                        <a href="{{route('admin.customer.wallet.report')}}"
                            class="{{request()->is('admin/customer/wallet/report')?'active-menu':''}}">
                            {{translate('Wallet Transactions')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
            --}}

            {{-- Loyalty Point tab (hidden)
            @can('point_view')
            <li class="has-sub-item {{request()->is('admin/customer/loyalty-point*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/customer/loyalty-point*')?'active-menu':''}}">
                    <span class="material-icons" title="Customers">paid</span>
                    <span class="link-title">{{translate('Loyalty Point')}}</span>
                </a>
                <ul class="nav sub-menu">
                    <li>
                        <a href="{{route('admin.customer.loyalty-point.report')}}"
                            class="{{request()->is('admin/customer/loyalty-point/report')?'active-menu':''}}">
                            {{translate('Loyalty Points Transactions')}}
                        </a>
                    </li>
                </ul>
            </li>
            @endcan
            --}}

            @can('newsletter_view')
                <li>
                    <a href="{{route('admin.customer.newsletter.index')}}"
                        class="{{request()->is('admin/customer/newsletter/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Subscribed Newsletter')}}">email</span>
                        <span class="link-title">{{translate('Subscribed Newsletter')}}</span>
                    </a>
                </li>
            @endcan

            @canany(['discount_view', 'discount_add', 'coupon_view', 'coupon_add', 'bonus_view', 'bonus_add', 'campaign_view', 'campaign_add', 'advertisement_view', 'advertisement_add', 'banner_add', 'banner_view'])
                <li class="nav-category" title="{{translate('Promotion Management')}}">
                    {{translate('Promotion Management')}}
                </li>
            @endcanany
            @canany(['discount_view', 'discount_add'])
                <li class="has-sub-item {{request()->is('admin/discount/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#" class="{{request()->is('admin/discount/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('discounts')}}">redeem</span>
                        <span class="link-title">{{translate('discounts')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('discount_view')
                            <li>
                                <a href="{{route('admin.discount.list')}}"
                                    class="{{request()->is('admin/discount/list') ? 'active-menu' : ''}}">
                                    {{translate('Discount List')}}
                                </a>
                            </li>
                        @endcan
                        @can('discount_add')
                            <li>
                                <a href="{{route('admin.discount.create')}}"
                                    class="{{request()->is('admin/discount/create') ? 'active-menu' : ''}}">
                                    {{translate('Add New Discount')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany(['coupon_view', 'coupon_add'])
                <li class="has-sub-item {{request()->is('admin/coupon/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#" class="{{request()->is('admin/coupon/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('coupons')}}">sell</span>
                        <span class="link-title">{{translate('coupons')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('coupon_view')
                            <li>
                                <a href="{{route('admin.coupon.list')}}"
                                    class="{{request()->is('admin/coupon/list') ? 'active-menu' : ''}}">
                                    {{translate('Coupon List')}}
                                </a>
                            </li>
                        @endcan
                        @can('coupon_add')
                            <li>
                                <a href="{{route('admin.coupon.create')}}"
                                    class="{{request()->is('admin/coupon/create') ? 'active-menu' : ''}}">
                                    {{translate('Add New Coupon')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            {{-- Wallet Bonus tab (hidden)
            @canany(['bonus_view', 'bonus_add'])
            <li class="has-sub-item {{request()->is('admin/bonus/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/bonus/*')?'active-menu':''}}">
                    <span class="material-icons matarial-symbols-outlined"
                        title="{{translate('bonus')}}">price_change</span>
                    <span class="link-title">{{translate('Wallet Bonus')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('bonus_view')
                    <li>
                        <a href="{{route('admin.bonus.list')}}"
                            class="{{request()->is('admin/bonus/list')?'active-menu':''}}">
                            {{translate('Bonus List')}}
                        </a>
                    </li>
                    @endcan
                    @can('bonus_add')
                    <li>
                        <a href="{{route('admin.bonus.create')}}"
                            class="{{request()->is('admin/bonus/create')?'active-menu':''}}">
                            {{translate('Add New Bonus')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
            --}}
            {{-- Campaigns tab (hidden)
            @canany(['campaign_view', 'campaign_add'])
            <li class="has-sub-item {{request()->is('admin/campaign/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/campaign/*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('campaigns')}}">campaign</span>
                    <span class="link-title">{{translate('campaigns')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('campaign_view')
                    <li>
                        <a href="{{route('admin.campaign.list')}}"
                            class="{{request()->is('admin/campaign/list')?'active-menu':''}}">
                            {{translate('Campaign List')}}
                        </a>
                    </li>
                    @endcan
                    @can('campaign_add')
                    <li>
                        <a href="{{route('admin.campaign.create')}}"
                            class="{{request()->is('admin/campaign/create')?'active-menu':''}}">
                            {{translate('Add New Campaign')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
            --}}
            {{-- Advertisements tab (hidden)
            @canany(['advertisement_view', 'advertisement_add'])
            <li class="has-sub-item {{request()->is('admin/advertisements/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/advertisements/*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('advertisements')}}">campaign</span>
                    <span class="link-title">{{translate('advertisements')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('advertisement_view')
                    <li>
                        <a href="{{route('admin.advertisements.ads-list', ['status' => 'all'])}}"
                            class="{{request()->is('admin/advertisements/ads-list')?'active-menu':''}}">
                            {{translate('Ads List')}}
                        </a>
                    </li>
                    @endcan
                    @can('advertisement_add')
                    <li>
                        <a href="{{route('admin.advertisements.new-ads-request', ['status' => 'new'])}}"
                            class="{{request()->is('admin/advertisements/new-ads-request')?'active-menu':''}}">
                            {{translate('New Ads Request')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
            --}}
            @canany(['banner_add', 'banner_view'])
                <li>
                    <a href="{{route('admin.banner.create')}}"
                        class="{{request()->is('admin/banner/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Promotional Banners')}}">flag</span>
                        <span class="link-title">{{translate('Promotional Banners')}}</span>
                    </a>
                </li>
            @endcanany

            @canany(['push_notification_view', 'push_notification_add', 'notification_message_view', 'notification_message_add', 'notification_message_update', 'notification_channel_view', 'notification_channel_add'])
                <li class="nav-category" title="{{translate('Notification Management')}}">
                    {{translate('Notification Management')}}
                </li>
            @endcanany
            @canany(['push_notification_add', 'push_notification_view'])
                <li>
                    <a href="{{route('admin.push-notification.create')}}"
                        class="{{request()->is('admin/push-notification/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Push Notification')}}">send</span>
                        <span class="link-title">{{translate('Send Notifications')}}</span>
                    </a>
                </li>
            @endcanany
            @can('push_notification_view')
                <li>
                    <a href="{{route('admin.ad-broadcast.index')}}"
                        class="{{request()->is('admin/ad-broadcast*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('AI Push Notifications')}}">auto_awesome</span>
                        <span class="link-title">{{translate('AI Push Notifications')}}</span>
                    </a>
                </li>
            @endcan

            @canany(['notification_message_view', 'notification_message_add', 'notification_message_update'])
                <li>
                    <a href="{{route('admin.configuration.get-notification-setting', ['type' => 'customers'])}}"
                        class="{{request()->is('admin/configuration/get-notification-setting') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Push Notification')}}">notifications</span>
                        <span class="link-title"> {{translate('Push Notification')}}</span>
                    </a>
                </li>
            @endcanany
            @canany(['notification_channel_view', 'notification_channel_add'])
                <li>
                    <a href="{{route('admin.business-settings.notification-channel', ['notification_type' => 'user'])}}"
                        class="{{request()->is('admin/business-settings/notification-channel') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Push Notification')}}">notifications_active</span>
                        <span class="link-title"> {{translate('Notification Channel')}}</span>

                    </a>
                </li>
            @endcanany


            @canany(['service_view', 'service_add', 'zone_add', 'zone_view', 'category_view', 'category_add', 'package_view', 'package_add'])
                <li class="nav-category" title="{{translate('Service Management')}}">
                    {{translate('Service Management')}}
                </li>
            @endcanany
            @canany(['zone_add', 'zone_view'])
                <li>
                    <a href="{{route('admin.zone.create')}}" class="{{request()->is('admin/zone/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Service Zones')}}">map</span>
                        <span class="link-title">{{translate('Service Zones Setup')}}</span>
                    </a>
                </li>
            @endcanany
            @canany(['category_add', 'category_view'])
                <li
                    class="has-sub-item {{(request()->is('admin/category/*') || request()->is('admin/sub-category/*')) ? 'sub-menu-opened' : ''}}">
                    <a href="#"
                        class="{{(request()->is('admin/category/*') || request()->is('admin/sub-category/*')) ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Services')}}">category</span>
                        <span class="link-title">{{translate('Services')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.category.create')}}"
                                class="{{request()->is('admin/category/*') ? 'active-menu' : ''}}">
                                {{translate('Services Setup')}}
                            </a>
                        </li>
                        <!-- <li>
                                <a href="{{route('admin.sub-category.create')}}"
                                    class="{{request()->is('admin/sub-category/*') ? 'active-menu' : ''}}">
                                    {{translate('Sub Services Setup')}}
                                </a>
                            </li> -->
                    </ul>
                </li>
            @endcanany
            @if(Route::has('admin.additional-service.index'))
                <li>
                    <a href="{{route('admin.additional-service.index')}}"
                        class="{{request()->is('admin/additional-service/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('الخدمات الإضافية')}}">add_to_photos</span>
                        <span class="link-title">{{translate('الخدمات الإضافية')}}</span>
                    </a>
                </li>
            @endif
            @canany(['service_view', 'service_add'])
                <li class="has-sub-item {{request()->is('admin/service/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#" class="{{request()->is('admin/service/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Packages">design_services</span>
                        <span class="link-title">{{translate('packages')}}</span>
                    </a>
                    <ul class="nav flex-column sub-menu">
                        @can('service_view')
                            <li>
                                <a href="{{route('admin.service.index')}}"
                                    class="{{request()->is('admin/service/list') ? 'active-menu' : ''}}">
                                    {{translate('Package List')}}
                                </a>
                            </li>
                        @endcan
                        @can('service_add')
                            <li>
                                <a href="{{route('admin.service.create')}}"
                                    class="{{request()->is('admin/service/create') ? 'active-menu' : ''}}">
                                    {{translate('Add New Package')}}
                                </a>
                            </li>
                        @endcan
                        @can('service_view')
                            <li>
                                <a href="{{route('admin.service.request.list')}}"
                                    class="{{request()->is('admin/service/request/list*') ? 'active-menu' : ''}}">
                                    <span class="link-title">{{translate('New Package Requests')}}</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Packages menu (hidden — services section is labeled Packages instead)
            <li class="has-sub-item {{request()->is('admin/package/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/package/*')?'active-menu':''}}">
                    <span class="material-icons" title="Packages">inventory_2</span>
                    <span class="link-title">{{translate('packages')}}</span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="{{route('admin.package.list')}}"
                            class="{{request()->is('admin/package/list')?'active-menu':''}}">
                            {{translate('package_list')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.package.create')}}"
                            class="{{request()->is('admin/package/create')?'active-menu':''}}">
                            {{translate('add_new_package')}}
                        </a>
                    </li>
                </ul>
            </li>
            --}}

            {{-- Properties menu --}}
            <li class="has-sub-item {{request()->is('admin/property/*') ? 'sub-menu-opened' : ''}}">
                <a href="#" class="{{request()->is('admin/property/*') ? 'active-menu' : ''}}">
                    <span class="material-icons" title="Properties">apartment</span>
                    <span class="link-title">{{translate('properties')}}</span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="{{route('admin.property.list')}}"
                            class="{{request()->is('admin/property/list') ? 'active-menu' : ''}}">
                            {{translate('property_list')}}
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Products / Store menu --}}
            <li
                class="has-sub-item {{(request()->is('admin/product/*') || request()->is('admin/product-category/*')) ? 'sub-menu-opened' : ''}}">
                <a href="#"
                    class="{{(request()->is('admin/product/*') || request()->is('admin/product-category/*')) ? 'active-menu' : ''}}">
                    <span class="material-icons" title="Products">storefront</span>
                    <span class="link-title">{{translate('products')}}</span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="{{route('admin.product.list')}}"
                            class="{{request()->is('admin/product/list') || request()->is('admin/product/edit/*') ? 'active-menu' : ''}}">
                            {{translate('product_list')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product.create')}}"
                            class="{{request()->is('admin/product/create') ? 'active-menu' : ''}}">
                            {{translate('add_new_product')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-category.list')}}"
                            class="{{request()->is('admin/product-category/*') ? 'active-menu' : ''}}">
                            {{translate('product_categories')}}
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Store Orders --}}
            <li class="has-sub-item {{request()->is('admin/product-order/*') ? 'sub-menu-opened' : ''}}">
                <a href="#" class="{{request()->is('admin/product-order/*') ? 'active-menu' : ''}}">
                    <span class="material-icons" title="Store Orders">receipt_long</span>
                    <span class="link-title">{{translate('store_orders')}}</span>
                </a>
                <ul class="nav flex-column sub-menu">
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'all'])}}"
                            class="{{request()->is('admin/product-order/list') && request()->query('order_status', 'all') == 'all' ? 'active-menu' : ''}}">
                            {{translate('all')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'pending'])}}"
                            class="{{request()->query('order_status') == 'pending' ? 'active-menu' : ''}}">
                            {{translate('pending')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'confirmed'])}}"
                            class="{{request()->query('order_status') == 'confirmed' ? 'active-menu' : ''}}">
                            {{translate('confirmed')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'processing'])}}"
                            class="{{request()->query('order_status') == 'processing' ? 'active-menu' : ''}}">
                            {{translate('processing')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'out_for_delivery'])}}"
                            class="{{request()->query('order_status') == 'out_for_delivery' ? 'active-menu' : ''}}">
                            {{translate('out_for_delivery')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'delivered'])}}"
                            class="{{request()->query('order_status') == 'delivered' ? 'active-menu' : ''}}">
                            {{translate('delivered')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.product-order.list', ['order_status' => 'canceled'])}}"
                            class="{{request()->query('order_status') == 'canceled' ? 'active-menu' : ''}}">
                            {{translate('canceled')}}
                        </a>
                    </li>
                </ul>
            </li>

            @canany(['role_view', 'role_add', 'employee_add', 'employee_view'])
                <li class="nav-category" title="{{translate('Employee Management')}}">{{translate('Employee Management')}}
                </li>
            @endcanany

            @canany(['role_view', 'role_add'])
            <li>
                <a href="{{route('admin.role.index')}}" class="{{request()->is('admin/role/*') ? 'active-menu' : ''}}">
                    <span class="material-icons" title="Employee">settings</span>
                    <span class="link-title">{{translate('Employee Role Setup')}}</span>
                </a>
            </li>
            @endcan
            @can('employee_view')
                <li>
                    <a href="{{route('admin.employee.index')}}"
                        class="{{request()->is('admin/employee/list') || request()->is('admin/employee/edit/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Employee List')}}">list</span>
                        <span class="link-title">{{translate('Employee List')}}</span>
                    </a>
                </li>
            @endcan
            @can('employee_add')
                <li>
                    <a href="{{route('admin.employee.create')}}"
                        class="{{request()->is('admin/employee/create') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Add New Employee')}}">add</span>
                        <span class="link-title">{{translate('Add New Employee')}}</span>
                    </a>
                </li>
            @endcan

            @canany(['transaction_view', 'report_view', 'analytics_view'])
                <li class="nav-category" title="{{translate('Transaction Report and Analytics Management')}}">
                    {{translate('Transaction Reports & Analytics')}}
                </li>
            @endcanany

            @can('transaction_view')
                <li>
                    <a href="{{route('admin.transaction.list', ['trx_type' => 'all'])}}"
                        class="{{request()->is('admin/transaction/list') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Customers">article</span>
                        <span class="link-title">{{translate('All Transactions')}}</span>
                    </a>
                </li>
            @endcan

            @can('report_view')
                <li class="has-sub-item {{request()->is('admin/report/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#" class="{{request()->is('admin/report/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Customers">event_note</span>
                        <span class="link-title">{{translate('Reports')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.report.transaction', ['transaction_type' => 'all'])}}"
                                class="{{request()->is('admin/report/transaction') ? 'active-menu' : ''}}">
                                {{translate('Transaction Reports')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.report.business.overview')}}"
                                class="{{request()->is('admin/report/business*') ? 'active-menu' : ''}}">
                                {{translate('Business Reports')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.report.booking')}}"
                                class="{{request()->is('admin/report/booking') ? 'active-menu' : ''}}">
                                {{translate('Booking Reports')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.report.provider')}}"
                                class="{{request()->is('admin/report/provider') ? 'active-menu' : ''}}">
                                {{translate('Provider Reports')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
            @can('analytics_view')
                <li class="has-sub-item {{request()->is('admin/analytics/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#" class="{{request()->is('admin/analytics/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Customers">analytics</span>
                        <span class="link-title">{{translate('Analytics')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.analytics.search.keyword')}}"
                                class="{{request()->is('admin/analytics/search/keyword') ? 'active-menu' : ''}}">
                                {{translate('Keyword Search')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.analytics.search.customer')}}"
                                class="{{request()->is('admin/analytics/search/customer') ? 'active-menu' : ''}}">
                                {{translate('Customer Search')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan


            @canany(['business_view', 'subscription_package_view', 'subscriber_view', 'subscription_settings_view', 'page_view', 'landing_view', 'error_logs_view', 'cron_job_view'])
                <li class="nav-category" title="{{translate('Business Setup')}}">{{translate('Business Setup')}}</li>
            @endcanany

            @can('business_view')
                <li>
                    <a href="{{route('admin.business-settings.get-business-information')}}"
                        class="{{request()->is('admin/business-settings/get-business-information') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Push Notification')}}">settings</span>
                        <span class="link-title"> {{translate('Business Settings')}}</span>
                    </a>
                </li>
            @endcan

            {{-- Subscription Management tab (hidden)
            @canany(['subscription_settings_view', 'subscriber_view', 'subscription_package_view'])
            <li class="has-sub-item {{request()->is('admin/subscription/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('admin/subscription/*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('Subscription Management')}}">campaign</span>
                    <span class="link-title">{{translate('Subscription Management')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('subscription_package_view')
                    <li>
                        <a href="{{route('admin.subscription.package.list')}}"
                            class="{{request()->is('admin/subscription/package/*')?'active-menu':''}}">
                            {{translate('Subscription Package')}}
                        </a>
                    </li>
                    @endcan
                    @can('subscriber_view')
                    <li>
                        <a href="{{route('admin.subscription.subscriber.list')}}"
                            class="{{request()->is('admin/subscription/subscriber/*') ?'active-menu':''}}">
                            {{translate('Subscriber List')}}
                        </a>
                    </li>
                    @endcan
                    @can('subscription_settings_view')
                    <li>
                        <a href="{{route('admin.subscription.settings')}}"
                            class="{{request()->is('admin/subscription/settings') ?'active-menu':''}}">
                            {{translate('Settings')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
            --}}

            @can('blog_view')
                <li
                    class="has-sub-item {{request()->is('admin/blog/*') ? 'sub-menu-opened' : ''}}">
                    <a href="#"
                        class="{{request()->is('admin/blog/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Blog')}}">rss_feed</span>
                        <span class="link-title">{{translate('Blog')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.blog.list', ['tab' => 'list'])}}"
                                class="{{request()->is('admin/blog/*') && request('tab', 'list') !== 'automation' ? 'active-menu' : ''}}">
                                {{translate('Articles')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.blog.list', ['tab' => 'automation'])}}"
                                class="{{request()->is('admin/blog/*') && request('tab') === 'automation' ? 'active-menu' : ''}}">
                                {{translate('Blog Automation')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany(['page_view', 'landing_view'])
                <li
                    class="has-sub-item {{request()->is('admin/business-page-setup/*') || request()->is('admin/social-media/*') || request()->is('admin/business-settings/get-landing-information*') ? 'sub-menu-opened' : ''}}">
                    <a href="#"
                        class="{{request()->is('admin/business-page-setup/*') || request()->is('admin/social-media/*') || request()->is('admin/business-settings/get-landing-information*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Business pages">article</span>
                        <span class="link-title">{{translate('Page & Media')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('page_view')
                            <li>
                                <a href="{{route('admin.business-page-setup.list')}}"
                                    class="{{request()->is('admin/business-page-setup*') ? 'active-menu' : ''}}">
                                    {{translate('Business Pages')}}
                                </a>
                            </li>
                        @endcan
                        @can('page_view')
                            <li>
                                <a href="{{ route('admin.social-media.index') }}"
                                    class="{{request()->is('admin/social-media/*') ? 'active-menu' : ''}}">
                                    {{translate('Social Media')}}
                                </a>
                            </li>
                        @endcan

                        @can('landing_view')
                            <li>
                                <a href="{{route('admin.business-settings.get-landing-information', ['web_page' => 'text_setup'])}}"
                                    class="{{request()->is('admin/business-settings/get-landing-information') ? 'active-menu' : ''}}">
                                    <span class="link-title">{{translate('Landing Page Settings')}}</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @can('error_logs_view')
                <li>
                    <a href="{{route('admin.business-settings.seo.setting', ['page_type' => 'error_logs'])}}"
                        class="{{request()->is('admin/business-settings/seo-setting') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Business 404 Logs">error</span>
                        <span class="link-title">{{translate('404 Logs')}}</span>
                    </a>
                </li>
            @endcan

            {{-- Cron Job tab (hidden)
            @can('cron_job_view')
            <li>
                <a href="{{route('admin.business-settings.cron-job.list')}}"
                    class="{{request()->is('admin/business-settings/cron-job') ?'active-menu':''}}">
                    <span class="material-icons" title="Cron Job">work</span>
                    <span class="link-title">{{translate('Cron Job')}}</span>
                </a>
            </li>
            @endcan
            --}}


            @canany(['language_view', 'gallery_view'])
                <li class="nav-category" title="{{translate('System Setup')}}">{{translate('System Setup')}}</li>
            @endcanany

            @can('language_view')
                <li>
                    <a href="{{route('admin.configuration.language_setup')}}"
                        class="{{request()->is('admin/configuration/language-setup') || request()->is('admin/language/translate/*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="{{translate('Language Setup')}}">language</span>
                        <span class="link-title">{{translate('Language Setup')}}</span>
                    </a>
                </li>
            @endcan

            @can('gallery_view')
                <li>
                    <a href="{{route('admin.business-settings.get-gallery-setup')}}"
                        class="{{request()->is('admin/business-settings/get-gallery-setup*') ? 'active-menu' : ''}}">
                        <span class="material-icons" title="Page Settings">collections_bookmark</span>
                        <span class="link-title">{{translate('Gallery')}}</span>
                    </a>
                </li>
            @endcan

            @canany(['firebase_view', 'payment_method_view', 'configuration_view', 'ai_configuration_view'])
                <li class="nav-category" title="{{translate('3rd Party Setup')}}">{{translate('3rd Party Setup')}}</li>
            @endcanany

            @can('firebase_view')
                <li>
                    <a href="{{ route('admin.configuration.third-party', 'firebase-configuration') }}"
                        class="{{ request()->is('admin/configuration/third-party/firebase-*') ? 'active-menu' : '' }}">
                        <span class="material-icons" title="{{ translate('Push Notification') }}">notifications</span>
                        <span class="link-title">{{ translate('Firebase') }}</span>
                    </a>
                </li>
            @endcan
            @can('payment_method_view')
                <li>
                    <a href="{{ route('admin.configuration.third-party', ['webPage' => 'payment_config', 'type' => 'digital_payment']) }}"
                        class="{{ request()->is('admin/configuration/third-party/payment_config*') || request()->is('admin/configuration/offline*') ? 'active-menu' : '' }}">
                        <span class="material-icons" title="{{ translate('Payment Methods') }}">payment</span>
                        <span class="link-title">{{ translate('Payment Methods') }}</span>
                    </a>
                </li>
            @endcan
            @can('ai_configuration_view')
                <li>
                    <a href="{{ route('admin.configuration.ai-configuration') }}"
                        class="{{ request()->is('admin/configuration/ai-configuration') ? 'active-menu' : '' }}">
                        <span class="material-icons" title="{{ translate('AI Configuration') }}">auto_awesome</span>
                        <span class="link-title">{{ translate('AI Configuration') }}</span>
                    </a>
                </li>
            @endcan
            @can('configuration_view')
                    <li>
                        <a href="{{ route('admin.configuration.third-party', 'map-api') }}" class="{{ (request()->is('admin/configuration/third-party/*') || request()->is('admin/configuration/ai-settings/*'))
                && !request()->is('admin/configuration/third-party/firebase-*')
                && !request()->is('admin/configuration/third-party/payment_config*') ? 'active-menu' : '' }}">
                            <span class="material-icons" title="{{ translate('Other Configuration') }}">settings</span>
                            <span class="link-title">{{ translate('Other Configuration') }}</span>
                        </a>
                    </li>
            @endcan

        </ul>
    </div>
</aside>