<?php $__env->startSection('title',translate('Update Serviceman')); ?>

<?php $__env->startSection('content'); ?>
    <?php ($currentProfileImage = $serviceman->user->profile_image_full_path ?? null); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title"><?php echo e(translate('Update Serviceman')); ?></h2>
            </div>

            <form id="serviceman-edit-form"
                  action="<?php echo e(route('admin.serviceman.update',[$serviceman->id])); ?>"
                  method="post"
                  enctype="multipart/form-data"
                  data-ff-validate novalidate>
                <?php echo method_field('put'); ?>
                <?php echo csrf_field(); ?>

                <div class="card mb-30">
                    <div class="card-body p-30">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                    <h4 class="c1 mb-20"><?php echo e(translate('General Information')); ?></h4>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'first_name',
                                                'label'       => translate('First Name'),
                                                'placeholder' => translate('Enter First Name'),
                                                'icon'        => 'account_circle',
                                                'required'    => true,
                                                'maxlength'   => 60,
                                                'charCount'   => true,
                                                'value'       => old('first_name', $serviceman->user->first_name),
                                                'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'last_name',
                                                'label'       => translate('Last Name'),
                                                'placeholder' => translate('Enter Last Name'),
                                                'icon'        => 'account_circle',
                                                'required'    => true,
                                                'maxlength'   => 60,
                                                'charCount'   => true,
                                                'value'       => old('last_name', $serviceman->user->last_name),
                                                'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-12">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type'        => 'tel',
                                                'name'        => 'phone',
                                                'id'          => 'phone',
                                                'label'       => translate('Phone Number'),
                                                'placeholder' => translate('Enter Phone Number'),
                                                'required'    => true,
                                                'autocomplete'=> 'tel',
                                                'value'       => old('phone', $serviceman->user->phone),
                                                'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                    <div class="w-100" style="max-width: 280px;">
                                        <?php echo $__env->make('adminmodule::admin.partials._single-image-upload', [
                                            'name'             => 'profile_image',
                                            'id'               => 'profile_image',
                                            'title'            => translate('Profile Image'),
                                            'subtitle'         => null,
                                            'required'         => false,
                                            'image'            => $currentProfileImage,
                                            'instructionRatio' => '1:1',
                                            'height'           => null,
                                            'showView'         => true,
                                            'showEdit'         => true,
                                            'showDelete'       => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-30">
                    <div class="card-body p-30">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                    <h4 class="c1 mb-20"><?php echo e(translate('Business Information')); ?></h4>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type'        => 'select',
                                                'name'        => 'identity_type',
                                                'id'          => 'identity_type',
                                                'label'       => translate('Identity Type'),
                                                'required'    => true,
                                                'selectClass' => 'select-identity theme-input-style',
                                                'optionNull'  => translate('Select Identity Type'),
                                                'options'     => [
                                                    'passport'        => translate('Passport'),
                                                    'driving_license' => translate('Driving License'),
                                                    'nid'             => translate('NID'),
                                                    'trade_license'   => translate('Trade License')],
                                                'value'       => old('identity_type', $serviceman->user->identification_type),
                                                'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-12">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'identity_number',
                                                'label'       => translate('Identity Number'),
                                                'placeholder' => translate('Enter Identity Number'),
                                                'icon'        => 'badge',
                                                'required'    => true,
                                                'maxlength'   => 50,
                                                'charCount'   => true,
                                                'value'       => old('identity_number', $serviceman->user->identification_number),
                                                'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                    <?php echo $__env->make('adminmodule::admin.partials._multiple-image-upload', [
                                        'name'             => 'identity_images[]',
                                        'id'               => 'identity_images',
                                        'title'            => translate('Identification Image'),
                                        'subtitle'         => null,
                                        'instructionRatio' => '2:1',
                                        'required'         => true,
                                        'images'           => $serviceman->user->identification_image_full_path,
                                        'imageNames'       => $serviceman->user->identification_image ?? [],
                                        'maxCount'         => 2,
                                        'imagePath'        => 'serviceman/profile/',
                                        'ratio'            => 'ratio-2-1',
                                        'defaultImagePath' => asset('public/assets/admin-module/img/media/provider-id.png')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-30">
                    <div class="card-body p-30">
                        <div class="bg-light rounded p-xxl-4 p-3">
                            <h4 class="c1 mb-20"><?php echo e(translate('Account Information')); ?></h4>
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <?php echo $__env->make('partials._form-field', [
                                        'type'        => 'email',
                                        'name'        => 'email',
                                        'label'       => translate('Email'),
                                        'placeholder' => translate('ex: abc@email.com'),
                                        'icon'        => 'mail',
                                        'required'    => true,
                                        'autocomplete'=> 'email',
                                        'value'       => old('email', $serviceman->user->email),
                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                                <div class="col-lg-4">
                                    <?php echo $__env->make('partials._form-field', [
                                        'type'        => 'password',
                                        'name'        => 'password',
                                        'id'          => 'pass',
                                        'label'       => translate('Password'),
                                        'placeholder' => translate('Enter New Password'),
                                        'icon'        => 'lock',
                                        'minlength'   => 8,
                                        'hint'        => translate('Leave blank to keep current password'),
                                        'autocomplete'=> 'new-password',
                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                                <div class="col-lg-4">
                                    <?php echo $__env->make('partials._form-field', [
                                        'type'        => 'password',
                                        'name'        => 'confirm_password',
                                        'id'          => 'confirm_password',
                                        'label'       => translate('Confirm Password'),
                                        'placeholder' => translate('Re-enter Password'),
                                        'icon'        => 'lock',
                                        'minlength'   => 8,
                                        'autocomplete'=> 'new-password',
                                        'extraAttrs'  => 'data-ff-match="#pass"',
                                        'wrapClass'   => 'mb-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                    <button type="reset" class="btn btn--secondary"><?php echo e(translate('Reset')); ?></button>
                    <button type="submit" class="btn btn--primary demo_check"><?php echo e(translate('Update')); ?></button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('public/assets/provider-module/js/spartan-multi-image-picker.js')); ?>"></script>
    <script>
        "use strict";

        var servicemanIdentificationImageCount = <?php echo e(count($serviceman->user->identification_image ?? [])); ?>;
        var servicemanMaxSizeReadable = "<?php echo e(readableUploadMaxFileSize('image')); ?>";
        var servicemanMaxFileSize = 2 * 1024 * 1024;

        if (servicemanMaxSizeReadable.toLowerCase().includes('mb')) {
            servicemanMaxFileSize = parseFloat(servicemanMaxSizeReadable) * 1024 * 1024;
        } else if (servicemanMaxSizeReadable.toLowerCase().includes('kb')) {
            servicemanMaxFileSize = parseFloat(servicemanMaxSizeReadable) * 1024;
        }

        var servicemanMaxCount;
        if (servicemanIdentificationImageCount === 0) {
            servicemanMaxCount = 2;
        } else if (servicemanIdentificationImageCount === 1) {
            servicemanMaxCount = 1;
        } else {
            servicemanMaxCount = 0;
        }

        function servicemanSetAcceptForAllInputs() {
            const allowedExtensions = ".<?php echo e(implode(',.', array_column(IMAGEEXTENSION, 'key'))); ?>,"
            $('#identity_images_picker input[type=file]').each(function () {
                $(this).attr('accept', allowedExtensions);
            });
        }

        function servicemanToggleIdentityImageWarning() {
            const existingImages = $('#identity_images_wrapper [data-existing-image-item]').length;
            const selectedImages = $('#identity_images_picker .spartan_image_input').filter(function () {
                return this.files && this.files.length > 0;
            }).length;
            const hasImages = (existingImages + selectedImages) > 0;
            const $proxy = $('#identity_images_wrapper .multi-image-validation-proxy');
            $proxy.val(hasImages ? '1' : '');
            $proxy.valid();
            return hasImages;
        }

        var servicemanIdentityImagesWrapperOriginalHtml = $('#identity_images_wrapper').prop('outerHTML');

        function servicemanInitIdentityImagesPicker() {
            $("#identity_images_picker").spartanMultiImagePicker({
                fieldName: 'identity_images[]',
                maxCount: servicemanMaxCount,
                allowedExt: 'png|jpg|jpeg|webp|gif',
                rowHeight: '120px',
                groupClassName: 'item',
                maxFileSize: servicemanMaxFileSize,
                dropFileLabel: "<?php echo e(translate('Drop here')); ?>",
                placeholderImage: {
                    image: '<?php echo e(asset('public/assets/admin-module/img/media/upload-placeholder2.png')); ?>',
                    width: '100%',
                },
                onAddRow() {
                    servicemanSetAcceptForAllInputs();
                },
                onRenderedPreview: function (index) {
                    servicemanToggleIdentityImageWarning();
                    toastr.success('<?php echo e(translate('Image added')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onRemoveRow: function (index) {
                    servicemanToggleIdentityImageWarning();
                },
                onExtensionErr: function (index, file) {
                    toastr.error('<?php echo e(translate("Please only input png|jpg|jpeg|gif|webp type file")); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error('File size must be less than ' + servicemanMaxSizeReadable);
                }
            });
            servicemanSetAcceptForAllInputs();
        }

        function servicemanResetIdentityImagesPicker() {
            var $existing = $('#identity_images_wrapper');
            if (!$existing.length || !servicemanIdentityImagesWrapperOriginalHtml) return;
            $existing.replaceWith(servicemanIdentityImagesWrapperOriginalHtml);
            servicemanInitIdentityImagesPicker();
            servicemanToggleIdentityImageWarning();
        }

        var servicemanOriginalPhone = <?php echo json_encode($serviceman->user->phone ?? '', 15, 512) ?>;
        var servicemanOriginalProfileImage = <?php echo json_encode($currentProfileImage ?? '', 15, 512) ?>;

        $(document).ready(function () {
            $('#identity_type').select2({
                placeholder: "<?php echo e(translate('Select Identity Type')); ?>",
                width: '100%'
            });

            servicemanInitIdentityImagesPicker();

            $(document).on('change', '#identity_images_picker .spartan_image_input', function () {
                servicemanToggleIdentityImageWarning();
            });

            if (window.FormValidator) {
                window.FormValidator.register('#serviceman-edit-form', {
                    rules: {
                        identity_images_validation: { required: true }
                    }
                });
            } else if ($.fn.rules) {
                $('input[name="identity_images_validation"]').rules('add', { required: true });
            }

            var $form = $('#serviceman-edit-form');
            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('select').each(function () {
                        var $s = $(this);
                        $s.find('option').each(function () { this.selected = this.defaultSelected; });
                        $s.trigger('change.select2');
                    });
                    $form.find('input[type="tel"]').each(function () {
                        if (window.intlTelInputGlobals) {
                            var iti = window.intlTelInputGlobals.getInstance(this);
                            if (iti) { iti.setNumber(servicemanOriginalPhone || ''); }
                        }
                    });
                    var $wrapper = $form.find('.global-image-upload').first();
                    if ($wrapper.length) {
                        if (servicemanOriginalProfileImage) {
                            $wrapper.addClass('has-image');
                            $wrapper.find('.global-image-preview').attr('src', servicemanOriginalProfileImage).removeClass('d-none');
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
                    if ($form.find('#identity_images_wrapper').length) {
                        servicemanResetIdentityImagesPicker();
                    }
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });
        });

        $(document).on('click', '.remove-existing-image', function () {
            let imageName = $(this).data('image');
            let elementId = $(this).data('id');
            let providerId = '<?php echo e($serviceman->id); ?>';

            Swal.fire({
                title: "<?php echo e(translate('Are you sure')); ?>?",
                text: "<?php echo e(translate('Want to remove this image')); ?>",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: '<?php echo e(translate('Cancel')); ?>',
                confirmButtonText: '<?php echo e(translate('Yes')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "<?php echo e(route('admin.serviceman.remove-image')); ?>",
                        method: 'DELETE',
                        data: {
                            provider_id: providerId,
                            image_name: imageName,
                            image_type: 'identity_image'
                        },
                        success: function (response) {
                            $(`#${elementId}`).remove();
                            servicemanToggleIdentityImageWarning();
                            toastr.success("<?php echo e(translate('Image removed successfully')); ?>");
                            location.reload();
                        },
                        error: function (xhr) {
                            toastr.error("<?php echo e(translate('Failed to remove image')); ?>");
                        }
                    });
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ServicemanModule/Resources/views/Admin/Serviceman/edit.blade.php ENDPATH**/ ?>