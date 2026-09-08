<?php $__env->startSection('title',translate('Overview')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title mb-2"><?php echo e(translate('Customer')); ?></h2>
                <div><?php echo e(translate('Joined on :date', ['date' => format_time_by_business_settings($customer?->created_at, 'd-M-y')])); ?></div>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($webPage=='overview'?'active':''); ?>"
                           href="<?php echo e(url()->current()); ?>?web_page=overview"><?php echo e(translate('Overview')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($webPage=='bookings'?'active':''); ?>"
                           href="<?php echo e(url()->current()); ?>?web_page=bookings"><?php echo e(translate('Bookings')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($webPage=='reviews'?'active':''); ?>"
                           href="<?php echo e(url()->current()); ?>?web_page=reviews"><?php echo e(translate('Reviews')); ?></a>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body p-30">
                    <div class="row justify-content-center g-2 mb-30">
                        <div class="col-sm-6 col-lg-6 provider-details-overview__statistics d-flex flex-column">
                            <div class="statistics-card statistics-card__style2 statistics-card__pending-withdraw">
                                <h2><?php echo e($customer->bookings_count); ?></h2>
                                <h3><?php echo e(translate('Total_Booking_Placed')); ?></h3>
                            </div>

                            <div class="statistics-card statistics-card__style2 statistics-card__already-withdraw">
                                <h2><?php echo e(with_currency_symbol($totalBookingAmount)); ?></h2>
                                <h3><?php echo e(translate('Total_Booking_Amount')); ?></h3>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 provider-details-overview__order-overview">
                            <div class="statistics-card statistics-card__order-overview h-100 pb-2">
                                <h3 class="mb-0"><?php echo e(translate('Booking_Overview')); ?></h3>
                                <div id="apex-pie-chart" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h2><?php echo e(translate('Personal_Details')); ?></h2>
                    </div>

                    <div>
                        <div class="information-details-box media flex-column flex-sm-row gap-20 mb-3">
                            <img class="avatar-img radius-5"
                                 src="<?php echo e($customer->profile_image_full_path); ?>" alt="<?php echo e(translate('image')); ?>">
                            <div class="media-body d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="information-details-box__title"><?php echo e(Str::limit(($customer->first_name ?? '') . ' ' .  ($customer->last_name ?? ''), 30)); ?></h2>

                                    <ul class="contact-list">
                                        <li>
                                            <span class="material-symbols-outlined">phone_iphone</span>
                                            <a href="tel:<?php echo e($customer->phone); ?>"><?php echo e($customer->phone); ?></a>
                                        </li>
                                        <li>
                                            <span class="material-symbols-outlined">mail</span>
                                            <a href="mailto:<?php echo e($customer->email); ?>"><?php echo e($customer->email); ?></a>
                                        </li>
                                    </ul>
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customer_update')): ?>
                                    <a href="<?php echo e(route('admin.customer.edit',[$customer->id])); ?>" class="btn btn--primary">
                                        <span class="material-icons">border_color</span>
                                        <?php echo e(translate('Edit')); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if($customer->addresses && $customer->addresses->count() > 0): ?>
                            <div class="information-details-box customer-address">
                                <?php $__currentLoopData = $customer->addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="d-flex justify-content-between gap-2 mb-20">
                                        <div class="media gap-2 gap-xl-3">
                                            <span class="material-icons fz-30 c1">home</span>
                                            <div class="media-body">
                                                <h4 class="fw-medium mb-1"><?php echo e($address->address_label); ?></h4>
                                                <div class="text-muted"><?php echo e(Str::limit($address->address, 100)); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header px-4">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo e(translate('Add_Customer_Address')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0 pt-4 mt-2 px-4">
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="street" name="street"
                               placeholder="<?php echo e(translate('Street')); ?>" value="<?php echo e(old('street')); ?>" required>
                        <label><?php echo e(translate('Street')); ?></label>
                    </div>
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="city" name="city"
                               placeholder="<?php echo e(translate('City')); ?>" value="<?php echo e(old('city')); ?>" required>
                        <label><?php echo e(translate('City')); ?></label>
                    </div>
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="country" name="country"
                               placeholder="<?php echo e(translate('Country')); ?>" value="<?php echo e(old('country')); ?>" required>
                        <label><?php echo e(translate('Country')); ?></label>
                    </div>
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="zip_code" name="zip_code"
                               placeholder="<?php echo e(translate('Zip_Code')); ?>" value="<?php echo e(old('zip_code')); ?>" required>
                        <label><?php echo e(translate('Zip_Code')); ?></label>
                    </div>
                    <div class="form-floating mb-30">
                        <textarea type="text" class="form-control" id="address" name="address"
                                  placeholder="<?php echo e(translate('Address')); ?>" value="<?php echo e(old('address')); ?>" required></textarea>
                        <label><?php echo e(translate('Address')); ?></label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn--secondary"
                            data-bs-dismiss="modal"><?php echo e(translate('Close')); ?></button>
                    <button type="button" class="btn btn--primary"><?php echo e(translate('Save_changes')); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

    <script src="<?php echo e(asset('public/assets/admin-module/plugins/apex/apexcharts.min.js')); ?>"></script>

    <script>
        "use strict"
        var options = {
            labels: ['pending', 'accepted', 'ongoing', 'completed', 'canceled'],
            series: <?php echo e(json_encode($total)); ?>,
            chart: {
                width: 235,
                height: 160,
                type: 'donut',
            },
            dataLabels: {
                enabled: false
            },
            title: {
                text: "<?php echo e($customer->bookings_count); ?> Bookings",
                align: 'center',
                offsetX: 0,
                offsetY: 58,
                floating: true,
                style: {
                    fontSize: '12px',
                    fontWeight: '500',
                },
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        show: true
                    }
                }
            }],
            legend: {
                position: 'bottom',
                offsetY: -5,
                height: 30,
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex-pie-chart"), options);
        chart.render();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/CustomerModule/Resources/views/admin/detail/overview.blade.php ENDPATH**/ ?>