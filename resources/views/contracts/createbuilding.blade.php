@extends('layouts.vertical', ['subTitle' => 'contract', 'title' => 'contract'])
@php($Name = 'contracts')
@section('content')
@include('layouts.partials/page-title', ['title' => 'contract', 'subTitle' => 'Add contract'])
<div class="card-body">

    @if (auth()->user()->hasPermission("create $Name"))
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Add Selling Contract</h5>
        </div>

        @include('layouts.partials.massages')
        <form method="POST" action="{{ route("$Name.storebuilding") }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                {{-- CLIENT --}}
                <div class="mb-3">
                    <label class="form-label">Select Client</label>
                    <div class="input-group">
                        <input type="text" id="client_search" class="form-control" placeholder="Search for a client">
                        <button id="add_client" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">+ Add</button>
                    </div>
                    <input type="hidden" id="client_id" name="client_id">
                    <div id="client_list" class="list-group position-absolute w-100 shadow" style="display:none; max-height:200px; overflow-y:auto; z-index:1050;"></div>
                </div>

                {{-- PROPERTY SWITCH --}}
                <div class="mb-3">
                    <label class="form-label">Property Type</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="propertyTypeSwitch" name="property_type" value="land">
                        <label class="form-check-label" for="propertyTypeSwitch">Switch to Land</label>
                    </div>
                </div>

                {{-- BUILDING/UNIT --}}
                <div class="mb-3 row" id="buildingUnitSection">
                    <div class="col-md-6">
                        <label class="form-label">Building</label>
                        <select name="building_id" id="building-select" class="form-select">
                            <option value="">Select a building</option>
                            @foreach($buildings as $building)
                            <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" id="unit-select" class="form-select">
                            <option value="">Select a unit</option>
                        </select>
                    </div>
                </div>

                {{-- LAND --}}
                <div class="mb-3" id="landSection">
                    <label class="form-label">Land</label>
                    <select name="land_id" id="land-select" class="form-select">
                        <option value="">Select land</option>
                        @foreach($lands as $land)
                        <option value="{{ $land->id }}">{{ $land->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- DATES --}}
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label class="form-label">Contract Date</label>
                        <input type="date" name="start_date" class="form-control" required value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                </div>

                {{-- PRICE --}}
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label class="form-label">Purchase price</label>
                        <input type="number" name="base_rent" id="purchase_price" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Initial payment</label>
                        <input type="number" name="amount_paid" id="initial_payment" class="form-control" required>
                    </div>
                </div>

                {{-- BILLING --}}
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label class="form-label">Duration of each bill per month</label>
                        <input type="number" name="billing_frequency" id="billing_frequency" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Invoice price</label>
                        <input type="number" name="invoice_price" id="invoice_price" class="form-control" required>
                    </div>
                </div>

                {{-- SERVICES --}}
                <div class="mb-3">
                    <label class="form-label">Select Services</label>
                    <select name="services[]" class="form-select" multiple>
                        @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} - ${{ $service->default_price }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- FEATURES --}}
                <div class="mb-3">
                    <label class="form-label">Select Features</label>
                    <select name="features[]" class="form-select" multiple>
                        @foreach($features as $feature)
                        <option value="{{ $feature->id }}">{{ $feature->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- FILES --}}
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label class="form-label">Upload images</label>
                        <input type="file" name="images[]" class="form-control" multiple>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload video</label>
                        <input type="file" name="contract_video" class="form-control" accept="video/*">
                    </div>
                </div>

                {{-- SUBMIT --}}
                <div class="text-end">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </div>
        </form>

        {{-- ADD CLIENT MODAL --}}
        <div class="modal fade" id="addClientModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addClientForm">
                            <div class="mb-3">
                                <label>Client Name</label>
                                <input type="text" class="form-control" id="client_name" required>
                            </div>
                            <div class="mb-3">
                                <label>Client Phone</label>
                                <input type="text" class="form-control" id="client_phone" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const switchElement = document.getElementById('propertyTypeSwitch');
        const buildingSection = document.getElementById('buildingUnitSection');
        const landSection = document.getElementById('landSection');
        function toggleProperty() {
            if (switchElement.checked) {
                landSection.style.display = 'block';
                buildingSection.style.display = 'none';
            } else {
                landSection.style.display = 'none';
                buildingSection.style.display = 'flex';
            }
        }
        toggleProperty();
        switchElement.addEventListener('change', toggleProperty);
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buildingSelect = document.getElementById("building-select");
        const unitSelect = document.getElementById("unit-select");
        const buildingsData = {!! $buildings->mapWithKeys(fn($b) => [
            $b->id => $b->units->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'is_rented' => $u->is_rented,
                'is_payed' => $u->is_payed
            ])->toArray()
        ])->toJson() !!};

        function populateUnits(buildingId) {
            unitSelect.innerHTML = '<option value="">Select a unit</option>';
            (buildingsData[buildingId] || []).forEach(unit => {
                if (!unit.is_rented && !unit.is_payed) {
                    let opt = document.createElement("option");
                    opt.value = unit.id;
                    opt.textContent = unit.name;
                    unitSelect.appendChild(opt);
                }
            });
        }
        buildingSelect.addEventListener("change", function() {
            populateUnits(this.value);
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const purchase = document.getElementById("purchase_price");
        const initial = document.getElementById("initial_payment");
        const billing = document.getElementById("billing_frequency");
        const invoice = document.getElementById("invoice_price");
        function check() {
            let total = parseFloat(purchase.value) || 0;
            let paid = parseFloat(initial.value) || 0;
            if (paid >= total) {
                billing.disabled = true;
                invoice.disabled = true;
            } else {
                billing.disabled = false;
                invoice.disabled = false;
            }
        }
        purchase.addEventListener("input", check);
        initial.addEventListener("input", check);
        check();
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        function setupSearch(inputId, listId, url, hiddenInputId) {
            let input = document.getElementById(inputId);
            let list = document.getElementById(listId);
            input.addEventListener("keyup", function() {
                let q = input.value.trim();
                if (q.length < 1) { list.style.display="none"; return; }
                fetch(`${url}?q=${q}`)
                .then(res => res.json())
                .then(data => {
                    list.innerHTML = "";
                    if (data.length===0) {
                        list.innerHTML = `<div class="list-group-item text-center text-muted">No results</div>`;
                    } else {
                        data.forEach(item => {
                            let el = document.createElement("a");
                            el.className = "list-group-item list-group-item-action";
                            el.textContent = item.name + (item.phone ? ` (${item.phone})` : "");
                            el.addEventListener("click", e => {
                                e.preventDefault();
                                input.value = item.name;
                                document.getElementById(hiddenInputId).value = item.id;
                                list.style.display="none";
                            });
                            list.appendChild(el);
                        });
                    }
                    list.style.display="block";
                });
            });
        }
        setupSearch("client_search","client_list","/clients/search","client_id");
    });
