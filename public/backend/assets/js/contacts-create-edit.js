$('#pais_id').on('change', function(e){
    if(e.target.value != '9300'){
        $('#depa_id').val('');
        $('#depa_id').trigger('change');
        $('#depa_id').attr('required', false);
        $('#depa_id').attr('readonly', true);
        $('#munidepa_id').val('');
        $('#munidepa_id').trigger('change');
        $('#munidepa_id').attr('required', false);
        $('#munidepa_id').attr('readonly', true);
    }else {
        $('#depa_id').attr('readonly', false);
        $('#munidepa_id').attr('readonly', false);
    }
});

$('#pais_id').trigger('change');

$("#company_nameIpt").alphanum({
    allow:    '&,;.:/-_.,@%&#()*+;:!?/!¡?=$"´´++][}{``^^',
    maxLength: 250
});

$("#tradename").alphanum({
    allow:    '&,;.:/-_.,@%&#()*+;:!?/!¡?=$"´´++][}{``^^',
    maxLength: 150
});

function depa_idChanged(inicio = false){
    if(inicio == false) $('#munidepa_id').val('');
    $('#munidepa_id').attr('data-where_extra', "depa_id = '"+$('#depa_id').val()+"' and muni_status = 'Active'");
    $('#munidepa_id').data('where_extra', "depa_id = '"+$('#depa_id').val()+"' and muni_status = 'Active'");
    setSelect2Ajax();
}
function munidepa_idChanged(inicio = false){
    if(inicio == false) $('#dist_id').val('');
    $('#dist_id').attr('data-where_extra', "munidepa_id = '"+$('#munidepa_id').val()+"'");
    $('#dist_id').data('where_extra', "munidepa_id = '"+$('#munidepa_id').val()+"'");
    setSelect2Ajax('.districts-select');
}

$('#depa_id').on('change', e=>depa_idChanged());
$('#munidepa_id').on('change', e=>munidepa_idChanged());
// $('#dist_id').on('change', function(e){
//     if(!e.target.value) return false;
//     let address = $('[name="address"]').val();
//     let distName = $('#dist_id option:selected').text().toLowerCase().split(' ').map(function(word) {
//         return word.charAt(0).toUpperCase() + word.slice(1);
//     }).join(' ');
//     if(address && !address.endsWith(' '+distName+'.')){
        
//         if (address.endsWith('.')) {
//             $('[name="address"]').val($('[name="address"]').val()+' '+distName+'.');
//         }else{
//             $('[name="address"]').val($('[name="address"]').val()+', '+distName+'.');
//         }
//     }
// });

$(()=>depa_idChanged(true));
$(()=>munidepa_idChanged(true));

//Contacts Create/Edit
if ($('#client_login').is(':checked') == false) {
		
    $("#client_login_card input, #client_login_card select").prop("disabled", true);
}

$(document).on('change', '#client_login', function () {
    if ($(this).is(':checked') == false) {
        $("#client_login_card input, #client_login_card select").prop("disabled", true);
    } else {
        $("#client_login_card input, #client_login_card select").prop("disabled", false);
    }
});

$('#exento_iva').on('change', function(e){
    if(e.target.value == 'si'){
        $('#nosujeto_iva').val('no');
    }
});

$('#nosujeto_iva').on('change', function(e){
    if(e.target.value == 'si'){
        $('#exento_iva').val('no');
    }
});

$(document).ready(function(){
    $('#actie_id').change(function(){
        // Obtenemos el valor seleccionado en el campo de actividad económica
        var selectedOption = $(this).children("option:selected").text();
        // Actualizamos el valor del campo de descripción con el valor seleccionado
        $('#descActividad').val(selectedOption);
    });
});

function mostrarCampos() {
    var tipoitem_id = document.getElementById("tpers_id").value;

    //1 persona natural |  2 persona jurídica
    if (tipoitem_id === "1") {
        CamposNatural();
    } else {
        CamposJuridica();
    }
}

$(document).ready(function() {
    // Cuando es persona natural se pide nombre y apellido, pero en todo se usa el company_name asi que se guarda ahi tambien
    $('#first_name, #lastName').on('input', function() {
        var firstName = $('#first_name').val();
        var lastName = $('#lastName').val();
        var fullName = firstName + ' ' + lastName;
        $('#company_nameIpt').val(fullName);
        $("#contact_nameinp" ).val(fullName);
    });
});


