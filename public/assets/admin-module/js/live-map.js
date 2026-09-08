(function () {
    'use strict';

    let map = null;
    let infoWindow = null;
    let markersById = new Map();
    let markerCluster = null;
    let bookingMarkers = [];
    let agentsCache = [];
    let pollTimer = null;
    let fetching = false;
    let echoInstance = null;
    let defaultCenter = { lat: 24.0, lng: 45.0 };
    let defaultZoom = 6;

    const ROLE_COLORS = {
        supervisor: '#008080',
        serviceman: '#0F2C59',
        customer: '#16a34a',
    };

    const ROLE_LETTERS = {
        supervisor: 'S',
        serviceman: 'T',
        customer: 'C',
    };

    const AVATAR_FALLBACK = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22%3E%3Ccircle cx=%2212%22 cy=%2212%22 r=%2212%22 fill=%22%23E6F7F5%22/%3E%3Cpath fill=%22%23008080%22 d=%22M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4%22/%3E%3C/svg%3E';

    function cfg() {
        return window.LIVE_MAP_CONFIG || {};
    }

    function ui(key, fallback) {
        return (cfg().ui && cfg().ui[key]) || fallback;
    }

    function setSocketBadge(state, text) {
        const el = document.getElementById('socket-status');
        const textEl = document.getElementById('socket-status-text');
        if (!el) return;
        el.dataset.state = state;
        const label = text
            || (state === 'on' || state === 'live' ? ui('liveOn', 'LIVE')
                : state === 'off' ? ui('liveOff', 'OFFLINE')
                    : ui('livePending', '…'));
        if (textEl) textEl.textContent = label;
        else el.textContent = label;
    }

    function roleIcon(role, statusColor) {
        const fill = ROLE_COLORS[role] || '#64748b';
        const letter = ROLE_LETTERS[role] || '?';
        const ring = statusColor || fill;
        const svg = `
            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="56" viewBox="0 0 44 56">
              <path d="M22 0C10.4 0 1 9.4 1 21c0 14.2 21 35 21 35s21-20.8 21-35C43 9.4 33.6 0 22 0z" fill="${fill}" stroke="#fff" stroke-width="2"/>
              <circle cx="22" cy="20" r="11" fill="#fff"/>
              <circle cx="22" cy="20" r="9.5" fill="none" stroke="${ring}" stroke-width="2.5"/>
              <text x="22" y="25" text-anchor="middle" font-size="13" font-family="Arial,sans-serif" font-weight="700" fill="${fill}">${letter}</text>
            </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(44, 56),
            anchor: new google.maps.Point(22, 54),
        };
    }

    function getFilters() {
        return {
            zone_id: document.getElementById('filter-zone')?.value || '',
            role: document.getElementById('filter-role')?.value || '',
            status: document.getElementById('filter-status')?.value || '',
            search: (document.getElementById('filter-search')?.value || '').trim().toLowerCase(),
        };
    }

    function buildUrl() {
        const f = getFilters();
        const params = new URLSearchParams();
        if (f.zone_id) params.append('zone_id', f.zone_id);
        if (f.role) params.append('role', f.role);
        if (f.status) params.append('status', f.status);
        const qs = params.toString();
        return cfg().apiUrl + (qs ? '?' + qs : '');
    }

    function updateStats(stats) {
        if (!stats) return;
        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? 0;
        };
        set('stat-available', stats.online_available);
        set('stat-busy', stats.online_busy);
        set('stat-offline', stats.offline);
        set('stat-total', stats.total);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function popupHtml(agent) {
        const labels = cfg().statusLabels || {};
        const phone = agent.phone || '';
        const waPhone = String(phone).replace(/[^\d+]/g, '').replace(/^0/, '966');
        const statusLabel = labels[agent.status] || agent.status;
        const name = escapeHtml(agent.name);
        const avatar = escapeHtml(agent.avatar || AVATAR_FALLBACK);
        const zone = escapeHtml(agent.zone || '-');
        const roleLabel = escapeHtml(agent.role_label || agent.role || '');
        const lastSeen = escapeHtml(agent.last_seen_at || '-');
        const company = escapeHtml(agent.company || '');
        const phoneEsc = escapeHtml(phone);
        const callLabel = escapeHtml(ui('call', 'Call'));
        const waLabel = escapeHtml(ui('whatsapp', 'WhatsApp'));

        return `
            <div class="popup-card">
                <div class="popup-header">
                    <img src="${avatar}" class="popup-avatar" alt="${name}" onerror="this.src='${AVATAR_FALLBACK}'">
                    <div style="min-width:0">
                        <div class="popup-name">${name}</div>
                        <div class="popup-type">${roleLabel}${company ? ' · ' + company : ''}</div>
                        <span class="popup-badge ${escapeHtml(agent.status || 'offline')}">${escapeHtml(statusLabel)}</span>
                    </div>
                </div>
                <div class="popup-body">
                    <div class="popup-row">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24c1.12.37 2.33.57 3.57.57c.55 0 1 .45 1 1V20c0 .55-.45 1-1 1c-9.39 0-17-7.61-17-17c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1c0 1.25.2 2.45.57 3.57c.11.35.03.74-.25 1.02z"/></svg>
                        <span dir="ltr">${phoneEsc || '-'}</span>
                    </div>
                    <div class="popup-row">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15 11V5l-3-3l-3 3v2H3v14h18V11zm-8 8H5v-2h2zm0-4H5v-2h2zm0-4H5V9h2zm6 8h-2v-2h2zm0-4h-2v-2h2zm0-4h-2V9h2zm0-4h-2V5h2zm6 12h-2v-2h2zm0-4h-2v-2h2z"/></svg>
                        <span>${zone} · ${roleLabel}</span>
                    </div>
                    <div class="popup-row">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8m.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67z"/></svg>
                        <span>${lastSeen}</span>
                    </div>
                </div>
                <div class="popup-actions">
                    ${phone ? `<a href="tel:${phoneEsc}" class="popup-btn popup-btn-call">
                        <svg width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24c1.12.37 2.33.57 3.57.57c.55 0 1 .45 1 1V20c0 .55-.45 1-1 1c-9.39 0-17-7.61-17-17c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1c0 1.25.2 2.45.57 3.57c.11.35.03.74-.25 1.02z"/></svg>
                        ${callLabel}
                    </a>` : ''}
                    ${phone ? `<a href="https://wa.me/${escapeHtml(waPhone)}" target="_blank" rel="noopener" class="popup-btn popup-btn-whatsapp">
                        <svg width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967c-.273-.099-.471-.148-.67.15c-.197.297-.767.966-.94 1.164c-.173.199-.347.223-.644.075c-.297-.15-1.255-.463-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059c-.173-.297-.018-.458.13-.606c.134-.133.298-.347.446-.52c.149-.174.198-.298.298-.497c.099-.198.05-.371-.025-.52c-.075-.149-.669-1.612-.916-2.207c-.242-.579-.487-.5-.669-.51c-.173-.008-.371-.01-.57-.01c-.198 0-.52.074-.792.372c-.272.297-1.04 1.016-1.04 2.479c0 1.462 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487c.709.306 1.262.489 1.694.625c.712.227 1.36.195 1.871.118c.571-.085 1.758-.719 2.006-1.413c.248-.694.248-1.289.173-1.413c-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214l-3.741.982l.998-3.648l-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884c2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        ${waLabel}
                    </a>` : ''}
                </div>
            </div>
        `;
    }

    function bookingPopupHtml(booking) {
        const profileUrl = booking.profile_url || '';
        const bookingUrl = booking.booking_url || '';
        const name = escapeHtml(booking.customer_name || 'Customer');
        return `
            <div class="popup-card">
                <div class="popup-header" style="background:#E6F7F5;">
                    <div style="min-width:0;padding:4px 0;">
                        <div class="popup-name">${name}</div>
                        <span class="popup-badge" style="background:#008080;">${escapeHtml(booking.role_label || 'Customer')}</span>
                    </div>
                </div>
                <div class="popup-body">
                    <div class="popup-row">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8m.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67z"/></svg>
                        <span>${escapeHtml(booking.created_at || '')}</span>
                    </div>
                    <div class="popup-row" style="font-size:11px;color:#9ca3af;">
                        Booking #${escapeHtml(booking.readable_id || booking.id)}
                    </div>
                </div>
                <div class="popup-actions" style="background:#E6F7F5;">
                    ${bookingUrl ? `<a href="${escapeHtml(bookingUrl)}" target="_blank" rel="noopener" class="popup-btn popup-btn-call">Open booking</a>` : ''}
                    ${profileUrl ? `<a href="${escapeHtml(profileUrl)}" target="_blank" rel="noopener" class="popup-btn popup-btn-whatsapp">Profile</a>` : ''}
                </div>
            </div>
        `;
    }

    function openAgentPopup(agent) {
        if (!agent || !map || !infoWindow) return;
        if (agent.latitude == null || agent.longitude == null) return;
        map.panTo({ lat: Number(agent.latitude), lng: Number(agent.longitude) });
        map.setZoom(14);
        const marker = markersById.get(String(agent.id));
        infoWindow.setContent(popupHtml(agent));
        if (marker) {
            infoWindow.open({ map, anchor: marker });
        } else {
            infoWindow.setPosition({ lat: Number(agent.latitude), lng: Number(agent.longitude) });
            infoWindow.open(map);
        }
    }

    function filteredAgents(agents) {
        const search = getFilters().search;
        if (!search) return agents;
        return agents.filter((a) => {
            const hay = `${a.name} ${a.phone || ''} ${a.company || ''} ${a.zone || ''}`.toLowerCase();
            return hay.includes(search);
        });
    }

    function renderList(agents) {
        const list = document.getElementById('agent-list');
        const count = document.getElementById('agent-count');
        if (!list) return;

        const labels = cfg().statusLabels || {};
        const filtered = filteredAgents(agents);

        if (count) count.textContent = String(filtered.length);
        renderCityStats(filtered);

        list.innerHTML = filtered.map((agent) => {
            const statusLabel = labels[agent.status] || agent.status || 'offline';
            const initial = String(agent.name || '?').trim().charAt(0).toUpperCase();
            const avatarHtml = agent.avatar
                ? `<img class="tech-list-avatar" src="${escapeHtml(agent.avatar)}" alt="" onerror="this.src='${AVATAR_FALLBACK}'">`
                : `<div class="tech-list-avatar-fallback">${escapeHtml(initial)}</div>`;

            return `
            <div class="tech-list-item" data-id="${escapeHtml(agent.id)}">
                <div class="tech-list-avatar-wrap">
                    ${avatarHtml}
                    <div class="tech-list-status ${escapeHtml(agent.status || 'offline')}"></div>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="margin:0;font-size:13px;font-weight:700;color:#0F2C59;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escapeHtml(agent.name)}</p>
                    <div style="display:flex;align-items:center;gap:4px;margin-top:2px;font-size:10px;color:#9ca3af">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escapeHtml(agent.zone || '-')}</span>
                        <span style="color:#d1d5db">•</span>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escapeHtml(agent.last_seen_at || '')}</span>
                    </div>
                </div>
                <span class="popup-badge ${escapeHtml(agent.status || 'offline')}" style="flex-shrink:0">${escapeHtml(statusLabel)}</span>
            </div>`;
        }).join('') || `<div class="lm-empty">${escapeHtml(ui('noAgents', 'No technicians found'))}</div>`;

        list.querySelectorAll('.tech-list-item').forEach((el) => {
            el.addEventListener('click', () => {
                const agent = agentsCache.find((a) => String(a.id) === String(el.dataset.id));
                openAgentPopup(agent);
            });
        });
    }

    function renderCityStats(agents) {
        const wrap = document.getElementById('city-stats-list');
        if (!wrap) return;

        const byZone = {};
        (agents || []).forEach((a) => {
            const key = a.zone || '-';
            byZone[key] = (byZone[key] || 0) + 1;
        });

        const rows = Object.entries(byZone).sort((a, b) => b[1] - a[1]);
        if (!rows.length) {
            wrap.innerHTML = `<div class="lm-empty">${escapeHtml(ui('noAgents', 'No technicians found'))}</div>`;
            return;
        }

        wrap.innerHTML = rows.map(([zone, count]) => `
            <div class="city-list-item">
                <div style="width:36px;height:36px;border-radius:10px;background:#E6F7F5;color:#008080;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <span class="material-icons" style="font-size:18px">location_city</span>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="margin:0;font-size:13px;font-weight:700;color:#0F2C59">${escapeHtml(zone)}</p>
                    <p style="margin:0;font-size:10px;color:#9ca3af">Technicians</p>
                </div>
                <span class="count">${count}</span>
            </div>
        `).join('');
    }

    function bindTabs() {
        document.querySelectorAll('[data-lm-tab]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-lm-tab');
                document.querySelectorAll('[data-lm-tab]').forEach((b) => b.classList.toggle('active', b === btn));
                document.querySelectorAll('[data-lm-panel]').forEach((panel) => {
                    panel.classList.toggle('d-none', panel.getAttribute('data-lm-panel') !== tab);
                });
            });
        });
    }

    function exportExcel() {
        const rows = [['Name', 'Phone', 'Role', 'Status', 'Zone', 'Company', 'Last seen']];
        const labels = cfg().statusLabels || {};
        agentsCache.forEach((a) => {
            rows.push([
                a.name || '',
                a.phone || '',
                a.role_label || a.role || '',
                labels[a.status] || a.status || '',
                a.zone || '',
                a.company || '',
                a.last_seen_at || '',
            ]);
        });

        const csv = rows.map((row) => row.map((cell) => {
            const value = String(cell ?? '').replace(/"/g, '""');
            return `"${value}"`;
        }).join(',')).join('\n');

        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `clean365-live-map-${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    function clusterRenderer() {
        return {
            render({ count, position }) {
                // Clean365 teal scale (tashyik uses indigo/pink)
                let size = 40;
                let bg = '#008080';
                let fontSize = 13;
                if (count >= 50) {
                    size = 60;
                    bg = '#0F2C59';
                    fontSize = 16;
                } else if (count >= 10) {
                    size = 50;
                    bg = '#006666';
                    fontSize = 14;
                }

                return new google.maps.Marker({
                    position,
                    icon: {
                        url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                            <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
                                <circle cx="${size / 2}" cy="${size / 2}" r="${size / 2 - 2}" fill="${bg}" stroke="rgba(255,255,255,0.8)" stroke-width="3"/>
                                <text x="50%" y="52%" text-anchor="middle" dy=".35em" fill="white" font-family="Arial,sans-serif" font-weight="700" font-size="${fontSize}">${count}</text>
                            </svg>
                        `)}`,
                        scaledSize: new google.maps.Size(size, size),
                        anchor: new google.maps.Point(size / 2, size / 2),
                    },
                    zIndex: 1000 + count,
                });
            },
        };
    }

    function ensureClusterer(markers) {
        const Clusterer = window.markerClusterer?.MarkerClusterer;
        if (!Clusterer) {
            markers.forEach((m) => m.setMap(map));
            return;
        }

        if (markerCluster) {
            markerCluster.clearMarkers();
            markerCluster.addMarkers(markers);
            return;
        }

        markerCluster = new Clusterer({
            map,
            markers,
            renderer: clusterRenderer(),
        });
    }

    function syncMarkers(agents) {
        const nextIds = new Set(agents.map((a) => String(a.id)));
        const activeMarkers = [];

        markersById.forEach((marker, id) => {
            if (!nextIds.has(id)) {
                marker.setMap(null);
                markersById.delete(id);
            }
        });

        agents.forEach((agent) => {
            if (agent.latitude == null || agent.longitude == null) return;
            const id = String(agent.id);
            const position = { lat: Number(agent.latitude), lng: Number(agent.longitude) };
            const statusColor = (cfg().statusColors || {})[agent.status] || '#9ca3af';
            const icon = roleIcon(agent.role, statusColor);
            let marker = markersById.get(id);

            if (!marker) {
                marker = new google.maps.Marker({
                    position,
                    icon,
                    title: `${agent.role_label || agent.role}: ${agent.name}`,
                    zIndex: agent.role === 'supervisor' ? 20 : 10,
                });
                marker.addListener('click', () => openAgentPopup(agent));
                markersById.set(id, marker);
            } else {
                marker.setPosition(position);
                marker.setIcon(icon);
                marker.setTitle(`${agent.role_label || agent.role}: ${agent.name}`);
                google.maps.event.clearListeners(marker, 'click');
                marker.addListener('click', () => openAgentPopup(agent));
            }
            activeMarkers.push(marker);
        });

        ensureClusterer(activeMarkers);
    }

    function syncBookingMarkers(bookings) {
        bookingMarkers.forEach((m) => m.setMap(null));
        bookingMarkers = [];

        (bookings || []).forEach((booking) => {
            if (booking.latitude == null || booking.longitude == null) return;
            const icon = roleIcon('customer', (cfg().statusColors || {}).pending_booking || '#008080');
            const marker = new google.maps.Marker({
                map,
                position: { lat: Number(booking.latitude), lng: Number(booking.longitude) },
                icon,
                title: `Customer: ${booking.customer_name || booking.readable_id}`,
                zIndex: 5,
            });
            marker.addListener('click', () => {
                infoWindow.setContent(bookingPopupHtml(booking));
                infoWindow.open({ map, anchor: marker });
            });
            bookingMarkers.push(marker);
        });
    }

    async function fetchData() {
        if (fetching || !map) return;
        if (document.visibilityState !== 'visible') return;

        fetching = true;
        try {
            const response = await fetch(buildUrl(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            agentsCache = data.agents || [];
            updateStats(data.stats);
            renderList(agentsCache);
            syncMarkers(agentsCache);
            syncBookingMarkers(data.pending_bookings || []);
        } catch (err) {
            console.error('[LiveMap] fetch failed', err);
        } finally {
            fetching = false;
        }
    }

    function bindFilters() {
        ['filter-zone', 'filter-role', 'filter-status'].forEach((id) => {
            document.getElementById(id)?.addEventListener('change', () => fetchData());
        });
        document.getElementById('filter-search')?.addEventListener('input', () => {
            renderList(agentsCache);
        });
    }

    function bindMapControls() {
        document.getElementById('lm-fullscreen')?.addEventListener('click', () => {
            const wrap = document.getElementById('lm-wrap');
            const btn = document.getElementById('lm-fullscreen');
            if (!wrap) return;
            wrap.classList.toggle('map-fullscreen');
            const on = wrap.classList.contains('map-fullscreen');
            btn?.classList.toggle('active', on);
            const icon = btn?.querySelector('.material-icons');
            if (icon) icon.textContent = on ? 'fullscreen_exit' : 'fullscreen';
            setTimeout(() => {
                if (map) google.maps.event.trigger(map, 'resize');
            }, 50);
        });

        document.getElementById('lm-reset-view')?.addEventListener('click', () => {
            if (!map) return;
            map.setCenter(defaultCenter);
            map.setZoom(defaultZoom);
        });
    }

    function startPolling(intervalMs) {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(fetchData, intervalMs || cfg().pollInterval || 15000);
    }

    function applyAgentLocationFromSocket(payload) {
        const agent = payload?.agent;
        if (!agent?.id || agent.latitude == null || agent.longitude == null || !map) {
            return;
        }

        const id = String(agent.id);
        const position = { lat: Number(agent.latitude), lng: Number(agent.longitude) };
        let cached = agentsCache.find((a) => String(a.id) === id);
        const role = agent.role === 'serviceman' ? 'serviceman' : 'supervisor';

        if (cached) {
            cached.latitude = position.lat;
            cached.longitude = position.lng;
            cached.last_seen_at = agent.last_seen_at || cached.last_seen_at;
            if (agent.is_online) {
                cached.status = cached.status === 'offline' ? 'online_available' : cached.status;
            }
        } else {
            cached = {
                id: agent.id,
                name: agent.name || 'Agent',
                phone: agent.phone,
                latitude: position.lat,
                longitude: position.lng,
                role,
                role_label: role === 'serviceman' ? 'Serviceman' : 'Supervisor',
                status: agent.is_online ? 'online_busy' : 'offline',
                zone: '-',
                company: '-',
                last_seen_at: agent.last_seen_at || '',
                avatar: '',
                profile_url: '',
            };
            agentsCache.push(cached);
        }

        syncMarkers(agentsCache);
        renderList(agentsCache);
        setSocketBadge('live');
    }

    function initBroadcast() {
        const broadcast = cfg().broadcast;
        const EchoLib = window.Echo;
        if (!broadcast?.enabled || !broadcast.key || !EchoLib || !window.Pusher) {
            setSocketBadge('off');
            return;
        }

        try {
            echoInstance = new EchoLib({
                broadcaster: 'reverb',
                key: broadcast.key,
                wsHost: broadcast.host,
                wsPort: broadcast.port || 8081,
                wssPort: broadcast.port || 8081,
                forceTLS: (broadcast.scheme || 'http') === 'https',
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
                authEndpoint: broadcast.web_auth_endpoint || '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': cfg().csrfToken || '',
                        Accept: 'application/json',
                    },
                },
            });

            echoInstance.private('admin.live-map')
                .listen('.BookingAgentLocationUpdated', (e) => applyAgentLocationFromSocket(e))
                .listen('.BookingTrackingStarted', () => fetchData())
                .listen('.BookingTrackingEnded', () => fetchData());

            startPolling(cfg().pollIntervalWithSocket || 30000);
            setSocketBadge('on');
            console.info('[LiveMap] Reverb subscribed to admin.live-map', broadcast);
        } catch (err) {
            console.warn('[LiveMap] broadcast init failed, using poll only', err);
            setSocketBadge('off');
        }
    }

    window.initFieldLiveMap = function () {
        const el = document.getElementById('field-live-map');
        if (!el || !window.google?.maps) return;

        defaultCenter = cfg().defaultCenter || defaultCenter;
        defaultZoom = cfg().defaultZoom || defaultZoom;

        map = new google.maps.Map(el, {
            center: defaultCenter,
            zoom: defaultZoom,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
        });
        infoWindow = new google.maps.InfoWindow();

        bindFilters();
        bindTabs();
        bindMapControls();
        document.getElementById('lm-export-excel')?.addEventListener('click', exportExcel);
        fetchData();
        startPolling(cfg().pollInterval || 15000);
        setSocketBadge('pending');
        initBroadcast();
    };
})();
