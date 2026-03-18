@extends('layouts.vertical', ['subTitle' => 'Client', 'title' => 'Client'])
@php($Name = 'clients')
@section('content')
    @include('layouts.partials/page-title', ['title' => 'Client', 'subTitle' => 'show clients'])

    <div class="card-body">
        @if (auth()->user()->hasPermission("update $Name"))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit clients</h5>
                </div>

                @include('layouts.partials.massages')
                <form method="POST" action="{{ route("$Name.update", $client->id) }}" enctype="multipart/form-data">

                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div>
                            <div class="mb-3">
                                <label for="name" class="form-label">name</label>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="{{ $client->name }}">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">phone</label>
                                <input type="number" id="phone" name="phone" class="form-control" required
                                    value="{{ $client->phone }}">
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
