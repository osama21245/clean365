$(document).ready(function () {
    function normalizeZones(zones) {
        const zonePolygons = {};

        if (!Array.isArray(zones)) {
            return zonePolygons;
        }

        zones.forEach(zone => {
            const coordinates = [];
            let parsed = zone?.coordinates ?? null;

            if (typeof parsed === 'string') {
                try {
                    parsed = JSON.parse(parsed);
                } catch (error) {
                    parsed = null;
                }
            }

            const polygonRings = Array.isArray(parsed?.coordinates) ? parsed.coordinates : [];
            const points = Array.isArray(polygonRings[0]) ? polygonRings[0] : [];

            points.forEach(point => {
                const lat = Number.parseFloat(point?.[1]);
                const lng = Number.parseFloat(point?.[0]);

                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    coordinates.push({lat, lng});
                }
            });

            zonePolygons[zone.id] = coordinates;
        });

        return zonePolygons;
    }

    function initMapPicker(wrapper) {
        const config = $(wrapper).data('map-config');
        if (!config || wrapper.dataset.mapPickerInitialized === 'true') {
            return;
        }

        const mapElement = document.getElementById(config.mapName);
        const latitudeInput = document.getElementById(config.latitudeField);
        const longitudeInput = document.getElementById(config.longitudeField);
        const addressInput = document.getElementById(config.addressField);
        const searchInput = document.getElementById(config.searchField);
        const zoneSelector = config.zoneField ? $(`select[name="${config.zoneField}"]`) : $();

        if (!mapElement || !latitudeInput || !longitudeInput || !searchInput) {
            return;
        }

        const parsedLatitude = Number.parseFloat(config.userLatitude);
        const parsedLongitude = Number.parseFloat(config.userLongitude);
        const parsedZoom = Number.parseInt(config.zoom, 10);
        const initialLatLng = {
            lat: Number.isFinite(parsedLatitude) ? parsedLatitude : 23.811842872190343,
            lng: Number.isFinite(parsedLongitude) ? parsedLongitude : 90.356331
        };
        const zonePolygons = normalizeZones(config.zones);
        const activeZoom = Number.isFinite(parsedZoom) ? parsedZoom : 13;
        let currentPolygon = null;

        mapElement.innerHTML = '';

        const map = new google.maps.Map(mapElement, {
            center: initialLatLng,
            zoom: activeZoom,
            mapTypeId: "roadmap",
        });
        const marker = new google.maps.Marker({
            position: initialLatLng,
            map: map,
        });

        wrapper.dataset.mapPickerInitialized = 'true';

        function keepConfiguredZoom(bounds) {
            if (!bounds.isEmpty()) {
                map.setCenter(bounds.getCenter());
            }
            map.setZoom(activeZoom);
        }

        function drawPolygon(zoneId, keepZoom = true) {
            if (currentPolygon) {
                currentPolygon.setMap(null);
            }

            if (!zonePolygons[zoneId] || zonePolygons[zoneId].length === 0) {
                currentPolygon = null;
                return;
            }

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

            const bounds = new google.maps.LatLngBounds();
            for (let i = 0; i < currentPolygon.getPath().getLength(); i++) {
                bounds.extend(currentPolygon.getPath().getAt(i));
            }

            if (keepZoom) {
                keepConfiguredZoom(bounds);
            }
        }

        const selectedZone = zoneSelector.length > 0 ? zoneSelector.val() : config.selectedZoneId;
        if (selectedZone) {
            drawPolygon(selectedZone, false);
        }

        if (zoneSelector.length > 0) {
            zoneSelector.off(`change.mapPicker.${config.mapName}`).on(`change.mapPicker.${config.mapName}`, function () {
                drawPolygon($(this).val(), true);
            });
        }

        const geocoder = new google.maps.Geocoder();
        google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
            const coordinates = mapsMouseEvent.latLng.toJSON();
            const latlng = new google.maps.LatLng(coordinates.lat, coordinates.lng);

            if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                toastr.error(config.validationMessage);
                return;
            }

            marker.setPosition(latlng);
            map.panTo(latlng);
            map.setZoom(activeZoom);

            latitudeInput.value = coordinates.lat;
            longitudeInput.value = coordinates.lng;

            if (addressInput) {
                geocoder.geocode({latLng: latlng}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK && results[1]) {
                        addressInput.value = results[1].formatted_address;
                    }
                });
            }
        });

        const searchBox = new google.maps.places.SearchBox(searchInput);
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(searchInput);
        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });
        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();

            if (!places || places.length === 0) {
                return;
            }

            const bounds = new google.maps.LatLngBounds();
            places.forEach((place) => {
                if (!place.geometry || !place.geometry.location) {
                    return;
                }

                const latlng = place.geometry.location;
                if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                    toastr.error(config.validationMessage);
                    return;
                }

                latitudeInput.value = latlng.lat();
                longitudeInput.value = latlng.lng();
                marker.setPosition(latlng);

                if (addressInput && place.formatted_address) {
                    addressInput.value = place.formatted_address;
                }

                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(latlng);
                }
            });

            keepConfiguredZoom(bounds);
        });
    }

    function bootMapPickers(retryCount = 0) {
        if (typeof google === 'undefined' || !google.maps || !google.maps.places || !google.maps.geometry) {
            if (retryCount < 20) {
                setTimeout(function () {
                    bootMapPickers(retryCount + 1);
                }, 250);
            }
            return;
        }

        $('.js-map-picker').each(function () {
            initMapPicker(this);
        });
    }

    function bindModalMapPickers() {
        $('.js-map-picker').each(function () {
            const wrapper = this;
            const config = $(wrapper).data('map-config');

            if (!config?.modalTarget) {
                return;
            }

            $(config.modalTarget).off(`shown.bs.modal.mapPicker.${config.mapName}`).on(`shown.bs.modal.mapPicker.${config.mapName}`, function () {
                wrapper.dataset.mapPickerInitialized = 'false';
                bootMapPickers();
            });
        });
    }

    setTimeout(bootMapPickers, 250);
    bindModalMapPickers();

    $(window).on('load', function () {
        setTimeout(bootMapPickers, 500);
    });
});