function CamposNatural() {
    $("#company_name").prop("required", false).hide();
    $("#nombreComercial").prop("required", false).hide();
    $("#acti_economica").prop("required", false);
    // $("#nit").prop("required", false).hide();
    $("#dui").prop("required", true).show();
    $("#nrc").prop("required", false);
    $("#contribuyente").prop("required", false).hide();
    $("#exento").prop("required", false).hide();
    $("#sujetoIva").prop("required", false).hide();
    $("#encargado").prop("required", false).hide();
    $("#group").prop("required", false);
    $("#nombre").prop("required", true).show();
    $("#apellido").prop("required", true).show();

    $("#first_name").prop("required", true);
    $("#lastName").prop("required", true);

    $("#company_nameIpt").prop("required", false);
    $("#tradename").prop("required", false);
    $("#actie_id").prop("required", false);
    $("#nit_").prop("required", false);
    // $("#nrc").prop("required", false);
}

function CamposJuridica() {
    $("#company_name").prop("required", true).show();
    $("#nombreComercial").prop("required", true).show();
    $("#acti_economica").prop("required", true);
    $("#nit").prop("required", true).show();
    $("#dui").prop("required", false).hide();
    $("#nrc").prop("required", true);
    $("#contribuyente").prop("required", true).show();
    $("#exento").prop("required", true).show();
    $("#sujetoIva").prop("required", true).show();
    $("#encargado").prop("required", true).show();
    $("#group").prop("required", true);
    $("#nombre").prop("required", false).hide();
    $("#apellido").prop("required", false).hide();

    $("#first_name").prop("required", false);
    $("#lastName").prop("required", false);

    $("#company_nameIpt").prop("required", true);
    $("#tradename").prop("required", true);
    $("#actie_id").prop("required", true);
    $("#nit_").prop("required", true);

}

$(document).ready(function() {
    $('#dui input[type="text"]').inputmask('99999999-9', {placeholder: '00000000-0'});
}); 

$(document).ready(function() {
    mostrarCampos();
});

$(".verificarCliente").on('change', function(){
    verifyClientExist();
});

function verifyClientExist(){
    
    let tipo_perfil = $("#tpers_id").val();
    
    let nrc = $('input[name="nrc"]').val();
    let dui = $('input[name="dui"]').val();
    
    let documento = ( tipo_perfil == 1 ) ? dui : nrc;
    
    if( dui != "" || nrc != "" ){

        let csrfToken = document.querySelector('input[name="_token"]').value;

        $.ajax({
            url: 'verifyClientExist',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            data: {
                tipo_perfil,
                documento
            },
            beforeSend: function () {
                // Swal.fire({
                //     title: '<b>Verificando Caja</b>',
                //     allowOutsideClick: false,
                //     allowEscapeKey: false,
                //     didOpen: () => {
                //         Swal.showLoading()
                //     },
                // });
            },
            success: function(response) {
    
                if( response.result === 'success' ){

                    var contacts = response.contacts;

                    console.log(contacts);
                    var tablaHtml = '<table class="table table-sm" >';
                    tablaHtml += '<thead><tr><th>Cliente</th><th>Acciones</th></tr></thead>';
                    tablaHtml += '<tbody>';

                    $.each(contacts, function(index, contact) {
                        tablaHtml += '<tr>';
                        tablaHtml += '<td>' + contact.company_name + '</td>';
                        tablaHtml += '<td><a href="' + _url + "/contacts/" + contact.id + '/edit" class="btn btn-primary">Editar</a></td>';
                        tablaHtml += '</tr>';
                    });

                    tablaHtml += '</tbody></table>';

                    let message = "Se han encontrado los siguientes clientes que coinciden con el nuevo registro<br>";
                    let question = "<br>¿Desea agregar nuevo cliente?"
    
                    Swal.fire({
                        title: "Oops!",
                        html: message + tablaHtml + question,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        icon: "warning",
                        confirmButtonText: "Sí, guardar!",
                        cancelButtonText: "Cancelar",
                        showCancelButton: true,
                    }).then((result) => {

                        if( result.isDismissed ){
                            let url = '/contacts/';
                            window.location.href = url; 
                        }
                    });
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

}