@extends('layouts.vertical', ['subTitle' => 'Building Details', 'title' => 'Buildings'])
@php $Name = 'buildings' @endphp

@section('content')
  @include('layouts.partials/page-title', [
    'title'    => 'Buildings',
    'subTitle' => 'Building Details'
  ])

  {{-- ====== Modern UI (same colors) ====== --}}
 <style>
  /* === Light tokens === */
  :root{
    --card-bg:#ffffff;
    --surface:#f8fafc;
    --ring:#e5e7eb;
    --ink:#111827;
    --muted:#6b7280;
    --brand:#3b82f6;
    --brand-2:#06b6d4;

    --badge-on-bg:#ecfdf5;
    --badge-on-border:#bbf7d0;
    --badge-on-ink:#047857;

    --badge-off-bg:#fef2f2;
    --badge-off-border:#fecaca;
    --badge-off-ink:#991b1b;

    --shadow-1:0 10px 30px rgba(0,0,0,.05);
    --shadow-1h:0 14px 34px rgba(0,0,0,.12);
  }

  /* === Dark tokens === */
  body.dark, [data-bs-theme="dark"]{
    --card-bg:#0b1020;
    --surface:#111827;
    --ring:#1f2937;
    --ink:#e5e7eb;
    --muted:#9ca3af;

    --badge-on-bg:#064e3b;
    --badge-on-border:#065f46;
    --badge-on-ink:#34d399;

    --badge-off-bg:#7f1d1d;
    --badge-off-border:#991b1b;
    --badge-off-ink:#fecaca;

    --shadow-1:0 10px 30px rgba(0,0,0,.45);
    --shadow-1h:0 14px 34px rgba(0,0,0,.65);
  }

  .x-container{max-width:1200px;margin-inline:auto;padding:1rem}
  .x-header{display:flex;gap:1rem;align-items:center;justify-content:space-between;flex-wrap:wrap}
  .x-breadcrumb{font-weight:700;color:var(--muted);font-size:.9rem}
  .x-actions{display:flex;gap:.5rem;flex-wrap:wrap}

  .btnx{display:inline-flex;align-items:center;gap:.5rem;border-radius:12px;padding:.6rem .9rem;
    font-weight:700;border:1px solid var(--ring);background:var(--surface);color:var(--ink);
    transition:transform .15s ease, background .15s ease, box-shadow .15s ease}
  .btnx:hover{transform:translateY(-1px);background:var(--ring);box-shadow:0 6px 18px rgba(2,6,23,.15)}

  .btnx-primary{display:flex;align-items:center;padding:10px;border-radius:12px;
    background:linear-gradient(135deg,var(--brand-2),var(--brand));border:0;color:#fff}
  .btnx-primary:hover{filter:brightness(.97)}

  .x-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:1rem}
  @media (max-width: 992px){ .x-grid{grid-template-columns:1fr} }

  .cardx{background:var(--card-bg);border:1px solid var(--ring);border-radius:18px;
    box-shadow:var(--shadow-1);overflow:hidden;color:var(--ink)}
  .cardx:hover{box-shadow:var(--shadow-1h)}
  .cardx-body{padding:1rem 1rem 1.2rem}

  .hero{position:relative;aspect-ratio:16/9;background:#f3f4f6;overflow:hidden}
  body.dark .hero{background:#1f2937}
  .hero img{width:100%;height:100%;object-fit:cover;display:block}
  .hero-badge{position:absolute;right:12px;bottom:12px;background:rgba(17,24,39,.72);color:#fff;
    padding:.35rem .6rem;border-radius:999px;font-weight:800;font-size:.82rem}

  .meta{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap}
  .chip{display:inline-flex;align-items:center;gap:.45rem;border:1px solid var(--ring);
    background:var(--surface);color:var(--ink);border-radius:999px;padding:.38rem .65rem;
    font-weight:700;font-size:.85rem}
  .muted{color:var(--muted)}
  .price{display:flex;gap:.35rem;align-items:baseline;font-weight:800}
  .price .num{font-size:1.05rem}

  .units-head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
  .units-grid{display:flex;flex-direction:column;gap:1rem}
  .unit-card{display:flex;height:100%;background:var(--card-bg);border:1px solid var(--ring);border-radius:14px;overflow:hidden}
  .unit-media{aspect-ratio:4/3;background:#f3f4f6}
  body.dark .unit-media{background:#1f2937}
  .unit-media img{width:100%;height:100%;object-fit:cover}
  .unit-body{padding:12px}
  .badge{display:inline-flex;align-items:center;gap:.4rem;border-radius:999px;padding:.25rem .55rem;font-weight:800;font-size:.74rem}
  .badge-on{background:var(--badge-on-bg);color:var(--badge-on-ink);border:1px solid var(--badge-on-border)}
  .badge-off{background:var(--badge-off-bg);color:var(--badge-off-ink);border:1px solid var(--badge-off-border)}
  .divider{height:1px;background:var(--ring);margin:.7rem 0}

  #map{width:100%;height:400px;border-radius:16px;overflow:hidden}
</style>


  <div class="x-container">
    {{-- ===== Header ===== --}}
    <div class="x-header">
      <div>
        <div class="x-breadcrumb">Properties / Buildings</div>
        <h1 class="mt-2" style="font-size:1.6rem;font-weight:900">{{ $building->name }}</h1>
        @if($building->address)
          <div class="muted">{{ $building->address }}</div>
        @elseif($building->location)
          <div class="muted">{{ $building->location }}</div>
        @endif
      </div>

      <div class="x-actions">
        <a class="btnx" href="{{ route("$Name.index") }}"><i class="bx bx-arrow-back"></i> Back</a>
        @if (auth()->user()->hasPermission('update buildings'))
          <a class="btnx" href="{{ route("$Name.edit", $building->id) }}"><i class="bx bx-edit-alt"></i> Edit</a>
        @endif
        @if (auth()->user()->hasPermission('read units'))
          <a class="btnx-primary" href="{{ route('units.index', ['building_id' => $building->id]) }}">
            <i class="bx bx-building-house"></i> View Units
          </a>
        @endif
      </div>
    </div>

    {{-- ===== Grid ===== --}}
    <div class="x-grid mt-3">
      {{-- Left --}}
      <div class="stack" style="display:grid;gap:1rem">
        {{-- Hero --}}
        <div class="cardx">
          <div class="hero">
            @php
              $hero = $building->image_path
                ? \Illuminate\Support\Facades\Storage::url($building->image_path)
                : asset('default.png');
            @endphp
            <img src="{{ $hero }}" alt="{{ $building->name }}" loading="lazy">
            @if(!is_null($building->start_price) || !is_null($building->end_price))
              <div class="hero-badge">
                <span class="price">
                  <span class="num">
                    @if(!is_null($building->start_price)) {{ number_format($building->start_price, 2) }} @endif
                    @if(!is_null($building->end_price)) – {{ number_format($building->end_price, 2) }} @endif
                  </span>
                  <span class="muted">USD</span>
                </span>
              </div>
            @endif
          </div>

          <div class="cardx-body">
            <div class="meta">
              @php
                $hasCoords = !is_null($building->lat) && !is_null($building->lng);
                $label = trim($building->location ?: ($building->address ?? 'View on map'));
                $country = strtoupper($building->country ?? '');
                $mapsUrl = $hasCoords
                  ? ('https://www.google.com/maps?q='.$building->lat.','.$building->lng)
                  : ('https://www.google.com/maps/search/?api=1&query='.urlencode($label.($country ? " $country" : '')));
              @endphp
              <a class="chip" href="{{ $mapsUrl }}" target="_blank" rel="noopener">
                <i class="bx bx-map-pin"></i> {{ $label }} @if($country) • {{ $country }} @endif
              </a>

              @if(!($building->is_payed ?? false))
                @php
                  $payed  = (int)($building->payed_units_count ?? 0);
                  $rented = (int)($building->rented_units_count ?? 0);
                  $pct    = $payed > 0 ? round(($rented / max($payed,1)) * 100) : 0;
                @endphp
                <span class="chip"><i class="bx bx-bar-chart-alt-2"></i> {{ $rented }} / {{ $payed }} units • {{ $pct }}%</span>
              @endif
            </div>

            @if($building->description)
              <div class="divider"></div>
              <div>
                <h3 style="font-weight:900;font-size:1.05rem;margin-bottom:.35rem">About this building</h3>
                <p class="muted" style="line-height:1.65">{{ $building->description }}</p>
              </div>
            @endif
          </div>
        </div>

        {{-- Units --}}
        <div class="cardx">
          <div class="cardx-body">
            <div class="units-head">
              <h3 style="font-weight:900;font-size:1.1rem;margin:0">Units in this building</h3>
              @if (auth()->user()->hasPermission('read units'))
                <a class="btnx" href="{{ route('units.index', ['building_id' => $building->id]) }}">
                  <i class="bx bx-filter"></i> All units
                </a>
              @endif
            </div>

            @if($building->units->isEmpty())
              <div class="muted mt-2">No units found for this building.</div>
            @else
              <div class="units-grid mt-3" style="    display: flex;flex-direction: column;">
                @foreach($building->units as $unit)
                  <div class="cardx unit-card">
                    <div class="unit-media">
                      @php
                        $uimg = $unit->image_path ? \Illuminate\Support\Facades\Storage::url($unit->image_path) : asset('default.png');
                      @endphp
                      <img src="{{ $uimg }}" alt="{{ $unit->name }}" loading="lazy">
                    </div>
                    <div class="unit-body">
                      <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem">
                        <div style="font-weight:900">{{ $unit->name }}</div>
                        @if($unit->is_rented)
                          <span class="badge badge-off"><i class="bx bx-check-circle"></i> Rented</span>
                        @else
                          <span class="badge badge-on"><i class="bx bx-time"></i> Available</span>
                        @endif
                      </div>

                      @if(!is_null($unit->start_price) || !is_null($unit->end_price))
                        <div class="muted mt-1" style="font-weight:700">
                          @if(!is_null($unit->start_price)) {{ number_format($unit->start_price, 2) }} @endif
                          @if(!is_null($unit->end_price)) – {{ number_format($unit->end_price, 2) }} @endif
                          <span class="muted">USD</span>
                        </div>
                      @endif

                      @if($unit->area)
                        <div class="muted" style="font-size:.9rem">
                          Area: {{ rtrim(rtrim(number_format($unit->area, 2), '0'), '.') }} m²
                        </div>
                      @endif



                      <div style="display :grid ;gap:.5rem;flex-wrap:wrap;margin-top:.55rem">
                        @if (auth()->user()->hasPermission('read units'))
                          <a class="btnx" href="{{ route('units.index', ['building_id' => $building->id]) }}">
                            <i class="bx bx-show"></i> View
                          </a>
                        @endif
                        @if (auth()->user()->hasPermission('create expenseOffers') || auth()->user()->hasPermission('create unit_expenses'))
                          <button class="btnx" type="button"
                                  data-id="{{ $unit->id }}" data-type="unit"
                                  data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                            <i class="bx bx-plus"></i> Add Expense
                          </button>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Right --}}
      <div class="stack" style="display:grid;gap:1rem">
        <div class="cardx">
          <div class="cardx-body">
            <h3 style="font-weight:900;font-size:1.05rem;margin-bottom:.6rem">Location</h3>
            @if(!is_null($building->lat) && !is_null($building->lng))
              <div id="map"></div>
              <div class="muted" style="margin-top:.6rem">
                <i class="bx bx-current-location"></i> {{ $building->address ?? $building->location }}
              </div>
            @else
              <div class="muted">
                No coordinates set.
                @if($building->location) <br>Saved location: {{ $building->location }} @endif
              </div>
            @endif
          </div>
        </div>

        <div class="cardx">
          <div class="cardx-body">
            <h3 style="font-weight:900;font-size:1.05rem;margin-bottom:.6rem">Quick facts</h3>
            <ul style="list-style:none;margin:0;padding:0;display:grid;gap:.5rem">
              <li class="muted"><i class="bx bx-id-card"></i> ID: <strong>#{{ $building->id }}</strong></li>
              <li class="muted"><i class="bx bx-flag"></i> Country: <strong>{{ strtoupper($building->country ?? '—') }}</strong></li>
              <li class="muted"><i class="bx bx-calendar"></i> Created: <strong>{{ optional($building->created_at)->format('Y-m-d') ?? '—' }}</strong></li>
              <li class="muted"><i class="bx bx-refresh"></i> Updated: <strong>{{ optional($building->updated_at)->format('Y-m-d') ?? '—' }}</strong></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Map: Google Maps (Normal/Roadmap) ===== --}}
  @if(!is_null($building->lat) && !is_null($building->lng))
    <script>
      window.initBuildingMap = function () {
        const lat = Number({{ $building->lat }});
        const lng = Number({{ $building->lng }});
        const map = new google.maps.Map(document.getElementById('map'), {
          center: { lat, lng },
          zoom: 15,
          mapTypeId: google.maps.MapTypeId.ROADMAP, // normal mode
          disableDefaultUI: false,
        });
        const marker = new google.maps.Marker({
          position: { lat, lng }, map, title: @json($building->name)
        });
        const info = new google.maps.InfoWindow({
          content: `<strong>{{ addslashes($building->name) }}</strong><br>{{ addslashes($building->address ?? $building->location ?? '') }}`
        });
        marker.addListener('click', () => info.open({anchor: marker, map}));
      };
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_KEY') }}&callback=initBuildingMap">
    </script>
  @endif
@endsection
