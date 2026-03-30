@extends('layouts.vertical', ['title' => 'Dashboard'])

@php
    use Illuminate\Support\Str;

    // ------- Prepare Pins (only with numeric lat/lng) -------
    $buildingPins = [];
    foreach ($allbuildings as $b) {
        if (isset($b->lat, $b->lng) && is_numeric($b->lat) && is_numeric($b->lng)) {
            $buildingPins[] = [
                'id' => $b->id,
                'type' => 'building',
                'name' => (string) $b->name,
                'desc' => (string) Str::limit((string) $b->description, 140),
                'lat' => (float) $b->lat,
                'lng' => (float) $b->lng,
                // OPEN goes to building SHOW:
                'url' => route('buildings.show', $b->id),
                'img' => $b->image
                    ? (Str::startsWith($b->image, ['http://', 'https://'])
                        ? $b->image
                        : asset($b->image))
                    : null,
            ];
        }
    }

    $landPins = [];
    foreach ($alllands as $l) {
        if (isset($l->lat, $l->lng) && is_numeric($l->lat) && is_numeric($l->lng)) {
            $landPins[] = [
                'id' => $l->id,
                'type' => 'land',
                'name' => (string) $l->name,
                'desc' => (string) Str::limit((string) $l->description, 140),
                'lat' => (float) $l->lat,
                'lng' => (float) $l->lng,
                // OPEN goes to land SHOW:
                'url' => route('lands.show', $l->id),
                'img' => $l->photo ? asset('storage/' . $l->photo) : null,
            ];
        }
    }
@endphp

