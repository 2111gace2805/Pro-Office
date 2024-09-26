(function ($) {
    "use strict";

    let Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    $(document).on('click', '#btnAdd', function () {

        let payment_analysis            = $("#payment_analysis").val();
        let code_product_institution    = $("#code_product_institution").val();
        let delivery_number             = $("#delivery_number").val();
        let product_origin              = $("#product_origin").val();
        let offered_expiry              = $("#offered_expiry").val();
        let product_lot                 = $("#product_lot").val();
        let expires                     = $("#expires").val();
        let manufacture                 = $("#manufacture").val();
        let analysis_certificate        = $("#analysis_certificate").val();
        let product_delivery_company    = $("#product_delivery_company").val();
        let product_id                  = $("#product").val();

        let inputs = [
            payment_analysis,
            code_product_institution,
            delivery_number,
            product_origin,
            offered_expiry,
            product_lot,
            expires,
            manufacture,
            product_id
        ];

        if( inputs.filter(input => input === '').length > 0 ){

            Toast.fire({
                icon: "error",
                title: "Complete todos los detalles de la nota"
            });

            return;
        }

        //if product has already in order table
        if( $("#order-table > tbody > #product-" + product_id).length > 0 ){
            let line = $("#order-table > tbody > #product-" + product_id);
            let quantity = parseFloat($(line).find(".input-quantity").val());

            $(line).find(".input-quantity").val(quantity + 1).trigger('change');
            $("#product").val("").trigger('change');;

            return;
        }

        //Ajax request for getting product details
        $.ajax({
            method: "GET",
            url: _url + '/products/get_product/' + product_id,
            beforeSend: function () {
                $("#preloader").fadeIn(100);
            },
            success: function (data) {

                $("#preloader").fadeOut(100);
                let json    = JSON.parse(data);
                let item    = json['item'];
                let product = json['product'];
                let product_brand = json['brand_name'];
                let lastRow = $('#order-table tbody tr:last td:first input')[0];
                let linea   = 1;

                if( lastRow != undefined ){
                    linea = (isNaN(parseInt(lastRow.value))?1:parseInt(lastRow.value))+1;
                }

                let product_row = `<tr class="items" id="product-${item['id']}">
											<td style="width: 95px"><input type="number" name="line[]" class="form-control input-line" value="${linea}"></td>
                                            <td width="8%" class="input-product-code" title="Producto original: ${product['original']}">${product['product_code']}</td>
											<td class="description">
                                                <textarea cols="10" rows="15" name="product_description[]" class="form-control input-description">${item['item_name']}</textarea>
                                            </td>
											<td class="text-center quantity" title="Cantidad máxima: ${json['available_quantity']}"><input type="number" name="quantity[]" min="1" class="form-control float-field input-quantity text-center" value="1"></td>
											<td class="text-center samples" title="Cantidad máxima: ${json['available_quantity']}"><input type="number" name="samples[]" min="1" class="form-control float-field input-samples text-center" value="1"></td>
											<td width="10%"  class="text-center">
												<button type="button" class="btn btn-info btn-sm view-product m-1" onclick="showModalDetails(${item['id']})" title="Ver detalles ítem"><i class='ti-eye'></i></button>
												<button type="button" class="btn btn-danger btn-sm remove-product m-1" title="Remover ítem"><i class='ti-trash'></i></button>
											</td>
											<input type="hidden" name="product_id[]" value="${item['id']}">
											<input type="hidden" name="product_stock[]" class="input-product-stock" value="${json['available_quantity']}">

											<input type="hidden" name="payment_analysis[]" class="input-payment_analysis" value="${payment_analysis}">
											<input type="hidden" name="code_product_institution[]" class="input-code_product_institution" value="${code_product_institution}">
											<input type="hidden" name="delivery_number[]" class="input-delivery_number" value="${delivery_number}">
											<input type="hidden" name="product_origin[]" class="input-product_origin" value="${product_origin}">
											<input type="hidden" name="offered_expiry[]" class="input-offered_expiry" value="${offered_expiry}">
											<input type="hidden" name="product_lot[]" class="input-product_lot" value="${product_lot}">
											<input type="hidden" name="expires[]" class="input-expires" value="${expires}">
											<input type="hidden" name="manufacture[]" class="input-manufacture" value="${manufacture}">
											<input type="hidden" name="analysis_certificate[]" class="input-analysis_certificate" value="${analysis_certificate}">
											<input type="hidden" name="product_delivery_company[]" class="input-product_delivery_company" value="${product_delivery_company}">
											<input type="hidden" name="product_brand[]" class="input-product_brand" value="${product_brand}">
									</tr>`;
                $("#order-table > tbody").append(product_row);

                $("#product").val("").trigger('change');
                $(".details").val("");

                $('[title]').tooltip({trigger : 'hover'});
                $('[title]').on('click', function(){
                    $(this).tooltip('hide');
                });

                update_summary();

                Toast.fire({
                    icon: "success",
                    title: "Item agregado correctamente"
                });
            }
        });

    });

     //Click remove product
    $(document).on('click', '.remove-product', function () {
        $(this).parent().parent().remove();

        let $filas = $('tbody tr.items');

        $filas.each(function(index) {
            $(this).find('td input[name="line[]"]').val(index + 1);
        });
    });

    $(document).on('keyup change', '.input-quantity', function (e) {

        let line = $(this).parent().parent();

        let stock       = parseFloat($(line).find('.input-product-stock').val());
        let line_qnty   = parseFloat($(line).find('.input-quantity').val());

        if( stock < line_qnty ){
            $.toast({
                heading: 'Stock máximo alcanzado',
                text: 'El stock máximo es '+stock,
                hideAfter: false,
                icon: 'error',
                position: 'bottom-left',
            });
            e.target.value = stock;
        }

        update_summary();
    });

    $(document).on('keyup change', '.input-samples', function (e) {

        let line = $(this).parent().parent();

        let stock       = parseFloat($(line).find('.input-product-stock').val());
        let line_qnty   = parseFloat($(line).find('.input-quantity').val());
        let line_samples   = parseFloat($(line).find('.input-quantity').val());

        stock = stock - line_qnty;

        if( stock < line_samples ){
            $.toast({
                heading: 'Stock máximo alcanzado',
                text: 'El stock máximo es '+stock,
                hideAfter: false,
                icon: 'error',
                position: 'bottom-left',
            });
            e.target.value = stock;
        }

        update_summary();
    });

})(jQuery);

