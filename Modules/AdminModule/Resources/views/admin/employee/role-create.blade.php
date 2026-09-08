@extends('adminmodule::layouts.master')

@section('title', translate('role_settings'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex justify-content-between gap-2 mb-3">
                        <h2 class="page-title">{{translate('Employee Role Setup')}}</h2>
                    </div>

                    <div class="card mb-30">
                        <div class="card-header shadow-none border-bottom">
                            <h4 class="mb-1">{{translate('Create New Role')}}</h4>
                            <p class="fs-12">{{translate('Create new role with access')}}</p>
                        </div>

                        <div class="card-body p-30">
                            <form id="role-create-form"
                                  action="{{route('admin.role.store')}}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            @include('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'role_name',
                                                'id'          => 'role_name',
                                                'label'       => translate('Role Name'),
                                                'placeholder' => translate('Enter Role Name'),
                                                'icon'        => 'subtitles',
                                                'required'    => true,
                                                'maxlength'   => 191,
                                                'charCount'   => true,
                                                'value'       => old('role_name'),
                                                'wrapClass'   => 'mb-0'])
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 mb-4">
                                    <div class="d-flex gap-4 align-items-center flex-wrap mb-1">
                                        <h4 class="mb-0">{{translate('Permissions / Accesses')}}</h4>

                                        <div class="bg--secondary px-2 py-1 rounded d-flex gap-1 align-items-center">
                                            <input type="checkbox" value="" id="select_all">
                                            <label class="user-select-none mb-0" for="select_all">{{translate('Select All')}}</label>
                                        </div>
                                    </div>
                                    <p class="fs-12 text-muted mb-0">{{translate('Select the options you want to give access to this role')}}</p>
                                </div>

                                <div class="access-checkboxes">
                                    <div class="row gy-3">
                                        @foreach(SYSTEM_MODULES as $module)
                                            @if(!isset($module['submodules']) && empty($module['submodules']))
                                                <div class="col-sm-6 col-lg-3">
                                                    <div class="bg--secondary px-3 py-2 rounded d-flex gap-1 align-items-center">
                                                        <label class="user-select-none flex-grow-1 mb-0"
                                                               for="{{ $module['key'] }}">{{ $module['value'] }}</label>
                                                        <input type="checkbox" name="modules[{{ $module['key'] }}]"
                                                               id="{{ $module['key'] }}">
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <hr class="my-4">

                                    <div class="row g-4 mb-4">
                                        @foreach(SYSTEM_MODULES as $module)
                                            @if(isset($module['submodules']) && !empty($module['submodules']))
                                                <div class="col-md-12">
                                                    <div class="card checkbox_card overflow-hidden">
                                                        <div class="checkbox_card__head bg--secondary p-3 d-flex gap-1 align-items-center">
                                                            <label class="user-select-none flex-grow-1 mb-0"
                                                                   for="{{ $module['key'] }}">{{ $module['value']}}</label>
                                                            <input type="checkbox"
                                                                   name="section_modules[{{ $module['key'] }}]"
                                                                   id="{{ $module['key'] }}">
                                                            <label class="user-select-none mb-0"
                                                                   for="{{ $module['key'] }}">{{translate('Select All')}}</label>
                                                        </div>

                                                        <div class="card-body">
                                                            <div class="grid-columns">
                                                                @foreach($module['submodules'] as $submodule)
                                                                    <div class="d-flex gap-1 align-items-center">
                                                                        <input class="mb-1" type="checkbox"
                                                                               name="modules[{{ $submodule['key'] }}]"
                                                                               id="{{ $submodule['key'] }}">
                                                                        <label class="user-select-none flex-grow-1 mb-0"
                                                                               for="{{ $submodule['key'] }}">{{ $submodule['value'] }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <h4 class="mb-3 mt-4">{{translate('Manage Access')}}</h4>
                                <div class="table-responsive">
                                    <table class="table align-middle border-bottom">
                                        <thead class="text-nowrap">
                                        <tr>
                                            <th class="text-center">{{translate('Add')}}</th>
                                            <th class="text-center">{{translate('Update')}}</th>
                                            <th class="text-center">{{translate('Delete')}}</th>
                                            <th class="text-center">{{translate('Export')}}</th>
                                            <th class="text-center">{{translate('Status on/Off')}}</th>
                                            <th class="text-center">{{translate('Approve or Deny')}}</th>
                                            <th class="text-center">{{translate('Assign Serviceman')}}</th>
                                            <th class="text-center">{{translate('Give FeedBack')}}</th>
                                            <th class="text-center">{{translate('Take Backup')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="add" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="update" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="delete" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="export" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="status" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="approve_or_deny" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="assign_serviceman" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="give_feedback" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="switcher mx-auto">
                                                    <input class="switcher_input" name="take_backup" type="checkbox" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert-show">
                                    <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show mt-3 mb-0" role="alert">
                                        <div class="media gap-2">
                                            <img src="{{asset('public/assets/admin-module/img/WarningOctagon.svg')}}" class="svg" alt="">
                                            <div class="media-body">
                                                {{translate('If no access is selected, employees with this role can only view the section for which permissions are granted; they cannot perform any actions.')}}
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>

                                @can('role_add')
                                    <div class="d-flex gap-4 flex-wrap justify-content-end mt-4">
                                        <button type="reset" class="btn btn--secondary">{{translate('reset')}}</button>
                                        <button type="submit" class="btn btn--primary" id="formSubmit">{{translate('submit')}}</button>
                                    </div>
                                @endcan
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/js/custom.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
    <script>
        "use strict";

        var roleForm = $("#role-create-form");

        roleForm.validate({
            ignore: ":disabled,:hidden",
            errorPlacement: function errorPlacement(error, element) {
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
                element.after(error);
            },
            rules: {
                role_name: {
                    required: true,
                    maxlength: 191
                }
            }
        });

        $('#select_all').on('change', function () {
            $('.access-checkboxes input[type="checkbox"]').prop('checked', this.checked);
        });
        $('.access-checkboxes input[type="checkbox"]').on('change', function () {
            updateSelectAllState();
        });

        $('.checkbox_card__head input[type="checkbox"]').on('change', function () {
            $(this).closest('.checkbox_card').find('input[type="checkbox"]').prop('checked', this.checked);
            updateSelectAllState();
        });
        $('.checkbox_card input[type="checkbox"]').on('change', function () {
            let allChecked = true;
            $(this).closest('.checkbox_card').find('.card-body input[type="checkbox"]').each(function () {
                if (!$(this).prop('checked')) {
                    allChecked = false;
                    return false;
                }
            });
            $(this).closest('.checkbox_card').find('.checkbox_card__head input[type="checkbox"]').prop('checked', allChecked);
            updateSelectAllState();
        });

        function updateSelectAllState() {
            const all = $('.access-checkboxes input[type="checkbox"]').length;
            const checked = $('.access-checkboxes input[type="checkbox"]:checked').length;
            $('#select_all').prop('checked', all === checked);
        }

        function areAllInputsUnchecked() {
            var checkboxes = document.querySelectorAll('.table .switcher_input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    return false;
                }
            }
            return true;
        }

        function toggleAlertShow() {
            var alertShowDiv = document.querySelector('.alert-show');
            if (!alertShowDiv) return;
            if (areAllInputsUnchecked()) {
                alertShowDiv.style.display = 'block';
            } else {
                alertShowDiv.style.display = 'none';
            }
        }

        var switchCheckboxes = document.querySelectorAll('.table .switcher_input');
        switchCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                toggleAlertShow();
            });
        });
        toggleAlertShow();

        roleForm.on('reset', function () {
            setTimeout(function () {
                if (window.FormCharCount) {
                    roleForm.find('[data-char-count]').each(function () {
                        window.FormCharCount.update(this);
                    });
                }
                roleForm.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                if (roleForm.data('validator')) {
                    roleForm.validate().resetForm();
                }
                updateSelectAllState();
                toggleAlertShow();
            }, 0);
        });
    </script>
@endpush
