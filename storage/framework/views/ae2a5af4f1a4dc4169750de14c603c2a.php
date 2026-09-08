<?php
    $setup = getSetupGuideSteps('admin_panel', auth()->user());
    $steps = collect($setup['steps'])->sortBy('order');
    $percentage = $setup['percentage'];
    $rotation = $setup['rotation'];
    $isFirstTimeGuide = $setup['isFirstTimeGuide'];
    $arrowStep = $steps->take(1)->firstWhere('checked', false);
    $firstUncheckedStep = $steps->firstWhere('checked', false);
    $allCompleted = $steps->every(fn ($step) => $step['checked'] === true);
    $uncheckedCount = $steps->where('checked', false);

    $uncheckedStepKeys  = $steps->where('checked', false)->pluck('key')->values();
?>




<div class="easy-setup-dropdown bg-white p-3 p-sm-20">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-20">
        <h5 class="mb-0"> <?php echo e(translate('Easy Setup')); ?></h5>
        <button type="button" class="p-0 m-0 border-0 shadow-none text-secondary bg-transparent easy-setup-dropdown_close"><span class="border rounded-circle d-flex align-items-center justify-content-center w-24 h-24 fs-14" aria-hidden="true">&times;</span></button>
    </div>

    <?php
        $currentRouteToCheckGuideLine = request()->route()->getName();
        $currentWebPageToCheckGuideLine = request()->query('web_page', '');
        $currentPathToCheckGuideLine = request()->path();
    ?>

    <?php if($currentRouteToCheckGuideLine === 'admin.business-settings.get-business-information' && $currentWebPageToCheckGuideLine === 'business_setup'): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForBusinessInformation">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>
    <?php elseif($currentRouteToCheckGuideLine === 'admin.business-settings.get-business-information' && $currentWebPageToCheckGuideLine === 'business_plan'): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForBusinessPlan">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>

    <?php elseif($currentRouteToCheckGuideLine === 'admin.configuration.third-party' && str_contains($currentPathToCheckGuideLine, 'map-api')): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForGoogleMap">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>

    <?php elseif($currentRouteToCheckGuideLine === 'admin.configuration.third-party' && str_contains($currentPathToCheckGuideLine, 'email-config')): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForEmailConfiguration">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>

    <?php elseif($currentRouteToCheckGuideLine === 'admin.configuration.third-party' && str_contains($currentPathToCheckGuideLine, 'firebase-configuration')): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForNotificationConfiguration">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>

    <?php elseif($currentRouteToCheckGuideLine === 'admin.business-settings.login.setup'): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForLoginOption">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>

    <?php elseif($currentRouteToCheckGuideLine === 'admin.configuration.third-party' && str_contains($currentPathToCheckGuideLine, 'payment_config')): ?>
        <div class="bg-light p-3 p-sm-20 rounded-10 d-flex justify-content-between align-items-center gap-2 mb-20 cursor-pointer"
             data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetupGuideForPaymentMethod">
            <i class="fi fi-sr-book-bookmark"></i>
            <span class="flex-grow-1"><?php echo e(translate('See Guideline')); ?> </span>
            <span class="text-primary cursor-pointer">
                <i class="fi fi-rr-up-right-from-square"></i>
            </span>
        </div>
    <?php endif; ?>



    <div class="bg-light p-3 p-sm-20 rounded-10">
        <div class="d-flex align-items-center gap-2 mb-20">
            <span><?php echo e(translate('Theme Mode')); ?> </span>
        </div>
        <div class="">
            <div class="d-flex gap-3 gap-sm-4 flex-wrap">
                <div class="setting-box flex-grow-1 light-mode">
                    <img src="<?php echo e(asset('public/assets/provider-module')); ?>/img/icons/light-mode.svg" width="30" alt="<?php echo e(translate('provider-module')); ?>">
                </div>
                <div class="setting-box flex-grow-1 dark-mode">
                    <img src="<?php echo e(asset('public/assets/provider-module')); ?>/img/icons/dark-mode.svg" width="30" alt="<?php echo e(translate('provider-module')); ?>">
                </div>
            </div>
        </div>
    </div>
</div>
--}}

