@extends('adminmodule::layouts.master')

@section('title', translate('Edit Offer Banner'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css" />
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Edit Offer Banner')}}</h2>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form id="offer-banner-edit-form" action="{{route('admin.offer-banner.update', [$banner->id])}}"
                                method="POST" enctype="multipart/form-data" data-ff-validate novalidate>
                                @method('PUT')
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type' => 'text',
                                                        'name' => 'title',
                                                        'id' => 'title',
                                                        'label' => translate('Banner Main Title'),
                                                        'placeholder' => translate('e.g. Exclusive Discounts from Clean 365'),
                                                        'icon' => 'title',
                                                        'required' => true,
                                                        'maxlength' => 190,
                                                        'value' => old('title', $banner->title),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>

                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type' => 'textarea',
                                                        'name' => 'subtitle',
                                                        'id' => 'subtitle',
                                                        'label' => translate('Subtitle / Description'),
                                                        'placeholder' => translate('Enter banner description or subtitle'),
                                                        'rows' => 2,
                                                        'value' => old('subtitle', $banner->subtitle),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type' => 'number',
                                                        'name' => 'original_price',
                                                        'id' => 'original_price',
                                                        'label' => translate('Original Price (SAR)'),
                                                        'placeholder' => '0.00',
                                                        'step' => '0.01',
                                                        'value' => old('original_price', $banner->original_price),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type' => 'number',
                                                        'name' => 'offer_price',
                                                        'id' => 'offer_price',
                                                        'label' => translate('Offer Price (SAR)'),
                                                        'placeholder' => '0.00',
                                                        'step' => '0.01',
                                                        'value' => old('offer_price', $banner->offer_price),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type' => 'text',
                                                        'name' => 'tag',
                                                        'id' => 'tag',
                                                        'label' => translate('Tag'),
                                                        'placeholder' => translate('e.g. Packages Section'),
                                                        'value' => old('tag', $banner->tag),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type' => 'text',
                                                        'name' => 'discount_badge',
                                                        'id' => 'discount_badge',
                                                        'label' => translate('Discount Badge'),
                                                        'placeholder' => translate('e.g. 20% OFF'),
                                                        'value' => old('discount_badge', $banner->discount_badge),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-2 fw-medium">{{translate('Resource Type')}} <span
                                                            class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="category" name="resource_type"
                                                                value="category" {{ old('resource_type', $banner->resource_type) == 'category' ? 'checked' : '' }}>
                                                            <label
                                                                for="category">{{translate('Package / Category')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="service" name="resource_type"
                                                                value="service" {{ old('resource_type', $banner->resource_type) == 'service' ? 'checked' : '' }}>
                                                            <label
                                                                for="service">{{translate('Service / Extra Service')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="redirect_link" name="resource_type"
                                                                value="link" {{ old('resource_type', $banner->resource_type) == 'link' ? 'checked' : '' }}>
                                                            <label
                                                                for="redirect_link">{{translate('Product / Redirect Link')}}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="category_selector"
                                                    style="display: {{ old('resource_type', $banner->resource_type) == 'category' ? 'block' : 'none' }}">
                                                    @include('partials._form-field', [
                                                        'type' => 'select',
                                                        'name' => 'category_id',
                                                        'id' => 'category_id',
                                                        'label' => translate('Select Package / Category'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull' => translate('Select Category'),
                                                        'options' => $categories->pluck('name', 'id')->toArray(),
                                                        'value' => old('category_id', $banner->resource_type == 'category' ? $banner->resource_id : ''),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>
                                                <div class="col-12" id="service_selector"
                                                    style="display: {{ old('resource_type', $banner->resource_type) == 'service' ? 'block' : 'none' }}">
                                                    @include('partials._form-field', [
                                                        'type' => 'select',
                                                        'name' => 'service_id',
                                                        'id' => 'service_id',
                                                        'label' => translate('Select Service'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull' => translate('Select Service'),
                                                        'options' => $services->pluck('name', 'id')->toArray(),
                                                        'value' => old('service_id', $banner->resource_type == 'service' ? $banner->resource_id : ''),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>
                                                <div class="col-12" id="link_selector"
                                                    style="display: {{ old('resource_type', $banner->resource_type) == 'link' ? 'block' : 'none' }}">
                                                    @include('partials._form-field', [
                                                        'type' => 'url',
                                                        'name' => 'redirect_link',
                                                        'id' => 'redirect_link_input',
                                                        'label' => translate('Redirect Link'),
                                                        'placeholder' => translate('https://example.com'),
                                                        'icon' => 'link',
                                                        'value' => old('redirect_link', $banner->redirect_link),
                                                        'wrapClass' => 'mb-0'
                                                    ])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div
                                            class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                @include('adminmodule::admin.partials._single-image-upload', [
                                                    'name' => 'image',
                                                    'id' => 'bannerImage',
                                                    'title' => translate('Banner Image'),
                                                    'subtitle' => translate('Upload banner image'),
                                                    'required' => false,
                                                    'image' => $banner->image_full_path,
                                                    'ratio' => 'ratio-2-1',
                                                    'instructionRatio' => '2:1',
                                                    'showDelete' => false
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                            <a href="{{route('admin.offer-banner.create')}}"
                                                class="btn btn--secondary">{{translate('reset')}}</a>
                                            <button type="submit"
                                                class="btn btn--primary demo_check">{{translate('update')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
        (function () {
            "use strict";

            $(document).ready(function () {
                $('.js-select').select2();
                toggleSelectors($('input[name="resource_type"]:checked').val());
            });

            $('input[name="resource_type"]').on('change', function () {
                toggleSelectors($(this).val());
            });

            function toggleSelectors(val) {
                if (val === 'category') {
                    $('#category_selector').show();
                    $('#service_selector').hide();
                    $('#link_selector').hide();
                } else if (val === 'service') {
                    $('#category_selector').hide();
                    $('#service_selector').show();
                    $('#link_selector').hide();
                } else {
                    $('#category_selector').hide();
                    $('#service_selector').hide();
                    $('#link_selector').show();
                }
            }
        })();
    </script>
@endpush