@extends('layouts.vertical', ['subTitle' => 'Service', 'title' => 'Service'])
@php($Name = 'services')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Service', 'subTitle' => 'show Service'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Service</h5>
                </div>

                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.store") }}" enctype="multipart/form-data">

                    @csrf
                    <div class="card-body">
                        <div>

                            <div class="mb-3">
                                <label for="name" class="form-label">name</label>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="{{ old('name') }}">
                            </div>

                            <div class="mb-3">
                                <label for="default_price" class="form-label">default price</label>
                                <input type="number" id="default_price" name="default_price" class="form-control"
                                    min="0" step="any" required value="{{ old('default_price') }}">
                            </div>

                            <div class="mb-3">
                                <label for="example-select" class="form-label">service or feature</label>
                                <select class="form-select" id="example-select" name="type">
                                    <option disabled selected>select one </option>
                                    <option @selected('service' == old('type')) value="service">service</option>
                                    <option @selected('feature' == old('type')) value="feature">feature</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">description</label>
                                <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                            </div>

                            <br>
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
<script>
    // Dropzone
    var dropzonePreviewNode = document.querySelector("#dropzone-preview-list");
    dropzonePreviewNode.id = "";
    if (dropzonePreviewNode) {
        var previewTemplate = dropzonePreviewNode.parentNode.innerHTML;
        dropzonePreviewNode.parentNode.removeChild(dropzonePreviewNode);
        var dropzone = new Dropzone(".dropzone", {
            url: 'https://httpbin.org/post',
            method: "post",
            previewTemplate: previewTemplate,
            previewsContainer: "#dropzone-preview",
        });
    }
</script>
