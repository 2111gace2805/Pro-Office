var total_quantity = 0;
var total_discount = 0;
var total_tax = 0;
var product_total = 0;
var grand_total = 0;
var current_row;
var selectedContact = null;
var percepcion_iva = 0;
var retencion_iva = 0;
var retencion_isr = 0;
var techo_percepcion_iva = 0;
var techo_retencion_iva = 0;
var techo_retencion_isr = 0;
var editing = false;
var gran_contribuyente = 'no';

(function ($) {
    "use strict";

    //update_summary();

    verificarCaja();

    $(document).on('change', '#product,#service,#kit', function () {
        var product_id = $(this).val();

        let id = $(this).attr('id');


        if (product_id == '') {
            return;
        }

        let tipo_dte = $("#tipodoc_id").val();
        let dte_relacionado = $("#doc_relacionado").val();

        if( tipo_dte == "05" || tipo_dte == "06" ){
            if( dte_relacionado == "" ){
                Swal.fire('Oops!', 'Debe agregar un DTE relacionado al item', 'warning')
                $(this).val("").trigger("change");
                return;
            }
        }

        let kit = ( id == "kit" ) ? 1 : 0;

        //if product has already in order table
        if( kit == 0 ){
            if($("#order-table > tbody > #product-" + product_id).length > 0) {
    
                console.log('entra aqui?');
                var line = $("#order-table > tbody > #product-" + product_id);
                var quantity = parseFloat($(line).find(".input-quantity").val() || 0);
    
                $(line).find(".input-quantity").val(quantity + 1).trigger('change');
    
                $("#product").val("").trigger('change');
                $("#service").val("").trigger('change');
                $("#kit").val("").trigger('change');
    
                return;
            }
        }
        else{
            if ($("#order-table > tbody > #kit-" + product_id).length > 0) {
                var line = $("#order-table > tbody > #kit-" + product_id);
                var quantity = parseFloat($(line).find(".input-quantity").val() || 0);
    
                $(line).find(".input-quantity").val(quantity + 1).trigger('change');
    
                $("#product").val("").trigger('change');
                $("#service").val("").trigger('change');
                $("#kit").val("").trigger('change');
    
                return;
            }
        }



        //Ajax request for getting product details
        $.ajax({
            method: "GET",
            url: _url + '/products/get_product/' + product_id,
            data: {
                kit: kit
            },
            beforeSend: function () {
                $("#preloader").fadeIn(100);
            },
            success: function (data) {
                $("#preloader").fadeOut(100);
                var json = JSON.parse(data);
                var item = json['item'];
                var product = json['product'];
                //var tax = json['tax'];

                // if (item['item_type'] == 'product') {
                //     var product_price = parseFloat(product['product_price']);

                // } else if (item['item_type'] == 'service') {
                //     var product_price = parseFloat(product['product_price']);
                // }
                var product_price = parseFloat(product['product_price']);

                //Tax Value calculation
                var unit_cost = product_price;
                var sub_total = product_price;


                var tax_selector = $("#tax-selector").html();

                var lastRow = $('#order-table tbody tr:last td:first input')[0];
                let linea = 1;

                if(lastRow != undefined){
                    linea = (isNaN(parseInt(lastRow.value))?1:parseInt(lastRow.value))+1;
                }

                let tipodoc_id = $('#tipodoc_id').val();

                let refisc_id = $('#refisc_id').val();

                let row_id = ( kit == 1 ) ? "kit-" : "product-";

                var product_row = `<tr class="items" id="${row_id}${item['id']}">
											<td style="width: 95px"><input type="number" name="line[]" class="form-control input-line" value="${linea}"></td>
                                            <td width="8%" class="input-product-code d-none" title="Producto original: ${product['original']}">${product['product_code']}</td>
											<td class="description">
                                                <textarea cols="10" rows="15" name="product_description[]" class="form-control input-description">${item['item_name']} - ${product['product_code']}</textarea>
                                            </td>
											<td class="text-center quantity" title="Cantidad máxima: ${json['available_quantity']}"><input type="text" name="quantity[]" min="1" class="form-control float-field numeric input-quantity text-center" value="1"></td>
											<td class="text-right unit-cost"  style="width:143px"><input type="text" name="unit_cost[]" class="form-control float-field numeric2 input-unit-cost text-right" value="${unit_cost.toFixed(6)}"></td>
											<td class="text-right discount d-none"><input type="text" name="discount[]" class="form-control float-field input-discount text-right" value="0.00"></td>
											<td class="text-right tax" width="11%"><select class="form-control selectpicker input-tax selectIva" name="tax[${item['id']}][]" title="${$lang_select_tax}" multiple="true">${tax_selector}</select></td>
											<td class="text-right sub-total" style="width:143px"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(6)}" readonly></td>
											<td class="text-center" style="width:10px">
												<button type="button" class="btn btn-danger btn-sm remove-product m-1" title="Remover ítem"><i class='ti-trash'></i></button>
												<button type="button" class="btn btn-info btn-sm boton-descargo d-none" onclick="modalInfoDescargo(${item['id']})" title="Información de anexo de descargo"><i class='ti-list'></i></button>
                                                ${getModalDescargo(item['id'])}
											</td>
											<input type="hidden" name="product_id[]" value="${item['id']}">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
											<input type="hidden" name="product_stock[]" class="input-product-stock" value="${json['available_quantity']}">
											<input type="hidden" name="product_price[]" class="input-product-price" value="${product_price}">
											<input type="hidden" id="precio_original" value="${product_price}">
											<input type="hidden" class="cambio_precio" value="0">
											<input type="hidden" name="dtes_relacionados[]" class="input-dte-relacionados" value="${dte_relacionado}">
											<input type="hidden" name="kit[]" class="input-kit" value="${kit}">
									</tr>`;
                $("#order-table > tbody").append(product_row);
                update_summary();

                $("#product").val("").trigger('change');
                $("#service").val("").trigger('change');
                $("#kit").val("").trigger('change');
                $("#doc_relacionado").val("").trigger('change');
                if( $("#tipodoc_id").val() == '14' ){
                    $('.selectIva').prop('disabled', true);
                }
                $('.selectpicker').selectpicker('render');

                tipodoc_idChanged($('#tipodoc_id').val(), false);
                // $("#order-table").tableDnD();
                $('[title]').tooltip({trigger : 'hover'});
                $('[title]').on('click', function(){
                    $(this).tooltip('hide');
                });

                if( $("#tipodoc_id").val() != '14' ){
                    // $('.selectIva').prop('disabled', true);
                    $('.selectpicker').selectpicker('refresh');
                }

                $('.selectIva').css('pointer-events', 'none');

                if( $("#tipodoc_id").val() == '05' || $("#tipodoc_id").val() == '06' ){

                    $("tbody > tr.items td.unit-cost > input").removeClass("float-field input-unit-cost");
                    $("tbody > tr.items td.unit-cost > input").addClass("nc_change_price");
                }
                else{
                    $("tbody > tr.items td.unit-cost > input").removeClass("float-field input-unit-cost");
                    $("tbody > tr.items td.unit-cost > input").addClass("all_change_price");
                }

                $(".numeric").numeric("integer");

                $(".numeric2").numeric({
                    maxDecimalPlaces: 6,
                    allowThouSep: false
                });
            }
        });

    });

    $(document).on('keyup change', '.input-quantity, .input-unit-cost, .input-discount', function (e) {
        var line = $(this).parent().parent();

        var stock = parseFloat($(line).find('.input-product-stock').val());
        var line_qnty = parseFloat($(line).find('.input-quantity').val() || 0);

        if (stock < line_qnty) {
            $.toast({
                heading: 'Stock máximo alcanzado',
                text: 'El stock máximo es '+stock,
                hideAfter: false,
                icon: 'error',
                position: 'bottom-left',
            });
            e.target.value = stock;
        }
        let selectedTipodoc = $('#tipodoc_id').val();

        taxSelected($(line).find('select.input-tax'), isTaxSelected(selectedTipodoc), selectedTipodoc);
    });


    $(document).on('input keyup change', '.nc_change_price', function (e) {

        let disponible = $("#nc_disponible_ccf").val();
        let precio = $(this).val();
        var line = $(this).parent().parent();
        let precio_original = line.find("#precio_original").val();

        if( parseFloat( precio ) >  parseFloat( disponible ) ){
            e.preventDefault();

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

            Toast.fire({
                icon: "error",
                title: "Precio unitario no puede superar el disponible del Crédito Fiscal"
            });

            // Restaurar el valor anterior
            $(this).val($(this).data('previous-value') || parseFloat( precio_original ).toFixed(2));

            $(line).find('.input-sub-total').val( parseFloat( precio_original ).toFixed(6) );
            $(line).find('.input-product-price').val( parseFloat( precio_original ).toFixed(6) );
            
        }
        else{
    
            $(line).find('.input-sub-total').val( precio );
            $(line).find('.input-product-price').val( precio );
    
            let selectedTipodoc = $('#tipodoc_id').val();
    
            taxSelected($(line).find('select.input-tax'), selectedTipodoc == '05' || selectedTipodoc == '06' ? false:true);
        }

    });


    $(document).on('input keyup change', '.all_change_price', function (e) {
        
        let selectedTipodoc = $('#tipodoc_id').val();

        let precio  = $(this).val();
        precio = ( precio == "" ) ? 0 : precio;
        let line    = $(this).parent().parent();
        let total = 0;
        if (selectedTipodoc == '01') { // 01 = FE
            total = precio;
            $(line).find('.input-product-price').val( precio/1.13 );
        }else{
            total = precio * 1.13;
            $(line).find('.input-product-price').val( precio );
        }

        $(line).find('.input-sub-total').val( total );

        // conitnuar aqui en revisar el calcilo de tax en factura consumido rfinal


        $(line).find('.cambio_precio').val( 1 );

        let isTipodocValid = ['01', '03', '04', '05', '06', '14'].includes(selectedTipodoc);

        // taxSelected($(line).find('select.input-tax'), !isTipodocValid);
        taxSelected($(line).find('select.input-tax'), selectedTipodoc=='01'?true:false);

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
    
    // on change renta retenida
    $(document).on('blur', '#isr_retenido', ()=>update_summary(true));

    //Select Tax
    $(document).on('change', 'select.input-tax', function (event) {
        event.stopPropagation();
        let selectedTipodoc = $('#tipodoc_id').val();
        taxSelected(this, isTaxSelected(selectedTipodoc));
    });

    $(document).on('change', '#client_id', function () {
        var client_id = $(this).val();
        if (client_id == '') {
            return;
        }

        //Ajax request for getting product details
        $.ajax({
            method: "GET",
            dataType: 'json',
            url: _url + '/contacts/get_contact/' + client_id,
            beforeSend: function () {
                $("#preloader").fadeIn(100);
            },
            success: function (contact) {
                $("#preloader").fadeOut(100);
                $('#tpers_id_invoice').val(contact.tpers_id);
                selectedContact = contact;
                if(selectedContact.actie_id > 0){
                    $('#actie_id_invoice').val(selectedContact.actie_id);
                    $('#actie_id_invoice').trigger('change');
                    $('#desc_actividad').val($('#actie_id_invoice').find(':selected').html());
                }
                if($('#tipodoc_id').val() == '03' || $('#tipodoc_id').val() == '04'){ // 03 es CCFE; 04 = NOTA REMISION
                    $('#num_documento').val(selectedContact.nit);
                    $('#desc_actividad').parent().parent().addClass('d-none');
                }else{
                    if (editing == false) {
                        $('#tdocrec_id').val('');
                        $('#num_documento').val('');
                    }
                    $('#actie_id_invoice').val('');
                    
                    $('#actie_id_invoice').trigger('change');
                    // $('#actie_id_invoice').parent().parent().addClass('d-none');
                    $('#desc_actividad').parent().parent().removeClass('d-none');
                }

                if (contact.tpers_id == 1) {
                    $('#tdocrec_id').val(13); 
                    $('#num_documento').val(selectedContact.dui);
                } else {
                    $('#tdocrec_id').val(36); 
                    $('#num_documento').val(selectedContact.nit);
                }

                if( contact.id == 47 ){
                    $('#tdocrec_id').val("");
                    $('#nombre_comercial').attr("readonly", true);
                    $('#tdocrec_id').attr("readonly", true);
                    $('#tdocrec_id').css("pointer-events", "none");
                    $('#num_documento').attr("readonly", true);
                }

                $('#nombre_comercial').val(selectedContact.tradename);
                if (selectedContact.pais != null) {
                    $('#pais_id_invoice').append($('<option>', {
                        value: selectedContact.pais.pais_id,
                        text: selectedContact.pais.pais_nombre,
                        selected: 'selected'
                    }));
                }
                if (selectedContact.departamento != null) {
                    $('#depa_id_invoice').append($('<option>', {
                        value: selectedContact.departamento.depa_id,
                        text: selectedContact.departamento.depa_nombre,
                        selected: 'selected'
                    }));
                }
                if (selectedContact.municipio != null) {
                    $('#munidepa_id_invoice').append($('<option>', {
                        value: selectedContact.municipio.munidepa_id,
                        text: selectedContact.municipio.muni_nombre,
                        selected: 'selected'
                    }));
                }
                if (selectedContact.actie_id != null && selectedContact.actividad_economica.actie_nombre != null) {
                    $('#actie_id_invoice').append($('<option>', {
                        value: selectedContact.actie_id,
                        text: selectedContact.actividad_economica.actie_nombre,
                        selected: 'selected'
                    }));
                }
                
                $('#telefono').val(selectedContact.contact_phone);
                $('#correo').val(selectedContact.contact_email);
                $('#complemento').val(selectedContact.address);

                $('#plazo_id_invoice').val(selectedContact.plazo_id);
                $('#periodo').val(selectedContact.payment_period);
                
                let tipoDocumento = $("#tipodoc_id").val();
                tipodoc_idChanged(tipoDocumento, false);
                // console.log(contact);
                if( tipoDocumento == '05' || tipoDocumento == '06' ){
                    setSelect2CCF();
                }

                if( selectedContact.gran_contribuyente == 'si' ){
                    $("#dvLicitacion").show('slow');
                }
                else{
                    $("#dvLicitacion").hide('slow');
                }
                if( selectedContact.exento_iva == 'si' ){
                    $("#chkVentaExenta").prop("checked", true);
                    $('#chkVentaExenta').prop('disabled', true);
                }
                else{
                    $("#chkVentaExenta").prop("checked", false);
                }
            },
            error: function(error){
                console.log(error);
            }
        });

    });

    $("#name_invoice").alphanum({
        allow:    '&,;.:/-_.,@%&#()*+;:!?/!¡?=$"´´++][}{``^^',
        maxLength: 250
    });

    $("#nombre_comercial").alphanum({
        maxLength: 150
    });

    $("#seller_code").select2({
        maximumSelectionLength: 2
    });

    $("#tipodoc_id").on("change", function (){

        let val = $(this).val();

        let dvRetencionRenta = $("#dvRetencionRenta");
        if( val == "04" || val == "11" ){
            dvRetencionRenta.addClass("d-none");
        }
        else{
            dvRetencionRenta.removeClass("d-none");
        }
    });

    $("#chkRetencionRenta").on("change", function (){
        update_summary();
    });

})(jQuery);

jQuery(function(){
    update_summary();
});

function update_summary(changedByUser=false) {
    total_quantity = 0;
    total_discount = 0;
    total_tax = 0;
    product_total = 0;
    // esta variable sirve para subtotal sin impuestos para calcular retencion y percepcion de iva
    let product_total_sin_iva = 0;
    let subtotal = 0; // totalGravado menos descuentos
    let cambio_precio = 0;
    let retencion_renta = 0;
    let discount_g = 0;
    let discount_g_sin_iva = 0;
    let general_discount = 0;
    let generalDiscountValue = parseFloat($('#general_discount_value').val());
    general_discount = isNaN(generalDiscountValue)?0:generalDiscountValue;
    let generalDiscountType = $('[name="general_discount_type"]:checked').val()==undefined?'Fixed':$('[name="general_discount_type"]:checked').val();
    

    $("#order-table > tbody > tr").each(function (index, obj) {
        total_quantity = total_quantity + parseFloat($(this).find(".input-quantity").val() || 0);
        total_discount = total_discount + parseFloat($(this).find(".input-discount").val());
        total_tax = total_tax + parseFloat($(this).find(".input-product-tax").val());
        product_total = product_total + parseFloat($(this).find(".input-sub-total").val() || 0);

        // esta variable sirve para subtotal sin impuestos para calcular retencion y percepcion de iva
        product_total_sin_iva += (parseFloat($(this).find(".input-quantity").val() || 0 )*parseFloat($(this).find(".input-product-price").val())-parseFloat($(this).find(".input-discount").val()));

        cambio_precio = $(this).find(".cambio_precio").val();
    });

    if( $('#tipodoc_id').val()== '01' ){
        if( cambio_precio == 1 ){
            //product_total_sin_iva = product_total_sin_iva / 1.13;
        }
    }

    // console.log({total_quantity});



    // INICIO DESCUENTOS
    let tipo_dte    = $('#tipodoc_id').val();
    let dtes_iva    = ['03', '04', '05', '06'];
    let dtes_no_iva = ['01', '11', '14'];
    
    if( general_discount > 0 ){

        // if( dtes_no_iva.includes(tipo_dte) ){
        //     discount_g = general_discount *  product_total / 100;
        // }
        // else if( dtes_iva.includes(tipo_dte) ){
        //     product_w_iva       = product_total + total_tax;
        //     discount_g          = general_discount *  product_w_iva / 100;
        //     discount_g_sin_iva  = general_discount *  product_total / 100;
        // }

        // discount_g = general_discount *  product_total / 100;
        // if(tipo_dte == '01'){
        //     discount_g = general_discount *  product_total / 100;
        // }else{
            discount_g = generalDiscountType=='Percentage'? (general_discount *  product_total_sin_iva / 100):general_discount;
        //}
        
    }
    else{
        discount_g = 0;
    }

    subtotal = product_total - discount_g;
    product_total_sin_iva = product_total_sin_iva - discount_g;
    
    $("#subtotal_2").html(subtotal.toFixed(2));
    $("#_subtotal_2").val(subtotal.toFixed(2));
    if(tipo_dte != '01' && total_tax>0){
        total_tax = subtotal*0.13;
    }

    if( dtes_no_iva.includes(tipo_dte) ){
        $("#th_discount_general").html(discount_g.toFixed(2));
    }
    else if( dtes_iva.includes(tipo_dte) ){
        $("#th_discount_general").html(discount_g.toFixed(2));
    }
    $("#discount_general").val(discount_g.toFixed(2));
    // FIN DESCUENTOS






    let sumatorias_impuestos = subtotal;

    // if( $('#tipodoc_id').val() == '03' || $('#tipodoc_id').val() == '04' || $('#tipodoc_id').val() == '05' || $('#tipodoc_id').val() == '06' ){
    if( $('#tipodoc_id').val() != '01'){
        // let total_tax_fix = total_tax.toFixed(2)
        // sumatorias_impuestos = parseFloat( sumatorias_impuestos )  + parseFloat( total_tax_fix );
        sumatorias_impuestos = parseFloat( sumatorias_impuestos )  + parseFloat( total_tax );
    }


    $("#total-qty").html(total_quantity);
    $("#total-discount").html(_currency + ' ' + total_discount.toFixed(2));
    $("#sumatorias_impuestos").html(_currency + ' ' + sumatorias_impuestos.toFixed(2));
    $("#total-tax").html(_currency + ' ' + total_tax.toFixed(2));
    $("#total").html(_currency + ' ' + product_total.toFixed(6));


    $("#product_total").val(product_total.toFixed(2));
    $("#tax_total").val(total_tax.toFixed(2));

    // de agregar descuento global exento o descuento global no sujeto, restar esos descuentos al product_total_sin_iva

    if (changedByUser == false && $('#venta_licitacion').val()!='1') {
        if(gran_contribuyente == 'no' && selectedContact != null && selectedContact.gran_contribuyente == 'si' && product_total_sin_iva >= techo_retencion_iva){
            $('#chkIvaRetenido').prop('checked', true);
        }else {
            $('#chkIvaRetenido').prop('checked', false);
        }   
    }

    // RETENCION IVA
    // if (gran_contribuyente == 'no' && selectedContact != null && selectedContact.gran_contribuyente == 'si') {
    if ($("#chkIvaRetenido").is(':checked') && ($('#tipodoc_id').val()== '01' || $('#tipodoc_id').val()== '03' || $('#tipodoc_id').val()== '05' || $('#tipodoc_id').val()== '06')) {
        // if (($('#tipodoc_id').val()== '01' || $('#tipodoc_id').val()== '03' || $('#tipodoc_id').val()== '05' || $('#tipodoc_id').val()== '06') && product_total_sin_iva >= techo_retencion_iva) {
        // if (($('#tipodoc_id').val()== '01' || $('#tipodoc_id').val()== '03' || $('#tipodoc_id').val()== '05' || $('#tipodoc_id').val()== '06')) {
            if ($('#tipodoc_id').val() != '01') {
                $("#iva-retenido").html((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
                $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
            }else{
                $("#iva-retenido").html(((subtotal/1.13)*(retencion_iva/100)).toFixed(2));
                $("#iva_retenido").val(((subtotal/1.13)*(retencion_iva/100)).toFixed(2));
            }
        // }else{
        //     $("#iva-retenido").html(0);
        //     $("#iva_retenido").val(0);
        // }
    }else{
        $("#iva-retenido").html(_currency + ' ' + '0.00');
        $("#iva_retenido").val(0.00);
    }

    // if( $("#chkIvaRetenido").is(':checked') ){
    //     if( $('#tipodoc_id').val()== '03' || $('#tipodoc_id').val()== '05' || $('#tipodoc_id').val()== '06' ){
    //         $("#iva-retenido").html(_currency + ' ' + (product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //         $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //     }

    //     if( $('#tipodoc_id').val()== '01' ){
    //         if( cambio_precio == 1 ){
    //             product_total_sin_iva = product_total_sin_iva / 1.13;
                
    //             $("#iva-retenido").html(_currency + ' ' + (product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //             $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //         }
    //         else{
    //             $("#iva-retenido").html(_currency + ' ' + (product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //             $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //         }
    //     }
    // }
    // else{
    //     if( $('#tipodoc_id').val()== '01' || $('#tipodoc_id').val()== '03' || $('#tipodoc_id').val()== '04' || $('#tipodoc_id').val()== '05' || $('#tipodoc_id').val()== '06' ){
    //         $("#iva-retenido").html(_currency + ' ' + '0.00');
    //         $("#iva_retenido").val(0.00);
    //     }
    // }

    //RETENCION DE RENTA
    if( $('#tipodoc_id').val() != "04" || $('#tipodoc_id').val() != "11"  ){
        if( $("#chkRetencionRenta").is(':checked') ){

            $("#retencion-renta").html(_currency + ' ' + (product_total_sin_iva*(10/100)).toFixed(2));
            $("#retencion_renta").val((product_total_sin_iva*(10/100)).toFixed(2));
  
        }
        else{
            $("#retencion-renta").html(_currency + ' ' + '0.00');
            $("#retencion_renta").val(0.00);
        }
    }

    // if( selectedContact != null && selectedContact.gran_contribuyente == 'si' && !$("#chkIvaRetenido").is(':checked') ){
    //     if( $('#tipodoc_id').val()== '03' || $('#tipodoc_id').val()== '05' || $('#tipodoc_id').val()== '06' ){

    //         if( parseFloat( product_total_sin_iva ) >= 100 ){
    //             $("#iva-retenido").html(_currency + ' ' + (product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //             $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //         }

    //     }

    //     if( $('#tipodoc_id').val()== '01' ){

    //         if( cambio_precio == 1 ){
    //             //product_total_sin_iva = product_total_sin_iva / 1.13;

    //             if( parseFloat( product_total_sin_iva ) >= 100 ){
    //                 $("#iva-retenido").html(_currency + ' ' + (product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //                 $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //             }
    //         }
    //         else{
    //             if( parseFloat( product_total_sin_iva ) >= 100 ){
    //                 $("#iva-retenido").html(_currency + ' ' + (product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //                 $("#iva_retenido").val((product_total_sin_iva*(retencion_iva/100)).toFixed(2));
    //             }
    //         }
    //     }
    // }
    
    // PERCEPCION IVA
    if (gran_contribuyente == 'si' && selectedContact != null && selectedContact.gran_contribuyente == 'no') {
        if (($('#tipodoc_id').val() == '03' || $('#tipodoc_id').val()== '04') && product_total_sin_iva >= techo_percepcion_iva) {
            $("#iva-percibido").html(_currency + ' ' + (product_total_sin_iva*(percepcion_iva/100)).toFixed(2));
            $("#iva_percibido").val((product_total_sin_iva*(percepcion_iva/100)).toFixed(2));
        }
    }
    
    // RETENCION ISR
    // if (gran_contribuyente == 'no' && selectedContact != null && selectedContact.gran_contribuyente == 'si' && changedByUser == false) {
    //     if (($('#tipodoc_id').val()== '01' || $('#tipodoc_id').val()== '03')) {
    //         if (product_total_sin_iva <= techo_retencion_isr) {
    //             $("#isr_retenido").val((product_total_sin_iva*(retencion_isr/100)).toFixed(2));
    //         }else if (product_total_sin_iva > techo_retencion_isr) {
    //             $("#isr_retenido").val((product_total_sin_iva*((retencion_isr/100)*2)).toFixed(2));
    //         }
    //     }
    // }

    
    if( $('#tipodoc_id').val() == '03' || $('#tipodoc_id').val() == '04' || $('#tipodoc_id').val() == '05' || $('#tipodoc_id').val() == '06' ){
        let valueGrandTotal = product_total + parseFloat($("#tax_total").val()) - parseFloat($('#iva_retenido').val()) - parseFloat($('#retencion_renta').val()) + parseFloat($('#iva_percibido').val())+parseFloat($('#isr_retenido').val());
        $("#grand_total").val(valueGrandTotal.toFixed(2));
        $("#grand-total").html(_currency + ' ' + valueGrandTotal.toFixed(2));

        // inicio descuentos
        let price_general_discount = valueGrandTotal - discount_g
        $("#grand-total").html(price_general_discount.toFixed(2));
        $("#grand_total").val(price_general_discount.toFixed(2));
        // let total_iva = (product_total + parseFloat($("#tax_total").val())) / 1.13;
        // total_tax = total_iva * 0.13;
        // $("#total-tax").html(total_tax.toFixed(2));
        // $("#tax_total").val(total_tax.toFixed(2));
        // fin descuentos
    }else{
        let valueGrandTotal = product_total - parseFloat($('#iva_retenido').val()) - parseFloat($('#retencion_renta').val()) + parseFloat($('#isr_retenido').val());
        $("#grand_total").val(valueGrandTotal.toFixed(2));
        $("#grand-total").html(_currency + ' ' + valueGrandTotal.toFixed(2));

        // inicio descuentos
        let price_general_discount = valueGrandTotal - discount_g;
        $("#grand-total").html(price_general_discount.toFixed(2));
        $("#grand_total").val(price_general_discount.toFixed(2));
        // fin descuentos
    }
    
}


$('#chkIvaRetenido').on("change", function() {
    update_summary(true);
});

$('#venta_licitacion').on("change", function() {
    let val = $(this).val();

    console.log({val});

    if( val == 1 ){
        // $('#chkIvaRetenido').attr('checked', true);
        $('#chkIvaRetenido').prop('checked', true);
        update_summary();
    }
    else{
        // $('#chkIvaRetenido').attr('checked', false);
        $('#chkIvaRetenido').prop('checked', false);
        update_summary();
    }
});

$('#chkVentaExenta').on("change", function() {

    let inputs = $("tbody > tr.items td.unit-cost > input");

    if( $(this).is(':checked') ){
        $(".selectIva").val('').trigger('change');

        if( $('#tipodoc_id').val() == '01' ){

            inputs.each( function(){
    
                let value = $(this).val();
                value = parseFloat(value);
                let iva = 1.13;
    
                let total_sin_iva = value / iva;
    
                $(this).val(total_sin_iva.toFixed(2))
            });
        }

    }
    else{
        $(".selectIva").val('1').trigger('change');

        if( $('#tipodoc_id').val() == '01' ){
    
            inputs.each( function(){
        
                let value = $(this).val();
                value = parseFloat(value);
                let iva = 1.13;
        
                let total_sin_iva = value * iva;
        
                $(this).val(total_sin_iva.toFixed(2))
            });
        }
    }

    update_summary();

});


// TIPO DE DOCUMENTO SELECCIONADO DEL RECEPTOR
$('#tdocrec_id').on('change', (event)=>tdocrec_idChanged(event.target.value));
function tdocrec_idChanged(value){
    let valor = value;
    if(selectedContact == null){
        return;
    }
    switch(valor.toString()){
        case "36":
            $('#num_documento').val(selectedContact.nit);
            // $('#num_documento_2').val(selectedContact.nit);
        break;
        case "13":
            $('#num_documento').val(selectedContact.dui);
            // $('#num_documento_2').val(selectedContact.dui);
        break;
        default:
            $('#num_documento').val('');
            // $('#num_documento_2').val('');
        break;
    }
}

// TIPO DE DOCUMENTO SELECCIONADO
$('#tipodoc_id').on('change', e=>tipodoc_idChanged(e.target.value));
function tipodoc_idChanged(value, limpiarCliente = true){

    if (!value) {
        return;
    }
    
    let selected = value;
    let selects = $(".input-tax").find('select');
    
    
    if( selected == '05' || selected == '06' ){
        let params = new URLSearchParams(location.search);
        let id_ccf = params.get('id_ccf');
        if( id_ccf == null ){
            $("#dvDteRelacionado").show('slow');
        }
    }
    else{
        $("#dvDteRelacionado").hide('slow');
    }

    if( selected == '11' || selected == '14' ){
        $("#dvVentaExcenta").hide('slow')
    }
    else{
        $("#dvVentaExcenta").show('slow')
    }
    
    if( selected == '11' ){
        $("#dvIncoterms").show('slow');
    }
    else{
        $("#dvIncoterms").hide('slow');
    }
    for (let i = 0; i < selects.length; i++) {
        // 11 tipodoc_id FEX
        if (selected == '11') {
            selects[i].value = 2; // 2 TAX EXPORTACION
            taxSelected(selects[i]);
        }
        else{
            if( (selectedContact != null && selectedContact.exento_iva == 'no' && selectedContact.nosujeto_iva == 'no' && !$("#chkVentaExenta").is(':checked')) || selectedContact == null && !$("#chkVentaExenta").is(':checked') ){
                if(selected == '14'){
                    selects[i].value = 0;
                    $('.selectIva').prop('disabled', true);
                }
                else{
                    $('.selectIva').prop('disabled', false);
                    selects[i].value = 1; // 1 TAX IVA
                }
                
                // 03 es ccf; 04 NOTA REMISION;
                taxSelected(selects[i], isTaxSelected(selected));
            }
        }
        
    }

    if( limpiarCliente ){
        // 03 ES CCFE Y 11 ES FEXE Y 05 ES NOTA DE CREDITO; 04 NOTA DE REMISION

        // if(value == '03' || value == '11' || value == '05'){
        //     $('#client_id').data('where_extra', 'tpers_id = 2');
        // }
        // else{
        //     $('#client_id').data('where_extra', 'tpers_id = 1');
        // }
        // 03 es CCFE; 04 es NOTA REMISION
        if(value == '03' || value == '04' || value == '05' || value == '06'){
            $('#tdocrec_id').val('36'); // se coloca 36 que es NIT
            $('#tdocrec_id').attr('readonly', true);
            $('#tdocrec_id').css('pointer-events', 'none');
            // $('#actie_id_invoice').parent().parent().removeClass('d-none');
            // $('#client_id').data('display2', 'nit');
            // $('#client_id').data('display2label', 'NIT');
        }
        else{
            $('#tdocrec_id').val('13'); // se coloca 13 que es DUI
            $('#tdocrec_id').attr('readonly', false);
            $('#tdocrec_id').css('pointer-events', 'all');
            // $('#actie_id_invoice').parent().parent().addClass('d-none');
            // $('#client_id').data('display2', 'dui');
        }

        $('#client_id').val('');
        $('#client_id').trigger('change');
        $('#tpers_id_invoice').val('');
        $('#num_documento').val('');
        // $('#num_documento_2').val('');
        $('#pais_id_invoice').val('');
        $('#telefono').val('');
        $('#correo').val('');
        $('#complemento').val('');
        $('#depa_id_invoice').val('');
        $('#munidepa_id_invoice').val('');
        $('#actie_id_invoice').val('');
        $('#actie_id_invoice').trigger('change');
        $('#desc_actividad').val('');
        $('#nombre_comercial').val('');

        setSelect2Ajax();
        setSelect2CCF();

        if(editing == false){
            $.ajax({
                method: "GET",
                url: "/invoices/get-number/"+value,
                beforeSend: function () {
                    $("#preloader").css("display", "block");
                }, 
                success: function (data) {
                    data = data.toString().padStart(5, '0');
                    $('#invoice_number').val(data);
                    $('#invoice_starting_number').val(data);
                    if( $("#invoice_number").hasClass('parsley-error') ){
                        $("#invoice_number").removeClass('parsley-error')
                    }
                },
                error: function (error) {
                    Swal.fire({
                        title: '¡Error!',
                        icon: 'error',
                        text: 'Hubo un error'
                    });
                    console.log(error);
                },
                complete: function(){
                    $("#preloader").css("display", "none");
                }
            });
        }
    
        $('.selectpicker').selectpicker('refresh');
        switch (value) {
            case '01': // FE
                $('#tr-impuesto').addClass('d-none');
                // $('#tr-iva-percibido').addClass('d-none');
                $('#tr-iva-retenido').removeClass('d-none');
                $('#dvIvaRetenido').removeClass('d-none');
                // $('.boton-descargo').addClass('d-none');
                $('#regi_id').parent().parent().addClass('d-none');
                $('#refisc_id').parent().parent().addClass('d-none');
                $('#dividerRecinto').addClass('d-none');
                // $('#no_anexo_descargo').parent().parent().addClass('d-none');
                break;
            case '11': // FEXE
                // $('#tr-iva-percibido').addClass('d-none');
                $('#tr-impuesto').addClass('d-none');
                $('#tr-iva-retenido').addClass('d-none');
                $('#dvIvaRetenido').addClass('d-none');
                // $('.boton-descargo').removeClass('d-none');
                $('#regi_id').parent().parent().removeClass('d-none');
                $('#refisc_id').parent().parent().removeClass('d-none');
                $('#dividerRecinto').removeClass('d-none');
                // $('#no_anexo_descargo').parent().parent().removeClass('d-none');
                break;
            case '14': // FSE
                $('#tr-impuesto').addClass('d-none');
                // $('#tr-iva-percibido').addClass('d-none');
                $('#tr-iva-retenido').removeClass('d-none');
                $('#dvIvaRetenido').removeClass('d-none');
                // $('.boton-descargo').addClass('d-none');
                $('#regi_id').parent().parent().addClass('d-none');
                $('#refisc_id').parent().parent().addClass('d-none');
                $('#dividerRecinto').addClass('d-none');
                // $('#no_anexo_descargo').parent().parent().addClass('d-none');
                break;
            default: // CCF
                // if(gran_contribuyente == '0'){
                //     $('#tr-iva-percibido').removeClass('d-none');
                // }
                $('#tr-impuesto').removeClass('d-none');
                $('#tr-iva-retenido').removeClass('d-none');
                $('#dvIvaRetenido').removeClass('d-none');
                // $('.boton-descargo').addClass('d-none');
                $('#regi_id').parent().parent().addClass('d-none');
                $('#refisc_id').parent().parent().addClass('d-none');
                $('#dividerRecinto').addClass('d-none');
                // $('#no_anexo_descargo').parent().parent().addClass('d-none');
                break;
        }

        $(".all_change_price").trigger("change");
    }
}

// jQuery(function(){
//     $("#order-table").tableDnD();
// });

function getModalDescargo(product_id){
    return ``;
}

function modalInfoDescargo(product_id){
    $('#modalDescargo'+product_id).modal('show');
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
                let form = document.getElementById('frmInvoices');
                let data = new FormData(form);

                let selected = $("#tipodoc_id").val();

                if( selected == '05' || selected == '06' ){
                    let params = new URLSearchParams(location.search);
                    let id_ccf = params.get('id_ccf');
                    if( id_ccf != null ){
                        data.append('id_ccf_rel', id_ccf);

                        let disponible = $("#nc_disponible_ccf").val();
                        disponible = disponible * 1.13;
                        disponible = disponible.toFixed(2)
                        let total = $("#grand_total").val();

                        if( parseFloat( total ) > parseFloat( disponible ) ){
                            Swal.fire({
                                title: "Oops!",
                                text: "No es posible generar Nota de crédito, Total a pagar supera el disponible del Crédito Fiscal",
                                icon: "error",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            });
                            return;
                        }
                    }
                }

                if( selected == '01' && $("#client_id").val() == 47 ){
                    let total = $("#grand_total").val();

                    if( parseFloat( total ) > 100 ){
                        Swal.fire({
                            title: "Oops!",
                            text: "No es posible generar Factura con Clientes Varios ya que el monto supera los $100.",
                            icon: "error",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        });
                        return;
                    }
                }

                if( selected == '01' || selected == '03' ){
                    let params = new URLSearchParams(location.search);
                    let id_nr = params.get('id_nr');
                    if( id_nr != null ){
                        data.append('id_nr_rel', id_nr);
                    }
                }


                if( selected == '04' ){
                    let params = new URLSearchParams(location.search);
                    let id_nota_p = params.get('id_nota_p');
                    if( id_nota_p != null ){
                        data.append('id_nota_p', id_nota_p);
                    }
                }
    
                $.ajax({
                    url: '/invoices',
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

    let formulario = document.getElementById('frmInvoices');
    let camposRequeridos = formulario.querySelectorAll('[required]');

    let camposValidos = true;
    let primerCampoInvalido = null;

    camposRequeridos.forEach(function(campo) {

        if (!campo.value.trim()) {

            campo.classList.add('parsley-error');

            camposValidos = false;
            
            if( !primerCampoInvalido ){
                primerCampoInvalido = campo;
            }
        }
    });

    if (!camposValidos && primerCampoInvalido) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    return camposValidos;
}

function validoDatos( input ){

    let value = input.value

    if( value != "" ){
        input.classList.remove('parsley-error');
        input.classList.add('parsley-success');
    }
}

function getNombre( select ){
    let textoSeleccionado = select.options[select.selectedIndex].text;    
    $("#name_invoice").val(textoSeleccionado);
}

function isTaxSelected(selectedTipodoc) {

    //03 CCF; 04 NOTA REMISION; 05 NOTA CREDITO; 06 NOTA DEBITO; 14 SUJETO EXCLUIDO
    const nonTaxTipodocs = ['03', '04', '05', '06', '14'];
    return !nonTaxTipodocs.includes(selectedTipodoc);
}

function verificarCaja(){

    $.ajax({
        url: _url + '/invoices/verificarCajaAbierta',
        method: 'GET',
        processData: false,
        contentType: false,
        beforeSend: function () {
            Swal.fire({
                title: '<b>Verificando Caja</b>',
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = '/cash/';
                        window.location.href = url; 
                    }
                });
            }
            else{
                Swal.close();
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

function validarCamposReturn() {

    let formulario = document.getElementById('formReturn');
    let camposRequeridos = formulario.querySelectorAll('[required]');

    let camposValidos = true;
    let primerCampoInvalido = null;

    camposRequeridos.forEach(function(campo) {

        if (!campo.value.trim()) {

            campo.classList.add('parsley-error');

            camposValidos = false;
            
            if( !primerCampoInvalido ){
                primerCampoInvalido = campo;
            }
        }
    });

    if (!camposValidos && primerCampoInvalido) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    return camposValidos;
}

function saveReturn( event ){

    event.preventDefault();

    let valido = validarCamposReturn();

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
                let form = document.getElementById('formReturn');
                let data = new FormData(form);
    
                $.ajax({
                    url: '/sales_returns',
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
                            title: '<b>Realizando devolución</b>',
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
                                title: "Oops! Error en devolución",
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

$('#general_discount_id').on('change', function(e){
    let general_discount_id = e.target.value;

    let type = $('#general_discount_id option:selected').data('type');
    let value = $('#general_discount_id option:selected').data('value');
    $(`#${type}Option`).prop('checked', true);

    if (general_discount_id === 'other') {
        $('#dvGeneralDiscountOther').show('slow');
    }else{
        $('#dvGeneralDiscountOther').hide('slow');
    }

    if (general_discount_id == '' || general_discount_id === 'other') {
        $('#general_discount_value').val(0);
    }else{
        $('#general_discount_value').val(value);
    }
    $('#general_discount_value').trigger('change');
});
$('#general_discount_value').on('change', function(e){
    update_summary();
});
$('[name="general_discount_type"]').on('change', function(e){
    // var selectedValue = $(this).val();
    // console.log('Opción seleccionada: ' + selectedValue);
    update_summary();
});