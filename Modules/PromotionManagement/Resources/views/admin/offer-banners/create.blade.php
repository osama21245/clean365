@extends('adminmodule::layouts.master')

@section('title', translate('Offer Banners'))

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
                    <div class="page-title-wrap mb-3 d-flex justify-content-between align-items-center">
                        <h2 class="page-title">{{translate('Offer Banners Setup')}}</h2>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form id="offer-banner-create-form"
                                  action="{{route('admin.offer-banner.store')}}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'title',
                                                        'id'          => 'title',
                                                        'label'       => translate('Banner Main Title'),
                                                        'placeholder' => translate('e.g. Exclusive Discounts from Clean 365'),
                                                        'icon'        => 'title',
                                                        'required'    => true,
                                                        'maxlength'   => 190,
                                                        'value'       => old('title'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'textarea',
                                                        'name'        => 'subtitle',
                                                        'id'          => 'subtitle',
                                                        'label'       => translate('Subtitle / Description'),
                                                        'placeholder' => translate('Enter banner description or subtitle'),
                                                        'rows'        => 2,
                                                        'value'       => old('subtitle'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'number',
                                                        'name'        => 'original_price',
                                                        'id'          => 'original_price',
                                                        'label'       => translate('Original Price (SAR)'),
                                                        'placeholder' => '0.00',
                                                        'step'        => '0.01',
                                                        'value'       => old('original_price'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'number',
                                                        'name'        => 'offer_price',
                                                        'id'          => 'offer_price',
                                                        'label'       => translate('Offer Price (SAR)'),
                                                        'placeholder' => '0.00',
                                                        'step'        => '0.01',
                                                        'value'       => old('offer_price'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'tag',
                                                        'id'          => 'tag',
                                                        'label'       => translate('Tag'),
                                                        'placeholder' => translate('e.g. Packages Section'),
                                                        'value'       => old('tag'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-md-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'discount_badge',
                                                        'id'          => 'discount_badge',
                                                        'label'       => translate('Discount Badge'),
                                                        'placeholder' => translate('e.g. 20% OFF'),
                                                        'value'       => old('discount_badge'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-2 fw-medium">{{translate('Resource Type')}} <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="category" name="resource_type"
                                                                   value="category"
                                                                   {{ old('resource_type', 'category') == 'category' ? 'checked' : '' }}>
                                                            <label for="category">{{translate('Package / Category')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="service" name="resource_type"
                                                                   value="service"
                                                                   {{ old('resource_type') == 'service' ? 'checked' : '' }}>
                                                            <label for="service">{{translate('Service / Extra Service')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="redirect_link" name="resource_type"
                                                                   value="link"
                                                                   {{ old('resource_type') == 'link' ? 'checked' : '' }}>
                                                            <label for="redirect_link">{{translate('Product / Redirect Link')}}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="category_selector">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'category_id',
                                                        'id'          => 'category_id',
                                                        'label'       => translate('Select Package / Category'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Category'),
                                                        'options'     => $categories->pluck('name', 'id')->toArray(),
                                                        'value'       => old('category_id'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12 service_selector" id="service_selector">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'service_id',
                                                        'id'          => 'service_id',
                                                        'label'       => translate('Select Service'),
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Service'),
                                                        'options'     => $services->pluck('name', 'id')->toArray(),
                                                        'value'       => old('service_id'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12 link_selector" id="link_selector">
                                                    @include('partials._form-field', [
                                                        'type'        => 'url',
                                                        'name'        => 'redirect_link',
                                                        'id'          => 'redirect_link_input',
                                                        'label'       => translate('Redirect Link'),
                                                        'placeholder' => translate('https://example.com'),
                                                        'icon'        => 'link',
                                                        'value'       => old('redirect_link'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                @include('adminmodule::admin.partials._single-image-upload', [
                                                    'name'             => 'image',
                                                    'id'               => 'bannerImage',
                                                    'title'            => translate('Banner Image'),
                                                    'subtitle'         => translate('Upload banner image'),
                                                    'required'         => true,
                                                    'image'            => null,
                                                    'ratio'            => 'ratio-2-1',
                                                    'instructionRatio' => '2:1'])
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                            <button type="reset" class="btn btn--secondary">{{translate('reset')}}</button>
                                            <button type="submit" class="btn btn--primary demo_check">{{translate('submit')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-borderless align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Image')}}</th>
                                        <th>{{translate('Title')}}</th>
                                        <th>{{translate('Type')}}</th>
                                        <th>{{translate('Offer Price')}}</th>
                                        <th>{{translate('Status')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($offerBanners as $key => $item)
                                        <tr>
                                            <td>{{$key + $offerBanners->firstItem()}}</td>
                                            <td>
                                                <img width="80" class="rounded border" src="{{ $item->image_full_path }}" alt="banner">
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{$item->title}}</div>
                                                <small class="text-muted">{{$item->subtitle}}</small>
                                            </td>
                                            <td><span class="badge bg-soft-info text-capitalize">{{$item->resource_type}}</span></td>
                                            <td>
                                                @if($item->offer_price)
                                                    <span class="fw-bold text-success">{{$item->offer_price}} SAR</span>
                                                    @if($item->original_price)<del class="text-muted ms-1 text-xs">{{$item->original_price}} SAR</del>@endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <label class="switcher">
                                                    <input class="switcher_input status-change"
                                                           data-url="{{route('admin.offer-banner.status-update', [$item->id])}}"
                                                           type="checkbox" {{$item->is_active?'checked':''}}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{route('admin.offer-banner.edit',[$item->id])}}" class="btn btn-soft-info btn-sm">
                                                        <i class="material-icons">edit</i>
                                                    </a>
                                                    <form action="{{route('admin.offer-banner.delete',[$item->id])}}" method="post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-soft-danger btn-sm demo_check">
                                                            <i class="material-icons">delete</i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">{{translate('No Offer Banners Found')}}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                {!! $offerBanners->links() !!}
                            </div>
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

            $('.status-change').on('change', function() {
                let url = $(this).data('url');
                $.get(url, function(data) {
                    toastr.success('{{translate("Status updated successfully")}}');
                });
            });
        })();
    </script>
@endpush
