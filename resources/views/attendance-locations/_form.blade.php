<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="space-y-3">
        <x-form.input label="Zone Name" name="name" :value="$location->name ?? null" required />

        <div class="grid grid-cols-2 gap-3">
            <x-form.input label="Latitude" name="latitude" type="number" step="any" :value="$location->latitude ?? null" required id="loc-lat" />
            <x-form.input label="Longitude" name="longitude" type="number" step="any" :value="$location->longitude ?? null" required id="loc-lng" />
        </div>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Radius (meters) <span class="text-error">*</span></legend>
            <input type="range" min="10" max="2000" step="10" value="{{ old('radius_meter', $location->radius_meter ?? 100) }}" class="range range-primary" id="loc-radius-range" />
            <input type="number" name="radius_meter" min="10" max="50000" value="{{ old('radius_meter', $location->radius_meter ?? 100) }}" class="input input-bordered w-full mt-2" id="loc-radius" required />
            <p class="label text-xs">Drag the slider or type a value (max 50&nbsp;km). The circle updates live.</p>
            @error('radius_meter')<p class="label text-error text-xs">{{ $message }}</p>@enderror
        </fieldset>

        <label class="label cursor-pointer justify-start gap-2">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $location->is_active ?? true)) />
            <span class="label-text">Active</span>
        </label>

        <div class="flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
            <a href="{{ route('attendance-locations.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </div>

    <div>
        <div id="loc-map" class="w-full h-96 rounded-box border border-base-300 z-0"></div>
        <p class="text-xs text-base-content/50 mt-2">Click the map or drag the pin to set the center. The shaded circle is the allowed check-in area.</p>
    </div>
</div>

<script>
    (function () {
        const latInput = document.getElementById('loc-lat');
        const lngInput = document.getElementById('loc-lng');
        const radiusInput = document.getElementById('loc-radius');
        const radiusRange = document.getElementById('loc-radius-range');

        const startLat = parseFloat(latInput.value) || -8.6705;
        const startLng = parseFloat(lngInput.value) || 115.2126;
        let radius = parseInt(radiusInput.value) || 100;

        const map = L.map('loc-map').setView([startLat, startLng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);
        const circle = L.circle([startLat, startLng], { radius: radius, color: '#2563eb', fillOpacity: 0.12 }).addTo(map);

        function setCenter(lat, lng) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
        }

        function setRadius(value) {
            radius = Math.max(10, Math.min(50000, parseInt(value) || 0));
            circle.setRadius(radius);
            // Keep range slider in sync within its display bounds.
            if (radius <= 2000) { radiusRange.value = radius; }
        }

        marker.on('dragend', () => { const p = marker.getLatLng(); setCenter(p.lat, p.lng); });
        map.on('click', (e) => setCenter(e.latlng.lat, e.latlng.lng));

        radiusInput.addEventListener('input', () => setRadius(radiusInput.value));
        radiusRange.addEventListener('input', () => { radiusInput.value = radiusRange.value; setRadius(radiusRange.value); });

        // Initialise hidden precision values.
        setCenter(startLat, startLng);
        setRadius(radius);
    })();
</script>
