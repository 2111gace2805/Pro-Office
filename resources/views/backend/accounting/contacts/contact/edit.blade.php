@extends('layouts.app')

@section('content')
<form method="post" class="validate" autocomplete="off" action="{{ action('ContactController@update', $id) }}"
    enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">{{ _lang('Editar cliente') }}</h4>
                </div>

                <div class="card-body">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="PATCH">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Profile Type') }}</label>
                                <select class="form-control" name="tpers_id" id="tpers_id" required onchange="mostrarCampos()">
                                    <option value="">{{ _lang('Select one') }}</option>
                                    {{ create_option("tipo_persona", "tpers_id", "tpers_nombre", old('tpers_id', $contact->tpers_id)) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" id="company_name">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre o razón social') }}</label>
                                <input type="text" class="form-control" name="company_name" id="company_nameIpt"
                                    value="{{ old('company_name', $contact->company_name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6" id="nombre" style="display: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombres') }}</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" 
                                    value="{{ old('first_name', $contact->firstName) }}" >
                            </div>
                        </div>
                        <div class="col-md-6" id="apellido" style="display: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Apellidos') }}</label>
                                <input type="text" class="form-control" name="lastName" id="lastName" 
                                    value="{{ old('lastName', $contact->lastName) }}" >
                            </div>
                        </div>

                        <div class="col-md-6" id="nombreComercial">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre comercial (caso Pers. jurídica)') }}</label>
                                <input type="text" class="form-control" name="tradename" id="tradename"
                                    value="{{ old('tradename', $contact->tradename) }}">
                            </div>
                        </div>

                        <div class="col-md-6" id="acti_economica">
                            <div class="form-group">
                                <label class="control-label">{{_lang('Acividad Economica / Giro')}}</label>
                                <select class="form-control select2" name="actie_id" id="actie_id" >
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("actividad_economica", "actie_id", "actie_nombre", old('actie_id', $contact->actie_id)) }}
                                </select>
                            </div>
                        </div>
                        <input type="text" class="form-control" hidden name="descActividad" id="descActividad" value="{{ old('descActividad', $contact->descActividad)}}" >


                        <div class="col-md-6" id="nit">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('NIT (homologado o no)') }}</label>
                                <input type="text" class="form-control" name="nit" id="nit_"
                                    value="{{ old('nit', $contact->nit) }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="dui">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('DUI') }} <small title="si es Persona Jurídica, DUI del Representante Legal">(si es PJ, DUI del RL)</small></label>
                                <input type="text" class="form-control" name="dui"
                                    value="{{ old('dui', $contact->dui) }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="nrc">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('NRC') }}</label>
                                <input type="text" class="form-control" name="nrc" id="nrc"
                                    value="{{ old('nrc', $contact->nrc) }}">
                            </div>
                        </div>

                        <div class="col-md-6" id="contribuyente">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Es gran contribuyente') }}</label>
                                <select class="form-control" name="gran_contribuyente" id="gran_contribuyente">
                                    <option value="no" @if(old('gran_contribuyente', $contact->gran_contribuyente)== 'no') selected @endif>No</option>
                                    <option value="si" @if(old('gran_contribuyente', $contact->gran_contribuyente)== 'si') selected @endif>Sí</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="exento">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Es exento de IVA') }}</label>
                                <select class="form-control" name="exento_iva" id="exento_iva">
                                    <option value="no" @if(old('exento_iva', $contact->exento_iva)== 'no') selected @endif>No</option>
                                    <option value="si" @if(old('exento_iva', $contact->exento_iva)== 'si') selected @endif>Sí</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="sujetoIva">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Es no sujeto a IVA') }}</label>
                                <select class="form-control" name="nosujeto_iva" id="nosujeto_iva">
                                    <option value="no" @if(old('nosujeto_iva', $contact->nosujeto_iva)== 'no') selected @endif>No</option>
                                    <option value="si" @if(old('nosujeto_iva', $contact->nosujeto_iva)== 'si') selected @endif>Sí</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-6" id="encargado">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre de contacto o encargado') }}</label>
                                <input type="text" class="form-control" name="contact_name"
                                    value="{{ old('contact_name', $contact->contact_name) }}" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Contact Email') }}</label>
                                <input type="text" class="form-control" name="contact_email"
                                    value="{{ old('contact_email', $contact->contact_email) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Contact Phone') }}</label>
                                <input type="text" class="form-control" name="contact_phone" required
                                    value="{{ old('contact_phone', $contact->contact_phone) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Country') }}</label>
                                <select class="form-control select2" name="pais_id" id="pais_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("paises", "pais_id", "pais_nombre", old('pais_id', $contact->pais_id)) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Departamento') }}</label>
                                <select class="form-control select2" name="depa_id" id="depa_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("departamentos", "depa_id", "depa_nombre", old('depa_id', $contact->depa_id)) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Municipio') }}</label>
                                <select class="form-control select2-ajax" data-value="munidepa_id" data-display="muni_nombre" 
                                    data-table="municipios" name="munidepa_id" id="munidepa_id" data-where_extra="depa_id = '-1'" required>
                                    <option value="">{{ _lang('- Select One -') }}</option>
                                    {{ create_option("municipios", "munidepa_id", "muni_nombre", old('munidepa_id', $contact->munidepa_id), ['munidepa_id=' => old('munidepa_id', $contact->munidepa_id)]) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Address') }}</label>
                                <textarea class="form-control" name="address" required>{{ old('address', $contact->address) }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6" id="group">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Group') }}</label>
                                <select class="form-control select2" name="group_id">
                                    <option value="">{{ _lang('- Select Group -') }}</option>
                                    {{ create_option("contact_groups","id","name",$contact->group_id,array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Código ZIP') }}</label>
                                <input type="text" class="form-control" name="zip" value="{{ old('zip', $contact->zip) }}">
                            </div>
                        </div>

                        {{-- <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Remarks') }}</label>
                                <textarea class="form-control" name="remarks">{{ old('remarks', $contact->remarks) }}</textarea>
                            </div>
                        </div> --}}

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                {{ _lang('Update Contact') }}</button>
        </div>
    </div>
</form>
@endsection



@section('js-script')
    <script src="{{ asset('public/backend/plugins/jquery-alphanum/jquery.alphanum.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('public/backend/assets/js/contacts-create-edit.js') }}"></script>
@endsection