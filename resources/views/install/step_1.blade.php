@extends('install.layout')

@section('content')
<div class="card">
    <div class="card-header text-center">
        <h4 class="header-title m-0 p-0">Check Requirements</h4>
    </div>
    <div class="card-body">
        @if(empty($requirements))
        <div class="text-center">
            <h6>Your Server is ready for installation.</h6>
            <a href="{{ url('install/database') }}" class="btn btn-install">Next</a>
        </div>
        @else
        @foreach($requirements as $r)
        <p class="required"><i class="glyphicon glyphicon-info-sign"></i> {{ $r }}</p>
        @endforeach
        @endif
    </div>
</div>
@endsection