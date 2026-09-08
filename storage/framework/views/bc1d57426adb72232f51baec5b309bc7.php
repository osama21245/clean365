
<div class="table-responsive">
    <?php
        $categoryTableColspan = 4;
    ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_manage_status')): ?>
        <?php
            $categoryTableColspan += 2;
        ?>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['category_delete', 'category_update'])): ?>
        <?php
            $categoryTableColspan++;
        ?>
    <?php endif; ?>
    <table id="example" class="table align-middle">
        <thead class="align-middle">
        <tr>
            <th><?php echo e(translate('SL')); ?></th>
            <th><?php echo e(translate('category_name')); ?></th>
            <th><?php echo e(translate('sub_category_count')); ?></th>
            <th><?php echo e(translate('zone_count')); ?></th>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_manage_status')): ?>
                <th><?php echo e(translate('status')); ?></th>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_manage_status')): ?>
                <th><?php echo e(translate('Is_Featured')); ?></th>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['category_delete', 'category_update'])): ?>
                <th><?php echo e(translate('action')); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($categories->firstitem()+$key); ?></td>
                <td><?php echo e($category->name); ?></td>
                <td><?php echo e($category->children_count); ?></td>
                <td class="d-flex">
                    <div><?php echo e($category->zones_count); ?></div>
                    <?php if($category->zones_count < 1): ?>
                        <i class="material-icons" data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="<?php echo e(translate('This category is not under any zone. Kindly update the category with zone')); ?>">info
                        </i>
                    <?php endif; ?>
                </td>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_manage_status')): ?>
                    <td>
                        <label class="switcher">
                            <input class="switcher_input status-update"
                                   type="checkbox"
                                   <?php echo e($category->is_active?'checked':''); ?> data-id="<?php echo e($category->id); ?>">
                            <span class="switcher_control"></span>
                        </label>
                    </td>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_manage_status')): ?>
                    <td>
                        <label class="switcher">
                            <input class="switcher_input feature-update"
                                   type="checkbox"
                                   <?php echo e($category->is_featured?'checked':''); ?> data-featured="<?php echo e($category->id); ?>">
                            <span class="switcher_control"></span>
                        </label>
                    </td>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['category_delete', 'category_update'])): ?>
                    <td>
                        <div class="d-flex gap-2">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_update')): ?>
                                <a href="<?php echo e(route('admin.category.edit',[$category->id])); ?>"
                                   class="action-btn btn--light-primary demo_check"
                                   style="--size: 30px">
                                    <span class="material-icons">edit</span>
                                </a>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('category_delete')): ?>
                                <button type="button"
                                        data-id="delete-<?php echo e($category->id); ?>"
                                        data-message="<?php echo e(translate('want_to_delete_this_category')); ?>?"
                                        class="action-btn btn--danger <?php echo e(env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check'); ?>"
                                        style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                </button>
                                <form
                                    action="<?php echo e(route('admin.category.delete',[$category->id])); ?>"
                                    method="post" id="delete-<?php echo e($category->id); ?>"
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
                'colspan' => $categoryTableColspan,
                'variant' => request()->filled('search') ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-end">
        <?php echo $categories->links(); ?>

    </div>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/CategoryManagement/Resources/views/admin/partials/_table.blade.php ENDPATH**/ ?>