<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('cash_movement.update', $cash_movement->cashmov_id) }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row p-2">

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Sucursal') }}</label>
                    <select class="form-control select-filter" name="company_id" id="company_id">
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("companies", "id", "company_name", old('company_id', $cash_movement->company_id), has_permission('companies.change')?[]:['id=', company_id()]) }}
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Caja') }}</label>
                    <select class="form-control select-filter" readonly name="cash_id" id="cash_id" style="pointer-events: none;">
                    <option value="">{{ _lang('- Select one -') }}</option>
                    {{ create_option("cashs", "cash_id", "cash_name", old('cash_id', $cash_movement->cash_id), ['cash_id=' => old('cash_id', $cash_movement->cash_id)]) }}
                </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label text-black">{{ _lang('Type') }}</label>
                    <select class="form-control" name="cashmov_type" id="cashmov_type" required>
                        <option value="In" @if(old('cashmov_type', $cash_movement->cashmov_type)== 'In') selected @endif>{{ _lang('In') }}</option>
                        <option value="Out" @if(old('cashmov_type', $cash_movement->cashmov_type)== 'Out') selected @endif>{{ _lang('Out') }}</option>
                    </select>
                </div>
            </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Concepto') }}</label>
                <input type="text" class="form-control" name="cashmov_concept" value="{{ old('cashmov_concept', $cash_movement->cashmov_concept) }}" required>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Value') }}</label>
                <input type="text" class="form-control" name="cashmov_value" value="{{ old('cashmov_value', $cash_movement->cashmov_value) }}" required>
            </div>
        </div>


        <div class="form-group">
            <div class="col-md-12 text-end">

                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                    {{ _lang('Save Changes') }}</button>
            </div>
        </div>
    </div>
    <script src="{{ asset('public/backend/assets/js/cash_movement.js') }}"></script>
</form>