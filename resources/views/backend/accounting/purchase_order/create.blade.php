@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Create Purchase Order') }}</h4>
            </div>

            @php $currency = currency(); @endphp

            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('purchase_orders.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Date') }}</label>
                                <input type="text" class="form-control datepicker" name="order_date"
                                    value="{{ old('order_date') }}" readOnly="true" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <a href="{{ route('suppliers.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Supplier') }}" class="ajax-modal-2 select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Supplier') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="supplier_name"
                                    data-table="suppliers"  name="supplier_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Status') }}</label>
                                <select class="form-control select2" name="order_status" required>
                                    <option value="1">{{ _lang('Ordered') }}</option>
                                    <option value="2">{{ _lang('Pending') }}</option>
                                    <option value="3">{{ _lang('Received') }}</option>
                                    <option value="4">{{ _lang('Canceled') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Attachemnt') }}</label>
                                <input type="file" class="form-control trickycode-file" name="attachemnt">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group select-product-container">
                                <a href="{{ route('products.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Product') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="item_name"
                                    data-table="items" data-where="2" name="product" id="product" data-items="all">
                                    <option value="">{{ _lang('Select Product') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Discount')." ".$currency }}</label>
                                <input type="text" class="form-control float-field" name="order_discount"
                                    value="{{ old('order_discount',0) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Shipping Cost')." ".$currency }}</label>
                                <input type="text" class="form-control float-field" name="shipping_cost"
                                    value="{{ old('shipping_cost',0) }}">
                            </div>
                        </div>

                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Import number') }}</label>
                                <input type="text" class="form-control" name="import_number"
                                    value="{{ old('import_number','') }}" required>
                            </div>
                        </div> --}}

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

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Note') }}</label>
                                <textarea class="form-control" name="note">{{ old('note') }}</textarea>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<select class="form-control d-none" id="tax-selector">
    @foreach(App\Tax::where("company_id",company_id())->get() as $tax)
    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">
        {{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
    @endforeach
</select>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/purchase_order.js?v=1.0') }}"></script>
@endsection