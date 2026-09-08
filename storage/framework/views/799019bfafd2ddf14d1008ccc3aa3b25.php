<?php $__env->startSection('title',translate('Zone Setup')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')); ?>"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')); ?>"/>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module/css/zone-module.css')); ?>"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title"><?php echo e(translate('Zone Setup')); ?></h2>
                    </div>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_add')): ?>
                        <div class="card zone-setup-instructions mb-30">
                            <div class="card-body p-30">
                                <form id="zone-form" action="<?php echo e(route('admin.zone.store')); ?>"
                                      enctype="multipart/form-data"
                                      method="POST"
                                      data-ff-validate novalidate>
                                    <?php echo csrf_field(); ?>
                                    <div class="row g-4 justify-content-between">
                                        <div class="col-lg-5">
                                            <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <h4 class="mb-3 c1"><?php echo e(translate('Instructions')); ?></h4>
                                                <div class="d-flex flex-column">
                                                    <p><?php echo e(translate('Create zone by clicking on the map and connecting the dots together')); ?></p>

                                                    <div class="media mb-2 gap-3 align-items-center">
                                                        <img
                                                            src="<?php echo e(asset('public/assets/admin-module/img/icons/map-drag.png')); ?>"
                                                            alt="<?php echo e(translate('image')); ?>" class="map-icon-global">
                                                        <div class="media-body">
                                                            <p><?php echo e(translate('Use this to drag the map and find the proper area')); ?></p>
                                                        </div>
                                                    </div>

                                                    <div class="media gap-3 align-items-center">
                                                        <img
                                                            src="<?php echo e(asset('public/assets/admin-module/img/icons/map-draw.png')); ?>"
                                                            alt="<?php echo e(translate('image')); ?>" class="map-icon-global">
                                                        <div class="media-body">
                                                            <p><?php echo e(translate('Click this icon to start pinning points on the map and connect them to draw a zone. Minimum 3 points are required.')); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="map-img mt-4">
                                                        <img class="dark-support"
                                                             src="<?php echo e(asset('public/assets/admin-module/img/instructions.gif')); ?>"
                                                             alt="<?php echo e(translate('image')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="bg-light rounded p-xxl-4 p-3 h-100">
                                                <?php ($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first()); ?>
                                                <?php ($default_lang = str_replace('_', '-', app()->getLocale())); ?>
                                                <?php if($language): ?>
                                                    <ul class="nav nav--tabs border-color-primary mb-4">
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link active"
                                                               href="#"
                                                               id="default-link"><?php echo e(translate('Default')); ?></a>
                                                        </li>
                                                        <?php $__currentLoopData = $language?->live_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link lang_link"
                                                                   href="#"
                                                                   id="<?php echo e($lang['code']); ?>-link"><?php echo e(get_language_name($lang['code'])); ?></a>
                                                            </li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                <?php endif; ?>

                                                <?php if($language): ?>
                                                    <div class="lang-form" id="default-form">
                                                        <?php echo $__env->make('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'name[]',
                                                            'id'          => 'default_zone_name',
                                                            'label'       => translate('Zone Name (:label)', ['label' => translate('Default')]),
                                                            'placeholder' => translate('Enter Zone Name'),
                                                            'icon'        => 'note_alt',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('name.0')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    <?php $__currentLoopData = $language?->live_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="lang-form d-none" id="<?php echo e($lang['code']); ?>-form">
                                                            <?php echo $__env->make('partials._form-field', [
                                                                'type'        => 'text',
                                                                'name'        => 'name[]',
                                                                'id'          => $lang['code'].'_zone_name',
                                                                'label'       => translate('Zone Name (:code)', ['code' => strtoupper($lang['code'])]),
                                                                'placeholder' => translate('Enter Zone Name'),
                                                                'icon'        => 'note_alt',
                                                                'maxlength'   => 191,
                                                                'charCount'   => true,
                                                                'value'       => old('name.' . ($index + 1))], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="<?php echo e($lang['code']); ?>">
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <div class="lang-form">
                                                        <?php echo $__env->make('partials._form-field', [
                                                            'type'        => 'text',
                                                            'name'        => 'name[]',
                                                            'id'          => 'default_zone_name',
                                                            'label'       => translate('Zone Name'),
                                                            'placeholder' => translate('Enter Zone Name'),
                                                            'icon'        => 'note_alt',
                                                            'required'    => true,
                                                            'maxlength'   => 191,
                                                            'charCount'   => true,
                                                            'value'       => old('name.0')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                <?php endif; ?>

                                                <input type="hidden" name="coordinates" id="coordinates" value="">
                                                <p class="mb-3 text-muted fs-12"><?php echo e(translate('Draw your zone on the map below')); ?>. <?php echo e(translate('After 3 points, click the green first point or Finish zone to close the shape.')); ?></p>

                                                <div class="zone-map-wrapper">
                                                    <div class="zone-map-controls d-flex flex-wrap align-items-center gap-2 mb-2">
                                                        <div class="zone-map-tools" role="toolbar" aria-label="<?php echo e(translate('Map tools')); ?>">
                                                            <button type="button" class="zone-map-tool" id="map-tool-drag"
                                                                    title="<?php echo e(translate('Use this to drag the map and find the proper area')); ?>">
                                                                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/map-drag.png')); ?>" alt="">
                                                            </button>
                                                            <button type="button" class="zone-map-tool active" id="map-tool-draw"
                                                                    title="<?php echo e(translate('Click this icon to start pinning points on the map and connect them to draw a zone. Minimum 3 points are required.')); ?>">
                                                                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/map-draw.png')); ?>" alt="">
                                                            </button>
                                                        </div>
                                                        <div class="zone-map-search-wrap flex-grow-1">
                                                            <span class="material-icons zone-map-search-icon">search</span>
                                                            <input id="pac-input" type="text" class="zone-map-search"
                                                                   placeholder="<?php echo e(translate('Search Here')); ?>" autocomplete="off">
                                                        </div>
                                                        <button type="button" id="zone-finish-draw" class="btn btn--primary btn-sm d-none">
                                                            <?php echo e(translate('Finish zone')); ?>

                                                        </button>
                                                        <button type="button" id="zone-clear-draw" class="btn btn--secondary btn-sm d-none">
                                                            <?php echo e(translate('Clear')); ?>

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
                                                        id="reset_btn"><?php echo e(translate('Reset')); ?></button>
                                                <button class="btn btn--primary demo_check"
                                                        type="submit"><?php echo e(translate('Submit')); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end border-bottom mx-lg-4 mb-10">
                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75"><?php echo e(translate('Total Zones')); ?>:</span>
                            <span class="title-color"><?php echo e($zones->total()); ?></span>
                        </div>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="<?php echo e(url()->current()); ?>" class="search-form search-form_style-two"  method="GET">
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input zone-search-input"
                                               value="<?php echo e($search); ?>" name="search"
                                               placeholder="<?php echo e(translate('Search Here')); ?>">
                                    </div>
                                    <button type="submit" class="btn btn--primary"><?php echo e(translate('Search')); ?></button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zone_export')): ?>
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> <?php echo e(translate('Download')); ?>

                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                       href="<?php echo e(route('admin.zone.download')); ?>?search=<?php echo e($search); ?>"><?php echo e(translate('Excel')); ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div id="ListTableContainer">
                                <?php echo $__env->make('zonemanagement::admin.partials._table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="offset" value="<?php echo e(request()->page); ?>">

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')); ?>"></script>

    <?php ($api_key=(business_config('google_map', 'third_party'))->live_values); ?>
    <script>
        "use strict";

        let map, zonePolygon = null;
        let drawVertices = [], drawMarkers = [], drawPolyline = null, mapClickListener = null;

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
                toastr.warning('<?php echo e(translate('Minimum 3 points are required')); ?>');
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
                        toastr.error('<?php echo e(translate('Location not found')); ?>');
                    }
                });
            });
        }

        function initZoneMapCreate() {
            const defaultCenter = { lat: 23.757989, lng: 90.360587 };
            map = new google.maps.Map(document.getElementById('map-canvas'), {
                zoom: 10,
                center: defaultCenter,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                gestureHandling: 'greedy',
            });

            const searchInput = document.getElementById('pac-input');
            initZoneSearch();

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    map.setCenter({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    });
                });
            }

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

            enableDrawMode();
        }

        window.initZoneMapCreate = initZoneMapCreate;
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($api_key['map_api_key_client'] ?? ''); ?>&libraries=places&callback=initZoneMapCreate"></script>

    <script>
        "use strict";

        $('#reset_btn').click(function (e) {
            e.preventDefault();
            var $form = $('#zone-form');
            $form.find('input[name="name[]"]').val('');
            clearZonePolygon();
            if (map) {
                map.setCenter({ lat: 23.757989, lng: 90.360587 });
                map.setZoom(10);
            }
            $('#pac-input').val('');
            enableDrawMode();
            if (window.FormCharCount) {
                $form.find('[data-char-count]').each(function () { window.FormCharCount.update(this); });
            }
            $form.find('.ff-field__messages label.error, .ff-field__messages .error').remove();
            if ($form.data('validator')) { $form.validate().resetForm(); }
        });

        $('#zone-form').submit(function (event) {
            if (!zonePolygon) {
                event.preventDefault();
                toastr.warning('<?php echo e(translate('Please draw your zone on the map')); ?>', {
                    CloseButton: true,
                    ProgressBar: true,
                });
            }
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

        let statusSelectedItem;
        let statusSelectedRoute;
        let statusInitialState;

        $('.nav-link').on('click', function () {
            const urlParams = new URLSearchParams($(this).attr('href').split('?')[1]);
        });

        $(document).on('change', '.status-update', function (e) {
            // Prevent default toggle behavior to avoid checkbox jumping
            e.preventDefault();
            e.stopImmediatePropagation();

            statusSelectedItem = $(this);
            statusInitialState = statusSelectedItem.prop('checked'); // Get current state (true if ON)

            // Immediately revert the checkbox visually until confirmation
            statusSelectedItem.prop('checked', !statusInitialState);

            let itemId = statusSelectedItem.data('id');
            statusSelectedRoute = '<?php echo e(route('admin.zone.status-update', ['id' => ':itemId'])); ?>'.replace(':itemId', itemId);

            let confirmationTitleText = statusInitialState
                ? '<?php echo e(translate('Are you sure to Turn On the Zone Status')); ?>?'
                : '<?php echo e(translate('Are you sure to Turn Off the Zone Status')); ?>?';

            $('.confirmation-title-text').text(confirmationTitleText);

            let confirmationDescriptionText = statusInitialState
                ? '<?php echo e(translate('Once you turn on the Zone Status, the user can find the category, services, and location in that zone')); ?>.'
                : '<?php echo e(translate('Once you turn off the Zone Status it will impact the category, services, and location finding for customers')); ?>.';

            $('.confirmation-description-text').text(confirmationDescriptionText);

            let imgSrc = statusInitialState
                ? "<?php echo e(asset('public/assets/admin-module/img/icons/status-on.png')); ?>"
                : "<?php echo e(asset('public/assets/admin-module/img/icons/status-off.png')); ?>";

            $('#confirmChangeModal img').attr('src', imgSrc);

            showModal();
        });

        $('#confirmChange').on('click', function () {
            updateStatus(statusSelectedRoute);
        });

        $('.cancel-change').on('click', function () {
            resetCheckboxState();
            hideModal();
        });

        $('#confirmChangeModal').on('hidden.bs.modal', function () {
            resetCheckboxState();
        });

        function showModal() {
            $('#confirmChangeModal').modal('show');
        }

        function hideModal() {
            $('#confirmChangeModal').modal('hide');
        }

        //  Reverts checkbox if user cancels
        function resetCheckboxState() {
            if (statusSelectedItem) {
                statusSelectedItem.prop('checked', !statusInitialState);
            }
        }

        //  AJAX update - triggers only if user confirms
        function updateStatus(route) {
            let page = $('#offset').val();
            $.ajax({
                url: route,
                type: 'POST',
                data: {_token: '<?php echo e(csrf_token()); ?>'},
                dataType: 'json',
                success: function (data) {
                    toastr.success(data.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });

                    // Update UI manually or reload table as needed
                    reloadTable(page); // Optional - if backend changes are needed
                    hideModal();
                },
                error: function (xhr) {
                    resetCheckboxState();
                    const errorMessage = xhr.responseJSON?.errors?.[0]?.message ?? xhr.responseJSON?.message ?? 'Something went wrong! Please try again.';
                    toastr.error(errorMessage, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function reloadTable(page) {
            let search = $('.zone-search-input').val();
            $.ajax({
                url: "<?php echo e(route('admin.zone.table')); ?>",
                type: "GET",
                data: {
                    search: search,
                    page: page
                },
                success: function (response) {
                    if (response.page != page) {
                        updateBrowserUrl(search, response.page);
                        $('#offset').val((response.page - 1) * <?php echo e(pagination_limit()); ?>);
                    } else {
                        $('#offset').val(response.offset);
                        updateBrowserUrl(search, page);
                    }

                    $('#totalListCount').html(response.totalCount)
                    $('#ListTableContainer').empty().html(response.view);
                },
                error: function () {
                    toastr.error('Failed to update table. Please reload the page.', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function updateBrowserUrl(search, page) {
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (page > 1) params.set('page', page);

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.new-master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ZoneManagement/Resources/views/admin/create.blade.php ENDPATH**/ ?>