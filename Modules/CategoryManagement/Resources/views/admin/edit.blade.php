@extends('adminmodule::layouts.master')

@section('title', translate('Category Update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css" />
    <style>
        input.icon-radio {
            display: none !important;
            position: absolute !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            visibility: hidden !important;
        }

        .icon-radio:checked + label {
            border-color: #00A79D !important;
            background-color: #E6F7F5 !important;
            box-shadow: 0 0 0 2px rgba(0, 167, 157, 0.25);
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Category Update')}}</h2>
                    </div>

                    <div class="card category-setup mb-30">
                        <div class="card-body p-30">
                            @can('category_update')
                                <form id="category-edit-form" action="{{route('admin.category.update', [$category->id])}}"
                                    method="post" enctype="multipart/form-data" data-ff-validate novalidate>
                                    @csrf
                                    @method('put')
                                    @php
                                        $language = \Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first();
                                        $default_lang = str_replace('_', '-', app()->getLocale());
                                        $categoryNameTranslations = collect($category['translations'] ?? [])
                                            ->where('key', 'name')
                                            ->pluck('value', 'locale')
                                            ->toArray();
                                    @endphp
                                    @if($language)
                                        <ul class="nav nav--tabs border-color-primary mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{translate('Default')}}</a>
                                            </li>
                                            @foreach ($language?->live_values as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="row g-4 align-items-stretch">
                                        <div class="col-xl-7 col-lg-8">
                                            <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <h4 class="mb-3">{{translate('Category Details')}}</h4>
                                                @if ($language)
                                                    <div class="lang-form" id="default-form">
                                                        @include('partials._form-field', [
                                                            'type' => 'text',
                                                            'name' => 'name[]',
                                                            'id' => 'default_category_name',
                                                            'label' => translate('Category Name (:label)', ['label' => translate('Default')]),
                                                            'placeholder' => translate('Enter Category Name'),
                                                            'icon' => 'subtitles',
                                                            'required' => true,
                                                            'maxlength' => 191,
                                                            'charCount' => true,
                                                            'value' => old('name.0', $category?->getRawOriginal('name'))
                                                        ])
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    @foreach ($language?->live_values as $index => $lang)
                                                        <div class="lang-form d-none" id="{{$lang['code']}}-form">
                                                            @include('partials._form-field', [
                                                                'type' => 'text',
                                                                'name' => 'name[]',
                                                                'id' => $lang['code'] . '_category_name',
                                                                'label' => translate('Category Name (:code)', ['code' => strtoupper($lang['code'])]),
                                                                'placeholder' => translate('Enter Category Name'),
                                                                'icon' => 'subtitles',
                                                                'maxlength' => 191,
                                                                'charCount' => true,
                                                                'value' => old('name.' . ($index + 1), $categoryNameTranslations[$lang['code']] ?? '')
                                                            ])
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                    @endforeach
                                                @else
                                                    <div class="lang-form">
                                                        @include('partials._form-field', [
                                                            'type' => 'text',
                                                            'name' => 'name[]',
                                                            'id' => 'default_category_name',
                                                            'label' => translate('Category Name'),
                                                            'placeholder' => translate('Enter Category Name'),
                                                            'icon' => 'subtitles',
                                                            'required' => true,
                                                            'maxlength' => 191,
                                                            'charCount' => true,
                                                            'value' => old('name.0', $category['name'])
                                                        ])
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif

                                                <div class="ff-field" data-ff-field>
                                                    <label class="ff-field__label" for="zone_selector__select">
                                                        <span class="ff-field__label-text">{{translate('Zones')}}<span
                                                                class="ff-field__required">*</span></span>
                                                    </label>
                                                    <div class="ff-field__control">
                                                        <select
                                                            class="form-control ff-field__input ff-field__select zone-select w-100"
                                                            name="zone_ids[]" multiple="multiple" id="zone_selector__select"
                                                            required>
                                                            <option value="all">{{translate('Select All')}}</option>
                                                            @foreach($zones as $zone)
                                                                <option value="{{$zone['id']}}"
                                                                    {{in_array($zone->id, $category->zones->pluck('id')->toArray()) ? 'selected' : ''}}>
                                                                    {{$zone->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="ff-field__footer">
                                                        <div class="ff-field__messages"></div>
                                                    </div>
                                                </div>

                                                @include('partials._form-field', [
                                                    'type' => 'textarea',
                                                    'name' => 'description',
                                                    'id' => 'category_description',
                                                    'label' => translate('Short Description'),
                                                    'placeholder' => translate('Shown under the category title on the card'),
                                                    'rows' => 3,
                                                    'maxlength' => 2000,
                                                    'value' => old('description', $category->getRawOriginal('description') ?? $category->description)
                                                ])

                                                @include('partials._form-field', [
                                                    'type' => 'number',
                                                    'name' => 'starting_price',
                                                    'id' => 'category_starting_price',
                                                    'label' => translate('Starting Price'),
                                                    'placeholder' => translate('Starts from'),
                                                    'step' => '0.01',
                                                    'min' => '0',
                                                    'value' => old('starting_price', $category->starting_price)
                                                ])

                                                @php
                                                    $existingIncludes = old('include_title')
                                                        ? collect(old('include_title'))->map(fn($t, $i) => ['title' => $t, 'icon' => old('include_icon.' . $i)])->all()
                                                        : ($category->includes ?? []);
                                                    if (empty($existingIncludes)) {
                                                        $existingIncludes = [['title' => '', 'icon' => null]];
                                                    }
                                                @endphp
                                                <div class="mb-3">
                                                    <label
                                                        class="fw-medium mb-2 d-block">{{ translate('Service Includes / Features') }}</label>
                                                    <p class="text-muted fs-12 mb-2">
                                                        {{ translate('Feature titles and optional icon shown on category card') }}</p>
                                                    <div id="category-includes-wrapper">
                                                        @foreach($existingIncludes as $idx => $include)
                                                            <div class="category-include-item border rounded p-3 mb-3 bg-white shadow-2xs">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <input type="text" name="include_title[]" class="form-control"
                                                                        placeholder="{{ translate('Example: Deep Cleaning') }}"
                                                                        value="{{ $include['title'] ?? '' }}">
                                                                    @if($idx > 0)
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-outline-danger ms-2 remove-include">×</button>
                                                                    @endif
                                                                </div>
                                                                <div class="mt-2">
                                                                    <label class="fs-12 text-muted mb-1 d-block">{{ translate('Feature Icon (Optional)') }}</label>
                                                                    <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light" style="max-height: 100px; overflow-y: auto;">
                                                                        @foreach(($icons ?? []) as $key => $icon)
                                                                            <div>
                                                                                <input type="radio" id="include_icon_{{ $idx }}_{{ $key }}" name="include_icon[{{ $idx }}]" value="{{ $icon }}" class="d-none icon-radio" {{ (($include['icon'] ?? null) === $icon) ? 'checked' : '' }}>
                                                                                <label for="include_icon_{{ $idx }}_{{ $key }}" class="border rounded p-1 d-flex align-items-center justify-content-center bg-white" style="cursor:pointer; width: 36px; height: 36px;">
                                                                                    <img src="{{ asset('assets/images/'.$icon) }}" width="24" height="24" alt="{{ $icon }}" title="{{ $icon }}">
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-success mt-1"
                                                        id="add-category-include">+ {{ translate('Add Feature') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-5 col-lg-4">
                                            <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <div class="mx-auto" style="max-width: 260px;">
                                                    @include('adminmodule::admin.partials._single-image-upload', [
                                                        'name' => 'image',
                                                        'id' => 'categoryImage',
                                                        'title' => translate('Image'),
                                                        'subtitle' => translate('Update category image'),
                                                        'required' => false,
                                                        'image' => $category->image_full_path,
                                                        'instructionRatio' => '1:1',
                                                        'showView' => true,
                                                        'showEdit' => true,
                                                        'showDelete' => false
                                                    ])
                                                    @can('category_update')
                                                        <form method="POST" action="{{ route('admin.category.regenerate-ai-image', [$category->id]) }}" class="mt-3"
                                                              onsubmit="return confirm('{{ translate('Replace category image with a new AI image? No text will be written on the image.') }}');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                                <span class="material-icons">auto_awesome</span>
                                                                {{ translate('Generate AI image') }}
                                                            </button>
                                                            <small class="text-muted d-block mt-2">
                                                                {{ translate('Uses Gemini. Photoreal category scene with Clean365 brand colors. No text overlays — describes the category visually.') }}
                                                            </small>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                                <button class="btn btn--secondary" type="reset">{{translate('Reset')}}</button>
                                                <button class="btn btn--primary demo_check"
                                                    type="submit">{{translate('Update')}}</button>
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
    <script src="{{asset('public/assets/category-module/js/category/edit.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>

    <script>
        "use strict"

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $(document).ready(function () {
            var $form = $('#category-edit-form');
            if (!$form.length) return;

            let originalSelection = $('#zone_selector__select').val();
            let originalName = {};
            $form.find('input[name="name[]"]').each(function () {
                originalName[this.id] = $(this).val();
            });

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('input[name="name[]"]').each(function () {
                        $(this).val(originalName[this.id] || '');
                    });
                    $('#zone_selector__select').val(originalSelection).trigger('change');

                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages label.error, .ff-field__messages .error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });
        });

        (function () {
            let includeIndex = {{ count($existingIncludes ?? [['title' => '']]) }};
            const wrapper = document.getElementById('category-includes-wrapper');
            const addBtn = document.getElementById('add-category-include');
            if (!wrapper || !addBtn) return;

            const iconsHtml = `@foreach(($icons ?? []) as $key => $icon)
                <div>
                    <input type="radio" id="include_icon___INDEX___{{ $key }}" name="include_icon[__INDEX__]" value="{{ $icon }}" class="d-none icon-radio">
                    <label for="include_icon___INDEX___{{ $key }}" class="border rounded p-1 d-flex align-items-center justify-content-center bg-white" style="cursor:pointer; width: 36px; height: 36px;">
                        <img src="{{ asset('assets/images/'.$icon) }}" width="24" height="24" alt="{{ $icon }}" title="{{ $icon }}">
                    </label>
                </div>
            @endforeach`;

            addBtn.addEventListener('click', function () {
                const block = document.createElement('div');
                block.className = 'category-include-item border rounded p-3 mb-3 bg-white shadow-2xs';
                block.innerHTML =
                    '<div class="d-flex justify-content-between align-items-center mb-2">' +
                    '<input type="text" name="include_title[]" class="form-control me-2" placeholder="{{ translate('Example: Deep Cleaning') }}">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-include">×</button>' +
                    '</div><div class="mt-2"><label class="fs-12 text-muted mb-1 d-block">{{ translate('Feature Icon (Optional)') }}</label>' +
                    '<div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light" style="max-height: 100px; overflow-y: auto;">' +
                    iconsHtml.replaceAll('__INDEX__', String(includeIndex)) + '</div></div>';
                wrapper.appendChild(block);
                includeIndex += 1;
            });

            wrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-include')) {
                    e.target.closest('.category-include-item')?.remove();
                }
            });
        })();

    </script>
@endpush