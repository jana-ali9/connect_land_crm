@extends('layouts.vertical', ['title' => 'Unit Details'])

@php
    use Illuminate\Support\Str;

    // Image helpers
    $unitImg = $unit->image_path
        ? (Str::startsWith($unit->image_path, ['http://', 'https://']) ? $unit->image_path : asset('storage/'.$unit->image_path))
        : asset('default.png');

    $bldImg  = $building->image_path
        ? (Str::startsWith($building->image_path, ['http://', 'https://']) ? $building->image_path : asset('storage/'.$building->image_path))
        : asset('default.png');

    $hasCoords = is_numeric($building->lat ?? null) && is_numeric($building->lng ?? null);

    // Small labels
    $locLabel = $building->location ?: $building->address;
@endphp

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Units', 'subTitle' => 'Unit details'])

    <style>
        .unit-hero {
            display:grid; grid-template-columns: 1.2fr .8fr; gap:1.25rem;
        }
        @media (max-width: 992px){ .unit-hero { grid-template-columns: 1fr; } }

        .card-clean {
            border:0; border-radius:1rem; background:#fff;
            box-shadow: 0 8px 30px rgba(0,0,0,.06);
        }
        .media-cover { aspect-ratio: 16/9; width:100%; object-fit:cover; border-radius: .75rem; }
        .meta-chip {
            display:inline-flex; align-items:center; gap:.4rem;
            padding:.35rem .6rem; border:1px solid #e5e7eb; border-radius:999px;
            background:#f8fafc; color:#111827; font-weight:600; font-size:.85rem;
        }
        .list-keys { list-style:none; padding:0; margin:0; }
        .list-keys li { display:flex; justify-content:space-between; padding:.5rem 0; border-bottom:1px dashed #eef2f7; }
        .list-keys li span:first-child { color:#6b7280; }
        .cta-row { display:flex; gap:.5rem; flex-wrap:wrap; }
        .btn-gradient {
            border:0; color:#fff; font-weight:700; border-radius:.75rem;
            background:linear-gradient(135deg,#06b6d4,#3b82f6);
        }
        .btn-soft {
            border:1px solid #e5e7eb; background:#f8fafc; color:#111827; font-weight:600; border-radius:.75rem;
        }
        #unit-map { width:100%; height:420px; border-radius:12px; border:1px solid #e5e7eb; }
    </style>

    <div class="unit-hero">
        {{-- Left: visual + description --}}
        <div class="card-clean p-3">
            <img src="{{ $unitImg }}" alt="Unit Image" class="media-cover mb-3">

            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <h2 class="mb-1 fw-bold">{{ $unit->name ?? 'Unit' }}</h2>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="meta-chip">
                            <i class="bx bx-building-house"></i> {{ $building->name }}
                        </span>

                        @if($locLabel)
                            <a class="meta-chip text-decoration-none"
                               href="https://www.google.com/maps/search/?api=1&query={{ urlencode($locLabel) }}"
                               target="_blank" rel="noopener">
                                <i class="bx bx-map-pin"></i> {{ Str::limit($locLabel, 40) }}
                            </a>
                        @endif

                        @if(!is_null($unit->area))
                            <span class="meta-chip"><i class="bx bx-ruler"></i> {{ (float)$unit->area }} m²</span>
                        @endif

                        @if($unit->is_rented)
                            <span class="badge bg-success rounded-pill">Rented</span>
                        @else
                            <span class="badge bg-secondary rounded-pill">Available</span>
                        @endif
                    </div>
                </div>

                <img src="{{ $bldImg }}" alt="Building" class="d-none d-lg-block" style="width:120px;height:80px;object-fit:cover;border-radius:.5rem;">
            </div>

            <hr class="my-3">

            <p class="text-muted mb-0">{{ Str::of($unit->description ?? '')->isNotEmpty() ? $unit->description : 'No description.' }}</p>
        </div>

        {{-- Right: quick facts / actions --}}
        <div class="card-clean p-3">
            <h5 class="mb-3 fw-bold">Quick Facts</h5>
            <ul class="list-keys">
                <li><span>Building</span><span class="fw-semibold">{{ $building->name }}</span></li>
                <li><span>Status</span>
                    <span class="fw-semibold">{{ $unit->is_rented ? 'Rented' : 'Available' }}</span>
                </li>
                @if(!is_null($unit->start_price))
                    <li><span>Start Price</span><span class="fw-semibold">${{ number_format($unit->start_price, 2) }}</span></li>
                @endif
                @if(!is_null($unit->end_price))
                    <li><span>End Price</span><span class="fw-semibold">${{ number_format($unit->end_price, 2) }}</span></li>
                @endif
                <li>
                    <span>Coordinates</span>
                    <span class="fw-semibold">
                        @if($hasCoords)
                            {{ (float)$building->lat }}, {{ (float)$building->lng }}
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </span>
                </li>
            </ul>

            <div class="mt-3 cta-row">
                @can('update units')
                    <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-gradient px-3">
                        <i class="bx bx-edit me-1"></i> Edit Unit
                    </a>
                @endcan

                @can('read buildings')
                    <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-soft px-3">
                        <i class="bx bx-building me-1"></i> Open Building
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Map (always at bottom) --}}
    <div class="card-clean p-3 mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-bold">Location</h5>
            @if($hasCoords && $locLabel)
                <a class="btn btn-sm btn-outline-primary"
                   href="https://www.google.com/maps/search/?api=1&query={{ urlencode($locLabel) }}"
                   target="_blank" rel="noopener">
                    <i class="bx bx-directions me-1"></i> Open in Google Maps
                </a>
            @endif
        </div>

        @if($hasCoords)
            <div id="unit-map"></div>
        @else
            <div class="p-4 text-center text-muted" style="border:1px dashed #e5e7eb;border-radius:12px;">
                Building coordinates are not set yet.
            </div>
        @endif
    </div>
@endsection

{{-- Map script --}}
@section('scripts')
@if($hasCoords)
<script>
(function(){
  const POS = { lat: Number({{ (float)$building->lat }}), lng: Number({{ (float)$building->lng }}) };

  function cardHtml(){
    const title = @json($unit->name ?? 'Unit');
    const bname = @json($building->name ?? 'Building');
    const desc  = @json(Str::limit($unit->description ?? ($building->description ?? ''), 140));
    return `
      <div style="max-width:260px">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <strong>${escapeHtml(title)}</strong>
          <span class="badge bg-primary">Unit</span>
        </div>
        <div class="text-muted" style="font-size:.9rem">${escapeHtml(desc)}</div>
        <div class="mt-2"><span class="badge bg-light text-dark"><i class="bx bx-building me-1"></i>${escapeHtml(bname)}</span></div>
      </div>`;
  }

  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  window.initUnitMap = function(){
    const el = document.getElementById('unit-map');
    if(!el) return;

    const map = new google.maps.Map(el, {
      center: POS, zoom: 15, mapTypeControl:false, streetViewControl:false, fullscreenControl:true
    });

    const marker = new google.maps.Marker({
      position: POS,
      map,
      title: @json($unit->name ?? 'Unit'),
      icon: {
        url: 'data:image/svg+xml;utf8,' + encodeURIComponent(`
          <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 48 48">
            <path d="M24 46c10-12 16-18 16-26A16 16 0 1 0 8 20c0 8 6 14 16 26Z" fill="#3b82f6"/>
            <circle cx="24" cy="20" r="6.5" fill="#fff"/>
          </svg>`),
        scaledSize: new google.maps.Size(36, 36),
        anchor: new google.maps.Point(18, 36)
      }
    });

    const inf = new google.maps.InfoWindow({ content: cardHtml() });
    marker.addListener('click', () => inf.open({ anchor: marker, map }));
  };
})();
</script>

{{-- Load Google Maps once (no Places needed here) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initUnitMap" async defer></script>
@endif
@endsection
