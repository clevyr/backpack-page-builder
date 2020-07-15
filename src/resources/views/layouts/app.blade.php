<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body>
@include('layouts.navigation.navbar')

<main id="app">
    @yield('content')
</main>

@include('layouts.footer.footer')
</body>
</html>
