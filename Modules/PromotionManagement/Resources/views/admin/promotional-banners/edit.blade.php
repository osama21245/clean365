@extends('adminmodule::layouts.master')

@section('title',translate('Promotional Banner Update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Promotional Banner Update')}}</h2>
                    </div>
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            @can('banner_update')
                            <form id="banner-edit-form"
                                  action="{{route('admin.banner.update',[$banner->id])}}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                @method('PUT')
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    @include('partials._form-field', [
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
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-2 fw-medium">{{translate('Resource Type')}} <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="category" name="resource_type" value="category"
                                                                {{ old('resource_type', $banner->resource_type) == 'category' ? 'checked' : '' }}>
                                                            <label for="category">{{translate('Category Wise')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="service" name="resource_type" value="service"
                                                                {{ old('resource_type', $banner->resource_type) == 'service' ? 'checked' : '' }}>
                                                            <label for="service">{{translate('Service Wise')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="redirect_link" name="resource_type" value="link"
                                                                {{ old('resource_type', $banner->resource_type) == 'link' ? 'checked' : '' }}>
                                                            <label for="redirect_link">{{translate('Redirect Link')}}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="category_selector" style="display: {{ old('resource_type', $banner->resource_type) == 'category' ? 'block' : 'none' }}">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'category_id',
                                                        'id'          => 'category_id',
                                                        'label'       => translate('Category'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Category'),
                                                        'options'     => $categories->pluck('name', 'id')->toArray(),
                                                        'value'       => old('category_id', $banner->resource_type == 'category' ? $banner->resource_id : ''),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12" id="service_selector" style="display: {{ old('resource_type', $banner->resource_type) == 'service' ? 'block' : 'none' }}">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'service_id',
                                                        'id'          => 'service_id',
                                                        'label'       => translate('Service'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Service'),
                                                        'options'     => $services->pluck('name', 'id')->toArray(),
                                                        'value'       => old('service_id', $banner->resource_type == 'service' ? $banner->resource_id : ''),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12" id="link_selector" style="display: {{ old('resource_type', $banner->resource_type) == 'link' ? 'block' : 'none' }}">
                                                    @include('partials._form-field', [
                                                        'type'        => 'url',
                                                        'name'        => 'redirect_link',
                                                        'id'          => 'redirect_link_input',
                                                        'label'       => translate('Redirect Link'),
                                                        'placeholder' => translate('https://example.com'),
                                                        'icon'        => 'link',
                                                        'value'       => old('redirect_link', $banner->redirect_link),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                @include('adminmodule::admin.partials._single-image-upload', [
                                                    'name'             => 'banner_image',
                                                    'id'               => 'bannerImage',
                                                    'title'            => translate('Cover Image'),
                                                    'subtitle'         => translate('Upload banner image'),
                                                    'required'         => false,
                                                    'image'            => $banner->banner_image_full_path,
                                                    'ratio'            => 'ratio-2-1',
                                                    'instructionRatio' => '2:1',
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
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
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

            var originalBannerImage = @json($banner->banner_image_full_path ?? '');

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
                            '{{ translate("Updating...") }}'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
@endpush
