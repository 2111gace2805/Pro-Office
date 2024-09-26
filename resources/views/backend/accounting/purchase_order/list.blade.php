@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">

        <div class="card mt-2">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">{{ _lang('Purchase Order List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="{{ route('purchase_orders.create') }}"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-lg-3 mb-2">
                        <label>{{ _lang('Supplier') }}</label>
                        <select class="form-control select2 select-filter" name="supplier_id">
                            <option value="">{{ _lang('All Supplier') }}</option>
                            {{ create_option('suppliers','id','supplier_name','',array()) }}
                        </select>
                    </div>

                    <div class="col-lg-3 mb-2">
                        <label>{{ _lang('Order Status') }}</label>
                        <select class="form-control select2 select-filter"
                            data-placeholder="{{ _lang('Order Status') }}" name="order_status" multiple="true">
                            <option value="1">{{ _lang('Ordered') }}</option>
                            <option value="2">{{ _lang('Pending') }}</option>
                            <option value="3">{{ _lang('Received') }}</option>
                            <option value="4">{{ _lang('Canceled') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3 mb-2">
                        <label>{{ _lang('Payment Status') }}</label>
                        <select class="form-control select2 select-filter"
                            data-placeholder="{{ _lang('Payment Status') }}" name="payment_status" multiple="true">
                            <option value="1">{{ _lang('Paid') }}</option>
                            <option value="0">{{ _lang('UnPaid') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label>{{ _lang('Order Date Range') }}</label>
                        <input type="text" class="form-control select-filter" id="date_range" autocomplete="off"
                            name="date_range">
                    </div>
                </div>
                <hr>

                <table class="table table-bordered" id="purchase-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Order Date') }}</th>
                            <th>{{ _lang('Supplier') }}</th>
                            <th>{{ _lang('Order Status') }}</th>
                            <th class="text-right">{{ _lang('Grand Total') }}</th>
                            <th class="text-right">{{ _lang('Paid') }}</th>
                            <th>{{ _lang('Payment Status') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


@section('js-script')
<script src="{{ asset('public/backend/assets/js/datatables/purchase-order-table.js') }}"></script>
@endsection