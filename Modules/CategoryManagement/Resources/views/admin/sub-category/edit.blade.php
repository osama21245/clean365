@extends('adminmodule::layouts.master')

@section('title',translate('Sub Category Update'))

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
                        <h2 class="page-title">{{translate('Sub Category Update')}}</h2>
                    </div>

                    <div class="card category-setup mb-30">
                        <div class="card-body p-30">
                            @can('category_update')
                            <form id="sub-category-edit-form" action="{{route('admin.sub-category.update',[$subCategory->id])}}" method="post"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                @csrf
                                @method('put')
                                @php
                                    $language = \Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first();
                                    $default_lang = str_replace('_', '-', app()->getLocale());
                                    $subCategoryNameTranslations = collect($subCategory['translations'] ?? [])
                                        ->where('key', 'name')
                                        ->pluck('value', 'locale')
                                        ->toArray();
                                    $subCategoryDescTranslations = collect($subCategory['translations'] ?? [])
                                        ->where('key', 'description')
                                        ->pluck('value', 'locale')
                                        ->toArray();
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
                                                        <option value="0" selected disabled>
                                                            {{translate('Select Category Name')}}
                                                        </option>
                                                        @foreach($mainCategories as $item)
                                                            <option
                                                                value="{{$item['id']}}" {{$subCategory->parent_id==$item->id?'selected':''}}>
                                                                {{$item->name}}
                                                            </option>
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
                                                        'value'       => old('name.0', $subCategory?->getRawOriginal('name'))])

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
                                                        'value'       => old('short_description.0', $subCategory?->getRawOriginal('description'))])
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
                                                            'value'       => old('name.' . ($index + 1), $subCategoryNameTranslations[$lang['code']] ?? '')])

                                                        @include('partials._form-field', [
                                                            'type'        => 'textarea',
                                                            'name'        => 'short_description[]',
                                                            'id'          => $lang['code'].'_short_description',
                                                            'label'       => translate('Short Description (:code)', ['code' => strtoupper($lang['code'])]),
                                                            'placeholder' => translate('Enter Short Description'),
                                                            'maxlength'   => 255,
                                                            'charCount'   => true,
                                                            'rows'        => 3,
                                                            'value'       => old('short_description.' . ($index + 1), $subCategoryDescTranslations[$lang['code']] ?? '')])
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
                                                        'value'       => old('name.0', $subCategory->name)])

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
                                                        'value'       => old('short_description.0', $subCategory->description)])
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
                                                    'subtitle' => translate('Update sub category image'),
                                                    'required' => false,
                                                    'image' => $subCategory->image_full_path,
                                                    'instructionRatio' => '1:1',
                                                    'showView' => true,
                                                    'showEdit' => true,
                                                    'showDelete' => false])
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                            <button class="btn btn--secondary"
                                                    type="reset">{{translate('Reset')}}</button>
                                            <button class="btn btn--primary demo_check" type="submit">{{translate('Update')}}</button>
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
    <script src="{{asset('public/assets/admin-module/plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('public/assets/category-module/js/sub-category/edit.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>

    <script>
        "use strict"

        $(document).ready(function () {
            var $form = $('#sub-category-edit-form');
            if (!$form.length) return;

            let originalParentId = $('#category_selector').val();
            let originalName = {};
            let originalDescription = {};
            $form.find('input[name="name[]"]').each(function () { originalName[this.id] = $(this).val(); });
            $form.find('textarea[name="short_description[]"]').each(function () { originalDescription[this.id] = $(this).val(); });

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('input[name="name[]"]').each(function () {
                        $(this).val(originalName[this.id] || '');
                    });
                    $form.find('textarea[name="short_description[]"]').each(function () {
                        $(this).val(originalDescription[this.id] || '');
                    });
                    $('#category_selector').val(originalParentId).trigger('change');

                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages label.error, .ff-field__messages .error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });
        });
    </script>
@endpush
