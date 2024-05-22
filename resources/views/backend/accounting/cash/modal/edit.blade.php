<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('CashController@update', $id) }}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="row">
		<div class="col-md-12">
			<div class="form-group">
			   <label class="control-label">Nombre de caja</label>						
			   <input type="text" class="form-control" name="cash_name" value="{{ $cash->cash_name }}" required>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
			   <label class="control-label">Monto de apertura</label>						
			   <input type="text" class="form-control" name="cash_value" id="cash_value" value="{{ $cash->cash_value }}" required>
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="control-label">Sucursal</label>
				<select class="form-control" name="company_id" id="company_id" required>
				  <option value="">{{ _lang('Select One') }}</option>
				  {{ create_option("companies", "id", "company_name", $cash->company_id) }}
				</select>
			</div>
		</div>
		<div class="col-12 col-md-12">
			<div class="form-group">
				<label class="control-label">Estado</label>
				<select class="form-control" name="cash_status" id="cash_status" required>
				  <option value="">{{ _lang('Select One') }}</option>
				  <option value="Opened" {{ ( $cash->cash_status == 'Opened') ? 'selected' : '' }}>Abierta</option>
				  <option value="Closed" {{ ( $cash->cash_status == 'Closed') ? 'selected' : '' }}>Cerrada</option>
				</select>
			</div>
		</div>
	</div>

	
	<div class="form-group">
	    <div class="col-md-12">
		    <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
	    </div>
	</div>
</form>
<script src="{{ asset('public/backend/plugins/jquery-alphanum/jquery.alphanum.js') }}"></script>
<script>
	$("#cash_value").numeric({
		allowMinus   : false,
		allowThouSep : false,
		maxDecimalPlaces : 2,
	});
</script>

