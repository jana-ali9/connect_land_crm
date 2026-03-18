@extends('layouts.vertical', ['title' => 'Contract Details'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Logo & title -->
                    <div class="clearfix">
                        <div class="float-sm-end">
                            <div class="auth-logo">
                                <img class="logo-dark me-1" src="/images/logo-dark-full.png" alt="logo-dark" height="24" />
                                <img class="logo-light me-1" src="/images/logo-light-full.png" alt="logo-dark"
                                    height="24" />
                            </div>

                            <address class="mt-3">
                                Connect Media<br>
                                lebanon - Dubai<br>
                                <abbr title="Phone">P:</abbr> +971551224221
                            </address>
                        </div>
                        <div class="float-sm-start">
                            <h5 class="card-title mb-2">Contract: #{{ $contract->id }}</h5>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Customer</h6>
                            <h6 class="fs-16"> {{ $contract->client->name }}</h6>
                            <address>
                                <abbr title="Phone">P:</abbr> {{ $contract->client->phone }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            @if ($contract->unit_id && $contract->unit)
                                <h6 class="fw-normal text-muted">Unit</h6>
                                <h6 class="fs-16">{{ $contract->unit->name }}</h6>
                                <address>
                                    {{ $contract->unit->description }}<br>
                                    {{ optional($contract->unit->building)->address }}
                                </address>
                            @elseif ($contract->building_id && $contract->building)
                                <h6 class="fw-normal text-muted">Building</h6>
                                <h6 class="fs-16">{{ $contract->building->name }}</h6>
                                <address>
                                    {{ $contract->building->address }}
                                </address>
                            @elseif ($contract->land_id && $contract->land)
                                <h6 class="fw-normal text-muted">Land</h6>
                                <h6 class="fs-16">{{ $contract->land->name }}</h6>
                                <address>
                                    {{ $contract->land->location }}<br>
                                    Area: {{ $contract->land->area }} m²
                                </address>
                            @else
                                <h6 class="fw-normal text-muted">Property</h6>
                                <h6 class="fs-16">N/A</h6>
                            @endif
                        </div>

                    </div>

                    <p class="text-muted">
                        All accounts are to be paid within 7 days from receipt of
                        invoice. To be paid by cheque or credit card or direct payment
                        online. If account is not paid within 7 days the credits details
                        supplied as confirmation of work undertaken will be charged the
                        agreed quoted fee noted above.
                    </p>
                    <!-- end row -->
                    <h4 id="scrollspyHeading1">Insurance : {{ $contract->insurance }}$</h4>
                    @foreach ($features as $feature)
                        <h4 id="scrollspyHeading1">{{ $feature->name }}</h4>
                        <p>{{ $feature->description }}</p>
                    @endforeach
                    @include('layouts.partials.massages')
                    <div class="row">

                        <div class="col-12">
                            <div class="table-responsive table-borderless text-nowrap mt-3 table-centered">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Service</th>
                                            <th class="border-0 py-2">Price</th>
                                            <th class="text-end border-0 py-2">Total</th>
                                            <th class="text-end border-0 py-2"><a href="#" id="add-service-btn"
                                                    class="btn btn-sm btn-soft-primary" data-bs-toggle="modal"
                                                    data-bs-target="#addServiceModal">Add Service</a>
                                            </th>
                                        </tr>
                                    </thead> <!-- end thead -->
                                    <tbody>
                                        {{--  <tr>
                                            <td>rent</td>
                                            <td>${{ $contract->base_rent }}</td>

                                            <td class="text-end">${{ $contract->base_rent }}</td>
                                            <td class="text-end">                                            </td>
                                        </tr> --}}
                                        @foreach ($services as $service)
                                            <tr>
                                                <td>{{ $service->name }}</td>
                                                <td>{{ $service->price }}</td>
                                                <td class="text-end">{{ $service->price }}</td>
                                                <td class="text-end">
                                                    <button type="button"
                                                        class="btn btn-sm btn-soft-primary me-1 edit-service-btn"
                                                        data-id="{{ $service->pivot->id }}"
                                                        data-name="{{ $service->name }}"
                                                        data-price="{{ $service->pivot->custom_price }}">
                                                        <i class="bx bx-cog fs-16"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $service->pivot->id }}"
                                                        style="display: initial" method="POST"
                                                        action="{{ route('contract-services.destroy', $service->pivot->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="#!"
                                                            onclick="confirmDelete({{ $service->pivot->id }})"
                                                            class="btn btn-sm btn-soft-danger"><i
                                                                class="bx bx-trash fs-16"></i></a>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody> <!-- end tbody -->
                                </table> <!-- end table -->
                            </div> <!-- end table responsive -->
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                    <!-- Modal لإضافة خدمة جديدة -->
                    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <!-- ✅ Form يجب أن يحتوي على method و action -->
                                <form method="POST" action="{{ route('contract-services.store') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="contract_id" value="{{ $contract->id }}">

                                        <label for="serviceSelect" class="form-label">Select Service</label>
                                        <select id="serviceSelect" name="service_id" class="form-select">
                                            <option disabled selected>Choose a service</option>
                                            @foreach ($allservices as $service)
                                                <option value="{{ $service->id }}"
                                                    data-price="{{ $service->default_price }}">
                                                    {{ $service->name }} - ${{ $service->default_price }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="mt-3">
                                            <label for="servicePrice" class="form-label">Custom Price</label>
                                            <input type="number" id="servicePrice" name="custom_price" class="form-control"
                                                min="0" step="any" placeholder="Enter custom price">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Add Service</button>
                                    </div>
                                </form> <!-- ✅ تأكد أن زر الإرسال داخل الفورم -->
                            </div>
                        </div>
                    </div>
                    <!-- Modal لتعديل سعر الخدمة -->
                    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form method="POST" id="editServiceForm">
                                    @csrf
                                    @method('PUT') <!-- مهم جداً -->
                                    <div class="modal-body">
                                        <input type="hidden" name="contract_service_id" id="editServiceId">

                                        <label class="form-label">Service Name</label>
                                        <input type="text" id="editServiceName" class="form-control" readonly>

                                        <label class="form-label mt-3">Custom Price</label>
                                        <input type="number" name="custom_price" id="editServicePrice"
                                            class="form-control" min="0" step="any">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal لإضافة خدمة جديدة -->
                    <div class="modal fade" id="addServicepay" tabindex="-1" aria-labelledby="addServicepayLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addServicepayLabel">Add New Service</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <!-- ✅ Form يجب أن يحتوي على method و action -->
                                <form method="POST" action="{{ route('pay-services.store') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="invoice_id" value="">
                                        <div class="mt-3">
                                            <label for="servicePrice" class="form-label">Price</label>
                                            <input type="number" id="servicePrice" name="custom_price"
                                                class="form-control" min="0" step="any"
                                                value="{{ $services->sum('price') }}"
                                                min="{{ $services->sum('price') - $contract->amount_for_services }}"
                                                placeholder="Enter servise price">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">pay Service</button>
                                    </div>
                                </form> <!-- ✅ تأكد أن زر الإرسال داخل الفورم -->
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-sm-7">

                        </div>
                        <div class="col-sm-5">

                            <div class="float-end">
                                <h3>services : {{ $services->sum('price') }} USD</h3>
                            </div>
                            <div class="clearfix"></div>
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                    <br>
                    <h4 id="scrollspyHeading1">contract invoices</h4>
                    @if (session('status'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-borderless text-nowrap mt-3 table-centered">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Invoice Date</th>
                                            <th class="border-0 py-2">Amount Due</th>
                                            <th class="border-0 py-2">Amount paid</th>
                                            <th class="text-end border-0 py-2">Status</th>
                                            <th class="text-end border-0 py-2" scope="col">Action <a href="#"
                                                    id="add-service-btn" class="btn btn-sm btn-soft-primary"
                                                    data-bs-toggle="modal" data-bs-target="#payContractAmount">
                                                    <!-- ✅ أضف هذا الجزء -->
                                                    Pay invoices
                                                </a></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoices as $invoice)
                                            <tr id="invoice-{{ $invoice->id }}">
                                                <td>{{ $invoice->invoice_date }}</td>
                                                <td>{{ $invoice->amount_due }}$</td>{{-- <td>{{ $invoice->amount_due + $invoice->services_cost }}$</td> --}}
                                                <td>{{ $invoice->amount_paid }}$</td>
                                                <td class="text-end">
                                                    <span
                                                        class='badge status-badge badge-soft-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'pending' ? 'secondary' : 'danger') }}'>
                                                        {{ $invoice->status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">

                                                    @if ($invoice->status != 'paid')
                                                        @if (auth()->user()->hasPermission('update invoices'))
                                                            <button class="btn btn-sm btn-soft-primary me-1 update-status"
                                                                data-id="{{ $invoice->id }}"
                                                                data-status="{{ $invoice->status }}">
                                                                <i class="bx bx-cog fs-16"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                    <a href="{{ route('invoice.view', $invoice->id) }}" target="_blank"
                                                        class="btn btn-sm btn-soft-secondary me-1"><i
                                                            class="bx bx-show fs-16"></i></a>

                                                    <a href="https://wa.me/+961{{ $invoice->client->phone }}?text={{ route('invoice.view', $invoice->id) }}"
                                                        target="_blank" class="btn btn-sm btn-soft-success me-1"><i
                                                            class="bx bx-send fs-16"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Modal لدفع مبلغ معين لعقد معين -->
                    <div class="modal fade" id="payContractAmount" tabindex="-1"
                        aria-labelledby="payContractAmountLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="payContractAmountLabel">Pay the contract amount</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <form method="POST" action="{{ route('contracts.pay') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="contract_id" value="{{ $contract->id }}">
                                        <div class="mt-3">
                                            <label for="payAmount" class="form-label">The amount to be paid</label>
                                            <input type="number" id="payAmount" name="amount" class="form-control"
                                                min="0" step="any" placeholder="Write the amount here">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">cancel</button>
                                        <button type="submit" class="btn btn-primary">pay</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if (count($invoicesServices) > 0)
                        <br>
                        <h4 id="scrollspyHeading1">invoices Services invoices</h4>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive table-borderless text-nowrap mt-3 table-centered">
                                    <table class="table mb-0">
                                        <thead class="bg-light bg-opacity-50">
                                            <tr>
                                                <th class="border-0 py-2">Invoice Date</th>
                                                <th class="border-0 py-2">Amount Due</th>
                                                <th class="border-0 py-2">Amount paid</th>
                                                <th class="text-end border-0 py-2">Status</th>
                                                <th class="text-end border-0 py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoicesServices as $invoicesService)
                                                <tr id="invoice-{{ $invoicesService->id }}">
                                                    <td>{{ $invoicesService->invoice_date }}</td>
                                                    <td>{{ $invoicesService->services_cost }}$</td>{{-- <td>{{ $invoice->amount_due + $invoice->services_cost }}$</td> --}}
                                                    <td>{{ $invoicesService->amount_paid }}$</td>
                                                    <td class="text-end">
                                                        <span
                                                            class='badge status-badge badge-soft-{{ $invoicesService->status == 'paid' ? 'success' : ($invoicesService->status == 'pending' ? 'secondary' : 'danger') }}'>
                                                            {{ $invoicesService->status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        @if ($invoicesService->status != 'paid')
                                                            <a href="#" id="add-service-btn"
                                                                class="btn btn-sm btn-soft-primary" data-bs-toggle="modal"
                                                                data-bs-target="#addServicepay"
                                                                data-invoice-id="{{ $invoicesService->id }}">
                                                                <!-- ✅ أضف هذا الجزء -->
                                                                Pay Services
                                                            </a>
                                                        @endif

                                                        <a href="{{ route('invoice.view', $invoicesService->id) }}"
                                                            target="_blank" class="btn btn-sm btn-soft-secondary me-1"><i
                                                                class="bx bx-show fs-16"></i></a>

                                                        <a href="https://wa.me/+961{{ $invoicesService->client->phone }}?text={{ route('invoice.view', $invoicesService->id) }}"
                                                            target="_blank" class="btn btn-sm btn-soft-success me-1"><i
                                                                class="bx bx-send fs-16"></i></a>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row mt-3">
                            <div class="col-sm-7">

                            </div>
                            <div class="col-sm-5">

                                <div class="float-end">
                                    @if ($contract->amount_for_services > 0)
                                        <h5>available for services : {{ $contract->amount_for_services }} USD</h5>
                                    @endif
                                </div>
                                <div class="clearfix"></div>
                            </div> <!-- end col -->
                        </div>
                        <br>
                    @endif
                    <br>
                    @if (count($contract->images) > 0)
                        <h4 id="scrollspyHeading1">contract images</h4>
                        <div class="row">
                            @foreach ($contract->images as $index => $image)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <img class="card-img-top" src="{{ $image->image }}" alt="Contract Image">
                                    </div>
                                </div>
                            @endforeach
                            @if ($contract->video)
                                <video class="col-md-4 mb-3" width="100%" controls>
                                    <source src="{{ $contract->video }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                        </div>
                    @endif

                    <div class="mt-5 mb-1">
                        <div class="text-end d-print-none">
                            <a href="javascript:window.print()" class="btn btn-primary">Print</a>

                            @if (auth()->user()->hasPermission('update contracts'))
                                <a href="{{ route('contracts.edit', $contract->id) }}"
                                    class="btn btn-soft-secondary me-1">Edit</i></a>
                            @endif
                            @if ($contract->contract_status != 'suspended')
                                <form id="delete-form-{{ $contract->id }}" style="display: initial" method="POST"
                                    action="{{ route('contracts.cancellation', $contract->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <a href="#!" onclick="confirmcancellation({{ $contract->id }})"
                                        class="btn btn-danger">cancellation</a>
                                </form>
                            @endif
                            @if ($contract->contract_status == 'suspended')
                                <form id="delete-form-{{ $contract->id }}" style="display: initial" method="POST"
                                    action="{{ route('contracts.destroy', $contract->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <a href="#!" onclick="confirmDelete({{ $contract->id }})"
                                        class="btn btn-sm btn-soft-danger">delete</a>
                                </form>
                            @endif
                        </div>
                    </div>

                </div> <!-- end card body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
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
    function confirmcancellation(id) {
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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // استهدف جميع الأزرار التي تحتوي على data-invoice-id
        document.querySelectorAll("#add-service-btn").forEach(button => {
            button.addEventListener("click", function() {
                // احصل على invoice_id من الزر المضغوط
                var invoiceId = this.getAttribute("data-invoice-id");

                // ضع قيمة invoice_id داخل input المخفي داخل المودال
                document.querySelector("#addServicepay input[name='invoice_id']").value =
                    invoiceId;
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-service-btn').forEach(button => {
            button.addEventListener('click', function() {
                let serviceId = this.getAttribute('data-id');
                let serviceName = this.getAttribute('data-name');
                let servicePrice = this.getAttribute('data-price');

                document.getElementById('editServiceId').value = serviceId;
                document.getElementById('editServiceName').value = serviceName;
                document.getElementById('editServicePrice').value = servicePrice;

                // ✅ ضبط الفورم ليقوم بإرسال الطلب إلى PUT route الصحيح
                document.getElementById('editServiceForm').action =
                    `/contract-services/${serviceId}`;

                let modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
                modal.show();
            });
        });
    });
</script>

<script>
    document.getElementById('add-service-btn').addEventListener('click', function(event) {
        event.preventDefault();

        let availableServices = [];
        let existingServices = new Set();

        // جمع جميع الخدمات المضافة مسبقًا
        document.querySelectorAll('.service-item').forEach(item => {
            existingServices.add(item.dataset.id);
        });

        // جلب جميع الخدمات المتاحة ولم يتم اختيارها بعد
        let serviceSelect = document.getElementById('serviceSelect');
        serviceSelect.innerHTML = '<option disabled selected>Choose a service</option>';

        Array.from(serviceSelect.options).forEach(option => {
            if (!existingServices.has(option.value)) {
                let optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.textContent;
                serviceSelect.appendChild(optionElement);
            }
        });

        // عرض المودال
        let modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
        modal.show();
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".update-status").forEach(button => {
            button.addEventListener("click", function() {
                let invoiceId = this.getAttribute("data-id");
                let currentStatus = this.getAttribute("data-status");
                let newStatus = currentStatus === "pending" ? "paid" :
                    "pending"; // تبديل الحالة

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
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
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
                                    badge.innerText = data.new_status;
                                    badge.classList.remove("badge-soft-secondary",
                                        "badge-soft-danger",
                                        "badge-soft-success");
                                    badge.classList.add(data.new_status === "paid" ?
                                        "badge-soft-success" :
                                        "badge-soft-secondary");
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
