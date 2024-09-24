@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Create Order Note') }}</h4>
            </div>

            <div class="card-body">
                <form method="post" class="validate" id="frmOrderNotes" autocomplete="off" action="{{ route('order_notes.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        {{-- <div class="col-md-8">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Empresa por la cual se está vendiendo') }}</label>
                                <input type="text" class="form-control" name="sales_company" value="{{ old('sales_company') }}" required>
                            </div>
                        </div> --}}

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Order Note Number') }}</label>
                                <input type="text" class="form-control" name="order_number" id="order_number" value="{{ old('order_number', $num_order) }}" onkeydown="validoDatos(this)" required readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <a href="{{ route('contacts.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Client') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="company_name" data-table="contacts"  name="client_id" id="client_id" required onchange="validoDatos(this);">
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <a href="{{ route('warehouses.create_order') }}" data-reload="false" data-title="{{ _lang('Add New Warehouse') }}" class="ajax-modal select2-add">
                                    <i class="ti-plus"></i> {{ _lang('Add New') }}
                                </a>
                                <label class="control-label">{{ _lang('Warehouses') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="name" data-table="warehouse_clients" name="warehouse_id" id="warehouse_id" data-where_extra="id = '-1'" required>
                                    <option value="">{{ _lang('- Select One -') }}</option>
                                    {{ create_option("warehouse_clients", "id", "name", old('warehouse_id'), ['id=' => old('warehouse_id')]) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <a href="{{ route('warehouses.create_order') }}" data-reload="false" data-title="{{ _lang('Add New Warehouse') }}" class="ajax-modal select2-add">
                                    <i class="ti-plus"></i> {{ _lang('Add New') }}
                                </a>
                                <label class="control-label">{{ _lang('Tipo de venta institucional') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="name" data-table="warehouse_clients" name="warehouse_id" id="warehouse_id" data-where_extra="id = '-1'" required>
                                    <option value="">{{ _lang('- Select One -') }}</option>
                                    {{-- {{ create_option("warehouse_clients", "id", "name", old('warehouse_id'), ['id=' => old('warehouse_id')]) }} --}}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Número de licitación pública') }}</label>
                                <input type="text" class="form-control" name="num_public_tender" value="{{ old('num_public_tender') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Número de contrato') }}</label>
                                <input type="text" class="form-control" name="num_contract" value="{{ old('num_contract') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Fecha de entrega según contrato') }}</label>
                                <input type="date" class="form-control" name="deliver_date_contract" value="{{ old('deliver_date_contract') }}" required>
                            </div>
                        </div>

                        <div style="background-color: #efefef; height: 2px; width: 100%; margin-bottom: 25px; margin-top: 12px;"></div>
                        <div class="col-md-12 mb-2">
                            <h5 class="header-title">{{ _lang('Details Note') }}</h5>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Pago de análisis') }}</label>
                                <input type="text" class="form-control details" id="payment_analysis">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Código del producto de la Institución') }}</label>
                                <input type="text" class="form-control details" id="code_product_institution">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('No. de entrega') }}</label>
                                <input type="text" class="form-control details" id="delivery_number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Origen del producto') }}</label>
                                <input type="text" class="form-control details" id="product_origin">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Vencimiento ofertado') }}</label>
                                <input type="text" class="form-control details" id="offered_expiry">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Lote del producto') }}</label>
                                <input type="text" class="form-control details" id="product_lot">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Vence') }}</label>
                                <input type="date" class="form-control details" id="expires">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Manufactura') }}</label>
                                <input type="text" class="form-control details" id="manufacture">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Certificados de análisis') }}</label>
                                <input type="text" class="form-control details" id="analysis_certificate">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Empresa por la que se esta entregando el producto') }}</label>
                                <input type="text" class="form-control details" id="product_delivery_company">
                            </div>
                        </div>
                        <div class="col-md-7">
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
                        <div class="col-md-1" style="margin-top: 2rem !important;">
                            <a type="button" class="btn btn-success btn-block" id="btnAdd"><i class="ti ti-plus"></i></a>
                        </div>

                        <!--Order table -->
                        @php $currency = currency(); @endphp

                        @php $taxes = App\Tax::all(); @endphp

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="order-table" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Renglón') }}</th>
                                            <th>{{ _lang('Referencia') }}</th>
                                            <th>{{ _lang('Description') }}</th>
                                            <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                                            <th class="text-center wp-100">{{ _lang('Muestras') }}</th>
                                            <th class="text-center">{{ _lang('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot class="tfoot active">
                                        <tr>
                                            <th>{{ _lang('Sumas') }}</th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-center" id="total-qty">0</th>
                                            <th class="text-center" id="total-samples">0</th>
                                            <th class="text-center"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!--End Order table -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Note') }}</label>
                                <textarea class="form-control" rows="4" name="note">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="button" onclick="saveInvoice(event)" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                                    {{ _lang('Save Note') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<select class="form-control d-none" id="tax-selector">
    @foreach(App\Tax::all() as $tax)
    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">
        {{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
    @endforeach
</select>

@endsection


@section('js-script')
<script src="{{ asset('public/backend/plugins/jquery-alphanum/jquery.alphanum.js') }}"></script>
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/order_note.js') }}"></script>

<script>
</script>
@endsection