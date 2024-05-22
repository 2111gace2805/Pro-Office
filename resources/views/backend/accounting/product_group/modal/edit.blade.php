<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('ProductGroupController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Name') }}</label>
            <input type="text" class="form-control" name="prodgrp_name" value="{{ $product_group->prodgrp_name }}" required>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Status') }}</label>
            <select class="form-control" id="prodgrp_status" name="prodgrp_status">
                <option @if(old('prodgrp_status', $product_group->prodgrp_status)== 'Active') selected @endif value="Active">{{_lang('Active')}}</option>
                <option @if(old('prodgrp_status', $product_group->prodgrp_status)== 'Inactive') selected @endif value="Inactive">{{_lang('Inactive')}}</option>
            </select>
        </div>
    </div>


    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Update') }}</button>
        </div>
    </div>
</form>