<!-- Guidline Offcanvas for business information-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForBusinessInformation" aria-labelledby="offcanvasSetupGuideForBusinessInformationLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Business Information Setup Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('System Maintenance')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('Turning on Maintenance mode will temporarily close your online site. So that the admin can do important updates or fixes')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('Admin can choose a specific section, like mobile app, web app, provider panel, provider app, serviceman app, or all systems, to temporarily deactivate for maintenance.')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('Also, there is an option to choose a specific date & time to go to maintenance mode.')); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="p-12 p-sm-20 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_02" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Basic Information Setup')); ?></span>
                </button>
            </div>

            <div class="collapse mt-3" id="collapseGeneralSetup_02">
                <div class="card card-body">
                    <p class="fs-12">
                        <strong><?php echo e(translate('Business Name')); ?>:</strong>
                        <?php echo e(translate('The Business name often serves as the primary identifier for your business as a legal entity.')); ?>

                    </p>
                    <p class="fs-12">
                        <strong><?php echo e(translate('Email')); ?>:</strong>
                        <?php echo e(translate('A company email system often provides centralised management and archiving of business communication.')); ?>

                    </p>
                    <p class="fs-12">
                        <strong><?php echo e(translate('phone')); ?>:</strong>
                        <?php echo e(translate('A phone number provides customers and providers with a direct and immediate way to reach your business for urgent inquiries, support needs, or quick questions.')); ?>

                    </p>
                    <p class="fs-12">
                        <strong><?php echo e(translate('country')); ?>:</strong>
                        <?php echo e(translate('The Country Name field, when setting up a business is essential for a multitude of reasons, spanning legal, operational, financial, and marketing aspects.')); ?>

                    </p>
                    <p class="fs-12">
                        <strong><?php echo e(translate('address')); ?>:</strong>
                        <?php echo e(translate('This address represents the business location from which the admin operates and manages providers, customers, and service personnel.')); ?>

                    </p>
                    <p class="fs-12">
                        <strong><?php echo e(translate('Logo and Fav Icon')); ?>:</strong>
                        <?php echo e(translate('This logo is the main visual identity of the business.')); ?> <?php echo e(translate('It represents the brand and is usually displayed on the website or app header, login pages, invoices, and promotional materials.')); ?> <?php echo e(translate('The fav icon helps users quickly identify the site among multiple open tabs.')); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_03" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('General Setup')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_03">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('General Setup is the basic configuration stage where you define essential business settings.')); ?> <?php echo e(translate('This section currently includes Time Zone and Date Format, Pagination Limit, Phone Number Visibility in Chat, and Currency configuration.')); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_04" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Customer')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_04">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('Enabling guest checkout allows customers to place orders without creating an account, making the checkout process faster.')); ?> <?php echo e(translate('You can also allow automatic account creation using guest information to convert guests into registered users.')); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_05" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Booking Notification')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_05">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('The Admin Notification Setup for booking allows the administrator to configure how system notifications are sent and managed. Admin notifications can be delivered either manually or through Firebase, depending on the selected configuration')); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_06" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Copyright & Cookies Text')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_06">
                <div class="card card-body">
                    <h5 class="mb-2"><?php echo e(translate('Copyright text')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('This is a short statement that shows your company owns the content on your website.')); ?> <?php echo e(translate('It usually includes the copyright symbol (©), the year, and your company name.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Cookies text')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('This is a short message shown on the website to let visitors know that the site uses cookies to collect information and improve their browsing experience')); ?>

                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Guidline Offcanvas for business plan-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForBusinessPlan" aria-labelledby="offcanvasSetupGuideForBusinessPlanLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Set up Business Plan Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Business Model')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <h5 class="mb-2"><?php echo e(translate('Subscription Based')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('A subscription-based business model allows providers to access specific features, services, or system functionalities by paying a recurring fee (monthly, quarterly, or yearly).')); ?> <?php echo e(translate('Instead of one-time payments, users remain active as long as their subscription is valid.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Commission Based')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('A commission-based business model allows the platform to earn revenue by charging a predefined percentage amount from each successful transaction completed through the system.')); ?> <?php echo e(translate('The commission is automatically deducted from the order value before the remaining amount is settled with the vendor or service provider.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guidline Offcanvas for google map-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForGoogleMap" aria-labelledby="offcanvasSetupGuideForGoogleMapLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Setup Google Map Configuration Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Google Map API')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('This section is used to configure Google Map integration for your system.')); ?> <?php echo e(translate('To enable map-based features such as location selection, serviceman tracking, and distance calculation, you must provide valid Google Map API keys.')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('Use the Server API Key to enable Place API access for backend services, and the Client API Key to enable the Maps JavaScript API for frontend usage.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guidline Offcanvas for email configuration-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForEmailConfiguration" aria-labelledby="offcanvasSetupGuideForEmailConfigurationLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Setup Email Configuration Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('SMTP Mail Configuration')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('This section allows you to configure SMTP email settings so the system can send emails reliably.')); ?> <?php echo e(translate('By providing valid SMTP credentials, the platform can deliver system notifications, OTPs, password reset emails, booking updates, and other automated messages.')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('Enter the mailer name, SMTP host, port, encryption type, username, password, and sender email address based on your email service provider.')); ?> <?php echo e(translate('Once configured, use the Send Test Mail option to verify that emails are working correctly.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Note')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('If SMTP configuration is disabled or incorrect, the system will not be able to send any email notifications.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guidline Offcanvas for notification configuration-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForNotificationConfiguration" aria-labelledby="offcanvasSetupGuideForNotificationConfigurationLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Setup Notification Configuration Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Firebase Configuration')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('Firebase Configuration is required to enable push notifications, real-time services, and authentication support in your system.')); ?> <?php echo e(translate('This setup connects your application with your Firebase project using official credentials provided by Google.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Important')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Firebase must be configured first before enabling Firebase Authentication.')); ?> <?php echo e(translate('Without this setup, Firebase-based features will not work properly.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guidline Offcanvas for login option-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForLoginOption" aria-labelledby="offcanvasSetupGuideForLoginOptionLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Explore Login Option Setup Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Choose How to Login')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('Select at least one login method to allow customers to access the system.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Manual Login')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Customers can sign up and log in using their credentials (email/phone and password).')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('OTP Login')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Customers can log in using a one-time password sent to their phone number.')); ?> <?php echo e(translate('This requires SMS Gateway setup to be configured first.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Social Media Login')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Customers can log in using their social media accounts, such as Google or Facebook.')); ?> <?php echo e(translate('At least one login method must remain enabled.')); ?>

                    </p>
                </div>
            </div>
        </div>
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_02" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Social Media')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_02">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('Enable social platforms that customers can use to log in.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Google Login')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Log in using Google email credentials')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Facebook Login')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Log in using Google facebook credentials')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Apple Login')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Available only for Apple devices')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('Click “Connect 3rd party login system” to configure social login credentials.')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('At least one social media option must remain active if social login is enabled.')); ?>

                    </p>
                </div>
            </div>
        </div>
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_03" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Verification')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_03">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('Choose how customers verify their identity during signup.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Email Verification')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Customers receive a verification code via email.')); ?>

                    </p>
                    <h5 class="mb-2"><?php echo e(translate('Phone Number Verification')); ?></h5>
                    <p class="fs-12">
                        <?php echo e(translate('Customers receive an OTP on their phone number.')); ?>

                    </p>
                </div>
            </div>
        </div>


    </div>
