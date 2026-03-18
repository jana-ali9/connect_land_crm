@extends('layouts.auth', ['title' => 'Sign In'])

@section('content')
    <div class="col-xl-5">
        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="" class="logo-dark">
                        <img src="/images/logo-sm.png" height="30" class="me-1" alt="logo sm">
                        <img src="/images/logo-dark.png" height="24" alt="logo dark">
                    </a>

                    <a href="" class="logo-light">
                        <img src="/images/logo-sm.png" height="30" class="me-1" alt="logo sm">
                        <img src="/images/logo-light.png" height="24" alt="logo light">
                    </a>
                </div>

                <h2 class="fw-bold text-center fs-18">Sign In</h2>
                <p class="text-muted text-center mt-1 mb-4">Enter your email address and password to access admin
                    panel.</p>

                <div class="px-4">

                    <form method="POST" action="{{ route('login') }}" class="mt-4">

                        @csrf

                        @include('layouts.partials.massages')

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                 placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">Password</label>
                                <a href="{{ route('second', ['auth', 'password']) }}"
                                    class="text-decoration-none small text-muted">Forgot password?</a>
                            </div>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember-me">
                            <label class="form-check-label" for="remember-me">Remember me</label>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-dark btn-lg fw-medium" type="submit">Sign In</button>
                        </div>
                    </form>
                </div> <!-- end col -->
            </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
@endsection