@section('content')
<style>
  /* =========================
     THEME TOKENS (Light)
     ========================= */
  :root{
    --card-bg:#ffffff;
    --ink:#111827;
    --muted:#6b7280;
    --ring:#e5e7eb;

    --img-bg:#f3f4f6;
    --overlay-grad: linear-gradient(180deg, rgba(0,0,0,.0) 30%, rgba(0,0,0,.45) 100%);

    --chip-bg:#f8fafc;
    --chip-ink:#111827;
    --chip-border:#e5e7eb;

    --fab-bg:rgba(17,24,39,.75);
    --fab-ink:#ffffff;

    --soft-btn-bg:#f8fafc;
    --soft-btn-bg-hover:#eef2f7;
    --soft-btn-ink:#111827;
    --soft-btn-border:#e5e7eb;

    --badge-soft-bg:#eef2f7;
    --badge-soft-ink:#111827;
    --badge-soft-border:#e5e7eb;

    --progress-bg:#eef2f7;

    --grad-start:#06b6d4;
    --grad-end:#3b82f6;

    --shadow-1: 0 4px 20px rgba(0,0,0,.06);
    --shadow-1h:0 10px 30px rgba(0,0,0,.12);
    --shadow-2: 0 6px 24px rgba(0,0,0,.06);
    --shadow-2h:0 14px 34px rgba(0,0,0,.12);
  }

  /* =========================
     THEME TOKENS (Dark)
     Use either body.dark OR [data-bs-theme="dark"]
     ========================= */
  body.dark, [data-bs-theme="dark"]{
    --card-bg:#0b1020;
    --ink:#e5e7eb;
    --muted:#9aa3b2;
    --ring:#1f2a44;

    --img-bg:#0f172a;
    --overlay-grad: linear-gradient(180deg, rgba(0,0,0,.00) 30%, rgba(0,0,0,.55) 100%);

    --chip-bg:#0f172a;
    --chip-ink:#e5e7eb;
    --chip-border:#233156;

    --fab-bg:rgba(255,255,255,.18);
    --fab-ink:#ffffff;

    --soft-btn-bg:#0f172a;
    --soft-btn-bg-hover:#111b33;
    --soft-btn-ink:#e5e7eb;
    --soft-btn-border:#233156;

    --badge-soft-bg:#111b33;
    --badge-soft-ink:#e5e7eb;
    --badge-soft-border:#233156;

    --progress-bg:#0f172a;

    /* keep gradient brand colors the same */
    --shadow-1: 0 4px 20px rgba(0,0,0,.35);
    --shadow-1h:0 10px 30px rgba(0,0,0,.55);
    --shadow-2: 0 6px 24px rgba(0,0,0,.35);
    --shadow-2h:0 14px 34px rgba(0,0,0,.55);
  }

  /* Optional: respect OS dark if you don't set a class/attr */
  @media (prefers-color-scheme: dark){
    :root:not(.force-light){
      /* mirror the same dark tokens */
    }
  }

  /* =========================
     COMPONENTS
     ========================= */

  /* Grid */
  .b-grid .col { display:flex; }

  /* Land card (older block) */
  .land-card {
    border: 0;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: var(--shadow-1);
    transition: transform .25s ease, box-shadow .25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: var(--card-bg);
    color: var(--ink);
  }
  .land-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-1h);
  }
  .land-card .image-wrap {
    position: relative;
    aspect-ratio: 16/9;
    background: var(--img-bg);
  }
  .land-card .image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display:block;
  }
  .land-card .image-overlay {
    position: absolute; inset: 0;
    background: var(--overlay-grad);
    opacity: 0; transition: opacity .25s ease;
    display: flex; align-items: end; justify-content: end; padding: .5rem;
  }
  .land-card:hover .image-overlay { opacity: 1; }

  .quick-badges {
    position: absolute; left: .75rem; bottom: .75rem;
    display: flex; gap: .5rem; flex-wrap: wrap;
  }
  .chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .25rem .55rem; font-size: .75rem; font-weight: 600;
    border-radius: 999px; color: var(--chip-ink);
    background: var(--chip-bg); border:1px solid var(--chip-border);
    backdrop-filter: blur(6px);
  }
  .edit-fab {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 999px;
    background: var(--fab-bg); color: var(--fab-ink); border:0;
  }
  .land-card .card-body { padding: 1rem 1rem 0; }
  .land-title { margin: 0; font-weight: 700; line-height: 1.25; color: var(--ink); }
  .land-meta { color: var(--muted); font-size: .9rem; }
  .land-desc { color: var(--ink); opacity:.8; }

  .action-bar {
    margin-top: auto;
    padding: .75rem 1rem 1rem;
    display: flex; gap: .5rem; flex-wrap: wrap;
  }
  .btn-soft {
    border-radius: .75rem; border: 1px solid var(--soft-btn-border);
    background: var(--soft-btn-bg); color: var(--soft-btn-ink); font-weight: 600;
  }
  .btn-soft:hover { background: var(--soft-btn-bg-hover); }

  .btn-gradient, .btn-grad {
    border:0; border-radius:.75rem; color:#fff; font-weight:700;
    background: linear-gradient(135deg, var(--grad-start), var(--grad-end));
  }
  .btn-gradient:hover, .btn-grad:hover { filter: brightness(.95); }

  /* Newer “b-” card set */
  .b-card{
    border:0;border-radius:16px;background:var(--card-bg);overflow:hidden;
    box-shadow:var(--shadow-2);
    transition:transform .2s ease, box-shadow .2s ease;
    display:flex;flex-direction:column;width:100%; color:var(--ink);
  }
  .b-card:hover{ transform:translateY(-3px); box-shadow:var(--shadow-2h); }

  .b-media{ position:relative; aspect-ratio:16/9; background:var(--img-bg); }
  .b-media img{ width:100%;height:100%;object-fit:cover;display:block; }

  .b-overlay{
    position:absolute; inset:0; display:flex; align-items:flex-end; justify-content:flex-end; padding:.5rem;
    background:var(--overlay-grad); opacity:0; transition:opacity .2s ease;
  }
  .b-card:hover .b-overlay{ opacity:1; }

  .b-fab{
    width:40px;height:40px;border-radius:999px;border:0; color:var(--fab-ink);
    background:var(--fab-bg); display:inline-flex;align-items:center;justify-content:center;
  }

  .b-body{ padding:14px 14px 10px; display:flex;flex-direction:column; gap:8px; }
  .b-title{ margin:0; font-weight:700; font-size:1.05rem; line-height:1.25; color:var(--ink); }
  .b-desc{ color:var(--ink); opacity:.85; font-size:.925rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

  .chip-row{ display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.1rem; }
  .chip i{ font-size:1rem; }

  .b-stats{ display:flex; align-items:center; justify-content:space-between; gap:8px; }
  .badge-soft{
    background:var(--badge-soft-bg); color:var(--badge-soft-ink);
    border:1px solid var(--badge-soft-border); font-weight:600;
  }
  .progress{ --bs-progress-height:.5rem; background:var(--progress-bg); }
  .progress-bar{ transition:width .4s ease; }

  .b-actions{ display:flex; gap:.5rem; padding:10px 14px 14px; margin-top:auto; flex-wrap:wrap; }
  .btn-grad{ background:linear-gradient(135deg,var(--grad-start),var(--grad-end)); color:#fff; }
</style>

    @if (auth()->user()->hasPermission('read dashboard'))
        {{-- Time Filter --}}
        <div class="d-flex justify-content-start mb-4 gap-4">
            <form method="GET" action="{{ url()->current() }}">
                <div class="btn-group" role="group" aria-label="Time Filter">
                    <button type="submit" name="filter" value="all"
                        class="me-2 btn btn-outline-primary {{ request('filter', 'all') === 'all' ? 'active' : '' }}">
                        All Time
                    </button>
                    <button type="submit" name="filter" value="year"
                        class="me-2 btn btn-outline-primary {{ request('filter') === 'year' ? 'active' : '' }}">
                        This Year
                    </button>
                    <button type="submit" name="filter" value="month"
                        class="me-2 btn btn-outline-primary {{ request('filter') === 'month' ? 'active' : '' }}">
                        This Month
                    </button>
                    <button type="submit" name="filter" value="week"
                        class="me-2 btn btn-outline-primary {{ request('filter') === 'week' ? 'active' : '' }}">
                        This Week
                    </button>
                </div>
            </form>

        </div>
<button type="button" class="btn btn-outline-secondary {{ request('filter') === 'custom' ? 'active' : '' }}" onclick="toggleCustomDate()">
            <i class="bx bx-calendar-event me-1"></i> Custom Range
        </button>
    </div>

    {{-- صندوق اختيار التاريخ - يظهر فقط عند الضغط على الزر أو إذا كان الفلتر مفعل --}}
    <div id="custom-date-box" class="card shadow-sm border-dashed p-3" style="display: {{ request('filter') === 'custom' ? 'block' : 'none' }}; max-width: 600px; background: var(--badge-soft-bg);">
        <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
            <input type="hidden" name="filter" value="custom">
            <div class="col-md-5">
                <label class="form-label small fw-bold">From Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold">To Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>
        {{-- KPI Row --}}
        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="card1">
                    <div class="card-content">
                        <div class="text-section">
                            <p class="label">Building</p>
                            <p class="number">{{ count($allbuildings) }}</p>
                            @if (auth()->user()->hasPermission('create buildings'))
                                <a class="create-btn d-inline-flex align-items-center gap-1"
                                    href="{{ route('buildings.create') }}">
                                    <span>+ Create Building</span>
                                </a>
                            @endif
                        </div>
                        <div class="icon-section"><img src="/images/Icon.png" width="60" height="60" alt="Building">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card1">
                    <div class="card-content">
                        <div class="text-section">
                            <p class="label">Active Contracts</p>
                            <p class="number">{{ $activeContractsCount }}</p>
                            @if (auth()->user()->hasPermission('read contracts'))
                                <a class="create-btn d-inline-flex align-items-center gap-1"
                                    href="{{ route('contracts.index') }}">
                                    <i class="bx bx-show fs-16"></i><span>Show Contract</span>
                                </a>
                            @endif
                        </div>
                        <div class="icon-section"><img src="/images/Icon2.png" width="60" height="60"
                                alt="Contracts"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card1">
                    <div class="card-content">
                        <div class="text-section">
                            <p class="label">Lands</p>
                            <p class="number">{{ isset($alllands) ? count($alllands) : 0 }}</p>
                            @if (auth()->user()->hasPermission('create lands'))
                                <a class="create-btn d-inline-flex align-items-center gap-1"
                                    href="{{ route('lands.create') }}">
                                    <span>+ Create Land</span>
                                </a>
                            @endif
                        </div>
                        <div class="icon-section"><img src="/images/land.png" width="60" height="60" alt="Land">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card1">
                    <div class="card-content">
                        <div class="text-section">
                            <p class="label">Amount Collected</p>
                            <p class="number">${{ number_format($currentAmountPaid) }}</p>
                            @if (auth()->user()->hasPermission('read invoices'))
                                <a class="create-btn d-inline-flex align-items-center gap-1"
                                    href="{{ route('invoices.history') }}">
                                    <i class="bx bx-time fs-16"></i><span>Amount History</span>
                                </a>
                            @endif
                        </div>
                        <div class="icon-section"><img src="/images/Icon3.png" width="60" height="60"
                                alt="Collected"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card1">
                    <div class="card-content">
                        <div class="text-section">
                            <p class="label">Amount Due</p>
                            <p class="number">${{ number_format($currentAmountDue) }}</p>
                            @if (auth()->user()->hasPermission('read invoices'))
                                <a class="create-btn d-inline-flex align-items-center gap-1"
                                    href="{{ route('invoices.history') }}">
                                    <i class="bx bx-time fs-16"></i><span>Amount History</span>
                                </a>
                            @endif
                        </div>
                        <div class="icon-section"><img src="/images/Icon4.png" width="60" height="60" alt="Due">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card1">
                    <div class="card-content">
                        <div class="text-section">
                            <p class="label">Total Expenses</p>
                            <p class="number">${{ number_format($currentExpenses) }}</p>
                            @if (auth()->user()->hasPermission('read expenseOffers'))
                                <a class="create-btn d-inline-flex align-items-center gap-1"
                                    href="{{ route('expenseOffers.index') }}">
                                    <i class="bx bx-list-ul fs-16"></i><span>Show Expense</span>
                                </a>
                            @endif
                        </div>
                        <div class="icon-section"><img src="/images/expense-icon.png" width="60" height="60"
                                alt="Expense"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lands Grid --}}
      <div class="col-12 mb-3">
    <div class="row g-3">
      @foreach ($alllands as $land)
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="land-card">
            {{-- Image --}}
            <div class="image-wrap">
              <img src="{{ $land->photo ? asset('storage/' . $land->photo) : asset('default.png') }}" alt="Land photo">
              {{-- Quick chips (area + location) --}}
              <div class="quick-badges">
                @if(!empty($land->area))
                  <span class="chip">
                    <i class="bx bx-ruler"></i> {{ $land->area }} m²
                  </span>
                @endif
                @if(!empty($land->location))
                  <span class="chip" title="{{ $land->location }}">
                    <i class="bx bx-map"></i> {{ Str::limit($land->location, 18) }}
                  </span>
                @endif
              </div>
              {{-- Overlay actions (edit) --}}
              <div class="image-overlay">
                @if (auth()->user()->hasPermission('update lands'))
                  <a class="edit-fab" href="{{ route('lands.edit', $land->id) }}" aria-label="Edit">
                    <i class="bx bx-edit"></i>
                  </a>
                @endif
              </div>
            </div>

            {{-- Body --}}
            <div class="card-body">
              <h3 class="h5 land-title">{{ $land->name }}</h3>
              @if(!empty($land->address) || !empty($land->country))
                <div class="land-meta mb-1">
                  <i class="bx bx-current-location"></i>
                  {{ $land->address ?? $land->location }}
                  @if(!empty($land->country))
                    <span>• {{ $land->country }}</span>
                  @endif
                </div>
              @endif
              <p class="land-desc mb-0">{{ Str::limit($land->description, 120) }}</p>
            </div>

            {{-- Actions --}}
            <div class="action-bar">
              @if (auth()->user()->hasPermission('read lands'))
                <a href="{{ route('lands.show', $land->id) }}" class="btn btn-gradient">
                  <i class="bx bx-show me-1"></i> Show
                </a>
              @endif

              <button class="btn btn-soft flex-grow-1"
                type="button"
                data-id="{{ $unit->id ?? ($building->id ?? $land->id) }}"
                data-type="{{ isset($unit) ? 'unit' : (isset($building) ? 'building' : 'land') }}"
                data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bx bx-plus me-1"></i> Add Expense
              </button>

              @if (auth()->user()->hasPermission('delete lands'))
                <button class="btn btn-danger"
                        onclick="confirmDelete({{ $land->id }})">
                  <i class="bx bx-trash"></i>
                </button>
                <form id="delete-form-{{ $land->id }}" action="{{ route('lands.destroy', $land->id) }}" method="POST" class="d-none">
                  @csrf @method('DELETE')
                </form>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

        {{-- Buildings Grid --}}
         <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 b-grid">
        @foreach ($allbuildings as $building)
          @php
            $payed  = (int)($building->payed_units_count ?? 0);
            $rented = (int)($building->rented_units_count ?? 0);
            $pct    = $payed > 0 ? round(($rented / max($payed,1)) * 100) : 0;

            // Resolve image (url / storage / default)
            $imgSrc = $building->image
              ? (Str::startsWith($building->image, ['http://','https://'])
                    ? $building->image
                    : (Str::startsWith($building->image, 'storage/')
                        ? asset($building->image)
                        : asset('storage/'.$building->image)))
              : asset('default.png');

            // Location chip
            $hasCoords = isset($building->lat,$building->lng) && is_numeric($building->lat) && is_numeric($building->lng);
            $label     = trim($building->location ?: ($building->address ?? 'View on map'));
            $country   = strtoupper($building->country ?? '');
            $mapsUrl   = $hasCoords
              ? ('https://www.google.com/maps?q='.$building->lat.','.$building->lng)
              : ('https://www.google.com/maps/search/?api=1&query='.urlencode($label.($country ? " $country" : '')));
          @endphp

          <div class="col">
    <div class="b-card h-100">
        {{-- Image --}}
        <div class="b-media position-relative">
            <img src="{{ $imgSrc }}" alt="Building image">

            {{-- Overlay with Edit + View --}}
            <div class="b-overlay">
                <div class="d-flex flex-column align-items-end gap-2 p-2" style="position:absolute; top:8px; right:8px;">
                    @if (auth()->user()->hasPermission('update buildings'))
                        <a class="b-fab" href="{{ route('buildings.edit', $building->id) }}" aria-label="Edit">
                            <i class="bx bx-edit"></i>
                        </a>
                    @endif

                    @if (auth()->user()->hasPermission('read buildings'))
                        <a class="b-fab" href="{{ route("buildings.show", $building->id) }}" aria-label="View">
                            <i class="bx bx-buildings"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="b-body">
            <h3 class="b-title">{{ $building->name }}</h3>

            {{-- Location chip --}}
            <div class="chip-row">
                <a class="chip" href="{{ $mapsUrl }}" target="_blank" rel="noopener">
                    <i class="bx bx-map-pin"></i>
                    {{ $label }} @if ($country)<span>• {{ $country }}</span>@endif
                </a>
            </div>

            {{-- Stats --}}
            @if (!($building->is_payed ?? false))
                <div class="b-stats mt-2 mb-2">
                    <span class="badge-soft px-2 py-1 rounded-pill">{{ $rented }} / {{ $payed }} units</span>
                    <span class="badge bg-primary">{{ $pct }}%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            @endif

            <p class="b-desc mb-0">{{ Str::limit($building->description, 120) }}</p>
        </div>

        {{-- Actions --}}
        <div class="b-actions">
            <button class="btn btn-soft add-expense-btn" type="button"
                data-id="{{ $unit->id ?? $building->id }}"
                data-type="{{ isset($unit) ? 'unit' : 'building' }}"
                data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bx bx-plus me-1"></i> Add Expense
            </button>

            @if (auth()->user()->hasPermission('read units'))
                <a class="btn btn-grad" href="{{ route('units.index', ['building_id' => $building->id]) }}">
                    <i class="bx bx-show me-1"></i> Show Unit
                </a>
            @endif
        </div>
    </div>