let dynamicModal;

function validoDatos( input ){

    let value = input.value

    if( value != "" ){
        input.classList.remove('parsley-error');
        input.classList.add('parsley-success');
    }
}

function showModalDetails( productID ){

    $("#dynamicModal").remove();

    let product = `product-${productID}`;

    let tr = $("#"+product);

    let line                        = tr.find(".input-line").val();
    let analisis                    = tr.find(".input-payment_analysis").val();
    let code_product_institution    = tr.find(".input-code_product_institution").val();
    let product_code                = tr.find(".input-product-code").html();
    let description                 = tr.find(".input-description").html();
    let quantity                    = tr.find(".input-quantity").val();
    let samples                     = tr.find(".input-samples").val();
    let delivery_number             = tr.find(".input-delivery_number").val();
    let product_brand               = tr.find(".input-product_brand").val();
    let product_origin              = tr.find(".input-product_origin").val();
    let offered_expiry              = tr.find(".input-offered_expiry").val();
    let product_lot                 = tr.find(".input-product_lot").val();
    let expires                     = tr.find(".input-expires").val();
    let manufacture                 = tr.find(".input-manufacture").val();
    let analysis_certificate        = tr.find(".input-analysis_certificate").val();
    let product_delivery_company    = tr.find(".input-product_delivery_company").val();

    let modalHTML = `
        <div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dynamicModalLabel">Detalles de item</h5>
                        <button type="button" class="btn btn-close" onclick="closeModal()" aria-label="Close">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Número de Renglón</label>
                                        <input type="text" class="form-control" value="${line}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Pago de análisis</label>
                                        <input type="text" class="form-control" value="${analisis}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Código del producto de la Institución</label>
                                        <input type="text" class="form-control" value="${code_product_institution}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Referencia del producto interna</label>
                                        <input type="text" class="form-control" value="${product_code}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Descripción del producto</label>
                                        <input type="text" class="form-control" value="${description}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Cantidad solicitada</label>
                                        <input type="text" class="form-control" value="${quantity}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Cantidad de muestras</label>
                                        <input type="text" class="form-control" value="${samples}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Número de entrega</label>
                                        <input type="text" class="form-control" value="${delivery_number}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Marca del producto</label>
                                        <input type="text" class="form-control" value="${product_brand}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Origen del producto</label>
                                        <input type="text" class="form-control" value="${product_origin}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Vencimiento ofertado</label>
                                        <input type="text" class="form-control" value="${offered_expiry}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Lote del producto</label>
                                        <input type="text" class="form-control" value="${product_lot}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Vencimiento</label>
                                        <input type="text" class="form-control" value="${expires}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Manufactura</label>
                                        <input type="text" class="form-control" value="${manufacture}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Certificado de análisis</label>
                                        <input type="text" class="form-control" value="${analysis_certificate}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Empresa por la que se está entregando el producto</label>
                                        <input type="text" class="form-control" value="${product_delivery_company}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $("body").append(modalHTML);

    // Inicializar el modal
    dynamicModal = new bootstrap.Modal(document.getElementById('dynamicModal'), {
        keyboard: false,
        backdrop: "static"
    });

    // Mostrar el modal
    dynamicModal.show();

    $('#dynamicModal').on('hidden.bs.modal', function () {
        console.log('entra');
        $(this).remove();
    });
}

function closeModal() {

    if( dynamicModal ){
        dynamicModal.hide();
    }
}

function saveInvoice( event ){

    event.preventDefault();

    let valido = validarCampos();

    if( valido ){

        Swal.fire({
            title: "¿Estas seguro de guardar?",
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, guardar!",
            cancelButtonText: "Cancelar",
            allowOutsideClick: false,
            allowEscapeKey: false
        })
        .then((result) => {

            if( result.isConfirmed ){
    
                let csrfToken = document.querySelector('input[name="_token"]').value;
                let form = document.getElementById('frmOrderNotes');
                let data = new FormData(form);
    
                $.ajax({
                    url: '/order_notes',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: data,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        Swal.fire({
                            title: '<b>Guardando nota de pedido...</b>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        });
                    },
                    success: function(response) {

                        if( response.result === 'error' ){    
                            Swal.fire({
                                title: "Error",
                                text: response.message,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                icon: "error"
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
                                let url = '/order_notes/'+response.data.id;
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

function validarCampos() {

    let formulario = document.getElementById('frmOrderNotes');
    let camposRequeridos = formulario.querySelectorAll('[required]');

    let camposValidos = true;
    let primerCampoInvalido = null;

    camposRequeridos.forEach(function(campo) {

        if( !campo.value.trim() ){

            campo.classList.add('parsley-error');

            camposValidos = false;
            
            if( !primerCampoInvalido ){
                primerCampoInvalido = campo;
            }
        }
    });

    if( !camposValidos && primerCampoInvalido ){
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    return camposValidos;
}

function update_summary() {

    total_quantity  = 0;
    total_samples   = 0;

    $("#order-table > tbody > tr").each(function (index, obj) {
        total_quantity  = total_quantity + parseFloat($(this).find(".input-quantity").val());
        total_samples   = total_samples + parseFloat($(this).find(".input-samples").val());
    });

    $("#total-qty").html(total_quantity);
    $("#total-samples").html(total_samples);
}


function setWarehouses( inicio = false ){

    if( inicio == false ){
        $('#warehouse_id').val('');
    }

    $('#warehouse_id').data('where_extra', "client_id = '"+$('#client_id').val()+"'");

    setSelect2Ajax();
}

$('#client_id').on('change', e=>setWarehouses());

$(()=>setWarehouses(true));