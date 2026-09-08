<?php if($webPage == 'map-api'): ?>
    <div class="tab-content">
        <div class="tab-pane fade show active"
             id="map_api">
            <div class="pick-map mb-15 p-12 rounded d-flex flex-md-nowrap flex-wrap align-items-center gap-1 bg-primary bg-opacity-10">
                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/focus_mode.svg')); ?>" alt="focus mode icon">
                <p class="fz-12"> <span class="fw-semibold "><?php echo e(translate('Client Key')); ?> </span> <?php echo e(translate('should have enable map')); ?>  <span class="fw-semibold"><?php echo e(translate('Javascript API')); ?></span> <?php echo e(translate('and you can restrict it with http refer')); ?>  <span class="fw-semibold"><?php echo e(translate('Server Key')); ?></span><?php echo e(translate(' should have enable place api key and you can restrict it with ip You can use same api for both field without any restrictions.')); ?></p>
            </div>
            <div class="bg-warning bg-opacity-10 fs-12 p-12 rounded mb-15">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?php echo e(asset('public/assets/admin-module/img/icons/alert_info.svg')); ?>" alt="alert info icon">
                    <p class="fz-12 fw-normal"><?php echo e(translate('Without configuring this section map functionality will not work properly thus the whole system will not work as it planned')); ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center justify-content-between flex-sm-nowrap flex-wrap gap-3 mb-20">
                        <div>
                            <h4 class="page-title mb-1"><?php echo e(translate('Google Map API')); ?></h4>
                            <p class="mb-0 fz-12"><?php echo e(translate('Fill-up google APIs credentials to setup & active google map integration to your system.')); ?></p>
                        </div>
                        <button type="button" class="rounded transition text-nowrap fz-12 fw-semibold btn btn-primary__outline d-flex align-items-center gap-1 py-2 px-3" data-bs-toggle="modal" data-bs-target="#map__view">
                           <span class="material-symbols-outlined m-0">
                             location_on
                            </span>
                            <?php echo e(translate('Test Map View')); ?>

                        </button>
                    </div>
                    <form action="<?php echo e(route('admin.configuration.store-third-party-data')); ?>"
                          method="POST"
                          class="third-party-data-form"
                          enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input name="party_name" value="google_map" type="hidden">
                        <div class="discount-type body-bg rounded p-20 mb-20">
                            <div class="row g-4">
                                <div class="col-md-6 col-12">
                                    <div class="">
                                        <label class="mb-2 text-dark"><?php echo e(translate('Map API Key Server')); ?>

                                            <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="<?php echo e(translate('Private key for server-side map requests')); ?>"
                                            >info</i>
                                        </label>
                                        <input type="text" class="form-control"
                                               name="map_api_key_server"
                                               placeholder="<?php echo e(translate('Map API Key Server')); ?> *"
                                               required=""
                                               value="<?php echo e($data['map_api_key_server'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="">
                                        <label class="mb-2 text-dark"><?php echo e(translate('Map API Key Client')); ?>

                                            <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="<?php echo e(translate('Public key for loading maps in the browser')); ?>"
                                            >info</i>
                                        </label>
                                        <input type="text" class="form-control"
                                               name="map_api_key_client"
                                               placeholder="<?php echo e(translate('Map API Key Client')); ?> *"
                                               required=""
                                               value="<?php echo e($data['map_api_key_client'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_update')): ?>
                            <div class="d-flex justify-content-end trans3 mt-4">
                                <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                    <button type="reset" class="btn btn--secondary rounded"><?php echo e(translate('reset')); ?></button>
                                    <button type="submit" class="btn btn--primary demo_check rounded"><?php echo e(translate('update')); ?></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="map__view" tabindex="-1" aria-labelledby="map__viewLabel" aria-hidden="true">
        <?php if(env('APP_ENV') == 'demo' || ($data['map_api_key_client'] !== null && $data['map_api_key_server'] !== null)): ?>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-xl-4 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4><?php echo e(translate('Map View')); ?></h4>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="view-map">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d52858603.10913246!2d-161.47084896700602!3d36.039016616416845!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54eab584e432360b%3A0x1c3bb99243deb742!2sUnited%20States!5e0!3m2!1sen!2sbd!4v1746511941597!5m2!1sen!2sbd"
                                width="100%" height="280" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-xl-4 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4><?php echo e(translate('Map View')); ?></h4>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="view-map-error d-center py-5 px-3 body-bg rounded border">
                            <div class="boxes text-center">
                                <img src="<?php echo e(asset('public/assets/admin-module/img/map-error.png')); ?>" alt="">
                                <h5 class="my-3 fz-16 text-dark"><?php echo e(translate('404 Error')); ?></h5>
                                <p class="fz-14"><?php echo e(translate('Map is not found.')); ?> <?php echo e(translate('Ensure the Map API Key (Client and Server) is entered correctly.')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/map.blade.php ENDPATH**/ ?>