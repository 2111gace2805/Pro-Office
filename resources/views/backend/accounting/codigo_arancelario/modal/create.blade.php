<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('codigo_arancelarios.store') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Código') }}</label>
            <input type="text" class="form-control" name="codaran_codigo" value="{{ old('codaran_codigo') }}" required>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Description') }}</label>
            <input type="text" class="form-control" name="codaran_descripcion" value="{{ old('codaran_descripcion') }}" required>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save') }}</button>
        </div>
    </div>
</form>