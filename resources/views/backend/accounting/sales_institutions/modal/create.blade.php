<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('institutions.store') }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Name') }}</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Códigos') }}</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mt-2">
                                                <div class="custom-control custom-switch">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input"
                                                        data-dv="dvISSS"
                                                        data-input="code_isss"
                                                        name="active_code_isss"
                                                        id="active_code_isss"
                                                        onclick="activeInput(this);"
                                                        value="1"
                                                    >
                                                    <label class="custom-control-label" for="active_code_isss">Código ISSS</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mt-2">
                                                <div class="custom-control custom-switch">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input"
                                                        data-dv="dvMinsal"
                                                        data-input="code_minsal"
                                                        name="active_code_minsal"
                                                        id="active_code_minsal"
                                                        onclick="activeInput(this);"
                                                        value="1"
                                                    >
                                                    <label class="custom-control-label" for="active_code_minsal">Código MINSAL</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mt-2">
                                                <div class="custom-control custom-switch">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input"
                                                        data-dv="dvOnu"
                                                        data-input="code_onu"
                                                        name="active_code_onu"
                                                        id="active_code_onu"
                                                        onclick="activeInput(this);"
                                                        value="1"
                                                    >
                                                    <label class="custom-control-label" for="active_code_onu">Código ONU</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" id="dvISSS" style="display:none;">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Código ISSS') }}</label>
                                    <input type="text" class="form-control codes" name="code_isss" id="code_isss" value="{{ old('code_isss') }}">
                                </div>
                            </div>
                            <div class="col-md-12" id="dvMinsal" style="display:none;">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Código MINSAL') }}</label>
                                    <input type="text" class="form-control codes" name="code_minsal" id="code_minsal" value="{{ old('code_minsal') }}">
                                </div>
                            </div>
                            <div class="col-md-12" id="dvOnu" style="display:none;">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Código ONU') }}</label>
                                    <input type="text" class="form-control codes" name="code_onu" id="code_onu" value="{{ old('code_onu') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-4">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>  {{ _lang('Guardar Institución') }}</button>
            </div>
        </div>
    </div>
</form>
<script src="{{ asset('public/backend/plugins/jquery-alphanum/jquery.alphanum.js') }}"></script>
<script>
    $(".codes").alphanum({
        allow:    '&,;.:/-_.,@%&#()*+;:!?/!¡?=$"´´++][}{``^^',
        maxLength: 25
    });


    function activeInput( input ){

        let id  = $(input).attr("id");
        let dv  = $(input).data("dv");
        let inp = $(input).data("input");

        if( $('#' + id + '').is(':checked') ){
            
            $('#' + dv + '').show('slow');
            $('#' + inp + '').attr('required', true);
        }
        else{
            $('#' + dv + '').hide('slow');
            $('#' + inp + '').attr('required', false);
            $('#' + inp + '').val('');
        }

    }
</script>
