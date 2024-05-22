<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('ProductUnitController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Unit Name') }}</label>
            <input type="text" class="form-control" name="unit_name" value="{{ $productunit->unit_name }}" required>
        </div>
    </div>


    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Update') }}</button>
        </div>
    </div>
</form>