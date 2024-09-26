@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Add Company') }}</h4>
            </div>

            <div class="card-body">
                <div class="col-md-6">
                    {{-- <form method="post" class="validate" autocomplete="off" action="{{ route('companies.store') }}"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="row">
                        </div>
                    </form> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection