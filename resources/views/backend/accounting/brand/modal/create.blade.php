<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('brands.store') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Brand Name') }}</label>
            <input type="text" class="form-control" name="brand_name" value="{{ old('brand_name') }}" required>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Brand status') }}</label>
            <select class="form-control" id="brand_status" name="brand_status">
                <option @if(old('brand_status')== 'Active') selected @endif value="Active">{{_lang('Active')}}</option>
                <option @if(old('brand_status')== 'Inactive') selected @endif value="Inactive">{{_lang('Inactive')}}</option>
            </select>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save') }}</button>
        </div>
    </div>
</form>