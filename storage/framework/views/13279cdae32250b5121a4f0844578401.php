<div class="tab-pane fade <?php echo e(request()->has('type') && request()->type == 'digital_payment' ? 'show active' : ''); ?>" id="payment-tabs1" role="tabpanel" aria-labelledby="payment-custom-tab1" tabindex="0">
    <div class="bg-warning bg-opacity-10 fs-12 p-12 text-dark rounded mb-10">
        <div class="d-flex align-items-center gap-2 mb-2">
            <p class="fz-12 fw-medium">
                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/alert_info.svg')); ?>" alt="alert info icon">
                <?php echo e(translate('Here you can configure payment gateways by obtaining the necessary credentials (e.g., API keys) from each respective payment gateway platform.')); ?></p>
        </div>
        <ul class="m-0 ps-20 d-flex flex-column gap-1 text-dark">
            <li><?php echo e(translate('To use digital payments, you need to set up at least one payment method')); ?></li>
            <?php ($businessInformationLink = auth()->user()->can('business_view')
                ? '<a href="' . route('admin.business-settings.get-business-information') . '" class="fw-semibold text-primary text-decoration-underline" target="_blank">' . translate('Business Information') . '</a>'
                : translate('Business Information')); ?>
            <li><?php echo translate('To make these payment options available, you must enable the Digital payment option from the :businessInformationLink page.', ['businessInformationLink' => $businessInformationLink]); ?></li>
        </ul>
    </div>
    <?php
        $currencySupported = 0;
        if (isset($data['gateways']))
        {
            foreach ($data['gateways'] as $gateway)
            {
                if ((bool)$gateway->is_active == 1 && checkCurrency($gateway->key_name, 'payment_gateway'))
                {
                    $currencySupported = 1;
                    break;
                }
            }
        }
    ?>
    <?php if(!$currencySupported): ?>
        <div class="mb-15 bg-danger bg-opacity-10 fs-12 p-10 rounded d-flex gap-2 align-items-center">
                    <span class="material-symbols-outlined text-danger">
                        warning
                    </span>
            <?php ($businessInformationLink = '<a href="' . route('admin.business-settings.get-business-information') . '" class="fw-medium text-primary text-decoration-underline">' . translate('Business Information') . '</a>'); ?>
            <span><?php echo e(translate('Currently no payment gateway supports your currency.')); ?> <?php echo e(translate('Activate at least one gateway that supports your currency.')); ?> <?php echo translate('To change currency setup, visit the :businessInformationLink page.', ['businessInformationLink' => $businessInformationLink]); ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-20">
            <div class="d-flex align-items-center flex-wrap gap-10 justify-content-between mb-20">
                <h4><?php echo e(translate('Digital Payment Methods List')); ?></h4>
                <form action="<?php echo e(route('admin.configuration.third-party', 'payment_config')); ?>" class="d-flex align-items-center gap-0 border rounded" method="GET">
                    <input type="hidden" name="type" value="digital_payment">
                    <input type="search" class="theme-input-style border-0 rounded block-size-36" value="<?php echo e($data['search'] ?? ''); ?>" name="search" placeholder="<?php echo e(translate('search_here')); ?>">
                    <button type="submit" class="bg-light border-0 px-2 block-size-36 rounded-end d-flex align-items-center justify-content-center">
                                    <span class="material-symbols-outlined fz-20 opacity-75">
                                        search
                                    </span>
                    </button>
                </form>
            </div>
            <?php if($publishedStatus == 1): ?>
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-around">
                            <h4 class="text-danger pt-2">
                                <i class="tio-info-outined"></i>
                                <?php echo e(translate('Your current payment settings are disabled because another payment gateway is active. Follow the link to open the active payment gateway settings.')); ?>.</h4>

                            <a href="<?php echo e(!empty($paymentUrl) ? $paymentUrl : ''); ?>"
                               class="btn btn-outline-primary"><?php echo e(translate('settings')); ?></a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row g-4 <?php echo e($publishedStatus == 1 ? 'disabled' : ''); ?>" id="gateway-cards" >
                <?php $__empty_1 = true; $__currentLoopData = $data['gateways']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-lg-6">
                        <div
                            class="cus-shadow2 payment-test__wrap p-20 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <?php
                                $gatewayReadyToUse = 1;
                                foreach (\Illuminate\Support\Arr::except($gateway['live_values'], 'status') as $liveValueKey => $liveValues) {
                                    if (empty($liveValues)) {
                                        $gatewayReadyToUse = 0;
                                    }
                                }
                                $gatewayImageFullPath = getPaymentGatewayImageFullPath(key: $gateway->key_name, settingsType: $gateway->settings_type, defaultPath: 'public/assets/admin-module/img/placeholder.png');
                                ?>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="text-dark"> <?php echo e(ucwords(str_replace('_',' ',$gateway->key_name))); ?></h5> <span
                                    class="bg-primary payment-test bg-opacity-10 rounded py-1 px-2 text-primary fz-12"><?php echo e($gateway->live_values['mode']); ?></span>
                                <?php if(!$gatewayReadyToUse): ?>
                                    <span
                                        class="bg-danger payment-test bg-opacity-10 rounded py-1 px-2 text-danger fz-12"> <?php echo e(translate('Not_Configured')); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex align-items-center gap-xxl-4 gap-xl-3 gap-2">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method_manage_status')): ?>
                                    <?php if(checkCurrency($gateway->key_name, 'payment_gateway')): ?>
                                        <?php if(!$gatewayReadyToUse): ?>
                                            <label class="switcher"
                                                   data-bs-toggle="offcanvas"
                                                   data-bs-target="#offcanvas-<?php echo e($gateway->key_name); ?>">
                                                <input class="switcher_input"
                                                       type="checkbox" value="1" name="status" disabled>
                                                <span class="switcher_control"></span>
                                            </label>
                                        <?php else: ?>
                                            <label class="switcher">
                                                <input type="checkbox"
                                                       <?php if($gateway->is_active): echo 'checked'; endif; ?>
                                                       class="<?php echo e(env('APP_ENV') == 'demo' ? '' : 'update-status-modal'); ?> switcher_input"
                                                       data-id="<?php echo e($gateway->key_name); ?>"
                                                       data-url="<?php echo e(route('admin.configuration.update-payment-status', ['gateway' => $gateway->key_name, 'status' => (int)!$gateway->is_active])); ?>"
                                                       data-on-title="<?php echo e(translate('Want to turn on :gateway as the digital payment method?', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                                       data-off-title="<?php echo e(translate('Want to turn off :gateway as the digital payment method?', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                                       data-on-description="<?php echo e(translate('if_enabled_customers_can_use_this_payment_method')); ?>"
                                                       data-off-description="<?php echo e(translate('if_disabled_this_payment_method_will_be_hidden_from_the_checkout_page')); ?>"
                                                       data-on-image="<?php echo e($gatewayImageFullPath); ?>"
                                                       data-off-image="<?php echo e($gatewayImageFullPath); ?>"
                                                       data-cancel-button-text="<?php echo e(translate('Cancel')); ?>"
                                                       data-confirm-button-text="<?php echo e(translate('Ok')); ?>"
                                                >
                                                <span class="switcher_control <?php echo e(env('APP_ENV') == 'demo' ? 'disabled' : ''); ?>"></span>
                                            </label>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if((bool)$gateway->is_active == 1): ?>
                                            <label class="switcher">
                                                <input type="checkbox"
                                                       <?php if($gateway->is_active): echo 'checked'; endif; ?>
                                                       class="<?php echo e(env('APP_ENV') == 'demo' ? '' : 'update-status-modal'); ?> switcher_input"
                                                       data-id="<?php echo e($gateway->key_name); ?>"
                                                       data-url="<?php echo e(route('admin.configuration.update-payment-status', ['gateway' => $gateway->key_name, 'status' => (int)!$gateway->is_active])); ?>"
                                                       data-on-title="<?php echo e(translate('Want to turn on :gateway as the digital payment method?', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                                       data-off-title="<?php echo e(translate('Want to turn off :gateway as the digital payment method?', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                                       data-on-description="<?php echo e(translate('if_enabled_customers_can_use_this_payment_method')); ?>"
                                                       data-off-description="<?php echo e(translate('if_disabled_this_payment_method_will_be_hidden_from_the_checkout_page')); ?>"
                                                       data-on-image="<?php echo e($gatewayImageFullPath); ?>"
                                                       data-off-image="<?php echo e($gatewayImageFullPath); ?>"
                                                       data-cancel-button-text="<?php echo e(translate('Cancel')); ?>"
                                                       data-confirm-button-text="<?php echo e(translate('Ok')); ?>"
                                                >
                                                <span class="switcher_control <?php echo e(env('APP_ENV') == 'demo' ? 'disabled' : ''); ?>"></span>
                                            </label>
                                        <?php else: ?>
                                            <label class="switcher">
                                                <input type="checkbox"
                                                       <?php if($gateway['is_active']): echo 'checked'; endif; ?>
                                                       class="<?php echo e(env('APP_ENV') == 'demo' ? '' : 'update-status-modal'); ?> switcher_input no-visual"
                                                       id="<?php echo e($gateway->key_name); ?>"
                                                       data-url=""
                                                       data-on-title="<?php echo e(translate(':gateway can not be turned on', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                                       data-off-title="<?php echo e(translate(':gateway can not be turned on', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                                       data-on-description="<?php echo e(translate('Your current currency is not supported by this gateway')); ?>"
                                                       data-off-description="<?php echo e(translate('Your current currency is not supported by this gateway')); ?>"
                                                       data-on-image="<?php echo e($gatewayImageFullPath); ?>"
                                                       data-off-image="<?php echo e($gatewayImageFullPath); ?>"
                                                       data-cancel-button-text="<?php echo e(translate('Cancel')); ?>"
                                                       data-confirm-button-text="<?php echo e(translate('Ok')); ?>"
                                                >
                                                <span class="switcher_control <?php echo e(env('APP_ENV') == 'demo' ? 'disabled' : ''); ?>"></span>
                                            </label>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method_update')): ?>
                                    <button type="button" class="action-btn btn--danger"
                                            data-bs-toggle="offcanvas" data-bs-target="#offcanvas-<?php echo e($gateway->key_name); ?>">
                                        <span class="material-icons">settings</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center bg-white  pt-5 pb-5">
                            <img src="<?php echo e(asset('public/assets/admin-module')); ?>/img/payment-list-error.png" alt="error" class="w-100px mx-auto mb-3">
                            <p><?php echo e(translate('No Payment Method List')); ?></p>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__currentLoopData = $data['gateways']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo $__env->make("businesssettingsmodule::admin.configurations.third-party.partials.offcanvas-edit-digital-payment-method", ['gateway' => $gateway], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/payment/payment-digital.blade.php ENDPATH**/ ?>