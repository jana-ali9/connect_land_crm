@extends('layouts.vertical', ['subTitle' => 'contract', 'title' => 'contract'])
@php($Name = 'contracts')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'contract', 'subTitle' => 'show contract'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("update $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit contract</h5>
                </div>

                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.updatebuilding", $contract->id) }}" enctype="multipart/form-data">

                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div>

                            <div class="mb-3">
                                <label for="client_search" class="form-label">Select Client</label>
                                <div class="input-group">
                                    <input type="text" id="client_search" class="form-control"
                                        value="{{ old('client_search', isset($contract) && $contract->client ? $contract->client->name : '') }}"
                                        placeholder="Search for a client" {{ isset($contract) ? '' : '' }}>
                                    <button id="add_client" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addClientModal">
                                        + Add
                                    </button>
                                </div>
                                <input type="hidden" id="client_id" name="client_id"
                                    value="{{ old('client_id', isset($contract) ? $contract->client_id : '') }}">
                                <div id="client_list" class="list-group position-absolute w-100 shadow"
                                    style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050;"></div>
                            </div>

                            @if ($propertyType == 'land')
                                <div class="mb-3 row">
                                    <label for="land-select" class="form-label">Land</label>
                                    <select class="form-select" id="land-select" name="land_id" required>
                                        <option disabled>Select one of Land</option>
                                        @foreach ($lands as $land)
                                            <option value="{{ $land->id }}"
                                                {{ isset($contract) && $contract->land_id == $land->id ? 'selected' : '' }}>
                                                {{ $land->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="mb-3 row">
                                    <div class="col-md-6">
                                        <label for="building-select" class="form-label">Building</label>
                                        <select class="form-select" id="building-select" name="building_id" required>
                                            <option disabled>Select one of Building</option>
                                            @foreach ($buildings as $building)
                                                <option value="{{ $building->id }}"
                                                    {{ isset($contract) && $contract->building_id == $building->id ? 'selected' : '' }}>
                                                    {{ $building->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="unit-select" class="form-label">Unit</label>
                                        <select class="form-select" id="unit-select" name="unit_id" required>
                                            <option disabled>Select unit</option>
                                            {{-- سيتم تعبئته تلقائيًا بالجافاسكريبت --}}
                                        </select>
                                    </div>
                            @endif
                        </div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const buildingsData = {!! $buildings->mapWithKeys(function ($building) {
                                        return [
                                            $building->id => $building->units->map(function ($unit) {
                                                    return [
                                                        'id' => $unit->id,
                                                        'name' => $unit->name,
                                                        'is_rented' => $unit->is_rented,
                                                        'is_payed' => $unit->is_payed,
                                                    ];
                                                })->toArray(),
                                        ];
                                    })->toJson() !!};

                                const buildingSelect = document.getElementById("building-select");
                                const unitSelect = document.getElementById("unit-select");

                                const selectedBuildingId = "{{ isset($contract) ? $contract->building_id : '' }}";
                                const selectedUnitId = "{{ isset($contract) ? $contract->unit_id : '' }}";

                                function loadUnits(buildingId, preselectedUnitId = null) {
                                    const units = buildingsData[buildingId] || [];
                                    unitSelect.innerHTML = '<option disabled>Select unit</option>';

                                    units.forEach(unit => {
                                        if (unit.is_rented != 1 && unit.is_payed != 1 || unit.id == preselectedUnitId) {
                                            const option = document.createElement("option");
                                            option.value = unit.id;
                                            option.textContent = unit.name;
                                            if (unit.id == preselectedUnitId) {
                                                option.selected = true;
                                            }
                                            unitSelect.appendChild(option);
                                        }
                                    });
                                }

                                // عند تحميل الصفحة - لو في بيانات من عقد سابق
                                if (selectedBuildingId) {
                                    buildingSelect.value = selectedBuildingId;
                                    loadUnits(selectedBuildingId, selectedUnitId);
                                }

                                // عند تغيير المبنى يدويًا
                                buildingSelect.addEventListener("change", function() {
                                    loadUnits(this.value);
                                });
                            });
                        </script>

                        <div class="mb-3 row">

                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Contract Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" required
                                    value="{{ $contract->start_date ?? old('start_date') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label">service End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control"
                                    value="{{ $contract->end_date ?? old('end_date') }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="base_rent" class="form-label">Purchase price</label>
                                <input type="number" id="base_rent" name="base_rent" class="form-control" min="0"
                                    step="any" required value="{{ $contract->base_rent ?? old('base_rent') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="amount_paid" class="form-label">Initial payment</label>
                                <input type="number" id="amount_paid" name="amount_paid" class="form-control" required
                                    value="{{ $contract->increase_rate ?? old('amount_paid') }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="billing_frequency" class="form-label">Duration of each bill per month</label>
                                <input type="number" id="billing_frequency" name="billing_frequency" class="form-control"
                                    required value="{{ $contract->billing_frequency ?? old('billing_frequency') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="invoice_price" class="form-label">Invoice price</label>
                                <input type="number" id="invoice_price" name="invoice_price" class="form-control" required
                                    value="{{ $contract->invoice_price ?? old('invoice_price') }}">
                            </div>
                        </div>


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
                        {{-- عرض الفيديو الحالي --}}
                        @if ($contract->contract_video)
                            <div class="mb-3">
                                <label class="form-label">Current Video</label>
                                <div class="d-flex align-items-center gap-3">
                                    <video width="300" controls>
                                        <source src="{{ $contract->video }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>

                                    <button type="button" class="btn btn-danger"
                                        onclick="deleteContractVideo({{ $contract->id }})">
                                        Delete Video
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- عرض الصور الحالية --}}
                        @if ($contract->images && count($contract->images))
                            <div class="mb-3">
                                <label class="form-label">Current Images</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($contract->images as $image)
                                        <div style="position: relative;" id="image-wrapper-{{ $image->id }}">
                                            <img src="{{ $image->image }}" width="120" class="border rounded">
                                            <button class="btn btn-sm btn-danger"
                                                style="position: absolute; top: -10px; right: -10px;" title="Delete"
                                                onclick="deleteImage({{ $image->id }})">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif


                        <div class="col-12" style="text-align: end">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </div>
                    </div>
            </div>
            </form>
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
@endsection

@section('script')
    <script>
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
                        let description = option.getAttribute('data-description') || 'No description available';
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

                    div.querySelector('.remove-service').addEventListener('click', function() {
                        let serviceId = this.getAttribute('data-id');
                        document.querySelector(`.service-item[data-id="${serviceId}"]`).remove();

                        Array.from(selectElement.options).forEach(option => {
                            if (option.value === serviceId) {
                                option.selected = false;
                            }
                        });
                    });
                }
            });
        }
    </script>

    <script>
        function deleteContractVideo(contractId) {
            if (!confirm('Are you sure you want to delete the video?')) return;

            fetch(`/contract-video-delete/${contractId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (res.ok) {
                        location.reload(); // إعادة تحميل الصفحة بعد الحذف
                    } else {
                        alert('Failed to delete the video.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred.');
                });
        }
    </script>
    <script>
        function deleteImage(imageId) {
            if (!confirm("هل أنت متأكد أنك تريد حذف الصورة؟")) return;

            fetch(`/contract-image/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to delete');
                    // احذف العنصر من الـ DOM
                    document.getElementById(`image-wrapper-${imageId}`).remove();
                })
                .catch(error => {
                    alert("فشل في حذف الصورة.");
                    console.error(error);
                });
        }
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
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('services');
            const featureSelect = document.getElementById('features');
            const selectedServicesDiv = document.getElementById('selected_services');
            const selectedFeaturesDiv = document.getElementById('selected_features');

            const selectedServices = {!! $contract->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'type' => $service->pivot->type ?? 'service',
                        'price' => $service->pivot->custom_price,
                        'name' => $service->name,
                        'description' => $service->description,
                    ];
                })->toJson() !!};

            selectedServices.forEach(service => {
                const isFeature = service.type === 'feature';
                const selectElement = isFeature ? featureSelect : serviceSelect;
                const targetDiv = isFeature ? selectedFeaturesDiv : selectedServicesDiv;

                // علم عليه كـ selected في الـ <select>
                for (let option of selectElement.options) {
                    if (option.value == service.id && option.dataset.type === service.type) {
                        option.selected = true;
                        break;
                    }
                }

                // استخدم نفس الفانكشن لإضافته في الـ UI
                updateSelectedList(selectElement, targetDiv, isFeature);
            });
        });
    </script>
