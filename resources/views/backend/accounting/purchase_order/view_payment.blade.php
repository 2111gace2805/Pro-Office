@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Payment History') }}</h4>
            </div>
            <div class="card-body">
                @php $currency = currency() @endphp

                <!--Payment History table -->
                <div class="table-responsive">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Account') }}</th>
                                <th>{{ _lang('Expense Type') }}</th>
                                <th class="text-right">{{ _lang('Amount') }}</th>
                                <th>{{ _lang('Payment Method') }}</th>
                                <th>{{ _lang('Reference') }}</th>
                                <th>{{ _lang('Note') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr id="transaction-{{ $transaction->id }}">
                                <td>{{ $transaction->trans_date }}</td>
                                <td>{{ $transaction->account->account_title }}</td>
                                <td>{{ $transaction->expense_type->name }}</td>
                                <td class="text-right">{{ decimalPlace($transaction->amount, $currency) }}</td>
                                <td>{{ $transaction->payment_method->name }}</td>
                                <td>{{ $transaction->reference }}</td>
                                <td>{{ $transaction->note }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!--End Payment History table -->
            </div>
        </div>
    </div>
</div>
@endsection