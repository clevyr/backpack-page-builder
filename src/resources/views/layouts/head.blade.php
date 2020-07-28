<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'Laravel') }} - @yield('title', $title)</title>

<link rel="dns-prefetch" href="//ajax.googleapis.com">
<link rel="preconnect" href="https://ajax.googleapis.com" crossorigin>
<link rel="dns-prefetch" href="//stackpath.bootstrapcdn.com">
<link rel="preconnect" href="https://stackpath.bootstrapcdn.com" crossorigin>

<!-- Fonts -->
<script>sessionStorage.fontsLoaded && document.documentElement.classList.add("wf-active");</script>
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
<script>
    if (!sessionStorage.fontsLoaded) {
        WebFont.load({
            typekit: {
                id: '',
            },
            active: function () {
                sessionStorage.fontsLoaded = true
            },
        });
    }
</script>

<!-- Styles -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script src="{{ asset('js/app.js') }}" defer></script>
