@extends('layouts.vertical', ['title' => 'Invoice Details'])


@section('content')
    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg p-4" style="max-width: 900px; width: 90%;">
            <div class="card-body">
                <!-- Logo & title -->
                <div class="clearfix">
                    <div class="float-sm-end text-end">
                        <div class="auth-logo">
                            <img class="logo-dark me-1" src="/images/logo-dark-full.png" alt="logo-dark" height="24" />
                            <img class="logo-light me-1" src="/images/logo-light-full.png" alt="logo-dark" height="24" />
                        </div>
                        <address class="mt-3">
                            Connect Media<br>
                            lebanon - Dubai<br>
                            <abbr title="Phone">P:</abbr> +971551224221
                        </address>
                    </div>
                    <div class="float-sm-start">
                        <h5 class="card-title mb-2">Invoice: #{{ $invoice->id }}</h5>
                    </div>
                </div>

                @php
                    $price = $invoice->type == 'service' ? $invoice->services_cost : $invoice->amount_due;
                @endphp
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="fw-normal text-muted">Customer</h6>
                        <h6 class="fs-16">{{ $invoice->client->name }}</h6>
                      @if ($invoice->contract->property_type === 'land' && $invoice->contract->land)
    <address>
        {{ $invoice->contract->land->name }}<br>
        {{ $invoice->contract->land->description ?? 'No description' }}<br>
        <abbr title="Phone">P:</abbr> {{ $invoice->client->phone }}
    </address>
@elseif ($invoice->contract->property_type === 'building' && $invoice->contract->unit && $invoice->contract->unit->building)
    <address>
        {{ $invoice->contract->unit->building->name }}<br>
        {{ $invoice->contract->unit->building->address }}<br>
        <abbr title="Phone">P:</abbr> {{ $invoice->client->phone }}
    </address>
@else
    <address>
        Unknown Property<br>
        <abbr title="Phone">P:</abbr> {{ $invoice->client->phone }}
    </address>
@endif

                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive table-borderless text-nowrap mt-3 table-centered">
                            <table class="table mb-0">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="border-0 py-2">invoice_date</th>
                                        <th class="border-0 py-2">amount_due</th>
                                        <th class="text-end border-0 py-2">amount_paid</th>
                                        <th class="border-0 text-end py-2">status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $invoice->invoice_date }}</td>
                                        <td>{{ $price }}</td>
                                        <td class="text-end">{{ $invoice->amount_paid }}</td>
                                        <td class="text-end"><span
                                                class='badge status-badge badge-soft-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'pending' ? 'secondary' : 'danger') }}'>
                                                {{ $invoice->status }}
                                            </span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-7">
                        <h6 class="text-muted">Notes:</h6>
                        <small class="text-muted">
                            All accounts are to be paid within 7 days from receipt of
                            invoice. To be paid by cheque or credit card or direct payment
                            online. If account is not paid within 7 days the credits details
                            supplied as confirmation of work undertaken will be charged the
                            agreed quoted fee noted above.
                        </small>
                    </div>
                    <div class="col-sm-5">
                        <div class="text-end">

                            <p><span class="fw-medium">amount :</span>

                                <span class="float-end">{{ $price }}</span>
                            </p>
                            <p><span class="fw-medium">Paid :</span>
                                <span class="float-end"> {{ $invoice->amount_paid }}</span>
                            </p>
                            <h3>{{ $invoice->status == 'paid' ? $price : $price - $invoice->amount_paid }} USD</h3>
                        </div>
                    </div>
                </div>

                <div class="mt-5 mb-1 text-end">
                    <a href="javascript:window.print()" class="btn btn-primary no-print">Print</a>
                </div>

            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> &copy; Crafted by <iconify-icon icon="solar:hearts-bold-duotone"
                            class="fs-18 align-middle text-danger"></iconify-icon> <a href=""
                            class="fw-bold footer-text" target="_blank">Connect Media</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
