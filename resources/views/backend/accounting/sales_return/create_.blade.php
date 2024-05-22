@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <span class="d-none panel-title">{{ _lang('Create Sales Return') }}</span>

            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('sales_returns.store') }}"
                    enctype="multipart/form-data">

                    <div class="row">

                        {{ csrf_field() }}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Return Date') }}</label>
                                <input type="text" class="form-control datepicker" name="return_date"
                                    value="{{ old('return_date') }}" readOnly="true" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ route('contacts.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Customer') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="contact_name"
                                    data-table="contacts"  name="customer_id" id="customer_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>


                        {{-- <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Attachemnt') }}</label>
                                <input type="file" class="form-control trickycode-file" name="attachemnt">
                            </div>
                        </div> --}}

                        <div class="col-md-12">
                            <div class="form-group select-product-container">
                                <label class="control-label">{{ _lang('Seleccionar factura') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="invoice_number"
                                    data-table="invoices" name="invoice_id" id="invoice_id" data-where_extra="status != 'Canceled'">
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group select-product-container">
                                <label class="control-label">{{ _lang('Select Item') }}</label>
                                <select class="form-control select2-ajax select-selectItems" data-value="id" data-display="description"
                                    data-table="invoice_items" name="selectItems" id="selectItems">
                                    <option value="">{{ _lang('Select Item') }}</option>
                                </select>
                            </div>
                        </div>

                        

                        @php $currency = currency(); @endphp

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
                                            <th>{{ _lang('Tax') }}</th>
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
<script src="{{ asset('public/backend/assets/js/sales_return.js') }}"></script>
@endsection