@extends('layouts.vertical', ['subTitle' => 'Client', 'title' => 'Client'])
@php($Name = 'clients')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Client', 'subTitle' => 'show clients'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add clients</h5>
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
                                <label for="phone" class="form-label">phone</label>
                                <input type="number" id="phone" name="phone" class="form-control" required
                                    value="{{ old('phone') }}">
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
