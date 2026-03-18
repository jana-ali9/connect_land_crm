@extends('layouts.vertical', ['subTitle' => 'Add Units', 'title' => 'Units'])

@php($Name = 'units')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Units', 'subTitle' => 'Add Units'])
    <div class="card-body">

        @if (auth()->user()->hasPermission("create $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Units</h5>
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
                                <label for="start_price" class="form-label">Purchase price</label>
                                <input type="number" id="start_price" name="start_price" class="form-control"
                                    min="0" step="any" value="{{ old('start_price') ?? 0 }}" value="0">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">description</label>
                                <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="area" class="form-label">area</label>
                                <input type="number" id="area" name="area" class="form-control"  min="0" step="any" required
                                    value="{{ old('area') }}">
                            </div>

                            <div class="mb-3">
                                <label for="example-select" class="form-label">Building</label>
                                <select class="form-select" id="example-select" name="building_id" required>
                                    <option disabled selected>select one of Building</option>
                                    @foreach ($allbuildings as $building)
                                        <option @selected($building->id == old('building_id')) value="{{ $building->id }}">{{ $building->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" id="image" name="image" class="form-control" required>
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