</script>

<script>
    // client search
    document.addEventListener("DOMContentLoaded", function() {
        function setupSearch(inputId, listId, url, hiddenInputId) {
            let input = document.getElementById(inputId);
            let list = document.getElementById(listId);
            input.addEventListener("keyup", function() {
                let q = input.value.trim();
                if (q.length < 1) { list.style.display = "none"; return; }
                fetch(`${url}?q=${q}`)
                    .then(res => res.json())
                    .then(data => {
                        list.innerHTML = "";
                        if (data.length === 0) {
                            list.innerHTML = `<div class="list-group-item text-center text-muted">No results found</div>`;
                        } else {
                            data.forEach(item => {
                                let el = document.createElement("a");
                                el.className = "list-group-item list-group-item-action";
                                el.textContent = item.name + (item.phone ? ` (${item.phone})` : "");
                                el.dataset.id = item.id;
                                el.addEventListener("click", function(e) {
                                    e.preventDefault();
                                    input.value = item.name;
                                    document.getElementById(hiddenInputId).value = item.id;
                                    list.style.display = "none";
                                });
                                list.appendChild(el);
                            });
                        }
                        list.style.display = "block";
                    });
            });
            document.addEventListener("click", e => {
                if (!input.contains(e.target) && !list.contains(e.target)) {
                    list.style.display = "none";
                }
            });
        }
        setupSearch("client_search", "client_list", "/clients/search", "client_id");
    });
</script>
<script>
    // features/services logic (optional enhancement later)
