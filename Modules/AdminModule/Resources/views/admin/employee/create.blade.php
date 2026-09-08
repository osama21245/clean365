@extends('adminmodule::layouts.master')

@section('title', translate('employee add'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/select2/select2.min.css') }}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Add New Employee')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body py-4">
                            @can('employee_add')
                            <form id="add-new-employee-form" action="{{route('admin.employee.store')}}" method="post" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div>
                                    <div>
                                        <div class="d-flex gap-1 flex-column">
                                            <h4>{{translate('Employee Info')}}</h4>
                                            <p class="fs-12">{{translate('Give employee’s basic and account info')}}</p>
                                        </div>
                                    </div>

                                    <section>
                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h3>{{translate('General Information')}}</h3>
                                            <p class="fs-12">{{translate('Fill an employee’s general info such as name, address number and set role')}}</p>
                                        </div>

                                        <div class="row g-4 mb-30">
                                            <div class="col-lg-8">
                                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'first_name',
                                                            'label'       => translate('First Name'),
                                                            'placeholder' => translate('Enter First Name'),
                                                            'icon'        => 'account_circle',
                                                            'required'    => true,
                                                            'maxlength'   => 60,
                                                            'charCount'   => true,
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-md-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'last_name',
                                                            'label'       => translate('Last Name'),
                                                            'placeholder' => translate('Enter Last Name'),
                                                            'icon'        => 'account_circle',
                                                            'required'    => true,
                                                            'maxlength'   => 60,
                                                            'charCount'   => true,
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-md-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'tel',
                                                            'name'        => 'phone',
                                                            'id'          => 'phone',
                                                            'label'       => translate('Phone Number'),
                                                            'placeholder' => translate('Enter Phone Number'),
                                                            'required'    => true,
                                                            'autocomplete'=> 'tel',
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-md-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'address',
                                                            'id'          => 'address',
                                                            'label'       => translate('Address'),
                                                            'placeholder' => translate('Enter Address'),
                                                            'icon'        => 'home',
                                                            'required'    => true,
                                                            'maxlength'   => 255,
                                                            'charCount'   => true,
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-md-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'role_id',
                                                            'label'       => translate('Role'),
                                                            'required'    => true,
                                                            'selectClass' => 'select-identity theme-input-style role-btn',
                                                            'optionNull'  => translate('Select Role'),
                                                            'options'     => $roles->pluck('role_name', 'id')->toArray(),
                                                            'value'       => old('role_id'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-md-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'zone_ids[]',
                                                            'id'          => 'zone_selector__select',
                                                            'label'       => translate('Zones'),
                                                            'required'    => true,
                                                            'multiple'    => true,
                                                            'selectClass' => 'zone-select theme-input-style',
                                                            'prependOptions' => [
                                                                ['value' => 'all', 'label' => translate('Select All')]],
                                                            'options'     => $zones->pluck('name', 'id')->toArray(),
                                                            'value'       => old('zone_ids', []),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                                    <div class="w-100">
                                                        @include('adminmodule::admin.partials._single-image-upload', [
                                                            'name'             => 'profile_image',
                                                            'id'               => 'uploadImage',
                                                            'title'            => translate('Image'),
                                                            'required'         => true,
                                                            'image'            => null,
                                                            'instructionRatio' => '1:1',
                                                            'height'           => null])
                                                        <div class="file_error"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h3>{{translate('Business Information')}}</h3>
                                            <p class="fs-12">{{translate('Give verified information to verify a employee')}}</p>
                                        </div>

                                        <div class="row g-4 mb-30">
                                            <div class="col-lg-6">
                                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        @include('partials._form-field', [
                                                            'type'         => 'select',
                                                            'name'         => 'identity_type',
                                                            'label'        => translate('Identity Type'),
                                                            'required'     => true,
                                                            'selectClass'  => 'select-identity theme-input-style',
                                                            'prependOptions' => [
                                                                ['value' => 0, 'label' => translate('Select Identity Type'), 'disabled' => true]],
                                                            'options'      => [
                                                                'passport'        => translate('Passport'),
                                                                'driving_license' => translate('Driving License'),
                                                                'nid'             => translate('NID'),
                                                                'trade_license'   => translate('Trade License')],
                                                            'value'        => old('identity_type'),
                                                            'wrapClass'    => 'mb-0'])
                                                    </div>
                                                    <div class="col-12">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'identity_number',
                                                            'label'       => translate('Identity Number'),
                                                            'placeholder' => translate('Enter Identity Number'),
                                                            'icon'        => 'badge',
                                                            'required'    => true,
                                                            'maxlength'   => 50,
                                                            'charCount'   => true,
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                    @include('adminmodule::admin.partials._multiple-image-upload', [
                                                        'name'             => 'identity_images[]',
                                                        'id'               => 'identity_images',
                                                        'title'            => translate('Identity Image'),
                                                        'subtitle'         => null,
                                                        'instructionRatio' => '2:1',
                                                        'required'         => true,
                                                        'images'           => [],
                                                        'maxCount'         => 2,
                                                        'ratio'            => 'ratio-2-1'])
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h3>{{translate('Account Information')}}</h3>
                                            <p class="fs-12">{{translate('This info will need for employee’s future login')}}</p>
                                        </div>

                                        <div class="bg-light rounded p-xxl-4 p-3">
                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                @include('partials._form-field', [
                                                    'type'        => 'email',
                                                    'name'        => 'email',
                                                    'label'       => translate('Email'),
                                                    'placeholder' => translate('ex: abc@email.com'),
                                                    'icon'        => 'mail',
                                                    'required'    => true,
                                                    'autocomplete'=> 'email',
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                            <div class="col-lg-4">
                                                @include('partials._form-field', [
                                                    'type'        => 'password',
                                                    'name'        => 'password',
                                                    'id'          => 'pass',
                                                    'label'       => translate('Password'),
                                                    'placeholder' => translate('Enter Password'),
                                                    'icon'        => 'lock',
                                                    'required'    => true,
                                                    'minlength'   => 8,
                                                    'hint'        => translate('Minimum 8 characters'),
                                                    'autocomplete'=> 'new-password',
                                                    'value'       => 'password',
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                            <div class="col-lg-4">
                                                @include('partials._form-field', [
                                                    'type'        => 'password',
                                                    'name'        => 'confirm_password',
                                                    'id'          => 'confirm_password',
                                                    'label'       => translate('Confirm Password'),
                                                    'placeholder' => translate('Re-enter Password'),
                                                    'icon'        => 'lock',
                                                    'required'    => true,
                                                    'minlength'   => 8,
                                                    'autocomplete'=> 'new-password',
                                                    'value'       => 'password',
                                                    'wrapClass'   => 'mb-0'])
                                            </div>
                                        </div>
                                        </div>
                                    </section>

                                    <div>
                                        <div class="d-flex gap-1 flex-column">
                                            <h4>{{translate('Set Permissions')}}</h4>
                                            <p class="fs-12">{{translate('Set what individuals on this role can do')}}</p>
                                        </div>
                                    </div>

                                    <section>
                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h4>{{translate('Set Permission')}}</h4>
                                            <p>{{translate('Modify what individuals on this role can do')}}</p>
                                        </div>
                                        <div class="role-access-permission"></div>
                                    </section>
                                </div>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script>
        "use strict";

        $(document).ready(function() {
            $('.role-btn').change(function() {
                var roleId = $(this).val();

                $.ajax({
                    url: '{{route('admin.employee.ajax.role.access')}}',
                    method: 'GET',
                    data: { role_id: roleId },
                    success: function(response) {
                        $('.role-access-permission').html(response.html);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });

        var form = $("#add-new-employee-form");
        form.validate({
            ignore: ":disabled,:hidden:not(.multi-image-validation-proxy)",
            errorPlacement: function errorPlacement(error, element) {
                if (element.hasClass('multi-image-validation-proxy')) {
                    const wrapperSelector = element.data('upload-wrapper');
                    const $warningBox = $(wrapperSelector).find('.global-multi-image-warning');
                    $warningBox.html('');
                    $warningBox.append(error);
                    return;
                }

                if (element.is('input[type="file"]')) {
                    const $gu = element.closest('.global-image-upload');
                    if ($gu.length) {
                        $gu.after(error);
                        return;
                    }
                }

                const $ffField = element.closest('[data-ff-field]');
                if ($ffField.length) {
                    let $messages = $ffField.find('.ff-field__messages').first();
                    if (!$messages.length) {
                        $messages = $('<div class="ff-field__messages"></div>');
                        $ffField.append($messages);
                    }
                    $messages.append(error);
                    return;
                }

                const $inputWrap = element.closest('.input-wrap');
                if ($inputWrap.length) {
                    $inputWrap.after(error);
                    return;
                }
                element.after(error);
            },
            rules: {
                identity_images_validation: {
                    required: true,
                },
                password: {
                    minlength: 8,
                },
                confirm_password: {
                    minlength: 8,
                    equalTo: "#pass"
                }
            }
        });
        form.children("div").steps({
            headerTag: "div",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            enableCancelButton: true,
            labels: {
                cancel: "{{ translate('reset') }}"
            },
            onStepChanging: function (event, currentIndex, newIndex) {
                if (newIndex < currentIndex) {
                    return true;
                }
                return validateEmployeeCreateStep();
            },
            onFinishing: function (event, currentIndex) {
                return validateEmployeeCreateStep();
            },
            onFinished: function (event, currentIndex) {
                form.submit();
            },
            onCanceled: function (event) {
                var $currentSection = form.find('section.body.current').first();
                if (!$currentSection.length) {
                    $currentSection = form.find('section.body:visible').first();
                }
                resetScope($currentSection.length ? $currentSection : form);
            }
        });

        form.find('.actions ul a[href="#cancel"]').parent('li').addClass('wizard-reset-item');
    </script>
    <script src="{{asset('public/assets/admin-module')}}/js/spartan-multi-image-picker.js"></script>
    <script>
        "use strict";

        $('#zone_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        let maxSizeReadable = "{{ readableUploadMaxFileSize('image') }}"; // "2MB"
        let maxFileSize = 2 * 1024 * 1024; // default 2MB

        if (maxSizeReadable.toLowerCase().includes('mb')) {
            maxFileSize = parseFloat(maxSizeReadable) * 1024 * 1024;
        } else if (maxSizeReadable.toLowerCase().includes('kb')) {
            maxFileSize = parseFloat(maxSizeReadable) * 1024;
        }

        function toggleIdentityImageWarning() {
            const hasImages = $('#identity_images_picker .spartan_image_input').filter(function () {
                return this.files && this.files.length > 0;
            }).length > 0;
            const $proxy = $('#identity_images_wrapper .multi-image-validation-proxy');

            $proxy.val(hasImages ? '1' : '');
            $proxy.valid();
            return hasImages;
        }

        function scrollToValidationTarget($target) {
            if (!$target || !$target.length) {
                return;
            }

            $('html, body').animate({
                scrollTop: $target.offset().top - 140
            }, 300);
        }

        function validateEmployeeCreateStep() {
            form.validate().settings.ignore = ":disabled,:hidden:not(.multi-image-validation-proxy)";

            toggleIdentityImageWarning();
            if (!form.valid()) {
                scrollToValidationTarget(form.find('label.error:visible').first());
                return false;
            }

            const fileInput = document.getElementById("uploadImage");
            const oFile = fileInput.files[0];

            if (oFile && oFile.size > 2097152) {
                return false;
            }

            return true;
        }

        function setAcceptForAllInputs() {
            const allowedExtensions = ".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }},"

            $('#identity_images_picker input[type=file]').each(function() {
                $(this).attr('accept', allowedExtensions);
            });
        }

        var identityImagesWrapperOriginalHtml = $('#identity_images_wrapper').prop('outerHTML');

        function initIdentityImagesPicker() {
            $("#identity_images_picker").spartanMultiImagePicker({
                fieldName: 'identity_images[]',
                maxCount: 2,
                rowHeight: '120px',
                groupClassName: 'item',
                maxFileSize: maxFileSize,
                dropFileLabel: "{{translate('Drop here')}}",
                placeholderImage: {
                    image: '{{asset('public/assets/admin-module')}}/img/media/upload-placeholder2.png',
                    width: '100%',
                },
                onAddRow() {
                    setAcceptForAllInputs();
                },
                onRenderedPreview: function (index) {
                    toggleIdentityImageWarning();
                    toastr.success('{{translate('Image added')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onRemoveRow: function (index) {
                    toggleIdentityImageWarning();
                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{ translate("Please only input png|jpg|jpeg|gif|webp type file") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error('File size must be less than ' + maxSizeReadable);
                }
            });
            setAcceptForAllInputs();
        }

        function resetIdentityImagesPicker() {
            var $existing = $('#identity_images_wrapper');
            if (!$existing.length || !identityImagesWrapperOriginalHtml) return;
            $existing.replaceWith(identityImagesWrapperOriginalHtml);
            initIdentityImagesPicker();
            toggleIdentityImageWarning();
        }

        initIdentityImagesPicker();

        $(document).on('change', '#identity_images_picker .spartan_image_input', function () {
            toggleIdentityImageWarning();
        });

        function resetScope($scope) {
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

                $scope.find('input[type="tel"]').each(function () {
                    if (window.intlTelInputGlobals) {
                        var iti = window.intlTelInputGlobals.getInstance(this);
                        if (iti) { iti.setNumber(''); }
                    }
                });

                $scope.find('.global-image-upload').each(function () {
                    var $c = $(this);
                    $c.removeClass('has-image');
                    $c.find('.global-image-preview').attr('src', '').addClass('d-none');
                    $c.find('.global-upload-box').removeClass('global-upload-box-hidden');
                    $c.find('.overlay-icons').addClass('d-none');
                    $c.find('input[type="file"]').val('');
                });

                if ($scope.find('#identity_images_wrapper').length) {
                    resetIdentityImagesPicker();
                }

                if (window.FormCharCount) {
                    $scope.find('[data-char-count]').each(function () {
                        window.FormCharCount.update(this);
                    });
                }

                $scope.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                form.validate().resetForm();
            }, 0);
        }

        form.on('reset', function () {
            resetScope(form);
        });

    </script>

    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/section/employee/custom.js"></script>

    <script>
        "use strict";
        $(document).ready(function () {
            $('#role_id').select2({
                placeholder: "{{ translate('Select Role') }}",
                width: '100%'
            });
            $('#identity_type').select2({
                placeholder: "{{ translate('Select Identity Type') }}",
                width: '100%'
            });
        });
    </script>

@endpush
