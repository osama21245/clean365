<?php $__env->startSection('title', translate('store_orders')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('store_orders')); ?></h2>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <?php $__currentLoopData = ['all','pending','confirmed','processing','out_for_delivery','delivered','canceled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo e($orderStatus==$status?'active':''); ?>"
                                       href="<?php echo e(route('admin.product-order.list', ['order_status' => $status, 'search' => $search])); ?>">
                                        <?php echo e(translate($status)); ?>

                                        <span class="badge bg-light text-dark ms-1"><?php echo e($statusCounts[$status] ?? 0); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="<?php echo e(url()->current()); ?>" class="search-form search-form_style-two" method="GET">
                                    <input type="hidden" name="order_status" value="<?php echo e($orderStatus); ?>">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="<?php echo e($search); ?>" name="search"
                                               placeholder="<?php echo e(translate('search_here')); ?>">
                                    </div>
                                    <button type="submit" class="btn btn--primary"><?php echo e(translate('search')); ?></button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th><?php echo e(translate('Sl')); ?></th>
                                        <th><?php echo e(translate('order_id')); ?></th>
                                        <th><?php echo e(translate('customer')); ?></th>
                                        <th><?php echo e(translate('items')); ?></th>
                                        <th><?php echo e(translate('total')); ?></th>
                                        <th><?php echo e(translate('order_status')); ?></th>
                                        <th><?php echo e(translate('date')); ?></th>
                                        <th><?php echo e(translate('action')); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($orders->firstItem() + $key); ?></td>
                                            <td>
                                                <span class="fw-medium">#<?php echo e(\Illuminate\Support\Str::limit($order->id, 8, '')); ?></span>
                                            </td>
                                            <td>
                                                <?php if($order->customer): ?>
                                                    <?php echo e(trim(($order->customer->first_name ?? '') . ' ' . ($order->customer->last_name ?? '')) ?: ($order->customer->phone ?? translate('N/A'))); ?>

                                                    <div class="fs-12 text-muted"><?php echo e($order->customer->phone); ?></div>
                                                <?php else: ?>
                                                    <?php echo e(translate('guest')); ?>

                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($order->total_quantity); ?></td>
                                            <td><?php echo e(with_currency_symbol($order->total)); ?></td>
                                            <td>
                                                <span class="badge
                                                    <?php if($order->order_status=='pending'): ?> bg-warning
                                                    <?php elseif($order->order_status=='canceled'): ?> bg-danger
                                                    <?php elseif($order->order_status=='delivered'): ?> bg-success
                                                    <?php else: ?> bg-primary <?php endif; ?>">
                                                    <?php echo e(translate($order->order_status)); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($order->created_at?->format('Y-m-d H:i')); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('admin.product-order.details', [$order->id])); ?>"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <span class="material-icons">visibility</span>
                                                </a>
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
                                <?php echo $orders->links(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/product-order/list.blade.php ENDPATH**/ ?>