//FUNCIONES DEL METODO ANULACION

function setSelect2CCF( clase = null, templateResult = undefined ){

    return new Promise((resolve, reject) => {
        if ($(clase == null? ".select2_ajax":clase).length) {
            $(clase == null? ".select2_ajax":clase).each(function (i, obj) {

                var display2 = "";
                var display2label = "";
                var where = "";
                var items = "";
                var where_extra = "";

                // display2 es el valor del label concatenado ej = "item - label: valor"
                if (typeof $(this).data('display2') !== "undefined") {
                    display2 = "&display2=" + $(this).data('display2');
                }
                
                // display2 es label concatenado ej = "item - label: 1234"
                if (typeof $(this).data('display2label') !== "undefined") {
                    display2label = "&display2label=" + $(this).data('display2label');
                }
                
                if (typeof $(this).data('where') !== "undefined") {
                    where = "&where=" + $(this).data('where');
                }
                
                where_extra = "&where_extra=" + $(this).data('where-extra');

                items = "&items="+$(this).data('items');
                action = "&action="+$(this).data('action');

                let parent = null;

                if($(this).closest('[role="dialog"]')[0] != undefined){
                    parent = $(this).closest('[role="dialog"] .modal-content');
                }

                $(this).select2({
                    dropdownParent: parent,
                    language: "es",
                    ajax: {
                        url: _url + '/ajax/get_table_data_ccf?table=' + $(this).data('table') + '&value=' + $(this).data('value') + '&display=' + $(this).data('display') + display2 +display2label + where + where_extra + items +action,
                        delay: 250,
                        dataType: 'json',
                        data: function (params) {
                            return {
                                term: params.term || '',
                                page: params.page || 1,
                                results: params
                            }
                        },
                        cache: true
                    },
                    templateResult: templateResult
                });
                resolve();
            });
        }
    });
}

function modalAnulacion( id_invoice, nombreCliente, tipoDocumento, NumDocumento ){

    $("#anulacionModal").modal({
        backdrop: 'static',
        keyboard: false
    });

    $("#frmAnulacion #nombre_soli").val(nombreCliente)
    $("#frmAnulacion #tipo_doc_soli").val(tipoDocumento).trigger("change");
    $("#frmAnulacion #num_doc_soli").val(NumDocumento)

    $("#id_invoice").val(id_invoice);

    setSelect2CCF();
}

function closeModal(){
    $("#anulacionModal").modal('hide');
    $("#frmAnulacion")[0].reset();
}

function motivoAnulacion( select ){

    let indiceSeleccionado = select.selectedIndex;
    let textoSeleccionado = select.options[indiceSeleccionado].text;
    let value = select.options[select.selectedIndex].value

    textoSeleccionado = ( value == "" ) ? "" : textoSeleccionado;

    document.getElementById("motivo_anulacion").value = textoSeleccionado;

    if( value != "" ){
        if( $("#motivo_anulacion").hasClass("parsley-error") ){
            $("#motivo_anulacion").removeClass("parsley-error").addClass("parsley-success");
        }
    }

    if( value == 1 || value == 3 ){
        $("#dvDteReemplaza").show("slow");
        $("#dte_reemplaza").attr("required", true);
    }
    else{
        $("#dvDteReemplaza").hide("slow");
        $("#dte_reemplaza").attr("required", false);
    }

    $("#dte_reemplaza").val("").trigger("change");
}
function validarCampos() {

    let formulario = document.getElementById('frmAnulacion');
    let camposRequeridos = formulario.querySelectorAll('[required]');

    let camposValidos = true;

    camposRequeridos.forEach(function(campo) {

        if (!campo.value.trim()) {

            campo.classList.add('parsley-error');

            camposValidos = false;

        }
    });

    return camposValidos;
}

function validoDatos( input ){

    let value = input.value

    if( value != "" ){
        input.classList.remove('parsley-error');
        input.classList.add('parsley-success');
    }
}

//FUNCIONES CONTINGENCIA

function modalContingencia( id_invoice ){

    $("#contingenciaModal").modal({
        backdrop: 'static',
        keyboard: false
    });

    $("#id_invoice_con").val(id_invoice);

}

function closeModalContingencia(){
    $("#contingenciaModal").modal('hide');
    $("#frmContingencia")[0].reset();
}

function motivoContingencia( select ){

    let indiceSeleccionado = select.selectedIndex;
    let textoSeleccionado = select.options[indiceSeleccionado].text;
    let value = select.options[select.selectedIndex].value

    textoSeleccionado = ( value == "" ) ? "" : textoSeleccionado;

    document.getElementById("motivo_contingencia").value = textoSeleccionado;

    if( value != "" ){
        if( $("#motivo_contingencia").hasClass("parsley-error") ){
            $("#motivo_contingencia").removeClass("parsley-error").addClass("parsley-success");
        }
    }
}

function validarCamposContingencia() {

    let formulario = document.getElementById('frmContingencia');
    let camposRequeridos = formulario.querySelectorAll('[required]');

    let camposValidos = true;

    camposRequeridos.forEach(function(campo) {

        if (!campo.value.trim()) {

            campo.classList.add('parsley-error');

            camposValidos = false;

        }
    });

    return camposValidos;
}

function re_enviarDTE( id_invoice ){

    Swal.fire({
        title: "¿Estas seguro de obtener sello para este DTE?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, obtener!",
        cancelButtonText: "Cancelar",
        allowOutsideClick: false,
        allowEscapeKey: false
    })
    .then((result) => {
        if( result.isConfirmed ){

            let csrfToken = document.querySelector('input[name="_token"]').value;

            $.ajax({
                url: _url + '/invoices/obtenerSelloHacienda',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                data: {
                    id_invoice
                },
                beforeSend: function () {
                    Swal.fire({
                        title: '<b>Generando DTE</b>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading()
                        },
                    });
                },
                success: function(response) {

                    if( response.result === 'error' ){
                        let  errorMessage;

                        if( Array.isArray(response.message) ){
                            errorMessage = '<ul>';
                            $.each(response.message, function(index, message) {
                                errorMessage += '<li>' + message + '</li>';
                            });
                            errorMessage += '</ul>';
                        }
                        else{
                            errorMessage = response.message;
                        }

                        Swal.fire({
                            title: "Error",
                            html: errorMessage,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            icon: "error"
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
                            title: "Oops! Error en transmisión de DTE",
                            html: errorMessage,
                            icon: "error",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        });
                    }
                    else{
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
                            let url = '/invoices/'+response.data.id;
                            window.open(url, '_blank');
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