</script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateSelectedList(selectElement, targetElement, isFeature = false) {
                let selectedOptions = Array.from(selectElement.selectedOptions);
                let existingValues = new Set();

                targetElement.querySelectorAll('.service-item').forEach(item => {
                    existingValues.add(item.dataset.id);
                });

                selectedOptions.forEach(option => {
                    if (!existingValues.has(option.value)) {
                        let div = document.createElement('div');
                        div.classList.add('service-item', 'border', 'p-2', 'mb-2', 'rounded');
                        div.dataset.id = option.value;

                        let type = option.getAttribute('data-type') || 'service';

                        if (type === 'feature') {
                            let description = option.getAttribute('data-description') ||
                                'No description available';
                            div.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${option.textContent}</strong><br>
                                <small class="text-muted">${description}</small>
                            </div>
                            <input type="hidden" name="services[${option.value}][id]" value="${option.value}">
                            <input type="hidden" name="services[${option.value}][type]" value="feature">
                            <button type="button" class="btn btn-danger remove-service" data-id="${option.value}">Remove</button>
                        </div>
                    `;
                        } else {
                            let defaultPrice = option.getAttribute('data-price') || 0;
                            div.innerHTML = `
                        <div class="input-group">
                            <span class="input-group-text fw-bold">${option.textContent}</span>
                            <input type="hidden" name="services[${option.value}][id]" value="${option.value}">
                            <input type="hidden" name="services[${option.value}][type]" value="service">
                            <input type="number" name="services[${option.value}][price]" class="form-control" min="0" step="any" value="${defaultPrice}" placeholder="Custom Price (Optional)">
                            <button type="button" class="btn btn-danger remove-service" data-id="${option.value}">Remove</button>
                        </div>
                    `;
                        }

                        targetElement.appendChild(div);

                        // إزالة الخدمة أو الميزة عند الحذف
                        div.querySelector('.remove-service').addEventListener('click', function() {
                            let serviceId = this.getAttribute('data-id');
                            document.querySelector(`.service-item[data-id="${serviceId}"]`)
                                .remove();

                            Array.from(selectElement.options).forEach(option => {
                                if (option.value === serviceId) {
                                    option.selected = false;
                                }
                            });
                        });
                    }
                });
            }

            let serviceSelect = document.getElementById('services');
            let featureSelect = document.getElementById('features');
            let serviceList = document.getElementById('selected_services');
            let featureList = document.getElementById('selected_features');

            serviceSelect.addEventListener('change', function() {
                updateSelectedList(serviceSelect, serviceList);
            });

            featureSelect.addEventListener('change', function() {
                updateSelectedList(featureSelect, featureList, true);
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function setupSearch(inputId, listId, url, hiddenInputId) {
                let searchInput = document.getElementById(inputId);
                let resultList = document.getElementById(listId);

                searchInput.addEventListener("keyup", function() {
                    let query = searchInput.value.trim();

                    if (query.length < 1) {
                        resultList.style.display = "none";
                        return;
                    }

                    fetch(`${url}?q=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            resultList.innerHTML = "";
                            if (data.length === 0) {
                                let noResult = document.createElement("div");
                                noResult.classList.add("list-group-item", "text-muted", "text-center");
                                noResult.textContent = "No results found";
                                resultList.appendChild(noResult);
                            } else {
                                data.forEach(item => {
                                    let listItem = document.createElement("a");
                                    listItem.href = "#";
                                    listItem.classList.add("list-group-item",
                                        "list-group-item-action");
                                    listItem.textContent = item.name + (item.phone ?
                                        ` (${item.phone})` : "");
                                    listItem.dataset.id = item.id;

                                    listItem.addEventListener("click", function(event) {
                                        event.preventDefault();
                                        searchInput.value = item.name;
                                        document.getElementById(hiddenInputId).value =
                                            item.id;
                                        resultList.style.display = "none";
                                    });

                                    resultList.appendChild(listItem);
                                });
                            }

                            resultList.style.display = "block";
                        })
                        .catch(error => console.error(`Error fetching ${inputId}:`, error));
                });

                document.addEventListener("click", function(event) {
                    if (!searchInput.contains(event.target) && !resultList.contains(event.target)) {
                        resultList.style.display = "none";
                    }
                });
            }

            setupSearch("client_search", "client_list", "/clients/search", "client_id");

            document.getElementById("addClientForm").addEventListener("submit", function(event) {
                event.preventDefault();

                let clientName = document.getElementById("client_name").value.trim();
                let clientPhone = document.getElementById("client_phone").value.trim();

                if (!clientName || !clientPhone) {
                    alert("Please fill in both fields.");
                    return;
                }

                fetch("/clients/store1", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .getAttribute("content")
                        },
                        body: JSON.stringify({
                            name: clientName,
                            phone: clientPhone
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Client added successfully!");
                            document.getElementById("client_search").value = clientName;
                            document.getElementById("client_id").value = data.client_id;
                            var modal = bootstrap.Modal.getInstance(document.getElementById(
                                "addClientModal"));
                            modal.hide();
                            document.getElementById("client_name").value = "";
                            document.getElementById("client_phone").value = "";
                        } else {
                            alert("Failed to add client.");
                        }
                    })
                    .catch(error => console.error("Error adding client:", error));
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const purchaseInput = document.getElementById('base_rent');
            const paymentInput = document.getElementById('amount_paid');
            const billingInput = document.getElementById('billing_frequency');
            const invoice_price = document.getElementById('invoice_price');

            function toggleBillingDuration() {
                const purchasePrice = parseFloat(purchaseInput.value) || 0;
                const initialPayment = parseFloat(paymentInput.value) || 0;

                if (initialPayment >= purchasePrice) {
                    billingInput.disabled = true;
                    billingInput.value = '';
                    invoice_price.disabled = true;
                    invoice_price.value = '';
                } else {
                    billingInput.disabled = false;
                    invoice_price.disabled = false;
                }
            }

            // Attach listeners
            purchaseInput.addEventListener('input', toggleBillingDuration);
            paymentInput.addEventListener('input', toggleBillingDuration);

            // Run once on load
            toggleBillingDuration();
        });
    </script>
