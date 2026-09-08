@extends('adminmodule::layouts.master')

@section('title',translate('promotional_banners'))

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
                        <h2 class="page-title">{{translate('promotional_banners')}}</h2>
                    </div>

                    @can('banner_add')
                        <div class="card mb-30">
                            <div class="card-body p-30">
                                <form id="banner-create-form"
                                      action="{{route('admin.banner.store')}}"
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
                                                            'name'        => 'banner_title',
                                                            'id'          => 'banner_title',
                                                            'label'       => translate('Banner Title'),
                                                            'placeholder' => translate('Enter Banner Title'),
                                                            'icon'        => 'title',
                                                            'required'    => true,
                                                            'maxlength'   => 190,
                                                            'charCount'   => true,
                                                            'value'       => old('banner_title'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="mb-2 fw-medium">{{translate('Resource Type')}} <span class="text-danger">*</span></div>
                                                        <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                            <div class="custom-radio">
                                                                <input type="radio" id="category" name="resource_type"
                                                                       value="category"
                                                                       {{ old('resource_type', 'category') == 'category' ? 'checked' : '' }}>
                                                                <label for="category">{{translate('Category Wise')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="service" name="resource_type"
                                                                       value="service"
                                                                       {{ old('resource_type') == 'service' ? 'checked' : '' }}>
                                                                <label for="service">{{translate('Service Wise')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="redirect_link" name="resource_type"
                                                                       value="link"
                                                                       {{ old('resource_type') == 'link' ? 'checked' : '' }}>
                                                                <label for="redirect_link">{{translate('Redirect Link')}}</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12" id="category_selector">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'category_id',
                                                            'id'          => 'category_id',
                                                            'label'       => translate('Category'),
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
                                                            'label'       => translate('Service'),
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
                                                        'name'             => 'banner_image',
                                                        'id'               => 'bannerImage',
                                                        'title'            => translate('Cover Image'),
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
                    @endcan

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$resourceType=='all'?'active':''}}"
                                   href="{{url()->current()}}?resource_type=all">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$resourceType=='category'?'active':''}}"
                                   href="{{url()->current()}}?resource_type=category">
                                    {{translate('category_wise')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$resourceType=='service'?'active':''}}"
                                   href="{{url()->current()}}?resource_type=service">
                                    {{translate('service_wise')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Banners')}}:</span>
                            <span class="title-color">{{$banners->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}?resource_type={{$resourceType}}"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit"
                                            class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @can('banner_export')
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                            <span
                                                class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <a class="dropdown-item"
                                                   href="{{route('admin.banner.download')}}?search={{$search}}&resource_type={{ $resourceType }}">
                                                    {{translate('excel')}}
                                                </a>
                                            </ul>
                                        </div>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                @php
                                    $bannerTableColspan = 3;
                                @endphp
                                @can('banner_manage_status')
                                    @php
                                        $bannerTableColspan++;
                                    @endphp
                                @endcan
                                @canany(['banner_delete', 'banner_update'])
                                    @php
                                        $bannerTableColspan++;
                                    @endphp
                                @endcanany
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('sl')}}</th>
                                        <th>{{translate('title')}}</th>
                                        <th>{{translate('type')}}</th>
                                        @can('banner_manage_status')
                                            <th>{{translate('status')}}</th>
                                        @endcan
                                        @canany(['banner_delete', 'banner_update'])
                                            <th>{{translate('action')}}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($banners as $key => $item)
                                        <tr>
                                            <td>{{$key+$banners->firstItem()}}</td>
                                            <td>{{$item->banner_title}}</td>
                                            <td>{{$item->resource_type}}</td>
                                            @can('banner_manage_status')
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input"
                                                               data-status="{{$item->id}}"
                                                               type="checkbox" {{$item->is_active?'checked':''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            @endcan
                                            @canany(['banner_delete', 'banner_update'])
                                                <td>
                                                    <div class="table-actions">
                                                        @can('banner_update')
                                                            <a href="{{route('admin.banner.edit',[$item->id])}}"
                                                               class="action-btn btn--light-primary">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        @endcan
                                                        @can('banner_delete')
                                                            <button type="button"
                                                                    data-id="{{$item->id}}"
                                                                    class="action-btn btn--danger delete_section">
                                                                <span class="material-icons">delete</span>
                                                            </button>
                                                            <form action="{{route('admin.banner.delete',[$item->id])}}"
                                                                  method="post" id="delete-{{$item->id}}"
                                                                  class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $bannerTableColspan,
                                            'variant' => $search || $resourceType != 'all' ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $banners->links() !!}
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
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
    <script>
        "use Strict";

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{ route('admin.banner.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert(route, '{{ translate('want_to_update_status') }}');
        })

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '{{ translate('want_to_delete_this') }}');
        })

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
            var $form = $('#banner-create-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('select').each(function () {
                        var $s = $(this);
                        $s.find('option').each(function () { this.selected = this.defaultSelected; });
                        $s.trigger('change.select2');
                    });
                    $form.find('.global-image-upload').each(function () {
                        var $c = $(this);
                        $c.removeClass('has-image');
                        $c.find('.global-image-preview').attr('src', '').addClass('d-none');
                        $c.find('.global-upload-box').removeClass('global-upload-box-hidden');
                        $c.find('.overlay-icons').addClass('d-none');
                        $c.find('input[type="file"]').val('');
                    });
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
                FormValidator.register('#banner-create-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '{{ translate("Submitting...") }}'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
@endpush
