<?php $__env->startSection('title', translate('Update Provider')); ?>

<?php $__env->startPush('css_or_js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php ($currentLogo = $provider->logo_full_path ?? null); ?>
<div class="main-content">
    <div class="container-fluid">

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_update')): ?>
            <form id="create-provider-form" action="<?php echo e(route('admin.provider.update', [$provider->id])); ?>" method="POST"
                enctype="multipart/form-data" data-ff-validate novalidate>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title"><?php echo e(translate('Update Provider')); ?></h2>
                </div>
                <div class="card">
                    <div class="card-body p-30">
                        <div class="row g-4">
                            <div class="col-lg-8" id="register-form-p-0">
                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                    <h4 class="c1 mb-20"><?php echo e(translate('General Information')); ?></h4>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'text',
                                                'name' => 'company_name',
                                                'label' => translate('Company or Individual Name'),
                                                'placeholder' => translate('Enter Company or Individual Name'),
                                                'icon' => 'store',
                                                'required' => true,
                                                'maxlength' => 191,
                                                'charCount' => true,
                                                'value' => old('company_name', $provider->company_name),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'tel',
                                                'name' => 'company_phone',
                                                'id' => 'company_phone',
                                                'label' => translate('Phone'),
                                                'placeholder' => translate('Enter Phone'),
                                                'required' => true,
                                                'autocomplete' => 'tel',
                                                'value' => old('company_phone', $provider->company_phone),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'email',
                                                'name' => 'company_email',
                                                'id' => 'company_email',
                                                'label' => translate('Email'),
                                                'placeholder' => translate('ex: abc@email.com'),
                                                'icon' => 'mail',
                                                'required' => true,
                                                'autocomplete' => 'email',
                                                'value' => old('company_email', $provider->company_email),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-12">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'select',
                                                'name' => 'zone_id',
                                                'id' => 'zone_id',
                                                'label' => translate('Zone'),
                                                'required' => true,
                                                'selectClass' => 'select-identity theme-input-style',
                                                'optionNull' => translate('Select Zone'),
                                                'options' => $zones->pluck('name', 'id')->toArray(),
                                                'value' => old('zone_id', $provider->zone_id),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-12">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'textarea',
                                                'name' => 'company_address',
                                                'id' => 'address',
                                                'label' => translate('Address'),
                                                'placeholder' => translate('Enter Company Address'),
                                                'required' => true,
                                                'rows' => 3,
                                                'maxlength' => 500,
                                                'charCount' => true,
                                                'value' => old('company_address', $provider->company_address),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div
                                    class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                    <div class="w-100" style="max-width: 280px;">
                                        <?php echo $__env->make('adminmodule::admin.partials._single-image-upload', [
                                            'name' => 'logo',
                                            'id' => 'logo_upload',
                                            'title' => translate('صورة المشرف'),
                                            'subtitle' => null,
                                            'required' => false,
                                            'image' => $currentLogo,
                                            'instructionRatio' => '1:1',
                                            'height' => null,
                                            'showView' => true,
                                            'showEdit' => true,
                                            'showDelete' => false
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <div class="file_error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mt-0">
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-body p-30">
                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                    <h4 class="c1 mb-20"><?php echo e(translate('Business Information')); ?></h4>
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'select',
                                                'name' => 'identity_type',
                                                'id' => 'identity_type',
                                                'label' => translate('Identity Type'),
                                                'required' => true,
                                                'selectClass' => 'select-identity theme-input-style',
                                                'optionNull' => translate('Select Identity Type'),
                                                'options' => [
                                                    'passport' => translate('Passport'),
                                                    'driving_license' => translate('Driving License'),
                                                    'nid' => translate('NID'),
                                                    'trade_license' => translate('Trade License'),
                                                    'company_id' => translate('Company ID')
                                                ],
                                                'value' => old('identity_type', $provider->owner->identification_type),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php echo $__env->make('partials._form-field', [
                                                'type' => 'text',
                                                'name' => 'identity_number',
                                                'label' => translate('Identity Number'),
                                                'placeholder' => translate('Enter Identity Number'),
                                                'icon' => 'badge',
                                                'required' => true,
                                                'maxlength' => 120,
                                                'charCount' => true,
                                                'value' => old('identity_number', $provider->owner->identification_number),
                                                'wrapClass' => 'mb-0'
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                        <div class="col-12">
                                            <div class="upload-file w-100">
                                                <?php echo $__env->make('adminmodule::admin.partials._multiple-image-upload', [
                                                    'name' => 'identity_images[]',
                                                    'id' => 'identity_images',
                                                    'title' => translate('Identification Image'),
                                                    'subtitle' => null,
                                                    'instructionRatio' => '2:1',
                                                    'required' => true,
                                                    'images' => $provider->owner->identification_image_full_path,
                                                    'imageNames' => $provider->owner->identification_image ?? [],
                                                    'maxCount' => 2,
                                                    'imagePath' => 'provider/identity/',
                                                    'ratio' => 'ratio-2-1',
                                                    'defaultImagePath' => asset('public/assets/admin-module/img/media/provider-id.png')
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="<?php echo e(route('admin.provider.list')); ?>"
                                class="btn btn--secondary"><?php echo e(translate('Cancel')); ?></a>
                            <button type="submit" class="btn btn--primary"><?php echo e(translate('Update Supervisor')); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

    <script src="<?php echo e(asset('public/assets/provider-module')); ?>/js/spartan-multi-image-picker.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(business_config('google_map', 'third_party')?->live_values['map_api_key_client']); ?>&libraries=places,geometry&v=3.45.8"></script>
    <script src="<?php echo e(asset('public/assets/admin-module/js/map/map-picker.js')); ?>"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            if (window.FormValidator) {
                window.FormValidator.register('#create-provider-form', {
                    rules: {
                        identity_images_validation: { required: true }
                    }
                });
            } else if ($.fn.rules) {
                $('input[name="identity_images_validation"]').rules('add', { required: true });
            }

            document.querySelectorAll('input[type="tel"]').forEach(function (input) {
                const itiInstance = window.intlTelInputGlobals.getInstance(input);
                const nextInput = input.nextElementSibling;
                if (nextInput && nextInput.tagName.toLowerCase() === 'input') {
                    const nameAttr = nextInput.getAttribute('name');
                    input.setAttribute('name', nameAttr);
                }
                if (itiInstance) itiInstance.destroy();
                input.removeAttribute('data-intl-initialized');
            });
        });

        $(document).ready(function () {
            $('#zone_id').select2({
                placeholder: "<?php echo e(translate('Select Zone')); ?>",
                width: '100%'
            });
            $('#identity_type').select2({
                placeholder: "<?php echo e(translate('Select Identity Type')); ?>",
                width: '100%'
            });

            $('.__right-eye').on('click', function () {
                const input = $(this).siblings('input');
                const isVisible = input.attr('type') === 'text';

                if (isVisible) {
                    input.attr('type', 'password');
                    $(this).text('visibility_off');
                } else {
                    input.attr('type', 'text');
                    $(this).text('visibility');
                }
            });
        });

        var providerIdentificationImageCount = <?php echo e(count($provider->owner->identification_image ?? [])); ?>;
        var providerMaxSizeReadable = "<?php echo e(readableUploadMaxFileSize('image')); ?>";
        var providerMaxFileSize = 2 * 1024 * 1024;

        if (providerMaxSizeReadable.toLowerCase().includes('mb')) {
            providerMaxFileSize = parseFloat(providerMaxSizeReadable) * 1024 * 1024;
        } else if (providerMaxSizeReadable.toLowerCase().includes('kb')) {
            providerMaxFileSize = parseFloat(providerMaxSizeReadable) * 1024;
        }

        var providerMaxCount;
        if (providerIdentificationImageCount === 0) {
            providerMaxCount = 2;
        } else if (providerIdentificationImageCount === 1) {
            providerMaxCount = 1;
        } else {
            providerMaxCount = 0;
        }

        function providerSetAcceptForAllInputs() {
            const allowedExtensions = ".<?php echo e(implode(',.', array_column(IMAGEEXTENSION, 'key'))); ?>,"
            $('#identity_images_picker input[type=file]').each(function () {
                $(this).attr('accept', allowedExtensions);
            });
        }

        function providerToggleIdentityImageWarning() {
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

        var providerIdentityImagesWrapperOriginalHtml = $('#identity_images_wrapper').prop('outerHTML');

        function providerInitIdentityImagesPicker() {
            $("#identity_images_picker").spartanMultiImagePicker({
                fieldName: 'identity_images[]',
                maxCount: providerMaxCount,
                allowedExt: 'png|jpg|jpeg|webp|gif',
                rowHeight: '120px',
                groupClassName: 'item',
                maxFileSize: providerMaxFileSize,
                dropFileLabel: "<?php echo e(translate('Drop_here')); ?>",
                placeholderImage: {
                    image: '<?php echo e(asset('public/assets/admin-module/img/media/upload-placeholder2.png')); ?>',
                    width: '100%',
                },
                onAddRow() {
                    providerSetAcceptForAllInputs();
                },
                onRenderedPreview: function (index) {
                    providerToggleIdentityImageWarning();
                    toastr.success('<?php echo e(translate('Image_added')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onRemoveRow: function (index) {
                    providerToggleIdentityImageWarning();
                },
                onExtensionErr: function (index, file) {
                    toastr.error('<?php echo e(translate("Please only input png|jpg|jpeg|gif|webp type file")); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error('File size must be less than ' + providerMaxSizeReadable);
                }
            });
            providerSetAcceptForAllInputs();
        }

        function providerResetIdentityImagesPicker() {
            var $existing = $('#identity_images_wrapper');
            if (!$existing.length || !providerIdentityImagesWrapperOriginalHtml) return;
            $existing.replaceWith(providerIdentityImagesWrapperOriginalHtml);
            providerInitIdentityImagesPicker();
            providerToggleIdentityImageWarning();
        }

        var providerOriginalLogo = <?php echo json_encode($currentLogo ?? '', 15, 512) ?>;
        var providerOriginalCompanyPhone = <?php echo json_encode($provider->company_phone ?? '', 15, 512) ?>;
        var providerOriginalContactPhone = <?php echo json_encode($provider->contact_person_phone ?? '', 15, 512) ?>;

        function providerSetLogoPreview(src) {
            var $wrapper = $('#create-provider-form').find('.global-image-upload').first();
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

        window.providerWizardReset = function ($scope) {
            setTimeout(function () {
                $scope.find('input, textarea').each(function () {
                    var $i = $(this);
                    var type = ($i.attr('type') || '').toLowerCase();
                    if (type === 'checkbox' || type === 'radio') {
                        $i.prop('checked', $i.prop('defaultChecked'));
                    } else if (type === 'file') {
                        $i.val('');
                    } else {
                        $i.val($i.prop('defaultValue') || '');
                    }
                });
                $scope.find('select').each(function () {
                    var $s = $(this);
                    $s.find('option').each(function () {
                        this.selected = this.defaultSelected;
                    });
                    $s.trigger('change.select2');
                });

                var companyPhoneEl = $scope.find('#company_phone').get(0);
                if (companyPhoneEl && window.intlTelInputGlobals) {
                    var companyIti = window.intlTelInputGlobals.getInstance(companyPhoneEl);
                    if (companyIti) { companyIti.setNumber(providerOriginalCompanyPhone || ''); }
                }
                var contactPhoneEl = $scope.find('#contact_person_phone').get(0);
                if (contactPhoneEl && window.intlTelInputGlobals) {
                    var contactIti = window.intlTelInputGlobals.getInstance(contactPhoneEl);
                    if (contactIti) { contactIti.setNumber(providerOriginalContactPhone || ''); }
                }

                if ($scope.find('.global-image-upload').length) {
                    providerSetLogoPreview(providerOriginalLogo);
                }

                if ($scope.find('#identity_images_wrapper').length) {
                    providerResetIdentityImagesPicker();
                }

                if (window.FormCharCount) {
                    $scope.find('[data-char-count]').each(function () {
                        window.FormCharCount.update(this);
                    });
                }

                $scope.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                var $form = $('#create-provider-form');
                if ($form.data('validator')) {
                    $form.validate().resetForm();
                }
            }, 0);
        };

        $(document).ready(function () {
            providerInitIdentityImagesPicker();

            $(document).on('change', '#identity_images_picker .spartan_image_input', function () {
                providerToggleIdentityImageWarning();
            });
        });

        $(document).on('click', '.remove-existing-image', function () {
            let imageName = $(this).data('image');
            let elementId = $(this).data('id');
            let providerId = '<?php echo e($provider->id); ?>';

            Swal.fire({
                title: "<?php echo e(translate('are_you_sure')); ?>?",
                text: "<?php echo e(translate('want_to_remove_this_image')); ?>",
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
                        url: "<?php echo e(route('admin.provider.remove-image')); ?>",
                        method: 'DELETE',
                        data: {
                            provider_id: providerId,
                            image_name: imageName,
                            image_type: 'identity_image'
                        },
                        success: function (response) {
                            $(`#${elementId}`).remove();
                            toastr.success("<?php echo e(translate('Image removed successfully')); ?>");
                            location.reload();
                        },
                        error: function (xhr) {
                            toastr.error("<?php echo e(translate('Failed to remove image')); ?>");
                        }
                    });
                }
            })
        });
    </script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ProviderManagement/Resources/views/admin/provider/edit.blade.php ENDPATH**/ ?>