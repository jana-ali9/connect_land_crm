@extends('layouts.auth', ['title' => 'Coming Soon'])

@section('content')


<div class="col-lg-8">
    <div class="card auth-card text-center">
        <div class="card-body">

            <div class="mx-auto my-5 text-center">
                <a href="{{ route('any', 'index')}}">
                    <img src="/images/logo-sm.png" height="32" alt="logo sm" class="me-1">
                    <img src="/images/logo-dark.png" alt="logo-dark" height="24" />
                </a>
            </div>

            <h2 class="fw-bold text-uppercase">We Are Launching Soon...</h2>
            <p class="lead mt-3 w-75 mx-auto pb-4 fst-italic">Exciting news is on the horizon! We're thrilled to announce that something incredible is coming your way very soon.</p>

            <div class="row my-5">
                <div class="col">
                    <h3 id="days" class="fw-bold fs-60">00</h3>
                    <p class="text-uppercase fw-semibold">Days</p>
                </div>
                <div class="col">
                    <h3 id="hours" class="fw-bold fs-60">00</h3>
                    <p class="text-uppercase fw-semibold">Hours</p>
                </div>
                <div class="col">
                    <h3 id="minutes" class="fw-bold fs-60">00</h3>
                    <p class="text-uppercase fw-semibold">Minutes</p>
                </div>
                <div class="col">
                    <h3 id="seconds" class="fw-bold fs-60">00</h3>
                    <p class="text-uppercase fw-semibold">Seconds</p>
                </div>
            </div>

        </div>
    </div>

    @endsection

    @section('script')
    @vite(['resources/js/pages/coming-soon.js'])
    @endsection