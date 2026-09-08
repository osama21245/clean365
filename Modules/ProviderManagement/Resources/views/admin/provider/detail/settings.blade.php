@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            @include('providermanagement::admin.provider.detail._tabs')

            <div class="card mb-30">
                <div class="card-body p-30">
                    <form id="provider-commission-settings-form"
                          action="{{route('admin.provider.commission_update', [$provider->id])}}"
                          method="post"
                          data-ff-validate novalidate>
                        @csrf
                        <div class="bg-light rounded p-xxl-4 p-3">
                            <h4 class="mb-3">{{translate('Commission Settings')}}</h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="mb-2 fw-medium">{{translate('Commission Type')}} <span class="text-danger">*</span></div>
                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-2">
                                        <div class="custom-radio">
                                            <input type="radio" name="commission_status" id="default_commission"
                                                   value="default" {{$provider->commission_status == 0 ? 'checked' : ''}}>
                                            <label for="default_commission">{{translate('Use Default')}}</label>
                                        </div>
                                        <div class="custom-radio">
                                            <input type="radio" name="commission_status" id="custom_commission"
                                                   value="custom" {{$provider->commission_status == 1 ? 'checked' : ''}}>
                                            <label for="custom_commission">{{translate('Set Custom Commission')}}</label>
                                        </div>
                                    </div>
                                    <p class="fs-14 mb-0 {{$provider->commission_status == 1 ? 'd-none' : ''}}" id="default_commission_text">
                                        {{ $commission }}% {{translate('Commission per booking order')}}
                                    </p>
                                </div>

                                <div class="col-lg-4 {{$provider->commission_status == 0 ? 'd-none' : ''}}" id="percentage">
                                    @include('partials._form-field', [
                                        'type'        => 'number',
                                        'name'        => 'custom_commission_value',
                                        'id'          => 'percentage__input',
                                        'label'       => translate('Commission Percentage'),
                                        'placeholder' => translate('Enter Commission Percentage'),
                                        'icon'        => 'percent',
                                        'required'    => $provider->commission_status == 1,
                                        'min'         => 0,
                                        'max'         => 100,
                                        'step'        => 'any',
                                        'suffix'      => '%',
                                        'value'       => old('custom_commission_value', $provider->commission_percentage),
                                        'wrapClass'   => 'mb-0'])
                                </div>
                            </div>
                        </div>

                        @can('provider_manage_status')
                            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                <button type="submit" class="btn btn--primary demo_check">{{translate('Save')}}</button>
                            </div>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script>
        "use strict";

        $('#default_commission').on('click', function () {
            if ($(this).is(':checked')) {
                $('#percentage').addClass('d-none');
                $('#percentage__input').removeAttr('required');
                $('#default_commission_text').removeClass('d-none');
            }
        });

        $('#custom_commission').on('click', function () {
            if ($(this).is(':checked')) {
                $('#percentage').removeClass('d-none');
                $('#percentage__input').prop('required', true);
                $('#default_commission_text').addClass('d-none');
            }
        });

        (function () {
            var $form = $('#provider-commission-settings-form');
            if (!$form.length) return;

            $form.on('reset', function () {
                setTimeout(function () {
                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }
                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                    if ($('input[name="commission_status"]:checked').val() === 'custom') {
                        $('#percentage').removeClass('d-none');
                        $('#default_commission_text').addClass('d-none');
                    } else {
                        $('#percentage').addClass('d-none');
                        $('#default_commission_text').removeClass('d-none');
                    }
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#provider-commission-settings-form', {
                    submitHandler: function (form) {
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
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
