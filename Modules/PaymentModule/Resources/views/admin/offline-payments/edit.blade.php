@extends('adminmodule::layouts.new-master')

@section('title', translate('Edit Offline Payment'))

@section('content')
    @php
        $paymentInformation = $withdrawalMethod['payment_information'] ?? [];
        $customerInformation = collect($withdrawalMethod['customer_information'] ?? [])
            ->filter(function ($item) { return ($item['field_name'] ?? null) !== 'payment_note'; })
            ->values()
            ->all();
    @endphp
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form id="offline-payment-update-form"
                          action="{{ route('admin.configuration.offline-payment.update') }}"
                          method="POST"
                          data-ff-validate novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" value="{{ $withdrawalMethod['id'] }}" name="id">

                        <div class="d-flex align-items-center justify-content-between flex-sm-nowrap flex-wrap gap-3 mb-20">
                            <div>
                                <h1 class="page-title mb-2">{{ translate('Payment Methods Setup') }}</h1>
                                <a href="{{ route('admin.configuration.third-party', ['webPage' => 'payment_config', 'type' => 'offline_payment'] ) }}" class="d-flex align-items-center gap-2 text-primary fz-14">
                                    <i class="material-symbols-outlined">arrow_back</i> {{ translate('Back to Offline Payment Methods') }}
                                </a>
                            </div>
                            <button type="button" class="rounded transition text-nowrap fz-12 fw-semibold btn-primary__outline btn d-flex align-items-center gap-1 py-2 px-3" data-bs-toggle="offcanvas" data-bs-target="#offline-offcanvas_preview">
                                <span class="material-symbols-outlined">visibility</span> {{ translate('Section View') }}
                            </button>
                        </div>
                        <div class="card mb-20">
                            <div class="card-body p-20">
                                <div class="d-flex align-items-center flex-wrap justify-content-between gap-2 mb-20">
                                    <div class="max-w-700">
                                        <h3 class="page-title mb-1">{{ translate('Payment Information') }}</h3>
                                        <p class="fz-12">
                                            {{ translate('Choose your preferred payment method such as bank, mobile wallet, digital cards, etc.') }}
                                            {{ translate('Customers will choose from these options and add the relevant input fields for the payment method.') }}
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn--primary rounded d-flex align-items-center gap-1" id="add-more-field-payment">
                                        <span class="absolute-white-bg rounded-full d-center text-primary w-14 h-14">+</span> {{ translate('Add New Field') }}
                                    </button>
                                </div>
                                <div class="row gy-3">
                                    <div class="col-sm-12">
                                        <div class="body-bg rounded p-20">
                                            @include('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'method_name',
                                                'id'          => 'method_name',
                                                'label'       => translate('Payment Method Name'),
                                                'placeholder' => translate('Enter Payment Method Name'),
                                                'hint'        => translate('Add payment method name.'),
                                                'required'    => true,
                                                'maxlength'   => 60,
                                                'charCount'   => true,
                                                'value'       => old('method_name', $withdrawalMethod['method_name'] ?? ''),
                                                'wrapClass'   => 'mb-0'])
                                        </div>
                                    </div>
                                    <div class="col-sm-12 d-flex flex-column gap-3">
                                        <div class="d-flex flex-column gap-3" id="custom-field-section-payment">
                                            @foreach($paymentInformation as $key => $field)
                                                @include('paymentmodule::admin.offline-payments.partials._payment-info-row', [
                                                    'rowIndex'   => $key,
                                                    'titleValue' => $field['title'] ? str_replace('_', ' ', $field['title']) : '',
                                                    'dataValue'  => $field['data'] ?? ''])
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body p-20">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-20">
                                    <div class="max-w-700">
                                        <h3 class="page-title mb-1">{{ translate('Required Information from Customer') }}</h3>
                                        <p class="fz-12">
                                            {{ translate('Add required input fields for customers to complete offline payments.') }}
                                            {{ translate('Mark fields as "Required" to ensure necessary info is provided.') }}
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn--primary rounded d-flex align-items-center gap-1" id="add-more-field-customer">
                                        <span class="absolute-white-bg rounded-full d-center text-primary w-14 h-14">+</span> {{ translate('Add New Field') }}
                                    </button>
                                </div>
                                <div class="row gy-3">
                                    <div class="col-sm-12 col-lg-6">
                                        @include('partials._form-field', [
                                            'type'        => 'textarea',
                                            'name'        => 'payment_note_preview',
                                            'id'          => 'payment_note',
                                            'label'       => translate('Payment Note'),
                                            'placeholder' => translate('Customer will leave a payment note here'),
                                            'disabled'    => true,
                                            'required'    => true,
                                            'rows'        => 3,
                                            'wrapClass'   => 'mb-0'])
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex flex-column gap-3" id="custom-field-section-customer">
                                            @foreach($customerInformation as $key => $field)
                                                @include('paymentmodule::admin.offline-payments.partials._customer-info-row', [
                                                    'rowIndex'         => $key,
                                                    'fieldNameValue'   => $field['field_name'] ? str_replace('_', ' ', $field['field_name']) : '',
                                                    'placeholderValue' => $field['placeholder'] ?? '',
                                                    'isRequired'       => !empty($field['is_required']) ? 1 : 0])
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @can('payment_method_update')
                            <div class="d-flex justify-content-end trans3 mt-4">
                                <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                    <div class="d-flex gap-3 justify-content-end">
                                        <button type="reset" class="btn btn--secondary rounded">{{ translate('Reset') }}</button>
                                        <button type="submit" class="btn btn--primary d-flex align-items-center gap-2 rounded demo_check">
                                            <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_9562_1632)">
                                                    <path d="M9.91732 0.5H4.08398V4H9.91732V0.5Z" fill="white"/>
                                                    <path d="M7.00065 9.83333C7.64498 9.83333 8.16732 9.311 8.16732 8.66667C8.16732 8.02233 7.64498 7.5 7.00065 7.5C6.35632 7.5 5.83398 8.02233 5.83398 8.66667C5.83398 9.311 6.35632 9.83333 7.00065 9.83333Z" fill="white"/>
                                                    <path d="M11.0833 0.5V5.16667H2.91667V0.5H1.75C1.28587 0.5 0.840752 0.684374 0.512563 1.01256C0.184374 1.34075 0 1.78587 0 2.25L0 14.5H14V3.41667L11.0833 0.5ZM7 11C6.53851 11 6.08738 10.8632 5.70367 10.6068C5.31995 10.3504 5.02088 9.98596 4.84428 9.55959C4.66768 9.13323 4.62147 8.66408 4.7115 8.21146C4.80153 7.75883 5.02376 7.34307 5.35008 7.01675C5.67641 6.69043 6.09217 6.4682 6.54479 6.37817C6.99741 6.28814 7.46657 6.33434 7.89293 6.51095C8.31929 6.68755 8.68371 6.98662 8.94009 7.37034C9.19649 7.75405 9.33333 8.20518 9.33333 8.66667C9.33333 9.28551 9.0875 9.879 8.64992 10.3166C8.21233 10.7542 7.61884 11 7 11Z" fill="white"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_9562_1632">
                                                        <rect width="14" height="14" fill="white" transform="translate(0 0.5)"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            {{ translate('Save Information') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end offcanvas-cus-sm" tabindex="-1" id="offline-offcanvas_preview" aria-labelledby="offlineOffcanvasPreviewLabel">
        <div class="offcanvas-header py-md-4 py-3">
            <h3 class="mb-0">{{ translate('Offline Payment') }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body bg-white">
            <div class="max-w-300 mx-auto">
                <div class="text-center mb-20">
                    <img width="100" src="{{ asset('public/assets/admin-module/img/offline_payment.png') }}" alt="" class="mb-2">
                    <p class="fz-12 mb-2 d-none d-sm-block">{{ translate('This view is from the user app.') }} {{ translate('This is how customer will see in the app') }}</p>
                    <h4>{{ translate('Amount') }} : xxx</h4>
                </div>
                <div class="rounded cus-shadow2 p-16 mb-20">
                    <div class="d-flex align-items-center justify-content-between mb-15">
                        <h4>{{ translate('Bank Info') }}</h4>
                        <button type="button" class="d-flex align-items-center text-primary border-0 gap-1 bg-primary bg-opacity-10 py-1 px-3 rounded fz-10">
                            {{ translate('Pay on this account') }} <span class="material-symbols-outlined fz-14">check_circle</span>
                        </button>
                    </div>
                    <ul class="d-flex flex-column gap-sm-2 gap-1 list-inline">
                        <li class="d-flex align-items-center gap-2 fz-12">
                            {{ translate('Holder Name') }} : <span class="text-dark">{{ translate('John Doe') }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 fz-12">
                            {{ translate('Branch') }} : <span class="text-dark">{{ translate('Branch-1') }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 fz-12">
                            {{ translate('A/C No') }} : <span class="text-dark">4857394057234</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 fz-12">
                            {{ translate('Bank Name') }} : <span class="text-dark">{{ translate('ABC Bank') }}</span>
                        </li>
                    </ul>
                </div>
                <h4 class="mb-10">{{ translate('Payment Info') }}</h4>
                <div class="body-bg rounded p-16 d-flex flex-column gap-4">
                    <div>
                        <div class="mb-10 text-dark">{{ translate('Payment By') }}</div>
                        <input type="text" placeholder="{{ translate('ex: David Miller') }}" class="form-control" value="David Miller" readonly>
                    </div>
                    <div>
                        <div class="mb-10 text-dark">{{ translate('Bank Name') }}</div>
                        <input type="text" placeholder="{{ translate('ex: ABC Bank') }}" class="form-control" value="ABC Bank" readonly>
                    </div>
                    <div>
                        <div class="mb-10 text-dark">{{ translate('A/C No') }}</div>
                        <input type="text" placeholder="{{ translate('ex: 66587698780') }}" class="form-control" value="66587698780" readonly>
                    </div>
                    <div>
                        <div class="mb-10 text-dark">{{ translate('Payment Note') }}</div>
                        <textarea class="form-control" placeholder="{{ translate('Write a payment note') }}" readonly rows="5">{{ translate('Payment Note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sectionViewModal" tabindex="-1" aria-labelledby="sectionViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center flex-column gap-3 text-center">
                        <h3>{{ translate('Offline Payment') }}</h3>
                        <img width="100" src="{{ asset('public/assets/admin-module/img/offline_payment.png') }}" alt="">
                        <p class="text-muted">{{ translate('This view is from the user app.') }} <br class="d-none d-sm-block"> {{ translate('This is how customer will see in the app') }}</p>
                    </div>

                    <div class="rounded p-4 mt-3" id="offline_payment_top_part">
                        <div class="d-flex justify-content-between gap-2 mb-3">
                            <h4 id="payment_modal_method_name"><span></span></h4>
                            <div class="text-primary d-flex align-items-center gap-2">
                                {{ translate('Pay on this account') }}
                                <span class="material-icons">check_circle</span>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2" id="methodNameDisplay"></div>
                        <div class="d-flex flex-column gap-2" id="displayDataDiv"></div>
                    </div>

                    <div class="rounded p-4 mt-3 mt-4" id="offline_payment_bottom_part">
                        <h2 class="text-center mb-4">{{ translate('Amount') }} : xxx</h2>
                        <h4 class="mb-3">{{ translate('Payment Info') }}</h4>
                        <div class="d-flex flex-column gap-3 mb-3" id="customer-info-display-div"></div>
                        <div class="d-flex flex-column gap-3">
                            <textarea class="form-control" readonly rows="10" placeholder="{{ translate('Payment note') }}"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-3">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="button" class="btn btn--primary">{{ translate('Submit') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        "use strict";

        function extractPaymentRowsData() {
            var data = [];
            $('.field-row-payment').each(function () {
                var title = $(this).find('input[name="title[]"]').val();
                var dataValue = $(this).find('input[name^="data["]').val();
                data.push({ title: title, data: dataValue });
            });
            return data;
        }

        function extractCustomerRowsData() {
            var data = [];
            $('.field-row-customer').each(function () {
                var fieldName = $(this).find('input[name^="field_name["]').val();
                var placeholder = $(this).find('input[name^="placeholder["]').val();
                var isRequired = $(this).find('input[type="checkbox"][name^="is_required["]').prop('checked');
                data.push({ fieldName: fieldName, placeholder: placeholder, isRequired: isRequired });
            });
            return data;
        }

        function renderPreviewModal(contentArgument) {
            if (contentArgument === 'bkashInfo') {
                $('#sectionViewModal #offline_payment_top_part').addClass('active');
                $('#sectionViewModal #offline_payment_bottom_part').removeClass('active');
            } else {
                $('#sectionViewModal #offline_payment_top_part').removeClass('active');
                $('#sectionViewModal #offline_payment_bottom_part').addClass('active');
            }

            var methodName = $('#method_name').val();
            if (methodName) {
                $('#payment_modal_method_name').text(methodName + ' ' + '{{ translate("Info") }}');
            }

            var payments = extractPaymentRowsData();
            var $displayDiv = $('#displayDataDiv');
            var $methodNameDisplay = $('#methodNameDisplay');
            $methodNameDisplay.empty();
            $displayDiv.empty();

            $methodNameDisplay.append(
                $('<div>').addClass('d-flex gap-3 align-items-center mb-2')
                    .append($('<span>').text('{{ translate("Payment Method") }}'))
                    .append($('<span>').text(':'))
                    .append($('<span>').html(methodName || ''))
            );

            payments.forEach(function (item) {
                if (!item.title) return;
                $displayDiv.append(
                    $('<div>').addClass('d-flex gap-3 align-items-center')
                        .append($('<span>').text(item.title))
                        .append($('<span>').text(':'))
                        .append($('<span>').html(item.data || ''))
                );
            });

            var customers = extractCustomerRowsData();
            var $customerInfo = $('#customer-info-display-div');
            $customerInfo.empty();
            $.each(customers, function (index, item) {
                var requiredAttr = item.isRequired ? 'required' : '';
                $customerInfo.append(
                    '<input type="text" class="form-control" readonly placeholder="' + (item.placeholder || '') + '" ' + requiredAttr + '>'
                );
            });

            $('#sectionViewModal').modal('show');
        }

        $(document).ready(function () {
            $('#bkashInfoModalButton').on('click', function () {
                renderPreviewModal('bkashInfo');
            });
            $('#paymentInfoModalButton').on('click', function () {
                renderPreviewModal('paymentInfo');
            });
        });
    </script>

    <script>
        "use strict";

        (function () {
            var $form = $('#offline-payment-update-form');
            if (!$form.length) return;

            var counter = {{ count($customerInformation) }};
            var counterPayment = {{ count($paymentInformation) }};

            function buildPaymentRow(idx) {
                var titleLabel    = @json(translate('Title'));
                var titlePh       = @json(translate('Enter Title'));
                var dataLabel     = @json(translate('Data'));
                var dataPh        = @json(translate('Enter Data'));
                var removeTitle   = @json(translate('Remove'));

                return '' +
                    '<div id="field-row-payment--' + idx + '" class="field-row-payment body-bg rounded p-20">' +
                        '<div class="row g-3 align-items-start">' +
                            '<div class="col-md-5">' +
                                '<div class="ff-field ff-field--text mb-0" data-ff-field>' +
                                    '<label for="payment_title_' + idx + '" class="ff-field__label">' +
                                        '<span class="ff-field__label-text">' + titleLabel + '<span class="ff-field__required">*</span></span>' +
                                    '</label>' +
                                    '<div class="ff-field__control">' +
                                        '<input type="text" name="title[]" id="payment_title_' + idx + '" value="" class="form-control ff-field__input" placeholder="' + titlePh + '" maxlength="60" required data-char-count data-char-count-target="#payment_title_' + idx + '_char_count">' +
                                    '</div>' +
                                    '<div class="ff-field__footer">' +
                                        '<div class="ff-field__messages"></div>' +
                                        '<small class="ff-field__counter"><span id="payment_title_' + idx + '_char_count">0</span>/60</small>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-5">' +
                                '<div class="ff-field ff-field--text mb-0" data-ff-field>' +
                                    '<label for="payment_data_' + idx + '" class="ff-field__label">' +
                                        '<span class="ff-field__label-text">' + dataLabel + '<span class="ff-field__required">*</span></span>' +
                                    '</label>' +
                                    '<div class="ff-field__control">' +
                                        '<input type="text" name="data[]" id="payment_data_' + idx + '" value="" class="form-control ff-field__input" placeholder="' + dataPh + '" maxlength="120" required data-char-count data-char-count-target="#payment_data_' + idx + '_char_count">' +
                                    '</div>' +
                                    '<div class="ff-field__footer">' +
                                        '<div class="ff-field__messages"></div>' +
                                        '<small class="ff-field__counter"><span id="payment_data_' + idx + '_char_count">0</span>/120</small>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-2 d-flex justify-content-end align-items-start pt-md-4 mt-md-2">' +
                                '<button type="button" class="btn btn--danger w-30 h-30 p-0 remove-field-payment-btn rounded-1 d-center" data-counter-payment="' + idx + '" title="' + removeTitle + '">' +
                                    '<i class="material-symbols-outlined m-0 fz-20">delete</i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }

            function buildCustomerRow(idx) {
                var fieldNameLabel = @json(translate('Input Field Name'));
                var fieldNamePh    = @json(translate('Enter Field Name'));
                var placeholderLb  = @json(translate('Placeholder'));
                var placeholderPh  = @json(translate('Enter Placeholder Text'));
                var requiredLabel  = @json(translate('Is Required'));
                var removeTitle    = @json(translate('Remove'));

                return '' +
                    '<div id="field-row-customer--' + idx + '" class="field-row-customer body-bg rounded p-20 position-relative">' +
                        '<div class="row g-3 align-items-start">' +
                            '<div class="col-md-5">' +
                                '<div class="ff-field ff-field--text mb-0" data-ff-field>' +
                                    '<label for="customer_field_name_' + idx + '" class="ff-field__label">' +
                                        '<span class="ff-field__label-text">' + fieldNameLabel + '<span class="ff-field__required">*</span></span>' +
                                    '</label>' +
                                    '<div class="ff-field__control">' +
                                        '<input type="text" name="field_name[' + idx + ']" id="customer_field_name_' + idx + '" value="" class="form-control ff-field__input" placeholder="' + fieldNamePh + '" maxlength="60" required data-char-count data-char-count-target="#customer_field_name_' + idx + '_char_count">' +
                                    '</div>' +
                                    '<div class="ff-field__footer">' +
                                        '<div class="ff-field__messages"></div>' +
                                        '<small class="ff-field__counter"><span id="customer_field_name_' + idx + '_char_count">0</span>/60</small>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-5">' +
                                '<div class="ff-field ff-field--text mb-0" data-ff-field>' +
                                    '<label for="customer_placeholder_' + idx + '" class="ff-field__label">' +
                                        '<span class="ff-field__label-text">' + placeholderLb + '<span class="ff-field__required">*</span></span>' +
                                    '</label>' +
                                    '<div class="ff-field__control">' +
                                        '<input type="text" name="placeholder[' + idx + ']" id="customer_placeholder_' + idx + '" value="" class="form-control ff-field__input" placeholder="' + placeholderPh + '" maxlength="120" required data-char-count data-char-count-target="#customer_placeholder_' + idx + '_char_count">' +
                                    '</div>' +
                                    '<div class="ff-field__footer">' +
                                        '<div class="ff-field__messages"></div>' +
                                        '<small class="ff-field__counter"><span id="customer_placeholder_' + idx + '_char_count">0</span>/120</small>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-2">' +
                                '<div class="form-check d-flex align-items-center gap-1 pt-md-4 mt-md-2">' +
                                    '<input class="form-check-input" type="checkbox" value="1" name="is_required[' + idx + ']" id="is_required_' + idx + '" checked>' +
                                    '<label class="form-check-label" for="is_required_' + idx + '">' + requiredLabel + '</label>' +
                                '</div>' +
                            '</div>' +
                            '<button type="button" class="btn btn--danger offline-delete-icon position-absolute top-0 end-3 w-30 h-30 p-0 remove-field-btn rounded-1 d-center" data-counter="' + idx + '" title="' + removeTitle + '">' +
                                '<i class="material-symbols-outlined m-0 fz-20">delete</i>' +
                            '</button>' +
                        '</div>' +
                    '</div>';
            }

            $(document).on('click', '.remove-field-btn', function () {
                var id = $(this).data('counter');
                $('#field-row-customer--' + id).remove();
            });

            $(document).on('click', '.remove-field-payment-btn', function () {
                var id = $(this).data('counter-payment');
                $('#field-row-payment--' + id).remove();
            });

            $('#add-more-field-customer').on('click', function (event) {
                event.preventDefault();
                if ($('#custom-field-section-customer .field-row-customer').length < 14) {
                    $('#custom-field-section-customer').append(buildCustomerRow(counter));
                    counter++;
                } else {
                    Swal.fire({
                        title: '{{ translate("Reached maximum") }}',
                        confirmButtonText: '{{ translate("Ok") }}'
                    });
                }
            });

            $('#add-more-field-payment').on('click', function (event) {
                event.preventDefault();
                if ($('#custom-field-section-payment .field-row-payment').length < 14) {
                    $('#custom-field-section-payment').append(buildPaymentRow(counterPayment));
                    counterPayment++;
                } else {
                    Swal.fire({
                        title: '{{ translate("Reached maximum") }}',
                        confirmButtonText: '{{ translate("Ok") }}'
                    });
                }
            });

            $form.on('reset', function () {
                setTimeout(function () {
                    var origCustomerCount = {{ count($customerInformation) }};
                    var origPaymentCount = {{ count($paymentInformation) }};

                    $('#custom-field-section-payment [id^="field-row-payment--"]').each(function () {
                        var idx = parseInt($(this).attr('id').replace('field-row-payment--', ''), 10);
                        if (!isNaN(idx) && idx >= origPaymentCount) {
                            $(this).remove();
                        }
                    });
                    $('#custom-field-section-customer [id^="field-row-customer--"]').each(function () {
                        var idx = parseInt($(this).attr('id').replace('field-row-customer--', ''), 10);
                        if (!isNaN(idx) && idx >= origCustomerCount) {
                            $(this).remove();
                        }
                    });

                    counter = origCustomerCount;
                    counterPayment = origPaymentCount;

                    $form.find('input[type="text"], input[type="email"], input[type="password"], input[type="number"], textarea').each(function () {
                        $(this).val($(this).prop('defaultValue') || '');
                    });
                    $form.find('input[type="checkbox"]').each(function () {
                        $(this).prop('checked', $(this).prop('defaultChecked'));
                    });

                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () {
                            window.FormCharCount.update(this);
                        });
                    }

                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) {
                        $form.validate().resetForm();
                    }
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#offline-payment-update-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) $btn.data('ffOriginalHtml', $btn.html());
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
