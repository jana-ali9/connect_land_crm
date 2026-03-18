@extends('layouts.vertical', ['subTitle' => 'Edit Land', 'title' => 'Admin'])

@php
$Name = 'lands'
@endphp
@section('content')
    @include('layouts.partials.page-title', ['title' => 'Land', 'subTitle' => 'Edit Land'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("update $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Land</h5>
                </div>

                @include('layouts.partials.massages')

                <form method="POST" action="{{ route("$Name.update", $land->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        {{-- Row 1: name + property_number --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" required
                                       value="{{ old('name', $land->name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_number" class="form-label" title="رقم العقار">Property Number</label>
                                <input type="text" id="property_number" name="property_number" class="form-control" required
                                       value="{{ old('property_number', $land->property_number) }}">
                            </div>
                        </div>

                        {{-- Row 2: description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $land->description) }}</textarea>
                        </div>

                        {{-- Row 3: Location (auto-filled by reverse geocoding, but editable) --}}
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" id="location" name="location" class="form-control" required
                                   value="{{ old('location', $land->location) }}">
                        </div>

                        {{-- Country + Lat/Lng --}}
                        <div class="row align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                @php $c = old('country', $land->country); @endphp
                                <select id="country-select" class="form-select">
                                    <option value="">Any</option>
                                    <option value="LB" @selected($c==='LB')>Lebanon</option>
                                    <option value="US" @selected($c==='US')>United States</option>
                                    <option value="GB" @selected($c==='GB')>United Kingdom</option>
                                    <option value="CA" @selected($c==='CA')>Canada</option>
                                    <option value="AU" @selected($c==='AU')>Australia</option>
                                    <option value="BD" @selected($c==='BD')>Bangladesh</option>
                                </select>
                                <input type="hidden" id="country" name="country" value="{{ $c ?? 'LB' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Latitude</label>
                                <input id="lat" name="lat" class="form-control" value="{{ old('lat', $land->lat) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Longitude</label>
                                <input id="lng" name="lng" class="form-control" value="{{ old('lng', $land->lng) }}" readonly>
                            </div>
                        </div>

                        {{-- Geolocate + Map --}}
                        <div class="d-flex justify-content-end mb-2">
                            <button id="use-my-location" type="button" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-current-location"></i> Use my location
                            </button>
                        </div>
                        <div id="map" style="width:100%;height:360px;border-radius:8px;border:1px solid #e5e7eb;" class="mb-4"></div>

                        {{-- Row 4: section_number + district_zone --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="section_number" class="form-label" title="رقم القسم">Section Number</label>
                                <input type="text" id="section_number" name="section_number" class="form-control" required
                                       value="{{ old('section_number', $land->section_number) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="district_zone" class="form-label" title=" المنطقة العقارية">District Zone</label>
                                <input type="text" id="district_zone" name="district_zone" class="form-control" required
                                       value="{{ old('district_zone', $land->district_zone) }}">
                            </div>
                        </div>

                        {{-- Row 5: area --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label">Area (m²)</label>
                                <input type="number" step="0.01" id="area" name="area" class="form-control" required
                                       value="{{ old('area', $land->area) }}">
                            </div>
                        </div>

                        {{-- Row 6: photo with preview --}}
                        <div class="mb-3 mt-4">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" id="photo" name="photo" class="form-control" onchange="previewImage(event)">
                            <div class="mt-2">
                                <img id="photo-preview"
                                     src="{{ $land->photo ? asset('storage/'.$land->photo) : asset('default.png') }}"
                                     alt="Current Photo"
                                     style="max-width:200px;border:1px solid #ddd;padding:4px;">
                            </div>
                        </div>

                        <div class="col-12 text-end">
                            <button class="btn btn-primary" type="submit">Update Land</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
  // Image preview
  function previewImage(event) {
    const input = event.target, preview = document.getElementById('photo-preview');
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => { preview.src = e.target.result; };
      reader.readAsDataURL(input.files[0]);
    }
  }

  // Map + Geocoder + Geolocation
  (function () {
    let map, marker, geocoder;

    const DEFAULT_CENTER = { lat: 33.8938, lng: 35.5018 }; // Beirut

    const latEl         = document.getElementById('lat');
    const lngEl         = document.getElementById('lng');
    const locInput      = document.getElementById('location');
    const countryHidden = document.getElementById('country');
    const countrySelect = document.getElementById('country-select');
    const locateBtn     = document.getElementById('use-my-location');

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
          locInput.value = results[0].formatted_address || locInput.value;
          setCountryFromComponents(results[0].address_components);
        }
      });
    }

    function normalizeCountry() {
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

    function wireGeolocation() {
      if (!locateBtn) return;
      locateBtn.addEventListener('click', () => {
        if (!navigator.geolocation) {
          alert('Geolocation is not supported by your browser.');
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
              1: 'Permission denied. Please allow location access.',
              2: 'Position unavailable.',
              3: 'Location request timed out.'
            }[err.code] || 'Could not get your location.';
            alert(msg);
          },
          { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
      });
    }

    window.initLandMap = function () {
      geocoder = new google.maps.Geocoder();

      // Default country LB if empty
      normalizeCountry();

      const existLat = parseFloat(latEl.value);
      const existLng = parseFloat(lngEl.value);
      const start = (!isNaN(existLat) && !isNaN(existLng))
        ? { lat: existLat, lng: existLng }
        : DEFAULT_CENTER;

      map = new google.maps.Map(document.getElementById('map'), {
        center: start,
        zoom: (!isNaN(existLat) && !isNaN(existLng)) ? 16 : 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
      });

      marker = new google.maps.Marker({
        map, position: start, draggable: true, animation: google.maps.Animation.DROP
      });

      // Drag → update + reverse geocode
      marker.addListener('dragend', () => {
        const p = marker.getPosition();
        moveMarkerTo({ lat: p.lat(), lng: p.lng() }, 17, true);
      });

      // Click → update + reverse geocode
      map.addListener('click', (e) => {
        moveMarkerTo({ lat: e.latLng.lat(), lng: e.latLng.lng() }, 17, true);
      });

      // Country dropdown sync
      countrySelect.addEventListener('change', () => {
        countryHidden.value = countrySelect.value || '';
      });

      // If we already have coords, fill address once
      if (!isNaN(existLat) && !isNaN(existLng)) {
        reverseGeocodeToForm(existLat, existLng);
      } else {
        updateLatLng(start.lat, start.lng);
      }

      wireGeolocation();
    };
  })();
</script>

{{-- Google Maps (Geocoder is in core; Places not required here) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initLandMap" async defer></script>
@endsection
