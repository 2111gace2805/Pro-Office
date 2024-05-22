<table class="table table-bordered">
    <tr>
        <td>Nombre de caja</td>
        <td>{{ $cash->cash_name }}</td>
    </tr>
    <tr>
        <td>Monto de apertura</td>
        <td>{{ currency() }} {{ $cash->cash_value }}</td>
    </tr>
    <tr>
        <td>Sucursal</td>
        <td>{{ $cash->company->company_name }}</td>
    </tr>
    <tr>
        <td>Estado</td>
        <td>{{ ( $cash->cash_status == 'Opened') ? 'Abierta' : 'Cerrada'  }}</td>
    </tr>
</table>
