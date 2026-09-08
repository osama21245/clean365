<div class="modal fade" id="customerAddressModal--<?php echo e($booking['id']); ?>" tabindex="-1" aria-labelledby="customerAddressModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form class="flex-grow-1" id="customerAddressModalSubmit">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 class="font-weight-bold"><?php echo e(translate('Change Service Location')); ?></h4>

                    <div class="row mt-4">
                        <div class="col-md-6 col-12">
                            <div class="col-md-12 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="contact_person_name"
                                               placeholder="<?php echo e(translate('Contact Person Name')); ?> *"
                                               value="<?php echo e($booking->service_address?->contact_person_name); ?>" required>
                                        <label><?php echo e(translate('Contact Person Name')); ?> *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control"
                                               name="contact_person_number"
                                               id="contact_person_number"
                                               placeholder="<?php echo e(translate('Contact Person Number')); ?> *"
                                               value="<?php echo e($booking->service_address?->contact_person_number); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-12">
                                <?php echo $__env->make('adminmodule::admin.partials.map-picker', [
                                    'mapWrapperId' => 'booking-service-location-map-' . $booking['id'],
                                    'wrapperClass' => 'location_map_new',
                                    'mapId' => 'address_location_map_canvas',
                                    'searchInputId' => 'address_pac-input',
                                    'initialLat' => $booking->service_address?->lat ?? null,
                                    'initialLng' => $booking->service_address?->lon ?? null,
                                    'addressField' => 'address_address',
                                    'latitudeField' => 'address_latitude',
                                    'longitudeField' => 'address_longitude',
                                    'zoom' => 13,
                                    'modalTarget' => '#customerAddressModal--' . $booking['id'],
                                    'zones' => $booking->zone ? [$booking->zone] : [],
                                    'selectedZoneId' => $booking->zone_id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-12 row">
                            <div class="col-md-12 col-12">
                                <div class="mb-30">
                                    <select class="js-select theme-input-style w-100" name="address_label">
                                        <option selected disabled><?php echo e(translate('Select_address_label')); ?>*</option>
                                        <option value="home" <?php echo e($booking->service_address?->address_label == 'home' ? 'selected' : ''); ?>><?php echo e(translate('Home')); ?></option>
                                        <option value="office" <?php echo e($booking->service_address?->address_label == 'office' ? 'selected' : ''); ?>><?php echo e(translate('Office')); ?></option>
                                        <option value="others" <?php echo e($booking->service_address?->address_label == 'others' ? 'selected' : ''); ?>><?php echo e(translate('others')); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="address" id="address_address"
                                               placeholder="<?php echo e(translate('address')); ?> *"
                                               value="<?php echo e($booking->service_address?->address); ?>" required>
                                        <label><?php echo e(translate('address')); ?> *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="latitude" id="address_latitude"
                                               placeholder="<?php echo e(translate('lat')); ?> *"
                                               value="<?php echo e($booking->service_address?->lat); ?>" required readonly
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="<?php echo e(translate('Select from map')); ?>">
                                        <label><?php echo e(translate('lat')); ?> *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="longitude" id="address_longitude"
                                               placeholder="<?php echo e(translate('long')); ?> *"
                                               value="<?php echo e($booking->service_address?->lon); ?>" required readonly
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="<?php echo e(translate('Select from map')); ?>">
                                        <label><?php echo e(translate('long')); ?> *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="city"
                                               placeholder="<?php echo e(translate('city')); ?>"
                                               value="<?php echo e($booking->service_address?->city); ?>">
                                        <label><?php echo e(translate('city')); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="street"
                                               placeholder="<?php echo e(translate('street')); ?>"
                                               value="<?php echo e($booking->service_address?->street); ?>">
                                        <label><?php echo e(translate('street')); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="zip_code"
                                               placeholder="<?php echo e(translate('Zip Code')); ?>"
                                               value="<?php echo e($booking->service_address?->zip_code); ?>">
                                        <label><?php echo e(translate('Zip Code')); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="country"
                                               placeholder="<?php echo e(translate('country')); ?>"
                                               value="<?php echo e($booking->service_address?->country); ?>">
                                        <label><?php echo e(translate('country')); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-3 border-0 pt-0 pb-4 m-4">
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal" aria-label="Close">
                        <?php echo e(translate('Cancel')); ?></button>
                    <button type="submit" class="btn btn--primary"><?php echo e(translate('Update')); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BookingModule/Resources/views/admin/booking/partials/details/_update-customer-address-modal.blade.php ENDPATH**/ ?>