<?php $__env->startSection('title', translate('Add Supervisor')); ?>

<?php $__env->startPush('css_or_js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_add')): ?>
                <form id="create-supervisor-form" action="<?php echo e(route('admin.provider.store')); ?>" method="POST"
                    enctype="multipart/form-data" data-ff-validate novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('Add New Supervisor')); ?></h2>
                        <p class="page-title-text mb-0">
                            <?php echo e(translate('Create an internal supervisor account to manage servicemen and accept orders.')); ?></p>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card h-100">
                                <div class="card-body p-30">
                                    <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                        <h4 class="c1 mb-20"><?php echo e(translate('Personal Information')); ?></h4>
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <?php echo $__env->make('partials._form-field', [
                                                    'type' => 'text',
                                                    'name' => 'first_name',
                                                    'label' => translate('First Name'),
                                                    'placeholder' => translate('Enter First Name'),
                                                    'icon' => 'account_circle',
                                                    'required' => true,
                                                    'maxlength' => 191,
                                                    'charCount' => true,
                                                    'value' => old('first_name'),
                                                    'wrapClass' => 'mb-0'
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php echo $__env->make('partials._form-field', [
                                                    'type' => 'text',
                                                    'name' => 'last_name',
                                                    'label' => translate('Last Name'),
                                                    'placeholder' => translate('Enter Last Name'),
                                                    'icon' => 'account_circle',
                                                    'required' => true,
                                                    'maxlength' => 191,
                                                    'charCount' => true,
                                                    'value' => old('last_name'),
                                                    'wrapClass' => 'mb-0'
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php echo $__env->make('partials._form-field', [
                                                    'type' => 'tel',
                                                    'name' => 'phone',
                                                    'id' => 'phone',
                                                    'label' => translate('Phone'),
                                                    'placeholder' => translate('Enter Phone'),
                                                    'required' => true,
                                                    'autocomplete' => 'tel',
                                                    'value' => old('phone'),
                                                    'wrapClass' => 'mb-0'
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php echo $__env->make('partials._form-field', [
                                                    'type' => 'email',
                                                    'name' => 'email',
                                                    'id' => 'email',
                                                    'label' => translate('Email'),
                                                    'placeholder' => translate('ex: abc@email.com'),
                                                    'icon' => 'mail',
                                                    'required' => true,
                                                    'autocomplete' => 'email',
                                                    'value' => old('email'),
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
                                                    'value' => old('zone_id'),
                                                    'wrapClass' => 'mb-0'
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                            <div class="col-12">
                                                <?php echo $__env->make('partials._form-field', [
                                                    'type' => 'textarea',
                                                    'name' => 'address',
                                                    'id' => 'address',
                                                    'label' => translate('Address'),
                                                    'placeholder' => translate('Enter Address'),
                                                    'required' => true,
                                                    'rows' => 3,
                                                    'maxlength' => 500,
                                                    'charCount' => true,
                                                    'value' => old('address'),
                                                    'wrapClass' => 'mb-0'
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-body p-30 d-flex align-items-center justify-content-center">
                                    <div class="w-100" style="max-width: 280px;">
                                        <?php echo $__env->make('adminmodule::admin.partials._single-image-upload', [
                                            'name' => 'logo',
                                            'id' => 'logo_upload',
                                            'title' => translate('Profile Photo'),
                                            'subtitle' => null,
                                            'required' => true,
                                            'image' => null,
                                            'instructionRatio' => '1:1',
                                            'height' => null
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <div class="file_error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card h-100">
                                <div class="card-body p-30">
                                    <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                        <h4 class="c1 mb-20"><?php echo e(translate('Identity Information')); ?></h4>
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
                                                        'nid' => translate('NID')
                                                    ],
                                                    'value' => old('identity_type'),
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
                                                    'value' => old('identity_number'),
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
                                                        'images' => [],
                                                        'maxCount' => 2,
                                                        'ratio' => 'ratio-2-1'
                                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="<?php echo e(route('admin.provider.list')); ?>"
                                    class="btn btn--secondary"><?php echo e(translate('Cancel')); ?></a>
                                <button type="submit" class="btn btn--primary"><?php echo e(translate('Create Supervisor')); ?></button>
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

        var providerMaxSizeReadable = "<?php echo e(readableUploadMaxFileSize('image')); ?>";
        var providerMaxFileSize = 2 * 1024 * 1024;

        if (providerMaxSizeReadable.toLowerCase().includes('mb')) {
            providerMaxFileSize = parseFloat(providerMaxSizeReadable) * 1024 * 1024;
        } else if (providerMaxSizeReadable.toLowerCase().includes('kb')) {
            providerMaxFileSize = parseFloat(providerMaxSizeReadable) * 1024;
        }

        function providerSetAcceptForAllInputs() {
            const allowedExtensions = ".<?php echo e(implode(',.', array_column(IMAGEEXTENSION, 'key'))); ?>,"
            $('#identity_images_picker input[type=file]').each(function () {
                $(this).attr('accept', allowedExtensions);
            });
        }

        function providerToggleIdentityImageWarning() {
            const hasImages = $('#identity_images_picker .spartan_image_input').filter(function () {
                return this.files && this.files.length > 0;
            }).length > 0;
            const $proxy = $('#identity_images_wrapper .multi-image-validation-proxy');
            $proxy.val(hasImages ? '1' : '');
            $proxy.valid();
            return hasImages;
        }

        function providerInitIdentityImagesPicker() {
            $("#identity_images_picker").spartanMultiImagePicker({
                fieldName: 'identity_images[]',
                maxCount: 2,
                allowedExt: 'png|jpg|jpeg|webp|gif',
                rowHeight: '120px',
                groupClassName: 'item',
                maxFileSize: providerMaxFileSize,
                dropFileLabel: "<?php echo e(translate('Drop_here')); ?>",
                placeholderImage: {
                    image: '<?php echo e(asset('public/assets/admin-module')); ?>/img/media/upload-placeholder2.png',
                    width: '100%',
                },
                onAddRow: function () {
                    providerSetAcceptForAllInputs();
                },
                onRenderedPreview: function () {
                    providerToggleIdentityImageWarning();
                },
                onRemoveRow: function () {
                    providerToggleIdentityImageWarning();
                },
                onExtensionErr: function () {
                    toastr.error('<?php echo e(translate("Please only input png|jpg|jpeg|gif|webp type file")); ?>');
                },
                onSizeErr: function () {
                    toastr.error('File size must be less than ' + providerMaxSizeReadable);
                }
            });
            providerSetAcceptForAllInputs();
        }

        $(document).ready(function () {
            if (window.FormValidator) {
                window.FormValidator.register('#create-supervisor-form', {
                    rules: {
                        identity_images_validation: { required: true }
                    }
                });
            }

            $('#zone_id, #identity_type').select2({
                width: '100%'
            });

            providerInitIdentityImagesPicker();

            $(document).on('change', '#identity_images_picker .spartan_image_input', function () {
                providerToggleIdentityImageWarning();
            });

            $('.__right-eye').on('click', function () {
                const input = $(this).siblings('input');
                const isVisible = input.attr('type') === 'text';
                input.attr('type', isVisible ? 'password' : 'text');
                $(this).text(isVisible ? 'visibility_off' : 'visibility');
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ProviderManagement/Resources/views/admin/provider/create.blade.php ENDPATH**/ ?>