@extends('layouts.app')

@section('content')
<form method="post" class="validate" autocomplete="off" action="{{ route('contacts.store') }}"
    enctype="multipart/form-data">
    <div class="row">
    
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">{{ _lang('Add New Contact') }}</h4>
                </div>

                <div class="card-body">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Profile Type') }}</label>
                                <select class="form-control verificarCliente" name="tpers_id" id="tpers_id" required onchange="mostrarCampos()">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_persona", "tpers_id", "tpers_nombre", old('tpers_id')) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" id="company_name">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre o razón social') }}</label>
                                <input type="text" class="form-control" name="company_name" id="company_nameIpt"
                                    value="{{ old('company_name') }}" >
                            </div>
                        </div>

                        <div class="col-md-6" id="nombre" style="display: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombres') }}</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" 
                                    value="{{ old('first_name') }}" >
                            </div>
                        </div>
                        <div class="col-md-6" id="apellido" style="display: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Apellidos') }}</label>
                                <input type="text" class="form-control" name="lastName" id="lastName" 
                                    value="{{ old('lastName') }}" >
                            </div>
                        </div>
                        
                        <div class="col-md-6"  id="nombreComercial">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre comercial (caso Pers. jurídica)') }}</label>
                                <input type="text" class="form-control" name="tradename" id="tradename"
                                    value="{{ old('tradename') }}">
                            </div>
                        </div>

                        <div class="col-md-6" id="acti_economica">
                            <div class="form-group">
                                <label class="control-label">{{_lang('Acividad Economica / Giro')}}</label>
                                <select class="form-control select2" name="actie_id" id="actie_id" >
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("actividad_economica", "actie_id", "actie_nombre", old('actie_id')) }}
                                </select>
                            </div>
                        </div>
                        
                        <input type="text" class="form-control" name="descActividad" hidden id="descActividad" value="{{ old('descActividad')}}" >
                        
                        
                        
                        <div class="col-md-6" id="nit">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('NIT (homologado o no)') }}</label>
                                <input type="text" class="form-control" name="nit" id="nit_"
                                    value="{{ old('nit') }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="dui">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('DUI') }}</label>
                                <input type="text" class="form-control verificarCliente" name="dui"
                                    value="{{ old('dui') }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="nrc">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('NRC') }}</label>
                                <input type="text" class="form-control verificarCliente" name="nrc" id="nrc"
                                    value="{{ old('nrc') }}">
                            </div>
                        </div>

                        <div class="col-md-6" id="contribuyente">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Es gran contribuyente') }}</label>
                                <select class="form-control" name="gran_contribuyente" id="gran_contribuyente">
                                    <option value="no" @if(old('gran_contribuyente')== 'no') selected @endif>No</option>
                                    <option value="si" @if(old('gran_contribuyente')== 'si') selected @endif>Sí</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="exento">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Es exento de IVA') }}</label>
                                <select class="form-control" name="exento_iva" id="exento_iva">
                                    <option value="no" @if(old('exento_iva')== 'no') selected @endif>No</option>
                                    <option value="si" @if(old('exento_iva')== 'si') selected @endif>Sí</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="sujetoIva">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Es no sujeto a IVA') }}</label>
                                <select class="form-control" name="nosujeto_iva" id="nosujeto_iva">
                                    <option value="no" @if(old('nosujeto_iva')== 'no') selected @endif>No</option>
                                    <option value="si" @if(old('nosujeto_iva')== 'si') selected @endif>Sí</option>
                                </select>
                            </div>
                        </div>

                        
                        <div class="col-md-6" id="encargado">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre de contacto o encargado') }}</label>
                                <input type="text" class="form-control" name="contact_name" id="contact_nameinp"
                                    value="{{ old('contact_name') }}" >
                            </div>
                        </div>
                        

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Contact Email') }}</label>
                                <input type="text" class="form-control" name="contact_email"
                                    value="{{ old('contact_email') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Contact Phone') }}</label>
                                <input type="text" class="form-control" name="contact_phone"
                                    value="{{ old('contact_phone') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Country') }}</label>
                                <select class="form-control select2" name="pais_id" id="pais_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("paises", "pais_id", "pais_nombre", old('pais_id', '9300')) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Departamento') }}</label>
                                <select class="form-control select2" name="depa_id" id="depa_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("departamentos", "depa_id", "depa_nombre", old('depa_id')) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Municipio') }}</label>
                                <select class="form-control select2-ajax" data-value="munidepa_id" data-display="muni_nombre" 
                                    data-table="municipios" name="munidepa_id" id="munidepa_id" data-where_extra="depa_id = '-1'" required>
                                    <option value="">{{ _lang('- Select One -') }}</option>
                                    {{ create_option("municipios", "munidepa_id", "muni_nombre", old('munidepa_id'), ['munidepa_id=' => old('munidepa_id')]) }}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('District') }}</label>
                                <select class="form-control select2-ajax districts-select" data-value="dist_id" data-display="dist_name" 
                                    data-table="districts" name="dist_id" id="dist_id" data-where_extra="munidepa_id = '-1'">
                                    <option value="">{{ _lang('- Select One -') }}</option>
                                    {{ create_option("districts", "dist_id", "dist_name", old('dist_id'), ['dist_id=' => old('dist_id')]) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Address') }}</label>
                                <textarea placeholder="Ej.: Avenida Norte, Barrio El Centro, San Salvador." class="form-control" required name="address">{{ old('address') }}</textarea>
                                <small style="font-size: small;color: gray;font-style: italic;">* Según la 'Ley de Ordenamiento y Desarrollo Territorial', para efectos de los DTE, el Distrito deberá ingresarse en la parte final de la dirección.</small>
                            </div>
                        </div>

                        

                        <div class="col-md-6" id="group">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Group') }}</label>
                                <select class="form-control select2" name="group_id">
                                    <option value="">{{ _lang('- Select Group -') }}</option>
                                    {{ create_option("contact_groups", "id", "name", old('group_id'), array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Código ZIP</label>
                                <input type="text" class="form-control" name="zip" value="{{ old('zip') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-12 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Contact') }}</button>
        </div>

    </div>
</form>
@endsection

@section('js-script')
    {{-- <script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script> --}}
    <script src="{{ asset('public/backend/plugins/jquery-alphanum/jquery.alphanum.js') }}"></script>
    <script src="{{ asset('public/backend/assets/js/contacts-create-edit.js') }}?v={{filemtime(public_path('/backend/assets/js/contacts-create-edit.js'))}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
@endsection