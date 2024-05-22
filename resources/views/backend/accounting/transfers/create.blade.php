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
                <form method="post" class="validate" autocomplete="off" action="{{ route('passes.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-xl-4 col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Departure date') }}</label>
                                <input type="text" class="form-control datepicker" name="transfer_date"
                                    value="{{ old('transfer_date') }}"  required>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Company') }}</label>
                                <select class="form-control select2" name="company" required>
                                    @foreach ($companies as $item)
                                        <option value="{{$item->id}}">{{$item->company_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Reference') }}</label>
                                <input type="text" class="form-control" required name="reference" value="{{old('reference')}}">
                            </div>
                        </div>

                        {{-- <div class="col-12">
                            <div class="form-group select-product-container">
                                <a href="{{ route('products.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Product') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="item_name"
                                    data-table="items" data-where="2" name="product" id="product" data-items="true"
                                    data-nosell="true" data-haslimit="true">
                                    <option value="">{{ _lang('Select Product') }}</option>
                                </select>
                            </div>
                        </div> --}}
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
                                            <th class="text-right">{{ _lang('Sub Total') }}</th>
                                            <th class="text-right">{{_lang('Impuesto')}}</th>
                                            <th class="text-right">{{_lang('A pagar')}}</th>
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
                                            <th class="text-right" id="total">0.00</th>
                                            <th class="text-center"></th>
                                            <th><input type="hidden" name="product_total" id="product_total" value="0"></th>
                                            <th><input type="hidden" name="tax_total" id="tax_total" value="0"></th>
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