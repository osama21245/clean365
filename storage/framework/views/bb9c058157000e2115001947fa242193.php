<?php $__env->startSection('title', translate('Serviceman_List')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <h2 class="page-title mb-0"><?php echo e(translate('Serviceman_List')); ?></h2>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('serviceman_add')): ?>
                            <a href="<?php echo e(route('admin.serviceman.create')); ?>" class="btn btn--primary">
                                <span class="material-icons">add</span>
                                <?php echo e(translate('Add_New_Serviceman')); ?>

                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($status == 'all' ? 'active' : ''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['status' => 'all', 'page' => null])); ?>"><?php echo e(translate('All')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($status == 'active' ? 'active' : ''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['status' => 'active', 'page' => null])); ?>"><?php echo e(translate('Active')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($status == 'inactive' ? 'active' : ''); ?>"
                                   href="<?php echo e(request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null])); ?>"><?php echo e(translate('Inactive')); ?></a>
                            </li>
                        </ul>
                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75"><?php echo e(translate('Total_Serviceman')); ?>:</span>
                            <span class="title-color"><?php echo e($servicemen->total()); ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="<?php echo e(url()->current()); ?>" class="search-form search-form_style-two" method="GET">
                                    <input type="hidden" name="status" value="<?php echo e($status); ?>">
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <select name="provider_id" class="theme-input-style js-select" style="min-width: 200px">
                                            <option value=""><?php echo e(translate('All_Supervisors')); ?></option>
                                            <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($provider->id); ?>" <?php echo e(($providerId ?? '') == $provider->id ? 'selected' : ''); ?>>
                                                    <?php echo e($provider->company_name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="input-group search-form__input_group">
                                            <span class="search-form__icon"><span class="material-icons">search</span></span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                   value="<?php echo e($search); ?>" name="search"
                                                   placeholder="<?php echo e(translate('Search Here')); ?>">
                                        </div>
                                        <button type="submit" class="btn btn--primary"><?php echo e(translate('search')); ?></button>
                                    </div>
                                </form>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('serviceman_export')): ?>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn--secondary text-capitalize dropdown-toggle" data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> <?php echo e(translate('download')); ?>

                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li>
                                                <a class="dropdown-item"
                                                   href="<?php echo e(route('admin.serviceman.download', ['status' => $status, 'search' => $search, 'provider_id' => $providerId])); ?>">
                                                    <?php echo e(translate('excel')); ?>

                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th><?php echo e(translate('SL')); ?></th>
                                        <th><?php echo e(translate('Name')); ?></th>
                                        <th><?php echo e(translate('Supervisor')); ?></th>
                                        <th><?php echo e(translate('Contact_Info')); ?></th>
                                        <th><?php echo e(translate('Status')); ?></th>
                                        <th><?php echo e(translate('Action')); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $servicemen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $serviceman): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($servicemen->firstItem() + $key); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('admin.serviceman.show', [$serviceman->serviceman->id])); ?>">
                                                    <?php echo e(Str::limit($serviceman->first_name, 25)); ?> <?php echo e(Str::limit($serviceman->last_name, 15)); ?>

                                                </a>
                                            </td>
                                            <td><?php echo e($serviceman->serviceman?->provider?->company_name ?? translate('Unassigned')); ?></td>
                                            <td>
                                                <?php echo e($serviceman->email); ?> <br/>
                                                <?php echo e($serviceman->phone); ?>

                                            </td>
                                            <td>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('serviceman_manage_status')): ?>
                                                    <label class="switcher">
                                                        <input class="switcher_input route-alert"
                                                               data-route="<?php echo e(route('admin.serviceman.status-update', [$serviceman->id])); ?>"
                                                               data-message="<?php echo e(translate('want_to_update_status')); ?>"
                                                               type="checkbox" <?php echo e($serviceman->is_active ? 'checked' : ''); ?>>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                <?php else: ?>
                                                    <span class="badge <?php echo e($serviceman->is_active ? 'badge-success' : 'badge-danger'); ?>">
                                                        <?php echo e($serviceman->is_active ? translate('Active') : translate('Inactive')); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="<?php echo e(route('admin.serviceman.show', [$serviceman->serviceman->id])); ?>"
                                                       class="action-btn btn--light-primary" style="--size: 30px">
                                                        <span class="material-icons">visibility</span>
                                                    </a>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('serviceman_update')): ?>
                                                        <a href="<?php echo e(route('admin.serviceman.edit', [$serviceman->serviceman->id])); ?>"
                                                           class="action-btn btn--light-primary" style="--size: 30px">
                                                            <span class="material-icons">edit</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('serviceman_delete')): ?>
                                                        <button type="button"
                                                                data-id="delete-<?php echo e($serviceman->serviceman->id); ?>"
                                                                data-title="<?php echo e(translate('want_to_delete_this_serviceman')); ?>?"
                                                                class="action-btn btn--danger form-alert" style="--size: 30px">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </button>
                                                        <form action="<?php echo e(route('admin.serviceman.delete', [$serviceman->serviceman->id])); ?>"
                                                              method="post" id="delete-<?php echo e($serviceman->serviceman->id); ?>" class="hidden">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                            'variant' => (filled($search) || (($status ?? 'all') !== 'all') || filled($providerId)) ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <?php echo $servicemen->links(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script>
        'use strict';
        $(document).ready(function () {
            $('.js-select').select2({ width: '200px' });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServicemanModule/Resources/views/Admin/Serviceman/list.blade.php ENDPATH**/ ?>