<?php $__env->startSection('title',translate('service_list')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/select.dataTables.min.css"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title"><?php echo e(translate('service_list')); ?></h2>
                        <div>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_add')): ?>
                                <a href="<?php echo e(route('admin.service.create')); ?>" class="btn btn--primary">
                                    <span class="material-icons">add</span>
                                    <?php echo e(translate('add_service')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($status=='all'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['status' => 'all', 'page' => null])); ?>">
                                    <?php echo e(translate('all')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($status=='active'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['status' => 'active', 'page' => null])); ?>">
                                    <?php echo e(translate('active')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($status=='inactive'?'active':''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null])); ?>">
                                    <?php echo e(translate('inactive')); ?>

                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75"><?php echo e(translate('Total_Services')); ?>:</span>
                            <span class="title-color"><?php echo e($services->total()); ?></span>
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
                                            <input type="hidden" name="status" value="<?php echo e($status); ?>">
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
                                    </div>

                                    <div class="table-responsive">
                                        <?php
                                            $serviceTableColspan = 6;
                                        ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_manage_status')): ?>
                                            <?php
                                                $serviceTableColspan++;
                                            ?>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service_delete', 'service_update'])): ?>
                                            <?php
                                                $serviceTableColspan++;
                                            ?>
                                        <?php endif; ?>
                                        <table id="example" class="table align-middle">
                                            <thead>
                                            <tr>
                                                <th><?php echo e(translate('SL')); ?></th>
                                                <th><?php echo e(translate('name')); ?></th>
                                                <th><?php echo e(translate('category')); ?></th>
                                                <th><?php echo e(translate('zones')); ?></th>
                                                <th><?php echo e(translate('Minimum Bidding Price')); ?></th>
                                                <th><?php echo e(translate('badge')); ?></th>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_manage_status')): ?>
                                                    <th><?php echo e(translate('status')); ?></th>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service_delete', 'service_update'])): ?>
                                                    <th><?php echo e(translate('action')); ?></th>
                                                <?php endif; ?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($services->firstitem()+$key); ?></td>
                                                    <td>
                                                        <a href="<?php echo e(route('admin.service.detail',[$service->id])); ?>">
                                                            <?php echo e(Str::limit($service->name, 50)); ?>

                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if($service->category): ?>
                                                            <?php echo e($service->category->name); ?>

                                                        <?php else: ?>
                                                            <div class="d-flex">
                                                                <span><?php echo e(translate('Unavailable')); ?></span>
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="<?php echo e(translate('Update the service category')); ?>">info
                                                                </i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($service->category): ?>
                                                            <?php if(count($service->category->zonesBasicInfo) > 0): ?>
                                                             <?php echo e(implode(', ',$service->category->zonesBasicInfo->pluck('name')->toArray())); ?>

                                                            <?php else: ?>
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="<?php echo e(translate('This category is not under any zone. Kindly update the category with zone')); ?>">info
                                                                </i>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo e(with_currency_symbol($service->min_bidding_price)); ?>


                                                        <?php if($service->min_bidding_price == 0): ?>
                                                            <i class="text-warning material-icons px-1"
                                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                                               title="<?php echo e(translate('Update the minimum bidding price')); ?>"
                                                            >warning</i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(count($service->badge) > 0): ?>
                                                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                                                <?php $__currentLoopData = $service->badge; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <span class="badge d-inline-flex align-items-center gap-1 py-1 px-2 fw-medium"
                                                                          style="background-color: <?php echo e($b['bg_color']); ?>; color: <?php echo e($b['text_color']); ?>; border: 1px solid <?php echo e($b['border_color']); ?>; border-radius: 12px; font-size: 11px;">
                                                                        <span class="material-icons" style="font-size: 13px;"><?php echo e($b['icon']); ?></span>
                                                                        <?php echo e(app()->getLocale() == 'ar' ? $b['name_ar'] : $b['name']); ?>

                                                                    </span>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge text-muted" style="background-color: #f5f5f5; border-radius: 12px; font-size: 11px;">
                                                                <?php echo e(translate('standard')); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_manage_status')): ?>
                                                        <td>
                                                            <label class="switcher mx-auto" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input status-update"
                                                                       type="checkbox"
                                                                       id="service-status-<?php echo e($service->id); ?>"
                                                                       <?php echo e($service->is_active ? 'checked' : ''); ?> data-status="<?php echo e($service->id); ?>">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service_delete', 'service_update'])): ?>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_update')): ?>
                                                                    <a href="<?php echo e(route('admin.service.edit',[$service->id])); ?>"
                                                                       class="action-btn btn--light-primary demo_check"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service_delete')): ?>
                                                                    <button type="button"
                                                                            data-id="delete-<?php echo e($service->id); ?>"
                                                                            data-message="<?php echo e(translate('want_to_delete_this_service')); ?>?"
                                                                            class="action-btn btn--danger <?php echo e(env('APP_ENV')!='demo' ? 'form-alert' : 'demo_check'); ?>"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="<?php echo e(route('admin.service.delete',[$service->id])); ?>"
                                                                        method="post" id="delete-<?php echo e($service->id); ?>"
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
                                                    'colspan' => $serviceTableColspan,
                                                    'variant' => (filled($search) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                                    'showButton' => true,
                                                    'buttonTextKey' => 'add_service',
                                                    'buttonUrl' => route('admin.service.create')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <?php echo $services->links(); ?>

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
        "use strict"

        $(document).ready(function () {
            $('.js-select').select2();

            $('.status-update').on('click', function () {
                let $this = $(this);
                let itemId = $(this).data('status');
                let initialState = $this.prop('checked');
                let route = '<?php echo e(route('admin.service.status-update', ['id' => ':itemId'])); ?>';
                let message = initialState
                    ? '<?php echo e(translate('If you turn it on, this service will be visible on the website.')); ?>'
                    : '<?php echo e(translate('If you turn it off, this service will not be visible on the website.')); ?>';
                route = route.replace(':itemId', itemId);
                route_alert_reload(route, message, true, initialState ? 1 : 0, 'service-status-' + itemId);
            });
        });
    </script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/dataTables.select.min.js"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/list.blade.php ENDPATH**/ ?>