@extends('layouts.vertical', ['subTitle' => 'Edit Unit', 'title' => 'Units'])
@php($Name = 'units')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Units', 'subTitle' => 'Edit Unit'])

    <div class="card-body">
        @if (auth()->user()->hasPermission("update $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Unit</h5>
                </div>

                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.update", $unit->id) }}" enctype="multipart/form-data">

                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div>
                            <div class="mb-3">
                                <label for="name" class="form-label">name</label>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="{{ $unit->name }}">
                            </div>

                            <div class="mb-3">
                                <label for="start_price" class="form-label">Purchase price</label>
                                <input type="number" id="start_price" name="start_price" class="form-control"
                                    min="0" step="any" value="{{ $unit->start_price ?? 0 }}" value="0">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">description</label>
                                <textarea class="form-control" id="description" name="description" rows="5">{{ $unit->description }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="area" class="form-label">area</label>
                                <input type="number" id="area" name="area" class="form-control" min="0"
                                    step="any" required value="{{ $unit->area }}">
                            </div>

                            <div class="mb-3">
                                <label for="example-select" class="form-label">Building</label>
                                <select class="form-select" id="example-select" name="building_id" required>
                                    <option disabled selected>select one of Building</option>
                                    @foreach ($allbuildings as $building)
                                        <option @selected($building->id == $unit->building_id) value="{{ $building->id }}">
                                            {{ $building->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" id="image" name="image" class="form-control"
                                    value="{{ $unit->image }}">
                            </div>
                            <div class="col-12" style="text-align: end">
                                <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="card-body">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            <span>Expense</span>
                            <button type="button" class="btn btn-sm btn-soft-primary add-expense-btn"
                                data-id="{{ $unit->id ?? $building->id }}"
                                data-type="{{ isset($unit) ? 'unit' : 'building' }}" data-bs-toggle="modal"
                                data-bs-target="#addExpenseModal">
                                Add Expense
                            </button>
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-borderless table-centered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">amount</th>
                                    <th scope="col">description</th>
                                    <th scope="col"></th>
                                    <th class="border-0 py-2" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($unit->start_price)
                                    <tr>
                                        <td>Purchase price</td>
                                        <td>{{ $unit->start_price }}
                                        </td>
                                        <td>
                                            <p class="card-text">price to pay</p>
                                        </td>
                                        <td> </td>
                                        <td> </td>
                                    </tr>
                                @endif

                                @if ($unit->paidInvoices->sum('amount_paid') > 0)
                                    <tr>
                                        <td>Bulk rents</td>
                                        <td>{{ $unit->paidInvoices->sum('amount_paid') }}
                                        </td>
                                        <td>
                                            <p class="card-text">All combined rents for this unit</p>
                                        </td>
                                        <td> </td>
                                        <td> </td>
                                    </tr>
                                @endif
                                @foreach ($unit->allExpenses() ?? [] as $expense)
                                    <tr>
                                        <td>{{ $expense->expense_name }}</td>
                                        <td>{{ $expense->amount }}
                                        </td>
                                        <td>
                                            <p class="card-text">{{ Str::limit($expense->description, 120) }}</p>
                                        </td>
                                        <td>{{ $expense->allocation_type == 'unit' ? 'belongs To unit' : 'to building' }}
                                        </td>
                                        <td>
                                            @if ($expense->allocation_type == 'unit')
                                                @if (auth()->user()->hasPermission("delete $Name"))
                                                    <form id="delete-form-{{ $expense->id }}" style="display: initial"
                                                        method="POST"
                                                        action="{{ route('unit-expenses.destroy', $expense->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="#!" onclick="confirmDelete({{ $expense->id }})"
                                                            class="btn btn-sm btn-soft-danger"><i
                                                                class="bx bx-trash fs-16"></i></a>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($unit->end_price)
                                    <tr>
                                        <td>selling price</td>
                                        <td>{{ $unit->end_price }}
                                        </td>
                                        <td>
                                            <p class="card-text">price to selling</p>
                                        </td>
                                        <td> </td>
                                        <td> </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="col-sm-12">
                            <div class="float-end" style="padding-right: 20px">
                                @if ($unit->start_price != 0)
                                    <p><span class="fw-medium">start price :</span>

                                        <span class="float-end">$ {{ $unit->start_price }}</span>
                                    </p>
                                @endif
                                <p><span class="fw-medium">income :</span>

                                    <span class="float-end">$ {{ $unit->paidInvoices->sum('amount_paid') }}</span>
                                </p>
                                <p><span class="fw-medium">outcome :</span>
                                    <span class="float-end">$
                                        {{ $unit->allExpenses()->sum('amount') }}</span>
                                </p>
                                @if ($unit->end_price != 0)
                                    <p><span class="fw-medium">Sell price :</span>

                                        <span class="float-end">$ {{ $unit->end_price }}</span>
                                    </p>
                                @endif
                                <h3>Profits :
                                    ${{ $unit->paidInvoices->sum('amount_paid') + $unit->end_price - ($unit->allExpenses()->sum('amount') + $unit->start_price) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (!$unit->is_payed)


            <div class="card-body">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            <span>Unit sale</span>
                            <a type="button" class="btn btn-sm btn-soft-danger add-expense-btn"
                                href="{{ route('contracts.createbuilding', ['building_id' => $unit->building_id , 'unit_id'=>$unit->id]) }}">
                                Unit sale
                        </a>
                        </h5>
                    </div>

                </div>
            </div>
            @endif
        @endif

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

    </div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".show-image-btn").forEach(button => {
            button.addEventListener("click", function() {
                let imageUrl = this.getAttribute("data-image");

                Swal.fire({
                    imageUrl: imageUrl,
                    imageWidth: '100%', // تجعل الصورة تمتد بعرض كامل
                    imageHeight: 'auto', // تحافظ على الأبعاد الطبيعية للصورة
                    showConfirmButton: false, // إزالة زر التأكيد
                    backdrop: true, // يضيف تأثير التعتيم الخلفي
                    customClass: {
                        popup: 'full-image-alert' // تخصيص الـ alert لتكون كبيرة
                    }
                });
            });
        });
    });
</script>
