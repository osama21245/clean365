<?php $__env->startSection('title',translate('Language Setup')); ?>

<?php $__env->startPush('css_or_js'); ?>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('Language_Setup')); ?></h2>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <form action="<?php echo e(route('admin.language.store')); ?>" method="post" id="language-add-form">
                                <?php echo csrf_field(); ?>
                                <div class="body-bg rounded p-20">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="">
                                                <div class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Language')); ?>

                                                    <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="<?php echo e(translate('Enter the language name to add to the list')); ?>"
                                                    >info</i>
                                                </div>
                                                <input type="text" class="form-control" name="name" value="" placeholder="<?php echo e(translate('Language Name')); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="country-select-whitebg only-country-picker">
                                                <div class="mb-2 text-dark d-flex align-items-center gap-1">
                                                    <?php echo e(translate('Country code')); ?>

                                                </div>
                                                <select class="js-select" name="code" id="" required>
                                                    <option value="" disabled selected><?php echo e(translate('Select One')); ?></option>
                                                    <?php $__currentLoopData = LANGUAGE_SETUP_COUNTRY_CODE_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($value); ?>"><?php echo e(translate($label)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="">
                                                <div class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Direction')); ?>

                                                    <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="<?php echo e(translate('Direction Change , Left to Right and Right to Left')); ?>"
                                                    >info</i>
                                                </div>
                                                <div class="rounded form-control min-h45">
                                                    <div class="d-flex align-items-center gap-4 gap-xl-5">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="ltr" name="direction" value="ltr"
                                                                checked="">
                                                            <label for="ltr"><?php echo e(translate('Left to Right')); ?></label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="rtl" name="direction" value="rtl">
                                                            <label for="rtl"><?php echo e(translate('Right to Left')); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_add')): ?>
                                    <div class="d-flex justify-content-end gap-3 mt-20">
                                        <button class="btn btn--secondary rounded"
                                                type="reset"><?php echo e(translate('reset')); ?></button>
                                        <button class="btn btn--primary rounded demo_check"
                                                type="submit"><?php echo e(translate('submit')); ?></button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="data-table-top mb-20 d-flex align-items-center flex-wrap gap-xl-3 gap-2 justify-content-between">
                                <h4 class="fw-bold text-dark"><?php echo e(translate('Language List')); ?></h4>
                                <form action="<?php echo e(url()->current()); ?>" class="search-form search-form_style-two d-flex align-items-center gap-0 border rounded" method="GET">
                                    <?php echo csrf_field(); ?>
                                    <div class="input-group search-form__input_group bg-transparent">
                                        <input type="search" class="theme-input-style search-form__input border-0  block-size-36" name="search"
                                               placeholder="<?php echo e(translate('search by code')); ?>"
                                               value="<?php echo e(request()?->search ?? null); ?>">
                                    </div>
                                    <button type="submit" class="bg-light border-0 px-2 block-size-36 rounded-end d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-outlined fz-20 opacity-75">
                                            search
                                        </span>
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <?php
                                    $languageSetupTableColspan = 3;
                                ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_manage_status')): ?>
                                    <?php
                                        $languageSetupTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['language_update', 'language_delete', 'language_view', 'language_manage_status'])): ?>
                                    <?php
                                        $languageSetupTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th><?php echo e(translate('SL')); ?></th>
                                        <th class="text-center"><?php echo e(translate('Language')); ?></th>
                                        <th class="text-center"><?php echo e(translate('Code')); ?></th>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_manage_status')): ?>
                                            <th class="text-center"><?php echo e(translate('Status')); ?></th>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['language_update', 'language_delete', 'language_view', 'language_manage_status'])): ?>
                                            <th class="text-center"><?php echo e(translate('action')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $searchValue = trim((string) request('search'));

                                        $collection = collect($system_language['live_values'] ?? []);

                                        $filteredValues = $collection;

                                        if ($searchValue !== '') {
                                            $filteredValues = $filteredValues->filter(function ($item) use ($searchValue) {
                                                $code = (string) ($item['code'] ?? '');
                                                return stripos($code, $searchValue) !== false;
                                            });
                                        }
                                        $filteredValues = $filteredValues->all();
                                    ?>
                                    <?php $__empty_1 = true; $__currentLoopData = $filteredValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="text-start"><?php echo e($key+1); ?></td>
                                            <td class="text-center">
                                                <?php echo e($data['name'] ?? ''); ?>

                                                <?php if($data['default']): ?>
                                                    <span class="badge badge-info font-weight-light fz-10"><?php echo e(translate('default')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo e($data['code']); ?></td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_manage_status')): ?>
                                                <td class="text-center">
                                                    <?php if(array_key_exists('default', $data) && $data['default']==true): ?>
                                                        <label class="switcher default-language-status mx-auto"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#deactivateAlertModal">
                                                            <input class="switcher_input"
                                                                   checked disabled
                                                                   type="checkbox" <?php echo e($data['status']?'checked':''); ?>>
                                                            <span class="switcher_control disabled"></span>
                                                        </label>
                                                    <?php elseif(array_key_exists('default', $data) && $data['default']==false): ?>
                                                        <label class="switcher mx-auto" data-bs-toggle="modal"
                                                               data-bs-target="#deactivateAlertModal">
                                                            <input class="switcher_input language-status-update"
                                                                   data-route="<?php echo e(route('admin.language.update-status',['code' =>$data['code']])); ?>"
                                                                   data-message="<?php echo e(translate('want_to_update_status')); ?>"
                                                                   type="checkbox" <?php echo e($data['status']?'checked':''); ?>>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['language_update', 'language_delete', 'language_view', 'language_manage_status'])): ?>
                                                <td>
                                                     <div class="d-flex gap-2 justify-content-center">
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_view')): ?>
                                                                <a href="<?php echo e(( env('APP_ENV') == 'demo') ? 'javascript:' : route('admin.language.translate', [$data['code']])); ?>"
                                                                   class="btn btn-outline-primary d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded fz-12 fw-semibold text-nowrap">
                                                                    <span class="material-symbols-outlined fz-16">g_translate</span>
                                                                    <span><?php echo e(translate('translate')); ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['language_manage_status', 'language_update', 'language_delete'])): ?>
                                                                <div class="btn-group">
                                                                    <button type="button"
                                                                            class="btn btn-outline-primary rounded d-inline-flex align-items-center justify-content-center p-2 fz-12 fw-semibold w-35px h-35px"
                                                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <span class="material-symbols-outlined fz-18">more_vert</span>
                                                                    </button>
                                                                    <div class="dropdown-menu cus-shadow2 p-3 dropdown-menu-right">
                                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_manage_status')): ?>
                                                                        <?php if($data['default']==false): ?>
                                                                            <button class="dropdown-item mb-1 d-flex align-items-center gap-2 fz-14 text-dark default-language"
                                                                                    type="button"
                                                                                    data-message="<?php echo e(translate('want_to_update_default_status')); ?>"
                                                                                    data-route="<?php echo e(route('admin.language.update-default-status',['code' =>$data['code']])); ?>">
                                                                                <i class="material-symbols-outlined">schedule</i> <?php echo e(translate('Mark As Default')); ?>

                                                                            </button>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>

                                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_update')): ?>
                                                                            <button class="dropdown-item mb-1 d-flex align-items-center gap-2 fz-14 text-dark edit-language-btn" type="button"
                                                                                    data-bs-toggle="offcanvas"
                                                                                    data-bs-target="#Edit__languageOffcanvas"
                                                                                    data-id="<?php echo e($data['id']); ?>"
                                                                                    data-name="<?php echo e($data['name'] ?? ''); ?>"
                                                                                    data-code="<?php echo e($data['code']); ?>"
                                                                                    data-direction="<?php echo e($data['direction']); ?>">
                                                                                <i class="material-symbols-outlined">edit_square</i> <?php echo e(translate('Edit')); ?>

                                                                            </button>
                                                                    <?php endif; ?>
                                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_delete')): ?>
                                                                            <?php if($data['default']==false): ?>
                                                                                <button type="button"
                                                                                        class="<?php echo e(env('APP_ENV') == 'demo' ? 'demo_check' : 'delete-content'); ?> dropdown-item  d-flex align-items-center gap-2 fz-14 text-dark"
                                                                                        data-id="<?php echo e($data['id']); ?>"
                                                                                        data-url="<?php echo e(route('admin.language.delete',[$data['code']])); ?>"
                                                                                        data-title="<?php echo e(translate('want_to_delete_this_language')); ?>?"
                                                                                        data-description="<?php echo e(translate('Once delete, you would not be translate this language')); ?>"
                                                                                        data-image="<?php echo e(asset('public/assets/admin-module/img/modal/delete-icon.svg')); ?>">
                                                                                    <i class="material-symbols-outlined">delete</i> <?php echo e(translate('Delete')); ?>

                                                                                </button>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $languageSetupTableColspan,
                                            'variant' => request()->filled('search') ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Offcanvas edit -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="Edit__languageOffcanvas" aria-labelledby="Edit__languageOffcanvasLabel">
        <div class="offcanvas-header bg-white py-lg-4 py-3">
            <h2 class="mb-0"><?php echo e(translate('Edit Language')); ?></h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form action="" method="post" id="update-form-submit">
            <?php echo csrf_field(); ?>

            <input type="hidden" name="code" id="language_code_hidden">

            <div class="offcanvas-body">
                <div class="d-flex flex-column gap-xl-4 gap-3">
                    <div class="">
                        <div class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Language')); ?>

                            <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="<?php echo e(translate('Change Language name')); ?>"
                            >info</i>
                        </div>
                        <input type="text" class="form-control" name="name" value="" id="language_name" placeholder="<?php echo e(translate('Language Name')); ?>" required>
                    </div>
                    <div class="country-select-whitebg only-country-picker">
                        <div class="mb-2 text-dark d-flex align-items-center gap-1">
                            <?php echo e(translate('Country code')); ?>

                        </div>
                        <select class="js-select" name="code" id="language_code" disabled>
                            <?php $__currentLoopData = LANGUAGE_SETUP_COUNTRY_CODE_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="">
                        <div class="mb-2 text-dark d-flex align-items-center gap-1"><?php echo e(translate('Direction')); ?>

                            <i class="material-icons fz-14 text-light-gray" data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="<?php echo e(translate('Direction Change , Left to Right and Right to Left')); ?>"
                            >info</i>
                        </div>
                        <div class="rounded form-control min-h45">
                            <div class="d-flex align-items-center gap-4 gap-xl-5">
                                <div class="custom-radio">
                                    <input type="radio" id="ltr1" name="direction" value="ltr">
                                    <label for="ltr1"><?php echo e(translate('Left to Right')); ?></label>
                                </div>
                                <div class="custom-radio">
                                    <input type="radio" id="rtl1" name="direction" value="rtl">
                                    <label for="rtl1"><?php echo e(translate('Right to Left')); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="offcanvas-footer">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language_update')): ?>
                    <div class="d-flex justify-content-center gap-2 bg-white px-3 py-sm-3 py-2">
                        <button class="btn btn--secondary w-100 rounded"
                                type="reset"><?php echo e(translate('reset')); ?></button>
                        <button class="btn btn--primary w-100 rounded demo_check"
                                type="submit"><?php echo e(translate('Update')); ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php $__currentLoopData = $system_language['live_values'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="lang-modal-update-<?php echo e($data['code']); ?>" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo e(translate('new_language')); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?php echo e(route('admin.language.update')); ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="hidden" name="code" value="<?php echo e($data['code']); ?>">
                                        <label for="message-text" class="col-form-label"><?php echo e(translate('language')); ?></label>
                                        <select disabled id="lang_code" class="form-control js-select2-custom">
                                            <?php $__currentLoopData = LANGUAGE_SETUP_COUNTRY_CODE_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option
                                                    value="<?php echo e($value); ?>" <?php echo e($data['code'] == $value?'selected':''); ?>><?php echo e($label); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="col-form-label"><?php echo e(translate('direction')); ?> :</label>
                                        <select class="form-control" name="direction">
                                            <option
                                                value="ltr" <?php echo e(isset($data['direction'])?$data['direction']=='ltr'?'selected':'':''); ?>>
                                                <?php echo e(translate('LTR')); ?>

                                            </option>
                                            <option
                                                value="rtl" <?php echo e(isset($data['direction'])?$data['direction']=='rtl'?'selected':'':''); ?>>
                                                <?php echo e(translate('RTL')); ?>

                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal"><?php echo e(translate('close')); ?></button>
                            <button type="submit" class="btn btn--primary"><?php echo e(translate('update')); ?> <i
                                    class="fa fa-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script>
        function default_language_status_alert() {
            toastr.warning('<?php echo e(translate("default_language_can_not_be_deactive")); ?>!');
        }
    </script>

    <script>
        "use strict";

        $('.delete-language').on('click', function () {
            let id = $(this).data('id');
            let message = "<?php echo e(translate('delete_this_language')); ?>?"
            <?php if(env('APP_ENV')!='demo'): ?>
            form_alert(id, message)
            <?php endif; ?>
        });

        $('.default-language').on('click', function () {
            let route = $(this).data('route');
            let message = $(this).data('message');
            <?php if(env('APP_ENV')!='demo'): ?>
            default_status_change(route, message)
            <?php endif; ?>
        });

        $('.language-status-update').on('click', function () {
            let route = $(this).data('route');
            let message = $(this).data('message');
            <?php if(env('APP_ENV')!='demo'): ?>
            route_alert(route, message)
            <?php endif; ?>
        });


        $('.default-language-status').on('click', function () {
            default_language_status_alert()
        });

        function default_status_change(route, message) {
            Swal.fire({
                title: "<?php echo e(translate('are_you_sure')); ?>?",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        dataType: 'json',
                        data: {},
                        beforeSend: function () {

                        },
                        success: function (data) {
                            console.log(data)
                            setTimeout(function () {
                                location.reload();
                            }, 1000);

                            toastr.success(data.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function () {

                        },
                    });
                }
            })
        }

        $(document).ready(function () {
            $('.edit-language-btn').on('click', function () {
                let name = $(this).data('name');
                let code = $(this).data('code');
                let direction = $(this).data('direction');

                // text input
                $('#language_name').val(name || code).prop('defaultValue', name || code); // set defaultValue for reset

                // select
                $('#language_code').val(code).trigger('change').find('option[value="' + code + '"]').prop('selected', true);
                $('#language_code_hidden').val(code);

                // radio buttons
                $('input[name="direction"]').prop('checked', false).prop('defaultChecked', false);
                $('input[name="direction"][value="' + direction + '"]').prop('checked', true).prop('defaultChecked', true);

                $('#update-form-submit').attr('action', '/admin/language/update');
            });
        });

        $(document).on('reset', '#language-add-form', function () {
            let $form = $(this);

            $form.find('.js-select').val('').trigger('change');
            $form.find('input[name="direction"][value="ltr"]').prop('checked', true);

        });


    </script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.new-master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/language-setup.blade.php ENDPATH**/ ?>