<?php $__env->startSection('title', translate('order_details')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h2 class="page-title"><?php echo e(translate('order_details')); ?></h2>
                <a href="<?php echo e(route('admin.product-order.list')); ?>" class="btn btn--secondary"><?php echo e(translate('back')); ?></a>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                                <div>
                                    <h4 class="mb-1">#<?php echo e($order->id); ?></h4>
                                    <p class="mb-0 text-muted"><?php echo e($order->created_at?->format('Y-m-d H:i')); ?></p>
                                </div>
                                <span class="badge
                                    <?php if($order->order_status=='pending'): ?> bg-warning
                                    <?php elseif($order->order_status=='canceled'): ?> bg-danger
                                    <?php elseif($order->order_status=='delivered'): ?> bg-success
                                    <?php else: ?> bg-primary <?php endif; ?> fs-14">
                                    <?php echo e(translate($order->order_status)); ?>

                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th><?php echo e(translate('product')); ?></th>
                                        <th><?php echo e(translate('price')); ?></th>
                                        <th><?php echo e(translate('quantity')); ?></th>
                                        <th><?php echo e(translate('total')); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img width="45" class="rounded" src="<?php echo e($detail->thumbnail_full_path); ?>" alt="">
                                                    <span><?php echo e($detail->product_name); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo e(with_currency_symbol($detail->unit_price)); ?></td>
                                            <td><?php echo e($detail->quantity); ?></td>
                                            <td><?php echo e(with_currency_symbol($detail->total_price)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-3"><?php echo e(translate('delivery_details')); ?></h4>
                            <p class="mb-2"><strong><?php echo e(translate('address')); ?>:</strong> <?php echo e($order->delivery_address); ?></p>
                            <p class="mb-0"><strong><?php echo e(translate('location')); ?>:</strong> <?php echo e($order->delivery_lat); ?>, <?php echo e($order->delivery_lng); ?></p>
                            <?php if($order->note): ?>
                                <hr>
                                <p class="mb-0"><strong><?php echo e(translate('note')); ?>:</strong> <?php echo e($order->note); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-3"><?php echo e(translate('customer')); ?></h4>
                            <?php if($order->customer): ?>
                                <p class="mb-1"><?php echo e(trim(($order->customer->first_name ?? '') . ' ' . ($order->customer->last_name ?? ''))); ?></p>
                                <p class="mb-1"><?php echo e($order->customer->phone); ?></p>
                                <p class="mb-0"><?php echo e($order->customer->email); ?></p>
                            <?php else: ?>
                                <p class="mb-0"><?php echo e(translate('guest')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-3"><?php echo e(translate('order_summary')); ?></h4>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo e(translate('subtotal')); ?> (<?php echo e($order->total_quantity); ?>)</span>
                                <span><?php echo e(with_currency_symbol($order->subtotal)); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo e(translate('discount')); ?></span>
                                <span class="text-success">-<?php echo e(with_currency_symbol($order->discount_amount)); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo e(translate('delivery_fee')); ?></span>
                                <span><?php echo e(with_currency_symbol($order->delivery_fee)); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span><?php echo e(translate('total')); ?></span>
                                <span><?php echo e(with_currency_symbol($order->total)); ?></span>
                            </div>
                            <div class="mt-2 fs-12 text-muted">
                                <?php echo e(translate('payment_method')); ?>: <?php echo e(translate($order->payment_method)); ?>

                                <?php if($order->coupon_code): ?>
                                    <br><?php echo e(translate('coupon')); ?>: <?php echo e($order->coupon_code); ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3"><?php echo e(translate('update_order_status')); ?></h4>
                            <form action="<?php echo e(route('admin.product-order.status-update', [$order->id])); ?>" method="post">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <select name="order_status" class="form-control" required>
                                        <?php $__currentLoopData = ['pending','confirmed','processing','out_for_delivery','delivered','canceled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status); ?>" <?php echo e($order->order_status == $status ? 'selected' : ''); ?>>
                                                <?php echo e(translate($status)); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn--primary w-100"><?php echo e(translate('Update')); ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/product-order/details.blade.php ENDPATH**/ ?>