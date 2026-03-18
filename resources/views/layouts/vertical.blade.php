<!DOCTYPE html>
<html lang="en" @yield('html') data-bs-theme="light" data-topbar-color="light" data-menu-color="dark"
    data-menu-size="default">

<head>
    @include('layouts.partials/title-meta', ['title' => $title])

    @yield('css')

    @include('layouts.partials/head-css')

    <style>
        @if (request()->is('invoice/*/view'))
            .wrapper .page-content {
                margin-left: 0 !important;
            }
        @endif
    </style>
</head>

<body @yield('body')>

    <div class="wrapper">

        @if (!request()->is('invoice/*/view'))
            @include('layouts.partials/menu')
        @endif

        <!-- ==================================================== -->
        <!-- Start Page Content here -->
        <!-- ==================================================== -->
        <div class="page-content">

            <!-- Start Content-->
            <div class="container-fluid">

                @yield('content')

            </div>

            @if (!request()->is('invoice/*/view'))
                @include('layouts.partials/footer')
            @endif

        </div>

    </div>

    @include('layouts.partials/right-sidebar')
    @include('layouts.partials/vendor-scripts')

    @vite(['resources/js/app.js', 'resources/js/layout.js'])

    @yield('scripts')

</body>

</html>
