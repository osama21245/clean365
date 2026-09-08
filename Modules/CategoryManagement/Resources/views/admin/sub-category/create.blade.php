@extends('adminmodule::layouts.master')

@section('title',translate('Sub Category Setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/select2/select2.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Sub Category Setup')}}</h2>
                    </div>

                    @can('category_add')
                        <div class="card category-setup mb-30">
                            <div class="card-body p-30">
                                <form id="sub-category-create-form" action="{{route('admin.sub-category.store')}}" method="post"
                                      enctype="multipart/form-data"
                                      data-ff-validate novalidate>
                                    @csrf
                                    @php
                                        $language = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first();
                                    @endphp
                                    @if($language)
                                        <ul class="nav nav--tabs border-color-primary mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                   href="#"
                                                   id="default-link">{{translate('Default')}}</a>
                                            </li>
                                            @foreach ($language?->live_values as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link"
                                                       href="#"
                                                       id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="row g-4 align-items-stretch">
                                        <div class="col-xl-7 col-lg-8">
                                            <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <h4 class="mb-3">{{translate('Sub Category Details')}}</h4>

                                                <div class="ff-field" data-ff-field>
                                                    <label class="ff-field__label" for="category_selector">
                                                        <span class="ff-field__label-text">{{translate('Parent Category')}}<span class="ff-field__required">*</span></span>
                                                    </label>
                                                    <div class="ff-field__control">
                                                        <select class="form-control ff-field__input ff-field__select js-select w-100" name="parent_id" id="category_selector" required>
                                                            <option value="" selected disabled>{{translate('Select Category Name')}}</option>
                                                            @foreach($mainCategories as $item)
                                                                <option value="{{$item['id']}}" {{ old('parent_id') == $item['id'] ? 'selected' : '' }}>{{$item->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="ff-field__footer">
                                                        <div class="ff-field__messages"></div>
                                                    </div>
                                                </div>

                                                @if($language)
                                                    <div class="lang-form" id="default-form">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'name[]',
                                                            'id'          => 'default_sub_category_name',
                                                            'label'       => translate('Sub Category Name (:label)', ['label' => translate('Default')]),
                                                            'placeholder' => translate('Enter Sub Category Name'),
                                                            'icon'        => 'subtitles',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('name.0')])

                                                        @include('partials._form-field', [
                                                            'type'        => 'textarea',
                                                            'name'        => 'short_description[]',
                                                            'id'          => 'default_short_description',
                                                            'label'       => translate('Short Description (:label)', ['label' => translate('Default')]),
                                                            'placeholder' => translate('Enter Short Description'),
                                                            'required'    => true,
                                                            'maxlength'   => 255,
                                                            'charCount'   => true,
                                                            'rows'        => 3,
                                                            'value'       => old('short_description.0')])
                                                    </div>

                                                    <input type="hidden" name="lang[]" value="default">
                                                    @foreach ($language?->live_values as $index => $lang)
                                                        <div class="lang-form d-none" id="{{ $lang['code'] }}-form">
                                                            @include('partials._form-field', [
                                                                'type'        => 'text',
                                                                'name'        => 'name[]',
                                                                'id'          => $lang['code'].'_sub_category_name',
                                                                'label'       => translate('Sub Category Name (:code)', ['code' => strtoupper($lang['code'])]),
                                                                'placeholder' => translate('Enter Sub Category Name'),
                                                                'icon'        => 'subtitles',
                                                                'maxlength'   => 191,
                                                                'charCount'   => true,
                                                                'value'       => old('name.' . ($index + 1))])

                                                            @include('partials._form-field', [
                                                                'type'        => 'textarea',
                                                                'name'        => 'short_description[]',
                                                                'id'          => $lang['code'].'_short_description',
                                                                'label'       => translate('Short Description (:code)', ['code' => strtoupper($lang['code'])]),
                                                                'placeholder' => translate('Enter Short Description'),
                                                                'maxlength'   => 255,
                                                                'charCount'   => true,
                                                                'rows'        => 3,
                                                                'value'       => old('short_description.' . ($index + 1))])
                                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="lang-form">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'name[]',
                                                            'id'          => 'default_sub_category_name',
                                                            'label'       => translate('Sub Category Name'),
                                                            'placeholder' => translate('Enter Sub Category Name'),
                                                            'icon'        => 'subtitles',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('name.0')])

                                                        @include('partials._form-field', [
                                                            'type'        => 'textarea',
                                                            'name'        => 'short_description[]',
                                                            'id'          => 'default_short_description',
                                                            'label'       => translate('Short Description'),
                                                            'placeholder' => translate('Enter Short Description'),
                                                            'required'    => true,
                                                            'maxlength'   => 255,
                                                            'charCount'   => true,
                                                            'rows'        => 3,
                                                            'value'       => old('short_description.0')])
                                                    </div>

                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-xl-5 col-lg-4">
                                            <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                                <div class="mx-auto w-100" style="max-width: 260px;">
                                                    @include('adminmodule::admin.partials._single-image-upload', [
                                                        'name' => 'image',
                                                        'id' => 'subCategoryImage',
                                                        'title' => translate('Image'),
                                                        'subtitle' => translate('Upload sub category image'),
                                                        'required' => true,
                                                        'image' => null,
                                                        'instructionRatio' => '1:1'])
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                                <button class="btn btn--secondary"
                                                        type="reset">{{translate('Reset')}}</button>
                                                <button class="btn btn--primary demo_check" type="submit">{{translate('Submit')}}</button>
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
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}">
                                    {{translate('All')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='active'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}">
                                    {{translate('Active')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='inactive'?'active':''}}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}">
                                    {{translate('Inactive')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total Sub Categories')}}:</span>
                            <span class="title-color">{{$subCategories->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="{{ url()->current() }}"
                                              class="search-form search-form_style-two"
                                              method="GET">
                                            <input type="hidden" name="status" value="{{ $status }}">
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$search}}" name="search"
                                                       placeholder="{{translate('Search Here')}}">
                                            </div>
                                            <button type="submit"
                                                    class="btn btn--primary">{{translate('Search')}}</button>
                                        </form>

                                        @can('category_export')
                                            <div class="d-flex flex-wrap align-items-center gap-3">
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                        <span class="material-icons">file_download</span> {{translate('Download')}}
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <li><a class="dropdown-item"
                                                               href="{{route('admin.sub-category.download')}}?search={{$search}}&status={{$status}}">{{translate('Excel')}}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>

                                    <div class="table-responsive">
                                        @php
                                            $subCategoryCreateTableColspan = 4;
                                        @endphp
                                        @can('category_manage_status')
                                            @php
                                                $subCategoryCreateTableColspan++;
                                            @endphp
                                        @endcan
                                        @canany(['category_delete', 'category_update'])
                                            @php
                                                $subCategoryCreateTableColspan++;
                                            @endphp
                                        @endcanany
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('name')}}</th>
                                                <th>{{translate('parent_category')}}</th>
                                                <th>{{translate('service_count')}}</th>
                                                @can('category_manage_status')
                                                    <th>{{translate('status')}}</th>
                                                @endcan
                                                @canany(['category_delete', 'category_update'])
                                                    <th>{{translate('action')}}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($subCategories as $key=>$category)
                                                <tr>
                                                    <td>{{$subCategories->firstitem()+$key}}</td>
                                                    <td>{{$category->name}}</td>
                                                    <td>{{$category->parent->name??translate('not_found')}}</td>
                                                    <td>{{$category->services_count}}</td>
                                                    @can('category_manage_status')
                                                        <td>
                                                            <label class="switcher" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input status-update"
                                                                       type="checkbox"
                                                                       id="sub-category-status-{{$category->id}}"
                                                                       {{$category->is_active?'checked':''}} data-status="{{$category->id}}">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['category_delete', 'category_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                @can('category_update')
                                                                    <a href="{{route('admin.sub-category.edit',[$category->id])}}"
                                                                       class="action-btn btn--light-primary demo_check"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                @endcan
                                                                @can('category_delete')
                                                                    <button type="button"
                                                                            class="action-btn btn--danger demo_check"
                                                                            data-delete="{{$category->id}}"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.sub-category.delete',[$category->id])}}"
                                                                        method="post" id="delete-{{$category->id}}"
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
                                                    'colspan' => $subCategoryCreateTableColspan,
                                                    'variant' => request()->filled('search') || $status != 'all' ? 'search' : 'list'])
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $subCategories->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module/plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('public/assets/category-module/js/sub-category/create.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>
    <script>
        "use strict"

        $('.status-update').on('click', function () {
            let $this = $(this);
            let itemId = $(this).data('status');
            let initialState = $this.prop('checked');
            let route = '{{route('admin.sub-category.status-update',['id' => ':itemId'])}}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}', true, initialState ? 1 : 0, 'sub-category-status-' + itemId);
        })

        $('.action-btn.btn--danger').on('click', function () {
            let itemId = $(this).data('delete');
            @if(env('APP_ENV')!='demo')
            form_alert('delete-' + itemId, '{{translate('want_to_delete_this')}}?')
            @endif
        })

        (function () {
            var $form = $('#sub-category-create-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('input[type="text"], textarea').each(function () {
                        var $input = $(this);
                        $input.val($input[0].defaultValue || '');
                    });
                    $('#category_selector').val('').trigger('change');

                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages label.error, .ff-field__messages .error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });
        })();
    </script>
@endpush
