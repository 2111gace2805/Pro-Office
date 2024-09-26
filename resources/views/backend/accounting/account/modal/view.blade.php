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