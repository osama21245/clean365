<?php
    $serviceLocationCustomer = $booking?->booking?->customer ?? $booking?->customer;
    $serviceLocationCustomerName = trim(($serviceLocationCustomer?->first_name ?? '') . ' ' . ($serviceLocationCustomer?->last_name ?? ''));
    $customer_name = $booking?->service_address?->contact_person_name
        ?: ($booking?->booking?->service_address?->contact_person_name
            ?: ($serviceLocationCustomerName !== '' ? $serviceLocationCustomerName : translate('No customer found')));
    $customer_phone = $booking?->service_address?->contact_person_number
        ?: ($booking?->booking?->service_address?->contact_person_number ?: $serviceLocationCustomer?->phone);
    $serviceLocationIsGuest = $booking?->booking?->is_guest ?? $booking?->is_guest;
?>

<div class="modal fade" id="serviceLocationModal--<?php echo e($booking['id']); ?>" tabindex="-1"
     aria-labelledby="serviceLocationModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="<?php echo e(route('admin.booking.change-service-location', [$booking['id']])); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h4 class="font-weight-bold mb-2"><?php echo e(translate('Change Service Location')); ?></h4>
                    <p class="text-muted"><?php echo e(translate('Select where to provide services for this booking. Contact the customer before making changes')); ?></p>

                    <div class="bg-light p-3 rounded">
                        <h6 class="font-weight-bold"><?php echo e(translate('Select Service Location')); ?></h6>
                        <div class="rbg-body border d-flex justify-content-between mt-2 p-3 rounded">
                            <div class="flex-grow-1">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="service_location" value="customer" id="customer_location"
                                    <?php echo e($booking->service_location == 'customer' ? 'checked' : ''); ?>>
                                    <label class="custom-control-label font-weight-bold" for="customer_location">
                                        <?php echo e(translate('At Customer Location')); ?>

                                        <p class="text-muted small mb-0"><?php echo e(translate('Provider has to go to customer place')); ?></p>
                                    </label>

                                </div>
                            </div>
                            <?php if($serviceAtProviderPlace == 1): ?>
                                <div class="flex-grow-1">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="service_location" value="provider" id="provider_location"
                                            <?php echo e($booking->service_location == 'provider' ? 'checked' : ''); ?>>
                                        <label class="custom-control-label font-weight-bold" for="provider_location">
                                            <?php echo e(translate('At Provider Location')); ?>

                                            <p class="text-muted small mb-0"><?php echo e(translate('Customer has to go to provider place')); ?></p>
                                        </label>

                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="customer-details mt-3">
                            <!-- Hidden Fields in Service Location Modal -->
                            <input type="hidden" name="contact_person_name" value="<?php echo e($booking->service_address->contact_person_name ?? ''); ?>">
                            <input type="hidden" name="contact_person_number" value="<?php echo e($booking->service_address->contact_person_number ?? ''); ?>">
                            <input type="hidden" name="address_label" value="<?php echo e($booking->service_address->label ?? ''); ?>">
                            <input type="hidden" name="address" value="<?php echo e($booking->service_address->address ?? ''); ?>">
                            <input type="hidden" name="latitude" value="<?php echo e($booking->service_address->latitude ?? ''); ?>">
                            <input type="hidden" name="longitude" value="<?php echo e($booking->service_address->longitude ?? ''); ?>">
                            <input type="hidden" name="city" value="<?php echo e($booking->service_address->city ?? ''); ?>">
                            <input type="hidden" name="street" value="<?php echo e($booking->service_address->street ?? ''); ?>">
                            <input type="hidden" name="zip_code" value="<?php echo e($booking->service_address->zip_code ?? ''); ?>">
                            <input type="hidden" name="country" value="<?php echo e($booking->service_address->country ?? ''); ?>">

                            <h6 class="font-weight-bold"><?php echo e(translate('Customer Details')); ?></h6>
                            <div class="media gap-2 flex-wrap p-3 rounded bg-card mt-3">
                                <?php if(!$serviceLocationIsGuest && $serviceLocationCustomer): ?>
                                    <img width="58" height="58"
                                         class="rounded-circle border border-white aspect-square object-fit-cover"
                                         src="<?php echo e($serviceLocationCustomer?->profile_image_full_path); ?>"
                                         alt="<?php echo e(translate('user_image')); ?>">
                                <?php else: ?>
                                    <img width="58" height="58"
                                         class="rounded-circle border border-white aspect-square object-fit-cover"
                                         src="<?php echo e(asset('public/assets/provider-module/img/user2x.png')); ?>"
                                         alt="<?php echo e(translate('user_image')); ?>">
                                <?php endif; ?>
                                    <div class="media-body d-flex justify-content-between">
                                        <div>
                                            <h5 class="c1 mb-3">
                                                <?php if(!$serviceLocationIsGuest && $serviceLocationCustomer): ?>
                                                    <span class="c1 updated_customer_name"><?php echo e(Str::limit($customer_name, 30)); ?></span>
                                                <?php else: ?>
                                                    <span class="updated_customer_name"><?php echo e(Str::limit($customer_name, 30)); ?></span>
                                                <?php endif; ?>
                                            </h5>
                                            <ul class="list-info">
                                                <?php if($customer_phone): ?>
                                                    <li>
                                                        <span class="material-icons">phone_iphone</span>
                                                        <a href="tel:<?php echo e($customer_phone); ?>" id="updated_customer_phone"><?php echo e($customer_phone); ?></a>
                                                    </li>
                                                <?php endif; ?>
                                                    <li>
                                                        <span class="material-icons">map</span>
                                                        <p id="customer_service_location" class="<?php echo e(empty($booking?->service_address?->address) ? 'text-danger' : ''); ?>">
                                                            <?php echo e(Str::limit($booking?->service_address?->address ?: translate('Customer Address required'), 100)); ?>

                                                        </p>
                                                    </li>

                                            </ul>
                                        </div>
                                        <div class="btn-group">
                                            <div data-bs-toggle="modal"
                                                 data-bs-target="#customerAddressModal--<?php echo e($booking['id']); ?>"
                                                 data-toggle="tooltip" data-placement="top">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="material-symbols-outlined">edit_square</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-end gap-3 border-0 pt-0 pb-4 m-4">
                                <button type="button" class="btn btn--secondary customer-address-reset-btn"><?php echo e(translate('Reset')); ?></button>
                                <button type="submit" class="btn btn--primary customer-address-update-btn" <?php echo e(empty($booking?->service_address?->address) ? 'disabled' : ''); ?>><?php echo e(translate('Update')); ?></button>
                            </div>
                        </div>

                        <div class="provider-details mt-3">
                            <h6 class="font-weight-bold"><?php echo e(translate('Provider Details')); ?></h6>
                            <?php if($booking->provider): ?>
                                <div class="media gap-2 flex-wrap p-3 rounded bg-card mt-3">
                                    <img width="58" height="58"
                                         class="rounded-circle border border-white aspect-square object-fit-cover"
                                         src="<?php echo e($booking?->provider?->logo_full_path); ?>"
                                         alt="<?php echo e(translate('provider')); ?>">
                                    <div class="media-body">
                                        <a href="<?php echo e(route('admin.provider.details', [$booking?->provider?->id, 'web_page' => 'overview'])); ?>">
                                            <h5 class="c1 mb-3">
                                                <?php echo e(Str::limit($booking->provider->company_name ?? translate('No provider found'), 30)); ?>

                                            </h5>
                                        </a>
                                        <ul class="list-info">
                                            <li>
                                                <span class="material-icons">phone_iphone</span>
                                                <a href="tel:<?php echo e($booking->provider->contact_person_phone ?? ''); ?>"><?php echo e($booking->provider->contact_person_phone ?? ''); ?></a>
                                            </li>
                                            <li>
                                                <span class="material-icons">map</span>
                                                <p><?php echo e(Str::limit($booking->provider->company_address ?? '', 100)); ?></p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="mt-2"><?php echo e(translate('The Service Location will be available after this booking assign to a provider')); ?></p>
                            <?php endif; ?>

                            <div class="modal-footer d-flex justify-content-end gap-3 border-0 pt-0 pb-4 m-4">
                                <button type="button" class="btn btn--secondary" data-bs-dismiss="modal" aria-label="Close"><?php echo e(translate('Close')); ?></button>
                                <button type="submit" class="btn btn--primary"><?php echo e(translate('Update')); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BookingModule/Resources/views/admin/booking/partials/details/_service-location-modal.blade.php ENDPATH**/ ?>