@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('View Contact Group') }}</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td>{{ _lang('Group') }}</td>
                        <td>{{ $contactgroup->name }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Note') }}</td>
                        <td>{{ $contactgroup->note }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection