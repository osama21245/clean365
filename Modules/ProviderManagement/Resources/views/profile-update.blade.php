@extends('providermanagement::layouts.master')

@section('title',translate('Profile Update'))

@push('css_or_js')

    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">

    <style>
        .location_map_div {
            height: 250px;
        }

        .location_map_canvas {
            height: 100%;
        }
    </style>
@endpush

@section('content')
    @php($currentLogo = $provider->logo_full_path ?? null)
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex justify-content-between">
                        <div>
                            <h2 class="page-title">{{translate('Update Profile')}}</h2>
                        </div>
                        <?php
                        $provider_self_delete = business_config('provider_self_delete', 'provider_config')->live_values ?? 0;
                        ?>

                        @if($provider_self_delete)
                            <div class="text-danger">
                            <span
                                class="btn btn-danger gap-2 d-flex provider-delete"
                                data-provider="delete-{{auth()->user()->id}}">
                                <span class="material-icons m-0">delete</span>{{translate('Delete Account')}}
                            </span>
                                <form
                                    action="{{route('provider.delete_account',[auth()->user()->id])}}"
                                    method="post" id="delete-{{auth()->user()->id}}"
                                    class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <form id="provider-profile-update-form"
                                  action="{{ route('provider.profile_update') }}"
                                  method="post"
                                  enctype="multipart/form-data"
                                  data-ff-validate novalidate>
                                @csrf

                                <div class="row g-4 mb-30">
                                    <div class="col-lg-8">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <h4 class="c1 mb-20">{{translate('General Information')}}</h4>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'company_name',
                                                        'label'       => translate('Company / Individual Name'),
                                                        'placeholder' => translate('Enter Company or Individual Name'),
                                                        'icon'        => 'store',
                                                        'required'    => true,
                                                        'maxlength'   => 191,
                                                        'charCount'   => true,
                                                        'value'       => old('company_name', $provider->company_name),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'email',
                                                        'name'        => 'company_email',
                                                        'label'       => translate('Company Email'),
                                                        'placeholder' => translate('ex: abc@email.com'),
                                                        'icon'        => 'mail',
                                                        'required'    => true,
                                                        'autocomplete'=> 'email',
                                                        'value'       => old('company_email', $provider->company_email),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'tel',
                                                        'name'        => 'company_phone',
                                                        'id'          => 'company_phone',
                                                        'label'       => translate('Company Phone'),
                                                        'placeholder' => translate('Enter Company Phone'),
                                                        'required'    => true,
                                                        'autocomplete'=> 'tel',
                                                        'value'       => old('company_phone', $provider->company_phone),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'select',
                                                        'name'        => 'zone_id',
                                                        'id'          => 'zone_id',
                                                        'label'       => translate('Zone'),
                                                        'required'    => true,
                                                        'selectClass' => 'select-zone theme-input-style',
                                                        'optionNull'  => translate('Select Zone'),
                                                        'options'     => $zones->pluck('name', 'id')->toArray(),
                                                        'value'       => old('zone_id', $provider?->zone?->id),
                                                        'extraAttrs'  => 'data-original-zone-id="'.($provider?->zone?->id ?? '').'"',
                                                        'hint'        => translate('Update your latitude and longitude according to the selected zone'),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'textarea',
                                                        'name'        => 'company_address',
                                                        'id'          => 'address',
                                                        'label'       => translate('Company Address'),
                                                        'placeholder' => translate('Enter Company Address'),
                                                        'required'    => true,
                                                        'rows'        => 3,
                                                        'maxlength'   => 500,
                                                        'charCount'   => true,
                                                        'value'       => old('company_address', $provider->company_address),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="w-100" style="max-width: 280px;">
                                                @include('adminmodule::admin.partials._single-image-upload', [
                                                    'name'             => 'logo',
                                                    'id'               => 'provider_logo',
                                                    'title'            => translate('Company Logo'),
                                                    'required'         => false,
                                                    'image'            => $currentLogo,
                                                    'instructionRatio' => '1:1',
                                                    'height'           => null,
                                                    'showView'         => true,
                                                    'showEdit'         => true,
                                                    'showDelete'       => false])
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4 mb-30">
                                    <div class="col-lg-6">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <h4 class="c1 mb-20">{{translate('Account Information')}}</h4>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <div class="ff-field ff-field--text ff-field--has-icon mb-0" data-ff-field>
                                                        <label class="ff-field__label">
                                                            <span class="ff-field__label-text">{{translate('Email')}}</span>
                                                        </label>
                                                        <div class="ff-field__control">
                                                            <span class="material-icons ff-field__icon">mail</span>
                                                            <span class="form-control ff-field__input opacity-75"
                                                                  data-bs-toggle="tooltip" data-bs-placement="top"
                                                                  title="{{translate('Not editable')}}">
                                                                {{ $provider->owner->email }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'tel',
                                                        'name'        => 'account_phone',
                                                        'id'          => 'account_phone',
                                                        'label'       => translate('Phone'),
                                                        'placeholder' => translate('Account Phone'),
                                                        'readonly'    => true,
                                                        'disabled'    => true,
                                                        'value'       => old('account_phone', $provider->owner->phone),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'password',
                                                        'name'        => 'password',
                                                        'id'          => 'password',
                                                        'label'       => translate('Password'),
                                                        'placeholder' => translate('Enter New Password'),
                                                        'icon'        => 'lock',
                                                        'minlength'   => 8,
                                                        'hint'        => translate('Leave blank to keep current password'),
                                                        'autocomplete'=> 'new-password',
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-lg-6">
                                                    @include('partials._form-field', [
                                                        'type'        => 'password',
                                                        'name'        => 'confirm_password',
                                                        'id'          => 'confirm_password',
                                                        'label'       => translate('Confirm Password'),
                                                        'placeholder' => translate('Re-enter New Password'),
                                                        'icon'        => 'lock',
                                                        'minlength'   => 8,
                                                        'autocomplete'=> 'new-password',
                                                        'extraAttrs'  => 'data-ff-match="#password"',
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <h4 class="c1 mb-20">{{translate('Contact Person')}}</h4>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'contact_person_name',
                                                        'label'       => translate('Name'),
                                                        'placeholder' => translate('Enter Contact Person Name'),
                                                        'icon'        => 'account_circle',
                                                        'required'    => true,
                                                        'maxlength'   => 191,
                                                        'charCount'   => true,
                                                        'value'       => old('contact_person_name', $provider->contact_person_name),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'tel',
                                                        'name'        => 'contact_person_phone',
                                                        'id'          => 'contact_person_phone',
                                                        'label'       => translate('Phone'),
                                                        'placeholder' => translate('Enter Contact Person Phone'),
                                                        'required'    => true,
                                                        'autocomplete'=> 'tel',
                                                        'value'       => old('contact_person_phone', $provider->contact_person_phone),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                                <div class="col-12">
                                                    @include('partials._form-field', [
                                                        'type'        => 'email',
                                                        'name'        => 'contact_person_email',
                                                        'label'       => translate('Business Email'),
                                                        'placeholder' => translate('ex: abc@email.com'),
                                                        'icon'        => 'mail',
                                                        'required'    => true,
                                                        'autocomplete'=> 'email',
                                                        'value'       => old('contact_person_email', $provider->contact_person_email),
                                                        'wrapClass'   => 'mb-0'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light rounded p-xxl-4 p-3 mb-30">
                                    <h4 class="c1 mb-20">{{translate('Business Location')}}</h4>
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            @include('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'latitude',
                                                'id'          => 'latitude',
                                                'label'       => translate('Latitude'),
                                                'placeholder' => translate('Select from Map'),
                                                'icon'        => 'location_on',
                                                'required'    => true,
                                                'readonly'    => true,
                                                'value'       => old('latitude', $provider->coordinates['latitude'] ?? null),
                                                'extraAttrs'  => 'data-bs-toggle="tooltip" data-bs-placement="top" title="'.translate('Select from Map').'"',
                                                'wrapClass'   => 'mb-0'])
                                        </div>
                                        <div class="col-lg-6">
                                            @include('partials._form-field', [
                                                'type'        => 'text',
                                                'name'        => 'longitude',
                                                'id'          => 'longitude',
                                                'label'       => translate('Longitude'),
                                                'placeholder' => translate('Select from Map'),
                                                'icon'        => 'location_on',
                                                'required'    => true,
                                                'readonly'    => true,
                                                'value'       => old('longitude', $provider->coordinates['longitude'] ?? null),
                                                'extraAttrs'  => 'data-bs-toggle="tooltip" data-bs-placement="top" title="'.translate('Select from Map').'"',
                                                'wrapClass'   => 'mb-0'])
                                        </div>
                                        <div class="col-12">
                                            <div id="location_map_div" class="location_map_div">
                                                <input id="pac-input" class="form-control w-auto"
                                                       data-toggle="tooltip"
                                                       data-placement="right"
                                                       data-original-title="{{ translate('Search your location here') }}"
                                                       type="text" placeholder="{{ translate('Search here') }}"/>
                                                <div id="location_map_canvas"
                                                     class="overflow-hidden rounded location_map_canvas"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                    <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                    <button type="submit"
                                            class="btn btn--primary demo_check">{{translate('Update')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('/public/assets/provider-module/img/profile-delete.png')}}" alt="">
                        <h3>{{translate('Sorry you can’t delete your account !')}}!</h3>
                        <p class="fw-medium">
                            {{translate('Please complete your ongoing and accepted bookings')}}
                        </p>
                        <a href="{{route('provider.booking.list', ['booking_status' => 'accepted'])}}">
                            <button type="reset" class="btn btn--primary">{{translate('Booking Request')}}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('/public/assets/provider-module/img/profile-delete.png')}}" alt="">
                        <h3>{{translate('Sorry you can’t delete your account !')}}!</h3>
                        <p class="fw-medium">
                            {{translate('You have cash in hand, you have to pay the due to delete your account.')}}
                        </p>
                        <a href="{{route('provider.account_info', ['page_type'=>'overview'])}}">
                            <button type="reset" class="btn btn--primary">{{translate('Pay the Due')}}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="zoneChangeModal" tabindex="-1" aria-labelledby="zoneChangeModalLabel"
         aria-hidden="true">
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

    <script src="https://maps.googleapis.com/maps/api/js?key={{business_config('google_map', 'third_party')?->live_values['map_api_key_client']}}&libraries=places,geometry&v=3.45.8"></script>


    <script>
        "use strict";

        $('.provider-delete').on('click', function () {
            let provider = $(this).data('provider');
            let message = "{{(translate('want_to_delete_your_account'))}}"
            if ('{{env('APP_ENV')=='demo'}}') {
                toastr.info('This function is disable for demo mode', {
                    CloseButton: true,
                    ProgressBar: true
                });
            } else {
                if ("{{$acceptedBookings}}" != 0 || "{{$ongoingBookings}}" != 0) {
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exampleModal'))
                    modal.show();
                } else if ("{{$account->account_payable != 0}}" || "{{$account->account_receivable != 0}}") {
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('accountModal'))
                    modal.show();
                } else {
                    form_alert(provider, message)
                }
            }
        });

        $(document).ready(function () {
            $('#zone_id').select2({ placeholder: "{{translate('Select Zone')}}", width: '100%' });
        });

        const providerProfileUpdateForm = document.getElementById('provider-profile-update-form');
        const zoneSelect = providerProfileUpdateForm?.querySelector('select[name="zone_id"]');
        const zoneChangeModalElement = document.getElementById('zoneChangeModal');
        const zoneChangeModal = zoneChangeModalElement ? bootstrap.Modal.getOrCreateInstance(zoneChangeModalElement) : null;
        const confirmZoneChangeButton = document.getElementById('confirmZoneChange');
        let zoneChangeConfirmed = false;

        function isZoneChangedPendingConfirm() {
            if (!zoneSelect || !zoneChangeModal) return false;
            const originalZoneId = zoneSelect.dataset.originalZoneId || '';
            const selectedZoneId = zoneSelect.value || '';
            return !zoneChangeConfirmed && originalZoneId && selectedZoneId && originalZoneId !== selectedZoneId;
        }

        if (confirmZoneChangeButton && zoneChangeModal && providerProfileUpdateForm) {
            confirmZoneChangeButton.addEventListener('click', function () {
                zoneChangeConfirmed = true;
                zoneChangeModal.hide();
                providerProfileUpdateForm.requestSubmit();
            });
        }

        if (zoneSelect) {
            zoneSelect.addEventListener('change', function () {
                zoneChangeConfirmed = false;
            });
        }

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        $(document).ready(function () {
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
                    lat:{{$provider->coordinates['latitude'] ?? 23.811842872190343}},
                    lng:{{$provider->coordinates['longitude'] ?? 90.356331}}
                };
                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: {
                        lat:{{$provider->coordinates['latitude'] ?? 23.811842872190343}},
                        lng:{{$provider->coordinates['longitude'] ?? 90.356331}}
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
                google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);

                    if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                        toastr.error('{{translate('you_cannot_pin_outside_of_the_selected_zone_polygon')}}');
                        return;
                    }

                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];


                    if (document.getElementById('address')) {
                        geocoder.geocode({
                            'latLng': latlng
                        }, function (results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                if (results[1]) {
                                    document.getElementById('address').value = results[1].formatted_address;
                                }
                            }
                        });
                    }
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
                            return;
                        }

                        var latlng = place.geometry.location;
                        if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                            toastr.error('{{translate('You_cannot_pin_outside_of_the_selected_zone_polygon')}}');
                            return;
                        }

                        document.getElementById('latitude').value = latlng.lat();
                        document.getElementById('longitude').value = latlng.lng();

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

        (function () {
            var $form = $('#provider-profile-update-form');
            if (!$form.length) return;

            var originalLogo       = @json($currentLogo ?? '');
            var originalLatitude   = @json(old('latitude', $provider->coordinates['latitude'] ?? null));
            var originalLongitude  = @json(old('longitude', $provider->coordinates['longitude'] ?? null));
            var originalCompanyPhone        = @json(old('company_phone', $provider->company_phone ?? ''));
            var originalContactPersonPhone  = @json(old('contact_person_phone', $provider->contact_person_phone ?? ''));

            function setLogoPreview(src) {
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

            function resetIntlTel(inputId, original) {
                var el = document.querySelector('#' + inputId);
                if (el && window.intlTelInputGlobals) {
                    var iti = window.intlTelInputGlobals.getInstance(el);
                    if (iti) { iti.setNumber(original || ''); }
                }
            }

            $form.on('reset', function () {
                setTimeout(function () {
                    setLogoPreview(originalLogo);
                    resetIntlTel('company_phone', originalCompanyPhone);
                    resetIntlTel('contact_person_phone', originalContactPersonPhone);

                    $('#latitude').val(originalLatitude || '');
                    $('#longitude').val(originalLongitude || '');

                    $('#zone_id').val(zoneSelect?.dataset?.originalZoneId || '').trigger('change');

                    if (window.FormCharCount) {
                        $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
                    }

                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                    if ($form.data('validator')) { $form.validate().resetForm(); }
                }, 0);
            });

            if (window.FormValidator) {
                FormValidator.register('#provider-profile-update-form', {
                    submitHandler: function (form) {
                        if (isZoneChangedPendingConfirm()) {
                            zoneChangeModal.show();
                            return false;
                        }
                        var $btn = $(form).find('button[type="submit"]');
                        if ($btn.prop('disabled')) return false;
                        if (!$btn.data('ffOriginalHtml')) { $btn.data('ffOriginalHtml', $btn.html()); }
                        $btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>' +
                            '{{ translate("Updating...") }}'
                        );
                        form.submit();
                    }
                });
            }
        })();

        $('.__right-eye').on('click', function () {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active')
                $(this).find('i').removeClass('tio-invisible')
                $(this).find('i').addClass('tio-hidden-outlined')
                $(this).siblings('input').attr('type', 'password')
            } else {
                $(this).addClass('active')
                $(this).siblings('input').attr('type', 'text')


                $(this).find('i').addClass('tio-invisible')
                $(this).find('i').removeClass('tio-hidden-outlined')
            }
        })

    </script>
@endpush
