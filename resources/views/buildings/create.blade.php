@extends('layouts.vertical', ['subTitle' => 'Add Building', 'title' => 'Admin'])

@php($Name = 'buildings')
@section('content')
    @include('layouts.partials.page-title', ['title' => 'Building', 'subTitle' => 'Add Building'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Building</h5>
                </div>

                @include('layouts.partials.massages')

                <form method="POST" action="{{ route("$Name.store") }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                   value="{{ old('name') }}">
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        </div>

                        {{-- Location (auto-filled from map but editable) --}}
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" id="location" name="location" class="form-control" required
                                   value="{{ old('location') }}">
                        </div>

                        {{-- Country + Lat/Lng --}}
                        <div class="row align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <select id="country-select" class="form-select">
                                    <option value="">Any</option>
                                    <option value="LB" @selected(old('country') === 'LB')>Lebanon</option>
                                    <option value="US" @selected(old('country') === 'US')>United States</option>
                                    <option value="GB" @selected(old('country') === 'GB')>United Kingdom</option>
                                    <option value="CA" @selected(old('country') === 'CA')>Canada</option>
                                    <option value="AE" @selected(old('country') === 'AE')>United Arab Emirates</option>
                                    <option value="SA" @selected(old('country') === 'SA')>Saudi Arabia</option>
                                    <option value="EG" @selected(old('country') === 'EG')>Egypt</option>
                                </select>
                                <input type="hidden" id="country" name="country" value="{{ old('country') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Latitude</label>
                                <input id="lat" name="lat" class="form-control" value="{{ old('lat') }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Longitude</label>
                                <input id="lng" name="lng" class="form-control" value="{{ old('lng') }}" readonly>
                            </div>
                        </div>

                        {{-- Geolocate button + Map --}}
                        <div class="d-flex justify-content-end mb-2">
                            <button id="locate-me" type="button" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-current-location"></i> Use my location
                            </button>
                        </div>
                        <div id="map" style="width:100%;height:360px;border-radius:8px;border:1px solid #e5e7eb;" class="mb-4"></div>

                        {{-- Image --}}
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" id="image" name="image" class="form-control" required>
                        </div>

                        <div class="col-12 text-end">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
(function () {
  let map, marker, geocoder;

  const DEFAULT_CENTER = { lat: 33.8938, lng: 35.5018 }; // Beirut

  const latEl         = document.getElementById('lat');
  const lngEl         = document.getElementById('lng');
  const locInput      = document.getElementById('location');
  const countryHidden = document.getElementById('country');
  const countrySelect = document.getElementById('country-select');
  const locateBtn     = document.getElementById('locate-me');

  function updateLatLng(lat, lng) {
    latEl.value = Number(lat).toFixed(7);
    lngEl.value = Number(lng).toFixed(7);
  }

  function setCountryFromComponents(components) {
    const c = (components || []).find(x => (x.types || []).includes('country'));
    if (c && c.short_name) {
      countryHidden.value = c.short_name;
      countrySelect.value = c.short_name;
    }
  }

  function reverseGeocodeToForm(lat, lng) {
    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
      if (status === 'OK' && results && results[0]) {
        // Put the human-readable address into the Location field
        locInput.value = results[0].formatted_address;
        setCountryFromComponents(results[0].address_components);
      }
    });
  }

  function normalizeCountryHidden() {
    const v = (countryHidden.value || '').trim();
    if (!v || v.length !== 2) {
      const fromSelect = (countrySelect.value && countrySelect.value.length === 2) ? countrySelect.value : 'LB';
      countryHidden.value = fromSelect;
    }
    if (!countrySelect.value || countrySelect.value.length !== 2) {
      countrySelect.value = countryHidden.value;
    }
  }

  function moveMarkerTo(position, zoomTo = 17, doReverse = true) {
    map.panTo(position);
    if (zoomTo) map.setZoom(zoomTo);
    marker.setPosition(position);
    updateLatLng(position.lat, position.lng);
    if (doReverse) reverseGeocodeToForm(position.lat, position.lng);
  }

  function useMyLocation() {
    if (!('geolocation' in navigator)) {
      alert('Geolocation is not supported by this browser.');
      return;
    }
    locateBtn.disabled = true;
    locateBtn.innerText = 'Locating…';
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        locateBtn.disabled = false;
        locateBtn.innerText = 'Use my location';
        moveMarkerTo({ lat: pos.coords.latitude, lng: pos.coords.longitude });
      },
      (err) => {
        locateBtn.disabled = false;
        locateBtn.innerText = 'Use my location';
        const msg = {
          1: 'Permission denied. Please allow location and try again.',
          2: 'Position unavailable. Please try again.',
          3: 'Request timed out. Please try again.',
        }[err.code] || 'Unable to get your location.';
        alert(msg);
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }

  window.initBuildingMap = function () {
    geocoder = new google.maps.Geocoder();

    normalizeCountryHidden();

    const existingLat = parseFloat(latEl.value);
    const existingLng = parseFloat(lngEl.value);
    const start = (!isNaN(existingLat) && !isNaN(existingLng))
      ? { lat: existingLat, lng: existingLng }
      : DEFAULT_CENTER;

    map = new google.maps.Map(document.getElementById('map'), {
      center: start,
      zoom: 14,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true,
    });

    marker = new google.maps.Marker({
      map,
      position: start,
      draggable: true,
      animation: google.maps.Animation.DROP,
    });

    // Drag marker -> update coords + write location
    marker.addListener('dragend', () => {
      const p = marker.getPosition();
      moveMarkerTo({ lat: p.lat(), lng: p.lng() }, 17, true);
    });

    // Click map -> move marker + write location
    map.addListener('click', (e) => {
      moveMarkerTo({ lat: e.latLng.lat(), lng: e.latLng.lng() }, 17, true);
    });

    // Country dropdown -> sync hidden ISO-2
    countrySelect.addEventListener('change', () => {
      countryHidden.value = countrySelect.value || '';
      normalizeCountryHidden();
    });

    // If coords empty, set defaults; otherwise optionally reverse geocode once
    if (isNaN(existingLat) || isNaN(existingLng)) {
      updateLatLng(start.lat, start.lng);
    } else {
      reverseGeocodeToForm(existingLat, existingLng);
    }

    locateBtn?.addEventListener('click', useMyLocation);

    // Normalize again right before submit
    const form = document.querySelector('form[action*="{{ route("$Name.store") }}"]') || document.querySelector('form');
    form?.addEventListener('submit', () => normalizeCountryHidden());
  };
})();
</script>

{{-- Maps JS (Geocoder is part of core; enable Geocoding API on your key) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initBuildingMap" async defer></script>
@endsection
