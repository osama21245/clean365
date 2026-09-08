@extends('adminmodule::layouts.master')

@section('title',translate('Zone Update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/zone-module.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Zone Update')}}</h2>
                    </div>

                    <div class="card zone-setup-instructions mb-30">
                        <div class="card-body p-30">
                            @can('zone_update')
                            <form id="zone-form" action="{{route('admin.zone.update',[$zone->id])}}" enctype="multipart/form-data"
                                  method="POST"
                                  data-ff-validate novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row g-4 justify-content-between">
                                    <div class="col-lg-5">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            <h4 class="mb-3 c1">{{translate('Instructions')}}</h4>
                                            <div class="d-flex flex-column">
                                                <p>{{translate('Create zone by clicking on the map and connecting the dots together')}}</p>

                                                <div class="media mb-2 gap-3 align-items-center">
                                                    <img
                                                        src="{{asset('public/assets/admin-module/img/icons/map-drag.png')}}"
                                                        alt="{{ translate('image') }}" class="map-icon-global">
                                                    <div class="media-body">
                                                        <p>{{translate('Use this to drag the map and find the proper area')}}</p>
                                                    </div>
                                                </div>

                                                <div class="media gap-3 align-items-center">
                                                    <img
                                                        src="{{asset('public/assets/admin-module/img/icons/map-draw.png')}}"
                                                        alt="{{ translate('image') }}" class="map-icon-global">
                                                    <div class="media-body">
                                                        <p>
                                                            {{translate('Click this icon to start pinning points on the map and connect them to draw a zone')}} {{ translate('Minimum 3 points are required') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="map-img mt-4">
                                                    <img src="{{asset('public/assets/admin-module/img/instructions.gif')}}"
                                                         alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                            @php
                                                $language = \Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first();
                                                $default_lang = str_replace('_', '-', app()->getLocale());
                                                $zoneNameTranslations = collect($zone['translations'] ?? [])
                                                    ->where('key', 'zone_name')
                                                    ->pluck('value', 'locale')
                                                    ->toArray();
                                                $existingCoords = '';
                                                $coordList = $zone->coordinates[0]->toArray()['coordinates'] ?? [];
                                                foreach ($coordList as $key => $coords) {
                                                    if (count($coordList) != $key + 1) {
                                                        if ($key != 0) $existingCoords .= ',';
                                                        $existingCoords .= '(' . $coords[1] . ',' . $coords[0] . ')';
                                                    }
                                                }
                                            @endphp
                                            @if($language)
                                                <ul class="nav nav--tabs border-color-primary mb-4">
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link active"
                                                           href="#"
                                                           id="default-link">{{translate('Default')}}</a>
                                                    </li>
                                                    @foreach ($language?->live_values as $lang)
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link"
                                                               href="#"
                                                               id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            @if($language)
                                                <div class="lang-form" id="default-form">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'name[]',
                                                        'id'          => 'default_zone_name',
                                                        'label'       => translate('Zone Name (:label)', ['label' => translate('Default')]),
                                                        'placeholder' => translate('Enter Zone Name'),
                                                        'icon'        => 'note_alt',
                                                        'required'    => true,
                                                        'maxlength'   => 191,
                                                        'charCount'   => true,
                                                        'value'       => old('name.0', $zone?->getRawOriginal('name'))])
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $index => $lang)
                                                    <div class="lang-form d-none" id="{{$lang['code']}}-form">
                                                        @include('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'name[]',
                                                            'id'          => $lang['code'].'_zone_name',
                                                            'label'       => translate('Zone Name (:code)', ['code' => strtoupper($lang['code'])]),
                                                            'placeholder' => translate('Enter Zone Name'),
                                                            'icon'        => 'note_alt',
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('name.' . ($index + 1), $zoneNameTranslations[$lang['code']] ?? '')])
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="lang-form">
                                                    @include('partials._form-field', [
                                                        'type'        => 'text',
                                                        'name'        => 'name[]',
                                                        'id'          => 'default_zone_name',
                                                        'label'       => translate('Zone Name'),
                                                        'placeholder' => translate('Enter Zone Name'),
                                                        'icon'        => 'note_alt',
                                                        'required'    => true,
                                                        'maxlength'   => 191,
                                                        'charCount'   => true,
                                                        'value'       => old('name.0', $zone->name)])
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif

                                            <input type="hidden" name="coordinates" id="coordinates" value="{{ $existingCoords }}">
                                            <p class="mb-3 text-muted fs-12">{{ translate('Draw your zone on the map below') }}</p>

                                            <div class="zone-map-wrapper">
                                                <div class="zone-map-controls d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <div class="zone-map-tools" role="toolbar" aria-label="{{ translate('Map tools') }}">
                                                        <button type="button" class="zone-map-tool active" id="map-tool-drag"
                                                                title="{{ translate('Use this to drag the map and find the proper area') }}">
                                                            <img src="{{asset('public/assets/admin-module/img/icons/map-drag.png')}}" alt="">
                                                        </button>
                                                        <button type="button" class="zone-map-tool" id="map-tool-draw"
                                                                title="{{ translate('Click this icon to start pinning points on the map and connect them to draw a zone') }}">
                                                            <img src="{{asset('public/assets/admin-module/img/icons/map-draw.png')}}" alt="">
                                                        </button>
                                                    </div>
                                                    <div class="zone-map-search-wrap flex-grow-1">
                                                        <span class="material-icons zone-map-search-icon">search</span>
                                                        <input id="pac-input" type="text" class="zone-map-search"
                                                               placeholder="{{ translate('Search Here') }}" autocomplete="off">
                                                    </div>
                                                    <button type="button" id="zone-finish-draw" class="btn btn--primary btn-sm d-none">
                                                        {{ translate('Finish zone') }}
                                                    </button>
                                                    <button type="button" id="zone-clear-draw" class="btn btn--secondary btn-sm d-none">
                                                        {{ translate('Clear') }}
                                                    </button>
                                                </div>
                                                <div class="map-warper rounded overflow-hidden">
                                                    <div class="map_canvas" id="map-canvas"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                            <button class="btn btn--secondary" type="reset"
                                                    id="reset_btn">{{translate('Reset')}}</button>
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
    @php($api_key=(business_config('google_map', 'third_party'))->live_values)
    <script>
        "use strict";

        let map, zonePolygon = null, existingZonePolygon = null;
        let drawVertices = [], drawMarkers = [], drawPolyline = null, mapClickListener = null;
        let polygons = [], originalName = {}, originalCoordinates = '';

        function pathToCoordsString(path) {
            const parts = [];
            for (let i = 0; i < path.getLength(); i++) {
                const ll = path.getAt(i);
                parts.push('(' + ll.lat() + ',' + ll.lng() + ')');
            }
            return parts.join(',');
        }

        function updateCoordsFromPolygon() {
            if (!zonePolygon) return;
            $('#coordinates').val(pathToCoordsString(zonePolygon.getPath()));
        }

        function vertexIcon(isFirst, canClose) {
            return {
                path: google.maps.SymbolPath.CIRCLE,
                scale: (isFirst && canClose) ? 10 : 7,
                fillColor: (isFirst && canClose) ? '#34a853' : '#1967d2',
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 2,
            };
        }

        function refreshDrawPreview() {
            if (drawPolyline) drawPolyline.setMap(null);
            if (drawVertices.length >= 2) {
                drawPolyline = new google.maps.Polyline({
                    path: drawVertices,
                    strokeColor: '#1967d2',
                    strokeOpacity: 0.9,
                    strokeWeight: 2,
                    map: map,
                });
            } else {
                drawPolyline = null;
            }

            const canClose = drawVertices.length >= 3;
            drawMarkers.forEach(function (marker, index) {
                marker.setIcon(vertexIcon(index === 0, canClose && index === 0));
            });

            if (canClose) {
                $('#zone-finish-draw, #zone-clear-draw').removeClass('d-none');
            } else {
                $('#zone-finish-draw, #zone-clear-draw').addClass('d-none');
            }
        }

        function clearDrawingPreview() {
            drawVertices = [];
            drawMarkers.forEach(function (m) { m.setMap(null); });
            drawMarkers = [];
            if (drawPolyline) { drawPolyline.setMap(null); drawPolyline = null; }
            $('#zone-finish-draw, #zone-clear-draw').addClass('d-none');
        }

        function clearZonePolygon() {
            if (zonePolygon) { zonePolygon.setMap(null); zonePolygon = null; }
            $('#coordinates').val('');
            clearDrawingPreview();
        }

        function isNearFirstPoint(latLng) {
            if (drawVertices.length < 3) return false;
            const first = drawVertices[0];
            return Math.abs(first.lat() - latLng.lat()) < 0.0003 &&
                Math.abs(first.lng() - latLng.lng()) < 0.0003;
        }

        function addVertex(latLng) {
            if (existingZonePolygon) {
                existingZonePolygon.setMap(null);
                existingZonePolygon = null;
            }

            if (isNearFirstPoint(latLng)) {
                finishPolygon();
                return;
            }

            drawVertices.push(latLng);
            const vertexIndex = drawVertices.length - 1;
            const marker = new google.maps.Marker({
                position: latLng,
                map: map,
                icon: vertexIcon(vertexIndex === 0, false),
            });

            marker.addListener('click', function () {
                if (vertexIndex === 0 && drawVertices.length >= 3) {
                    finishPolygon();
                }
            });

            drawMarkers.push(marker);
            refreshDrawPreview();
        }

        function finishPolygon() {
            if (drawVertices.length < 3) {
                toastr.warning('{{ translate('Minimum 3 points are required') }}');
                return;
            }

            const vertices = drawVertices.slice();
            if (zonePolygon) zonePolygon.setMap(null);
            clearDrawingPreview();

            zonePolygon = new google.maps.Polygon({
                paths: vertices,
                strokeColor: '#1967d2',
                fillColor: '#1967d2',
                fillOpacity: 0.25,
                strokeWeight: 2,
                editable: true,
                map: map,
            });
            $('#coordinates').val(pathToCoordsString(zonePolygon.getPath()));
            const path = zonePolygon.getPath();
            google.maps.event.addListener(path, 'set_at', updateCoordsFromPolygon);
            google.maps.event.addListener(path, 'insert_at', updateCoordsFromPolygon);
            enableDragMode();
        }

        function setMapToolActive(tool) {
            $('.zone-map-tool').removeClass('active');
            $('#map-tool-' + tool).addClass('active');
        }

        function enableDrawMode() {
            if (!map) return;
            map.setOptions({ draggable: false });
            if (mapClickListener) google.maps.event.removeListener(mapClickListener);
            mapClickListener = map.addListener('click', function (e) { addVertex(e.latLng); });
            setMapToolActive('draw');
            $('#map-canvas').css('cursor', 'crosshair');
        }

        function enableDragMode() {
            if (!map) return;
            map.setOptions({ draggable: true });
            if (mapClickListener) {
                google.maps.event.removeListener(mapClickListener);
                mapClickListener = null;
            }
            setMapToolActive('drag');
            $('#map-canvas').css('cursor', '');
        }

        function goToGeometry(geometry) {
            if (!geometry) return;
            if (geometry.viewport) {
                map.fitBounds(geometry.viewport);
            } else if (geometry.location) {
                map.setCenter(geometry.location);
                map.setZoom(15);
            }
        }

        function initZoneSearch() {
            const input = document.getElementById('pac-input');
            const geocoder = new google.maps.Geocoder();

            if (google.maps.places && google.maps.places.Autocomplete) {
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    fields: ['geometry', 'formatted_address', 'name'],
                });
                autocomplete.bindTo('bounds', map);
                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    if (!place || !place.geometry) return;
                    goToGeometry(place.geometry);
                });
            }

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                const query = input.value.trim();
                if (!query) return;
                geocoder.geocode({ address: query }, function (results, status) {
                    if (status === 'OK' && results[0]) {
                        goToGeometry(results[0].geometry);
                    } else {
                        toastr.error('{{ translate('Location not found') }}');
                    }
                });
            });
        }

        function initZoneMapEdit() {
            const center = { lat: {{trim(explode(' ',$zone->center)[1], 'POINT()')}}, lng: {{trim(explode(' ',$zone->center)[0], 'POINT()')}} };
            map = new google.maps.Map(document.getElementById('map-canvas'), {
                zoom: 13,
                center: center,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                gestureHandling: 'greedy',
            });

            const polygonCoords = [
                @foreach($area['coordinates'] as $coords)
                    { lat: {{$coords[1]}}, lng: {{$coords[0]}} },
                @endforeach
            ];

            existingZonePolygon = new google.maps.Polygon({
                paths: polygonCoords,
                strokeColor: '#050df2',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#050df2',
                fillOpacity: 0.1,
            });
            existingZonePolygon.setMap(map);

            const bounds = new google.maps.LatLngBounds();
            polygonCoords.forEach(function (c) { bounds.extend(c); });
            map.fitBounds(bounds);

            initZoneSearch();

            $('#map-tool-draw').on('click', function (e) {
                e.preventDefault();
                enableDrawMode();
            });
            $('#map-tool-drag').on('click', function (e) {
                e.preventDefault();
                enableDragMode();
            });
            $('#zone-finish-draw').on('click', function (e) {
                e.preventDefault();
                finishPolygon();
            });
            $('#zone-clear-draw').on('click', function (e) {
                e.preventDefault();
                clearZonePolygon();
                enableDrawMode();
            });

            enableDragMode();

            set_all_zones();
        }

        function set_all_zones() {
            $.get({
                url: '{{route('admin.zone.get-active-zones',[$zone->id])}}',
                dataType: 'json',
                success: function (data) {
                    for (var i = 0; i < data.length; i++) {
                        polygons.push(new google.maps.Polygon({
                            paths: data[i],
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.1,
                        }));
                        polygons[i].setMap(map);
                    }
                },
            });
        }

        window.initZoneMapEdit = initZoneMapEdit;
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{$api_key['map_api_key_client'] ?? ''}}&libraries=places&callback=initZoneMapEdit"></script>

    <script>
        "use strict";

        $(document).ready(function () {
            $('#zone-form input[name="name[]"]').each(function () {
                originalName[this.id] = $(this).val();
            });
            originalCoordinates = $('#coordinates').val();
        });

        $('#reset_btn').click(function (e) {
            e.preventDefault();
            var $form = $('#zone-form');
            $form.find('input[name="name[]"]').each(function () {
                $(this).val(originalName[this.id] || '');
            });
            $('#coordinates').val(originalCoordinates);
            clearZonePolygon();
            if (existingZonePolygon) existingZonePolygon.setMap(map);
            enableDragMode();
            if (window.FormCharCount) {
                $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
            }
            $form.find('.ff-field__messages label.error, .ff-field__messages .error').remove();
            if ($form.data('validator')) { $form.validate().resetForm(); }
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
    </script>
@endpush
