@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Create Purchase Return') }}</h4>
            </div>
            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('purchase_returns.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Return Date') }}</label>
                                <input type="text" class="form-control datepicker" name="return_date"
                                    value="{{ old('return_date') }}" readOnly="true" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <a href="{{ route('suppliers.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Supplier') }}" class="ajax-modal-2 select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Supplier') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="supplier_name"
                                    data-table="suppliers"  name="supplier_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <a href="{{ route('accounts.create') }}" data-reload="false"
                                    data-title="{{ _lang('Create Account') }}" class="ajax-modal-2 select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Credit Account') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="account_title"
                                    data-table="accounts" 
                                    name="account_id" id="account_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option('accounts','id','account_title',old('account_id'),array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <a href="{{ route('chart_of_accounts.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Income/Expense Type') }}"
                                    class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Deposit Category') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="name"
                                    data-table="chart_of_accounts" data-where="3" name="chart_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <a href="{{ route('payment_methods.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Deposit Payment Method') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="name"
                                    data-table="payment_methods"  name="payment_method_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Attachemnt') }}</label>
                                <input type="file" class="form-control trickycode-file" name="attachemnt">
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
<script src="{{ asset('public/backend/assets/js/purchase_return.js?v=1.0') }}"></script>
@endsection