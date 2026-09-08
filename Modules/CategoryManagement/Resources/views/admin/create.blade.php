@extends('adminmodule::layouts.master')

@section('title', translate('Category Setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/select2/select2.min.css')}}" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}" />
    <style>
        @media (max-width: 767.98px) {
            .category-search-form {
                width: 100% !important;
            }
        }

        input.icon-radio {
            display: none !important;
            position: absolute !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            visibility: hidden !important;
        }

        .icon-radio:checked+label {
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
                    <h2 class="page-title">{{translate('Category Setup')}}</h2>
                </div>

                @can('category_add')
                <div class="card category-setup mb-30">
                    <div class="card-body p-30">
                        <form id="category-create-form" action="{{route('admin.category.store')}}" method="post"
                            enctype="multipart/form-data" data-ff-validate novalidate>
                            @csrf
                            @php($language = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first())
                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
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
                                                    'value' => old('name.0')
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
                                                        'value' => old('name.' . ($index + 1))
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
                                                    'value' => old('name.0')
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
                                                    class="form-control ff-field__input ff-field__select select-zone w-100"
                                                    name="zone_ids[]" multiple="multiple" id="zone_selector__select"
                                                    required>
                                                    <option value="all" {{ (old('zone_ids') && in_array('all', old('zone_ids'))) ? 'selected' : '' }}>
                                                        {{translate('Select All')}}
                                                    </option>
                                                    @foreach($zones as $zone)
                                                        <option value="{{$zone['id']}}" {{ (old('zone_ids') && in_array($zone['id'], old('zone_ids'))) ? 'selected' : '' }}>
                                                            {{$zone->name}}
                                                        </option>
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
                                            'value' => old('description')
                                        ])

                                        @include('partials._form-field', [
                                            'type' => 'number',
                                            'name' => 'starting_price',
                                            'id' => 'category_starting_price',
                                            'label' => translate('Starting Price'),
                                            'placeholder' => translate('Starts from'),
                                            'step' => '0.01',
                                            'min' => '0',
                                            'value' => old('starting_price')
                                        ])

                                        <div class="mb-3">
                                            <label
                                                class="fw-medium mb-2 d-block">{{ translate('Service Includes / Features') }}</label>
                                            <p class="text-muted fs-12 mb-2">
                                                {{ translate('Feature titles and optional icon shown on category card') }}
                                            </p>
                                            <div id="category-includes-wrapper">
                                                <div
                                                    class="category-include-item border rounded p-3 mb-3 bg-white shadow-2xs">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <input type="text" name="include_title[]" class="form-control"
                                                            placeholder="{{ translate('Example: Deep Cleaning') }}"
                                                            value="">
                                                    </div>
                                                    <div class="mt-2">
                                                        <label
                                                            class="fs-12 text-muted mb-1 d-block">{{ translate('Feature Icon (Optional)') }}</label>
                                                        @if(count($icons ?? []) > 0)
                                                            <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light"
                                                                style="max-height: 100px; overflow-y: auto;">
                                                                @foreach(($icons ?? []) as $key => $icon)
                                                                    <div>
                                                                        <input type="radio" id="include_icon_0_{{ $key }}"
                                                                            name="include_icon[0]" value="{{ $icon }}"
                                                                            class="d-none icon-radio">
                                                                        <label for="include_icon_0_{{ $key }}"
                                                                            class="border rounded p-1 d-flex align-items-center justify-content-center bg-white"
                                                                            style="cursor:pointer; width: 36px; height: 36px;">
                                                                            <img src="{{ asset('assets/images/' . $icon) }}"
                                                                                width="24" height="24" alt="{{ $icon }}"
                                                                                title="{{ $icon }}">
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="p-2 border rounded bg-light text-muted fs-12">
                                                                {{ translate('Upload icon files to public/assets/images/ on the server to select icons here.') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
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
                                                'subtitle' => translate('Upload category image'),
                                                'required' => true,
                                                'image' => null,
                                                'instructionRatio' => '1:1'
                                            ])
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                        <button class="btn btn--secondary" type="reset">{{translate('Reset')}}</button>
                                        <button class="btn btn--primary demo_check"
                                            type="submit">{{translate('Submit')}}</button>
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
                            <a class="nav-link {{$status == 'all' ? 'active' : ''}}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}">
                                {{translate('All')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$status == 'active' ? 'active' : ''}}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}">
                                {{translate('Active')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$status == 'inactive' ? 'active' : ''}}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}">
                                {{translate('Inactive')}}
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex gap-2 fw-medium">
                        <span class="opacity-75">{{translate('Total Categories')}}:</span>
                        <span class="title-color" id="totalListCount">{{$categories->total()}}</span>
                    </div>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-tab-pane">
                        <div class="card">
                            <div class="card-body">
                                <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                    <form action="{{ url()->current() }}"
                                        class="search-form search-form_style-two category-search-form" method="GET">
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                value="{{$search}}" name="search"
                                                placeholder="{{translate('Search Here')}}">
                                        </div>
                                        <button type="submit" class="btn btn--primary">{{translate('Search')}}</button>
                                    </form>

                                    @can('category_export')
                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div class="dropdown">
                                                <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                    <span class="material-icons">file_download</span>
                                                    {{translate('Download')}}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                    <li><a class="dropdown-item"
                                                            href="{{route('admin.category.download')}}?search={{$search}}&status={{$status}}">{{translate('Excel')}}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endcan
                                </div>

                                <div id="ListTableContainer">
                                    @include('categorymanagement::admin.partials._table')
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<input type="hidden" id="offset" value="{{ request()->page }}">
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/category-module')}}/js/category/create.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    <script>
        "use strict"

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
        });

        (function () {
            var $form = $('#category-create-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('input[type="text"], textarea').each(function () {
                        var $input = $(this);
                        $input.val($input[0].defaultValue || '');
                    });
                    $('#zone_selector__select option').prop('selected', false);
                    $('#zone_selector__select').trigger('change');

                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages label.error, .ff-field__messages .error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });
        })();

        let selectedItem;
        let selectedRoute;
        let initialState;
        let currentStatus = "{{ request('status', 'all') }}"; // Keep the current tab status

        $('.nav-link').on('click', function () {
            const urlParams = new URLSearchParams($(this).attr('href').split('?')[1]);
            currentStatus = urlParams.get('status') || 'all';
        });

        $(document).on('change', '.feature-update', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            selectedItem = $(this);
            initialState = selectedItem.prop('checked');
            selectedItem.prop('checked', !initialState);

            let itemId = selectedItem.data('featured');
            selectedRoute = '{{ route('admin.category.featured-update', ['id' => ':itemId']) }}'.replace(':itemId', itemId);

            let confirmationTitleText = initialState
                ? '{{ translate('Are you sure to turn on the featured category') }}?'
                : '{{ translate('Are you sure to turn off the featured category') }}?';

            let confirmationDescriptionText = initialState
                ? '{{ translate('If the feature is on, this category and its services will appear in featured sections on the website') }}.'
                : '{{ translate('If the feature is off, this category and its services will no longer appear in featured sections on the website') }}.';

            $('.confirmation-title-text').text(confirmationTitleText);
            $('.confirmation-description-text').text(confirmationDescriptionText);
            $('#confirmChangeModal img').attr('src', "{{ asset('public/assets/admin-module/img/svg/feature.svg') }}");

            showModal();
        });

        // Attach event listener for status change with event delegation
        $(document).on('change', '.status-update', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            selectedItem = $(this);
            initialState = selectedItem.prop('checked');

            // Revert checkbox visual state until confirmation
            selectedItem.prop('checked', !initialState);

            let itemId = selectedItem.data('id');
            selectedRoute = '{{ route('admin.category.status-update', ['id' => ':itemId']) }}'.replace(':itemId', itemId);

            let confirmationTitleText = initialState
                ? '{{ translate('Are you sure to Turn On the Category Status') }}?'
                : '{{ translate('Are you sure to Turn Off the Category Status') }}?';

            $('.confirmation-title-text').text(confirmationTitleText);

            let confirmationDescriptionText = initialState
                ? '{{ translate('Once you turn on the Category Status, the user can find the Category and its service for selection') }}.'
                : '{{ translate('Once you turn off the Category Status, the Provider can’t subscribe to the services of that category and the Customer can’t find the category & its service when they want to book') }}.';

            $('.confirmation-description-text').text(confirmationDescriptionText);

            let imgSrc = initialState
                ? "{{ asset('public/assets/admin-module/img/icons/status-on.png') }}"
                : "{{ asset('public/assets/admin-module/img/icons/status-off.png') }}";

            $('#confirmChangeModal img').attr('src', imgSrc);

            showModal();
        });

        $('#confirmChange').on('click', function () {
            updateStatus(selectedRoute);
        });

        //  Cancel and reset checkbox state
        $('.cancel-change').on('click', function () {
            resetCheckboxState();
            hideModal();
        });

        $('#confirmChangeModal').on('hidden.bs.modal', function () {
            resetCheckboxState();
        });

        //  Show/hide modal functions
        function showModal() {
            $('#confirmChangeModal').modal('show');
        }
        function hideModal() {
            $('#confirmChangeModal').modal('hide');
        }

        //  Reset the checkbox if change canceled
        function resetCheckboxState() {
            if (selectedItem) {
                selectedItem.prop('checked', !initialState);
            }
        }

        //  Submit the status change with AJAX
        function updateStatus(route) {
            let page = $('#offset').val();
            $.ajax({
                url: route,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function (data) {
                    toastr.success(data.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    reloadTable(currentStatus, page);
                    hideModal();
                },
                error: function (xhr) {
                    resetCheckboxState();
                    const errorMessage = xhr.responseJSON?.errors?.[0]?.message ?? xhr.responseJSON?.message ?? 'Something went wrong! Please try again.';
                    toastr.error(errorMessage, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        // Reload the table after status update
        function reloadTable(status, page) {
            let search = $('input[name="search"]').val();

            $.ajax({
                url: "{{ route('admin.category.table') }}",
                type: "GET",
                data: {
                    status: status,
                    search: search,
                    page: page
                },
                success: function (response) {
                    if (response.page != page) {
                        updateBrowserUrl(status, search, response.page);
                        $('#offset').val((response.page - 1) * {{ pagination_limit() }});
                    } else {
                        $('#offset').val(response.offset);
                        updateBrowserUrl(status, search, page);
                    }

                    $('#totalListCount').html(response.totalCategory)
                    $('#ListTableContainer').empty().html(response.view);
                },
                error: function () {
                    toastr.error('Failed to update table. Please reload the page.', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        // Update browser URL
        function updateBrowserUrl(status, search, page) {
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (status) params.set('status', status);
            if (page > 1) params.set('page', page);

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);
        }

        (function () {
            let includeIndex = 1;
            const wrapper = document.getElementById('category-includes-wrapper');
            const addBtn = document.getElementById('add-category-include');
            if (!wrapper || !addBtn) return;

            const iconsHtml = `@foreach(($icons ?? []) as $key => $icon)
                <div>
                    <input type="radio" id="include_icon___INDEX___{{ $key }}" name="include_icon[__INDEX__]" value="{{ $icon }}" class="d-none icon-radio">
                    <label for="include_icon___INDEX___{{ $key }}" class="border rounded p-1 d-flex align-items-center justify-content-center bg-white" style="cursor:pointer; width: 36px; height: 36px;">
                        <img src="{{ asset('assets/images/' . $icon) }}" width="24" height="24" alt="{{ $icon }}" title="{{ $icon }}">
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