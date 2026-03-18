@extends('layouts.vertical', ['subTitle' => 'Roles', 'title' => 'Roles'])

@php
    $Name = 'roles';
@endphp

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Roles', 'subTitle' => 'Create Roles'])

    <div class="card-body">
        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create Roles</h5>
                </div>
                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.store") }}">
                    @csrf
                    <div class="card-body">
                        <div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Role name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">All</th>
                                            <th class="text-center">Permissions</th>
                                            <th class="text-center">Read</th>
                                            <th class="text-center">Create</th>
                                            <th class="text-center">Update</th>
                                            <th class="text-center">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allpermission as $groupName => $permissions)
                                            @php
                                                $label = explode(' ', $groupName)[1] ?? $groupName;
                                                $permissions = collect($permissions);
                                                $read = $permissions->firstWhere('name', 'read ' . $label);
                                                $create = $permissions->firstWhere('name', 'create ' . $label);
                                                $update = $permissions->firstWhere('name', 'update ' . $label);
                                                $delete = $permissions->firstWhere('name', 'delete ' . $label);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input select-row" data-group="{{ $groupName }}">
                                                    </div>
                                                </td>

                                                <td class="text-capitalize fw-semibold">{{ $label }}</td>

                                                <td>
                                                    @if ($read)
                                                        <div class="form-check form-checkbox-success">
                                                            <input type="checkbox" class="form-check-input row-checkbox"
                                                                name="permissions[{{ $groupName }}][read][]"
                                                                value="{{ $read['id'] }}" id="perm-{{ $read['id'] }}"
                                                                data-group="{{ $groupName }}">
                                                            <label for="perm-{{ $read['id'] }}">Read</label>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($create)
                                                        <div class="form-check form-checkbox-primary">
                                                            <input type="checkbox" class="form-check-input row-checkbox"
                                                                name="permissions[{{ $groupName }}][create][]"
                                                                value="{{ $create['id'] }}" id="perm-{{ $create['id'] }}"
                                                                data-group="{{ $groupName }}">
                                                            <label for="perm-{{ $create['id'] }}">Create</label>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($update)
                                                        <div class="form-check form-checkbox-warning">
                                                            <input type="checkbox" class="form-check-input row-checkbox"
                                                                name="permissions[{{ $groupName }}][update][]"
                                                                value="{{ $update['id'] }}" id="perm-{{ $update['id'] }}"
                                                                data-group="{{ $groupName }}">
                                                            <label for="perm-{{ $update['id'] }}">Update</label>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($delete)
                                                        <div class="form-check form-checkbox-danger">
                                                            <input type="checkbox" class="form-check-input row-checkbox"
                                                                name="permissions[{{ $groupName }}][delete][]"
                                                                value="{{ $delete['id'] }}" id="perm-{{ $delete['id'] }}"
                                                                data-group="{{ $groupName }}">
                                                            <label for="perm-{{ $delete['id'] }}">Delete</label>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-12 text-end mt-3">
                                <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle all permissions in row when 'All' is clicked
            document.querySelectorAll('.select-row').forEach(function (selectAll) {
                selectAll.addEventListener('change', function () {
                    const group = this.dataset.group;
                    document.querySelectorAll(`.row-checkbox[data-group='${group}']`) 
                        .forEach(cb => cb.checked = this.checked);
                });
            });

            // Sync 'All' checkbox if every permission in the row is checked
            document.querySelectorAll('.row-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const group = this.dataset.group;
                    const allCheckbox = document.querySelector(`.select-row[data-group='${group}']`);
                    const groupCheckboxes = document.querySelectorAll(`.row-checkbox[data-group='${group}']`);
                    const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
                    allCheckbox.checked = allChecked;
                });
            });
        });
    </script>
@endsection
