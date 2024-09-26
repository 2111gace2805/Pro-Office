<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ get_option('site_title', config('app.name')) }}</title>

    <!-- Scripts -->
    <script src="{{ asset('public/auth/js/app.js') }}" defer></script>

    <!-- Google font -->
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('public/auth/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/auth/css/app.css') }}" rel="stylesheet">
</head>
<body style="padding: 0; margin: 0; background-image: url(public/backend/images/bg-image.jpg); background-size: cover; height: 100vh;">
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
	
	@yield('js-script')
</body>
</html>
