@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-signin my-5">
                <div class="card-header text-center">{{ _lang('Verify Your Email Address') }}</div>

                <div class="card-body text-center">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ _lang('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ _lang('Before proceeding, please check your email for a verification link.') }}
                    {{ _lang('If you did not receive the email') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ _lang('click here to request another') }}</button>.
                    </form>
				</div>
            </div>
        </div>
    </div>
</div>
@endsection
