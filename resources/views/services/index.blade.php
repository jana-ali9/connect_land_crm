@extends('layouts.vertical', ['subTitle' => 'Service', 'title' => 'Service'])
@php($Name = 'services')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Service', 'subTitle' => 'show Service'])
    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                    <span>show Service</span>
                    @if (auth()->user()->hasPermission("create $Name"))
                        <a type="button" class="btn btn-success" href="{{ route("$Name.create") }}"><i
                                class='bx bx-user-plus'></i></a>
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

                <div class="table-responsive">
                    <table class="table table-striped table-borderless table-centered">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">description</th>
                                <th scope="col">default price</th>
                                <th scope="col">Type</th>
                                <th class="border-0 py-2" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allservices as $service)
                                <tr>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ Str::limit($service->description, 60) }}</td>
                                    <td>{{ $service->default_price }}</td>
                                    <td>{{ $service->type }}</td>
                                    <td>

                                        @if (auth()->user()->hasPermission("update $Name"))
                                            <a href="{{ route("$Name.edit", $service->id) }}"
                                                class="btn btn-sm btn-soft-secondary me-1"><i class="bx bx-edit fs-16"></i></a>
                                        @endif
                                    
                                        @if (auth()->user()->hasPermission("delete $Name"))
                                            <form id="delete-form-{{ $service->id }}" style="display: initial"
                                                method="POST" action="{{ route("$Name.destroy", $service->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <a href="#!" onclick="confirmDelete({{ $service->id }})" 
                                                    class="btn btn-sm btn-soft-danger"><i class="bx bx-trash fs-16"></i></a>
                                            </form>
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
                        @if ($allservices->onFirstPage())
                            <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $allservices->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        <!-- أرقام الصفحات -->
                        @for ($page = 1; $page <= $allservices->lastPage(); $page++)
                            @if ($page == $allservices->currentPage())
                                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $allservices->url($page) }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        <!-- زر التالي -->
                        @if ($allservices->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $allservices->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled"><a class="page-link">Next</a></li>
                        @endif
                    </ul>
                </nav>



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
