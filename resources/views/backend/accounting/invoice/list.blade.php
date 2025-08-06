@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Invoice List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto"
                    href="{{ route('invoices.create') }}"><i class="ti-plus"></i> {{ _lang('Create Invoice') }}</a>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 mb-2">
                        <label>{{ _lang('Tipo de factura') }}</label>
                         <select class="form-control select2 select-filter" name="tipodoc_id" id="tipodoc_id">
                            <option value="">{{ _lang('Select One') }}</option>
                            {{ create_option("tipo_documento", "tipodoc_id", "tipodoc_nombre", old('tipodoc_id'), ["tipodoc_estado=" => 'Activo']) }}
                        </select>
                   </div>	
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Invoice Number') }}</label>
                     	<input type="text" class="form-control select-filter" name="invoice_number" id="invoice-number">
                    </div>	
					
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Customer') }}</label>
						<select class="form-control select2 select-filter" name="client_id" id="client_id">
                            <option value="">{{ _lang('All Customer') }}</option>
							{{ create_option('contacts','id','company_name','',array('company_id=' => company_id())) }}
                     	</select>
                    </div>	
					
                    <div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Status') }}</label>
                     	<select class="form-control select2 select-filter" data-placeholder="{{ _lang('Invoice Status') }}" name="status" name="status" multiple="true">
							<option value="Unpaid">{{ _lang('Unpaid') }}</option>
							<option value="Paid">{{ _lang('Paid') }}</option>
							<option value="Partially_Paid">{{ _lang('Partially Paid') }}</option>
							<option value="Canceled">{{ _lang('Canceled') }}</option>
                     	</select>
                    </div>	

                    <div class="col-lg-3">
                     	<label>{{ _lang('Date Range') }}</label>
                     	<input type="text" class="form-control select-filter" id="date_range" autocomplete="off" name="date_range">
                    </div>

                    <div class="col-lg-12 mt-4">
                        <div class="row">
                            <div class="col-lg-2">
                                <button class="btn btn-primary" id="btnDownloadJson" onclick="downloadJSON()">Descargar JSON</button>
                            </div>	
                            <div class="col-lg-2">
                                <button class="btn btn-primary" id="btnDownloadJson" onclick="downloadJSON(true)">Descargar PDF</button>
                            </div>	
                        </div>
                    </div>
	
                </div>

                <hr>
                <table class="table table-bordered" id="invoice-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Invoice Number') }}</th>

                            <th>{{ _lang('Tipo de factura')}}</th>
                            <th>{{ _lang('Client') }}</th>
                            <th>{{ _lang('Invoice Date') }}</th>
                            {{-- <th>{{ _lang('Due Date') }}</th> --}}
                            <th class="text-right">{{ _lang('Grand Total') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center">{{ _lang('Downloads') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                            {{--
                            <th>{{ _lang('Control Number') }}</th>
                            <th>{{ _lang('Generation Code') }}</th>
                            <th>{{ _lang('Sello') }}</th>
                            <th>{{ _lang('total_tax') }}</th>
                            <th>{{ _lang('sub_total') }}</th>
                            --}}
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="anulacionModal" data-bs-backdrop="static" data-bs-keyboard="false"  aria-labelledby="anulacionModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ _lang('Cancel Invoice') }}</h5>
          <button type="button" class="btn btn-close" onclick="closeModal()">x</button>
        </div>
        <div class="modal-body">
            <div class="container">
                <form id="frmAnulacion">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="hidden" id="id_invoice">
                                <label class="control-label">{{ _lang('Tipo de invalidación') }}</label>
                                <select class="form-control" name="tipo_anulacion" id="tipo_anulacion" required onchange="motivoAnulacion(this);validoDatos(this);">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_invalidacion", "id", "tipo_invalidacion_nombre", old('id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Motivo de invalidación') }}</label>
                                <input type="text" class="form-control" name="motivo_anulacion" id="motivo_anulacion" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" onchange="validoDatos(this)" oninput="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-12" id="dvDteReemplaza" style="display:none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('DTE Reemplaza') }}</label>
                                <select class="form-control select2_ajax" data-value="tipodoc_id" data-display="invoice_number" data-display2="numero_control"
                                    data-table="invoices" data-where="Canceled" data-where-extra="" data-action="anular" name="dte_reemplaza" id="dte_reemplaza" onchange="validoDatos(this)">
                                    <option value="">{{ _lang('Select') }}</option>
                                </select>
                            </div>
                        </div>
                        {{-- <div class="col-md-12">
                            <fieldset>
                                <legend style="font-size: 1rem;"><b>Responsable de anulación:</b></legend>
                            </fieldset>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre:') }}</label>
                                <input type="text" class="form-control" name="nombre_resp" id="nombre_resp" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo documento:') }}</label>
                                <select class="form-control" name="tipo_doc_resp" id="tipo_doc_resp" onchange="validoDatos(this)" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_doc_ident_receptor", "tdocrec_id", "tdocrec_nombre", old('tdocrec_id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Número de documento:') }}</label>
                                <input type="text" class="form-control" name="num_doc_resp" id="num_doc_resp" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" required>
                            </div>
                        </div> --}}
                        <div class="col-md-12">
                            <fieldset>
                                <legend style="font-size: 1rem;"><b>Solicitante de invalidación:</b></legend>
                            </fieldset>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre:') }}</label>
                                <input type="text" class="form-control" name="nombre_soli" id="nombre_soli" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo documento:') }}</label>
                                <select class="form-control" name="tipo_doc_soli" id="tipo_doc_soli" onchange="validoDatos(this)" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_doc_ident_receptor", "tdocrec_id", "tdocrec_nombre", old('tdocrec_id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Número de documento:') }}</label>
                                <input type="text" class="form-control" name="num_doc_soli" id="num_doc_soli" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
          <button type="button" class="btn btn-danger" onclick="deleteOrder()">Invalidar</button>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="contingenciaModal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="contingenciaModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ _lang('Contingency Mode Invoice') }}</h5>
          <button type="button" class="btn btn-close" onclick="closeModalContingencia()">x</button>
        </div>
        <div class="modal-body">
            <div class="container">
                <form id="frmContingencia">
                    <div class="row">
                        <input type="hidden" id="id_invoice_con">
                        <div class="col-md-12">
                            <fieldset>
                                <legend style="font-size: 1rem;"><b>Responsable de establecimiento:</b></legend>
                            </fieldset>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre:') }}</label>
                                <input type="text" class="form-control" name="responsableEstablecimiento" id="responsableEstablecimiento" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo documento:') }}</label>
                                <select class="form-control" name="tipoDocRespEstablecimiento" id="tipoDocRespEstablecimiento" onchange="validoDatos(this)" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_doc_ident_receptor", "tdocrec_id", "tdocrec_nombre", old('tdocrec_id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Número de documento:') }}</label>
                                <input type="text" class="form-control" name="numDocRespEstablecimiento" id="numDocRespEstablecimiento" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <fieldset>
                                <legend style="font-size: 1rem;"><b>Detalles de contingencia:</b></legend>
                            </fieldset>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de contingencia') }}</label>
                                <select class="form-control" name="tipoContingencia" id="tipoContingencia" required onchange="motivoContingencia(this);validoDatos(this);">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_contingencia", "tconting_id", "tconting_nombre", old('id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Motivo de contingencia') }}</label>
                                <input type="text" class="form-control" name="motivo_contingencia" id="motivo_contingencia" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" onchange="validoDatos(this)" oninput="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Fecha inicio') }}</label>
                                <input type="date" class="form-control" name="fecha_inicio_contingencia" id="fecha_inicio_contingencia" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" onchange="validoDatos(this)" oninput="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Hora inicio') }}</label>
                                <input type="time" class="form-control" name="hora_inicio_contingencia" id="hora_inicio_contingencia" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" onchange="validoDatos(this)" oninput="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Fecha fin') }}</label>
                                <input type="date" class="form-control" name="fecha_fin_contingencia" id="fecha_fin_contingencia" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" onchange="validoDatos(this)" oninput="validoDatos(this)" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Hora fin') }}</label>
                                <input type="time" class="form-control" name="hora_fin_contingencia" id="hora_fin_contingencia" onkeydown="validoDatos(this)" onkeyup="validoDatos(this)" onchange="validoDatos(this)" oninput="validoDatos(this)" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModalContingencia()">{{ _lang('Close') }}</button>
          <button type="button" class="btn btn-success" onclick="sendInvoiceContingency()">{{ _lang('Send') }}</button>
        </div>
      </div>
    </div>