</div>

        @endforeach
      </div>

        {{-- === Assets Map (Buildings + Lands) === --}}
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Your Properties on Map</h5>
                <div class="text-muted small">Hover a pin to preview • Click to open</div>
            </div>
            <div class="card-body">
                <div id="assets-map"
                    style="width:100%;min-height:520px;height:520px;border-radius:12px;border:1px solid #e5e7eb;"></div>
            </div>
        </div>

        {{-- Expose pins to JS --}}
        <script>
            window.__ASSET_PINS__ = {
                buildings: @json($buildingPins, JSON_UNESCAPED_UNICODE),
                lands: @json($landPins, JSON_UNESCAPED_UNICODE)
            };
        </script>

        <div class="row">
            @if (auth()->user()->hasPermission('read invoices'))
                @if (count($allinvoicesshow) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title d-flex justify-content-between align-items-center">
                                <span>invoices</span>
                            </h5>
                        </div> @include('layouts.partials.massages') <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-borderless table-centered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">User Name</th>
                                            <th scope="col">cole date</th>
                                            <th scope="col">Amount due</th>
                                            <th scope="col">Amount paid</th>
                                            <th scope="col">status</th>
                                            <th scope="col">type</th>
                                            <th class="border-0 py-2" scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allinvoicesshow as $invoice)
                                            @php $price = $invoice->type == 'service' ? $invoice->services_cost : $invoice->amount_due; @endphp <tr>
                                                <td>{{ $invoice->client->name }}</td>
                                                <td>{{ $invoice->invoice_date }} @if ($invoice->days_remaining > 5)
                                                        <span
                                                            class="badge bg-success badge-pill text-end">{{ $invoice->days_remaining }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-danger badge-pill text-end">{{ $invoice->days_remaining }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $price }}</td>
                                                <td>{{ $invoice->amount_paid }}</td>
                                                <td><span
                                                        class='badge status-badge badge-soft-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'pending' ? 'secondary' : 'danger') }}'>
                                                        {{ $invoice->status }} </span></td>
                                                <td>{{ $invoice->type }}</td>
                                                <td>
                                                    @if ($invoice->type == 'service')
                                                        <a href="{{ route('contracts.show', $invoice->contract_id) }}"
                                                            class="btn btn-sm btn-soft-primary me-1"><i
                                                                class="bx bx-cog fs-16"></i></a>
                                                    @else
                                                        @if ($invoice->status != 'paid')
                                                            @if (auth()->user()->hasPermission('update invoices'))
                                                                <button
                                                                    class="btn btn-sm btn-soft-primary me-1 update-status"
                                                                    data-id="{{ $invoice->id }}"
                                                                    data-status="{{ $invoice->status }}"> <i
                                                                        class="bx bx-cog fs-16"></i> </button>
                                                            @endif
                                                        @endif
                                                    @endif <a
                                                        href="{{ route('invoice.view', $invoice->id) }}" target="_blank"
                                                        class="btn btn-sm btn-soft-secondary me-1"><i
                                                            class="bx bx-show fs-16"></i></a> <a
                                                        href="https://wa.me/+961{{ $invoice->client->phone }}?text={{ route('invoice.view', $invoice->id) }}"
                                                        target="_blank" class="btn btn-sm btn-soft-success me-1"><i
                                                            class="bx bx-send fs-16"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                        document.querySelectorAll(".update-status").forEach(button => {
                                                    button.addEventListener("click", function() {
                                                                let invoiceId = this.getAttribute("data-id");
                                                                let currentStatus = this.getAttribute(
                                                                    "data-status"
                                                                ); // 🔄 التبديل بين الحالات مع جعل overdue تصبح paid تلقائيًا let newStatus; if (currentStatus === "pending") { newStatus = "paid"; } else if (currentStatus === "paid") { newStatus = "pending"; } else if (currentStatus === "overdue") { newStatus = "paid"; // 🔄 تحويل overdue إلى paid تلقائيًا } else { newStatus = "pending"; } Swal.fire({ title: "Confirm Status Change", text: Are you sure you want to change the status to ${newStatus}?, icon: "warning", showCancelButton: true, confirmButtonText: "Yes, change it!", cancelButtonText: "Cancel", }).then((result) => { if (result.isConfirmed) { fetch(/invoices/${invoiceId}, { method: "PUT", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector( 'meta[name="csrf-token"]').getAttribute( "content"), }, body: JSON.stringify({ status: newStatus }), }) .then(response => response.json()) .then(data => { if (data.success) { let badge = document.querySelector( #invoice-${invoiceId} .status-badge); if (badge) { badge.innerText = data.new_status; badge.classList.remove( "badge-soft-secondary", "badge-soft-danger", "badge-soft-success"); if (data.new_status === "paid") { badge.classList.add( "badge-soft-success"); } else if (data.new_status === "pending") { badge.classList.add( "badge-soft-secondary"); } else if (data.new_status === "overdue") { badge.classList.add( "badge-soft-danger"); } } Swal.fire("Updated!", "Invoice status has been changed.", "success").then(() => { location .reload(); // 🔄 تحديث الصفحة بعد نجاح العملية }); } else { Swal.fire("Error", "Failed to update status.", "error"); } }) .catch(() => { Swal.fire("Error", "Something went wrong.", "error"); }); } }); }); }); });
                        </script>
                @endif
            @endif
        </div> <!-- end row-->
        <div class="row">
            @if (auth()->user()->hasPermission('read invoices'))
                @if (count($expiringContracts) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title d-flex justify-content-between align-items-center">
                                <span>Contract</span>
                            </h5>
                        </div> @include('layouts.partials.massages') <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-borderless table-centered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">User Name</th>
                                            <th scope="col">cend_date</th>
                                            <th scope="col">rent</th>
                                            <th scope="col">status</th>
                                            <th class="border-0 py-2" scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($expiringContracts as $contract)
                                            <tr>
                                                <td>{{ $contract->client->name }}</td>
                                                <td>{{ $contract->end_date }} @php $daysRemaining = \Carbon\Carbon::parse( $contract->end_date, )->diffInDays(now()); @endphp
                                                    @if (abs((int) $daysRemaining) > 5)
                                                        <span
                                                            class="badge bg-success badge-pill text-end">{{ abs((int) $daysRemaining) }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-danger badge-pill text-end">{{ abs((int) $daysRemaining) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $contract->base_rent }}</td>
                                                <td><span
                                                        class='badge status-badge badge-soft-{{ $contract->contract_status == 'active' ? 'success' : ($contract->contract_status == 'expired' ? 'secondary' : 'danger') }}'>
                                                        {{ $contract->contract_status }} </span></td>
                                                <td>
                                                    @if (auth()->user()->hasPermission('read contracts'))
                                                        <a href="{{ route('contracts.show', $contract->id) }}"
                                                            class="btn btn-sm btn-soft-primary me-1"><i
                                                                class="bx bx-show fs-16"></i></a>
                                                        @endif @if (auth()->user()->hasPermission('delete contracts'))
                                                            <form id="delete-form-{{ $contract->id }}"
                                                                style="display: initial" method="POST"
                                                                action="{{ route('contracts.destroy', $contract->id) }}">
                                                                @csrf @method('DELETE') <a href="#!"
                                                                    onclick="confirmDelete({{ $contract->id }})"
                                                                    class="btn btn-sm btn-soft-danger"><i
                                                                        class="bx bx-trash fs-16"></i></a>
                                                            </form>
                                                        @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                @endif
            @endif
        </div>

    @endif
@endsection

@section('scripts')
    @vite(['resources/js/pages/dashboard.js'])

    {{-- SweetAlert (confirmDelete) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            }).then((r) => {
                if (r.isConfirmed) document.getElementById(`delete-form-${id}`).submit();
            });
        }
    </script>

    {{-- MarkerClusterer --}}
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

    {{-- Google Map (always loads, always inits) --}}
    <script>
        (function() {
            const ICONS = {
                building: 'data:image/svg+xml;utf8,' +
                    encodeURIComponent(
                        `<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><g fill="none"><path d="M24 46c10-12 16-18 16-26A16 16 0 1 0 8 20c0 8 6 14 16 26Z" fill="#2563eb"/><circle cx="24" cy="20" r="6.5" fill="#fff"/></g></svg>`
                        ),
                land: 'data:image/svg+xml;utf8,' +
                    encodeURIComponent(
                        `<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><g fill="none"><path d="M24 46c10-12 16-18 16-26A16 16 0 1 0 8 20c0 8 6 14 16 26Z" fill="#10b981"/><circle cx="24" cy="20" r="6.5" fill="#fff"/></g></svg>`
                        )
            };

            const esc = s => (s || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [m]));
            const infoHtml = (pin) => {
                const img = pin.img ?
                    `<div style="margin-bottom:8px"><img src="${pin.img}" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px"/></div>` :
                    '';
                const type = pin.type === 'building' ? `<span class="badge bg-primary">Building</span>` :
                    `<span class="badge bg-success">Land</span>`;
                const open = pin.url ?
                    `<a href="${pin.url}" class="btn btn-sm btn-primary" style="margin-top:6px">Open</a>` : '';
                return `<div style="max-width:280px">
        ${img}
        <div class="d-flex align-items-center justify-content-between mb-1">
          <h6 class="mb-0">${esc(pin.name||'')}</h6>${type}
        </div>
        <div class="text-muted" style="font-size:.9rem">${esc(pin.desc||'')}</div>
        ${open}
      </div>`;
            };

            let hoverTimer;

            window.initAssetsMap = function() {
                const el = document.getElementById('assets-map');
                if (!el) return;

                const map = new google.maps.Map(el, {
                    center: {
                        lat: 33.8938,
                        lng: 35.5018
                    }, // Beirut default
                    zoom: 8,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                });

                const pins = [
                    ...(window.__ASSET_PINS__?.buildings || []),
                    ...(window.__ASSET_PINS__?.lands || [])
                ];

                const info = new google.maps.InfoWindow();
                const bounds = new google.maps.LatLngBounds();
                const markers = [];

                pins.forEach(pin => {
                    if (typeof pin.lat !== 'number' || typeof pin.lng !== 'number') return;

                    const marker = new google.maps.Marker({
                        position: {
                            lat: pin.lat,
                            lng: pin.lng
                        },
                        map,
                        title: pin.name || '',
                        icon: {
                            url: ICONS[pin.type] || ICONS.building,
                            scaledSize: new google.maps.Size(36, 36),
                            anchor: new google.maps.Point(18, 36)
                        },
                    });

                    marker.addListener('click', () => {
                        info.setContent(infoHtml(pin));
                        info.open({
                            anchor: marker,
                            map
                        });
                    });
                    marker.addListener('mouseover', () => {
                        clearTimeout(hoverTimer);
                        info.setContent(infoHtml(pin));
                        info.open({
                            anchor: marker,
                            map
                        });
                    });
                    marker.addListener('mouseout', () => {
                        hoverTimer = setTimeout(() => info.close(), 1500);
                    });

                    markers.push(marker);
                    bounds.extend(marker.getPosition());
                });

                if (markers.length) {
                    map.fitBounds(bounds);
                    google.maps.event.addListenerOnce(map, 'idle', () => {
                        if (map.getZoom() > 16) map.setZoom(16);
                    });
                    new markerClusterer.MarkerClusterer({
                        map,
                        markers,
                        algorithmOptions: {
                            maxZoom: 16
                        }
                    });
                } else {
                    map.setZoom(8);
                }
            };

            // If google already loaded (unlikely), init immediately
            if (window.google && window.google.maps) window.initAssetsMap();
        })();

    </script>

    {{-- Load Google Maps JS once, with our callback --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initAssetsMap"
        async defer></script>
        <script>
    function toggleCustomDate() {
        var box = document.getElementById('custom-date-box');
        if (box.style.display === 'none') {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }
</script>
@endsection
