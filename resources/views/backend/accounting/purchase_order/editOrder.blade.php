@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Update Purchase Order') }}</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('purchase_orders.updateOrder', $id) }}">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    {{-- <input name="_method" type="hidden" value="PATCH"> --}}

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Date') }}</label>
                                <input type="text" class="form-control datepicker" name="order_date"
                                    value="{{ $purchase->getRawOriginal('order_date') }}" readOnly="true" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <a href="{{ route('suppliers.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Supplier') }}" class="ajax-modal-2 select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Supplier') }}</label>
                                <select class="form-control select2-ajax" name="supplier_id" data-value="id"
                                    data-display="supplier_name" data-table="suppliers"  required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("suppliers","id","supplier_name",$purchase->supplier_id,array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Status') }}</label>
                                <select class="form-control select2" name="order_status" required>
                                    <option value="1" {{ $purchase->order_status == '1' ? 'selected' : '' }}>
                                        {{ _lang('Ordered') }}</option>
                                    <option value="2" {{ $purchase->order_status == '2' ? 'selected' : '' }}>
                                        {{ _lang('Pending') }}</option>
                                    <option value="3" {{ $purchase->order_status == '3' ? 'selected' : '' }}>
                                        {{ _lang('Received') }}</option>
                                    <option value="4" {{ $purchase->order_status == '4' ? 'selected' : '' }}>
                                        {{ _lang('Canceled') }}</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group select-product-container">
                                <a href="{{ route('products.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Product') }}</label>
                                <select class="form-control select2 product" name="product" id="product">
                                    <option value="" disabled selected>{{ _lang('Select Product') }}</option>
                                    @foreach ($productos as $item)
                                        <option value="{{ $item->id }}">{{ $item->category?->nombre }} {{ $item->sub_category?->subc_name }} - {{ $item->product_stock->where('company_id', session('company')->id)->first()->quantity??'' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Payment Status') }}</label>
                                <select class="form-control select2" name="payment_status" required>
                                    <option value="0" {{ $purchase->payment_status == '0' ? 'selected' : '' }}>
                                        {{ _lang('Due') }}</option>
                                    <option value="1" {{ $purchase->payment_status == '1' ? 'selected' : '' }}>
                                        {{ _lang('Paid') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Attachemnt') }}</label>
                                <input type="file" class="form-control trickycode-file"
                                    data-value="{{ $purchase->attachemnt }}" name="attachemnt">
                            </div>
                        </div>

                        @php $currency = currency(); @endphp
                        @php $taxes = App\Tax::where("company_id",company_id())->get(); @endphp

                        <!--Order table -->
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="order-table" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Name') }}</th>
                                            <th>{{ _lang('Description') }}</th>
                                            <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                                            <th class="text-right">{{ _lang('Unit Cost') }}</th>
                                            <th class="text-right wp-100">{{ _lang('Discount') }}</th>
                                            <th class="text-right">{{ _lang('Tax') }}</th>
                                            <th class="text-right">{{ _lang('Sub Total') }}</th>
                                            <th class="text-center">{{ _lang('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->purchase_items as $item)
                                        <tr id="product-{{ $item->product_id }}">
                                            <td>
                                                <b>{{ $item->item->item_name }}</b><br>
                                            </td>
                                            <td class="description"><input type="text" name="product_description[]"
                                                    class="form-control input-description"
                                                    value="{{ $item->description }}"></td>
                                            <td class="text-center quantity"><input type="number" name="quantity[]"
                                                    min="1" class="form-control input-quantity text-center"
                                                    value="{{ $item->quantity }}"></td>
                                            <td class="text-right unit-cost"><input type="text" name="unit_cost[]"
                                                    class="form-control input-unit-cost text-right"
                                                    value="{{ $item->unit_cost }}"></td>
                                            <td class="text-right discount"><input type="text" name="discount[]"
                                                    class="form-control input-discount text-right"
                                                    value="{{ $item->discount }}"></td>
                                            <td class="text-right tax">
                                                <select class="form-control auto-multiple-select selectpicker input-tax"
                                                    name="tax[{{ $item->product_id }}][]"
                                                    title="{{ _lang('Select TAX') }}"
                                                    data-selected="{{ $item->taxes->pluck('tax_id') }}" multiple="true">
                                                    @foreach($taxes as $tax)
                                                    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}"
                                                        data-tax-rate="{{ $tax->rate }}">{{ $tax->tax_name }} -
                                                        {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-right sub-total"><input type="text" name="sub_total[]"
                                                    class="form-control input-sub-total text-right"
                                                    value="{{ $item->sub_total }}" readonly></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-xs remove-product"><i
                                                        class='ti-trash'></i></button>
                                            </td>
                                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="product_tax[]" class="input-product-tax"
                                                value="{{ $item->tax_amount }}">
                                            <input type="hidden" name="product_price[]" class="input-product-price" value="{{$item->item->product->product_price}}">
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="tfoot active">
                                        <tr>
                                            <th>{{ _lang('Total') }}</th>
                                            <th></th>
                                            <th class="text-center" id="total-qty">0</th>
                                            <th></th>
                                            <th class="text-right" id="total-discount">0.00</th>
                                            <th class="text-right" id="total-tax">0.00</th>
                                            <th class="text-right" id="total">0.00</th>
                                            <th class="text-center"></th>
                                            <input type="hidden" name="product_total" id="product_total" value="0">
                                            <input type="hidden" name="tax_total" id="tax_total" value="0">
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!--End Order table -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Discount')." ".$currency }}</label>
                                <input type="text" class="form-control float-field" name="order_discount"
                                    value="{{ $purchase->order_discount }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Shipping Cost')." ".$currency }}</label>
                                <input type="text" class="form-control float-field" name="shipping_cost"
                                    value="{{ $purchase->shipping_cost }}">
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Note') }}</label>
                                <textarea class="form-control" name="note">{{ old('note', $purchase->note) }}</textarea>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<select class="form-control d-none" id="tax-selector">
    @foreach($taxes as $tax)
    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">
        {{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
    @endforeach
</select>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/purchase_order.js?v=1.0') }}"></script>
@endsection