<?php if($webPage == 'storage_connection'): ?>
    <div class="tab-content">
        <?php
            $credentialsJson = empty($data['s3_storage_credentials']) ? '{}' : $data['s3_storage_credentials'];
            $credentials = json_decode($credentialsJson, true);
            $isS3FieldEmpty = is_array($credentials) && count(array_filter($credentials, fn($v) => $v !== null && $v !== '')) === 0;
        ?>
        <div class="tab-pane fade <?php echo e($webPage == 'storage_connection' ? 'show active' : ''); ?>" id="storage_connection">
            <div class="pick-map mb-3 p-12 rounded d-flex flex-md-nowrap flex-wrap align-items-center gap-1 bg-primary bg-opacity-10">
                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/focus_mode.svg')); ?>" alt="focus mode icon">
                <p class="fz-12"><?php echo e(translate('You can manage all your storage files from')); ?>  <a <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gallery_view')): ?> href="<?php echo e(route('admin.business-settings.get-gallery-setup')); ?>" <?php endif; ?> target="_blank" class="text-primary fw-semibold text-decoration-underline"> <?php echo e(translate('Gallery')); ?></a></p>
            </div>

            <div class="card mb-20">
                <div class="card-body p-20">
                    <div class="row g-lg-4 g-4 align-items-center">
                        <div class="col-lg-3">
                            <h3 class="mb-2"><?php echo e(translate('Storage Connection')); ?></h3>
                            <p class="fz-12 mb-xl-3 mb-xxl-4 mb-3"><?php echo e(translate('Choose the SMS model you want to use for OTP & Other SMS')); ?></p>
                            <?php if($isS3FieldEmpty): ?>
                                <div class="bg-warning bg-opacity-10 fs-12 p-12 rounded">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo e(asset('public/assets/admin-module/img/icons/alert_info.svg')); ?>" alt="alert info icon">
                                        <p class="fz-12 fw-normal"><?php echo e(translate('3rd Party storage is not set up yet.')); ?> <?php echo e(translate('Please configure it first to ensure it works properly.')); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-9">
                            <div class="bg-light rounded-2 p-20">
                                <label class="text-dark mb-3 d-flex align-items-center gap-1"><?php echo e(translate('Select Business Model')); ?>

                                    <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                       data-bs-placement="top"
                                       title="<?php echo e(translate('Select a storage model to manage and store files properly')); ?>"
                                    >info</i>
                                </label>
                                <div class="bg-white rounded-2 p-16">
                                    <div class="row g-xl-4 g-3">
                                        <div class="col-md-6">
                                            <div class="custom-radio">
                                                <input type="radio"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="<?php echo e(translate('Select storage connection model')); ?>"
                                                       data-name="storage_connection_type"
                                                       id="radio-option-1"
                                                       value="local"
                                                       <?php if(old('storage_connection_type', $data['storage_connection_type']) == 'local' || empty(old('storage_connection_type', $data['storage_connection_type']))): echo 'checked'; endif; ?>
                                                       class="update-status-modal"
                                                       data-url="<?php echo e(route('admin.configuration.change-storage-connection-type')); ?>"
                                                       data-on-title="<?php echo e(translate('Do you want to switch 3rd party storage to local storage ?')); ?>"
                                                       data-off-title="<?php echo e(translate('Do you want to switch 3rd party storage to local storage ?')); ?>"
                                                       data-on-description="<?php echo e(translate('If you switch this newly uploaded created files & data will store to local storage')); ?>"
                                                       data-off-description="<?php echo e(translate('If you switch this newly uploaded created files & data will store to local storage')); ?>"
                                                       data-on-image="<?php echo e(asset('public/assets/admin-module/img/icons/swap.svg')); ?>"
                                                       data-off-image="<?php echo e(asset('public/assets/admin-module/img/icons/swap.svg')); ?>"
                                                >
                                                <label for="radio-option-1">
                                                    <h5 class="mb-1"><?php echo e(translate('Local Storage')); ?></h5>
                                                    <p class="fz-12 max-w-250"><?php echo e(translate('If enable this, newly uploaded/created files and data will store to local storage.')); ?></p>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6" <?php if($isS3FieldEmpty): ?>
                                            data-bs-toggle="tooltip"
                                             data-bs-placement="top"
                                             title="<?php echo e(translate('3rd_party_storage_is_currently_disabled_please_configure_credentials_data_first')); ?>"
                                        <?php endif; ?>>
                                            <div class="custom-radio <?php echo e($isS3FieldEmpty ? 'disabled' : ''); ?>" >
                                                <input type="radio"
                                                       data-name="storage_connection_type"
                                                       id="radio-option-2"
                                                       value="s3"
                                                       <?php if(old('storage_connection_type', $data['storage_connection_type']) == 's3'): echo 'checked'; endif; ?>
                                                       class="update-status-modal"
                                                       data-url="<?php echo e(route('admin.configuration.change-storage-connection-type')); ?>"
                                                       data-on-title="<?php echo e(translate('Do you want to switch local storage to 3rd party storage?')); ?>"
                                                       data-off-title="<?php echo e(translate('Do you want to switch local storage to 3rd party storage?')); ?>"
                                                       data-on-description="<?php echo e(translate('If you switch this newly uploaded created files & data will store to 3rd party storage')); ?>"
                                                       data-off-description="<?php echo e(translate('If you switch this newly uploaded created files & data will store to 3rd party storage')); ?>"
                                                       data-on-image="<?php echo e(asset('public/assets/admin-module/img/svg/storage-switch.svg')); ?>"
                                                       data-off-image="<?php echo e(asset('public/assets/admin-module/img/svg/storage-switch.svg')); ?>"
                                                >
                                                <label for="radio-option-2">
                                                    <h5 class="mb-1"><?php echo e(translate('3rd Party Storage')); ?></h5>
                                                    <p class="fz-12 max-w-250"><?php echo e(translate('If enable this, newly uploaded/created files and data will store to 3rd party storage.')); ?></p></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form action="<?php echo e(route('admin.configuration.update-storage-connection')); ?>" id="update-storage-form" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="mb-20">
                            <h4 class="mb-1"><?php echo e(translate('S3 Credential')); ?></h4>
                            <p class="fs-12">
                                <?php echo e(translate('The Access Key ID is a publicly accessible identifier used to authenticate requests to S3.')); ?>

                                <a href="https://aws.amazon.com/s3/" target="_blank" class="c1 text-decoration-underline" data-bs-toggle="tooltip" title=""><?php echo e(translate('Learn more')); ?></a>
                            </p>
                        </div>
                        <div class="rounded p-20 body-bg">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                 <div class="w-400">
                                    <div class="min-w180 mb-1"><strong><?php echo e(translate('Key')); ?></strong></div>
                                    <p class="fz-12"><?php echo e(translate('Your unique public key used to authenticate s3 requests.')); ?></p>
                                </div>
                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                    <input type="text" name="key" class="form-control" value="<?php echo e(old('key', $credentials['key'] ?? '')); ?>" placeholder="<?php echo e(translate('Enter key')); ?>" >
                                </div>
                            </div>
                        </div>
                        <div class="rounded p-20 body-bg mt-3">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                 <div class="w-400">
                                    <div class="min-w180 mb-1"><strong><?php echo e(translate('Secret Credential')); ?></strong></div>
                                    <p class="fz-12"><?php echo e(translate('The private key paired with your access key for secure authentication.')); ?></p>
                                </div>
                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                    <input type="text" name="secret" class="form-control" value="<?php echo e(old('secret', $credentials['secret'] ?? '')); ?>" placeholder="<?php echo e(translate('Enter secret credential')); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="rounded p-20 body-bg mt-3">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                  <div class="w-400">
                                    <div class="min-w180 mb-1"><strong><?php echo e(translate('Region')); ?></strong></div>
                                    <p class="fz-12"><?php echo e(translate('The aws region where your s3 bucket is hosted.')); ?></p>
                                </div>
                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                    <input type="text" name="region" class="form-control" value="<?php echo e(old('region', $credentials['region'] ?? '')); ?>" placeholder="<?php echo e(translate('Enter region')); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="rounded p-20 body-bg mt-3">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                  <div class="w-400">
                                    <div class="min-w180 mb-1"><strong><?php echo e(translate('Bucket')); ?></strong></div>
                                    <p class="fz-12"><?php echo e(translate('The name of the s3 bucket where your files will be stored.')); ?></p>
                                </div>
                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                    <input type="text" name="bucket" class="form-control" value="<?php echo e(old('bucket', $credentials['bucket'] ?? '')); ?>" placeholder="<?php echo e(translate('Enter bucket')); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="rounded p-20 body-bg mt-3">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                  <div class="w-400">
                                    <div class="min-w180 mb-1"><strong><?php echo e(translate('Url')); ?></strong></div>
                                    <p class="fz-12"><?php echo e(translate('The base url used to access your s3 bucket.')); ?></p>
                                </div>
                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                    <input type="text" name="url" class="form-control" value="<?php echo e(old('url', $credentials['url'] ?? '')); ?>" placeholder="<?php echo e(translate('Enter url')); ?>" >
                                </div>
                            </div>
                        </div>
                        <div class="rounded p-20 body-bg mt-3">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                  <div class="w-400">
                                    <div class="min-w180 mb-1"><strong><?php echo e(translate('Endpoint')); ?></strong></div>
                                    <p class="fz-12"><?php echo e(translate('The custom endpoint for your s3-compatible storage (optional for aws s3).')); ?></p>
                                </div>
                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                    <input type="text" name="endpoint" class="form-control" value="<?php echo e(old('endpoint', $credentials['endpoint'] ?? '')); ?>" placeholder="<?php echo e(translate('Enter endpoint')); ?>" required>
                                </div>
                            </div>
                        </div>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_update')): ?>
                            <div class="d-flex justify-content-end trans3 mt-4">
                                <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                    <div class="d-flex justify-content-end gap-3">
                                        <button type="reset" class="btn btn--secondary rounded"><?php echo e(translate('Reset')); ?></button>
                                        <button type="submit" class="btn d-flex align-items-center gap-2 btn--primary demo_check rounded" data-bs-toggle="modal" data-bs-target="#confirmation">
                                            <img src="<?php echo e(asset('public/assets/admin-module/img/icons/save-icon.svg')); ?>" alt="save icon">
                                            <?php echo e(translate('Save Information')); ?>

                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/storage-connection.blade.php ENDPATH**/ ?>