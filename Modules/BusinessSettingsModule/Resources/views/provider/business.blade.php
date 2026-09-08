@extends('providermanagement::layouts.new-master')

@section('title',translate('business_settings'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('business_settings')}}</h2>
                    </div>

                    <div class="mb-3 nav-tabs-responsive position-relative">
                        <ul class="nav nav--tabs nav--tabs__style2 scrollbar-w flex-nowrap white-nowrap overflow-x-auto flex-wrap-nowrap">
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=businessinfos"
                                   class="nav-link rounded-pill {{$webPage=='businessinfos'?'active':''}}">
                                    {{translate('Business Information')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=service_availability"
                                   class="nav-link rounded-pill {{$webPage=='service_availability'?'active':''}}">
                                    {{translate('Service Availability')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=bookings"
                                   class="nav-link rounded-pill {{$webPage=='bookings'?'active':''}}">
                                    {{translate('bookings')}}
                                </a>
                            </li>
                        </ul>
                        <div class="nav--tab__prev position-absolute top-0 start-0">
                            <button class="border-0 w-38 h-38 d-flex align-items-center justify-content-center rounded-full p-0 bg-white text-primary">
                                <i class="fi fi-rr-angle-left"></i>
                            </button>
                        </div>
                        <div class="nav--tab__next position-absolute top-0 right-0">
                            <button class="border-0 w-38 h-38 d-flex align-items-center justify-content-center rounded-full p-0 bg-white text-primary">
                                <span class="fi fi-rr-angle-right"></span>
                            </button>
                        </div>
                    </div>

                    @if($webPage=='businessinfos')

                    <div class="tab-content">
                        <div class="tab-pane fade {{$webPage=='businessinfos'?'active show':''}}">

                            <form id="business-information-form" action="{{route('provider.business-settings.update-business-information')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card mb-3">
                                    <div class="border-bottom px-4 py-3">
                                        <h3 class="mb-1">
                                            {{translate('Basic Information')}}
                                        </h3>
                                        <p class="fs-12">{{ translate('Here you setup your all business information.') }}</p>
                                    </div>
                                    <div class="card-body p-30">
                                        <div class="discount-type">
                                            <div class="row g-3 mb-4">
                                                <div class="col-xxl-9 col-lg-8">
                                                    <div class="shadow-sm rounded p-20">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <div class="form-business">
                                                                    <label class="mb-2 title-color">{{translate('Company Name / Individual Name')}} <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control h-45"
                                                                           name="company_name"
                                                                           value="{{ $provider->company_name }}"
                                                                           placeholder="{{translate('Company_/_Individual_Name')}}"
                                                                           required="">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-business">
                                                                    <label class="mb-2 title-color">{{translate('Company Email')}} <span class="text-danger">*</span></label>
                                                                    <input type="email" class="form-control h-45"
                                                                           name="company_email"
                                                                           value="{{ $provider->company_email }}"
                                                                           placeholder="{{translate('Company_Email')}}"
                                                                           required="">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-business phone-picker-wrap country-picker-1">
                                                                    <label class="mb-2 title-color">{{translate('Phone')}} <span class="text-danger">*</span></label>
                                                                    <input type="tel"
                                                                           class="form-control"
                                                                           name="company_phone"
                                                                           placeholder="{{translate('Enter your number')}}"
                                                                           required id="company_phone"
                                                                           value="{{ $provider->company_phone }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-business">
                                                                    <label class="mb-2 title-color">{{translate('Select Zone')}} <span class="text-danger">*</span></label>
                                                                    <select class="js-select theme-input-style w-100" name="zone_id" id="zone_id" data-original-zone-id="{{ $provider?->zone?->id ?? '' }}" required>
                                                                        <option selected disabled>{{translate('Select_Zone')}}</option>
                                                                        @foreach($zones as $zone)
                                                                            <option value="{{$zone->id}}" {{ $provider?->zone?->id == $zone->id ? 'selected' : '' }}>{{$zone->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-business">
                                                                    <label class="mb-2 title-color">{{translate('address')}} <span class="text-danger">*</span>
                                                                        <i class="fi fi-sr-info fz-14 text-muted" data-bs-toggle="tooltip"
                                                                           data-bs-placement="top"
                                                                           title="{{translate('Type your business address')}}"
                                                                        ></i>
                                                                    </label>
                                                                    <textarea class="form-control" rows="1" name="company_address" id="business_address"
                                                                              placeholder="{{translate('Ex : House#38, Road#04, Demo City')}}" required="">{!! $provider->company_address !!}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12" id="location_map_div">
                                                                <input id="pac-input" class="controls rounded" data-toggle="tooltip"
                                                                       data-placement="right"
                                                                       data-original-title="{{ translate('search_your_location_here') }}"
                                                                       type="text" placeholder="{{ translate('Search Here') }}" />
                                                                <div id="location_map_canvas" class="overflow-hidden rounded h-100"></div>
                                                            </div>

                                                            <input type="hidden" name="latitude" id="address_latitude" value="{{$addressLat}}">
                                                            <input type="hidden" name="longitude" id="address_longitude" value="{{$addressLong}}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-lg-4">
                                                    <div class="d-flex flex-column gap-4 justify-content-center h-100">
                                                        <div class="bg-light rounded-10 p-3 p-sm-20 d-flex flex-column gap-20 w-100 h-100">
                                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                                'name'             => 'logo',
                                                                'id'               => 'businessLogo',
                                                                'title'            => translate('Upload Logo'),
                                                                'subtitle'         => translate('Upload your business logo'),
                                                                'required'         => false,
                                                                'image'            => $provider->logo_full_path,
                                                                'ratio'            => 'ratio-1-1',
                                                                'height'           => 'h-120',
                                                                'instructionRatio' => '1:1',
                                                                'showView'         => true,
                                                                'showEdit'         => true,
                                                                'showDelete'       => false])
                                                        </div>
                                                        <div class="bg-light rounded-10 p-3 p-sm-20 d-flex flex-column gap-20 w-100 h-100">
                                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                                'name'             => 'cover_image',
                                                                'id'               => 'businessCoverImage',
                                                                'title'            => translate('Upload Cover Image'),
                                                                'subtitle'         => translate('Upload your business cover'),
                                                                'required'         => false,
                                                                'image'            => $provider->cover_image_full_path,
                                                                'ratio'            => 'ratio-3-1',
                                                                'height'           => 'h-120',
                                                                'instructionRatio' => '3:1',
                                                                'showView'         => true,
                                                                'showEdit'         => true,
                                                                'showDelete'       => false])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pick-map p-12 rounded d-flex flex-md-nowrap flex-wrap align-items-center gap-1 bg-primary bg-opacity-10">
                                                <img src="{{asset('public/assets/provider-module')}}/img/icons/bulp-icon.svg" alt="pulp/img" class="icon">
                                                <p class="fz-12">{{ translate('For the address setup you can simply drag the map to pick for the perfect') }} <span class="fw-semibold">Lat(Latitude) & Log(Longitude)</span> {{ translate('value') }}.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-body mb-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-20">
                                        <div>
                                            <h3 class="mb-1">
                                                {{translate('Contact Person Information')}}
                                            </h3>
                                            <p class="fs-12">{{ translate('Setup your account information') }}</p>
                                        </div>
                                        <div class="form-check p-0 m-0 d-flex align-items-center gap-2">
                                            <label class="form-check-label" for="business_info">
                                                {{ translate('Same as Business Information') }}
                                            </label>
                                            <input class="form-check-input ml--0" type="checkbox" value="" id="same_as_business_info">
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-20">
                                        <div class="row g-3">
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="form-business">
                                                    <label for="name_" class="mb-2 title-color">{{translate('Person Name')}} <span class="text-danger">*</span></label>
                                                    <input type="text" id="name_" class="form-control h-45" name="contact_person_name"
                                                           value="{{ $provider->contact_person_name }}"
                                                           placeholder="{{translate('Name')}}" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="form-business">
                                                    <label for="email_" class="mb-2 title-color">{{translate('Person Email')}} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control h-45" name="contact_person_email"
                                                           value="{{ $provider->contact_person_email }}"
                                                           placeholder="{{translate('Business_Email')}}">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="form-business phone-picker-wrap country-picker-2">
                                                    <label class="mb-2 title-color">{{translate('Phone')}} <span class="text-danger">*</span></label>
                                                    <input type="tel"
                                                           class="form-control"
                                                           name="contact_person_phone"
                                                           id="contact_person_phone"
                                                           value="{{ $provider->contact_person_phone }}"
                                                           placeholder="{{translate('Phone')}}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-body mb-3">
                                    <div class="mb-20">
                                        <h3 class="mb-1">
                                            {{translate('Identity Information')}}
                                        </h3>
                                        <p  class="fs-12">{{ translate('Setup your Identity Information') }}</p>
                                    </div>
                                    <div class="bg-light rounded p-20 mb-3">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="form-business">
                                                    <label for="" class="mb-2 title-color">{{translate('Identity Type')}}</label>
                                                    <select class="select-identity theme-input-style2 w-100" name="identity_type" required>
                                                        <option selected disabled>{{translate('Select_Identity_Type')}}</option>
                                                        <option value="passport"{{$provider->owner->identification_type == 'passport' ? 'selected': ''}}>{{translate('Passport')}}</option>
                                                        <option value="driving_license"{{$provider->owner->identification_type == 'driving_license' ? 'selected': ''}}>{{translate('Driving_License')}}</option>
                                                        <option value="nid"{{$provider->owner->identification_type == 'nid' ? 'selected': ''}}>{{translate('nid')}}</option>
                                                        <option value="trade_license"{{$provider->owner->identification_type == 'trade_license' ? 'selected': ''}}>{{translate('Trade_License')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-business">
                                                    <label for="" class="mb-2 title-color">{{translate('Identity Number')}} <span class="text-danger">*</span></label>
                                                    <input type="text" id="" class="form-control h-45"  name="identity_number"
                                                           value="{{$provider->owner->identification_number}}"
                                                           placeholder="{{translate('Identity_Number')}}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-20">
                                        <div class="upload-file w-100">
                                            @include('adminmodule::admin.partials._multiple-image-upload', [
                                                'name' => 'identity_images[]',
                                                'id' => 'identity_images',
                                                'title' => translate('Identification_Image'),
                                                'subtitle' => null,
                                                'instructionRatio' => '2:1',
                                                'required' => true,
                                                'images' => $provider->owner->identification_image_full_path,
                                                'imageNames' => $provider->owner->identification_image ?? [],
                                                'maxCount' => 2,
                                                'imagePath' => 'provider/identity/',
                                                'ratio' => 'ratio-2-1',
                                                'defaultImagePath' =>  asset('public/assets/admin-module/img/media/provider-id.png')
                                            ])
                                        </div>
                                    </div>
                                </div>


                                <div class="d-flex justify-content-end trans3 mt-4">
                                    <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                        <button type="button" id="custom-reset" class="btn btn--secondary min-w-120">
                                            {{ translate('reset') }}
                                        </button>
                                        <button type="submit" class="btn btn--primary min-w-120">
                                            <i class="fi fi-sr-disk"></i>
                                            {{ translate('save_information') }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                    @endif

                    @if($webPage=='service_availability')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='service_availability'?'active show':''}}">
                                <form action="{{route('provider.business-settings.availability-schedule')}}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="card card-body mb-3">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-lg-8">
                                                <div>
                                                    <h3 class="mb-1">{{translate('Service Availability')}}</h3>
                                                    <p class="fz-12 mb-0">
                                                        {{translate('By turning off availability mode, you will not get any new booking request from customers and customer will that you are currently unavailable to provide service')}}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                                    <span>{{ translate('Status') }}</span>
                                                    <label class="switcher m-0">
                                                        <input class="switcher_input service-availability"
                                                               type="checkbox" {{Auth::user()->provider->service_availability == '1' ? 'checked' : ''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-body">
                                        <div class="mb-4">
                                            <h3 class="mb-1">{{translate('Service Provider Availability Schedules')}}</h3>
                                            <p class="fz-12 mb-0">
                                                {{translate('Using the current time slot, the system shows your availability to customers in app & web, enabling them to make successful bookings')}}
                                            </p>
                                        </div>
                                        <div class="card card-body bg-light shadow-none">
                                            <div class="row align-items-center g-3">
                                                <div class="col-lg-6">
                                                    <div>
                                                        <h5 class="mb-1">{{translate('Service Providing Time')}}</h5>
                                                        <p class="fz-12 mb-0">
                                                            {{translate('Choose time range when you want to provide services.')}}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="mb-2 title-color">{{translate('Select Time Range')}} <span class="text-danger">*</span></label>
                                                    <div class="table-responsive rounded border">
                                                        <table class="table border-0 align-middle text-nowrap bg-white mb-0">
                                                            <tr>
                                                                <td class="h-40 py-0 px-0 border-inline-end">
                                                                    <div class="position-relative">
                                                                        <input type="time"
                                                                               name="start_time"
                                                                               class="custom-time-input input-dark-support f-flex justify-content-start form-control border-0 m-0 py-0 ps-36 shadow-none bg-transparent"
                                                                               value="{{isset($timeSchedule['start_time']) ? $timeSchedule['start_time'] : ''}}">
                                                                    </div>
                                                                </td>
                                                                <td class="h-40 py-0 px-0">
                                                                    <div class="position-relative">
                                                                        <input type="time"
                                                                               name="end_time"
                                                                               class="custom-time-input input-dark-support f-flex justify-content-start form-control border-0 m-0 py-0 ps-36 shadow-none bg-transparent"
                                                                               value="{{isset($timeSchedule['end_time']) ? $timeSchedule['end_time'] : ''}}">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="__border"/>
                                            <div class="row align-items-center g-3">
                                                <div class="col-lg-6">
                                                    <div>
                                                        <h5 class="mb-1">{{translate('Weekend')}}</h5>
                                                        <p class="fz-12 mb-0">
                                                            {{translate('Select the systems you want to temporarily deactivate for maintenance')}}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="bg-white border p-20 rounded d-flex align-items-center gap-20 flex-wrap">
                                                        @foreach(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
                                                            <label class="m-0">
                                                                <input type="checkbox" class="form-check-input" name="day[]" value="{{ $day }}"
                                                                    {{ in_array($day, $weekEnds ?? []) ? 'checked' : '' }}>
                                                                <span class="form-check-label">{{ translate(ucfirst($day)) }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end trans3 mt-4">
                                        <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                            <button type="reset" class="btn btn--secondary min-w-120">
                                                {{ translate('reset') }}
                                            </button>
                                            <button type="submit" class="btn btn--primary min-w-120">
                                                <i class="fi fi-sr-disk"></i>
                                                {{ translate('save_information') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='bookings')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='bookings'?'active show':''}}">

                                <form action="{{route('provider.business-settings.set-business-information')}}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    @php($servicemanCanBookingCancel = collect([ ['key' => 'provider_serviceman_can_cancel_booking','info_message' => 'Service Men Can Cancel Booking', 'title' => 'Cancel Booking Req'] ]))
                                    @php($servicemanCanBookingEdit = collect([ ['key' => 'provider_serviceman_can_edit_booking','info_message' => 'Service Men Can Edit Booking', 'title' => 'Edit Booking Req'] ]))

                                    <div class="card card-body mb-3">
                                        <div class="mb-4">
                                            <h3 class="mb-1">{{ translate('Booking Request Setup') }}</h3>
                                            <p class="fz-12 mb-0">{{ translate('Here you can setup for service man where they want to cancel or edit bookings') }}</p>
                                        </div>
                                        <div class="card card-body bg-light shadow-none mb-3">
                                            <div class="row g-3 align-items-center">
                                                <div class="col-lg-8">
                                                    <div>
                                                        <h4 class="mb-1">{{ translate('Cancel Booking Request') }}</h4>
                                                        <p class="fz-12 mb-0">{{ translate('By turning the switch service men can cancel booking') }}</p>
                                                    </div>
                                                </div>
                                                @php($value = $dataValues->where('key_name', 'provider_serviceman_can_cancel_booking')->where('settings_type', 'serviceman_config')->where('provider_id', $providerId)?->first()?->live_values ?? null)
                                                <div class="col-lg-4">
                                                    <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                                        <span>{{ translate('Status') }}</span>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   id="provider_serviceman_can_cancel_booking"
                                                                   name="provider_serviceman_can_cancel_booking"
                                                                   data-id="provider_serviceman_can_cancel_booking"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-message="Want to change the status of bidding system">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card card-body bg-light shadow-none mb-3">
                                            <div class="row g-3 align-items-center">
                                                <div class="col-lg-8">
                                                    <div>
                                                        <h4 class="mb-1">{{ translate('Edit Booking Request') }}</h4>
                                                        <p class="fz-12 mb-0">{{ translate('By turning the switch service men can edit booking') }}</p>
                                                    </div>
                                                </div>
                                                @php($value = $dataValues->where('key_name', 'provider_serviceman_can_edit_booking')->where('settings_type', 'serviceman_config')->where('provider_id', $providerId)?->first()?->live_values ?? null)

                                                <div class="col-lg-4">
                                                    <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                                        <span>{{ translate('Status') }}</span>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   id="provider_serviceman_can_edit_booking"
                                                                   name="provider_serviceman_can_edit_booking"
                                                                   data-id="provider_serviceman_can_edit_booking"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-message="Want to change the status of bidding system">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php($serviceAtProviderPlace = (int)((business_config('service_at_provider_place', 'provider_config'))->live_values ?? 0))

                                    @if($serviceAtProviderPlace)
                                        <div class="card card-body">
                                            <div class="mb-4">
                                                <h3 class="mb-1">{{ translate('Service Location') }}</h3>
                                                <p class="fz-12 mb-0">{{ translate('Here you setup where you want to provide services at your business location or customer location') }}</p>
                                            </div>
                                            <div class="card card-body bg-light shadow-none">
                                                <div class="d-flex gap-3 justify-content-between align-items-center flex-wrap">
                                                    <div class="flex-grow-1 d-flex gap-2 align-items-start">
                                                        <input type="checkbox" class="form-check-input service-location" name="customer_location" id="customer_location"
                                                            {{ in_array('customer', $serviceLocations) ? 'checked' : '' }}>
                                                        <div class="form-check-label">
                                                            <h5 class="opacity-75">{{ translate('Customer Location') }} </h5>
                                                            <p class="fs-12 mb-0">{{ translate('By checking this option you will be able to provide service at customer location') }} </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 d-flex gap-2 align-items-start">
                                                        <input type="checkbox" class="form-check-input" name="provider_location" id="provider_location"
                                                            {{ in_array('provider', $serviceLocations) ? 'checked' : '' }}>
                                                        <div class="form-check-label">
                                                            <h5 class="opacity-75">{{ translate('My Location') }}</h5>
                                                            <p class="fs-12 mb-0">{{ translate('By checking this option you will be able to provide service at your business location') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-end trans3 mt-4">
                                        <div class="d-flex justify-content-sm-end justify-content-center gap-2 gap-sm-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                                            <button type="reset" class="btn btn--secondary rounded">
                                                {{translate('reset')}}
                                            </button>
                                            <button type="submit" class="btn btn--primary">
                                                <i class="fi fi-sr-disk"></i>
                                                {{translate('Save Information')}}
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade custom-confirmation-modal" id="imageShowingMOdal" tabindex="-1" aria-labelledby="imageShowingMOdalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body py-3 px-sm-4 px-3">
                    <button type="button" class="btn-close bg-light rounded-full" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="image-display-container">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="zoneChangeModal" tabindex="-1" aria-labelledby="zoneChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('/public/assets/admin-module/img/location.png')}}" alt="" width="80" height="80">
                        <h3 id="zoneChangeModalLabel">{{translate('Change Zone?')}}</h3>
                        <p class="fw-medium">
                            {{translate('Changing the zone will reset all subscribed services. You will need to subscribe again after updating.')}}
                        </p>
                        <div class="d-flex gap-3 flex-wrap justify-content-center mt-2">
                            <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{translate('No')}}</button>
                            <button type="button" class="btn btn--primary" id="confirmZoneChange">{{translate('Yes, Update')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    @php($api_key=(business_config('google_map', 'third_party'))->live_values)
    <script src="https://maps.googleapis.com/maps/api/js?key={{$api_key['map_api_key_client']}}&libraries=drawing,places,geometry&v=3.45.8"></script>

    <script>
        "use strict";

        $('.switcher-btn').on('click', function () {
            let id = $(this).data('id');
            let status = $(this).is(':checked') === true ? 1 : 0;
            let message = $(this).data('message');
            switch_alert(id, status, message)
        });

        $('.service-availability').on('click', function () {
            @if(env('APP_ENV')!='demo')
                updateAvailability($(this).is(':checked')===true?1:0)
            @endif
        });

        function switch_alert(id, status, message) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: message,
                type: 'warning',
                showDenyButton: true,
                showCancelButton: true,
                denyButtonColor: 'var(--bs-primary)',
                confirmButtonColor: 'var(--bs-primary)',
                confirmButtonText: '{{ translate('Save') }}',
                denyButtonText: '{{ 'Do not save' }}',
            }).then((result) => {
                if (result.value) {
                } else {
                    if (status === 1) $(`#${id}`).prop('checked', false);
                    if (status === 0) $(`#${id}`).prop('checked', true);

                    Swal.fire('{{translate('Changes are not saved')}}', '', 'info')
                }
            })
        }

        function updateAvailability(status) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: '{{translate('want_to_update_status')}}',
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: '{{ translate('Cancel') }}',
                confirmButtonText: '{{ translate('Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('provider.business-settings.availability-status')}}",
                        data: {
                            service_availability: status,
                        },
                        type: 'put',
                        success: function (response) {
                            toastr.success('{{translate('successfully_updated')}}')
                        },
                        error: function () {

                        }
                    });
                }
            })
        }

        $(document).ready(function () {
            $('.js-select').select2();
        });

        (function () {
            const businessForm = document.getElementById('business-information-form');
            const zoneSelect = businessForm?.querySelector('select[name="zone_id"]');
            const zoneChangeModalElement = document.getElementById('zoneChangeModal');
            const zoneChangeModal = zoneChangeModalElement ? bootstrap.Modal.getOrCreateInstance(zoneChangeModalElement) : null;
            const confirmZoneChangeButton = document.getElementById('confirmZoneChange');
            let zoneChangeConfirmed = false;

            if (!businessForm || !zoneSelect || !zoneChangeModal) {
                return;
            }

            function isZoneChangedPendingConfirm() {
                const originalZoneId = zoneSelect.dataset.originalZoneId || '';
                const selectedZoneId = zoneSelect.value || '';
                return !zoneChangeConfirmed && originalZoneId && selectedZoneId && originalZoneId !== selectedZoneId;
            }

            $(zoneSelect).on('change', function () {
                zoneChangeConfirmed = false;
            });

            businessForm.addEventListener('submit', function (event) {
                if (isZoneChangedPendingConfirm()) {
                    event.preventDefault();
                    zoneChangeModal.show();
                }
            });

            if (confirmZoneChangeButton) {
                confirmZoneChangeButton.addEventListener('click', function () {
                    zoneChangeConfirmed = true;
                    zoneChangeModal.hide();
                    businessForm.requestSubmit
                        ? businessForm.requestSubmit()
                        : businessForm.submit();
                });
            }
        })();

        $(document).ready(function () {
            $('.service-location').on('change', function () {
                let customerChecked = $('#customer_location').is(':checked');
                let providerChecked = $('#provider_location').is(':checked');

                if (!customerChecked && !providerChecked) {
                    $(this).prop('checked', true);
                    toastr.error('{{ translate('At least one service location must be selected.') }}');
                }
            });
        });


        $( document ).ready(function() {
            function initAutocomplete() {
                var zonePolygons = {
                    @foreach($zones as $zone)
                    "{{ $zone->id }}": [
                            @if($zone->coordinates)
                            @foreach(json_decode($zone->coordinates[0]->toJson(), true)['coordinates'] as $coord)
                        { lat: {{ $coord[1] }}, lng: {{ $coord[0] }} },
                        @endforeach
                        @endif
                    ],
                    @endforeach
                };
                let currentPolygon = null;

                var myLatLng = {
                    lat: {{ $addressLat }},
                    lng: {{ $addressLong }}
                };

                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: {
                        lat: {{ $addressLat }},
                        lng: {{ $addressLong }}
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);

                function drawPolygon(zoneId, shouldFitBounds = true) {
                    if (currentPolygon) {
                        currentPolygon.setMap(null);
                    }
                    if (zonePolygons[zoneId] && zonePolygons[zoneId].length > 0) {
                        currentPolygon = new google.maps.Polygon({
                            paths: zonePolygons[zoneId],
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.1,
                            clickable: false
                        });
                        currentPolygon.setMap(map);

                        var bounds = new google.maps.LatLngBounds();
                        for (var i = 0; i < currentPolygon.getPath().getLength(); i++) {
                            bounds.extend(currentPolygon.getPath().getAt(i));
                        }

                        if (shouldFitBounds) {
                            map.setCenter(bounds.getCenter());
                        }
                    }
                }

                let selectedZone = $('select[name="zone_id"]').val();
                if (selectedZone) {
                    drawPolygon(selectedZone, false);
                }

                $('select[name="zone_id"]').on('change', function() {
                    drawPolygon($(this).val(), true);
                });

                var geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function(mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);

                    if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                        toastr.error('{{translate('you_cannot_pin_outside_of_the_selected_zone_polygon')}}');
                        return;
                    }

                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('address_latitude').value = coordinates['lat'];
                    document.getElementById('address_longitude').value = coordinates['lng'];

                    geocoder.geocode({
                        'latLng': latlng
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('business_address').value = results[1].formatted_address;
                            }
                        }
                    });
                });

                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });

                let markers = [];
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }
                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];
                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }

                        var latlng = place.geometry.location;
                        if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                            toastr.error('{{translate('You_cannot_pin_outside_of_the_selected_zone_polygon')}}');
                            return;
                        }

                        document.getElementById('address_latitude').value = latlng.lat();
                        document.getElementById('address_longitude').value = latlng.lng();

                        marker.setPosition(latlng);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(latlng);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete();
        });

        $('#same_as_business_info').on('change', function () {
            if ($(this).is(':checked')) {
                let companyName = $('[name="company_name"]').val();
                let companyEmail = $('[name="company_email"]').val();
                let companyPhone = $('[name="company_phone"]').val()

                $('[name="contact_person_name"]').val(companyName);
                $('[name="contact_person_email"]').val(companyEmail);
                $('[name="contact_person_phone"]').val(companyPhone);

            } else {
                $('[name="contact_person_name"]').val('');
                $('[name="contact_person_email"]').val('');
                $('[name="contact_person_phone"]').val('');
            }
        });



        document.getElementById('custom-reset').addEventListener('click', function () {
            const form = this.closest('form');

            form.querySelectorAll('input, textarea, select').forEach(el => {
                if (el.tagName === 'SELECT') {
                    Array.from(el.options).forEach(option => {
                        option.selected = option.defaultSelected;
                    });

                    if ($(el).hasClass('select2-hidden-accessible')) {
                        $(el).trigger('change');
                    }
                    return;
                }

                const defaultValue = el.defaultValue;
                const type = el.type;

                if (type === 'checkbox' || type === 'radio') {
                    if (el.checked !== el.defaultChecked) {
                        el.checked = el.defaultChecked;
                    }
                    return;
                }

                if (type === 'file') {
                    if (el.files.length > 0) {
                        el.value = '';
                        const viewerId = el.getAttribute('data-viewer-id');
                        if (viewerId) {
                            const viewer = document.getElementById(viewerId);
                            if (viewer) {
                                viewer.src = viewer.getAttribute('data-onerror-image') || '';
                            }
                        }
                    }
                    return;
                }

                if (type === 'tel') {
                    const itiInstance = window.intlTelInputGlobals?.getInstance(el);
                    if (itiInstance) {
                        itiInstance.setNumber(defaultValue || '');
                    } else if (el.value !== defaultValue) {
                        el.value = defaultValue;
                    }

                    $(el)
                        .removeClass('error is-invalid')
                        .trigger('input')
                        .trigger('change');

                    $(el).closest('.phone-picker-wrap')
                        .find('.iti, .form-control')
                        .removeClass('error is-invalid');

                    $(el).closest('.phone-picker-wrap')
                        .find('label.error, .invalid-feedback')
                        .remove();
                    return;
                }

                if (el.value !== defaultValue) {
                    el.value = defaultValue;
                }
            });
        });
        $(document).ready(function () {
            const identificationImageCount = {{ count($provider->owner->identification_image ?? []) }};
            let maxSizeReadable = "{{ readableUploadMaxFileSize('image') }}"; // "2MB"
            let maxFileSize = 2 * 1024 * 1024; // default 2MB

            if (maxSizeReadable.toLowerCase().includes('mb')) {
                maxFileSize = parseFloat(maxSizeReadable) * 1024 * 1024;
            } else if (maxSizeReadable.toLowerCase().includes('kb')) {
                maxFileSize = parseFloat(maxSizeReadable) * 1024;
            }

            let maxCount;

            if (identificationImageCount === 0) {
                maxCount = 2;
            } else if (identificationImageCount === 1) {
                maxCount = 1;
            } else if (identificationImageCount === 2) {
                maxCount = 0;
            }

            function setAcceptForAllInputs() {
                const allowedExtensions = ".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }},"

                $('#identity_images_picker input[type=file]').each(function() {
                    $(this).attr('accept', allowedExtensions);
                });
            }

            setAcceptForAllInputs();

            $("#identity_images_picker").spartanMultiImagePicker({
                fieldName: 'identity_images[]',
                maxCount: maxCount,
                allowedExt: 'png|jpg|jpeg|webp|gif',
                rowHeight: '120px',
                groupClassName: 'item',
                maxFileSize: maxFileSize,
                dropFileLabel: "{{translate('Drop_here')}}",
                placeholderImage: {
                    image: '{{asset('public/assets/admin-module/img/media/upload-placeholder2.png')}}',
                    width: '100%',
                },

                onAddRow() {
                    setAcceptForAllInputs()
                },
                onRenderedPreview: function (index) {
                    toastr.success('{{translate('Image_added')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onRemoveRow: function (index) {
                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{ translate("Please only input png|jpg|jpeg|gif|webp type file") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error('File size must be less than ' + maxSizeReadable);
                }

            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#viewer').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#customFileEg1").change(function () {
                readURL(this);
            });

        });
        $(document).on('click', '.remove-existing-image', function() {
            let imageName = $(this).data('image');
            let elementId = $(this).data('id');
            let uploadWrapper = $(this).attr('data-upload-wrapper');
            let existingImageCount = uploadWrapper
                ? $(uploadWrapper).find('[data-existing-image-item="true"]').length
                : $(this).closest('.global-multi-image-upload').find('[data-existing-image-item="true"]').length;

            if (existingImageCount <= 1) {
                toastr.error("{{ translate('At least one identity image is required') }}");
                return;
            }

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_remove_this_image')}}",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: '{{ translate('Cancel') }}',
                confirmButtonText: '{{ translate('Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ route('provider.remove-image') }}",
                        method: 'DELETE',
                        data: {
                            image_name: imageName,
                            image_type: 'identity_image'
                        },
                        success: function(response) {
                            $(`#${elementId}`).remove();
                            toastr.success("{{ translate('Image removed successfully') }}");
                            location.reload();
                        },
                        error: function(xhr) {
                            toastr.error("{{ translate('Failed to remove image') }}");
                        }
                    });
                }
            })
        });
    </script>
@endpush
