<?php $__env->startSection('title', translate('role_settings')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/select.dataTables.min.css"/>
    <style>
        .role-tooltip-trigger {
            display: inline-block;
            cursor: pointer;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex justify-content-between gap-2 mb-3">
                        <h2 class="page-title"><?php echo e(translate('Employee_Role_List')); ?></h2>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role_add')): ?>
                            <a href="<?php echo e(route('admin.role.create')); ?>" class="btn btn--primary">
                                <span class="material-symbols-outlined">add</span>
                                <?php echo e(translate('add_Role')); ?>

                            </a>
                        <?php endif; ?>
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
                            <span class="opacity-75"><?php echo e(translate('Total Employee Roles')); ?>:</span>
                            <span class="title-color"><?php echo e($roles->total()); ?></span>
                        </div>
                    </div>

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
                                               placeholder="<?php echo e(translate('search_by_role_name')); ?>">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        <?php echo e(translate('search')); ?>

                                    </button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee_export')): ?>
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span class="material-icons">file_download</span> <?php echo e(translate('download')); ?>

                                            </button>
                                        <?php endif; ?>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <a class="dropdown-item"
                                               href="<?php echo e(route('admin.role.download')); ?>?search=<?php echo e($search); ?>&status=<?php echo e($status); ?>">
                                                <?php echo e(translate('excel')); ?>

                                            </a>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <?php
                                    $roleTableColspan = 3;
                                ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role_manage_status')): ?>
                                    <?php
                                        $roleTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role_delete', 'role_update'])): ?>
                                    <?php
                                        $roleTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th><?php echo e(translate('SL')); ?></th>
                                        <th><?php echo e(translate('role_name')); ?></th>
                                        <th><?php echo e(translate('Modules')); ?></th>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role_manage_status')): ?>
                                            <th><?php echo e(translate('status')); ?></th>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role_delete', 'role_update'])): ?>
                                            <th class="text-center"><?php echo e(translate('action')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $output = '';
                                        ?>
                                        <?php $__currentLoopData = SYSTEM_MODULES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $matchedRoleBtn = Modules\UserManagement\Entities\RoleAccess::where('role_id', $item->id)->where('section_name', $module['key'])->first();
                                                $hasMatchingSubmodules = false;
                                            ?>

                                            <?php if(isset($module['submodules'])): ?>
                                                <?php $__currentLoopData = $module['submodules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submodule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $matchedRoleSection = Modules\UserManagement\Entities\RoleAccess::where('role_id', $item->id)->where('section_name', $submodule['key'])->first();
                                                        if($matchedRoleSection) {
                                                            $hasMatchingSubmodules = true;
                                                            break;
                                                        }
                                                    ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                <?php if($hasMatchingSubmodules): ?>
                                                    <?php
                                                        $output .= $module['value'] . ' (';
                                                        $submodulesOutput = '';
                                                    ?>

                                                    <?php $__currentLoopData = $module['submodules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submodule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $matchedRoleSection = Modules\UserManagement\Entities\RoleAccess::where('role_id', $item->id)->where('section_name', $submodule['key'])->first();
                                                        ?>
                                                        <?php if($matchedRoleSection): ?>
                                                            <?php
                                                                $submodulesOutput .= $submodule['value'] . ', ';
                                                            ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                    <?php
                                                        $output .= rtrim($submodulesOutput, ', ') . ') ';
                                                    ?>
                                                <?php endif; ?>
                                            <?php elseif($matchedRoleBtn): ?>
                                                <?php
                                                    $output .= $module['value'] . ', ';
                                                ?>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($key+$roles?->firstItem()); ?></td>
                                            <td><?php echo e($item->role_name); ?></td>
                                            <td>
                                                <span class="role-tooltip-trigger"
                                                      data-bs-toggle="tooltip"
                                                      data-bs-placement="top"
                                                      data-bs-title="<?php echo e(rtrim($output, ', ')); ?>">
                                                    <?php echo e(Str::limit(rtrim($output, ', '), 150)); ?>

                                                </span>
                                            </td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role_manage_status')): ?>
                                                <td>
                                                    <?php $isOwnRole = auth()->user()->roles->contains('id', $item->id); ?>
                                                    <?php if($isOwnRole): ?>
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(translate('You cannot change your own role status')); ?>" class="d-inline-block">
                                                    <?php endif; ?>
                                                    <label class="switcher <?php echo e($isOwnRole ? 'opacity-50 pe-none' : ''); ?>">
                                                        <input class="switcher_input status_updated" type="checkbox"
                                                               <?php echo e($item->is_active?'checked':''); ?> data-status="<?php echo e($item->id); ?>"
                                                               <?php echo e($isOwnRole ? 'disabled' : ''); ?>>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                    <?php if($isOwnRole): ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role_delete', 'role_update'])): ?>
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role_update')): ?>
                                                            <?php if($isOwnRole): ?>
                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(translate('You cannot edit your own role')); ?>" class="d-inline-block">
                                                            <?php endif; ?>
                                                            <a href="<?php echo e(route('admin.role.edit',[$item->id])); ?>"
                                                               class="action-btn btn--light-primary <?php echo e($isOwnRole ? 'opacity-50 pe-none' : ''); ?>"
                                                               style="--size: 30px">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                            <?php if($isOwnRole): ?>
                                                            </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role_delete')): ?>
                                                            <?php if($isOwnRole): ?>
                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(translate('You cannot delete your own role')); ?>" class="d-inline-block">
                                                            <?php endif; ?>
                                                            <button type="button" data-delete="<?php echo e($item->id); ?>"
                                                                    class="action-btn btn--danger <?php echo e($isOwnRole ? 'opacity-50' : ''); ?>" style="--size: 30px"
                                                                    <?php echo e($isOwnRole ? 'disabled' : ''); ?>>
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            <?php if($isOwnRole): ?>
                                                            </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <form action="<?php echo e(route('admin.role.delete',[$item->id])); ?>"
                                                          method="post" id="delete-<?php echo e($item->id); ?>"
                                                          class="hidden">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $roleTableColspan,
                                            'variant' => (filled($search) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                            'showButton' => true,
                                            'buttonTextKey' => 'add_Role',
                                            'buttonUrl' => route('admin.role.create')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <?php echo $roles->links(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/js/custom.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/dataTables.select.min.js"></script>
    <script>
        "use strict";

        function initRoleIndexTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((tooltipTriggerEl) => {
                bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
            });
        }

        initRoleIndexTooltips();

        $('.status_updated').on('click', function () {
            let itemId = $(this).data('status');
            let route = '<?php echo e(route('admin.role.status-update', ['id' => ':itemId'])); ?>';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '<?php echo e(translate('want_to_update_status')); ?>');
        })

        $('.action-btn.btn--danger').on('click', function () {
            let itemId = $(this).data('delete');
            <?php if(env('APP_ENV')!='demo'): ?>
            form_alert('delete-' + itemId, '<?php echo e(translate('want_to_delete_this_role')); ?>?')
            <?php endif; ?>
        })
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/admin/employee/role-index.blade.php ENDPATH**/ ?>