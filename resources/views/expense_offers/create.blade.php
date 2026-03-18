@extends('layouts.vertical', ['subTitle' => 'Client', 'title' => 'Client'])
@php($Name = 'unit-expenses')

@section('content')
@include('layouts.partials.page-title', ['title' => 'Client', 'subTitle' => 'show unit-expenses'])

<div class="card-body">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Add unit-expenses</h5>
        </div>

        @include('layouts.partials.massages')

        <form method="POST" action="{{ route("$Name.storeInPage") }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="expense_name" class="form-label">Expense Name</label>
                    <input type="text" id="expense_name" name="expense_name" class="form-control" required
                        value="{{ old('expense_name') }}">
                </div>

                {{-- Toggle Switch --}}
                <div class="mb-3">
                    <label class="form-label">Property Type</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="propertyTypeSwitch" name="property_type"
                            value="land">
                        <label class="form-check-label" for="propertyTypeSwitch">Switch to Land</label>
                    </div>
                </div>

                {{-- Building & Unit --}}
                <div class="mb-3 row transition-section" id="buildingUnitSection">
                    <div class="col-md-6">
                        <label class="form-label">Building</label>
                        <select class="form-select" id="building-select" name="building_id">
                            <option disabled selected>Select Building</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unit</label>
                        <select class="form-select" id="unit-select" name="unit_id">
                            <option disabled selected>Select Unit</option>
                        </select>
                    </div>
                </div>

                {{-- Land --}}
                <div class="mb-3 transition-section" id="landSection">
                    <label class="form-label">Land</label>
                    <select class="form-select" name="land_id" id="land-select">
                        <option disabled selected>Select Land</option>
                        @foreach ($lands as $land)
                            <option value="{{ $land->id }}">{{ $land->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Hidden Inputs --}}
                <input type="hidden" name="allocation_type" id="allocation_type" value="unit">
                <input type="hidden" id="target_id" name="target_id">

                {{-- Amount --}}
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="0" step="any"
                        required value="{{ old('amount') }}">
                </div>

                {{-- Category --}}
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option disabled selected>Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="text-end">
                    <button class="btn btn-primary" type="submit">Submit form</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggle = document.getElementById("propertyTypeSwitch");
        const buildingSection = document.getElementById("buildingUnitSection");
        const landSection = document.getElementById("landSection");

        const buildingSelect = document.getElementById("building-select");
        const unitSelect = document.getElementById("unit-select");
        const landSelect = document.getElementById("land-select");

        const allocationTypeInput = document.getElementById("allocation_type");
        const targetInput = document.getElementById("target_id");

        // Building to Units mapping
        const buildingsData = @json(
            $buildings->mapWithKeys(function ($building) {
                return [
                    $building->id => $building->units->map(function ($unit) {
                        return [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'is_rented' => $unit->is_rented
                        ];
                    })->toArray()
                ];
            })->toArray()
        );

        function updateToggleState() {
            const isLand = toggle.checked;

            // Section toggling
            buildingSection.style.display = isLand ? 'none' : 'flex';
            landSection.style.display = isLand ? 'block' : 'none';

            // Field requirements
            buildingSelect.required = !isLand;
            unitSelect.required = !isLand;
            landSelect.required = isLand;

            // Enable/Disable
            buildingSelect.disabled = isLand;
            unitSelect.disabled = isLand;
            landSelect.disabled = !isLand;

            // Set type
            allocationTypeInput.value = isLand ? 'land' : 'unit';

            // Reset target_id
            updateTargetField();
        }

        function updateUnitDropdown(buildingId) {
            unitSelect.innerHTML = '<option disabled selected>Select Unit</option>';
            const units = buildingsData[buildingId] || [];

            units.forEach(unit => {
                const option = document.createElement("option");
                option.value = unit.id;
                option.textContent = unit.name + (unit.is_rented ? ' (Rented)' : '');
                unitSelect.appendChild(option);
            });
        }

        function updateTargetField() {
            if (toggle.checked) {
                targetInput.value = landSelect.value;
            } else if (unitSelect.value) {
                targetInput.value = unitSelect.value;
            } else {
                targetInput.value = buildingSelect.value;
            }
        }

        // Event bindings
        toggle.addEventListener("change", updateToggleState);
        buildingSelect.addEventListener("change", function () {
            updateUnitDropdown(this.value);
            updateTargetField();
        });
        unitSelect.addEventListener("change", updateTargetField);
        landSelect.addEventListener("change", updateTargetField);

        // Initial load
        updateToggleState();
    });
</script>
@endsection
