@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Order Notes List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="{{ route('order_notes.create') }}"><i class="ti-plus"></i> {{ _lang('Create Order Note') }}</a>
            </div>

            <div class="card-body">
                <div class="row">
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Order Note Number') }}</label>
                     	<input type="text" class="form-control select-filter" name="order_number" id="order_number">
                    </div>
					
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Customer') }}</label>
						<select class="form-control select2 select-filter" name="client_id">
                            <option value="">{{ _lang('All Customer') }}</option>
							{{ create_option('contacts','id','company_name','',array('company_id=' => company_id())) }}
                     	</select>
                    </div>
					
                    <div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Status') }}</label>
                     	<select class="form-control select2 select-filter" data-placeholder="{{ _lang('Estado de nota') }}" name="status" multiple="true">
							<option value="0">{{ _lang('Anuladas') }}</option>
							<option value="1">{{ _lang('Ingresadas') }}</option>
							<option value="2">{{ _lang('Descargada') }}</option>
                     	</select>
                    </div>

                    <div class="col-lg-3">
                     	<label>{{ _lang('Fecha de entrega') }}</label>
                     	<input type="text" class="form-control select-filter" id="date_range" autocomplete="off" name="date_range">
                    </div>
                </div>

                <hr>
                <table class="table table-bordered" id="order_note-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Order Note Number') }}</th>
                            <th>{{ _lang('Client') }}</th>
                            <th>{{ _lang('Número de contrado') }}</th>
                            <th>{{ _lang('Fecha de entrega') }}</th>
                            <th>{{ _lang('Status') }}</th>
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
<script src="{{ asset('public/backend/assets/js/datatables/order_note-table.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/list_order_notes.js') }}"></script>

@endsection