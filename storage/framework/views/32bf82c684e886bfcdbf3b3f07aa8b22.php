<div class="modal-header border-0 pb-1 pb-lg-1 p-lg-5">
    <div class="d-flex flex-column gap-1">
        <h5><?php echo e(translate('Available Servicemen')); ?></h5>
        <div class="fs-12"><?php echo e(translate(':count servicemen are available right now', ['count' => $servicemen->count()])); ?></div>
    </div>
    <button type="button" class="btn-close provider-cross" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-lg-5 pt-lg-3">
    <div class="d-flex gap-2">
        <form action="#" class="search-form flex-grow-1" autocomplete="off">
            <div class="input-group position-relative search-form__input_group rounded-3">
                                <span class="search-form__icon">
                                    <span class="material-icons">search</span>
                                </span>
                <input type="search" class="theme-input-style search-form__input search-form-input1"
                       id="search-form__input"
                       placeholder="<?php echo e(translate('Search Here')); ?>'" value="<?php echo e($search ?? ''); ?>">
            </div>
        </form>
    </div>

    <div class="d-flex flex-column">
        <?php
            $matchedServiceman = null;
            $otherServicemen = [];
        ?>

        
        <?php $__currentLoopData = $servicemen ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceman): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($booking->serviceman_id == $serviceman->id): ?>
                <?php $matchedServiceman = $serviceman; ?>
            <?php else: ?>
                <?php $otherServicemen[] = $serviceman; ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($matchedServiceman): ?>
            <div class="d-flex gap-2 justify-content-between align-items-center mt-4 pb-3 flex-wrap">
                <div class="media gap-2">
                    <img width="60" class="rounded-circle w-58px h-58px object-cover"
                         src="<?php echo e($matchedServiceman?->user?->profile_image_full_path); ?>"
                         alt="<?php echo e(translate('serviceman-logo')); ?>">
                    <div class="media-body">
                        <h5 class="mb-2"><?php echo e($matchedServiceman->user->first_name . ' '. $matchedServiceman->user->last_name); ?></h5>
                        <div class="mb-1 fs-12"><a href="tel:<?php echo e($matchedServiceman->user->phone); ?>"><?php echo e($matchedServiceman->user->phone); ?></a></div>
                        <div class="provider-devider">
                            <ol class="breadcrumb fs-12 mb-0">
                                <li class="breadcrumb-item active"><?php echo e(translate('Bookings')); ?> - <?php echo e($matchedServiceman->bookings_count); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="text-success"><?php echo e(translate('Currently Assigned')); ?></div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php $__currentLoopData = $otherServicemen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceman): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="d-flex gap-2 justify-content-between align-items-center mt-4 pb-3 flex-wrap">
                <div class="media gap-2">
                    <img width="60" class="rounded-circle w-58px h-58px object-cover"
                         src="<?php echo e($serviceman->user?->profile_image_full_path); ?>"
                         alt="<?php echo e(translate('serviceman-logo')); ?>">
                    <div class="media-body">
                        <h5 class="mb-2"><?php echo e($serviceman->user->first_name . ' '. $serviceman->user->last_name); ?></h5>
                        <div class="mb-1 fs-12"><a href="tel:<?php echo e($serviceman->user->phone); ?>"><?php echo e($serviceman->user->phone); ?></a></div>
                        <div class="provider-devider">
                            <ol class="breadcrumb fs-12 mb-0">
                                <li class="breadcrumb-item active"><?php echo e(translate('Bookings')); ?> - <?php echo e($serviceman->bookings_count); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary w-100 max-w320 reassign-serviceman"
                            data-serviceman-reassign="<?php echo e($serviceman->id); ?>"> <?php echo e($booking->serviceman_id ? translate('Re Assign') : 'Assign'); ?>

                    </button>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BookingModule/Resources/views/admin/booking/partials/details/serviceman-info-modal-data.blade.php ENDPATH**/ ?>