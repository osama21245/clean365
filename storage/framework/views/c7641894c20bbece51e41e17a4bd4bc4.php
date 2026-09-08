<?php $__env->startSection('title', translate('properties')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('properties')); ?></h2>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3"><?php echo e(translate('add_new_property')); ?></h4>
                            <form action="<?php echo e(route('admin.property.store')); ?>" method="post">
                                <?php echo csrf_field(); ?>
                                <div class="mb-30">
                                    <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('name')); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo e(old('name')); ?>"
                                        placeholder="<?php echo e(translate('Enter property name')); ?>">
                                </div>
                                <div class="mb-30">
                                    <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('description')); ?></label>
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="<?php echo e(translate('Enter property description')); ?>"><?php echo e(old('description')); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn--primary w-100"><?php echo e(translate('Submit')); ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="<?php echo e(url()->current()); ?>" class="search-form search-form_style-two" method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="<?php echo e($search); ?>" name="search" placeholder="<?php echo e(translate('search_here')); ?>">
                                    </div>
                                    <button type="submit" class="btn btn--primary"><?php echo e(translate('search')); ?></button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th><?php echo e(translate('Sl')); ?></th>
                                            <th><?php echo e(translate('name')); ?></th>
                                            <th><?php echo e(translate('packages')); ?></th>
                                            <th><?php echo e(translate('status')); ?></th>
                                            <th><?php echo e(translate('action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($properties->firstItem() + $key); ?></td>
                                                <td>
                                                    <div class="fw-medium"><?php echo e($property->name); ?></div>
                                                    <?php if($property->description): ?>
                                                        <div class="fs-12 text-muted">
                                                            <?php echo e(\Illuminate\Support\Str::limit($property->description, 60)); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($property->packages_count); ?></td>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input status-change" type="checkbox"
                                                            <?php echo e($property->is_active ? 'checked' : ''); ?>

                                                            data-url="<?php echo e(route('admin.property.status-update', [$property->id])); ?>">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editProperty-<?php echo e($property->id); ?>">
                                                            <span class="material-icons">edit</span>
                                                        </button>
                                                        <button type="button" data-id="property-<?php echo e($property->id); ?>"
                                                            data-message="<?php echo e(translate('want_to_delete_this_property')); ?>?"
                                                            class="btn btn-outline-danger btn-sm form-alert">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                        <form action="<?php echo e(route('admin.property.delete', [$property->id])); ?>"
                                                            method="post" id="property-<?php echo e($property->id); ?>" class="hidden">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                        </form>
                                                    </div>

                                                    <div class="modal fade" id="editProperty-<?php echo e($property->id); ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form
                                                                    action="<?php echo e(route('admin.property.update', [$property->id])); ?>"
                                                                    method="post">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('PUT'); ?>
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title"><?php echo e(translate('edit_property')); ?>

                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-30">
                                                                            <label
                                                                                class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('name')); ?>

                                                                                <span class="text-danger">*</span></label>
                                                                            <input type="text" name="name" class="form-control"
                                                                                required value="<?php echo e($property->name); ?>">
                                                                        </div>
                                                                        <div class="mb-30">
                                                                            <label
                                                                                class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('description')); ?></label>
                                                                            <textarea name="description" class="form-control"
                                                                                rows="3"><?php echo e($property->description); ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn--secondary"
                                                                            data-bs-dismiss="modal"><?php echo e(translate('Cancel')); ?></button>
                                                                        <button type="submit"
                                                                            class="btn btn--primary"><?php echo e(translate('Update')); ?></button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', ['text' => 'no_data_found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <?php echo $properties->links(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/property/list.blade.php ENDPATH**/ ?>