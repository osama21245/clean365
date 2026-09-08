@extends('adminmodule::layouts.master')

@section('title',translate('Update Push Notification'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    @php($currentCoverImage = $pushNotification->cover_image_full_path ?? null)
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update Push Notification')}}</h2>
                    </div>
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            @can('push_notification_update')
                            <form id="push-notification-edit-form"
                                  action="{{route('admin.push-notification.update',[$pushNotification->id])}}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <div class="d-flex flex-column gap-3">
                                                @include('partials._form-field', [
                                                    'type'        => 'textarea',
                                                    'name'        => 'title',
                                                    'id'          => 'push_title',
                                                    'label'       => translate('Title'),
                                                    'placeholder' => translate('Type title'),
                                                    'rows'        => 1,
                                                    'required'    => true,
                                                    'maxlength'   => 100,
                                                    'charCount'   => true,
                                                    'extraAttrs'  => 'data-maxlength="100"',
                                                    'value'       => old('title', $pushNotification->title),
                                                    'wrapClass'   => 'mb-0'])
                                                @include('partials._form-field', [
                                                    'type'        => 'textarea',
                                                    'name'        => 'description',
                                                    'id'          => 'push_description',
                                                    'label'       => translate('Description'),
                                                    'placeholder' => translate('Type about the description'),
                                                    'rows'        => 3,
                                                    'required'    => true,
                                                    'maxlength'   => 200,
                                                    'charCount'   => true,
                                                    'extraAttrs'  => 'data-maxlength="200"',
                                                    'value'       => old('description', $pushNotification->description),
                                                    'wrapClass'   => 'mb-0'])
                                                <div class="row g-3">
                                                    <div class="col-lg-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'zone_ids[]',
                                                            'id'          => 'zone_selector__select',
                                                            'label'       => translate('Zones'),
                                                            'multiple'    => true,
                                                            'required'    => true,
                                                            'selectClass' => 'select-zone theme-input-style w-100',
                                                            'prependOptions' => [
                                                                ['value' => 'all', 'label' => translate('Select All')]],
                                                            'options'     => $zones->pluck('name', 'id')->toArray(),
                                                            'value'       => old('zone_ids', collect($pushNotification['zone_ids'])->pluck('id')->toArray()),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-lg-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'to_users[]',
                                                            'id'          => 'user_selector__select',
                                                            'label'       => translate('Targeted User'),
                                                            'multiple'    => true,
                                                            'required'    => true,
                                                            'selectClass' => 'select-user theme-input-style w-100',
                                                            'options'     => [
                                                                'all'                 => translate('all'),
                                                                'customer'            => translate('customer'),
                                                                'provider-admin'      => translate('provider'),
                                                                'provider-serviceman' => translate('serviceman')],
                                                            'value'       => old('to_users', $pushNotification->to_users ?? []),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                @include('adminmodule::admin.partials._single-image-upload', [
                                                    'name'             => 'cover_image',
                                                    'id'               => 'pushNotificationImage',
                                                    'title'            => translate('Cover Image'),
                                                    'required'         => false,
                                                    'image'            => $currentCoverImage,
                                                    'ratio'            => 'ratio-2-1',
                                                    'instructionRatio' => '2:1',
                                                    'showView'         => true,
                                                    'showEdit'         => true,
                                                    'showDelete'       => false])
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                            <button type="reset" class="btn btn--secondary">{{translate('reset')}}</button>
                                            <button type="submit" class="btn btn--primary demo_check">{{translate('update')}}</button>
                                        </div>
                                    </div>
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
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>

    <script>
        "use strict";

        $('#user_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $(document).ready(function () {
            $('.select-zone').select2({ placeholder: "{{translate('Select Zones')}}", width: '100%' });
            $('.select-user').select2({ placeholder: "{{translate('Select Users')}}", width: '100%' });
        });

        (function () {
            var $form = $('#push-notification-edit-form');
            if (!$form.length) return;

            var originalCoverImage = @json($currentCoverImage ?? '');

            function setCoverPreview(src) {
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
                    setCoverPreview(originalCoverImage);
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () {
                            window.FormCharCount.update(this);
                        });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#push-notification-edit-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '{{ translate("Updating...") }}'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
@endpush
