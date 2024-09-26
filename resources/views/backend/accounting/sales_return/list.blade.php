@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            @php $currency = currency(); @endphp

            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">{{ _lang('Sales Return List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="{{ route('invoices.index') }}"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered" id="sales-return-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Return Date') }}</th>
                            <th>{{ _lang('Customer') }}</th>
                            <th class="text-right">{{ _lang('Grand Total') }}</th>
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
<script src="{{ asset('public/backend/assets/js/datatables/sales-return.js') }}"></script>
@endsection