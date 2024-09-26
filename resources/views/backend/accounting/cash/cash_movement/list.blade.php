@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-0">
				<h4 class="header-title">{{ _lang('Movimientos de caja') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal"
                    href="{{ route('cash_movement.create') }}" data-title="Ingreso/salida de efectivo"><i class="ti-plus"></i> {{ _lang('Ingreso/salida de efectivo') }}</a>
            </div>

            <div class="card-body">
                <div class="">
                {{-- <span class="float-end"><b>En caja</b>:&nbsp; $ {{ get_cash()->cash_value }}</span> --}}
                </div>
                <div class="row mt-3">
					
                    <div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Tipo de movimiento') }}</label>
                     	<select class="form-control select2 select-filter" data-placeholder="{{ _lang('Type') }}" name="cashmov_type[]" id="cashmov_type" multiple="true">
							<option value="Closing">{{ _lang('Closing') }}</option>
							{{-- <option value="Opening">{{ _lang('Opening') }}</option> --}}
							<option value="In">{{ _lang('In') }}</option>
							<option value="Out">{{ _lang('Out') }}</option>
                     	</select>
                    </div>

                    <div class="col-lg-3 mb-2 d-none">
                     	<label>{{ _lang('Company') }}</label>
						<select class="form-control select-filter" name="company_id" id="company_id">
                            <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("companies", "id", "company_name", old('company_id', company_id()), has_permission('companies.change')?[]:['id=', company_id()]) }}
                            </select>
                    </div>

                    <div class="col-lg-3">
                     	<label>{{ _lang('Date Range') }}</label>
                     	<input type="text" class="form-control select-filter" id="date_range" autocomplete="off" name="date_range">
                    </div>
	
                </div>

                <hr>
                <table class="table table-bordered" id="cash-movement-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Date') }}</th>
                            <th>{{ _lang('Concepto') }}</th>
                            <th>{{ _lang('Valor') }}</th>
                            <th>{{ _lang('Caja') }}</th>
                            <th>{{ _lang('Usuario') }}</th>
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
<script src="{{ asset('public/backend/assets/js/datatables/cash-movement-table.js') }}"></script>
<script>
jQuery(function(){
    $('#company_id').on('change', e=>setSelectDataAjax('cashs', 'cash_id', 'cash_name', '#cash_id', {showSelectOption: false, where_extra: 'company_id='+e.target.value}));

    $('#company_id').trigger('change');
});
</script>
@endsection