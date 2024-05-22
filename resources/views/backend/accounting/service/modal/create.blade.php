<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('services.store') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Service Name') }}</label>
            <input type="text" class="form-control" name="item_name" value="{{ old('item_name') }}" required>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Service Cost').' '.currency() }}</label>
            <input type="text" class="form-control" name="cost" value="{{ old('cost') }}" required>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Description') }}</label>
            <textarea class="form-control" name="description">{{ old('description') }}</textarea>
        </div>
    </div>


    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
        </div>
    </div>
</form>