@extends('layouts.vertical', ['subTitle' => 'Unit Expenses', 'title' => 'Unit Expenses'])

@php($Name = 'unitExpenses')

@section('content')
    @include('layouts.partials.page-title', [
        'title' => 'Unit Expenses',
        'subTitle' => 'Show Unit Expenses',
    ])

    <div class="card">
        <div class="card-header">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="border:none; padding-left : 0 ;padding-right:0 ;">
                <h5 class="card-title mb-0">Show Unit Expenses</h5>
                <a href="{{ route('expenseOffers.create') }}" class="btn btn-success d-flex align-items-center gap-1">
                    <i class="bx bx-plus-circle"></i>
                    <span>Create Expense</span>
                </a>
            </div>


            <form method="GET" class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Building</label>
                    <select class="form-select" name="building_id">
                        <option value="">All Buildings</option>
                        @foreach ($buildings as $building)
                            <option value="{{ $building->id }}"
                                {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                {{ $building->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Unit</label>
                    <select class="form-select" name="unit_id" id="unit_id">
                        <option value="">All Units</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Land</label>
                    <select class="form-select" name="land_id">
                        <option value="">All Lands</option>
                        @foreach ($lands as $land)
                            <option value="{{ $land->id }}" {{ request('land_id') == $land->id ? 'selected' : '' }}>
                                {{ $land->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Min Amount</label>
                    <input type="number" class="form-control" name="min_amount" value="{{ request('min_amount') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Max Amount</label>
                    <input type="number" class="form-control" name="max_amount" value="{{ request('max_amount') }}">
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-info w-100" data-bs-toggle="modal"
                        data-bs-target="#CategoriesModal">
                        Add Categories
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body">

            <!-- offers section -->
            <div id="offers-section" class="d-none mt-4">
                <h5>Expense Offers:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Offer Amount</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="offers-list">
                            <!-- dynamically loaded -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- expenses table -->
            <div class="table-responsive mt-4">
                <table class="table table-striped table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Expense Name</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Property Type</th>
                            <th>Property Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allUnits as $unit)
                            <tr>
                                <td>{{ $unit->id }}</td>
                                <td>{{ $unit->expense_name }}</td>
                                <td id="unit-amount-{{ $unit->id }}">{{ $unit->amount }}</td>
                                <td>{{ $unit->expenseCategory->category_name ?? '' }}</td>
                                <td>{{ ucfirst($unit->allocation_type) }}</td>
                                <td>
                                    @if ($unit->allocation_type == 'building')
                                        {{ json_decode($unit->building)->name ?? '' }}
                                    @elseif($unit->allocation_type == 'unit')
                                        {{ json_decode($unit->unit)->name ?? '' }}
                                    @elseif($unit->allocation_type == 'land')
                                        {{ $unit->land?->name ?? '' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="d-flex flex-wrap gap-1">
                                    {{-- <button class="btn btn-sm btn-info" onclick="loadOffers({{ $unit->id }})">Show
                                        Offers</button>
                                    <button class="btn btn-sm btn-success"
                                        onclick="showAddOfferModal({{ $unit->id }})">Add Offer</button>
                                    <form id="delete-form-{{ $unit->id }}" method="POST" class="d-inline"
                                        action="{{ route('unit-expenses.destroy', $unit->id) }}"> --}}

                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete({{ $unit->id }})"
                                        class="btn btn-sm btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                    </form>


                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $allUnits->links() }}
            </div>
        </div>
    </div>

    <!-- Categories Modal -->
    <div class="modal fade" id="CategoriesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addCategoryForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Categories</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-9">
                                <input type="text" name="category_name" class="form-control"
                                    placeholder="Add new category" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <button class="btn btn-primary w-100">Add</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="categoriesTableBody">
                                    @foreach ($categories as $category)
                                        <tr id="category-row-{{ $category->id }}">
                                            <td>{{ $category->category_name }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteCategory({{ $category->id }})">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Offer Modal -->
    <div class="modal fade" id="addOfferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addOfferForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Offer</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="expense_id" id="unitId">
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" name="company_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Offer Amount</label>
                            <input type="number" class="form-control" name="offer_amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Add Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedUnitId = null;

        function loadOffers(unitId) {
            selectedUnitId = unitId;
            document.getElementById('offers-list').innerHTML = '';

            fetch(`/unit-expenses/${unitId}/offers`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(offer => {
                            html += `
                <tr>
                    <td>${offer.company_name}</td>
                    <td>${offer.offer_amount}</td>
                    <td>
                        ${offer.status ? 'Accepted' : 'Rejected'}
                        <input type="checkbox" class="form-check-input ms-2"
                            onchange="updateOfferStatus(${offer.id}, this.checked)"
                            ${offer.status ? 'checked' : ''}>
                    </td>
                    <td>${offer.description || 'N/A'}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteOffer(${offer.id})">Delete</button>
                    </td>
                </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="5" class="text-center">No offers found</td></tr>`;
                    }
                    document.getElementById('offers-list').innerHTML = html;
                    document.getElementById('offers-section').classList.remove('d-none');
                    updateUnitExpenseAmount();
                });
        }





        function updateUnitExpenseAmount() {
            fetch(`/unit-expenses/${selectedUnitId}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelector(`#unit-amount-${data.id}`).innerText = data.amount;
                });
        }

        function updateOfferStatus(offerId, isChecked) {
            fetch(`/expenseOffers/${offerId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: isChecked ? 1 : 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(() => {
                            loadOffers(selectedUnitId);
                        }, 200);
                        window.location.reload();
                    } else {
                        alert('Failed to update status');
                    }
                });
        }

        function deleteOffer(offerId) {
            if (confirm('Are you sure you want to delete this offer?')) {
                fetch(`/expenseOffers/${offerId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            setTimeout(() => {
                                loadOffers(selectedUnitId);
                            }, 200);
                        } else {
                            alert('Failed to delete offer');
                        }
                    });
            }
        }

        function showAddOfferModal(unitId) {
            selectedUnitId = unitId;
            document.getElementById('unitId').value = unitId;
            new bootstrap.Modal(document.getElementById('addOfferModal')).show();
        }

        document.getElementById('addOfferForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(`{{ route('expenseOffers.store') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadOffers(selectedUnitId);
                        bootstrap.Modal.getInstance(document.getElementById('addOfferModal')).hide();
                    } else {
                        alert('Failed to add offer');
                    }
                });
        });

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


        document.querySelector('[name="building_id"]').addEventListener('change', function() {
            const buildingId = this.value;
            const unitSelect = document.getElementById('unit_id');
            unitSelect.innerHTML = `<option value="">All Units</option>`;
            if (buildingId) {
                fetch(`/buildings/${buildingId}/units`)
                    .then(res => res.json())
                    .then(units => {
                        units.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = unit.name;
                            if (unit.id == "{{ request('unit_id') }}") option.selected = true;
                            unitSelect.appendChild(option);
                        });
                    });
            }
        });

        window.addEventListener('load', () => {
            const buildingSelected = document.querySelector('[name="building_id"]').value;
            if (buildingSelected) {
                document.querySelector('[name="building_id"]').dispatchEvent(new Event('change'));
            }
        });

        document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(`{{ route('expense-categories.store') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to add category');
                    }
                });
        });

        function deleteCategory(id) {
            if (!confirm('Are you sure?')) return;
            fetch(`{{ url('/expense-categories') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete category');
                    }
                });
        }
    </script>
@endsection
