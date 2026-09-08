@extends('adminmodule::layouts.new-master')

@section('title', translate('Create Offline Payment'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @can('payment_method_add')
                    <form id="offline-payment-create-form"
                          action="{{ route('admin.configuration.offline-payment.store') }}"
                          method="POST"
                          data-ff-validate novalidate>
                        @csrf
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
                                            {{ translate('Add relevant input fields for customers to fill up after completing the offline payment.') }}
                                            {{ translate('You can add multiple input fields and placeholders and define them as "Is Required", so customers cannot complete offline payment without adding that information.') }}
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
                                                'value'       => old('method_name'),
                                                'wrapClass'   => 'mb-0'])
                                        </div>
                                    </div>
                                    <div class="col-sm-12 d-flex flex-column gap-3">
                                        <div class="d-flex flex-column gap-3" id="custom-field-section-payment"></div>
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
                                        <div class="d-flex flex-column gap-3" id="custom-field-section-customer"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end trans3 mt-4">
                            <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                <div class="d-flex gap-3 justify-content-end">
                                    <button type="reset" class="btn btn--secondary rounded">{{ translate('Reset') }}</button>
                                    <button type="submit" class="btn btn--primary d-flex align-items-center gap-2 rounded demo_check">
                                        <img src="{{ asset('public/assets/admin-module/img/icons/save-icon.svg') }}" alt="save icon">
                                        {{ translate('Save Information') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endcan
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
                        <input type="text" placeholder="{{ translate('ex: David Miller') }}" class="form-control" readonly>
                    </div>
                    <div>
                        <div class="mb-10 text-dark">{{ translate('Bank Name') }}</div>
                        <input type="text" placeholder="{{ translate('ex: ABC Bank') }}" class="form-control" readonly>
                    </div>
                    <div>
                        <div class="mb-10 text-dark">{{ translate('A/C No') }}</div>
                        <input type="text" placeholder="{{ translate('ex: 66587698780') }}" class="form-control" readonly>
                    </div>
                    <div>
                        <div class="mb-10 text-dark">{{ translate('Payment Note') }}</div>
                        <textarea class="form-control" placeholder="{{ translate('Write a payment note') }}" readonly rows="5"></textarea>
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
            var $form = $('#offline-payment-create-form');
            if (!$form.length) return;

            var counter = 0;
            var counterPayment = 0;

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
                if (counter < 14) {
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
                if (counterPayment < 14) {
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
                    $('#custom-field-section-payment').empty();
                    $('#custom-field-section-customer').empty();
                    counter = 0;
                    counterPayment = 0;

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
                FormValidator.register('#offline-payment-create-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) $btn.data('ffOriginalHtml', $btn.html());
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '{{ translate("Saving...") }}'
                        );
                        form.submit();
                    }
                });
            }
        })();
    </script>
@endpush
