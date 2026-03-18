@extends('layouts.vertical', ['subTitle' => 'Buildings', 'title' => 'Building'])

@php
    $Name = 'buildings';
@endphp
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Building', 'subTitle' => 'Show Buildings'])

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


    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                    <span>Show Buildings</span>
                    @if (auth()->user()->hasPermission("create $Name"))
                        <a class="btn btn-success" href="{{ route("$Name.create") }}"><i class='bx bx-user-plus'></i></a>
                    @endif
                </h5>
            </div>

            @include('layouts.partials.massages')

            <div class="card-body">
                {{-- Search --}}
                <form class="app-search d-none d-md-block me-auto" method="GET" action="{{ route("$Name.index") }}">
                    <div class="input-group">
                        <input type="search" class="form-control" name="search" placeholder="search..." autocomplete="off"
                            value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">search</button>
                        @if (request('search'))
                            <a href="{{ route("$Name.index") }}" class="btn btn-secondary">Remove</a>
                        @endif
                    </div>
                </form>

                <br>

                {{-- Grid --}}
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 b-grid">
                    @foreach ($allBuildings as $building)
                        @php
                            $payed = (int) ($building->payed_units_count ?? 0);
                            $rented = (int) ($building->rented_units_count ?? 0);
                            $pct = $payed > 0 ? round(($rented / max($payed, 1)) * 100) : 0;

                            // Resolve image (url / storage / default)
                            $imgSrc = $building->image
                                ? (Str::startsWith($building->image, ['http://', 'https://'])
                                    ? $building->image
                                    : (Str::startsWith($building->image, 'storage/')
                                        ? asset($building->image)
                                        : asset('storage/' . $building->image)))
                                : asset('default.png');

                            // Location chip
                            $hasCoords =
                                isset($building->lat, $building->lng) &&
                                is_numeric($building->lat) &&
                                is_numeric($building->lng);
                            $label = trim($building->location ?: $building->address ?? 'View on map');
                            $country = strtoupper($building->country ?? '');
                            $mapsUrl = $hasCoords
                                ? 'https://www.google.com/maps?q=' . $building->lat . ',' . $building->lng
                                : 'https://www.google.com/maps/search/?api=1&query=' .
                                    urlencode($label . ($country ? " $country" : ''));
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
                        <a class="b-fab" href="{{ route("$Name.show", $building->id) }}" aria-label="View">
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

                {{-- Pagination --}}
                <nav aria-label="Page navigation example" class="mt-4">
                    <ul class="pagination justify-content-center">
                        @if ($allBuildings->onFirstPage())
                            <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        @else
                            <li class="page-item"><a class="page-link"
                                    href="{{ $allBuildings->previousPageUrl() }}">Previous</a></li>
                        @endif

                        @for ($page = 1; $page <= $allBuildings->lastPage(); $page++)
                            @if ($page == $allBuildings->currentPage())
                                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $allBuildings->url($page) }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        @if ($allBuildings->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $allBuildings->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled"><a class="page-link">Next</a></li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>

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
    @endif
@endsection
