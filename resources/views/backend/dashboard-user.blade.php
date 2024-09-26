@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('public/backend/plugins/chartJs/Chart.min.css') }}">

@php $currency = currency(); @endphp
@php $permissions = permission_list(); @endphp

<div class="row">
    @if (in_array('dashboard.current_day_income',$permissions))
    <div class="col-xl-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ _lang('Current Day Income') }}</h5>
                <h6 class="pt-1"><b>{{ decimalPlace($current_day_income, $currency) }}</b></h6>
            </div>
        </div>
    </div>
    @endif

    @if (in_array('dashboard.current_day_expense',$permissions))
    <div class="col-xl-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ _lang('Current Day Expense') }}</h5>
                <h6 class="pt-1"><b>{{ decimalPlace($current_day_expense, $currency) }}</b></h6>
            </div>
        </div>
    </div>
    @endif

    @if (in_array('dashboard.current_month_income',$permissions))
    <div class="col-xl-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ _lang('Monthly Income') }}</h5>
                <h6 class="pt-1"><b>{{ decimalPlace($current_month_income, $currency) }}</b></h6>
            </div>
        </div>
    </div>
    @endif

    @if (in_array('dashboard.current_month_expense',$permissions))
    <div class="col-xl-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ _lang('Monthly Expense') }}</h5>
                <h6 class="pt-1"><b>{{ decimalPlace($current_month_expense, $currency) }}</b></h6>
            </div>
        </div>
    </div>
    @endif
</div>

@if (in_array('dashboard.yearly_income_vs_expense',$permissions))
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Income vs Expense of')." ".date('Y') }}</h4>
            </div>
            <div class="card-body">
                <canvas id="yearly_income_expense" width="100%" height="25"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    @if (in_array('dashboard.latest_income',$permissions))
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Last 5 Income') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Type') }}</th>
                                <th class="text-right">{{ _lang('Amount') }}</th>
                                <th class="text-center">{{ _lang('Details') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($latest_income as $transaction)
                            <tr id="row_{{ $transaction->id }}">
                                <td class='trans_date'>{{ $transaction->trans_date }}</td>
                                <td class='chart_id'>
                                    {{ isset($transaction->income_type->name) ? $transaction->income_type->name : _lang('Transfer') }}
                                </td>
                                <td class='amount text-right'>{{ decimalPlace($transaction->amount, $currency) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{action('IncomeController@show', $transaction['id'])}}"
                                        data-title="{{ _lang('View Income') }}"
                                        class="btn btn-light btn-sm ajax-modal">{{ _lang('View Details') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (in_array('dashboard.latest_expense',$permissions))
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Last 5 Expense') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Type') }}</th>
                                <th class="text-right">{{ _lang('Amount') }}</th>
                                <th class="text-center">{{ _lang('Details') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($latest_expense as $expense)
                            <tr id="row_{{ $expense->id }}">
                                <td class='trans_date'>{{ $expense->trans_date }}</td>
                                <td class='chart_id'>
                                    {{ isset($expense->expense_type->name) ? $expense->expense_type->name : _lang('Transfer') }}
                                </td>
                                <td class='amount text-right'>{{ decimalPlace($expense->amount, $currency) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{action('ExpenseController@show', $expense['id'])}}"
                                        data-title="{{ _lang('View Expense') }}"
                                        class="btn btn-light btn-sm ajax-modal">{{ _lang('View Details') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


<div class="row">
    @if (in_array('dashboard.monthly_income_vs_expense',$permissions))
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Income vs Expense of')." ".date('M, Y') }}</h4>
            </div>
            <div class="card-body">
                <canvas id="dn_income_expense" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if (in_array('dashboard.financial_account_balance',$permissions))
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Financial Account Balance') }}</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ _lang('A/C') }}</th>
                            <th>{{ _lang('A/C Number') }}</th>
                            <th class="text-right">{{ _lang('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(get_financial_balance() as $account)
                        <tr id="row_{{ $account->id }}">
                            <td class='account_title'>{{ $account->account_title }}</td>
                            <td class='account_number'>{{ $account->account_number }}</td>
                            <td class='opening_balance text-right'>{{ decimalPlace($account->balance, $currency) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/chartJs/Chart.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('public/backend/assets/js/dashboard.js') }}"></script>
@endsection