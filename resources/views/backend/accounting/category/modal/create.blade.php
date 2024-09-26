<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('categories.store') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Category Name') }}</label>
            <input type="text" class="form-control" name="category_name" value="{{ old('category_name') }}" required>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save') }}</button>
        </div>
    </div>
</form>