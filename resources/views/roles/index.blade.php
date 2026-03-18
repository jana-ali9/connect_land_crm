@extends('layouts.vertical', ['subTitle' => 'Admin', 'title'=>'Roles'])

@php($Name = 'roles')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Roles', 'subTitle' => 'show Roles'])


    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                    <span>show roles</span>
                    <a type="button" class="btn btn-success" href="{{ route("$Name.create") }}"><i
                            class='bx bx-user-plus'></i></a>
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
                                <th scope="col">id</th>
                                <th scope="col">Name</th>
                                <th scope="col">description</th>
                                <th scope="col">Action</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allroles as $role)
                                <tr>
                                    <td> {{ $role->id }}</td>
                                    <td> {{ $role->name }}</td>
                                    <td> {{ Str::limit($role->description, 60) }}</td>
                                    <td>

                                        @if (auth()->user()->hasPermission("update $Name") && $role->name != "Super Admin")
                                            <a href="{{ route("$Name.edit", $role->id) }}"
                                                class="btn btn-primary btn-sm w-100">Edit</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->hasPermission("delete $Name")&& $role->name != "Super Admin")
                                        <form id="delete-form-{{ $role->id }}" style="display: initial" method="POST"
                                            action="{{ route("$Name.destroy", $role->id) }}">
                                            @csrf
                                            @method('DELETE')

                                            <a href="#" class="btn btn-danger btn-sm w-100"
                                                onclick="confirmDelete({{ $role->id }})">Remove</a>
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
                        @if ($allroles->onFirstPage())
                            <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $allroles->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        <!-- أرقام الصفحات -->
                        @for ($page = 1; $page <= $allroles->lastPage(); $page++)
                            @if ($page == $allroles->currentPage())
                                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $allroles->url($page) }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        <!-- زر التالي -->
                        @if ($allroles->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $allroles->nextPageUrl() }}">Next</a>
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
