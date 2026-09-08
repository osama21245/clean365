@extends('providermanagement::layouts.master')

@section('title',translate('Request for Service'))

@push('css_or_js')
    <style>
        .service-request-illustration {
            width: 220px;
            max-width: 100%;
            height: auto;
        }

        @media (max-width: 575.98px) {
            .service-request-illustration {
                width: 160px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Request for Service')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body py-xl-5">
                            <div class="row justify-content-center align-items-center gy-5">
                                <div class="col-12 col-lg-5 order-2 order-lg-1">
                                    <h3 class="mb-3">{{translate('Tell us more about your desired services')}}</h3>
                                    <p>{{translate('Suggest more services that are willing to book and help us make  more efficient platform for you')}} ...</p>

                                    <div class="collapse" id="serviceForm">
                                        <form id="service-request-form"
                                              action="{{route('provider.service.make-request')}}"
                                              method="post"
                                              data-ff-validate novalidate>
                                            @csrf
                                            <div class="bg-light rounded p-xxl-4 p-3">
                                                <h4 class="mb-3">{{translate('Service Details')}}</h4>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'category_id',
                                                            'id'          => 'service_request_category',
                                                            'label'       => translate('Category'),
                                                            'required'    => true,
                                                            'selectClass' => 'js-select category__select',
                                                            'optionNull'  => translate('Select Category'),
                                                            'options'     => array_merge(collect($categories)->pluck('name', 'id')->toArray(), ['null' => translate('Other')]),
                                                            'value'       => old('category_id'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-12">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'service_name',
                                                            'label'       => translate('Service Name'),
                                                            'placeholder' => translate('Enter Service Name'),
                                                            'icon'        => 'design_services',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('service_name'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-12">
                                                        @include('partials._form-field', [
                                                            'type'        => 'textarea',
                                                            'name'        => 'service_description',
                                                            'id'          => 'floatingTextarea',
                                                            'label'       => translate('Description'),
                                                            'placeholder' => translate('Provide Some Description'),
                                                            'required'    => true,
                                                            'rows'        => 4,
                                                            'maxlength'   => 500,
                                                            'charCount'   => true,
                                                            'value'       => old('service_description'),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                                <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                                <button type="submit" class="btn btn--primary demo_check">{{translate('Send Request')}}</button>
                                            </div>
                                        </form>
                                    </div>
                                    <a href="#serviceForm" class="btn btn--primary show_form-btn" data-bs-toggle="collapse">{{translate('Send Request')}}</a>


                                </div>
                                <div class="col-12 col-lg-5 order-1 order-lg-2">
                                    <div class="text-center">
                                        <img class="service-request-illustration" src="{{asset('public/assets/admin-module/img/media/serv.png')}}" alt="{{ translate('service') }}">
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
    <script>
        "use strict";

        function collapse() {
            $(document.body).on('click', '[data-toggle="collapse"]', function (e) {
                e.preventDefault();
                var target = '#' + $(this).data('target');

                $(this).slideToggle('collapsed');
                $(target).slideToggle();

            })
        }
        collapse();

        $(document).ready(function () {
            $('.category__select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
        });
    </script>
@endpush
