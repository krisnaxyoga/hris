<x-layouts.app title="My Attendance">
    <x-ui.page-header title="My Attendance" subtitle="Check in and out with live GPS & selfie" />

    @unless ($employee)
        <div class="alert alert-warning">Your account is not linked to an employee profile. Contact HR.</div>
    @else
        {{-- Leaflet (OpenStreetMap) assets --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Map + check-in --}}
            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <h2 class="card-title text-lg">Your Location</h2>
                    <div id="att-map" class="w-full h-80 rounded-box z-0 border border-base-300"></div>
                    <div id="geo-badge" class="mt-2"></div>

                    @if (! $today?->check_in_time)
                        <form method="POST" action="{{ route('attendance.check-in') }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="latitude" id="att-lat" />
                            <input type="hidden" name="longitude" id="att-lng" />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend">Mode</legend>
                                    <select name="attendance_mode" id="att-mode" class="select select-bordered w-full">
                                        <option value="office">Office</option>
                                        <option value="wfh">Work From Home</option>
                                        <option value="business_trip">Business Trip</option>
                                    </select>
                                </fieldset>
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend">Work Location (WFH / trip)</legend>
                                    <input type="text" name="work_location" class="input input-bordered w-full" placeholder="e.g. Home" />
                                </fieldset>
                            </div>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Selfie (optional)</legend>
                                <input type="file" name="photo" accept="image/*" capture="user" class="file-input file-input-bordered file-input-sm w-full" />
                            </fieldset>
                            <button type="submit" id="att-checkin-btn" class="btn btn-primary w-full" disabled>Check In</button>
                        </form>
                    @elseif (! $today?->check_out_time)
                        <form method="POST" action="{{ route('attendance.check-out') }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="latitude" id="att-lat" />
                            <input type="hidden" name="longitude" id="att-lng" />
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Selfie (optional)</legend>
                                <input type="file" name="photo" accept="image/*" capture="user" class="file-input file-input-bordered file-input-sm w-full" />
                            </fieldset>
                            <button type="submit" id="att-checkin-btn" class="btn btn-secondary w-full">Check Out</button>
                        </form>
                    @else
                        <div class="alert alert-success mt-3">Attendance complete for today. 🎉</div>
                    @endif
                </div>
            </div>

            {{-- Today + history --}}
            <div class="space-y-6">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Today</h2>
                        <p class="text-sm text-base-content/60">{{ now()->format('l, d M Y') }}</p>
                        @if ($today?->check_in_time)
                            <p class="mt-2">Checked in: <span class="font-semibold">{{ $today->check_in_time->format('H:i') }}</span>
                                @if ($today->late_minutes > 0)<span class="badge badge-warning badge-sm ml-1">Late {{ $today->late_minutes }}m</span>@endif
                            </p>
                        @endif
                        @if ($today?->check_out_time)
                            <p>Checked out: <span class="font-semibold">{{ $today->check_out_time->format('H:i') }}</span> · {{ $today->working_hours }} hrs</p>
                        @endif
                        @unless ($today?->check_in_time)
                            <p class="text-sm text-base-content/50 mt-2">Not checked in yet.</p>
                        @endunless
                    </div>
                </div>

                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Recent</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse ($recent as $row)
                                        <tr>
                                            <td>{{ $row->attendance_date->format('d M') }}</td>
                                            <td>{{ $row->check_in_time?->format('H:i') ?? '—' }}</td>
                                            <td>{{ $row->check_out_time?->format('H:i') ?? '—' }}</td>
                                            <td><span class="badge badge-sm {{ $row->attendance_status->color() }}">{{ $row->attendance_status->label() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-base-content/50 py-4">No history.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const offices = @json($locations);
                const badge = document.getElementById('geo-badge');
                const latInput = document.getElementById('att-lat');
                const lngInput = document.getElementById('att-lng');
                const checkInBtn = document.getElementById('att-checkin-btn');
                const modeSelect = document.getElementById('att-mode');

                // Default center: first office, else Bali.
                const center = offices.length
                    ? [parseFloat(offices[0].latitude), parseFloat(offices[0].longitude)]
                    : [-8.6705, 115.2126];

                const map = L.map('att-map').setView(center, 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Draw office geofences.
                offices.forEach((o) => {
                    const lat = parseFloat(o.latitude), lng = parseFloat(o.longitude);
                    L.circle([lat, lng], { radius: o.radius_meter, color: '#2563eb', fillOpacity: 0.1 })
                        .addTo(map).bindPopup(o.name + ' (' + o.radius_meter + 'm)');
                    L.marker([lat, lng]).addTo(map).bindPopup(o.name);
                });

                let marker = null;

                function haversine(lat1, lng1, lat2, lng2) {
                    const R = 6371000, toRad = (d) => d * Math.PI / 180;
                    const dLat = toRad(lat2 - lat1), dLng = toRad(lng2 - lng1);
                    const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                }

                function updatePosition(lat, lng) {
                    latInput.value = lat;
                    lngInput.value = lng;

                    let nearest = Infinity;
                    offices.forEach((o) => {
                        const d = haversine(lat, lng, parseFloat(o.latitude), parseFloat(o.longitude)) - o.radius_meter;
                        nearest = Math.min(nearest, d);
                    });

                    const inside = offices.length === 0 ? false : nearest <= 0;
                    const mode = modeSelect ? modeSelect.value : 'office';

                    if (mode !== 'office') {
                        badge.innerHTML = '<span class="badge badge-accent">Location captured (' + mode.replace('_', ' ') + ')</span>';
                        if (checkInBtn) checkInBtn.disabled = false;
                    } else if (inside) {
                        badge.innerHTML = '<span class="badge badge-success">Inside office radius ✓</span>';
                        if (checkInBtn) checkInBtn.disabled = false;
                    } else {
                        const m = offices.length ? Math.round(nearest) : 0;
                        badge.innerHTML = '<span class="badge badge-error">Outside office radius'
                            + (offices.length ? ' (' + m + 'm away)' : '') + '</span>';
                        if (checkInBtn) checkInBtn.disabled = true;
                    }
                }

                function placeMarker(lat, lng) {
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', () => {
                            const p = marker.getLatLng();
                            updatePosition(p.lat, p.lng);
                        });
                    }
                    updatePosition(lat, lng);
                }

                if (modeSelect) {
                    modeSelect.addEventListener('change', () => {
                        if (marker) { const p = marker.getLatLng(); updatePosition(p.lat, p.lng); }
                    });
                }

                badge.innerHTML = '<span class="badge badge-ghost">Locating you…</span>';
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const lat = pos.coords.latitude, lng = pos.coords.longitude;
                            map.setView([lat, lng], 17);
                            placeMarker(lat, lng);
                        },
                        () => { badge.innerHTML = '<span class="badge badge-warning">Allow location access, or drag the pin.</span>'; placeMarker(center[0], center[1]); },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                } else {
                    badge.innerHTML = '<span class="badge badge-warning">Geolocation unsupported. Drag the pin.</span>';
                    placeMarker(center[0], center[1]);
                }
            })();
        </script>
    @endunless
</x-layouts.app>
