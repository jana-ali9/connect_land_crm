@extends('layouts.vertical', ['subTitle' => 'Land Details', 'title' => 'Admin'])

@php($Name = 'lands')

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Land', 'subTitle' => 'Land Details'])

    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h4 mb-1">Land Details</h1>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('lands.index') }}" class="btn btn-outline-secondary">← Back to List</a>
                        @if (auth()->user()->hasPermission("update $Name"))
                            <a href="{{ route('lands.edit', $land) }}" class="btn btn-primary">Edit</a>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    {{-- LEFT: Photo + Key Numbers --}}
                    <div class="col-lg-5">
                        <div class="position-relative rounded-3 overflow-hidden border bg-light-subtle">
                            <img src="{{ $land->photo_url }}" alt="Land Photo"
                                 class="w-100 d-block" style="object-fit: cover; aspect-ratio: 4/3;">
                            @if ($land->area)
                                <span class="badge text-bg-dark position-absolute top-0 end-0 m-2 shadow-sm">
                                    {{ number_format($land->area) }} m²
                                </span>
                            @endif
                        </div>

                        <ul class="list-group list-group-flush mt-3 rounded-3 overflow-hidden">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Property #</span>
                                <span class="fw-semibold">{{ $land->property_number ?? '—' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Section #</span>
                                <span class="fw-semibold">{{ $land->section_number ?? '—' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">District Zone</span>
                                <span class="fw-semibold">{{ $land->district_zone ?? '—' }}</span>
                            </li>

                        </ul>
                    </div>

                    {{-- RIGHT: Details --}}
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="border rounded-3 p-3 h-100">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-4 text-muted">Name</dt>
                                        <dd class="col-sm-8 fw-semibold">{{ $land->name }}</dd>

                                        <dt class="col-sm-4 text-muted">Address (Google Maps)</dt>
                                        <dd class="col-sm-8">{{ $land->location }}</dd>

                                       

                                        <dt class="col-sm-4 text-muted">Country</dt>
                                        <dd class="col-sm-8">{{ $land->country ?? '—' }}</dd>

                                        <dt class="col-sm-4 text-muted">Latitude</dt>
                                        <dd class="col-sm-8">{{ $land->lat ?? '—' }}</dd>

                                        <dt class="col-sm-4 text-muted">Longitude</dt>
                                        <dd class="col-sm-8">{{ $land->lng ?? '—' }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3 h-100">
                                    <h2 class="h6 mb-2">Description</h2>
                                    <p class="mb-0">{{ $land->description ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MAP --}}
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">Location on Map</h2>
                        @if ($land->lat && $land->lng)
                            <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"
                               href="https://www.google.com/maps?q={{ $land->lat }},{{ $land->lng }}">Open in Google Maps</a>
                        @endif
                    </div>
                    <div class="ratio ratio-16x9 rounded-3 border overflow-hidden" id="land-map"></div>
                    <small class="text-muted d-block mt-2">
                        @if ($land->lat && $land->lng)
                            Showing marker at ({{ $land->lat }}, {{ $land->lng }})
                        @else
                            No coordinates saved yet; map is centered on Beirut by default.
                        @endif
                    </small>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (function () {
            const DEFAULT_CENTER = { lat: 33.8938, lng: 35.5018 };

            window.initLandShowMap = function () {
                const lat = Number('{{ $land->lat }}');
                const lng = Number('{{ $land->lng }}');
                const hasCoords = !Number.isNaN(lat) && !Number.isNaN(lng);

                const center = hasCoords ? { lat, lng } : DEFAULT_CENTER;

                const map = new google.maps.Map(document.getElementById('land-map'), {
                    center,
                    zoom: hasCoords ? 16 : 12,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                });

                if (hasCoords) {
                    new google.maps.Marker({
                        map,
                        position: center,
                        title: @json($land->name ?? 'Land'),
                        animation: google.maps.Animation.DROP,
                    });
                }
            };
        })();
    </script>

    {{-- Load Google Maps JS; no Places needed on show --}}
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initLandShowMap" async defer></script>
@endsection
