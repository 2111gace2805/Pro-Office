@php $currency = currency() @endphp
<!--payment History table -->
<div class="table-responsive">
    <table id="order-table" class="table table-bordered">
        <thead>
            <tr>
                <th>{{ _lang('Date') }}</th>
                <th>{{ _lang('Account') }}</th>
                <th>{{ _lang('Income Type') }}</th>
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
                <td>{{ $transaction->income_type->name }}</td>
                <td class="text-right">{{ decimalPlace($transaction->amount, $currency) }}</td>
                <td>{{ $transaction->payment_method->name }}</td>
                <td>{{ $transaction->reference }}</td>
                <td>{{ $transaction->note }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!--End Order table -->