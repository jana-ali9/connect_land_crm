@extends('layouts.vertical', ['subTitle' => 'invoice', 'title' => 'invoice'])
@php($Name = 'invoices')
@section('content')
    @include('layouts.partials.page-title', ['title' => 'invoice', 'subTitle' => 'Payment History'])

    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                    <span>Payment History</span>
                    <span class="badge bg-primary">{{ $allPayments->total() }} records</span>
                </h5>
            </div>

<div class="card-body">

    {{-- Filters --}}
    <div class="mb-4 border rounded-3 p-3 bg-light">
        <h6 class="mb-3">Filter Payments</h6>
        <form method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">Building</label>
                    <select name="building_id" class="form-select" id="buildingSelect">
                        <option value="">All Buildings</option>
                        @foreach ($buildings as $building)
                            <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                {{ $building->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">Land</label>
                    <select name="land_id" class="form-select">
                        <option value="">All Lands</option>
                        @foreach ($lands as $land)
                            <option value="{{ $land->id }}" {{ request('land_id') == $land->id ? 'selected' : '' }}>
                                {{ $land->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">Unit</label>
                    <select name="unit_id" class="form-select" id="unitSelect">
                        <option value="">All Units</option>
                        {{-- loaded via JS --}}
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">From</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">To</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-12 col-xl-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-50">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <a href="{{ route('invoices.history') }}" class="btn btn-outline-secondary w-50">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-responsive mt-3">
        <table class="table table-striped table-hover table-bordered align-middle text-center">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Invoice</th>
                    <th>Contract</th>
                    <th>Client</th>
                    <th>Property Type</th>
                    <th>Property Name</th>
                    <th>Amount Paid</th>
                    <th>Due After</th>
                    <th>Total</th>
                    <th>Paid At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($allPayments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if ($payment->invoice_id)
                                <a href="{{ route('invoice.view', $payment->invoice_id) }}" class="fw-semibold text-primary text-decoration-underline">
                                    #{{ $payment->invoice_id }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ url('/contracts/' . $payment->contract_id . '/show') }}" class="fw-semibold text-dark text-decoration-underline">
                                #{{ $payment->contract_id }}
                            </a>
                        </td>
                        <td>{{ $payment->client?->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info text-dark text-capitalize">
                                {{ $payment->contract?->property_type ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if ($payment->contract?->property_type == 'land')
                                {{ $payment->contract?->land?->name ?? 'N/A' }}
                            @elseif ($payment->contract?->property_type == 'building')
                                {{ $payment->contract?->unit?->name ?? $payment->contract?->building?->name ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="text-success fw-bold">{{ number_format($payment->amount_paid) }} $</td>
                        <td class="text-danger">{{ number_format($payment->due_after_payment) }} $</td>
                        <td>{{ number_format($payment->due_after_payment + $payment->amount_paid) }} $</td>
                        <td>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-muted">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Total --}}
    <div class="mt-3">
        <h5>Total Paid:
            <span class="text-success fw-bold">{{ number_format($totalPaid, 2) }} $</span>
        </h5>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $allPayments->withQueryString()->links() }}
    </div>
</div>


        </div>
    @endif
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buildingSelect = document.getElementById('buildingSelect');
            const unitSelect = document.getElementById('unitSelect');

            function loadUnits(buildingId) {
                unitSelect.innerHTML = '<option value="">All Units</option>';
                if (buildingId) {
                    fetch('/units/by-building/' + buildingId)
                        .then(response => response.json())
                        .then(units => {
                            units.forEach(unit => {
                                const option = document.createElement('option');
                                option.value = unit.id;
                                option.textContent = unit.name;
                                if (unit.id == "{{ request('unit_id') }}") {
                                    option.selected = true;
                                }
                                unitSelect.appendChild(option);
                            });
                        });
                }
            }

            buildingSelect.addEventListener('change', function() {
                loadUnits(this.value);
            });

            if (buildingSelect.value) {
                loadUnits(buildingSelect.value);
            }
        });
    </script>
@endsection
