@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Account Details') }}</h4>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td>{{ _lang('Account Title') }}</td>
                        <td>{{ $account->account_title }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Opening Date') }}</td>
                        <td>{{ $account->openingDate() }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Account Number') }}</td>
                        <td>{{ $account->account_number }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Opening Balance') }}</td>
                        <td>{{ decimalPlace($account->opening_balance, currency()) }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Note') }}</td>
                        <td>{{ $account->note }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection