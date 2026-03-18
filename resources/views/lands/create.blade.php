@extends('layouts.vertical', ['subTitle' => 'Add Land', 'title' => 'Admin'])

@php($Name = 'lands')
@section('content')
    @include('layouts.partials.page-title', ['title' => 'Land', 'subTitle' => 'Add Land'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Land</h5>
                </div>

                @include('layouts.partials.massages')

                <form method="POST" action="{{ route("$Name.store") }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">

                        {{-- Row 1: name + property_number --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_number" class="form-label" title="رقم العقار ">Property Number</label>
                                <input type="text" id="property_number" name="property_number" class="form-control" required value="{{ old('property_number') }}">
                            </div>
                        </div>

                        {{-- Row 2: description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        </div>

                        {{-- Row 3: Location (auto-filled from map) --}}
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" id="location" name="location" class="form-control" required value="{{ old('location') }}">
                        </div>

                        {{-- Country + Lat/Lng --}}
                        <div class="row align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <select id="country-select" class="form-select">
                                    <option value="">Any</option>
                                    <option value="US" @selected(old('country')==='US')>United States</option>
                                    <option value="GB" @selected(old('country')==='GB')>United Kingdom</option>
                                    <option value="CA" @selected(old('country')==='CA')>Canada</option>
                                    <option value="AU" @selected(old('country')==='AU')>Australia</option>
                                    <option value="BD" @selected(old('country')==='BD')>Bangladesh</option>
                                    <option value="LB" @selected(old('country')==='LB')>Lebanon</option>
                                </select>
                                <input type="hidden" id="country" name="country" value="{{ old('country', config('services.google_maps.default_country')) }}">
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

                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" id="use-my-location" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-current-location me-1"></i> Use my location
                            </button>
                        </div>
                        <div id="map" style="width:100%;height:360px;border-radius:8px;border:1px solid #e5e7eb;" class="mb-4"></div>

                        {{-- Row 4: section_number + district_zone --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="section_number" class="form-label" title="رقم القسم">Section Number</label>
                                <input type="text" id="section_number" name="section_number" class="form-control" required value="{{ old('section_number') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="district_zone" class="form-label" title=" المنطقة العقارية">District Zone</label>
                                <input type="text" id="district_zone" name="district_zone" class="form-control" required value="{{ old('district_zone') }}">
                            </div>
                        </div>

                        {{-- Row 5: area --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label">Area (m²)</label>
                                <input type="number" step="0.01" id="area" name="area" class="form-control" required value="{{ old('area') }}">
                            </div>
                        </div>

                        {{-- Row 6: photo + preview --}}
                        <div class="mb-3 mt-4">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" id="photo" name="photo" class="form-control" onchange="previewImage(event)">
                            <div class="mt-2">
                                <img id="photo-preview" src="#" alt="Selected Photo" style="max-width:200px; display:none; border:1px solid #ddd; padding:4px;">
                            </div>
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
/**
 * Land form: Map + Geocoder (+ optional Places)
 * - Click / drag / "Use my location" => updates lat/lng, writes human address to #location, sets country ISO-2
 */
(function () {
  let map, marker, geocoder, autocomplete;

  const DEFAULT_CENTER = { lat: 33.8938, lng: 35.5018 }; // Beirut

  const latEl         = document.getElementById('lat');
  const lngEl         = document.getElementById('lng');
  const locInput      = document.getElementById('location');
  const countryHidden = document.getElementById('country');
  const countrySelect = document.getElementById('country-select');
  const useMyLocBtn   = document.getElementById('use-my-location');

  // Optional address search input (if you add one later)
  const addressInput  = document.getElementById('pac-input');

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
    if (!geocoder) return;
    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
      if (status === 'OK' && results && results[0]) {
        // Write the formatted address into #location
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
    if (!useMyLocBtn) return;
    useMyLocBtn.addEventListener('click', () => {
      if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
      }
      navigator.geolocation.getCurrentPosition(
        (pos) => moveMarkerTo({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
        (err) => {
          const msg = {1:'Permission denied.',2:'Position unavailable.',3:'Timed out.'}[err.code] || 'Could not get your location.';
          alert(msg);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    });
  }

  window.initLandMap = function () {
    geocoder = new google.maps.Geocoder();

    // Default country to LB unless present
    normalizeCountry();

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
      map, position: start, draggable: true, animation: google.maps.Animation.DROP,
    });

    // Drag marker -> update + write location
    marker.addListener('dragend', () => {
      const p = marker.getPosition();
      moveMarkerTo({ lat: p.lat(), lng: p.lng() }, 17, true);
    });

    // Click map -> update + write location
    map.addListener('click', (e) => {
      moveMarkerTo({ lat: e.latLng.lat(), lng: e.latLng.lng() }, 17, true);
    });

    // Country dropdown -> sync hidden ISO-2
    countrySelect.addEventListener('change', () => {
      countryHidden.value = countrySelect.value || "";
    });

    // If we already have coords (old input), fill address once
    if (!isNaN(existingLat) && !isNaN(existingLng)) {
      reverseGeocodeToForm(existingLat, existingLng);
    } else {
      updateLatLng(start.lat, start.lng);
    }

    // Optional Places Autocomplete (if you add #pac-input in the form)
    if (addressInput) {
      addressInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
      autocomplete = new google.maps.places.Autocomplete(addressInput, {
        fields: ["geometry","formatted_address","address_components"],
      });
      autocomplete.bindTo('bounds', map);
      autocomplete.addEventListener?.('place_changed', () => {}); // guard for older TS hints
      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry || !place.geometry.location) return;
        const loc = place.geometry.location;
        moveMarkerTo({ lat: loc.lat(), lng: loc.lng() }, 17, false);
        // Also write to #location from the chosen place
        if (place.formatted_address) locInput.value = place.formatted_address;
        setCountryFromComponents(place.address_components || []);
      });
    }

    wireGeolocation();
  };
})();
</script>

{{-- Maps JS (Geocoder is part of core; Places is optional but included) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initLandMap" async defer></script>
@endsection
