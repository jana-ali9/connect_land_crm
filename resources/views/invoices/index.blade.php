@extends('layouts.vertical', ['subTitle' => 'invoice', 'title' => 'invoice'])
@php($Name = 'invoices')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'invoice', 'subTitle' => 'show invoices'])
    @if (auth()->user()->hasPermission("read $Name"))
        <div class="card">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                    <span>show invoices</span>
                    {{-- @if (auth()->user()->hasPermission("create $Name"))
                        <a type="button" class="btn btn-success" href="{{ route("$Name.create") }}"><i
                                class='bx bx-user-plus'></i></a>
                    @endif --}}
                </h5>

            </div>

            @include('layouts.partials.massages')
            <div class="card-body">
                <form class="app-search d-none d-md-block me-auto" method="GET" action="{{ route("$Name.index") }}">
                    <div class="input-group mb-2">
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
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>pending</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>overdue</option>
                        </select> 
                        <select name="type" class="form-control">
                            <option value="">type</option>
                            <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>service</option>
                            <option value="rent" {{ request('type') == 'rent' ? 'selected' : '' }}>rent</option>
                            <option value="sale" {{ request('sale') == 'overdue' ? 'sale' : '' }}>sale</option>
                        </select>
                    </div>

                    <div class="input-group mb-2">
                        <input type="number" name="min_price" class="form-control" placeholder="min price"
                            value="{{ request('min_price') }}">
                        <input type="number" name="max_price" class="form-control" placeholder="max price"
                            value="{{ request('max_price') }}">
                    </div>

                    <div class="input-group mb-2">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">search</button>
                    @if (request()->hasAny(['client_id', 'status', 'type','min_price', 'max_price', 'start_date', 'end_date']))
                        <a href="{{ route("$Name.index") }}" class="btn btn-secondary">Remove Filters</a>
                    @endif
                </form>

                <br>

                <div class="table-responsive">
                    <table class="table table-striped table-borderless table-centered">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">User Name</th>
                                <th scope="col">cole date</th>
                                <th scope="col">Amount due</th>
                                <th scope="col">Amount paid</th>
                                <th scope="col">type</th>
                                <th scope="col">status</th>
                                <th class="border-0 py-2" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                         @foreach ($allinvoices as $invoice)
        <tr>
            <td>{{ $invoice->client->name }}</td>
            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>

            <td>{{ ($invoice->type == "service") ? $invoice->services_cost : $invoice->amount_due }}</td>
            <td>{{ $invoice->amount_paid }}</td>

            {{-- حل مشكلة نوع العقد --}}
            <td>
                @if($invoice->type == "service")
                    {{ $invoice->type }}
                @else
                    {{ optional($invoice->contract)->contract_type ?? 'N/A (عقد محذوف)' }}
                @endif
            </td>

            {{-- حل مشكلة الحالة (Status) --}}
            <td>
                <span class='badge status-badge badge-soft-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'pending' ? 'secondary' : 'danger') }}'>
                    {{ $invoice->status }}
                </span>
            </td>

            {{-- قسم العمليات (Actions) --}}
            <td>
                @if ($invoice->type == 'service')
                    @if($invoice->contract_id)
                        <a href="{{ route('contracts.show', $invoice->contract_id) }}"
                           class="btn btn-sm btn-soft-primary me-1">
                            <i class="bx bx-cog fs-16"></i>
                        </a>
                    @endif
                @else
                    @if ($invoice->status != 'paid')
                        @if (auth()->user()->hasPermission('update invoices'))
                            <button class="btn btn-sm btn-soft-primary me-1 update-status"
                                    data-id="{{ $invoice->id }}"
                                    data-status="{{ $invoice->status }}">
                                <i class="bx bx-cog fs-16"></i>
                            </button>
                        @endif
                    @endif
                @endif
                                        <a href="{{ route('invoice.view', $invoice->id) }}" target="_blank"
                                            class="btn btn-sm btn-soft-secondary me-1"><i class="bx bx-show fs-16"></i></a>

                                        <a href="https://wa.me/+961{{ $invoice->client->phone }}?text={{ route('invoice.view', $invoice->id) }}"
                                            target="_blank" class="btn btn-sm btn-soft-success me-1"><i
                                                class="bx bx-send fs-16"></i></a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <!-- زر السابق -->
                        @if ($allinvoices->onFirstPage())
                            <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $allinvoices->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        <!-- أرقام الصفحات -->
                        @for ($page = 1; $page <= $allinvoices->lastPage(); $page++)
                            @if ($page == $allinvoices->currentPage())
                                <li class="page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $allinvoices->url($page) }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        <!-- زر التالي -->
                        @if ($allinvoices->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $allinvoices->nextPageUrl() }}">Next</a>
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
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll(".update-status").forEach(button => {
                        button.addEventListener("click", function() {
                            let invoiceId = this.getAttribute("data-id");
                            let currentStatus = this.getAttribute("data-status");

                            // 🔄 التبديل بين الحالات مع جعل overdue تصبح paid تلقائيًا
                            let newStatus;
                            if (currentStatus === "pending") {
                                newStatus = "paid";
                            } else if (currentStatus === "paid") {
                                newStatus = "pending";
                            } else if (currentStatus === "overdue") {
                                newStatus = "paid"; // 🔄 تحويل overdue إلى paid تلقائيًا
                            } else {
                                newStatus = "pending";
                            }

                            Swal.fire({
                                title: "Confirm Status Change",
                                text: `Are you sure you want to change the status to ${newStatus}?`,
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Yes, change it!",
                                cancelButtonText: "Cancel",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch(`/invoices/${invoiceId}`, {
                                            method: "PUT",
                                            headers: {
                                                "Content-Type": "application/json",
                                                "X-CSRF-TOKEN": document.querySelector(
                                                    'meta[name="csrf-token"]').getAttribute(
                                                    "content"),
                                            },
                                            body: JSON.stringify({
                                                status: newStatus
                                            }),
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                let badge = document.querySelector(
                                                    `#invoice-${invoiceId} .status-badge`);
                                                if (badge) {
                                                    badge.innerText = data.new_status;
                                                    badge.classList.remove(
                                                        "badge-soft-secondary",
                                                        "badge-soft-danger",
                                                        "badge-soft-success");

                                                    if (data.new_status === "paid") {
                                                        badge.classList.add(
                                                            "badge-soft-success");
                                                    } else if (data.new_status === "pending") {
                                                        badge.classList.add(
                                                            "badge-soft-secondary");
                                                    } else if (data.new_status === "overdue") {
                                                        badge.classList.add(
                                                        "badge-soft-danger");
                                                    }
                                                }
                                                Swal.fire("Updated!",
                                                    "Invoice status has been changed.",
                                                    "success").then(() => {
                                                    location
                                                .reload(); // 🔄 تحديث الصفحة بعد نجاح العملية
                                                });
                                            } else {
                                                Swal.fire("Error", "Failed to update status.",
                                                    "error");
                                            }
                                        })
                                        .catch(() => {
                                            Swal.fire("Error", "Something went wrong.",
                                            "error");
                                        });
                                }
                            });
                        });
                    });
                });
            </script>
    @endif
@endsection
