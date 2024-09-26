
<div class="col-xl-6 col-md-6 col-sm-12 m-auto">
    <div class="card shadow-none" style="border:1px solid #e0e0e0;">
        <div class="card-body">
                @if ($item->product->image)
                    <div class="text-center align-center" style="border-radius:10px 10px 0 0; min-height: 150px; border:1px solid #e0e0e0;">
                        <img src="{{ url($item->product->image->url()) }}" alt="no image" height="150" id="imagen-producto">
                    </div>
                @endif
        </div>
    </div>
</div>

<table class="table table-bordered">
    {{-- <tr>
        <td>{{ _lang('Item ID') }}</td>
        <td>{{ $item->id }}</td>
    </tr> --}}
    <tr>
        <td>{{ _lang('Product Code') }}</td>
        <td>{{ $item->product->product_code }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Product Name') }}</td>
        <td>{{ $item->item_name }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Description') }}</td>
        <td>{{ $item->product->description }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Weight') }}</td>
        <td>{{ $item->product->weight }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Supplier') }}</td>
        <td>{{ $item->product->supplier->supplier_name }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Product Cost') }}</td>
        <td>{{ currency()." ".$item->product->product_cost }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Product Price') }}</td>
        <td>{{ currency()." ".$item->product->product_price }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Product Unit') }}</td>
        <td>{{ $item->product->product_unit }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Saldo') }}</td>
        <td>{{ $item->product->saldo }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Available Quantity') }}</td>
        <td>
                            <ul>
                            @foreach ($item->product_stock as $stock)
                                <li><b>{{$stock->company->company_name}}</b>: {{ ($stock->quantity ?? 0).' '.$item->product_unit }}</li>
                            @endforeach
                            </ul>
                        </td>
    </tr>
    <tr>
        <td>{{ _lang('Description') }}</td>
        <td>{{ $item->product->description }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Warranty') }}</td>
        <td>{{ $item->product->warranty_value }} {{ _lang($item->product->warranty_type) }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Note') }}</td>
        <td>{{ $item->product->note }}</td>
    </tr>
    <tr>
        <td>{{ _lang('Comment') }}</td>
        <td>{{ $item->product->comment }}</td>
    </tr>
</table>