<form action="<?php echo e(route('admin.configuration.payment-set')); ?>" method="post" id="<?php echo e($gateway->key_name); ?>-form" enctype="multipart/form-data" class="third-party-data-form">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="offcanvas offcanvas-end offcanvas-cus-sm" tabindex="-1" id="offcanvas-<?php echo e($gateway->key_name); ?>" aria-labelledby="offcanvas-<?php echo e(str_replace('_',' ',$gateway->key_name)); ?>-Label">
        <div class="offcanvas-header py-md-4 py-3">
            <h2 class="mb-0">
                <?php echo e(translate('Setup: :gateway', ['gateway' => ucwords(str_replace('_',' ',$gateway->key_name))])); ?>

            </h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body bg-white">
            <div class="body-bg rounded p-20 mb-20">
                <div class="mb-15">
                    <h4 class="mb-1"><?php echo e(str_replace('_',' ', $gateway->key_name)); ?></h4>
                    <p class="fz-12"><?php echo e(translate('If you turn off customer can`t pay through this payment gateway.')); ?></p>
                </div>
                <div class="border rounded py-3 px-3 bg-white d-flex align-items-center justify-content-between">
                    <h5 class="fw-normal"><?php echo e(translate('Status')); ?></h5>
                    <?php ($gatewayImageFullPath = getPaymentGatewayImageFullPath(key: $gateway->key_name, settingsType: $gateway->settings_type, defaultPath: 'public/assets/admin-module/img/placeholder.png')); ?>
                    <?php ($additionalData = $gateway['additional_data'] != null ? json_decode($gateway['additional_data']) : []); ?>

                    <?php if(($gateway->is_active == 0 && checkCurrency($gateway->key_name, 'payment_gateway')) || ($gateway->is_active == 1)): ?>
                        <label class="switcher">
                            <input type="checkbox"
                                   name="status"
                                   <?php if($gateway->is_active): echo 'checked'; endif; ?>
                                   class="update-status-modal switcher_input"
                                   data-id="<?php echo e($gateway->key_name); ?>"
                                   data-url=""
                                   data-on-title="<?php echo e(translate('Want to turn on :gateway as the digital payment method?', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                   data-off-title="<?php echo e(translate('Want to turn off :gateway as the digital payment method?', ['gateway' => str_replace('_',' ',strtoupper($gateway->key_name))])); ?>"
                                   data-on-description="<?php echo e(translate('if_enabled_customers_can_use_this_payment_method')); ?>"
                                   data-off-description="<?php echo e(translate('if_disabled_this_payment_method_will_be_hidden_from_the_checkout_page')); ?>"
                                   data-on-image="<?php echo e($gatewayImageFullPath); ?>"
                                   data-off-image="<?php echo e($gatewayImageFullPath); ?>"
                            >
                            <span class="switcher_control"></span>
                        </label>
                    <?php else: ?>
                        <label class="switcher">
                            <input type="checkbox"
                                   <?php if($gateway['is_active']): echo 'checked'; endif; ?>
                                   class="update-status-modal switcher_input no-visual"
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
                            <span class="switcher_control"></span>
                        </label>
                    <?php endif; ?>

                </div>
            </div>
            <div class="body-bg rounded-2 p-20 mb-20">
                <div class="boxes">
                    <div class="mb-20 text-start">
                        <h5 class="fz-16 mb-1"><?php echo e(translate('Choose Logo')); ?> <span class="text-danger">*</span></h5>
                        <p class="fz-12"><?php echo e(translate('It will show in website & app.')); ?></p>
                    </div>
                    <div class="global-image-upload position-relative max-w-100 overflow-hidden bg-white border-dashed rounded-2 mx-auto mb-20 ratio-3-1 h-100px d-center <?php echo e(isset($gatewayImageFullPath) ? 'has-image' : ''); ?>">
                        <input type="file" name="gateway_image" accept="image/png, image/jpeg, image/jpg" <?php echo e(isset($gatewayImageFullPath) ? '' : 'required'); ?> style="position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                        <div class="global-upload-box <?php echo e(isset($gatewayImageFullPath) ? 'd-none' : ''); ?>">
                            <div class="upload-content text-center">
                                <span class="material-symbols-outlined placeholder-icon mb-1 text-primary">photo_camera</span>
                                <span class="fz-10 d-block">Add image</span>
                            </div>
                        </div>
                        <img class="global-image-preview <?php echo e(isset($gatewayImageFullPath) ? '' : 'd-none'); ?>" src="<?php echo e($gatewayImageFullPath); ?>" alt="Preview" style="max-height: 100%; max-width: 100%;" />
                        <div class="overlay-icons <?php echo e(isset($gatewayImageFullPath) ? '' : 'd-none'); ?>">
                            <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover edit-icon" title="Edit">
                                <span class="material-icons">edit</span>
                            </button>
                        </div>
                        <div class="image-file-name d-none mt-2 text-center text-muted" style="font-size: 12px;"></div>
                    </div>
                    <p class="fz-12 mt-lg-4 mt-3 text-center">
                        <?php echo e(translate('Image format - :formats', ['formats' => implode(', ', array_column(IMAGEEXTENSION, 'key'))])); ?>

                        <?php echo e(translate('Image Size - maximum size :size', ['size' => readableUploadMaxFileSize('image')])); ?>

                        <?php echo e(translate('Image Ratio - :ratio', ['ratio' => '3:1'])); ?>

                    </p>
                </div>
            </div>
            <div class="body-bg rounded-2 p-20 d-flex flex-column gap-lg-4 gap-3">
                <input type="hidden" name="gateway" value="<?php echo e($gateway->key_name); ?>">
                <div>
                    <div class="mb-2 text-dark"><?php echo e(translate('Choose Use Type')); ?>

                        <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           data-bs-html="true"
                           data-bs-title="<div><?php echo e(translate('when_select_live_option')); ?> : <?php echo e(translate('during_use_this_from_website_or_app_need_real_required_data')); ?>. <?php echo e(translate('other_wise_this_gateway_can_not_work')); ?>.</div>
                                        <div class='p-2'></div>
                                        <div><?php echo e(translate('when_select_test_option')); ?> : <?php echo e(translate('during_use_this_from_website_or_app_use_fake_required_data_to_test_payment_gateway_work_properly_or_not')); ?></div>"
                        >info</i>
                    </div>
                    <?php ($mode = $gateway->live_values['mode']); ?>
                    <div class="border setup-box p-12 rounded d-flex align-items-center gap-xl-5 gap-3">
                        <div class="custom-radio w-50">
                            <input type="radio" id="live-<?php echo e($gateway->key_name); ?>" name="mode" value="live" <?php echo e($mode == 'live' ? 'checked' : ''); ?> required>
                            <label for="live-<?php echo e($gateway->key_name); ?>" class="fz-14 text-dark"><?php echo e(translate('Live')); ?></label>
                        </div>
                        <div class="custom-radio w-50">
                            <input type="radio" id="test-<?php echo e($gateway->key_name); ?>" name="mode" value="test" <?php echo e($mode == 'test' ? 'checked' : ''); ?> required>
                            <label for="test-<?php echo e($gateway->key_name); ?>" class="fz-14 text-dark"><?php echo e(translate('Test')); ?></label>
                        </div>
                    </div>
                </div>
                <?php if($gateway->key_name === 'paystack'): ?>
                    <?php ($skip=['gateway', 'mode', 'status', 'supported_country', 'callback_url']); ?>
                <?php else: ?>
                    <?php ($skip=['gateway','mode','status', 'supported_country']); ?>
                <?php endif; ?>
                <?php $__currentLoopData = $gateway->live_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gatewayKey => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!in_array($gatewayKey , $skip)): ?>
                        <div>
                            <div class="mb-2 text-dark">
                                <?php echo e(ucwords(str_replace('_',' ',$gatewayKey))); ?>

                                <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control" name="<?php echo e($gatewayKey); ?>" placeholder="<?php echo e(ucwords(str_replace('_',' ',$gatewayKey))); ?> *"  value="<?php echo e(env('APP_ENV') == 'demo' ? '' : $value); ?>" required>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <div class="mb-2 text-dark">
                        <?php echo e(translate('payment_gateway_title')); ?>

                        <span class="text-danger">*</span>
                    </div>
                    <input type="text" class="form-control" name="gateway_title" placeholder="<?php echo e(translate('Payment Gateway Title')); ?> *"  value="<?php echo e($additionalData != null ? $additionalData->gateway_title : ''); ?>" required>
                </div>
            </div>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method_update')): ?>
            <div class="offcanvas-footer border-top">
                <div class="d-flex justify-content-center gap-2 bg-white px-3 py-sm-3 py-2">
                    <button class="btn btn--secondary w-100 rounded h-45" type="reset"><?php echo e(translate('Reset')); ?></button>
                    <button class="btn btn--primary w-100 rounded h-45 demo_check" type="submit"><?php echo e(translate('Save')); ?></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</form>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/partials/offcanvas-edit-digital-payment-method.blade.php ENDPATH**/ ?>