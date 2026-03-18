@extends('layouts.vertical', ['subTitle' => 'Edit Building', 'title' => 'Buildings'])
@php($Name = 'buildings')

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Buildings', 'subTitle' => 'Edit Building'])

    <div class="card-body">
        @if (auth()->user()->hasPermission("update $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Building</h5>
                </div>

                @include('layouts.partials.massages')

                <form method="POST" action="{{ route("$Name.update", $building->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                   value="{{ old('name', $building->name) }}">
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $building->description) }}</textarea>
                        </div>

                        {{-- Location (auto-filled by reverse geocoding but editable) --}}
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" id="location" name="location" class="form-control" required
                                   value="{{ old('location', $building->location) }}">
                        </div>

                        {{-- Country + Lat/Lng --}}
                        <div class="row align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <select id="country-select" class="form-select">
                                    <option value="">Any</option>
                                    <option value="LB" @selected(old('country', $building->country) === 'LB')>Lebanon</option>
                                    <option value="US" @selected(old('country', $building->country) === 'US')>United States</option>
                                    <option value="GB" @selected(old('country', $building->country) === 'GB')>United Kingdom</option>
                                    <option value="CA" @selected(old('country', $building->country) === 'CA')>Canada</option>
                                    <option value="AE" @selected(old('country', $building->country) === 'AE')>United Arab Emirates</option>
                                    <option value="SA" @selected(old('country', $building->country) === 'SA')>Saudi Arabia</option>
                                    <option value="EG" @selected(old('country', $building->country) === 'EG')>Egypt</option>
                                </select>
                                <input type="hidden" id="country" name="country"
                                       value="{{ old('country', $building->country ?: 'LB') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Latitude</label>
                                <input id="lat" name="lat" class="form-control"
                                       value="{{ old('lat', $building->lat) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Longitude</label>
                                <input id="lng" name="lng" class="form-control"
                                       value="{{ old('lng', $building->lng) }}" readonly>
                            </div>
                        </div>

                        {{-- Geolocate + Map --}}
                        <div class="d-flex justify-content-end mb-2">
                            <button id="locate-me" type="button" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-current-location"></i> Use my location
                            </button>
                        </div>
                        <div id="map" style="width:100%;height:360px;border-radius:8px;border:1px solid #e5e7eb;" class="mb-4"></div>

                        {{-- Image --}}
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" id="image" name="image" class="form-control">
                            @if($building->image)
                                <img src="{{ $building->image }}" alt="Building Image" class="img-thumbnail mt-2" style="max-width: 200px;">
                            @endif
                        </div>

                        <div class="col-12 text-end">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Expenses (unchanged) --}}
            <div class="card-body">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            <span>Expense</span>
                            <button type="button" class="btn btn-sm btn-soft-primary add-expense-btn"
                                    data-id="{{ $unit->id ?? $building->id }}"
                                    data-type="{{ isset($unit) ? 'unit' : 'building' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addExpenseModal">
                                Add Expense
                            </button>
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-borderless table-centered">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Description</th>
                                <th scope="col">Allocation</th>
                                <th class="border-0 py-2" scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($building->expenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_name }}</td>
                                    <td>{{ $expense->amount }}</td>
                                    <td><p class="card-text mb-0">{{ Str::limit($expense->description, 120) }}</p></td>
                                    <td>{{ $expense->allocation_type == 'unit' ? 'Belongs to unit' : 'To building' }}</td>
                                    <td>
                                        @if (auth()->user()->hasPermission("delete $Name"))
                                            <form id="delete-form-{{ $expense->id }}" style="display: inline" method="POST"
                                                  action="{{ route('unit-expenses.destroy', $expense->id) }}">
                                                @csrf @method('DELETE')
                                                <a href="#!" onclick="confirmDelete({{ $expense->id }})"
                                                   class="btn btn-sm btn-soft-danger">
                                                    <i class="bx bx-trash fs-16"></i>
                                                </a>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if ($building->end_price)
                                <tr>
                                    <td>Selling price</td>
                                    <td>{{ $building->end_price }}</td>
                                    <td>Price to selling</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                            </tbody>
                        </table>

                        <div class="col-sm-12">
                            <div class="float-end">
                                <h3>TOTAL : ${{ $building->expenses->sum('amount') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (!$building->is_payed)
                <div class="card-body">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title d-flex justify-content-between align-items-center">
                                <span>Building sale</span>
                                <a class="btn btn-sm btn-soft-danger"
                                   href="{{ route('contracts.createbuilding', ['building_id' => $building->id]) }}">
                                    Building sale
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@section('scripts')
<script>
/**
 * Edit Building: Map + Geolocation + Reverse Geocode
 * - Default to Lebanon (LB)
 * - Drag marker / click map / Use my location -> sets lat/lng + location + country (ISO-2)
 */
(function () {
  let map, marker, geocoder;

  const DEFAULT_CENTER = { lat: 33.8938, lng: 35.5018 }; // Beirut

  const latEl       = document.getElementById('lat');
  const lngEl       = document.getElementById('lng');
  const locInput    = document.getElementById('location');
  const countryHid  = document.getElementById('country');
  const countrySel  = document.getElementById('country-select');
  const locateBtn   = document.getElementById('locate-me');

  function updateLatLng(lat, lng) {
    latEl.value = Number(lat).toFixed(7);
    lngEl.value = Number(lng).toFixed(7);
  }

  function setCountryFromComponents(components) {
    const c = (components || []).find(x => (x.types || []).includes('country'));
    if (c && c.short_name) {
      countryHid.value = c.short_name;
      countrySel.value = c.short_name;
    }
  }

  function reverseGeocodeToForm(lat, lng) {
    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
      if (status === 'OK' && results && results[0]) {
        // Fill the editable Location field with the formatted address
        locInput.value = results[0].formatted_address;
        setCountryFromComponents(results[0].address_components);
      }
    });
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

  window.initEditBuildingMap = function () {
    geocoder = new google.maps.Geocoder();

    // Default country LB if empty
    if (!countryHid.value) countryHid.value = 'LB';
    if (!countrySel.value) countrySel.value = 'LB';

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

    // Drag marker → update + reverse geocode
    marker.addListener('dragend', () => {
      const p = marker.getPosition();
      moveMarkerTo({ lat: p.lat(), lng: p.lng() }, 17, true);
    });

    // Click map → move marker + reverse geocode
    map.addListener('click', (e) => {
      moveMarkerTo({ lat: e.latLng.lat(), lng: e.latLng.lng() }, 17, true);
    });

    // Country dropdown → keep hidden in sync (ISO-2)
    countrySel.addEventListener('change', () => {
      countryHid.value = countrySel.value || '';
    });

    // Ensure coords filled at load
    if (isNaN(existingLat) || isNaN(existingLng)) {
      updateLatLng(start.lat, start.lng);
    }

    // Geolocate button
    locateBtn?.addEventListener('click', useMyLocation);
  };
})();
</script>

{{-- Google Maps (no Places needed here) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initEditBuildingMap" async defer></script>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من استعادة هذا العنصر بعد الحذف!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، احذف!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }
</script>
@endsection
