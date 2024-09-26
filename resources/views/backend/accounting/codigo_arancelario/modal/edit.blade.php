<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('CodigoArancelarioController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Código') }}</label>
            <input type="text" class="form-control" name="codaran_codigo" value="{{ $codigo_arancelario->codaran_codigo }}" required>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Description') }}</label>
            <input type="text" class="form-control" name="codaran_descripcion" value="{{ $codigo_arancelario->codaran_descripcion }}" required>
        </div>
    </div>


    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Update') }}</button>
        </div>
    </div>
</form>