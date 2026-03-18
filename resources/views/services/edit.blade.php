@extends('layouts.vertical', ['subTitle' => 'Service', 'title' => 'Service'])
@php($Name = 'services')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Service', 'subTitle' => 'show Service'])

    <div class="card-body">
        @if (auth()->user()->hasPermission("update $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Service</h5>
                </div>

                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.update", $service->id) }}" enctype="multipart/form-data">

                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div>
                            <div class="mb-3">
                                <label for="name" class="form-label">name</label>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="{{ $service->name }}">
                            </div>

                            <div class="mb-3">
                                <label for="default_price" class="form-label">default price</label>
                                <input type="number" id="default_price" name="default_price" class="form-control"  min="0" step="any" required
                                    value="{{ $service->default_price }}">
                            </div>

                            <div class="mb-3">
                                <label for="example-select" class="form-label">service or feature</label>
                                <select class="form-select" id="example-select" name="type">
                                    <option disabled selected>select one </option>
                                    <option @selected('service' == $service->type ) value="service">service</option>
                                    <option @selected('feature' == $service->type ) value="feature">feature</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">description</label>
                                <textarea class="form-control" id="description" name="description" rows="5">{{ $service->description }}</textarea>
                            </div>

                            <div class="col-12" style="text-align: end">
                                <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        @endif
    </div>
@endsection
