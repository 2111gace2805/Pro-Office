@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header row align-items-center">
                <div class="col-md-9">
                    <h4 class="header-title">{{ _lang('Product List') }}</h4>
                </div>
                <div class="col-md-3 text-right">
                    <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Product') }}"
                        href="{{route('products.create')}}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
                    <a class="btn btn-success btn-sm ml-auto" data-title="{{ _lang('Carga Excel') }}"
                        href="{{route('products.load')}}"><i class="ti-plus"></i> {{ _lang('Carga Excel') }}</a>
                </div>
            </div>

            <div class="card-header d-flex align-items-center">
                <div class="row w-100">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('products.index') }}" class="d-flex">
                            <label for="item_type" style="color: black" class="mr-2">{{ _lang('Filtar por tipo de Item') }}</label>
                            <div class="form-group mb-0">
                                
                                <select name="item_type" id="item_type" class="form-control">
                                    <option value="all" {{ request('item_type') == 'all' ? 'selected' : '' }}>{{ _lang('All') }}</option>
                                    <option value="product" {{ request('item_type') == 'product' ? 'selected' : '' }}>{{ _lang('Product') }}</option>
                                    <option value="service" {{ request('item_type') == 'service' ? 'selected' : '' }}>{{ _lang('Service') }}</option>
                                </select>
                            </div>
                            {{-- <button type="submit" class="btn btn-primary ml-2">{{ _lang('Aplicar filtro') }}</button> --}}
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <table class="table table-bordered" id="tabla-productos-new">
                    <thead>
                        <tr>
                        <th>{{ _lang('Product Code') }}</th>
                            <th>{{ _lang('Image') }}</th>
                            <th>{{ _lang('Product Name') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Product Cost') }}</th>
                            <th>{{ _lang('Product Price') }}</th>
                            {{-- <th>{{ _lang('Product Unit') }}</th> --}}
                            <th class="text-center">{{ _lang('Available Stock') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
{{-- 
                        @php $currency = currency(); @endphp
                        @foreach($items as $item)
                        <tr id="row_{{ $item->id }}">
                            <td class='product_code'>{{ $item->product->product_code }}</td>
                            <td class='text-center'>
                                <img src="{{asset($item->product->image)}}" alt="image" height="50">
                            </td>
                            <td class='item_id'>{{ $item->item_name }}</td>
                            <td class='description'>{{ $item->product->description }}</td>
                            <td class='product_cost'>{{ decimalPlace($item->product->product_cost, $currency) }}</td>
                            <td class='product_price'>{{ decimalPlace($item->product->product_price, $currency) }}</td>
                            <td class='product_unit'>{{ $item->product->product_unit }}</td>
                            <td class='product_stock text-center'>{{ $item->product_stock->where('company_id', session('company')->id)->first()->quantity??'' }}</td>
                            <td class="text-center">
                                <form action="{{action('ProductController@destroy', $item['id'])}}" method="post">
                                    <a href="{{action('ProductController@edit', $item['id'])}}"
                                        data-title="{{ _lang('Update Product') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    <a href="{{action('ProductController@show', $item['id'])}}"
                                        data-title="{{ _lang('View Product') }}"
                                        class="btn btn-primary btn-sm ajax-modal"><i class="ti-eye"></i></a>
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove"
                                        type="submit"><i class="ti-trash"></i></button>
                                    @if(Auth::user()->user_type == 'admin')
                                        
                                        <a href="{{ route('stock.modal', ['id' => $item->id]) }}" data-title="{{ _lang('Stock') }}" class="btn btn-primary btn-sm ajax-modal">
                                            <i class="ti-settings"></i>
                                        </a>
                                    @endif
                                </form>
                                
                            </td>
                        </tr>
                        @endforeach --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection
@section('js-script')
<script src="{{ asset('public/backend/assets/js/datatables/products-table.js') }}"></script>
@endsection