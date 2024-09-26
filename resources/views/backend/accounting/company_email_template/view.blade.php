@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('View Email Template') }}</h4>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td>{{ _lang('Related To') }}</td>
                        <td>{{ ucwords($companyemailtemplate->related_to) }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Name') }}</td>
                        <td>{{ $companyemailtemplate->name }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Subject') }}</td>
                        <td>{{ $companyemailtemplate->subject }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Body') }}</td>
                        <td>{!! xss_clean($companyemailtemplate->body) !!}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection