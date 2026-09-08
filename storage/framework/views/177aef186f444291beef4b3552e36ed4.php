<?php $__env->startSection('title',translate('3rd_party')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module')); ?>/plugins/swiper/swiper-bundle.min.css"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('firebase')); ?></h2>
                    </div>

                    <?php if($webPage == 'firebase-configuration' || $webPage == 'firebase-authentication'): ?>
                        <div class="main-content mb-5">
                            <div class="container-fluid">
                                <?php echo $__env->make('businesssettingsmodule::admin.configurations.third-party.partials.firebase-inline-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <div class="tab-content">
                                    <div class="tab-pane fade <?php echo e($webPage == 'firebase-configuration' ? 'active show' : ''); ?>"
                                         aria-labelledby="configuration-custom-tab1" tabindex="0">
                                        
                                        <div class="card mb-20">
                                            <div class="card-body p-20">
                                                <div class="row g-lg-4 g-4 align-items-center">
                                                    <div class="col-lg-3">
                                                        <h3 class="mb-2"><?php echo e(translate('Firebase Configuration')); ?></h3>
                                                        <p class="fz-12 mb-xl-3 mb-2"><?php echo e(translate('Here fillup the following data & setup the firebase to work properly the notifications of your system.')); ?></p>
                                                        <a href="#" class="fz-12 text-primary fw-semibold text-decoration-underline" data-bs-toggle="modal" data-bs-target="#firebase-where-to-get-this-information"><?php echo e(translate('Where to Get This Information')); ?></a>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <div class="bg-light rounded-2 p-20">
                                                            <h5 class="mb-10 fw-normal"><?php echo e(translate('Service Content')); ?></h5>
                                                            <div class="bg-white rounded-2 p-16">
                                                                <div class="row g-xl-4 g-3">
                                                                    <div class="col-md-6">
                                                                        <div class="custom-radio">
                                                                            <input type="radio" id="firebase-login-active1" name="status"
                                                                                   value="1" checked="">
                                                                            <label for="firebase-login-active1">
                                                                                <h5 class="mb-1"><?php echo e(translate('File Upload')); ?></h5>
                                                                                <p class="fz-12 max-w-250"><?php echo e(translate('upload the entire JSON file directly ')); ?></p>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="custom-radio">
                                                                            <input type="radio" id="firebase-login-active2" name="status"
                                                                                   value="1">
                                                                            <label for="firebase-login-active2">
                                                                                <h5 class="mb-1"><?php echo e(translate('File Content')); ?></h5>
                                                                                <p class="fz-12 max-w-250"><?php echo e(translate('manually paste or edit the JSON content as text in the input box.')); ?></p>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card mb-20">
                                            <div class="card-body p-20">
                                                <div class="row g-3 mb-20 file-upload-div">
                                                    <div class="col-lg-6">
                                                        <div
                                                            class="bg-primary d-flex align-items-center rounded-2 p-20 bg-opacity-10 h-100">
                                                            <div class="boxes">
                                                                <div class="d-flex align-items-center gap-1 text-primary mb-3">
                                                                    <img
                                                                        src="<?php echo e(asset('public/assets/admin-module/img/icons/focus_mode.svg')); ?>"
                                                                        class="svg" alt="focus mode icon">
                                                                    <h4 class="text-primary"><?php echo e(('Instructions')); ?></h4>
                                                                </div>
                                                                <ul class="d-flex flex-column gap-2 px-3 mb-0">
                                                                    <li class="fz-12"><?php echo e(translate('Upload file must be JSON file format in and click Update button.')); ?>

                                                                    </li>
                                                                    <li class="fz-12"><?php echo e(translate('Without update the service File content can’t update properly and you can’t see the updated content in the field.')); ?>

                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="bg-light rounded-2 p-20" id="upload_json_file">
                                                            <form action="<?php echo e(route('admin.configuration.store-third-party-data')); ?>" class="third-party-data-form" method="POST" enctype="multipart/form-data">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('PUT'); ?>
                                                                <input type="hidden" name="party_name" value="push_notification">
                                                                <input type="hidden" name="server_key"
                                                                       value="<?php echo e($data['server_key'] ?? ''); ?>">
                                                                <div class="mx-auto">
                                                                    <div
                                                                        class="process-json-file position-relative overflow-hidden bg-white border-dashed rounded-2 mx-auto d-center p-30">
                                                                        <input type="file" name="service_file" accept=".json" required
                                                                               data-maxFileSize="<?php echo e(readableUploadMaxFileSize('file')); ?>"
                                                                               style="position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                                                                        <div class="global-upload-box">
                                                                            <div class="upload-content text-center">
                                                                                <img class="mb-20"
                                                                                     src="<?php echo e(asset('public/assets/admin-module/img/drop-upload-cloud.png')); ?>"
                                                                                     alt="">
                                                                                <h5 class="mb-1 fw-normal"><strong class="text-primary"><?php echo e(translate('Click to upload')); ?></strong> <?php echo e(translate('or')); ?> <strong><?php echo e(translate('Drag & Drop')); ?></strong> <?php echo e(translate('here')); ?>

                                                                                </h5>
                                                                                <span class="fz-12 d-block mb-15"><?php echo e(translate('JSON file size no more than 10MB')); ?></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="inside-upload-zipBox uploaded-zip-box-white mt-20">
                                                                    </div>
                                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configuration_update')): ?>
                                                                        <div class="d-flex justify-content-end gap-3">
                                                                            <button type="reset"
                                                                                    class="btn btn--secondary rounded reset-uploaded-file"><?php echo e(translate('Reset')); ?></button>
                                                                            <button type="submit"
                                                                                    class="btn btn--primary demo_check rounded"><?php echo e(translate('Update')); ?></button>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-light rounded-2 p-20">
                                                    <form action="<?php echo e(route('admin.configuration.store-third-party-data')); ?>" class="third-party-data-form" method="POST">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PUT'); ?>
                                                        <div class="discount-type">
                                                            <div class="row g-lg-4 g-4">
                                                                <div class="col-md-12 col-12" id="service-file-content-box">
                                                                    <div class="mb-4">
                                                                        <div class="">
                                                                            <div
                                                                                class="mb-2 text-dark"><?php echo e(translate('Service File content')); ?>

                                                                                <i class="material-icons fz-14 text-light-gray"
                                                                                   data-bs-toggle="tooltip"
                                                                                   data-bs-placement="top"
                                                                                   title="<?php echo e(translate('paste or edit the JSON content as text in the input box')); ?>"
                                                                                >info</i>
                                                                            </div>
                                                                            <input type="hidden" name="party_name" value="firebase">
                                                                            <textarea class="form-control"
                                                                                      name="service_file_content"
                                                                                      placeholder="<?php echo e(translate('Service File Content')); ?> *"
                                                                                      required
                                                                                      rows="8"
                                                                                      style="height: auto;"><?php echo e($data['service_file_content'] ?? ''); ?></textarea>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-12">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('Api Key')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter your Api key')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" placeholder="<?php echo e(translate('Ex: Smtp.amailtrap.io')); ?>"
                                                                               class="form-control" name="apiKey" value="<?php echo e($data['apiKey'] ?? ''); ?>"
                                                                               autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-6">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('Auth Domain')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter auth domain')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" class="form-control" name="authDomain" value="<?php echo e($data['authDomain'] ?? ''); ?>"
                                                                               autocomplete="off" placeholder="<?php echo e(translate('Ex: Smtp')); ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-6">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('Project ID')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter your project id')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" class="form-control" name="projectId" value="<?php echo e($data['projectId'] ?? ''); ?>"
                                                                               autocomplete="off" placeholder="<?php echo e(translate('Ex: 587')); ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-6">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('Storage Bucket')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter your storage bucket')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" class="form-control" name="storageBucket"
                                                                               value="<?php echo e($data['storageBucket'] ?? ''); ?>" autocomplete="off" placeholder="<?php echo e(translate('Ex: yahoo')); ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-6">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('Messaging Sender ID')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter your messaging sender ID')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" placeholder="<?php echo e(translate('Ex: example@demo.com')); ?>"
                                                                               class="form-control" name="messagingSenderId" value="<?php echo e($data['messagingSenderId'] ?? ''); ?>"
                                                                               autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-6">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('App ID')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter your app ID')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" placeholder="<?php echo e(translate('Ex: Tis')); ?>" class="form-control"
                                                                               name="appId" value="<?php echo e($data['appId'] ?? ''); ?>" autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-6">
                                                                    <div class="">
                                                                        <div class="mb-2 text-dark"><?php echo e(translate('Measurement ID')); ?>

                                                                            <i class="material-icons fz-14 text-light-gray"
                                                                               data-bs-toggle="tooltip"
                                                                               data-bs-placement="top"
                                                                               title="<?php echo e(translate('Enter your measurement ID')); ?>"
                                                                            >info</i>
                                                                        </div>
                                                                        <input type="text" placeholder="<?php echo e(translate('Ex: 123456789')); ?>" class="form-control"
                                                                               name="measurementId" value="<?php echo e($data['measurementId'] ?? ''); ?>" autocomplete="off">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('firebase_update')): ?>
                                                            <div class="d-flex justify-content-end trans3 mt-4">
                                                                <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                                                    <div class="d-flex justify-content-end gap-xl-3 gap-2">
                                                                        <button type="reset" class="btn btn--secondary rounded">
                                                                            <?php echo e(translate('reset')); ?>

                                                                        </button>
                                                                        <button type="submit" class="btn btn--primary rounded demo_check d-flex align-items-center gap-2">
                                                                            <img src="<?php echo e(asset('public/assets/admin-module/img/icons/save-icon.svg')); ?>" alt="save icon">
                                                                            <?php echo e(translate('Save Information')); ?>

                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade <?php echo e($webPage == 'firebase-authentication' ? 'active show' : ''); ?>">
                                        <div
                                            class="pick-map mb-20 p-12 rounded d-flex flex-md-nowrap flex-wrap align-items-center gap-1 bg-primary bg-opacity-10">
                                            <img src="<?php echo e(asset('public/assets/admin-module/img/icons/focus_mode.svg')); ?>"
                                                 alt="focus mode icon">
                                            <p class="fz-12">
                                                <?php echo e(translate('Please ensure that your firebase configuration is set up before using these features.')); ?> <?php echo translate('Check :firebaseConfiguration.', ['firebaseConfiguration' => '<a href=\"' . route('admin.configuration.third-party', 'firebase-configuration') . '\" class=\"text-primary fw-semibold text-decoration-underline\">' . translate('Firebase Configuration') . '</a>']); ?>

                                            </p>
                                        </div>
                                        <div class="card mb-3 view-details-container">
                                            <form action="<?php echo e(route('admin.configuration.store-third-party-data')); ?>" class="third-party-data-form" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <input type="hidden" name="party_name" value="firebase_otp_verification">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-xxl-8 col-md-6 mb-md-0 mb-2">
                                                            <h3 class="black-color mb-1 d-block"><?php echo e(translate('Firebase Authentication')); ?></h3>
                                                            <p class="fz-12 text-c mb-0"><?php echo e(translate('If this feature is active customers get the otp through firebase.')); ?></p>
                                                        </div>
                                                        <div class="col-xxl-4 col-md-6">
                                                            <div
                                                                class="d-flex flex-sm-nowrap flex-wrap justify-content-end justify-content-end align-items-center gap-sm-3 gap-2">
                                                                <div class="mb-0">
                                                                    <label class="switcher">
                                                                        <input type="checkbox" name="status" class="switcher_input" <?php if($data['status'] ?? false): echo 'checked'; endif; ?>>
                                                                        <span class="switcher_control"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 view-details d-block">
                                                        <div class="body-bg rounded p-20 mb-20">
                                                            <div class="">
                                                                <div class="mb-2 text-dark"><?php echo e(translate('Web Api Key')); ?>

                                                                    <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                                       data-bs-placement="top"
                                                                       title="<?php echo e(translate('Enter web api key.')); ?>"
                                                                    >info</i>
                                                                </div>
                                                                <input type="text" placeholder="<?php echo e(translate('Ex: Smtp.amailtrap.io')); ?>" class="form-control"
                                                                       name="web_api_key" value="<?php echo e($data['web_api_key'] ?? ''); ?>" >
                                                            </div>
                                                        </div>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('firebase_update')): ?>
                                                            <div class="d-flex justify-content-end trans3 mt-4">
                                                                <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                                                    <div class="d-flex justify-content-end gap-3">
                                                                        <button type="reset" class="btn btn--secondary rounded"><?php echo e(translate('Reset')); ?></button>
                                                                        <button type="submit" class="btn btn--primary demo_check rounded"><?php echo e(translate('Save Information')); ?></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="firebase-where-to-get-this-information" tabindex="-1"
                             aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                                        <button type="button" class="btn-close border-0 shadow-none" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body px-4 px-sm-5 pt-0">
                                        <div class="d-flex flex-column align-items-center gap-2">
                                            <h3 class="modal-title my-3 text-center" id="instructionsModalLabel">
                                                <?php echo e(translate('Instructions')); ?></h3>
                                            <p><?php echo e(translate('To configure OTP in Firebase, you must create a Firebase project first.')); ?> <?php echo e(translate('If you have not created any project for your application yet, please create one first.')); ?>

                                            </p>
                                            <p><?php echo translate('Now go to the :firebaseConsoleLink and follow the instructions below.', ['firebaseConsoleLink' => '<a href="https://console.firebase.google.com/" target="_blank" class="text-primary fw-semibold">' . translate('Firebase console') . '</a>']); ?></p>
                                            <ol class="d-flex flex-column __gap-1 __instructions">
                                                <li><?php echo e(translate('Go to your firebase project.')); ?></li>
                                                <li><?php echo e(translate('Navigate to the build menu from the left sidebar and select authentication.')); ?>

                                                </li>
                                                <li><?php echo e(translate('Get started the project and go to the sign-in method tab.')); ?></li>
                                                <li><?php echo e(translate('From the sign in providers section select the phone option.')); ?></li>
                                                <li><?php echo e(translate('Ensure to enable the method phone and press save.')); ?></li>
                                            </ol>
                                            <div class="d-flex justify-content-center mt-4">
                                                <button type="button" class="btn btn--primary px-5" data-bs-dismiss="modal"><?php echo e(translate('Got it')); ?>

                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    <span id="payment-gateway-published-status" data-status="<?php echo e($data['payment_gateway_publish_status'] ?? 0 == 1 ? 'true' : 'false'); ?>"></span>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script>
        $('.third-party-data-form').on('submit', function (event) {
            event.preventDefault();
            let formElement = this;
            let formData = new FormData(formElement);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: $(formElement).attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                success: function (response) {
                    console.log(response)
                    if (response?.errors.length > 0)
                    {
                        response.errors.forEach((error, index) => {
                            toastr.error(error.message);
                        })
                    } else {
                        toastr.success('<?php echo e(translate('successfully_updated')); ?>');
                        if (!$(formElement).hasClass('no-reload')) {
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        }
                    }
                },
                error: function (error) {
                    toastr.error(JSON.parse(error.responseText).message)
                }
            });
        });

        $(document).ready(function () {
            function toggleFileContentBox() {
                if ($('#firebase-login-active2').is(':checked')) {
                    $('.file-upload-div').hide();
                    $("textarea[name='service_file_content']").attr('readonly', false);
                } else {
                    $('.file-upload-div').show();
                    $("textarea[name='service_file_content']").attr('readonly', true);
                }
            }

            toggleFileContentBox();

            $('input[name="status"]').on('change', toggleFileContentBox);

            $('.process-json-file input[type="file"]').on('change', function (event) {
                const files = event.target.files;
                if (!files.length) return;
                const uploadBox = $('.inside-upload-zipBox');
                uploadBox.empty();

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileName = file.name;
                    const fileSizeMB = (file.size / 1024 / 1024).toFixed(1);
                    if (!fileName.toLowerCase().endsWith('.json')) {
                        alert('Only .json files are allowed.');
                        continue;
                    }

                    const uploadedContent = `
                    <div class="uploaded-zip-box position-relative bg-light rounded p-3 mb-md-3 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="zip-icon mt-1">
                                <span class="material-symbols-outlined text-primary">insert_drive_file</span>
                            </div>
                            <div class="d-flex align-items-center w-100 justify-content-between gap-1 flex-wrap">
                                <div class="fz-12 text-break">${fileName}</div>
                                <div class="text-muted text-color fz-12">${fileSizeMB}MB</div>
                            </div>
                        </div>
                        <button class="btn btn-danger p-1 position-absolute top-0 end-cus-0 w-20 h-20 rounded-full d-center remove-uploaded-file">✕</button>
                    </div>
                `;

                    uploadBox.append(uploadedContent);
                }
            });

            $(document).on('click', '.remove-uploaded-file, .reset-uploaded-file', function () {
                $('.uploaded-zip-box').remove();
                $("input[name='service_file']").val('');
            });

            let paymentGatewayPublishedStatus = $('#payment-gateway-published-status').data('status');
            if (paymentGatewayPublishedStatus?.toString() === 'true') {
                'use strict';
                let gatewayCards = $('#gateway-cards');
                gatewayCards.find('input').each(function () {
                    $(this).attr('disabled', true);
                });
                gatewayCards.find('select').each(function () {
                    $(this).attr('disabled', true);
                });
                gatewayCards.find('.switcher_input').each(function () {
                    $(this).removeAttr('checked', true);
                });
                gatewayCards.find('button').each(function () {
                    $(this).attr('disabled', true);
                });
            }

            var visibleCount = 3;

            $('.data-group').each(function () {
                var $group = $(this);
                var $items = $group.find('.items');
                var $button = $group.find('.toggle-btn');
                // Only show the button if more than 3 items
                if ($items.length > visibleCount) {
                    $button.show();

                    // Hide items beyond the first 3
                    $items.each(function (index) {
                        if (index >= visibleCount) {
                            $(this).hide();
                        }
                    });

                    // Button click toggle
                    $button.on('click', function () {
                        var hidden = $items.filter(':hidden').length > 0;

                        if (hidden) {
                            $items.slideDown();
                            $(this).text('See Less');
                        } else {
                            $items.each(function (index) {
                                if (index >= visibleCount) {
                                    $(this).slideUp();
                                }
                            });
                            $(this).text('See More');
                        }
                    });
                }
            });

            $('.load-delete-modal').on('click', function () {
                let itemId = $(this).data('delete');
                <?php if(env('APP_ENV')!='demo'): ?>
                form_alert('delete-' + itemId, '<?php echo e(translate('want_to_delete_this')); ?>?')
                <?php endif; ?>
            })
});
        $(".view-btn").on("click", function () {
            var container = $(this).closest(".view-details-container");
            var details = container.find(".view-details");
            var icon = $(this).find("i");

            $(this).toggleClass("active");
            details.slideToggle(300);
            icon.toggleClass("rotate-180deg");
        });
        $(".section-toggle").on("change", function () {
            if (!$(this).hasClass('section-toggle')) return;
            if ($(this).is(':checked')) {
                $(this).closest(".view-details-container").find(".view-details").slideDown(300);
            } else {
                $(this).closest(".view-details-container").find(".view-details").slideUp(300);
            }
        });

        
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.new-master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/firebase.blade.php ENDPATH**/ ?>