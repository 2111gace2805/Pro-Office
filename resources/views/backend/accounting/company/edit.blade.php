@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Update Category') }}</h4>
            </div>

            <div class="card-body">
                <div class="col-md-6">
                    {{-- <form method="post" class="validate" autocomplete="off"
                        action="{{ action('CategoryController@update', $id) }}" enctype="multipart/form-data">
                    </form> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection