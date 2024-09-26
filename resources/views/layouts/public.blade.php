<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ get_option('site_title', config('app.name')) }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v3.0.6/css/line.css">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('public/backend/assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('public/backend/assets/css/invoice.css') }}">
</head>

<body>
    <div id="app">
        <div class="container">
            <main class="py-4">
                @if(Session::has('success'))
                <div class="alert alert-success" role="alert">
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(Session::has('error'))
                <div class="alert alert-danger" role="alert">
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('public/backend/assets/js/jquery-3.6.0.min.js') }}"></script>
    @yield('js-script')
</body>

</html>