</div>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/datatables/invoice-table.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/list_invoices.js') }}"></script>

<script>
    function sendInvoiceContingency() {

        let id =  $("#id_invoice_con").val();

        let data = $("#frmContingencia").serialize();

        let valido = validarCamposContingencia();

        if( valido ){
            Swal.fire({
                title: "¿Estas seguro?",
                text: "Se enviará DTE en contingencia",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, enviar!",
                cancelButtonText: "Cancelar",
                allowOutsideClick: false,
                allowEscapeKey: false
            })
            .then((result) => {
    
                if( result.isConfirmed ){
    
                    $.ajax({
                        url: `/contingenciaInvoiceMH/${id}`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: data,
                        beforeSend: function () {
                            Swal.fire({
                                title: '<b>Enviando DTE</b>',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                },
                            });
                        },
                        success: function(response) {
                            if( response.result === 'errorMH' ){
    
                                let  errorMessage = '<ul>';
                                errorMessage += '<li>' + response.data.descripcionMsg + '</li>';
                                $.each( response.data.observaciones, function(index, message) {
                                    errorMessage += '<li>' + message + '</li>';
                                });
                                errorMessage += '</ul>';
    
                                Swal.fire({
                                    title: "Oops! Error al enviar DTE",
                                    html: errorMessage,
                                    icon: "error",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                });
                            }
                            else{
                                $("#contingenciaModal").modal('hide');
                                Swal.fire({
                                    title: "Realizado!",
                                    text: response.message,
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    timer: 2000,
                                    showConfirmButton: false,
                                })
    
                                setTimeout(() => {
                                    let url = '/invoices/';
                                    window.location.href = url; 
                                }, 2000);
    
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire({
                                title: "Error",
                                text: errorThrown,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

    }

    function deleteOrder() {

        let id =  $("#id_invoice").val();

        let data = $("#frmAnulacion").serialize();

        let valido = validarCampos();

        if( valido ){
            Swal.fire({
                title: "¿Estas seguro?",
                text: "Los productos serán reintegrados al stock.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, ¡Invalidar!",
                cancelButtonText: "Cancelar",
                allowOutsideClick: false,
                allowEscapeKey: false
            })
            .then((result) => {
    
                if( result.isConfirmed ){
    
                    $.ajax({
                        url: `/invoices/${id}?${data}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        beforeSend: function () {
                            Swal.fire({
                                title: '<b>Anulando DTE</b>',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                },
                            });
                        },
                        success: function(response) {
                            if( response.result === 'error_usuario' ){
    
                                Swal.fire({
                                    title: "Oops! Error al invalidar de DTE",
                                    html: response.message,
                                    icon: "error",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                });
                            }
                            else if( response.result === 'errorMH' ){
    
                                let  errorMessage = '<ul>';
                                errorMessage += '<li>' + response.data.descripcionMsg + '</li>';
                                $.each( response.data.observaciones, function(index, message) {
                                    errorMessage += '<li>' + message + '</li>';
                                });
                                errorMessage += '</ul>';
    
                                Swal.fire({
                                    title: "Oops! Error al invalidar de DTE",
                                    html: errorMessage,
                                    icon: "error",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                });
                            }
                            else{
                                $("#anulacionModal").modal('hide');
                                Swal.fire({
                                    title: "Realizado!",
                                    text: response.message,
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    timer: 2000,
                                    showConfirmButton: false,
                                })
    
                                setTimeout(() => {
                                    let url = '/invoices/';
                                    window.location.href = url; 
                                }, 2000);
    
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire({
                                title: "Error",
                                text: errorThrown,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

    }

    function reenviarCorreo( id_invoice ){
        Swal.fire({
            title: "¿Estas seguro?",
            text: "Se reenviara DTE a cliente.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, reenviar!",
            cancelButtonText: "Cancelar",
            allowOutsideClick: false,
            allowEscapeKey: false
        })
        .then((result) => {
    
            if( result.isConfirmed ){

                let anular = false;
                let reenvio = true;

                $.ajax({
                    url: `/testCorreo/${id_invoice}/${anular}/${reenvio}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: '<b>Reenviando DTE</b>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        });
                    },
                    success: function(response) {

                        Swal.fire({
                            title: "Realizado!",
                            text: "¡DTE reenviado correctamente!",
                            icon: "success",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: "Error",
                            text: errorThrown,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }

    function downloadJSON( download_pdf = false ){

        let tipo_factura    = $("#tipodoc_id").val();
        let numero_factura  = $("#invoice_number").val();
        let client_id       = $("#client_id").val();
        let status          = $("#status").val();
        let rangos          = $("#date_range").val();

        let pdf = ( download_pdf ) ? 1 : 0;

        if( rangos == "" ){

            Swal.fire({
                title: "Error",
                text: "Seleccione un rango de fechas",
                icon: "error"
            });

            return;
        }

        let tipo = ( download_pdf ) ? "PDF" : "JSON";

        Swal.fire({
            title: "¿Estas seguro?",
            html: `Se descargarán los ${tipo} de las fechas ${rangos}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, descargar!",
            cancelButtonText: "Cancelar",
            allowOutsideClick: false,
            allowEscapeKey: false
        })
        .then((result) => {

            if( result.isConfirmed ){

                $.ajax({
                    url: "/downloadJsons/",
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        tipo_factura,
                        numero_factura,
                        client_id,
                        status,
                        rangos,
                        pdf
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: `<b>Generando archivo zip con ${tipo}...</b>`,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        });
                    },
                    success: function(response) {

                        if( response.url ){
                            window.location.href = response.url;
                        }

                        Swal.fire({
                            title: "Realizado!",
                            text: `¡${tipo} generados correctamente!`,
                            icon: "success",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        let errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.error ? jqXHR.responseJSON.error : 'Ocurrió un error inesperado';

                        Swal.fire({
                            title: "Error",
                            text: errorMessage,
                            icon: "error"
                        });
                    }
                });
            }
        })
    }
</script>
@endsection