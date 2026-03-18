@extends('layouts.vertical', ['title' => 'Dashboard']) @section('content') @if (auth()->user()->hasPermission('read dashboard'))
    <div class="d-flex justify-content-start mb-4 gap-4">
        <form method="GET" action="{{ url()->current() }}">
            <div class="btn-group" role="group" aria-label="Time Filter"> <button type="submit" name="filter"
                    value="all"
                    class=" me-2 btn btn-outline-primary {{ request('filter', 'all') === 'all' ? 'active' : '' }}"> All
                    Time </button> <button type="submit" name="filter" value="year"
                    class=" me-2 btn btn-outline-primary {{ request('filter') === 'year' ? 'active' : '' }}"> This Year
                </button> <button type="submit" name="filter" value="month"
                    class="me-2 btn btn-outline-primary {{ request('filter') === 'month' ? 'active' : '' }}"> This Month
                </button> <button type="submit" name="filter" value="week"
                    class="me-2 btn btn-outline-primary {{ request('filter') === 'week' ? 'active' : '' }}"> This Week
                </button> </div>
        </form>
    </div>
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card1">
                <div class="card-content">
                    <div class="text-section">
                        <p class="label">Building</p>
                        <p class="number">{{ count($allbuildings) }}</p>
                        @if (auth()->user()->hasPermission('create buildings'))
                            <a class="create-btn d-inline-flex align-items-center gap-1"
                                href="{{ route('buildings.create') }}"> <span>+ Create Building </span> </a>
                        @endif
                    </div>
                    <div class="icon-section"> <img src="/images/Icon.png"alt="Building Icon" width="60px"
                            height="60px" /> </div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-md-6 col-xl-3">
            <div class="card1">
                <div class="card-content">
                    <div class="text-section">
                        <p class="label">Active Contracts</p>
                        <p class="number">{{ $activeContractsCount }}</p>
                        @if (auth()->user()->hasPermission('read contracts'))
                            <a class="create-btn d-inline-flex align-items-center gap-1"
                                href="{{ route('contracts.index') }}"> <i class="bx bx-show fs-16"></i> <span>show
                                    Contract</span> </a>
                        @endif
                    </div>
                    <div class="icon-section"> <img src="/images/Icon2.png"alt="Building Icon" width="60px"
                            height="60px" /> </div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-md-6 col-xl-3">
            <div class="card1">
                <div class="card-content">
                    <div class="text-section">
                        <p class="label">Lands</p>
                        <p class="number">{{ isset($alllands) ? count($alllands) : 0 }}</p>
                        @if (auth()->user()->hasPermission('create lands'))
                            <a class="create-btn d-inline-flex align-items-center gap-1"
                                href="{{ route('lands.create') }}"> <span>+ Create Land</span> </a>
                        @endif
                    </div>
                    <div class="icon-section"> <img src="/images/land.png" alt="Land Icon" width="60px"
                            height="60px" /> </div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-md-6 col-xl-3">
            <div class="card1">
                <div class="card-content">
                    <div class="text-section">
                        <p class="label">Amount collected</p>
                        <p class="number">${{ number_format($currentAmountPaid) }} </p>
                        @if (auth()->user()->hasPermission('read invoices'))
                            <a class="create-btn d-inline-flex align-items-center gap-1"
                                href="{{ route('invoices.history') }}"> <i class="bx bx-time fs-16"></i> <span>Amount
                                    history</span> </a>
                        @endif
                    </div>
                    <div class="icon-section"> <img src="/images/Icon3.png"alt="Building Icon" width="60px"
                            height="60px" /> </div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-md-6 col-xl-3">
            <div class="card1">
                <div class="card-content">
                    <div class="text-section">
                        <p class="label">Amount Due</p>
                        <p class="number">${{ number_format($currentAmountDue) }} </p>
                        @if (auth()->user()->hasPermission('read invoices'))
                            <a class="create-btn d-inline-flex align-items-center gap-1"
                                href="{{ route('invoices.history') }}"> <i class="bx bx-time fs-16"></i> <span>Amount
                                    history</span> </a>
                        @endif
                    </div>
                    <div class="icon-section"> <img src="/images/Icon4.png"alt="Building Icon" width="60px"
                            height="60px" /> </div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-md-6 col-xl-3">
            <div class="card1">
                <div class="card-content">
                    <div class="text-section">
                        <p class="label">Total Expenses</p>
                        <p class="number">${{ number_format($currentExpenses) }}</p>
                        @if (auth()->user()->hasPermission('read expenseOffers'))
                            <a class="create-btn d-inline-flex align-items-center gap-1"
                                href="{{ route('expenseOffers.index') }}"> <i class="bx bx-list-ul fs-16"></i> <span
                                    style="display: block;">Show Expense</span> </a>
                        @endif
                    </div>
                    <div class="icon-section"> <img src="/images/expense-icon.png" alt="Expense Icon" width="60px"
                            height="60px" /> </div>
                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->
    <div class="row">
        <div class="col-12"> {{-- Lands Section --}} <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                @foreach ($alllands as $land)
                    <div class="col">
                        <div class="building-card h-100">
                            <div class="image-container position-relative"> <img
                                    src="{{ $land->photo ? asset('storage/' . $land->photo) : asset('default.png') }}"
                                    alt="land" class="img-fluid rounded w-100" />
                                @if (auth()->user()->hasPermission('update lands'))
                                    <button class="edit-btn position-absolute top-0 end-0 m-2"> <a
                                            href="{{ route('lands.edit', $land->id) }}"> <i
                                                class="bx bx-edit text-white"></i> </a> </button>
                                @endif
                            </div>
                            <div class="card-body">
                                <h5 class="mb-2">{{ $land->name }}</h5>
                                @if (!$land->is_payed)
                                    <span class="badge bg-danger mb-2">Unpaid</span>
                                @endif
                                <p class="description text-muted">{{ Str::limit($land->description, 120) }}
                                </p>
                                <div class="card-actions d-grid gap-2 mt-3"> <button class="btn1 blue add-expense-btn"
                                        type="button" data-id="{{ $land->id }}" data-type="land"
                                        data-bs-toggle="modal" data-bs-target="#addExpenseModal"> <i
                                            class="bx bx-plus"></i> Add Expense
                                    </button>
                                    @if (auth()->user()->hasPermission('read lands'))
                                        <a href="{{ route('lands.show', $land->id) }}" class="btn1 teal"> <i
                                                class="bx bx-show"></i> Show </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div> {{-- Buildings Section --}} <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mt-4 mb-4">
                @foreach ($allbuildings as $building)
                    <div class="col">
                        <div class="building-card h-100">
                            <div class="image-container position-relative"> <img src="{{ $building->image }}"
                                    alt="building" class="img-fluid rounded w-100" />
                                @if (auth()->user()->hasPermission('update buildings'))
                                    <button class="edit-btn position-absolute top-0 end-0 m-2"> <a
                                            href="{{ route('buildings.edit', $building->id) }}"> <i
                                                class="bx bx-edit text-white"></i> </a> </button>
                                @endif
                            </div>
                            <div class="card-body">
                                <h5 class="mb-2">{{ $building->name }}</h5>
                                @if (!$building->is_payed)
                                    <span class="badge bg-success mb-2">
                                        {{ $building->payed_units_count > 0 ? ($building->rented_units_count / $building->payed_units_count) * 100 : 0 }}%
                                    </span> {{ $building->rented_units_count }} / {{ $building->payed_units_count }}
                                @endif
                                <p class="description text-muted">
                                    {{ Str::limit($building->description, 120) }}</p>
                                <div class="card-actions d-grid gap-2 mt-3"> <button class="btn1 blue add-expense-btn"
                                        type="button" data-id="{{ $unit->id ?? $building->id }}"
                                        data-type="{{ isset($unit) ? 'unit' : 'building' }}" data-bs-toggle="modal"
                                        data-bs-target="#addExpenseModal"> <i class="bx bx-plus"></i> Add Expense
                                    </button>
                                    @if (auth()->user()->hasPermission('read units'))
                                        <a class="btn1 teal"
                                            href="{{ route('units.index', ['building_id' => $building->id]) }}">
                                            <i class="bx bx-show"></i> Show Unit </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div> <br>
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
                                                        href="{{ route('invoice.view', $invoice->id) }}"
                                                        target="_blank" class="btn btn-sm btn-soft-secondary me-1"><i
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
@endif @endsection @section('script')
@vite(['resources/js/pages/dashboard.js'])
@endsection
