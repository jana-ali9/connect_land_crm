<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials/title-meta', ['title' => $title])

    @yield('css')

    @include('layouts.partials/head-css')
</head>

<body>

    @yield('content')

    @include('layouts.partials/vendor-scripts')

    @vite(['resources/js/app.js'])

    @yield('script')


</body>

</html>