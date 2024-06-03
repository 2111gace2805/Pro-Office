
<table class="table table-bordered">
    <tr>
        <td>{{ _lang('Code') }}</td>
        <td>{{ $kit->code }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Name') }}</td>
        <td>{{ $kit->name }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Price') }}</td>
        <td>${{ $kit->amount }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Available Quantity') }}</td>
        <td>
            <ul>
                @foreach( $productDetails as $detail )
                    <li><b>{{$detail['product']}}</b>: Cantidad: {{ $detail['quantity'] }}</li>
                @endforeach
            </ul>
        </td>
    </tr>
</table>