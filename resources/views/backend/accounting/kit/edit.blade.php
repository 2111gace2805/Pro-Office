@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Update Kit') }}</h4>
            </div>

            <div class="card-body">
                <form method="post" class="validate" id="frmKits" autocomplete="off" action="{{ action('KitController@update', $id) }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Code') }}</label>
                                <input type="text" class="form-control" name="code"  value="{{ $kit->code }}" required>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="name" value="{{ $kit->name }}" required>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Price Kit') }}</label>
                                <input type="text" class="form-control float-field" name="amount" id="amount"  value="{{ $kit->amount }}" required>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group select-product-container">
                                <a href="{{ route('products.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Product') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="item_name"
                                    data-table="items" data-where="2" name="product" id="product" data-items="con_stock">
                                    <option value="">{{ _lang('Select Product') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <table id="order-table" class="table table-bordered table-sm" style="width:60%;"> 
                                <thead>
                                    <tr>
                                        <th style="width:80%">{{ _lang('Product') }}</th>
                                        <th class="text-center">{{ _lang('Quantity') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot class="tfoot active">
                                    <tr>
                                        <th>{{ _lang('Sumas') }}</th>
                                        <th class="text-center" id="total-qty">0</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="button" onclick="saveInvoice(event, true, '{{ $kit->id }}')" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                                    {{ _lang('Save Kit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/kits.js') }}"></script>
<script>

    @if( $products != null )

        let products = @json($products);

        $.each(products, function(index, value){
            $("#product").select2("trigger", "select", {
                data: { id: value.product_id }
            });
        });

        $(document).ajaxStop(function() {
            $.each(products, function(index, value){
                $("#order-table").find("#product-"+value.product_id).find(".input-quantity").val( value.quantity ).trigger("change");
            });
        });

    @endif

</script>
@endsection
