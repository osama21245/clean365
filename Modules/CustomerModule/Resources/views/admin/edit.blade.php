@extends('adminmodule::layouts.master')

@section('title',translate('customer update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    @php($currentCustomerImage = $customer->profile_image_full_path)
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('customer update')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            @can('customer_update')
                            <form action="{{route('admin.customer.update',[$customer->id])}}" method="post"
                                  enctype="multipart/form-data"
                                  id="customer-update-form"
                                  data-ff-validate novalidate>
                                @csrf
                                @method('put')
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'first_name',
                                                        'label'       => translate('First Name'),
                                                        'placeholder' => translate('Enter First Name'),
                                                        'icon'        => 'account_circle',
                                                        'required'    => true,
                                                        'maxlength'   => 60,
                                                        'charCount'   => true,
                                                        'value'       => old('first_name', $customer['first_name']),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'last_name',
                                                        'label'       => translate('Last Name'),
                                                        'placeholder' => translate('Enter Last Name'),
                                                        'icon'        => 'account_circle',
                                                        'required'    => true,
                                                        'maxlength'   => 60,
                                                        'charCount'   => true,
                                                        'value'       => old('last_name', $customer['last_name']),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'email',
                                                        'name'        => 'email',
                                                        'label'       => translate('Email'),
                                                        'placeholder' => translate('ex: abc@email.com'),
                                                        'icon'        => 'mail',
                                                        'required'    => true,
                                                        'autocomplete'=> 'email',
                                                        'value'       => old('email', $customer['email']),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>

                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'tel',
                                                        'name'        => 'phone',
                                                        'id'          => 'phone',
                                                        'label'       => translate('Phone'),
                                                        'placeholder' => translate('Enter Phone Number'),
                                                        'required'    => true,
                                                        'autocomplete'=> 'tel',
                                                        'value'       => old('phone', $customer['phone']),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                @include('adminmodule::admin.partials._single-image-upload', [
                                                    'name'             => 'profile_image',
                                                    'id'               => 'profile_image',
                                                    'title'            => translate('Profile Image'),
                                                    'required'         => false,
                                                    'image'            => $currentCustomerImage,
                                                    'instructionRatio' => '1:1',
                                                    'height'           => null,
                                                    'showView'         => true,
                                                    'showEdit'         => true,
                                                    'showDelete'       => false])
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 order-last">
                                        <div class="d-flex justify-content-end gap-20 mt-20">
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
    <script>
        "use strict";

        (function () {
            var $form = $('#customer-update-form');
            if (!$form.length) return;

            var originalCustomerImage = @json($currentCustomerImage ?? '');
            var originalPhone         = @json(old('phone', $customer['phone'] ?? ''));

            function setCustomerImagePreview(src) {
                var $wrapper = $form.find('.global-image-upload').first();
                if (!$wrapper.length) return;
                if (src) {
                    $wrapper.addClass('has-image');
                    $wrapper.find('.global-image-preview').attr('src', src).removeClass('d-none');
                    $wrapper.find('.global-upload-box').addClass('global-upload-box-hidden');
                    $wrapper.find('.overlay-icons').removeClass('d-none');
                } else {
                    $wrapper.removeClass('has-image');
                    $wrapper.find('.global-image-preview').attr('src', '').addClass('d-none');
                    $wrapper.find('.global-upload-box').removeClass('global-upload-box-hidden');
                    $wrapper.find('.overlay-icons').addClass('d-none');
                }
                $wrapper.find('input[type="file"]').val('');
            }

            $form.on('reset', function () {
                setTimeout(function () {
                    setCustomerImagePreview(originalCustomerImage);

                    var phoneEl = document.querySelector('#phone');
                    if (phoneEl && window.intlTelInputGlobals) {
                        var iti = window.intlTelInputGlobals.getInstance(phoneEl);
                        if (iti) { iti.setNumber(originalPhone || ''); }
                    }

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
                FormValidator.register('#customer-update-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) {
                            $btn.data('ffOriginalHtml', $btn.html());
                        }
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
