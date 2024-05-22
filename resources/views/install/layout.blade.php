<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- CSRF Token -->
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>Installation</title>
		<!-- Google font -->
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

		<!-- Bootstrap -->
		<link type="text/css" rel="stylesheet" href="{{ asset('public/install_asset/css/bootstrap.min.css') }}"/>
		
		<link type="text/css" rel="stylesheet" href="{{ asset('public/install_asset/css/select2.css') }}"/>

		<!-- Custom stlylesheet -->
		<link type="text/css" rel="stylesheet" href="{{ asset('public/install_asset/css/style.css') }}"/>

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

    </head>
	<body>
		
		<div class="container">	    
			<div class="row">
				<div class="col-md-6 offset-md-3">
					<div class="install-container">
						@yield('content')
					</div>
				</div>
			</div>
		</div>

		<!-- jQuery Plugins -->
		<script type="text/javascript" src="{{ asset('public/install_asset/js/jquery-3.6.0.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('public/install_asset/js/bootstrap.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('public/install_asset/js/select2.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('public/install_asset/js/scripts.js') }}"></script>
		@yield('js-script')		
	</body>
</html>