</div>

<!-- Guidline Offcanvas for payment method-->
<div class="offcanvas offcanvas-cus-sm offcanvas-end" tabindex="-1" id="offcanvasSetupGuideForPaymentMethod" aria-labelledby="offcanvasSetupGuideForPaymentMethodLabel">
    <div class="offcanvas-header bg-light p-20">
        <h3 class="mb-0"><?php echo e(translate('Setup Payments Guideline')); ?></h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-20 bg-white">
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_01" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Digital Payment Methods')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('A digital payment method is a way to pay or receive money electronically without using cash')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('How to Set Up & Use - choose the digital payment method you want to use, enter the required provider details (like API or merchant info), turn it on, and save to activate it for customers.')); ?>

                    </p>
                </div>
            </div>
        </div>
        <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
            <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseGeneralSetup_02" aria-expanded="true">
                    <div class="btn-collapse-icon d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                        <i class="fi fi-rr-angle-up"></i>
                    </div>
                    <span class="fw-bold text-start text-dark"><?php echo e(translate('Offline Payment')); ?></span>
                </button>
            </div>
            <div class="collapse mt-3" id="collapseGeneralSetup_02">
                <div class="card card-body">
                    <p class="fs-12">
                        <?php echo e(translate('An offline payment method allows customers to pay manually after booking placement, and the admin confirms the payment late')); ?>

                    </p>
                    <p class="fs-12">
                        <?php echo e(translate('How to Set Up & Use - Add the Offline Payment Method to the list by entering the method name & required information, and enable it, and saving it to make it available for use.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ===============================
         * ELEMENTS
         * =============================== */
        const uncheckedStepKeys = <?php echo json_encode($uncheckedStepKeys, 15, 512) ?>;
        const guideModalEl  = document.getElementById('guideModal');

        const offcanvasMap = {
            business_setup: document.getElementById('offcanvasSetupGuideForBusinessInformation'),
            business_plan: document.getElementById('offcanvasSetupGuideForBusinessPlan'),
            map_api: document.getElementById('offcanvasSetupGuideForGoogleMap'),
            email_config: document.getElementById('offcanvasSetupGuideForEmailConfiguration'),
            firebase_config: document.getElementById('offcanvasSetupGuideForNotificationConfiguration'),
            payment_config: document.getElementById('offcanvasSetupGuideForPaymentMethod'),
            login_option: document.getElementById('offcanvasSetupGuideForLoginOption'),
        };

        /* ===============================
         * CONTEXT FROM BACKEND
         * =============================== */
        const isFirstTimeGuide = <?php echo e($isFirstTimeGuide ? 'true' : 'false'); ?>;
        const allCompleted    = <?php echo e($allCompleted ? 'true' : 'false'); ?>;

        const currentRoute   = "<?php echo e(request()->route()->getName()); ?>";
        const currentWebPage = "<?php echo e(request()->query('web_page', '')); ?>";
        const currentPath    = "<?php echo e(request()->path()); ?>";

        const fromGuide = <?php echo e(request()->boolean('from_guide') ? 'true' : 'false'); ?>;

        /* ===============================
         * LOCAL STORAGE
         * =============================== */
        const SKIP_KEY = 'setup_guide_skipped';
        const isSkipped = localStorage.getItem(SKIP_KEY) === '1';

        /* ===============================
         * HELPERS
         * =============================== */
        function cleanupBackdrop() {
            document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop')
                .forEach(el => el.remove());

            document.body.classList.remove('modal-open', 'offcanvas-backdrop');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        function removeFromGuideParam() {
            const url = new URL(window.location.href);
            if (url.searchParams.has('from_guide')) {
                url.searchParams.delete('from_guide');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        }

        function showOffcanvas(el) {
            if (!el) return false;

            cleanupBackdrop();
            guideModalEl && bootstrap.Modal.getInstance(guideModalEl)?.hide();

            new bootstrap.Offcanvas(el).show();
            removeFromGuideParam();
            return true;
        }

        /* ===============================
         * AUTO OPEN LOGIC
         * =============================== */
        let offcanvasOpened = false;

        if(fromGuide){
            // Business Info
            if (currentRoute === 'admin.business-settings.get-business-information' &&
                currentWebPage === 'business_setup' &&
                uncheckedStepKeys.includes('business_information')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.business_setup);
            }

            // Business Plan
            else if (currentRoute === 'admin.business-settings.get-business-information' &&
                currentWebPage === 'business_plan' &&
                uncheckedStepKeys.includes('business_plan')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.business_plan);
            }

            // Google Map
            else if (currentRoute === 'admin.configuration.third-party' &&
                currentPath.includes('map-api') &&
                uncheckedStepKeys.includes('google_map_configuration')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.map_api);
            }

            // Email
            else if (currentRoute === 'admin.configuration.third-party' &&
                currentPath.includes('email-config') &&
                uncheckedStepKeys.includes('email_configuration')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.email_config);
            }

            // Notification
            else if (currentRoute === 'admin.configuration.third-party' &&
                currentPath.includes('firebase-configuration') &&
                uncheckedStepKeys.includes('notification_configuration')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.firebase_config);
            }

            // Payment
            else if (currentRoute === 'admin.configuration.third-party' &&
                currentPath.includes('payment_config') &&
                uncheckedStepKeys.includes('digital_payment')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.payment_config);
            }

            // Login Option
            else if (currentRoute === 'admin.business-settings.login.setup' &&
                uncheckedStepKeys.includes('login_option')) {
                offcanvasOpened = showOffcanvas(offcanvasMap.login_option);
            }
        }


        /* ===============================
         * GUIDE MODAL (ONLY IF NO OFFCANVAS)
         * =============================== */
        if (!offcanvasOpened && isFirstTimeGuide && !allCompleted && !isSkipped && guideModalEl) {
            cleanupBackdrop();
            new bootstrap.Modal(guideModalEl).show();
            removeFromGuideParam();
        }

        /* ===============================
         * SKIP FOR NOW
         * =============================== */
        document.getElementById('skipSetupGuide')?.addEventListener('click', function () {
            localStorage.setItem(SKIP_KEY, '1');

            const modal = bootstrap.Modal.getInstance(guideModalEl);
            modal?.hide();

            cleanupBackdrop();
        });

        /* ===============================
         * CLEANUP ON CLOSE
         * =============================== */
        guideModalEl?.addEventListener('hidden.bs.modal', cleanupBackdrop);
        Object.values(offcanvasMap).forEach(el => {
            el?.addEventListener('hidden.bs.offcanvas', cleanupBackdrop);
        });

    });

    function refreshSetupGuideUI() {
        fetch('<?php echo e(route('admin.setup-guide.status')); ?>')
            .then(res => res.json())
            .then(data => {

                // Badge count
                const badge = document.getElementById('setupGuideBadge');
                if (badge) {
                    badge.textContent = data.unchecked_count;
                }

                // Percentage
                const percentageEl = document.getElementById('setupGuidePercentage');
                if (percentageEl) {
                    percentageEl.innerHTML = data.percentage;
                }

                // Checkboxes
                Object.values(data.steps).forEach(step => {
                    const checkbox = document.getElementById(`guide-step-${step.key}`);
                    if (checkbox) {
                        checkbox.checked = step.checked === true;
                    }
                });

                // Hide guide completely if done
                if (data.all_completed) {
                    document.querySelector('.setup-guide')?.remove();
                    bootstrap.Modal.getInstance(document.getElementById('guideModal'))?.hide();
                }
            });
    }

</script>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/layouts/partials/_settings-sidebar.blade.php ENDPATH**/ ?>