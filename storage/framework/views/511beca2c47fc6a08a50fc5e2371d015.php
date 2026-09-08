<?php $__env->startSection('title',translate('discounts')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/select.dataTables.min.css"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('discounts')); ?></h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($type=='all'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['type' => 'all', 'page' => null])); ?>">
                                    <?php echo e(translate('all')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($type=='category'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['type' => 'category', 'page' => null])); ?>">
                                    <?php echo e(translate('category_wise')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($type=='service'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['type' => 'service', 'page' => null])); ?>">
                                    <?php echo e(translate('service_wise')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($type=='additional_service'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['type' => 'additional_service', 'page' => null])); ?>">
                                    <?php echo e(translate('Additional Services')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($type=='mixed'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['type' => 'mixed', 'page' => null])); ?>">
                                    <?php echo e(translate('mixed')); ?>

                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75"><?php echo e(translate('Total_Discount')); ?>:</span>
                            <span class="title-color"><?php echo e($discounts->total()); ?></span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="<?php echo e(url()->current()); ?>"
                                              class="search-form search-form_style-two"
                                              method="GET">
                                            <input type="hidden" name="type" value="<?php echo e($type); ?>">
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="<?php echo e($search); ?>" name="search"
                                                       placeholder="<?php echo e(translate('search_here')); ?>">
                                            </div>
                                            <button type="submit"
                                                    class="btn btn--primary"><?php echo e(translate('search')); ?></button>
                                        </form>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_export')): ?>
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                    <span
                                                        class="material-icons">file_download</span> <?php echo e(translate('download')); ?>

                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <a class="dropdown-item"
                                                           href="<?php echo e(route('admin.discount.download')); ?>?search=<?php echo e($search); ?>&type=<?php echo e($type); ?>">
                                                            <?php echo e(translate('excel')); ?>

                                                        </a>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <?php
                                            $discountTableColspan = 4;
                                        ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_manage_status')): ?>
                                            <?php
                                                $discountTableColspan++;
                                            ?>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['discount_delete', 'discount_update'])): ?>
                                            <?php
                                                $discountTableColspan++;
                                            ?>
                                        <?php endif; ?>
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th><?php echo e(translate('Sl')); ?></th>
                                                <th><?php echo e(translate('title')); ?></th>
                                                <th><?php echo e(translate('discount_type')); ?></th>
                                                <th><?php echo e(translate('zones')); ?></th>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_manage_status')): ?>
                                                    <th><?php echo e(translate('status')); ?></th>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['discount_delete', 'discount_update'])): ?>
                                                    <th><?php echo e(translate('action')); ?></th>
                                                <?php endif; ?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($key+$discounts->firstItem()); ?></td>
                                                    <td><?php echo e($discount->discount_title); ?></td>
                                                    <td><?php echo e($discount->discount_type); ?></td>
                                                    <td>
                                                        <?php $__currentLoopData = $discount->zone_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php echo e($type->zone?$type->zone->name.',':''); ?>

                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </td>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_manage_status')): ?>
                                                        <td>
                                                            <label class="switcher" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input"
                                                                       data-status="<?php echo e($discount->id); ?>"
                                                                       type="checkbox" <?php echo e($discount->is_active?'checked':''); ?>>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['discount_delete', 'discount_update'])): ?>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_update')): ?>
                                                                    <a href="<?php echo e(route('admin.discount.edit',[$discount->id])); ?>"
                                                                       class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('discount_delete')): ?>
                                                                    <button type="button"
                                                                            data-id="<?php echo e($discount->id); ?>"
                                                                            class="action-btn btn--danger delete_section"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="<?php echo e(route('admin.discount.delete',[$discount->id])); ?>"
                                                                        method="post" id="delete-<?php echo e($discount->id); ?>"
                                                                        class="hidden">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('DELETE'); ?>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                                    'colspan' => $discountTableColspan,
                                                    'variant' => (filled($search) || (($type ?? 'all') !== 'all')) ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <?php echo $discounts->links(); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.js"></script>
    <script>
        "use Strict"

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '<?php echo e(route('admin.discount.status-update', ['id' => ':itemId'])); ?>';
            route = route.replace(':itemId', itemId);
            route_alert(route, '<?php echo e(translate('want_to_update_status')); ?>');
        })

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '<?php echo e(translate('want_to_delete_this_discount')); ?>');
        })

        $(document).ready(function () {
            $('.js-select').select2();
        });
    </script>

    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/dataTables.select.min.js"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/PromotionManagement/Resources/views/admin/discounts/list.blade.php ENDPATH**/ ?>