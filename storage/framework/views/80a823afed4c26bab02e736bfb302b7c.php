<?php $__env->startSection('title', translate('تعديل خدمة إضافية')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <style>
        .feature-row-input {
            transition: all 0.2s ease;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex align-items-center justify-content-between">
                        <h2 class="page-title"><?php echo e(translate('تعديل الخدمة الإضافية')); ?></h2>
                        <a href="<?php echo e(route('admin.additional-service.index')); ?>" class="btn btn--primary">
                            <span class="material-icons">arrow_back</span> <?php echo e(translate('العودة للقائمة')); ?>

                        </a>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form action="<?php echo e(route('admin.additional-service.update', [$service->id])); ?>" method="post"
                                enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('put'); ?>

                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-label"><?php echo e(translate('الاسم الرئيسي')); ?>

                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" id="name" class="form-control"
                                                        value="<?php echo e(old('name', $service->name)); ?>" required maxlength="191">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="price" class="form-label"><?php echo e(translate('السعر')); ?></label>
                                                    <input type="number" step="0.01" name="price" id="price"
                                                        class="form-control" value="<?php echo e(old('price', $service->price)); ?>"
                                                        min="0">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="sort_order"
                                                        class="form-label"><?php echo e(translate('الترتيب')); ?></label>
                                                    <input type="number" name="sort_order" id="sort_order"
                                                        class="form-control"
                                                        value="<?php echo e(old('sort_order', $service->sort_order)); ?>" min="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 text-center">
                                        <?php echo $__env->make('adminmodule::admin.partials._single-image-upload', [
                                            'name' => 'icon',
                                            'id' => 'additionalServiceIcon',
                                            'title' => translate('صورة الخدمة'),
                                            'subtitle' => translate('رفع صورة الخدمة الإضافية'),
                                            'required' => false,
                                            'image' => $service->icon ? $service->icon_full_path : null,
                                            'instructionRatio' => '1:1'
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label
                                                    class="form-label mb-0 fw-semibold"><?php echo e(translate('الفيتشرز (الميزات مع السعر والأيقونة)')); ?></label>
                                                <button type="button"
                                                    class="btn btn-sm btn--primary-light d-flex align-items-center gap-1"
                                                    id="add-feature-btn">
                                                    <span class="material-icons fs-16">add</span>
                                                    <?php echo e(translate('إضافة ميزة أخرى')); ?>

                                                </button>
                                            </div>

                                            <div id="features-wrapper">
                                                <?php
                                                    $features = is_array($service->features) && count($service->features) > 0 ? $service->features : [['title' => '', 'price' => 0, 'icon' => null, 'icon_full_path' => null]];
                                                ?>
                                                <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="row g-2 align-items-center mb-2 feature-row-input">
                                                        <div class="col-md-5">
                                                            <textarea name="feature_titles[<?php echo e($index); ?>]" class="form-control"
                                                                rows="2"
                                                                placeholder="<?php echo e(translate('اسم الميزة (أضف أسطر جديدة لإضافة أكثر من ميزة)')); ?>"><?php echo e($feature['title'] ?? ''); ?></textarea>
                                                            <input type="hidden" name="feature_existing_icons[<?php echo e($index); ?>]"
                                                                value="<?php echo e($feature['icon'] ?? ''); ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" step="0.01" name="feature_prices[<?php echo e($index); ?>]"
                                                                class="form-control" value="<?php echo e($feature['price'] ?? 0); ?>"
                                                                placeholder="<?php echo e(translate('السعر (اختياري)')); ?>" min="0">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="file" name="feature_icons[<?php echo e($index); ?>]"
                                                                class="form-control" accept="image/*">
                                                            <?php if(!empty($feature['icon_full_path'])): ?>
                                                                <div class="mt-1">
                                                                    <img src="<?php echo e($feature['icon_full_path']); ?>" alt=""
                                                                        style="width: 28px; height: 28px; object-fit: contain;"
                                                                        class="border rounded p-1">
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-1 text-end">
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm remove-feature-btn"
                                                                style="min-width: 38px; height: 38px;">
                                                                <span class="material-icons">delete</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="<?php echo e(route('admin.additional-service.index')); ?>"
                                        class="btn btn-secondary"><?php echo e(translate('إلغاء')); ?></a>
                                    <button type="submit"
                                        class="btn btn--primary"><?php echo e(translate('تحديث البيانات')); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script>
        $(document).ready(function () {
            var rowIndex = <?php echo e(count($features)); ?>;

            $('#add-feature-btn').on('click', function () {
                var newRow = `
                                    <div class="row g-2 align-items-center mb-2 feature-row-input">
                                        <div class="col-md-5">
                                            <textarea name="feature_titles[${rowIndex}]" class="form-control" rows="2" placeholder="<?php echo e(translate('اسم الميزة (أضف أسطر جديدة لإضافة أكثر من ميزة)')); ?>"></textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" step="0.01" name="feature_prices[${rowIndex}]" class="form-control" placeholder="<?php echo e(translate('السعر (اختياري)')); ?>" min="0">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="file" name="feature_icons[${rowIndex}]" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-feature-btn" style="min-width: 38px; height: 38px;">
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </div>
                                    </div>`;
                $('#features-wrapper').append(newRow);
                rowIndex++;
            });

            $(document).on('click', '.remove-feature-btn', function () {
                if ($('#features-wrapper .feature-row-input').length > 1) {
                    $(this).closest('.feature-row-input').remove();
                } else {
                    $(this).closest('.feature-row-input').find('input').val('');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/additional-services/edit.blade.php ENDPATH**/ ?>