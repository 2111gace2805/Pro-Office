<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('CompanyController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row">
        <div class="col-12 col-md-8">
            <div class="form-group">
                <label class="control-label">{{ _lang('Company Name') }}</label>
                <input type="text" class="form-control" name="company_name" value="{{ $info->company_name }}" required>
            </div>
        </div>

        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de establecimiento') }}</label>
                                <select class="form-control" name="tipoest_id" id="tipoest_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_establecimiento", "tipoest_id", "tipoest_nombre", old('tipoest_id', $info->tipoest_id)) }}
                                </select>
                            </div>
                        </div>
    
        <div class="col-xl-6 col-md-6 col-sm-12">
            <div class="card shadow-none" style="border:1px solid #e0e0e0;">
                <div class="card-body">
                    <div class="card-title text-black"><strong>Logo</strong></div>
    
                    <input type="file" class="form-control dropify" name="imagen" 
                        data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG"
                        data-default-file="{{ isset($info->logo) ? url($info->logo) : '' }}">
                </div>
            </div>
        </div>
    
        <div class="col-xl-6 col-md-6 col-sm-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Phone') }}</label>
                <input type="text" class="form-control" name="phone" value="{{ $info->cellphone }}" required>
            </div>
            <div class="form-group">
                <label class="control-label">{{ _lang('Email') }}</label>
                <input type="email" class="form-control" name="email" value="{{ $info->email }}">
            </div>
        
            <div class="form-group">
                <label class="control-label">{{ _lang('Status') }}</label>
                <select class="form-control auto-select" data-selected="{{ $info->status }}" name="status"
                    required>
                    <option value="1">{{ _lang('Active') }}</option>
                    <option value="0">{{ _lang('Inactive') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Departamento') }}</label>
                                <select class="form-control select2" name="depa_id" id="depa_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("departamentos", "depa_id", "depa_nombre", old('depa_id', $info->depa_id)) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mt-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Municipio') }}</label>
                                <select class="form-control select2-ajax" data-value="munidepa_id" data-display="muni_nombre" 
                                    data-table="municipios" name="munidepa_id" id="munidepa_id" data-where_extra="depa_id = '-1'">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("municipios", "munidepa_id", "muni_nombre", old('munidepa_id', $info->munidepa_id), ['munidepa_id=' => old('munidepa_id', $info->munidepa_id)]) }}
                                </select>
                            </div>
                        </div>
        
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Address') }}</label>
                <textarea class="form-control" name="address">{{ $info->address }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Update') }}</button>
        </div>
    </div>
</form>
<script>
    function depa_idChanged(inicio = false){
        if(inicio == false) $('#munidepa_id').val('');
        $('#munidepa_id').attr('data-where_extra', "depa_id = '"+$('#depa_id').val()+"'");
        $('#munidepa_id').data('where_extra', "depa_id = '"+$('#depa_id').val()+"'");
        setSelect2Ajax();
    }

    $('#depa_id').on('change', e=>depa_idChanged());

    $(()=>depa_idChanged(true));
</script>