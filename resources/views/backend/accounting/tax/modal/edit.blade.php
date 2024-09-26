<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('TaxController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Tax Name') }}</label>
            <input type="text" class="form-control" name="tax_name" value="{{ $tax->tax_name }}" required>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Rate') }}</label>
            <input type="text" class="form-control float-field" name="rate" value="{{ $tax->rate }}" required>
        </div>
    </div>


    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Type') }}</label>
            <select class="form-control" name="type" required>
                <option value="percent" {{ $tax->type == 'percent' ? 'selected' : '' }}>{{ _lang('Percentage %') }}
                </option>
                <option value="fixed" {{ $tax->type == 'fixed' ? 'selected' : '' }}>{{ _lang('Fixed') }}</option>
            </select>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                {{ _lang('Update') }}</button>
        </div>
    </div>
</form>