<table class="table table-bordered">
    <tr>
        <td>{{ _lang('Trans Date') }}</td>
        <td>{{ date("d M, Y",strtotime($transaction->trans_date)) }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Account') }}</td>
        <td>{{ $transaction->account->account_title }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Income Type') }}</td>
        <td>{{ $transaction->income_type->name }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Amount') }}</td>
        <td>{{ currency()." ".$transaction->amount }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Payer') }}</td>
        <td>{{ isset($transaction->payer->name) ? $transaction->payer->name : '' }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Payment Method') }}</td>
        <td>{{ $transaction->payment_method->name }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Reference') }}</td>
        <td>{{ $transaction->reference }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Note') }}</td>
        <td>{{ $transaction->note }}</td>
    </tr>
</table>