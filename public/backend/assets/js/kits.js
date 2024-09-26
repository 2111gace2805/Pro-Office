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

    $(document).on('change', '#product', function () {

        let product_id = $(this).val();

        if (product_id == '') {
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

                let product_row = `<tr class="items" id="product-${item['id']}">
                                            <td width="8%" class="input-product-code" title="Producto original: ${product['original']}">${item['item_name']}</td>
											<td class="text-center quantity" title="Cantidad máxima: ${json['available_quantity']}"><input type="number" name="quantity[]" min="1" class="form-control float-field input-quantity text-center" value="1"></td>
											<td width="10%"  class="text-center">
												<button type="button" class="btn btn-danger btn-sm remove-product m-1" title="Remover ítem"><i class='ti-trash'></i></button>
											</td>
											<input type="hidden" name="product_id[]" value="${item['id']}">
											<input type="hidden" name="product_stock[]" class="input-product-stock" value="${json['available_quantity']}">
									</tr>`;
                $("#order-table > tbody").append(product_row);

                $("#product").val("").trigger('change');

                $('[title]').tooltip({trigger : 'hover'});
                $('[title]').on('click', function(){
                    $(this).tooltip('hide');
                });

                update_summary();
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

        update_summary();
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

})(jQuery);

function validoDatos( input ){

    let value = input.value

    if( value != "" ){
        input.classList.remove('parsley-error');
        input.classList.add('parsley-success');
    }
}

function saveInvoice( event, update = false, id = 0 ){

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
                let form = document.getElementById('frmKits');
                let data = new FormData(form);

                let url = ( update ) ? '/kits/' + id : '/kits';
            
                if( update ){
                    data.append('_method', 'PUT');
                }
    
                $.ajax({
                    url: url,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: data,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        Swal.fire({
                            title: '<b>Guardando Kit...</b>',
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
                                let url = '/kits';
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

    let formulario = document.getElementById('frmKits');
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

    $("#order-table > tbody > tr").each(function (index, obj) {
        total_quantity  = total_quantity + parseFloat($(this).find(".input-quantity").val());
    });

    $("#total-qty").html(total_quantity);
}