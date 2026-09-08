<?php $__env->startSection('title', translate('edit_product')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.css"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $product->loadMissing('translations');
        $getTranslation = function ($key, $locale) use ($product) {
            return optional($product->translations->where('key', $key)->where('locale', $locale)->first())->value;
        };
    ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('edit_product')); ?></h2>
                    </div>
                    <div class="card-wrap">
                        <div class="card-body-inner">
                            <form action="<?php echo e(route('admin.product.update', [$product->id])); ?>" method="post" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="row service-description-wrapper">
                                    <div class="col-xxl-9 col-lg-8 mb-5 mb-lg-0">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="mb-20">
                                                    <h3 class="mb-1 text-dark"><?php echo e(translate('Basic Setup')); ?></h3>
                                                    <p class="fs-12 text-color"><?php echo e(translate('Update product details')); ?></p>
                                                </div>
                                                <div class="bg-light p-xxl-20 p-12px rounded">
                                                    <?php ($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first()); ?>
                                                    <?php if($language): ?>
                                                        <ul class="nav nav--tabs text-nowrap overflow-auto flex-nowrap border-color-primary mb-4">
                                                            <li class="nav-item">
                                                                <a class="nav-link lang_link active" href="#" id="default-link"><?php echo e(translate('default')); ?></a>
                                                            </li>
                                                            <?php $__currentLoopData = $language?->live_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link lang_link" href="#" id="<?php echo e($lang['code']); ?>-link"><?php echo e(get_language_name($lang['code'])); ?></a>
                                                                </li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </ul>
                                                    <?php endif; ?>

                                                    <?php if($language): ?>
                                                        <div class="lang-form" id="default-form">
                                                            <div class="mb-30">
                                                                <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('Product Name (:label)', ['label' => translate('default')])); ?> <span class="text-danger">*</span></label>
                                                                <input type="text" name="name[]" class="form-control" required value="<?php echo e(old('name.0', $product->getRawOriginal('name'))); ?>">
                                                            </div>
                                                            <div class="mb-30">
                                                                <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('Short Description (:label)', ['label' => translate('default')])); ?></label>
                                                                <textarea name="short_description[]" class="form-control" rows="3"><?php echo e(old('short_description.0', $product->getRawOriginal('short_description'))); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="default">

                                                        <?php $__currentLoopData = $language?->live_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="d-none lang-form" id="<?php echo e($lang['code']); ?>-form">
                                                                <div class="mb-30">
                                                                    <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('Product Name (:code)', ['code' => strtoupper($lang['code'])])); ?></label>
                                                                    <input type="text" name="name[]" class="form-control" value="<?php echo e($getTranslation('name', $lang['code'])); ?>">
                                                                </div>
                                                                <div class="mb-30">
                                                                    <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('Short Description (:code)', ['code' => strtoupper($lang['code'])])); ?></label>
                                                                    <textarea name="short_description[]" class="form-control" rows="3"><?php echo e($getTranslation('short_description', $lang['code'])); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="<?php echo e($lang['code']); ?>">
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php endif; ?>

                                                    <hr class="my-4">

                                                    <div class="row">
                                                        <div class="col-md-6 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('category')); ?> <span class="text-danger">*</span></label>
                                                            <select name="product_category_id" class="select2 form-control" required>
                                                                <option value=""><?php echo e(translate('select_category')); ?></option>
                                                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($category->id); ?>" <?php echo e(old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : ''); ?>>
                                                                        <?php echo e($category->name); ?>

                                                                    </option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('badge')); ?></label>
                                                            <select name="badge" class="select2 form-control">
                                                                <option value=""><?php echo e(translate('none')); ?></option>
                                                                <option value="new" <?php echo e(old('badge', $product->badge) == 'new' ? 'selected' : ''); ?>><?php echo e(translate('new')); ?></option>
                                                                <option value="sale" <?php echo e(old('badge', $product->badge) == 'sale' ? 'selected' : ''); ?>><?php echo e(translate('sale')); ?></option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('price')); ?> (<?php echo e(currency_symbol()); ?>) <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" min="0" name="price" class="form-control" required value="<?php echo e(old('price', $product->price)); ?>">
                                                        </div>
                                                        <div class="col-md-4 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('sale_price')); ?> (<?php echo e(currency_symbol()); ?>)</label>
                                                            <input type="number" step="0.01" min="0" name="sale_price" class="form-control" value="<?php echo e(old('sale_price', $product->sale_price)); ?>">
                                                        </div>
                                                        <div class="col-md-4 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium"><?php echo e(translate('rating')); ?></label>
                                                            <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="<?php echo e(old('rating', $product->rating)); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-end gap-3">
                                                        <a href="<?php echo e(route('admin.product.list')); ?>" class="btn btn--secondary"><?php echo e(translate('Cancel')); ?></a>
                                                        <button type="submit" class="btn btn--primary"><?php echo e(translate('Update')); ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-3 col-lg-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="bg-light rounded w-100 mb-30 p-3">
                                                    <?php echo $__env->make('adminmodule::admin.partials._single-image-upload', [
                                                        'name' => 'thumbnail',
                                                        'id' => 'productThumbnail',
                                                        'title' => translate('product_image'),
                                                        'subtitle' => translate('Upload your product Image'),
                                                        'required' => false,
                                                        'image' => $product->thumbnail_full_path,
                                                        'ratio' => 'ratio-1-1',
                                                        'height' => 'h-120',
                                                        'instructionRatio' => '1:1',
                                                        'showView' => true,
                                                        'showEdit' => true,
                                                        'showDelete' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.js"></script>
    <script>
        $(".lang_link").click(function (e) {
            e.preventDefault();
            $('.lang_link').removeClass('active');
            $(this).addClass('active');
            let lang = $(this).attr('id').split('-')[0];
            $('.lang-form').addClass('d-none');
            $("#" + lang + "-form").removeClass('d-none');
        });
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServiceManagement/Resources/views/admin/product/edit.blade.php ENDPATH**/ ?>