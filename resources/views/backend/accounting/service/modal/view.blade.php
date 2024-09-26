<table class="table table-bordered">
    <tr>
        <td>{{ _lang('Item Id') }}</td>
        <td>{{ $item->id }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Service Name') }}</td>
        <td>{{ $item->item_name }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Service Cost') }}</td>
        <td>{{ decimalPlace($item->service->cost, currency()) }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Description') }}</td>
        <td>{{ $item->service->description }}</td>
    </tr>
</table>