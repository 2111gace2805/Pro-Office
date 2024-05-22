@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-signin my-5 p-3">              
				<div class="card-body">
				    <img class="logo" src="{{ get_logo() }}">
					
					<h5 class="text-center py-4">{{ _lang('Create Your Account Now') }}</h4> 
					
                    <form method="POST" class="form-signup" autocomplete="off" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
							<div class="col-md-12">
                                <input id="name" type="text" placeholder="{{ _lang('Name') }}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">

                            <div class="col-md-12">
                                <input id="email" type="email" placeholder="{{ _lang('E-Mail Address') }}" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">

                            <div class="col-md-12">
                                <input id="password" type="password" placeholder="{{ _lang('Password') }}" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                           <div class="col-md-12">
                                <input id="password-confirm" type="password" class="form-control" placeholder="{{ _lang('Confirm Password') }}" name="password_confirmation" required>
                            </div>
                        </div>
						
						<div class="form-group row mt-5">
							<div class="col-md-12 text-center">
								<button type="submit" class="btn btn-primary btn-login">
								{{ _lang('Create My Account') }}
                                </button>
							</div>
						</div>
                        <div class="form-group row mt-5">
							<div class="col-md-12 text-center">
							   {{ _lang('Already Have An Account?') }} 
                               <a href="{{ route('login') }}">{{ _lang('Log In Here') }}</a>
							</div>
						</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
