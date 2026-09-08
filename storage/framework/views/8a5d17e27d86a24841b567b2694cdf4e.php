<?php if($webPage == 'app_settings'): ?>
    <div class="tab-content">
        <div class="tab-pane fade show active"
             id="app_settings">
            <div class="bg-warning bg-opacity-10 fs-12 p-12 text-dark rounded mb-15">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="<?php echo e(asset('public/assets/admin-module/img/icons/alert_info.svg')); ?>" alt="alert info icon">
                    <p class="fz-12 fw-medium"><?php echo e(translate('On this page you can force users to update to the latest app version.')); ?> <?php echo e(translate('Please input proper data for the app link and versions.')); ?></p>
                </div>
                <ul class="m-0 ps-20 d-flex flex-column gap-1 text-dark">
                    <li><?php echo e(translate('Some time older version app can’t work properly and crash when start the app.')); ?></li>
                    <li><?php echo e(translate('This section may help user to get the update features in their app.')); ?></li>
                </ul>
            </div>
            <div class="card mb-15">
                <div class="p-20 border-bottom">
                    <h4 class="page-title mb-1">
                        <?php echo e(translate('Customer app version control')); ?>

                    </h4>
                    <p class="fz-12"><?php echo e(translate('Here you setup your Customer app version & app download URL')); ?></p>
                </div>
                <div class="card-body p-20">
                    <form action="<?php echo e(route('admin.configuration.set-app-settings')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="discount-type mb-20">
                            <div class="row g-lg-4 g-3">
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-20">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/google-play-icon.png')); ?>" alt="">  <?php echo e(translate('For android')); ?>

                                    </div>
                                    <div class="body-bg rounded p-20">
                                        <div class="mb-xl-4 mb-3">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Minimum User App Version for Force Update (Android)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Lowest Android app version allowed.')); ?> <?php echo e(translate('Users below this version must update.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="text" class="form-control" name="min_version_for_android" placeholder="<?php echo e(translate('Min Version For Android')); ?> *" required=""
                                                   value="<?php echo e($data['customer_app_settings']['min_version_for_android'] ?? ''); ?>"
                                                   pattern="^\d+(\.\d+){0,2}$"
                                                   title="Minimum User App Version for Force Update (Android)">
                                        </div>
                                        <div class="">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Download URL for User App (Android)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Link to download the Android app ')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="url" class="form-control" name="download_link_for_android" placeholder="<?php echo e(translate('Download Url')); ?> *" required="" value="<?php echo e($data['customer_app_settings']['download_link_for_android'] ?? ''); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-20">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/ios-icon.png')); ?>" alt="">  <?php echo e(translate('For ios')); ?>

                                    </div>
                                    <div class="body-bg rounded p-20">
                                        <div class="mb-xl-4 mb-3">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Minimum User App Version for Force Update (ios)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Lowest iOS app version allowed.')); ?> <?php echo e(translate('Users below this version must update.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="min_version_for_ios"
                                                   placeholder="<?php echo e(translate('min_version_for_IOS')); ?> *"
                                                   required=""
                                                   value="<?php echo e($data['customer_app_settings']['min_version_for_ios'] ?? ''); ?>"
                                                   pattern="^\d+(\.\d+){0,2}$"
                                                   title="Please enter a version number like 1.0.0 with a maximum of two dots.">
                                        </div>
                                        <div class="">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Download URL for User App (ios)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Link to download the iOS app from the App Store.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="url" class="form-control" name="download_link_for_ios" placeholder="<?php echo e(translate('Download Url')); ?> *" required="" value="<?php echo e($data['customer_app_settings']['download_link_for_ios'] ?? ''); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <input name="app_type" value="customer"
                                       class="hide-div">
                            </div>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_update')): ?>
                            <div class="d-flex justify-content-end gap-xl-3 gap-2">
                                <button type="reset" class="btn btn--secondary rounded"><?php echo e(translate('reset')); ?></button>
                                <button type="submit" class="btn btn--primary demo_check rounded"><?php echo e(translate('update')); ?></button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card mb-15">
                <div class="p-20 border-bottom">
                    <h4 class="page-title mb-1">
                        <?php echo e(translate('Provider app version control')); ?>

                    </h4>
                    <p class="fz-12"><?php echo e(translate('Here you setup your Vendor app version & app download URL')); ?></p>
                </div>
                <div class="card-body p-20">
                    <form action="<?php echo e(route('admin.configuration.set-app-settings')); ?>" method="POST" id="google-map-update-form" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="discount-type mb-20">
                            <div class="row g-lg-4 g-3">
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-20">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/google-play-icon.png')); ?>" alt="">  <?php echo e(translate('For android')); ?>

                                    </div>
                                    <div class="body-bg rounded p-20">
                                        <div class="mb-xl-4 mb-3">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Minimum User App Version for Force Update (Android)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Lowest Android app version allowed.')); ?> <?php echo e(translate('Users below this version must update.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="text" class="form-control" name="min_version_for_android" placeholder="<?php echo e(translate('Min Version For Android')); ?> *" required=""
                                                   value="<?php echo e($data['provider_app_settings']['min_version_for_android'] ?? ''); ?>"
                                                   pattern="^\d+(\.\d+){0,2}$"
                                                   title="Minimum User App Version for Force Update (Android)">
                                        </div>
                                        <div class="">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Download URL for User App (Android)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Link to download the Android app ')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="url" class="form-control" name="download_link_for_android" placeholder="<?php echo e(translate('Download Url')); ?> *" required="" value="<?php echo e($data['provider_app_settings']['download_link_for_android'] ?? ''); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-20">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/ios-icon.png')); ?>" alt="">  <?php echo e(translate('For ios')); ?>

                                    </div>
                                    <div class="body-bg rounded p-20">
                                        <div class="mb-xl-4 mb-3">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Minimum User App Version for Force Update (ios)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Lowest iOS app version allowed.')); ?> <?php echo e(translate('Users below this version must update.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="min_version_for_ios"
                                                   placeholder="<?php echo e(translate('min_version_for_IOS')); ?> *"
                                                   required=""
                                                   value="<?php echo e($data['provider_app_settings']['min_version_for_ios'] ?? ''); ?>"
                                                   pattern="^\d+(\.\d+){0,2}$"
                                                   title="Please enter a version number like 1.0.0 with a maximum of two dots.">
                                        </div>
                                        <div class="">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Download URL for User App (ios)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Link to download the iOS app from the App Store.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="url" class="form-control" name="download_link_for_ios" placeholder="<?php echo e(translate('Download Url')); ?> *" required="" value="<?php echo e($data['provider_app_settings']['download_link_for_ios'] ?? ''); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <input name="app_type" value="provider"
                                       class="hide-div">
                            </div>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_update')): ?>
                            <div class="d-flex justify-content-end gap-xl-3 gap-2">
                                <button type="reset" class="btn btn--secondary rounded"><?php echo e(translate('reset')); ?></button>
                                <button type="submit" class="btn btn--primary demo_check rounded"><?php echo e(translate('update')); ?></button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="p-20 border-bottom">
                    <h4 class="page-title mb-1">
                        <?php echo e(translate('Delivery man app version control')); ?>

                    </h4>
                    <p class="fz-12"><?php echo e(translate('Here you setup your Delivery Man app version & app download URL')); ?></p>
                </div>
                <div class="card-body p-20">
                    <form action="<?php echo e(route('admin.configuration.set-app-settings')); ?>" method="POST" id="google-map-update-form" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="discount-type mb-20">
                            <div class="row g-lg-4 g-3">
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-20">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/google-play-icon.png')); ?>" alt="">  <?php echo e(translate('For android')); ?>

                                    </div>
                                    <div class="body-bg rounded p-20">
                                        <div class="mb-xl-4 mb-3">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Minimum User App Version for Force Update (Android)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Lowest Android app version allowed.')); ?> <?php echo e(translate('Users below this version must update.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="text" class="form-control" name="min_version_for_android" placeholder="<?php echo e(translate('Min Version For Android')); ?> *" required=""
                                                   value="<?php echo e($data['serviceman_app_settings']['min_version_for_android'] ?? ''); ?>"
                                                   pattern="^\d+(\.\d+){0,2}$"
                                                   title="Minimum User App Version for Force Update (Android)">
                                        </div>
                                        <div class="">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Download URL for User App (Android)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Link to download the Android app ')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="url" class="form-control" name="download_link_for_android" placeholder="<?php echo e(translate('Download Url')); ?> *" required="" value="<?php echo e($data['serviceman_app_settings']['download_link_for_android'] ?? ''); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-20">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/ios-icon.png')); ?>" alt="">  <?php echo e(translate('For ios')); ?>

                                    </div>
                                    <div class="body-bg rounded p-20">
                                        <div class="mb-xl-4 mb-3">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Minimum User App Version for Force Update (ios)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Lowest iOS app version allowed.')); ?> <?php echo e(translate('Users below this version must update.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="min_version_for_ios"
                                                   placeholder="<?php echo e(translate('min_version_for_IOS')); ?> *"
                                                   required=""
                                                   value="<?php echo e($data['serviceman_app_settings']['min_version_for_ios'] ?? ''); ?>"
                                                   pattern="^\d+(\.\d+){0,2}$"
                                                   title="Please enter a version number like 1.0.0 with a maximum of two dots.">
                                        </div>
                                        <div class="">
                                            <label class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Download URL for User App (ios)')); ?>

                                                <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(translate('Link to download the iOS app from the App Store.')); ?>"
                                                >info</i>
                                            </label>
                                            <input type="url" class="form-control" name="download_link_for_ios" placeholder="<?php echo e(translate('Download Url')); ?> *" required="" value="<?php echo e($data['serviceman_app_settings']['download_link_for_ios'] ?? ''); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <input name="app_type" value="serviceman"
                                       class="hide-div">
                            </div>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_update')): ?>
                            <div class="d-flex justify-content-end gap-xl-3 gap-2">
                                <button type="reset" class="btn btn--secondary rounded"><?php echo e(translate('reset')); ?></button>
                                <button type="submit" class="btn btn--primary demo_check rounded"><?php echo e(translate('update')); ?></button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/app-settings.blade.php ENDPATH**/ ?>