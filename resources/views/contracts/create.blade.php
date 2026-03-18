@extends('layouts.vertical', ['subTitle' => 'contract', 'title' => 'contract'])
@php($Name = 'contracts')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'contract', 'subTitle' => 'show contract'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add contract</h5>
                </div>

                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.store") }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div>
                            {{-- CLIENT SEARCH --}}
                            <div class="mb-3">
                                <label for="client_search" class="form-label">Select Client</label>
                                <div class="input-group">
                                    <input type="text" id="client_search" class="form-control"
                                        placeholder="Search for a client">
                                    <button id="add_client" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addClientModal">+ Add</button>
                                </div>
                                <input type="hidden" id="client_id" name="client_id">
                                <div id="client_list" class="list-group position-absolute w-100 shadow"
                                    style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050;"></div>
                            </div>

                            {{-- PROPERTY TYPE TOGGLE --}}
                            <div class="mb-3">
                                <label class="form-label">Property Type</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="propertyTypeSwitch"
                                        name="property_type" value="land">
                                    <label class="form-check-label" for="propertyTypeSwitch">Switch to Land</label>
                                </div>
                            </div>

                            {{-- BUILDING/UNIT --}}
                            <div class="mb-3 row transition-section" id="buildingUnitSection">
                                <div class="col-md-6">
                                    <label class="form-label">Building</label>
                                    <select name="building_id" id="building-select" class="form-select">
                                        <option value="">Select a building</option>
                                        @foreach ($buildings as $building)
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
                            <div class="mb-3 transition-section" id="landSection">
                                <label class="form-label">Land</label>
                                <select name="land_id" id="land-select" class="form-select">
                                    <option value="">Select land</option>
                                    @foreach ($lands as $land)
                                        <option value="{{ $land->id }}">{{ $land->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- DATES --}}
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" id="start_date" name="start_date" class="form-control" required
                                        value="{{ old('start_date') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" id="end_date" name="end_date" class="form-control"
                                        value="{{ old('end_date') }}">
                                </div>
                            </div>

                            {{-- BILLING --}}
                            <div class="mb-3 row">
                                <div class="col-md-4">
                                    <label for="billing_frequency" class="form-label">Duration of each bill per month</label>
                                    <input type="number" id="billing_frequency" name="billing_frequency"
                                        class="form-control" required value="{{ old('billing_frequency') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="amount_paid" class="form-label">Initial payment</label>
                                    <input type="number" id="amount_paid" name="amount_paid" class="form-control" required
                                        value="{{ old('amount_paid') ?? 0 }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="insurance" class="form-label">Insurance</label>
                                    <input type="number" id="insurance" name="insurance" class="form-control"
                                        min="0" step="any" required value="{{ old('insurance') ?? 0 }}">
                                </div>
                            </div>

                            {{-- RENT INCREASE --}}
                            <div class="mb-3 row">
                                <div class="col-md-4">
                                    <label for="base_rent" class="form-label">Initial rent</label>
                                    <input type="number" id="base_rent" name="base_rent" class="form-control"
                                        min="0" step="any" required value="{{ old('base_rent') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="increase_rate" class="form-label">The percentage of increase</label>
                                    <input type="number" id="increase_rate" name="increase_rate" class="form-control"
                                        min="0" step="any" required value="{{ old('increase_rate') ?? 0 }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="increase_frequency" class="form-label">Increased duration</label>
                                    <input type="number" id="increase_frequency" name="increase_frequency"
                                        class="form-control" min="0" required
                                        value="{{ old('increase_frequency') ?? 0 }}">
                                </div>
                            </div>

                            {{-- SERVICES --}}
                            <div class="mb-3">
                                <div id="selected_services"></div>
                                <label class="form-label">Select Services</label>
                                <select id="services" name="services[]" class="form-select" multiple>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" data-price="{{ $service->default_price }}"
                                            data-type="service">
                                            {{ $service->name }} - {{ $service->default_price }}$
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- FEATURES --}}
                            <div class="mb-3">
                                <div id="selected_features"></div>
                                <label class="form-label">Select Features</label>
                                <select id="features" name="services[]" class="form-select" multiple>
                                    @foreach ($features as $feature)
                                        <option value="{{ $feature->id }}"
                                            data-description="{{ $feature->description }}" data-type="feature">
                                            {{ $feature->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- FILES --}}
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label class="form-label">Select images</label>
                                    <input name="images[]" type="file" multiple="multiple" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Upload Video</label>
                                    <input type="file" name="contract_video" accept="video/*" class="form-control">
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- MODAL --}}
                <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addClientForm">
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">Client Name</label>
                                        <input type="text" class="form-control" id="client_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="client_phone" class="form-label">Client Phone</label>
                                        <input type="text" class="form-control" id="client_phone" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Save Client</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <style>
        .transition - section {
            transition: all 0.4 s ease - in - out;
            overflow: hidden;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggle = document.getElementById('propertyTypeSwitch');
            const buildingSection = document.getElementById('buildingUnitSection');
            const landSection = document.getElementById('landSection');

            const buildingSelect = document.getElementById('building-select');
            const unitSelect = document.getElementById('unit-select');
            const landSelect = document.getElementById('land-select');

            function updateToggleState() {
                const isLand = toggle.checked;

                buildingSection.style.maxHeight = isLand ? '0' : '1000px';
                buildingSection.style.opacity = isLand ? '0' : '1';
                buildingSection.style.pointerEvents = isLand ? 'none' : 'auto';

                landSection.style.maxHeight = isLand ? '1000px' : '0';
                landSection.style.opacity = isLand ? '1' : '0';
                landSection.style.pointerEvents = isLand ? 'auto' : 'none';

                buildingSelect.required = !isLand;
                unitSelect.required = !isLand;
                landSelect.required = isLand;

                if (isLand) {
                    buildingSelect.value = '';
                    unitSelect.value = '';
                } else {
                    landSelect.value = '';
                }
            }

            toggle.addEventListener('change', updateToggleState);
            updateToggleState();
        });

        document.addEventListener("DOMContentLoaded", function() {
            const switchElement = document.getElementById('propertyTypeSwitch');
            const buildingSection = document.getElementById('buildingUnitSection');
            const landSection = document.getElementById('landSection');

            const buildingSelect = document.getElementById('building-select');
            const unitSelect = document.getElementById('unit-select');
            const landSelect = document.getElementById('land-select');

            function updateToggleState() {
                const isLand = switchElement.checked;

                // Toggle visibility
                buildingSection.style.display = isLand ? 'none' : 'flex';
                landSection.style.display = isLand ? 'block' : 'none';

                // Toggle required
                buildingSelect.required = !isLand;
                unitSelect.required = !isLand;
                landSelect.required = isLand;

                // Clear values
                if (isLand) {
                    buildingSelect.value = '';
                    unitSelect.value = '';
                } else {
                    landSelect.value = '';
                }
            }

            switchElement.addEventListener('change', updateToggleState);
            updateToggleState(); // Initialize state
        });



        // Function to handle the client search and selection
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



        document.addEventListener("DOMContentLoaded", function() {
            const switchElement = document.getElementById('propertyTypeSwitch');
            const buildingSelect = document.getElementById('building-select');
            const unitSelect = document.getElementById('unit-select');
            const landSelect = document.getElementById('land-select');

            function applyToggleState() {
                if (switchElement.checked) {
                    landSelect.disabled = false;
                    landSelect.required = true;
                    buildingSelect.disabled = true;
                    unitSelect.disabled = true;
                } else {
                    landSelect.disabled = true;
                    landSelect.required = false;
                    buildingSelect.disabled = false;
                    unitSelect.disabled = false;
                }
            }

            applyToggleState();

            switchElement.addEventListener('change', applyToggleState);
        });
document.getElementById('building-select').addEventListener('change', function() {
    const buildingId = this.value;
    const unitSelect = document.getElementById('unit-select');

    unitSelect.innerHTML = '<option value="">Loading...</option>';

    if (!buildingId) {
        unitSelect.innerHTML = '<option value="">Select a unit</option>';
        return;
    }

    // تم تعديل الرابط ليطابق ما هو موجود في web.php
    fetch(`/units/by-building/${buildingId}`)
        .then(response => {
            if (!response.ok) throw new Error('Not Found or Unauthorized');
            return response.json();
        })
        .then(data => {
            unitSelect.innerHTML = '<option value="">Select a unit</option>';
            data.forEach(unit => {
                // تم تعديل unit.unit_number إلى unit.name ليطابق قاعدة بياناتك
                unitSelect.innerHTML += `<option value="${unit.id}">${unit.name}</option>`;
            });
        })
        .catch(error => {
            console.error('Error fetching units:', error);
            unitSelect.innerHTML = '<option value="">Error loading units</option>';
        });
});
    </script>
@endsection
