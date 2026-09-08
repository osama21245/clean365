<div class="modal-header border-0 pb-1 pb-lg-1 p-lg-5">
    <div class="d-flex flex-column gap-1">
        <h5><?php echo e(translate('Available Providers')); ?></h5>
        <?php
            $count = isset($currentlyAssignProvider) ? count($providers) +1 : count($providers)
        ?>
        <div class="fs-12"><span class="provider-count"><?php echo e($count); ?></span> <?php echo e(translate('Providers are available right now')); ?></div>
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
                <input type="search" class="theme-input-style search-form__input search-form-input"
                       id="search-form__input"
                       placeholder="<?php echo e(translate('Search Here')); ?>'" value="<?php echo e($search ?? ''); ?>">
            </div>
        </form>
        <?php if($count > 0): ?>
            <div class="dropdown">
                <button type="button" class="btn px-3 py-2 border text-capitalize rounded-3 title-color apply-filter-button"
                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    <span class="material-icons">filter_list</span> Sort By
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-3"
                     data-popper-placement="bottom-end">
                    <form id="sort-form">
                        <div class="d-flex flex-column gap-3 title-color">
                            <label class="d-flex gap-2 gap-sm-3 sort-by-class">
                                <input type="radio" name="sort" value="default" class="position-static" checked>
                                <?php echo e(translate('Default')); ?>

                            </label>

                            <label class="d-flex gap-2 gap-sm-3 sort-by-class">
                                <input type="radio" name="sort" class="position-static"
                                       value="top-rated" <?php echo e($sort_by == 'top-rated' ? 'checked' : ''); ?>>
                                <?php echo e(translate('Top Rated')); ?>

                            </label>

                            <label class="d-flex gap-2 gap-sm-3 sort-by-class">
                                <input type="radio" name="sort" class="position-static"
                                       value="bookings-completed" <?php echo e($sort_by == 'bookings-completed' ? 'checked' : ''); ?>>
                                <?php echo e(translate('Bookings Completed')); ?>

                            </label>
                        </div>
                    </form>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <div class="d-flex flex-column">
        
        <?php if($booking->provider_id && isset($currentlyAssignProvider)): ?>
            <div class="d-flex gap-2 justify-content-between align-items-center mt-4 pb-3 flex-wrap">
                <div class="media gap-2">
                    <img width="60" class="rounded"
                         src="<?php echo e($currentlyAssignProvider->logo_full_path); ?>"
                         alt="<?php echo e(translate('provider-logo')); ?>">
                    <div class="media-body">
                        <h5 class="mb-2"><?php echo e($currentlyAssignProvider->company_name); ?></h5>
                        <div class="mb-1 fs-12"><a href="tel:<?php echo e($currentlyAssignProvider->contact_person_phone); ?>"><?php echo e($currentlyAssignProvider->contact_person_phone); ?></a></div>
                        <div class="provider-devider">
                            <ol class="breadcrumb fs-12 mb-0">
                                <li class="breadcrumb-item">
                                <span class="common-list_rating d-flex gap-1 text-secondary">
                                    <span class="material-icons">star</span>
                                    <?php echo e($currentlyAssignProvider->avg_rating); ?> (<?php echo e($currentlyAssignProvider->reviews_count); ?>)
                                </span>
                                </li>
                                <li class="breadcrumb-item active"><?php echo e(translate('Bookings')); ?> - <?php echo e($currentlyAssignProvider->bookings_count); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="text-success"><?php echo e(translate('Currently Assigned')); ?></div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="d-flex gap-2 justify-content-between align-items-center mt-4 pb-3 flex-wrap">
                <div class="media gap-2">
                    <img width="60" class="rounded"
                         src="<?php echo e($provider->logo_full_path); ?>"
                         alt="<?php echo e(translate('provider-logo')); ?>">
                    <div class="media-body">
                        <h5 class="mb-2"><?php echo e($provider->company_name); ?></h5>
                        <div class="mb-1 fs-12"><a href="tel:<?php echo e($provider->contact_person_phone); ?>"><?php echo e($provider->contact_person_phone); ?></a></div>
                        <div class="provider-devider">
                            <ol class="breadcrumb fs-12 mb-0">
                                <li class="breadcrumb-item">
                                <span class="common-list_rating d-flex gap-1 text-secondary">
                                    <span class="material-icons">star</span>
                                    <?php echo e($provider->avg_rating); ?> (<?php echo e($provider->reviews_count); ?>)
                                </span>
                                </li>
                                <li class="breadcrumb-item active"><?php echo e(translate('Bookings')); ?> - <?php echo e($provider->bookings_count); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary w-100 max-w320 reassign-provider"
                            data-provider-reassign="<?php echo e($provider->id); ?>"> <?php echo e($booking->provider_id ? translate('Re Assign') : 'Assign'); ?>

                    </button>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

</div>

<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BookingModule/Resources/views/admin/booking/partials/details/provider-info-modal-data.blade.php ENDPATH**/ ?>