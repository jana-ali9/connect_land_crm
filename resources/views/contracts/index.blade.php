@extends('layouts.vertical', ['subTitle' => 'contract', 'title' => 'contract'])
@php($Name = 'contracts')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'contract', 'subTitle' => 'show contract'])
    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                    <span>show contract</span>
                    @if (auth()->user()->hasPermission("create $Name"))
                        <a type="button" class="btn btn-success" href="{{ route("$Name.create") }}"><i
                                class='bx bx-user-plus'></i></a>
                    @endif
                </h5>

            </div>

            @include('layouts.partials.massages')
            <div class="card-body">

                <form class="app-search d-none d-md-block me-auto" method="GET" action="{{ route("$Name.index") }}">
                    <div class="input-group mb-2">
                        <input type="search" class="form-control" name="search" placeholder="search..." autocomplete="off"
                            value="{{ request('search') }}">
                        <select name="client_id" class="form-control">
                            <option value="">client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group mb-2">
                        <select name="status" class="form-control">
                            <option value="">status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>expired</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>suspended
                            </option>
                        </select>
                        <select name="type" class="form-control">
                            <option value="">type</option>
                            <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>sale</option>
                            <option value="rent" {{ request('type') == 'rent' ? 'selected' : '' }}>rent</option>
                        </select>
                    </div>


                    <div class="input-group mb-2">
                        <input type="number" name="min_price" class="form-control" placeholder="min price"
                            value="{{ request('min_price') }}">
                        <input type="number" name="max_price" class="form-control" placeholder="max price"
                            value="{{ request('max_price') }}">
                        <button type="submit" class="btn btn-primary">search</button>
                        @if (request()->hasAny(['client_id', 'status', 'type', 'min_price', 'max_price', 'search']))
                            <a href="{{ route("$Name.index") }}" class="btn btn-secondary">Remove Filters</a>
                        @endif
                    </div>



                </form>

                <br>

                <div class="table-responsive">
                    <table class="table table-striped table-borderless table-centered">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">property name</th>
                                <th scope="col">property type</th>
                                <th scope="col">client name</th>
                                <th scope="col">type</th>
                                <th scope="col">billing</th>
                                <th scope="col">status</th>
                                <th scope="col">end date</th>
                                <th class="border-0 py-2" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allcontracts as $contract)
                                <tr>
                                    <td>
                                        @if ($contract->unit_id && $contract->unit)
                                            {{ $contract->unit->name }}
                                        @elseif ($contract->building_id && $contract->building)
                                            {{ $contract->building->name }}
                                        @elseif ($contract->land_id && $contract->land)
                                            {{ $contract->land->name }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if ($contract->unit_id)
                                            unit
                                        @elseif ($contract->building_id)
                                            building
                                        @elseif ($contract->land_id)
                                            land
                                        @else
                                            N/A
                                        @endif
                                    </td>


                                    <td>{{ $contract->client->name }}</td>
                                    <td>{{ $contract->contract_type }}</td>
                                    <td>{{ $contract->base_rent }}</td>
                                    <td>{{ $contract->contract_status }}</td>
                                    <td>{{ $contract->end_date }}</td>
                                    <td>

                                        @if (auth()->user()->hasPermission("read $Name"))
                                            <a href="{{ route("$Name.show", $contract->id) }}"
                                                class="btn btn-sm btn-soft-primary me-1"><i
                                                    class="bx bx-show fs-16"></i></a>
                                        @endif

                                        @if (auth()->user()->hasPermission("update $Name"))
                                            <a href="{{ route("$Name.edit", $contract->id) }}"
                                                class="btn btn-sm btn-soft-secondary me-1"><i
                                                    class="bx bx-edit fs-16"></i></a>
                                        @endif
                                        @if (auth()->user()->hasPermission("delete $Name"))
                                            @if ($contract->contract_type == 'sale')
                                                <form id="delete-form-{{ $contract->id }}" style="display: initial"
                                                    method="POST" action="{{ route("$Name.destroy", $contract->id) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <a href="#!" onclick="confirmDelete({{ $contract->id }})"
                                                        class="btn btn-sm btn-soft-danger"><i
                                                            class="bx bx-trash fs-16"></i></a>
                                                </form>
                                            @else
                                                @if ($contract->contract_status != 'suspended')
                                                    <form id="delete-form-{{ $contract->id }}" style="display: initial"
                                                        method="POST"
                                                        action="{{ route("$Name.cancellation", $contract->id) }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <a href="#!" onclick="confirmSuspended({{ $contract->id }})"
                                                            class="btn btn-sm btn-soft-danger"><i
                                                                class="bx bx-trash fs-16"></i></a>
                                                    </form>
                                                @endif

                                                @if ($contract->contract_status == 'suspended')
                                                    <form id="delete-form-{{ $contract->id }}" style="display: initial"
                                                        method="POST"
                                                        action="{{ route("$Name.destroy", $contract->id) }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <a href="#!" onclick="confirmDelete({{ $contract->id }})"
                                                            class="btn btn-sm btn-soft-danger"><i
                                                                class="bx bx-trash fs-16"></i></a>
                                                    </form>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <!-- زر السابق -->
                        @if ($allcontracts->onFirstPage())
                            <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $allcontracts->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        <!-- أرقام الصفحات -->
                        @for ($page = 1; $page <= $allcontracts->lastPage(); $page++)
                            @if ($page == $allcontracts->currentPage())
                                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $allcontracts->url($page) }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        <!-- زر التالي -->
                        @if ($allcontracts->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $allcontracts->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled"><a class="page-link">Next</a></li>
                        @endif
                    </ul>
                </nav>



            </div>

            <script>
                function confirmSuspended(id) {
                    Swal.fire({
                        title: 'هل أنت متأكد من ايقاف العقد؟',
                        text: "لن تتمكن من الرجوع في هذا القرار!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، الغاء!',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${id}`).submit();
                        }
                    });
                }

                function confirmDelete(id) {
                    Swal.fire({
                        title: 'هل أنت متأكد من حذف العقد؟',
                        text: "لن تتمكن من الرجوع في هذا القرار!",
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
