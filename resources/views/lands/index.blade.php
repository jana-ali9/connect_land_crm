@extends('layouts.vertical', ['subTitle' => 'Lands', 'title' => 'Admin'])

@php($Name = 'lands')
@section('content')
    @include('layouts.partials.page-title', ['title' => 'Land', 'subTitle' => 'Show Lands'])
    <style>
  .land-card {
    border: 0;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
    transition: transform .25s ease, box-shadow .25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  .land-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,.12);
  }
  .land-card .image-wrap {
    position: relative;
    aspect-ratio: 16/9;
    background: #f3f4f6;
  }
  .land-card .image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .land-card .image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,.0) 30%, rgba(0,0,0,.45) 100%);
    opacity: 0;
    transition: opacity .25s ease;
    display: flex; align-items: end; justify-content: end;
    padding: .5rem;
  }
  .land-card:hover .image-overlay { opacity: 1; }

  .quick-badges {
    position: absolute;
    left: .75rem; bottom: .75rem;
    display: flex; gap: .5rem; flex-wrap: wrap;
  }
  .chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .25rem .55rem; font-size: .75rem; font-weight: 600;
    border-radius: 999px; color: #fff; backdrop-filter: blur(6px);
    background: rgba(17,24,39,.55);
  }
  .edit-fab {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 999px;
    background: rgba(17,24,39,.7); color:#fff; border:0;
  }
  .land-card .card-body { padding: 1rem 1rem 0; }
  .land-title { margin: 0; font-weight: 700; line-height: 1.25; }
  .land-meta { color: #6b7280; font-size: .9rem; }
  .land-desc { color: #4b5563; }

  .action-bar {
    margin-top: auto;
    padding: .75rem 1rem 1rem;
    display: flex; gap: .5rem; flex-wrap: wrap;
  }
  .btn-soft {
    border-radius: .75rem; border: 1px solid #e5e7eb;
    background: #f8fafc; color: #111827; font-weight: 600;
  }
  .btn-soft:hover { background:#eef2f7; }
  .btn-gradient {
    border:0; border-radius:.75rem; color:#fff; font-weight:700;
    background: linear-gradient(135deg, #06b6d4, #3b82f6);
  }
  .btn-gradient:hover { filter: brightness(0.95); }
</style>

    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                    <span>Show Lands</span>
                    @if (auth()->user()->hasPermission("create $Name"))
                        <a type="button" class="btn btn-success" href="{{ route("$Name.create") }}"><i
                                class='bx bx-plus'></i></a>
                    @endif
                </h5>
            </div>

            @include('layouts.partials.massages')
            <div class="card-body">
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

               <div class="row">
  <div class="col-12">
    <div class="row g-3">
      @foreach ($allLands as $land)
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
</div>


                {{-- pagination --}}
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        @if ($allLands->onFirstPage())
                            <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $allLands->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        @for ($page = 1; $page <= $allLands->lastPage(); $page++)
                            @if ($page == $allLands->currentPage())
                                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $allLands->url($page) }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        @if ($allLands->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $allLands->nextPageUrl() }}">Next</a>
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
                    title: 'Are you sure?',
                    text: "This land will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${id}`).submit();
                    }
                });
            }
        </script>
    @endif
@endsection
