@extends('adminmodule::layouts.master')

@section('title',translate('Add New Coupon'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/daterangepicker.css')}}"/>
@endpush

@section('content')
    @php($language = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
    @php($default_lang = str_replace('_', '-', app()->getLocale()))
    @php($oldDiscountType = old('discount_type', 'category'))
    @php($oldDiscountAmountType = old('discount_amount_type', 'percent'))
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Add New Coupon')}}</h2>
                    </div>
                    <div class="card">
                        <div class="card-body p-30">
                            @can('coupon_add')
                            <form id="coupon-create-form"
                                  action="{{route('admin.coupon.store')}}"
                                  method="POST"
                                  data-ff-validate novalidate>
                                @csrf
                                @if($language)
                                    <ul class="nav nav--tabs border-color-primary mb-3">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active" href="#" id="default-link">{{translate('Default')}}</a>
                                        </li>
                                        @foreach ($language?->live_values as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link" href="#" id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="bg-light rounded p-xxl-4 p-3">
                                            <h4 class="mb-3">{{translate('Coupon Details')}}</h4>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'coupon_type',
                                                        'id'          => 'coupon-type',
                                                        'label'       => translate('Coupon Type'),
                                                        'required'    => true,
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'optionNull'  => translate('Select Coupon Type'),
                                                        'options'     => collect(COUPON_TYPES)->mapWithKeys(fn($v, $k) => [$k => $v])->toArray(),
                                                        'value'       => old('coupon_type'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'coupon_code',
                                                        'id'          => 'coupon_code',
                                                        'label'       => translate('Coupon Code'),
                                                        'placeholder' => translate('Enter Coupon Code'),
                                                        'icon'        => 'subtitles',
                                                        'required'    => true,
                                                        'maxlength'   => 50,
                                                        'charCount'   => true,
                                                        'value'       => old('coupon_code'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12 d-none" id="customer-select__div">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'customer_user_ids[]',
                                                        'id'          => 'customer-select',
                                                        'label'       => translate('Customers'),
                                                        'multiple'    => true,
                                                        'selectClass' => 'js-select theme-input-style w-100',
                                                        'options'     => $customers->mapWithKeys(function ($c) {
                                                            return [$c->id => $c->first_name.' '.$c->last_name];
                                                        })->toArray(),
                                                        'value'       => old('customer_user_ids', []),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-2 fw-medium">{{translate('Discount Type')}} <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="category" name="discount_type" value="category" {{ $oldDiscountType === 'category' ? 'checked' : '' }}>
                                                            <label for="category">{{translate('Category Wise')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="service" name="discount_type" value="service" {{ $oldDiscountType === 'service' ? 'checked' : '' }}>
                                                            <label for="service">{{translate('Service Wise')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="mixed" name="discount_type" value="mixed" {{ $oldDiscountType === 'mixed' ? 'checked' : '' }}>
                                                            <label for="mixed">{{translate('Mixed')}}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($language)
                                                    <div class="col-12 lang-form" id="default-form">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'discount_title[]',
                                                            'id'          => 'discount_title_default',
                                                            'label'       => translate('Discount Title (:label)', ['label' => translate('Default')]),
                                                            'placeholder' => translate('Enter Discount Title'),
                                                            'icon'        => 'title',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('discount_title.0'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    @foreach ($language?->live_values as $i => $lang)
                                                        <div class="col-12 d-none lang-form" id="{{$lang['code']}}-form">
                                                            @include('partials._form-field', [
                                                                'type'        => 'text',
                                                                'name'        => 'discount_title[]',
                                                                'id'          => 'discount_title_'.$lang['code'],
                                                                'label'       => translate('Discount Title (:code)', ['code' => strtoupper($lang['code'])]),
                                                                'placeholder' => translate('Enter Discount Title'),
                                                                'icon'        => 'title',
                                                                'maxlength'   => 191,
                                                                'charCount'   => true,
                                                                'value'       => old('discount_title.'.($i + 1)),
                                                                'wrapClass'   => 'mb-0'])
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                    @endforeach
                                                @else
                                                    <div class="col-12">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'discount_title[]',
                                                            'id'          => 'discount_title_default',
                                                            'label'       => translate('Discount Title'),
                                                            'placeholder' => translate('Enter Discount Title'),
                                                            'icon'        => 'title',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('discount_title.0'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif

                                                <div class="col-lg-6" id="category_selector">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'category_ids[]',
                                                        'id'          => 'category_selector__select',
                                                        'label'       => translate('Categories'),
                                                        'required'    => in_array($oldDiscountType, ['category', 'mixed']),
                                                        'multiple'    => true,
                                                        'selectClass' => 'category-select theme-input-style w-100',
                                                        'prependOptions' => [
                                                            ['value' => 'all', 'label' => translate('Select All')]],
                                                        'options'     => $categories->pluck('name', 'id')->toArray(),
                                                        'value'       => old('category_ids', []),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6 service_selector" id="service_selector">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'service_ids[]',
                                                        'id'          => 'service_selector__select',
                                                        'label'       => translate('Services'),
                                                        'multiple'    => true,
                                                        'selectClass' => 'service-select theme-input-style w-100',
                                                        'prependOptions' => [
                                                            ['value' => 'all', 'label' => translate('Select All')]],
                                                        'options'     => $services->pluck('name', 'id')->toArray(),
                                                        'value'       => old('service_ids', []),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'zone_ids[]',
                                                        'id'          => 'zone_selector__select',
                                                        'label'       => translate('Zones'),
                                                        'required'    => true,
                                                        'multiple'    => true,
                                                        'selectClass' => 'zone-select theme-input-style w-100',
                                                        'prependOptions' => [
                                                            ['value' => 'all', 'label' => translate('Select All')]],
                                                        'options'     => $zones->pluck('name', 'id')->toArray(),
                                                        'value'       => old('zone_ids', []),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="bg-light rounded p-xxl-4 p-3">
                                            <h4 class="mb-3">{{translate('Amount & Duration')}}</h4>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <div class="mb-2 fw-medium">{{translate('Discount Amount Type')}} <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                                        <div class="custom-radio">
                                                            <input type="radio" id="percentage" name="discount_amount_type" value="percent" {{ $oldDiscountAmountType === 'percent' ? 'checked' : '' }}>
                                                            <label for="percentage">{{translate('Percentage')}}</label>
                                                        </div>
                                                        <div class="custom-radio">
                                                            <input type="radio" id="fixed_amount" name="discount_amount_type" value="amount" {{ $oldDiscountAmountType === 'amount' ? 'checked' : '' }}>
                                                            <label for="fixed_amount">{{translate('Fixed Amount')}}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="ff-field ff-field--number ff-field--has-icon mb-0" data-ff-field>
                                                        <label class="ff-field__label" for="discount_amount">
                                                            <span class="ff-field__label-text" id="discount_amount__label">{{translate('Amount')}} ({{ $oldDiscountAmountType === 'percent' ? '%' : currency_symbol() }})<span class="ff-field__required">*</span></span>
                                                        </label>
                                                        <div class="ff-field__control">
                                                            <span class="material-icons ff-field__icon">price_change</span>
                                                            <input type="number" class="form-control ff-field__input" id="discount_amount"
                                                                name="discount_amount" placeholder="{{translate('Enter Amount')}}"
                                                                min="0.01" {{ $oldDiscountAmountType === 'percent' ? 'max=100' : '' }} step="any"
                                                                value="{{ old('discount_amount', 0) }}" required>
                                                        </div>
                                                        <div class="ff-field__footer">
                                                            <div class="ff-field__messages"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    @include('partials._form-field', [
                                                        'type'        => 'number',
                                                        'name'        => 'min_purchase',
                                                        'id'          => 'min_purchase',
                                                        'label'       => translate('Min Purchase').' ('.currency_symbol().')',
                                                        'placeholder' => translate('Enter Min Purchase'),
                                                        'icon'        => 'price_change',
                                                        'required'    => true,
                                                        'min'         => 0,
                                                        'step'        => 'any',
                                                        'value'       => old('min_purchase', 0),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-4" id="max_discount_amount">
                                                    @include('partials._form-field', [
                                                        'type'        => 'number',
                                                        'name'        => 'max_discount_amount',
                                                        'id'          => 'max_discount_amount_input',
                                                        'label'       => translate('Max Discount').' ('.currency_symbol().')',
                                                        'placeholder' => translate('Enter Max Discount'),
                                                        'icon'        => 'price_change',
                                                        'min'         => 0,
                                                        'step'        => 'any',
                                                        'value'       => old('max_discount_amount', 0),
                                                        'wrapClass'   => 'mb-0',
                                                        'required' => true
                                                    ])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'start_date',
                                                        'id'          => 'start_date',
                                                        'label'       => translate('Start Date'),
                                                        'icon'        => 'calendar_month',
                                                        'required'    => true,
                                                        'value'       => old('start_date', now()->format('Y-m-d')),
                                                        'extraAttrs'  => 'data-ff-datepicker readonly autocomplete="off" inputmode="none"',
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'end_date',
                                                        'id'          => 'end_date',
                                                        'label'       => translate('End Date'),
                                                        'icon'        => 'calendar_month',
                                                        'required'    => true,
                                                        'value'       => old('end_date', now()->addDays(2)->format('Y-m-d')),
                                                        'extraAttrs'  => 'data-ff-datepicker readonly autocomplete="off" inputmode="none"',
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6" id="limit_per_user__div">
                                                    @include('partials._form-field', [
                                                        'type'        => 'number',
                                                        'name'        => 'limit_per_user',
                                                        'id'          => 'limit_per_user',
                                                        'label'       => translate('Limit For Same User'),
                                                        'placeholder' => translate('Enter Limit'),
                                                        'icon'        => 'person',
                                                        'required'    => true,
                                                        'min'         => 1,
                                                        'value'       => old('limit_per_user', 1),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
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
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module/js/moment.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/js/daterangepicker.min.js')}}"></script>
    <script>
        "use strict";

        function initSingleDatePicker(selector) {
            const $input = $(selector);

            $input.daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            if ($input.val()) {
                const selectedDate = moment($input.val(), 'YYYY-MM-DD', true);

                if (selectedDate.isValid()) {
                    $input.data('daterangepicker').setStartDate(selectedDate);
                    $input.data('daterangepicker').setEndDate(selectedDate);
                    $input.val(selectedDate.format('YYYY-MM-DD'));
                }
            }

            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
        }

        $('#category_selector__select, #service_selector__select, #zone_selector__select, #customer-select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        function applyDiscountType(type) {
            if (type === 'category') {
                $('#category_selector').show();
                $('#service_selector').hide();
                $('#category_selector__select').prop('required', true);
                $('#service_selector__select').prop('required', false);
            } else if (type === 'service') {
                $('#category_selector').hide();
                $('#service_selector').show();
                $('#service_selector__select').prop('required', true);
                $('#category_selector__select').prop('required', false);
            } else {
                $('#category_selector').show();
                $('#service_selector').show();
                $('#service_selector__select').prop('required', true);
                $('#category_selector__select').prop('required', true);
            }
        }

        $('input[name="discount_type"]').on('change click', function () {
            applyDiscountType($(this).val());
        });

        function applyAmountType(type) {
            if (type === 'percent') {
                $('#max_discount_amount').show();
                $('[name="max_discount_amount"]').attr('required', true);
                $('#discount_amount').attr({ 'max': 100 });
                $('#discount_amount__label').html('{{translate("Amount")}} (%)<span class="ff-field__required">*</span>');
            } else {
                $('#max_discount_amount').hide();
                $('[name="max_discount_amount"]').removeAttr('required');
                $('#discount_amount').removeAttr('max');
                $('#discount_amount__label').html('{{translate("Amount")}} ({{currency_symbol()}})<span class="ff-field__required">*</span>');
            }
        }

        $('input[name="discount_amount_type"]').on('change click', function () {
            applyAmountType($(this).val());
        });

        $('#coupon-type').on('change', function () {
            if ($(this).val() === 'customer_wise') {
                $('#customer-select__div').removeClass('d-none');
                $('#customer-select').prop('required', true);
            } else {
                $('#customer-select__div').addClass('d-none');
                $('#customer-select').prop('required', false);
            }

            if ($(this).val() === 'first_booking') {
                $('#limit_per_user__div').addClass('d-none');
                $('#limit_per_user').prop('required', false);
            } else {
                $('#limit_per_user__div').removeClass('d-none');
                $('#limit_per_user').prop('required', true);
            }
        });

        $(document).ready(function () {
            $('.category-select').select2({ placeholder: "{{translate('Select Category')}}", width: '100%' });
            $('.service-select').select2({ placeholder: "{{translate('Select Service')}}", width: '100%' });
            $('.zone-select').select2({ placeholder: "{{translate('Select Zone')}}", width: '100%' });
            $('#customer-select').select2({ placeholder: "{{translate('Select Customer')}}", width: '100%' });

            applyDiscountType($('input[name="discount_type"]:checked').val() || 'category');
            applyAmountType($('input[name="discount_amount_type"]:checked').val() || 'percent');

            initSingleDatePicker('#start_date');
            initSingleDatePicker('#end_date');

            $('#coupon-create-form').on('submit', function (e) {
                let startDate = $('#start_date').val();
                let endDate = $('#end_date').val();

                if (startDate === '' || endDate === '') {
                    toastr.error('{{ translate('please select start and end date') }}');
                    e.preventDefault();
                    return;
                }

                let start = moment(startDate, 'YYYY-MM-DD', true);
                let end = moment(endDate, 'YYYY-MM-DD', true);

                if (!start.isValid() || !end.isValid() || start.isAfter(end)) {
                    toastr.error('{{ translate('Start date must be earlier than End date') }}');
                    e.preventDefault();
                }
            });
        });

        $('.lang_link').on('click', function (e) {
            e.preventDefault();
            $('.lang_link').removeClass('active');
            $('.lang-form').addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $('#' + lang + '-form').removeClass('d-none');
        });

        (function () {
            var $form = $('#coupon-create-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    $form.find('select').each(function () {
                        var $s = $(this);
                        $s.find('option').each(function () { this.selected = this.defaultSelected; });
                        $s.trigger('change.select2');
                    });
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () {
                            window.FormCharCount.update(this);
                        });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }

                    $('.lang_link').removeClass('active');
                    $('#default-link').addClass('active');
                    $('.lang-form').addClass('d-none');
                    $('#default-form').removeClass('d-none');

                    applyDiscountType($('input[name="discount_type"]:checked').val() || 'category');
                    applyAmountType($('input[name="discount_amount_type"]:checked').val() || 'percent');
                    $('#customer-select__div').addClass('d-none');
                    $('#limit_per_user__div').removeClass('d-none');
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#coupon-create-form', {
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
