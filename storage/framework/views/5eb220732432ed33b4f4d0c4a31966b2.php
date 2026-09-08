<div class="modal fade" id="serviceUpdateModal--<?php echo e($booking['id']); ?>" tabindex="-1"
     aria-labelledby="serviceUpdateModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header px-4 pt-4 border-0 pb-1">
                <h3 class="text-capitalize"><?php echo e(translate('update_booking_list')); ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="mb-30" data-bs-toggle="tooltip" data-bs-placement="top"
                             title="<?php echo e(translate('Can not change Category')); ?>">
                            <select class="theme-input-style w-100 disabled" id="category_selector__select"
                                    name="category_id" readonly disabled>
                                <option value="<?php echo e($category?->id); ?>" selected><?php echo e($category?->name); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="mb-30" data-bs-toggle="tooltip" data-bs-placement="top"
                             title="<?php echo e(translate('Can not change Sub Category')); ?>">
                            <select class="theme-input-style w-100 disabled" id="sub_category_selector__select"
                                    name="sub_category_id" readonly disabled>
                                <option value="<?php echo e($subCategory?->id); ?>" selected><?php echo e($subCategory?->name); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="mb-30">
                            <select class="theme-input-style w-100" id="service_selector__select" name="service_id"
                                    required>
                                <option value="" selected disabled><?php echo e(translate('Select Service')); ?></option>
                                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($service->id); ?>"><?php echo e($service->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="mb-30">
                            <select class="theme-input-style w-100" id="service_variation_selector__select"
                                    name="variant_key" required>
                                <option selected disabled><?php echo e(translate('Select Service Variant')); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="mb-30">
                            <div class="form-floating">
                                <input type="number" class="form-control" name="service_quantity" id="service_quantity"
                                       placeholder="<?php echo e(translate('Service Quantity')); ?>" min="1"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                                <label><?php echo e(translate('Service Quantity')); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="booking_id" value="<?php echo e($booking->id); ?>">
                        <div class="d-flex gap-3 justify-content-end mb-4">
                            <button type="reset" class="btn btn--secondary"><?php echo e(translate('reset')); ?></button>
                            <button type="submit" class="btn btn--primary"
                                    id="add-service"><?php echo e(translate('Add Service')); ?></button>
                        </div>
                    </div>
                </div>

                <form action="<?php echo e(route('admin.booking.service.update_booking_service')); ?>" method="POST"
                      id="booking-edit-table">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0" id="service-edit-table">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('put'); ?>
                            <thead>
                            <tr>
                                <th class="ps-lg-3"><?php echo e(translate('Service')); ?></th>
                                <th><?php echo e(translate('Price') . ' (' . currency_symbol() . ')'); ?></th>
                                <th class="text-center"><?php echo e(translate('Qty')); ?></th>
                                <th><?php echo e(translate('Discount') . ' (' . currency_symbol() . ')'); ?></th>
                                <th><?php echo e(translate('Total') . ' (' . currency_symbol() . ')'); ?></th>
                                <th class="text-center"><?php echo e(translate('Action')); ?></th>
                            </tr>
                            </thead>

                            <tbody id="service-edit-tbody">
                            <?php ($sub_total=0); ?>
                            <?php $__empty_1 = true; $__currentLoopData = $booking->detail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr id="service-row--<?php echo e($detail?->variant_key); ?>">
                                    <td class="text-wrap ps-lg-3">
                                        <?php if(isset($detail->service)): ?>
                                            <div class="d-flex flex-column">
                                                <a href="<?php echo e(route('admin.service.detail',[$detail->service->id])); ?>"
                                                   class="fw-bold"><?php echo e(Str::limit($detail->service->name, 30)); ?></a>
                                                <div><?php echo e(Str::limit($detail ? $detail->variant_key : '', 50)); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span
                                                class="badge badge-pill badge-danger"><?php echo e(translate('Service_unavailable')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td id="service-cost-<?php echo e($detail?->variant_key); ?>"><?php echo e(with_currency_symbol($detail->service_cost)); ?></td>
                                    <td>
                                        <input type="number" min="1" name="qty[]" class="form-control booking-service-qty-input dark-color-bo m-auto"
                                               id="qty-<?php echo e($detail?->variant_key); ?>" value="<?php echo e($detail->quantity); ?>"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                               readonly>
                                    </td>
                                    <td id="discount-amount-<?php echo e($detail?->variant_key); ?>">
                                        <span class="discount-amount-value"><?php echo e($detail->discount_amount); ?></span>
                                        <br><span class="fz-12 text-capitalize discount-type-label"><?php echo e($detail->discount_amount > 0 ? translate('service') : ''); ?></span>
                                    </td>
                                    <td id="total-cost-<?php echo e($detail?->variant_key); ?>"><?php echo e($detail->total_cost); ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <span class="material-icons text-danger cursor-pointer remove-service-row"
                                                  data-row="service-row--<?php echo e($detail?->variant_key); ?>">delete
                                            </span>
                                        </div>
                                    </td>
                                    <input type="hidden" name="service_ids[]" value="<?php echo e($detail->service_id); ?>">
                                    <input type="hidden" name="variant_keys[]" value="<?php echo e($detail->variant_key); ?>">
                                </tr>
                                <?php ($sub_total += $detail->service_cost*$detail->quantity); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 6,
                                    'variant' => 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endif; ?>
                            <input type="hidden" name="zone_id" value="<?php echo e($booking->zone_id); ?>">
                            <input type="hidden" name="booking_id" value="<?php echo e($booking->id); ?>">
                            </tbody>
                        </table>
                    </div>
                </form>

            </div>
            <div class="modal-footer d-flex justify-content-end gap-3 border-0 pt-0 pb-4">
                <button type="button" class="btn btn--secondary" data-bs-dismiss="modal"
                        aria-label="Close"><?php echo e(translate('Cancel')); ?></button>
                <button type="submit" class="btn btn--primary"
                        form="booking-edit-table"><?php echo e(translate('update_cart')); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    "use strict";

    $(".remove-service-row").on('click', function (){
        let row = $(this).data('row');
        removeServiceRow(row)
    })
</script>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BookingModule/Resources/views/admin/booking/partials/details/_service-modal.blade.php ENDPATH**/ ?>