@extends('layouts.vertical', ['subTitle' => 'Units', 'title' => 'unit'])
@php
$Name = 'units'
@endphp
@section('content')
    @include('layouts.partials/page-title', ['title' => 'unit', 'subTitle' => 'show Units'])

  <style>
  /* ----------------------------
     THEME TOKENS (Light)
     ---------------------------- */
  :root{
    --card-bg:#ffffff;
    --surface:#f8fafc;
    --img-bg:#f3f4f6;

    --ink:#111827;
    --ink-muted:#6b7280;
    --ink-subtle:#4b5563;

    --ring:#e5e7eb;

    --brand-1:#06b6d4;
    --brand-2:#3b82f6;
    --danger:#ef4444;

    --fab-bg:rgba(17,24,39,.75);
    --fab-ink:#ffffff;

    --chip-bg:#f8fafc;
    --chip-ink:#111827;
    --chip-border:#e5e7eb;

    --pill-bg:#f8fafc;
    --pill-border:#eef2f7;
    --pill-ink:#111827;

    --pill-success-bg:#ecfdf5;
    --pill-success-border:#d1fae5;
    --pill-success-ink:#065f46;

    --pill-warn-bg:#fff7ed;
    --pill-warn-border:#ffedd5;
    --pill-warn-ink:#9a3412;

    --shadow-1:0 6px 24px rgba(0,0,0,.06);
    --shadow-1h:0 14px 34px rgba(0,0,0,.12);
  }

  /* ----------------------------
     THEME TOKENS (Dark)
     Enable with: body.dark  OR  [data-bs-theme="dark"]
     ---------------------------- */
  body.dark, [data-bs-theme="dark"]{
    --card-bg:#0b1020;
    --surface:#0f172a;
    --img-bg:#0f172a;

    --ink:#e5e7eb;
    --ink-muted:#9aa3b2;
    --ink-subtle:#c7d0dc;

    --ring:#233156;

    --fab-bg:rgba(255,255,255,.18);
    --fab-ink:#ffffff;

    --chip-bg:#0f172a;
    --chip-ink:#e5e7eb;
    --chip-border:#233156;

    --pill-bg:#0f172a;
    --pill-border:#1e2a44;
    --pill-ink:#e5e7eb;

    --pill-success-bg:#0f2a22;
    --pill-success-border:#1a3a2f;
    --pill-success-ink:#7ee1b6;

    --pill-warn-bg:#2a1c0f;
    --pill-warn-border:#3a2a18;
    --pill-warn-ink:#f6c087;

    --shadow-1:0 6px 24px rgba(0,0,0,.35);
    --shadow-1h:0 14px 34px rgba(0,0,0,.55);
  }

  /* ----------------------------
     COMPONENTS
     ---------------------------- */
  .building-card{
    border:0;border-radius:16px;background:var(--card-bg);overflow:hidden;
    box-shadow:var(--shadow-1);
    transition:transform .2s ease, box-shadow .2s ease;
    height:100%;display:flex;flex-direction:column;color:var(--ink);
  }
  .building-card:hover{ transform:translateY(-3px); box-shadow:var(--shadow-1h); }

  .image-container{ position:relative; aspect-ratio:16/9; background:var(--img-bg); }
  .image-container img{ width:100%; height:100%; object-fit:cover; display:block; }

  .edit-btn{
    position:absolute; right:.5rem; bottom:.5rem; width:40px; height:40px; border-radius:999px;
    border:0; background:var(--fab-bg); color:var(--fab-ink);
    display:inline-flex; align-items:center; justify-content:center;
  }

  .card-body{ padding:14px 14px 10px; display:flex; flex-direction:column; gap:8px; }
  .unit-title, .ucb-title{ margin:0; font-weight:700; font-size:1.05rem; line-height:1.25; color:var(--ink); }
  .bld-sub, .ucb-sub{ margin:0; color:var(--ink-muted); font-weight:600; font-size:.9rem; }
  .description, .ucb-desc{ color:var(--ink-subtle); font-size:.925rem; }

  .chip-row{ display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.1rem; }
  .chip{
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.35rem .6rem; font-size:.8rem; font-weight:600;
    border-radius:999px; border:1px solid var(--chip-border);
    background:var(--chip-bg); color:var(--chip-ink);
    text-decoration:none; transition:background .2s ease, transform .2s ease, color .2s ease;
  }
  .chip i{ font-size:1rem; }
  .chip:hover{ background:var(--surface); transform:translateY(-1px); color:var(--chip-ink); }

  /* Unit card body block */
  .unit-card-body{display:flex;flex-direction:column;gap:.75rem;padding:14px 14px 12px}
  .ucb-head{display:flex;align-items:center;gap:.5rem;justify-content:space-between;flex-wrap:wrap}
  .ucb-meta{display:flex;gap:.5rem;flex-wrap:wrap}

  .ucb-stats{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.25rem}
  @media (min-width: 992px){ .ucb-stats{grid-template-columns:repeat(3,1fr)} }

  .pill{
    display:flex;align-items:center;gap:.4rem;justify-content:flex-start;padding:.35rem .6rem;
    border-radius:.6rem;background:var(--pill-bg);border:1px solid var(--pill-border);
    font-size:.82rem;font-weight:600;color:var(--pill-ink);
  }
  .pill i{font-size:1rem;opacity:.9}
  .pill.success{background:var(--pill-success-bg);border-color:var(--pill-success-border);color:var(--pill-success-ink)}
  .pill.warn{background:var(--pill-warn-bg);border-color:var(--pill-warn-border);color:var(--pill-warn-ink)}

  .ucb-actions, .card-actions{ display:flex; gap:.5rem; padding:10px 14px 14px; margin-top:auto; flex-wrap:wrap; }

  .btn1{ border-radius:.75rem; font-weight:700; }
  .btn1.blue{ background:linear-gradient(135deg,var(--brand-1),var(--brand-2)); color:#fff; border:0; }
  .btn1.red{ background:var(--danger); color:#fff; border:0; }

  /* Optional: SweetAlert2 dark support */
  body.dark .swal2-popup, [data-bs-theme="dark"] .swal2-popup{
    background:var(--card-bg) !important;
    color:var(--ink) !important;
    border:1px solid var(--ring);
    box-shadow:var(--shadow-1);
  }
  body.dark .swal2-title, [data-bs-theme="dark"] .swal2-title{ color:var(--ink) !important; }
  body.dark .swal2-styled.swal2-confirm, [data-bs-theme="dark"] .swal2-styled.swal2-confirm{
    background:linear-gradient(135deg,var(--brand-1),var(--brand-2)) !important;
  }
  body.dark .swal2-styled.swal2-cancel, [data-bs-theme="dark"] .swal2-styled.swal2-cancel{
    background:var(--surface) !important; color:var(--ink) !important; border:1px solid var(--ring);
  }
</style>


    @if (auth()->user()->hasPermission("read $Name"))
    <div class="card">
      <div class="card-header">
        <h5 class="card-title d-flex justify-content-between align-items-center">
          <span>show Units</span>
          @if (auth()->user()->hasPermission("create $Name"))
            <a type="button" class="btn btn-success" href="{{ route("$Name.create") }}"><i class='bx bx-user-plus'></i></a>
          @endif
        </h5>
      </div>

      @include('layouts.partials.massages')

      <div class="card-body">
        <form style="width :100%;" class="app-search d-none d-md-block me-auto" method="GET" action="{{ route("$Name.index") }}">
          <div class="input-group">
            <input type="search" class="form-control" name="search" placeholder="search..." autocomplete="off"
                   value="{{ request('search') }}" style="width :60%;">
            <select class="form-control" name="building_id">
              <option value="">All Buildings</option>
              @foreach ($buildings as $building)
                <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                  {{ $building->name }}
                </option>
              @endforeach
            </select>
            <button type="submit" class="btn btn-primary">search</button>
            @if (request('search'))
              <a href="{{ route("$Name.index") }}" class="btn btn-secondary">Remove</a>
            @endif
          </div>
        </form>

        <br>

        <div class="row">
          <div class="col-12">
            <div class="row row-cols-1 row-cols-md-3 g-3">
              @foreach ($allunits as $unit)
                @php
                  $b = $unit->building ?? null;
                  // Resolve unit image
                  $imgSrc = $unit->image
                    ? (Str::startsWith($unit->image, ['http://','https://'])
                        ? $unit->image
                        : (Str::startsWith($unit->image,'storage/') ? asset($unit->image) : asset('storage/'.$unit->image)))
                    : asset('default.png');

                  // Location chip (from building)
                  $hasCoords = $b && isset($b->lat,$b->lng) && is_numeric($b->lat) && is_numeric($b->lng);
                  $locLabel  = $b ? trim($b->location ?: ($b->address ?? 'View on map')) : null;
                  $country   = $b ? strtoupper($b->country ?? '') : '';
                  $mapsUrl   = $b
                    ? ($hasCoords
                        ? ('https://www.google.com/maps?q='.$b->lat.','.$b->lng)
                        : ('https://www.google.com/maps/search/?api=1&query='.urlencode(($locLabel ?: '').($country ? " $country" : ''))))
                    : null;
                @endphp

                <div class="col">
                  <div class="building-card">
                    <div class="image-container">
                      <img src="{{ $imgSrc }}" alt="unit image">
                      @if (auth()->user()->hasPermission("update $Name"))
                        <button class="edit-btn">
                          <a href="{{ route("$Name.edit", $unit->id) }}"><i class="bx bx-edit" style="color:#fff"></i></a>
                        </button>
                      @endif
                    </div>


<div class="unit-card-body">
  {{-- Header: title + quick meta --}}
  <div class="ucb-head">
    <h3 class="ucb-title">{{ $unit->name }}</h3>

    {{-- Inline meta chips (right side on wide, wrap on narrow) --}}
    <div class="ucb-meta">
      @if($b)
        <span class="chip" title="Building">
          <i class="bx bxs-buildings"></i>{{ $b->name }}
        </span>

        @if($mapsUrl && $locLabel)
          <a class="chip" href="{{ $mapsUrl }}" target="_blank" rel="noopener" title="Open in Google Maps">
            <i class="bx bx-map-pin"></i>{{ $locLabel }} @if($country)<span>• {{ $country }}</span>@endif
          </a>
        @endif
      @endif
    </div>
  </div>

  {{-- Description --}}
  @if(!empty($unit->description))
    <p class="ucb-desc">{{ Str::limit($unit->description, 120) }}</p>
  @endif

  {{-- Stat strip: area + rent status --}}
  <div class="ucb-stats">
    <div class="pill" title="Area">
      <i class="bx bx-grid-alt"></i> {{ $unit->area }} m²
    </div>

    @if (!$unit->is_payed)
      @if ($unit->is_rented && $unit->contract)
        <a class="pill success" href="{{ route('contracts.show', $unit->contract->id) }}" title="Rented until">
          <i class="bx bx-check-circle"></i> Rented · {{ \Carbon\Carbon::parse($unit->contract->end_date)->format('Y-m-d') }}
        </a>
      @else
        <a class="pill warn" href="{{ route('contracts.create') }}" title="Create contract">
          <i class="bx bx-time"></i> Not rented · Contract
        </a>
      @endif
    @endif

    {{-- Example: add more stats if needed (beds, baths, price...) --}}
    {{-- <div class="pill"><i class="bx bx-bed"></i> 3 Beds</div> --}}
  </div>

  {{-- Actions --}}
  <div class="ucb-actions">
    <button class="btn1 blue add-expense-btn" type="button" style="height:46px;margin-bottom:0"
            data-id="{{ $unit->id ?? $building->id }}"
            data-type="{{ isset($unit) ? 'unit' : 'building' }}"
            data-bs-toggle="modal" data-bs-target="#addExpenseModal">
      <i class="bx bx-plus"></i> Add Expense
    </button>

    @if (auth()->user()->hasPermission("delete $Name"))
      <form id="delete-form-{{ $unit->id }}" style="display:inline"
            method="POST" action="{{ route("$Name.destroy", $unit->id) }}">
        @csrf @method('DELETE')
        <button type="button" class="btn1 red" onclick="confirmDelete({{ $unit->id }})" style="height:46px;margin-bottom:0">
          <i class="bx bx-trash"></i> Remove
        </button>
      </form>
    @endif
  </div>
</div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <br>

        {{-- Pagination --}}
        <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center">
            @if ($allunits->onFirstPage())
              <li class="page-item disabled"><a class="page-link">Previous</a></li>
            @else
              <li class="page-item"><a class="page-link" href="{{ $allunits->previousPageUrl() }}">Previous</a></li>
            @endif

            @for ($page = 1; $page <= $allunits->lastPage(); $page++)
              @if ($page == $allunits->currentPage())
                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
              @else
                <li class="page-item"><a class="page-link" href="{{ $allunits->url($page) }}">{{ $page }}</a></li>
              @endif
            @endfor

            @if ($allunits->hasMorePages())
              <li class="page-item"><a class="page-link" href="{{ $allunits->nextPageUrl() }}">Next</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".show-image-btn").forEach(button => {
      button.addEventListener("click", function() {
        let imageUrl = this.getAttribute("data-image");
        Swal.fire({
          imageUrl: imageUrl,
          imageWidth: '100%',
          imageHeight: 'auto',
          showConfirmButton: false,
          backdrop: true,
          customClass: { popup: 'full-image-alert' }
        });
      });
    });
  });
</script>
