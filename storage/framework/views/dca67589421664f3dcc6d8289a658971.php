<div class="table-responsive">
    <?php
        $zoneTableColspan = 4;
    ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_manage_status')): ?>
        <?php
            $zoneTableColspan++;
        ?>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['zone_delete', 'zone_update'])): ?>
        <?php
            $zoneTableColspan++;
        ?>
    <?php endif; ?>
    <table id="example" class="table align-middle">
        <thead>
        <tr>
            <th><?php echo e(translate('SL')); ?></th>
            <th><?php echo e(translate('zone_name')); ?></th>
            <th><?php echo e(translate('providers')); ?></th>
            <th><?php echo e(translate('Category')); ?></th>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_manage_status')): ?>
                <th><?php echo e(translate('status')); ?></th>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['zone_delete', 'zone_update'])): ?>
                <th><?php echo e(translate('action')); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($key+$zones->firstItem()); ?></td>
                <td><?php echo e($zone->name); ?></td>
                <td><?php echo e($zone->providers_count); ?></td>
                <td><?php echo e($zone->categories_count); ?></td>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_manage_status')): ?>
                    <td>
                        <label class="switcher">
                            <input class="switcher_input status-update"
                                   data-id="<?php echo e($zone->id); ?>"
                                   type="checkbox" <?php echo e($zone->is_active?'checked':''); ?>>
                            <span class="switcher_control"></span>
                        </label>
                    </td>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['zone_delete', 'zone_update'])): ?>
                    <td>
                        <div class="d-flex gap-2">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_update')): ?>
                                <a href="<?php echo e(route('admin.zone.edit',[$zone->id])); ?>"
                                   class="action-btn btn--light-primary demo_check">
                                    <span class="material-icons">edit</span>
                                </a>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_delete')): ?>
                                <button type="button"
                                        data-id="delete-<?php echo e($zone->id); ?>"
                                        data-message="<?php echo e(translate('want_to_delete_this_zone')); ?>?"
                                        class="action-btn btn--danger <?php echo e(env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check'); ?>"
                                        style="--size: 30px">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                                <form
                                    action="<?php echo e(route('admin.zone.delete',[$zone->id])); ?>"
                                    method="post" id="delete-<?php echo e($zone->id); ?>"
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
                'colspan' => $zoneTableColspan,
                'variant' => request()->filled('search') ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    <?php echo $zones->links(); ?>

</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ZoneManagement/Resources/views/admin/partials/_table.blade.php ENDPATH**/ ?>