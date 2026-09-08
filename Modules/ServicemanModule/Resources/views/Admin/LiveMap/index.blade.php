@extends('adminmodule::layouts.master')

@section('title', translate('Live Map'))

@push('css_or_js')
    <style>
        /* Clean365 brand — same layout as Tashyik technician-map */
        .gm-style-iw { border-radius: 16px !important; padding: 0 !important; overflow: hidden !important; }
        .gm-style-iw-d { overflow: auto !important; padding: 0 !important; }
        .gm-style-iw-c { padding: 0 !important; border-radius: 16px !important; box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important; max-height: none !important; }
        .gm-style .gm-style-iw-tc::after { background: #fff !important; }
        div.gm-style-iw-chr { position: absolute; top: 10px; left: 10px; right: auto; z-index: 10; border-radius: 50%; opacity: 0.7; }

        .lm-wrap {
            margin: -1.25rem -1.25rem 0;
            padding: 1.25rem;
            background: #f8fafc;
            min-height: calc(100vh - 110px);
        }

        .lm-wrap.map-fullscreen .lm-map-shell {
            position: fixed !important;
            inset: 0;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 99999;
            border-radius: 0;
            display: flex;
            flex-direction: column;
        }
        .lm-wrap.map-fullscreen #field-live-map {
            flex: 1;
            height: 100% !important;
            border-radius: 0;
            min-height: 0;
        }

        #field-live-map {
            height: calc(100vh - 250px);
            min-height: 520px;
            width: 100%;
            border-radius: 0 0 1rem 1rem;
        }

        .popup-card { direction: rtl; font-family: inherit; min-width: 260px; }
        .popup-header {
            padding: 16px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .popup-avatar {
            width: 48px; height: 48px; border-radius: 50%; object-fit: cover;
            border: 2px solid #e5e7eb; flex-shrink: 0; background: #E6F7F5;
        }
        .popup-name { font-weight: 700; font-size: 15px; color: #0F2C59; margin-bottom: 2px; }
        .popup-type { font-size: 12px; color: #9ca3af; }
        .popup-badge {
            display: inline-block; padding: 3px 10px; border-radius: 50px;
            font-size: 11px; font-weight: 600; color: #fff;
        }
        .popup-badge.online_available { background: #22c55e; }
        .popup-badge.online_busy { background: #f97316; }
        .popup-badge.offline { background: #9ca3af; }

        .popup-body { padding: 12px 16px; }
        .popup-row {
            display: flex; align-items: center; gap: 8px;
            padding: 5px 0; font-size: 13px; color: #6b7280;
        }
        .popup-row svg { width: 16px; height: 16px; color: #9ca3af; flex-shrink: 0; }

        .popup-actions {
            display: flex; gap: 8px; padding: 12px 16px;
            border-top: 1px solid #f3f4f6; background: #f9fafb;
        }
        .popup-btn {
            flex: 1; display: flex; align-items: center; justify-content: center;
            gap: 6px; padding: 8px; border-radius: 10px; font-size: 12px;
            font-weight: 600; text-decoration: none !important; transition: all 0.2s; cursor: pointer;
        }
        .popup-btn-call { background: #E6F7F5; color: #008080; }
        .popup-btn-call:hover { background: #d0efeb; color: #006666; }
        .popup-btn-whatsapp { background: #f0fdf4; color: #16a34a; }
        .popup-btn-whatsapp:hover { background: #dcfce7; color: #15803d; }

        .lm-main { display: flex; gap: 1rem; position: relative; }
        @media (max-width: 1024px) {
            .side-panel { display: none; }
            .lm-main { display: block; }
        }

        .side-panel {
            width: 340px; flex-shrink: 0; display: flex; flex-direction: column; gap: 0;
            background: #fff; border-radius: 1rem; border: 1px solid #e5e7eb;
            overflow: hidden; max-height: calc(100vh - 220px); min-height: 500px;
            order: 2;
        }
        .lm-map-shell {
            flex: 1; min-width: 0; background: #fff; border-radius: 1rem;
            border: 1px solid #e5e7eb; overflow: hidden; position: relative;
            order: 1;
        }

        .side-panel-header { padding: 16px; border-bottom: 1px solid #f3f4f6; }
        .side-panel-list { flex: 1; overflow-y: auto; scrollbar-width: thin; }
        .side-panel-list::-webkit-scrollbar { width: 4px; }
        .side-panel-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

        .tech-list-item, .city-list-item {
            display: flex; align-items: center; gap: 10px; padding: 12px 16px;
            cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f9fafb;
        }
        .tech-list-item:hover, .city-list-item:hover { background: #E6F7F5; }

        .tech-list-avatar-wrap { position: relative; flex-shrink: 0; }
        .tech-list-avatar {
            width: 36px; height: 36px; border-radius: 50%; object-fit: cover; display: block;
            background: #E6F7F5;
        }
        .tech-list-avatar-fallback {
            width: 36px; height: 36px; border-radius: 50%;
            background: #E6F7F5; color: #008080; display: flex; align-items: center;
            justify-content: center; font-weight: 800; font-size: 13px;
        }
        .tech-list-status {
            width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
            border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
            position: absolute; bottom: -1px; inset-inline-end: -1px;
        }
        .tech-list-status.online_available { background: #22c55e; }
        .tech-list-status.online_busy { background: #f97316; }
        .tech-list-status.offline { background: #9ca3af; }

        .panel-tabs {
            display: flex; border-bottom: 1px solid #e5e7eb; background: #f9fafb;
        }
        .panel-tab {
            flex: 1; padding: 12px 0; text-align: center; font-size: 13px;
            font-weight: 600; color: #6b7280; border-bottom: 2px solid transparent;
            cursor: pointer; transition: all 0.2s; background: transparent; border-top: 0; border-left: 0; border-right: 0;
        }
        .panel-tab:hover { color: #0F2C59; }
        .panel-tab.active {
            color: #008080; border-bottom-color: #008080; background: #fff;
        }

        .stat-mini {
            display: flex; align-items: center; gap: 8px; padding: 10px 12px;
            border-radius: 12px; transition: all 0.2s;
        }
        .stat-mini:hover { transform: translateY(-1px); }
        .stat-mini-icon {
            width: 36px; height: 36px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; flex-shrink: 0;
        }
        .stat-mini-icon .material-icons { font-size: 18px; }
        .stat-mini .value { font-size: 1.125rem; font-weight: 800; color: #0F2C59; line-height: 1.1; margin: 0; }
        .stat-mini .label { font-size: 10px; color: #6b7280; font-weight: 600; margin: 0; line-height: 1.2; }

        .stat-mini--available { background: linear-gradient(to bottom right, #ecfdf5, #d1fae5); }
        .stat-mini--available .stat-mini-icon { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
        .stat-mini--busy { background: linear-gradient(to bottom right, #fff7ed, #ffedd5); }
        .stat-mini--busy .stat-mini-icon { background: rgba(249, 115, 22, 0.12); color: #f97316; }
        .stat-mini--offline { background: linear-gradient(to bottom right, #f9fafb, #f1f5f9); }
        .stat-mini--offline .stat-mini-icon { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
        .stat-mini--total { background: linear-gradient(to bottom right, #E6F7F5, #d0efeb); }
        .stat-mini--total .stat-mini-icon { background: rgba(0, 128, 128, 0.12); color: #008080; }

        .map-control-btn {
            width: 36px; height: 36px; border-radius: 10px; background: #fff;
            border: 1px solid #e5e7eb; display: flex; align-items: center;
            justify-content: center; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); color: #0F2C59;
        }
        .map-control-btn:hover { background: #E6F7F5; transform: scale(1.05); color: #008080; }
        .map-control-btn.active { color: #008080; border-color: #008080; background: #E6F7F5; }
        .map-controls {
            position: absolute; top: 12px; inset-inline-end: 12px; z-index: 5;
            display: flex; flex-direction: column; gap: 8px;
        }

        .live-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
            animation: lm-pulse 1.5s infinite;
        }
        #socket-status[data-state="off"] .live-dot { background: #ef4444; box-shadow: none; animation: none; }
        #socket-status[data-state="pending"] .live-dot { background: #eab308; }

        @keyframes lm-pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5); }
            70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .lm-filter-select {
            width: 100%; border-radius: 10px; border: 1px solid #e5e7eb;
            background: #fff; padding: 9px 12px; font-size: 13px; font-weight: 600;
            color: #0F2C59; outline: none;
        }
        .lm-filter-select:focus {
            border-color: #008080; box-shadow: 0 0 0 3px rgba(0,128,128,0.12);
        }

        .lm-empty {
            text-align: center; color: #94a3b8; font-size: 13px; font-weight: 600;
            padding: 32px 16px;
        }

        .city-list-item .count {
            background: #E6F7F5; color: #008080; font-weight: 800; font-size: 12px;
            border-radius: 999px; padding: 4px 10px; margin-inline-start: auto;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="lm-wrap" id="lm-wrap">
            {{-- Header (Tashyik layout + Clean365 colors) --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
                         style="width:40px;height:40px;background:linear-gradient(135deg,#008080,#006666);color:#fff;">
                        <span class="material-icons" style="font-size:22px">local_shipping</span>
                    </div>
                    <div>
                        <h1 class="mb-0 fw-bold" style="font-size:1.25rem;color:#0F2C59;">{{ translate('Technicians Map') }}</h1>
                        <p class="mb-0" style="font-size:12px;color:#9ca3af;">{{ translate('Track technicians locations in real-time') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="position-relative">
                        <span class="material-icons position-absolute"
                              style="font-size:18px;color:#9ca3af;inset-inline-start:12px;top:50%;transform:translateY(-50%);">search</span>
                        <input id="filter-search" type="search"
                               placeholder="{{ translate('Search by name or mobile...') }}"
                               class="form-control"
                               style="width:min(260px,70vw);border-radius:12px;border:1px solid #e5e7eb;padding:10px 14px 10px 40px;font-size:12px;font-weight:600;">
                    </div>

                    <div id="socket-status" data-state="pending"
                         class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 border"
                         style="font-size:12px;font-weight:700;color:#6b7280;background:#f9fafb;border-color:#e5e7eb!important;">
                        <div class="live-dot"></div>
                        <span id="socket-status-text">LIVE</span>
                    </div>

                    <button type="button" id="lm-export-excel"
                            style="display:inline-flex;align-items:center;gap:8px;padding:9px 20px;border-radius:12px;font-size:13px;font-weight:700;color:#fff;background:linear-gradient(135deg,#008080,#006666);box-shadow:0 4px 14px rgba(0,128,128,0.35);border:none;cursor:pointer;">
                        <span class="material-icons" style="font-size:18px">download</span>
                        {{ translate('Export Excel') }}
                    </button>
                </div>
            </div>

            <div class="lm-main">
                {{-- Side panel --}}
                <aside class="side-panel">
                    <div class="panel-tabs" role="tablist">
                        <button type="button" class="panel-tab active" data-lm-tab="technicians">{{ translate('Technicians') }}</button>
                        <button type="button" class="panel-tab" data-lm-tab="cities">{{ translate('City Statistics') }}</button>
                        <button type="button" class="panel-tab" data-lm-tab="alerts">{{ translate('Work Alerts') }}</button>
                    </div>

                    <div data-lm-panel="technicians" class="d-flex flex-column h-100 overflow-hidden">
                        <div class="row g-2 p-3">
                            <div class="col-6">
                                <div class="stat-mini stat-mini--available">
                                    <div class="stat-mini-icon"><span class="material-icons">check_circle</span></div>
                                    <div>
                                        <p class="value" id="stat-available">{{ $stats['online_available'] }}</p>
                                        <p class="label">{{ translate('Online available') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini stat-mini--busy">
                                    <div class="stat-mini-icon"><span class="material-icons">schedule</span></div>
                                    <div>
                                        <p class="value" id="stat-busy">{{ $stats['online_busy'] }}</p>
                                        <p class="label">{{ translate('Online busy') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini stat-mini--offline">
                                    <div class="stat-mini-icon"><span class="material-icons">sensors_off</span></div>
                                    <div>
                                        <p class="value" id="stat-offline">{{ $stats['offline'] }}</p>
                                        <p class="label">{{ translate('Offline') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini stat-mini--total">
                                    <div class="stat-mini-icon"><span class="material-icons">groups</span></div>
                                    <div>
                                        <p class="value" id="stat-total">{{ $stats['total'] }}</p>
                                        <p class="label">{{ translate('Total technicians') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="side-panel-header">
                            <div class="d-flex flex-column gap-2">
                                <select id="filter-zone" class="lm-filter-select">
                                    <option value="">🏙️ {{ translate('All cities') }}</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                                <select id="filter-role" class="lm-filter-select">
                                    <option value="">🏷️ {{ translate('All roles') }}</option>
                                    <option value="supervisor">{{ translate('Supervisor') }}</option>
                                    <option value="serviceman">{{ translate('Serviceman') }}</option>
                                </select>
                                <select id="filter-status" class="lm-filter-select">
                                    <option value="">📊 {{ translate('All statuses') }}</option>
                                    <option value="online">🟢 {{ translate('Online') }}</option>
                                    <option value="online_available">🟢 {{ translate('Available') }}</option>
                                    <option value="online_busy">🟠 {{ translate('Busy') }}</option>
                                    <option value="offline">⚫ {{ translate('Offline') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="side-panel-list" id="agent-list"></div>
                        <span id="agent-count" class="d-none">0</span>
                    </div>

                    <div data-lm-panel="cities" class="d-none h-100 overflow-hidden">
                        <div class="side-panel-list" id="city-stats-list">
                            <div class="lm-empty">{{ translate('No city data yet') }}</div>
                        </div>
                    </div>

                    <div data-lm-panel="alerts" class="d-none h-100 overflow-hidden">
                        <div class="lm-empty">
                            <span class="material-icons mb-2 d-block" style="color:#008080;font-size:32px">notifications_none</span>
                            {{ translate('No work alerts right now') }}
                        </div>
                    </div>
                </aside>

                {{-- Map --}}
                <div class="lm-map-shell">
                    <div class="map-controls">
                        <button type="button" class="map-control-btn" id="lm-fullscreen" title="{{ translate('Fullscreen') }}">
                            <span class="material-icons" style="font-size:18px">fullscreen</span>
                        </button>
                        <button type="button" class="map-control-btn" id="lm-reset-view" title="{{ translate('Reset map') }}">
                            <span class="material-icons" style="font-size:18px">my_location</span>
                        </button>
                    </div>
                    @if(empty($googleMapsApiKey))
                        <div class="alert alert-warning m-3">
                            {{ translate('Google Maps API key is missing. Configure it in third-party map settings.') }}
                        </div>
                    @else
                        <div id="field-live-map"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    @if(!empty($googleMapsApiKey))
        @php
            $broadcast = \App\Support\Tracking\BroadcastClientConfig::forClients();
        @endphp
        <script>
            window.LIVE_MAP_CONFIG = {
                apiUrl: @json(route('admin.live-map.api')),
                pollInterval: 15000,
                pollIntervalWithSocket: 30000,
                defaultCenter: { lat: 24.0, lng: 45.0 },
                defaultZoom: 6,
                statusLabels: {
                    online_available: @json(translate('Available')),
                    online_busy: @json(translate('Busy')),
                    offline: @json(translate('Offline')),
                },
                statusColors: {
                    online_available: '#22c55e',
                    online_busy: '#f97316',
                    offline: '#9ca3af',
                    pending_booking: '#008080',
                },
                ui: {
                    liveOn: 'LIVE',
                    liveOff: 'OFFLINE',
                    livePending: '…',
                    noAgents: @json(translate('No technicians found')),
                    call: @json(translate('Call')),
                    whatsapp: @json(translate('WhatsApp')),
                    openProfile: @json(translate('Open profile')),
                    sector: @json(translate('Sector')),
                    lastSeen: @json(translate('Last seen')),
                },
                broadcast: @json($broadcast),
                csrfToken: @json(csrf_token()),
            };
        </script>
        <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
        @if(!empty($broadcast['enabled']))
            <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        @endif
        <script src="{{ asset('public/assets/admin-module/js/live-map.js') }}?v={{ @filemtime(public_path('assets/admin-module/js/live-map.js')) ?: time() }}"></script>
        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initFieldLiveMap"></script>
    @endif
@endpush
