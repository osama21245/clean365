<?php $__env->startSection('title', translate('product_list')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h2 class="page-title"><?php echo e(translate('product_list')); ?></h2>
                        <a href="<?php echo e(route('admin.product.create')); ?>" class="btn btn--primary">
                            <span class="material-icons">add</span>
                            <?php echo e(translate('add_new_product')); ?>

                        </a>
                    </div>

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

                                <div class="d-flex gap-2 fw-medium align-items-center">
                                    <span class="opacity-75"><?php echo e(translate('Total_Products')); ?>:</span>
                                    <span class="title-color"><?php echo e($products->total()); ?></span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th><?php echo e(translate('Sl')); ?></th>
                                            <th><?php echo e(translate('image')); ?></th>
                                            <th><?php echo e(translate('name')); ?></th>
                                            <th><?php echo e(translate('category')); ?></th>
                                            <th><?php echo e(translate('price')); ?></th>
                                            <th><?php echo e(translate('badge')); ?></th>
                                            <th><?php echo e(translate('status')); ?></th>
                                            <th><?php echo e(translate('action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($products->firstItem() + $key); ?></td>
                                                <td>
                                                    <img width="50" class="rounded" src="<?php echo e($product->thumbnail_full_path); ?>"
                                                        alt="<?php echo e($product->name); ?>">
                                                </td>
                                                <td><?php echo e($product->name); ?></td>
                                                <td><?php echo e($product->category?->name ?? translate('N/A')); ?></td>
                                                <td>
                                                    <?php if($product->sale_price): ?>
                                                        <span
                                                            class="text-decoration-line-through text-muted me-1"><?php echo e(with_currency_symbol($product->price)); ?></span>
                                                        <span class="fw-bold"><?php echo e(with_currency_symbol($product->sale_price)); ?></span>
                                                    <?php else: ?>
                                                        <?php echo e(with_currency_symbol($product->price)); ?>

                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($product->badge): ?>
                                                        <span
                                                            class="badge bg-<?php echo e($product->badge == 'sale' ? 'danger' : 'success'); ?>">
                                                            <?php echo e(translate($product->badge)); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input status-change" type="checkbox"
                                                            <?php echo e($product->is_active ? 'checked' : ''); ?>

                                                            data-url="<?php echo e(route('admin.product.status-update', [$product->id])); ?>">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="<?php echo e(route('admin.product.edit', [$product->id])); ?>"
                                                            class="btn btn-outline-primary btn-sm">
                                                            <span class="material-icons">edit</span>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger btn-sm form-alert"
                                                            data-id="product-<?php echo e($product->id); ?>"
                                                            data-message="<?php echo e(translate('want_to_delete_this_product')); ?>?">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                        <form action="<?php echo e(route('admin.product.delete', [$product->id])); ?>"
                                                            method="post" id="product-<?php echo e($product->id); ?>" class="hidden">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', ['text' => 'no_data_found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <?php echo $products->links(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/product/list.blade.php ENDPATH**/ ?>