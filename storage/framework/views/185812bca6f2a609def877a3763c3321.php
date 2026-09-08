<?php $__env->startSection('title',translate('Promotional Banner Update')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/select.dataTables.min.css"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('Promotional Banner Update')); ?></h2>
                    </div>
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_update')): ?>
                            <form id="banner-edit-form"
                                  action="<?php echo e(route('admin.banner.update',[$banner->id])); ?>"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                <?php echo method_field('PUT'); ?>
                                <?php echo csrf_field(); ?>
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <?php echo $__env->make('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'banner_title',
                                                        'id'          => 'banner_title',
                                                        'label'       => translate('Banner Title'),
                                                        'placeholder' => translate('Enter Banner Title'),
                                                        'icon'        => 'title',
                                                        'required'    => true,
                                                        'maxlength'   => 190,
                                                        'charCount'   => true,
                                                        'value'       => old('banner_title', $banner->banner_title),
                                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-2 fw-medium"><?php echo e(translate('Resource Type')); ?> <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="category" name="resource_type" value="category"
                                                                <?php echo e(old('resource_type', $banner->resource_type) == 'category' ? 'checked' : ''); ?>>
                                                            <label for="category"><?php echo e(translate('Category Wise')); ?></label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="service" name="resource_type" value="service"
                                                                <?php echo e(old('resource_type', $banner->resource_type) == 'service' ? 'checked' : ''); ?>>
                                                            <label for="service"><?php echo e(translate('Service Wise')); ?></label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="redirect_link" name="resource_type" value="link"
                                                                <?php echo e(old('resource_type', $banner->resource_type) == 'link' ? 'checked' : ''); ?>>
                                                            <label for="redirect_link"><?php echo e(translate('Redirect Link')); ?></label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="category_selector" style="display: <?php echo e(old('resource_type', $banner->resource_type) == 'category' ? 'block' : 'none'); ?>">
                                                    <?php echo $__env->make('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'category_id',
                                                        'id'          => 'category_id',
                                                        'label'       => translate('Category'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Category'),
                                                        'options'     => $categories->pluck('name', 'id')->toArray(),
                                                        'value'       => old('category_id', $banner->resource_type == 'category' ? $banner->resource_id : ''),
                                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>
                                                <div class="col-12" id="service_selector" style="display: <?php echo e(old('resource_type', $banner->resource_type) == 'service' ? 'block' : 'none'); ?>">
                                                    <?php echo $__env->make('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'service_id',
                                                        'id'          => 'service_id',
                                                        'label'       => translate('Service'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Service'),
                                                        'options'     => $services->pluck('name', 'id')->toArray(),
                                                        'value'       => old('service_id', $banner->resource_type == 'service' ? $banner->resource_id : ''),
                                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>
                                                <div class="col-12" id="link_selector" style="display: <?php echo e(old('resource_type', $banner->resource_type) == 'link' ? 'block' : 'none'); ?>">
                                                    <?php echo $__env->make('partials._form-field', [
                                                        'type'        => 'url',
                                                        'name'        => 'redirect_link',
                                                        'id'          => 'redirect_link_input',
                                                        'label'       => translate('Redirect Link'),
                                                        'placeholder' => translate('https://example.com'),
                                                        'icon'        => 'link',
                                                        'value'       => old('redirect_link', $banner->redirect_link),
                                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                <?php echo $__env->make('adminmodule::admin.partials._single-image-upload', [
                                                    'name'             => 'banner_image',
                                                    'id'               => 'bannerImage',
                                                    'title'            => translate('Cover Image'),
                                                    'subtitle'         => translate('Upload banner image'),
                                                    'required'         => false,
                                                    'image'            => $banner->banner_image_full_path,
                                                    'ratio'            => 'ratio-2-1',
                                                    'instructionRatio' => '2:1',
                                                    'showDelete'       => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                            <button type="reset" class="btn btn--secondary"><?php echo e(translate('reset')); ?></button>
                                            <button type="submit" class="btn btn--primary demo_check"><?php echo e(translate('update')); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/select2/select2.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/dataTables/dataTables.select.min.js"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            $('.js-select').select2({ width: '100%' });
            checkResourceType();
        });

        function checkResourceType() {
            if ($('#category').is(':checked')) {
                $('#category_selector').show();
                $('#service_selector').hide();
                $('#link_selector').hide();
            } else if ($('#service').is(':checked')) {
                $('#category_selector').hide();
                $('#service_selector').show();
                $('#link_selector').hide();
            } else if ($('#redirect_link').is(':checked')) {
                $('#category_selector').hide();
                $('#service_selector').hide();
                $('#link_selector').show();
            }
        }

        $('#category, #service, #redirect_link').on('click change', function () {
            checkResourceType();
        });

        (function () {
            var $form = $('#banner-edit-form');
            if (!$form.length) return;

            var originalBannerImage = <?php echo json_encode($banner->banner_image_full_path ?? '', 15, 512) ?>;

            function setBannerImagePreview(src) {
                var $wrapper = $form.find('.global-image-upload').first();
                if (!$wrapper.length) return;
                if (src) {
                    $wrapper.addClass('has-image');
                    $wrapper.find('.global-image-preview').attr('src', src).removeClass('d-none');
                    $wrapper.find('.global-upload-box').addClass('global-upload-box-hidden');
                    $wrapper.find('.overlay-icons').removeClass('d-none');
                } else {
                    $wrapper.removeClass('has-image');
                    $wrapper.find('.global-image-preview').attr('src', '').addClass('d-none');
                    $wrapper.find('.global-upload-box').removeClass('global-upload-box-hidden');
                    $wrapper.find('.overlay-icons').addClass('d-none');
                }
                $wrapper.find('input[type="file"]').val('');
            }

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('select').each(function () {
                        var $s = $(this);
                        $s.find('option').each(function () { this.selected = this.defaultSelected; });
                        $s.trigger('change.select2');
                    });
                    setBannerImagePreview(originalBannerImage);
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () {
                            window.FormCharCount.update(this);
                        });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                    checkResourceType();
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#banner-edit-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '<?php echo e(translate("Updating...")); ?>'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/PromotionManagement/Resources/views/admin/promotional-banners/edit.blade.php ENDPATH**/ ?>