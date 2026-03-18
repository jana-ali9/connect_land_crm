@extends('layouts.vertical', ['subTitle' => 'Add Admin' , 'title'=>'Admin'])

@php($Name = 'users')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Admin', 'subTitle' => 'Add Admin'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Admin</h5>
            </div>

            @include('layouts.partials.massages')
            <form method="POST" action="{{ route('admins.store') }}">

                @csrf
                <div class="card-body">
                    <div>

                        <div class="mb-3">
                            <label for="name" class="form-label">name</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                value="{{ old('name') }}">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control"required
                                value="{{ old('email') }}" placeholder="Email">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="example-select" class="form-label">Roles type</label>
                            <select class="form-select" id="example-select" name="role_id">
                                <option disabled selected>select one of roles</option>

                                @foreach ($allroles as $role)
                                    <option @selected($role->id == old('role_id')) value="{{ $role->id }}">{{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12" style="text-align: end">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
        @endif
    </div>
@endsection
