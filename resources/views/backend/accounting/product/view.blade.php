@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('View Product') }}</h4>
            </div>

            <div class="card-body">
                <div class="col-xl-6 col-md-6 col-sm-12 m-auto">
                    <div class="card shadow-none" style="border:1px solid #e0e0e0;">
                        <div class="card-body">
                            <div class="text-center align-center" style="border-radius:10px 10px 0 0; min-height: 150px; border:1px solid #e0e0e0;">
                                <img src="{{url($item->image)}}" alt="no image" height="150" id="imagen-producto">
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered">
                    <tr>
                        <td>{{ _lang('Item ID') }}</td>
                        <td>{{ $item->id }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Product Name') }}</td>
                        <td>{{ $item->item_name }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Supplier') }}</td>
                        <td>{{ $item->supplier_name }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Product Cost') }}</td>
                        <td>{{ currency()." ".$item->product_cost }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Product Price') }}</td>
                        <td>{{ currency()." ".$item->product_price }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Product Unit') }}</td>
                        <td>{{ $item->product_unit }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Available Quantity') }}</td>
                        <td>
                            <ul>
                            @foreach ($item->product_stock as $stock)
                                <li>{{$stock->company->company_name}} {{ ($stock->quantity ?? 0).' '.$item->product_unit }}</li>
                            @endforeach
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Description') }}</td>
                        <td>{{ $item->description }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection