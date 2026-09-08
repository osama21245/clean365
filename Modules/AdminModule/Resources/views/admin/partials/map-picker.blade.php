@php
    $mapId = $mapId ?? 'location_map_canvas';
    $searchInputId = $searchInputId ?? 'pac-input';
    $mapWrapperId = $mapWrapperId ?? $mapId . '_wrapper';
    $wrapperClass = $wrapperClass ?? '';
    $zoom = is_numeric($zoom ?? null) ? (int) $zoom : 13;
    $initialLat = is_numeric($initialLat ?? null) ? (float) $initialLat : 23.811842872190343;
    $initialLng = is_numeric($initialLng ?? null) ? (float) $initialLng : 90.356331;
    $mapConfig = [
        'zones' => $zones ?? [],
        'selectedZoneId' => $selectedZoneId ?? null,
        'mapName' => $mapId,
        'userLatitude' => $initialLat,
        'userLongitude' => $initialLng,
        'addressField' => $addressField,
        'latitudeField' => $latitudeField,
        'longitudeField' => $longitudeField,
        'zoneField' => $zoneField ?? null,
        'searchField' => $searchInputId,
        'zoom' => $zoom,
        'validationMessage' => translate('you_cannot_pin_outside_of_the_selected_zone_polygon'),
        'modalTarget' => $modalTarget ?? null];
@endphp

<div
    id="{{ $mapWrapperId }}"
    class="location_map_class js-map-picker {{ $wrapperClass }}"
    data-map-config='@json($mapConfig)'
>
    <input id="{{ $searchInputId }}" class="form-control w-auto"
           data-toggle="tooltip"
           data-placement="right"
           data-original-title="{{ translate('search_your_location_here') }}"
           type="text" placeholder="{{ translate('search_here') }}"/>
    <div id="{{ $mapId }}" class="overflow-hidden rounded canvas_class"></div>
</div>
