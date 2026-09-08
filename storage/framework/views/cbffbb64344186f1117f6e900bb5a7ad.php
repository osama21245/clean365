<?php $__env->startSection('title',translate('promotional_banners')); ?>

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
                        <h2 class="page-title"><?php echo e(translate('promotional_banners')); ?></h2>
                    </div>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_add')): ?>
                        <div class="card mb-30">
                            <div class="card-body p-30">
                                <form id="banner-create-form"
                                      action="<?php echo e(route('admin.banner.store')); ?>"
                                      method="POST"
                                      enctype="multipart/form-data"
                                      data-ff-validate novalidate>
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
                                                            'value'       => old('banner_title'),
                                                            'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="mb-2 fw-medium"><?php echo e(translate('Resource Type')); ?> <span class="text-danger">*</span></div>
                                                        <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                            <div class="custom-radio">
                                                                <input type="radio" id="category" name="resource_type"
                                                                       value="category"
                                                                       <?php echo e(old('resource_type', 'category') == 'category' ? 'checked' : ''); ?>>
                                                                <label for="category"><?php echo e(translate('Category Wise')); ?></label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="service" name="resource_type"
                                                                       value="service"
                                                                       <?php echo e(old('resource_type') == 'service' ? 'checked' : ''); ?>>
                                                                <label for="service"><?php echo e(translate('Service Wise')); ?></label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="redirect_link" name="resource_type"
                                                                       value="link"
                                                                       <?php echo e(old('resource_type') == 'link' ? 'checked' : ''); ?>>
                                                                <label for="redirect_link"><?php echo e(translate('Redirect Link')); ?></label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12" id="category_selector">
                                                        <?php echo $__env->make('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'category_id',
                                                            'id'          => 'category_id',
                                                            'label'       => translate('Category'),
                                                            'selectClass' => 'js-select theme-input-style w-100',
                                                            'optionNull'  => translate('Select Category'),
                                                            'options'     => $categories->pluck('name', 'id')->toArray(),
                                                            'value'       => old('category_id'),
                                                            'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </div>
                                                    <div class="col-12 service_selector" id="service_selector">
                                                        <?php echo $__env->make('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'service_id',
                                                            'id'          => 'service_id',
                                                            'label'       => translate('Service'),
                                                            'selectClass' => 'js-select theme-input-style w-100',
                                                            'optionNull'  => translate('Select Service'),
                                                            'options'     => $services->pluck('name', 'id')->toArray(),
                                                            'value'       => old('service_id'),
                                                            'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </div>
                                                    <div class="col-12 link_selector" id="link_selector">
                                                        <?php echo $__env->make('partials._form-field', [
                                                            'type'        => 'url',
                                                            'name'        => 'redirect_link',
                                                            'id'          => 'redirect_link_input',
                                                            'label'       => translate('Redirect Link'),
                                                            'placeholder' => translate('https://example.com'),
                                                            'icon'        => 'link',
                                                            'value'       => old('redirect_link'),
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
                                                        'required'         => true,
                                                        'image'            => null,
                                                        'ratio'            => 'ratio-2-1',
                                                        'instructionRatio' => '2:1'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                                <button type="reset" class="btn btn--secondary"><?php echo e(translate('reset')); ?></button>
                                                <button type="submit" class="btn btn--primary demo_check"><?php echo e(translate('submit')); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($resourceType=='all'?'active':''); ?>"
                                   href="<?php echo e(url()->current()); ?>?resource_type=all">
                                    <?php echo e(translate('all')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($resourceType=='category'?'active':''); ?>"
                                   href="<?php echo e(url()->current()); ?>?resource_type=category">
                                    <?php echo e(translate('category_wise')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($resourceType=='service'?'active':''); ?>"
                                   href="<?php echo e(url()->current()); ?>?resource_type=service">
                                    <?php echo e(translate('service_wise')); ?>

                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75"><?php echo e(translate('Total_Banners')); ?>:</span>
                            <span class="title-color"><?php echo e($banners->total()); ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="<?php echo e(url()->current()); ?>?resource_type=<?php echo e($resourceType); ?>"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="<?php echo e($search); ?>" name="search"
                                               placeholder="<?php echo e(translate('search_here')); ?>">
                                    </div>
                                    <button type="submit"
                                            class="btn btn--primary"><?php echo e(translate('search')); ?></button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_export')): ?>
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                            <span
                                                class="material-icons">file_download</span> <?php echo e(translate('download')); ?>

                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <a class="dropdown-item"
                                                   href="<?php echo e(route('admin.banner.download')); ?>?search=<?php echo e($search); ?>&resource_type=<?php echo e($resourceType); ?>">
                                                    <?php echo e(translate('excel')); ?>

                                                </a>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <?php
                                    $bannerTableColspan = 3;
                                ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_manage_status')): ?>
                                    <?php
                                        $bannerTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['banner_delete', 'banner_update'])): ?>
                                    <?php
                                        $bannerTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th><?php echo e(translate('sl')); ?></th>
                                        <th><?php echo e(translate('title')); ?></th>
                                        <th><?php echo e(translate('type')); ?></th>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_manage_status')): ?>
                                            <th><?php echo e(translate('status')); ?></th>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['banner_delete', 'banner_update'])): ?>
                                            <th><?php echo e(translate('action')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($key+$banners->firstItem()); ?></td>
                                            <td><?php echo e($item->banner_title); ?></td>
                                            <td><?php echo e($item->resource_type); ?></td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_manage_status')): ?>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input"
                                                               data-status="<?php echo e($item->id); ?>"
                                                               type="checkbox" <?php echo e($item->is_active?'checked':''); ?>>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['banner_delete', 'banner_update'])): ?>
                                                <td>
                                                    <div class="table-actions">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_update')): ?>
                                                            <a href="<?php echo e(route('admin.banner.edit',[$item->id])); ?>"
                                                               class="action-btn btn--light-primary">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner_delete')): ?>
                                                            <button type="button"
                                                                    data-id="<?php echo e($item->id); ?>"
                                                                    class="action-btn btn--danger delete_section">
                                                                <span class="material-icons">delete</span>
                                                            </button>
                                                            <form action="<?php echo e(route('admin.banner.delete',[$item->id])); ?>"
                                                                  method="post" id="delete-<?php echo e($item->id); ?>"
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
                                            'colspan' => $bannerTableColspan,
                                            'variant' => $search || $resourceType != 'all' ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <?php echo $banners->links(); ?>

                            </div>
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
        "use Strict";

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '<?php echo e(route('admin.banner.status-update', ['id' => ':itemId'])); ?>';
            route = route.replace(':itemId', itemId);
            route_alert(route, '<?php echo e(translate('want_to_update_status')); ?>');
        })

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '<?php echo e(translate('want_to_delete_this')); ?>');
        })

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
            var $form = $('#banner-create-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('select').each(function () {
                        var $s = $(this);
                        $s.find('option').each(function () { this.selected = this.defaultSelected; });
                        $s.trigger('change.select2');
                    });
                    $form.find('.global-image-upload').each(function () {
                        var $c = $(this);
                        $c.removeClass('has-image');
                        $c.find('.global-image-preview').attr('src', '').addClass('d-none');
                        $c.find('.global-upload-box').removeClass('global-upload-box-hidden');
                        $c.find('.overlay-icons').addClass('d-none');
                        $c.find('input[type="file"]').val('');
                    });
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
                FormValidator.register('#banner-create-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '<?php echo e(translate("Submitting...")); ?>'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/PromotionManagement/Resources/views/admin/promotional-banners/create.blade.php ENDPATH**/ ?>