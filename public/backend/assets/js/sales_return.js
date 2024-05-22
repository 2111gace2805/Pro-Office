var total_quantity = 0;
var total_discount = 0;
var total_tax = 0;
var product_total = 0;
var grand_total = 0;
var current_row;
	
(function($) {
    "use strict";

	update_summary();

	$(document).on('change', '#selectItems', function() {
	    var invoice_item_id = $(this).val();
		if( invoice_item_id == '' ){
			return;
		}

	    //if product has already in order table
	    if ($("#order-table > tbody > #product-" + invoice_item_id).length > 0) {
			var line = $("#order-table > tbody > #product-" + invoice_item_id);
			var quantity = parseFloat($(line).find(".input-quantity").val());
			
			$(line).find(".input-quantity").val(quantity + 1).trigger('change');
			$("#selectItems").val("").trigger('change');;
			
			return;
	    }


	    //Ajax request for getting product details
	    $.ajax({
	        method: "GET",
	        url: _url + '/sales_returns/get_invoice_item',
			data: {invoice_item_id: invoice_item_id},
	        beforeSend: function() {
	            $("#preloader").fadeIn(100);
	        },
	        success: function(data) {
	            $("#preloader").fadeOut(100);

				
				var tax_selector = $("#tax-selector").html();
				
	            var product_row = `<tr id="product-${data.id}">
											<td><b>${data.product_code}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="${data.description != null ? data.description : ''}"></td>
											<td class="text-center quantity"><input type="number" name="quantity[]" min="1" class="form-control input-quantity text-center" value="${data.quantity}"></td>
											<td class="text-right unit-cost"><input type="text" name="unit_cost[]" class="form-control input-unit-cost text-right" value="${data.unit_cost.toFixed(2)}"></td>
											<td class="text-right discount"><input type="text" name="discount[]" class="form-control input-discount text-right" value="${data.discount}"></td>
											<td class="text-right tax"><select class="form-control selectpicker input-tax" name="tax[${data.id}][]" title="${$lang_select_tax}" multiple="true">${tax_selector}</select></td>
											<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${data.sub_total.toFixed(2)}" readonly></td>
											<td class="text-center">
												<button type="button" class="btn btn-danger btn-xs remove-product"><i class='ti-trash'></i></button>
											</td>
											<input type="hidden" name="product_id[]" value="${data.id}">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="${data.tax_amount}">
											<input type="hidden" name="product_price[]" class="input-product-price" value="${data.product_price}">
									</tr>`;
	            $("#order-table > tbody").append(product_row);

				// descomentar linea si precio ya no incluira IVA
	            //update_summary();

	            $("#product").val("").trigger('change');
	            $("#service").val("").trigger('change');
				
				$('.selectpicker').selectpicker('render');

				// comentar lineas si precio ya no incluira IVA
				let selects = $(".input-tax").find('select');
				for (let i = 0; i < selects.length; i++) {
        			selects[i].value = 1; // 1 TAX IVA
					taxSelected(selects[i], false);
				}
				//

				$('.selectpicker').selectpicker('render');
	        }
	    });

	});


	$(document).on('keyup change', '.input-quantity, .input-unit-cost, .input-discount', function() {
	    var line = $(this).parent().parent();
		// var line_qnty = parseFloat($(line).find('.input-quantity').val());
		// var line_unit_cost = parseFloat($(line).find('.input-unit-cost').val());
		// var line_discount = parseFloat($(line).find('.input-discount').val());
		// var line_total = (line_qnty * line_unit_cost) - line_discount;
		
		// $(line).find('.input-sub-total').val(line_total);
		
		// //Update TAX
		// var product_tax = 0;

		// $.each($(line).find('select.input-tax').val(), function(index, value) {
		// 	var tax_rate = $(line).find('select.input-tax').find('option[value="' + value + '"]').data('tax-rate');
		// 	var tax_type = $(line).find('select.input-tax').find('option[value="' + value + '"]').data('tax-type');

		// 	if (tax_type == 'percent') {
		// 		product_tax += (line_total / 100) * tax_rate;
		// 	} else if (tax_type == 'fixed') {
		// 		product_tax += tax_rate;
		// 	}
		// });
		
		// $(line).find(".input-product-tax").val(product_tax);
		
		//update_summary();

		taxSelected($(line).find('select.input-tax'), false);
	});


	//Click remove product
	$(document).on('click', '.remove-product', function() {
	    $(this).parent().parent().remove();
	    update_summary();
	});
	
	//Select Tax
	$(document).on('change', 'select.input-tax', function(event) {
		event.stopPropagation();
		// var elem = $(this);
		// var line = $(elem).parent().parent().parent();
		// var line_total = $(line).find('.input-sub-total').val();
		// var product_tax = 0;

		// $.each($(this).val(), function(index, value) {
		// 	var tax_rate = $(elem).find('option[value="' + value + '"]').data('tax-rate');
		// 	var tax_type = $(elem).find('option[value="' + value + '"]').data('tax-type');

		// 	if (tax_type == 'percent') {
		// 		product_tax += (line_total / 100) * tax_rate;
		// 	} else if (tax_type == 'fixed') {
		// 		product_tax += tax_rate;
		// 	}
		// });
		
		// $(line).find(".input-product-tax").val(product_tax);
		
		// update_summary();

		taxSelected(this, false);
	});

})(jQuery);	

function update_summary() {
	total_quantity = 0;
    total_discount = 0;
    total_tax = 0;
    product_total = 0;

    $("#order-table > tbody > tr").each(function(index, obj) {
        total_quantity = total_quantity + parseFloat($(this).find(".input-quantity").val());
        total_discount = total_discount + parseFloat($(this).find(".input-discount").val());
        total_tax = total_tax + parseFloat($(this).find(".input-product-tax").val());
        product_total = product_total + parseFloat($(this).find(".input-sub-total").val());
    });

    $("#total-qty").html(total_quantity);
    $("#total-discount").html(_currency + ' ' + total_discount.toFixed(2));
    $("#total-tax").html(_currency + ' ' + total_tax.toFixed(2));
    $("#total").html(_currency + ' ' + product_total.toFixed(2));
    $("#product_total").val(product_total.toFixed(2));
    $("#tax_total").val(total_tax.toFixed(2));

}

$('#invoice_id').on('change', function(e){
	$('#selectItems').attr('data-where_extra', 'invoice_id = '+$('#invoice_id').val());
	$('#selectItems').data('where_extra', 'invoice_id = '+$('#invoice_id').val());
	setSelect2Ajax('.select-selectItems', formatRepo);
});


var formatRepo = function (repo) {
	if (repo.loading) {
	  return repo.text;
	}
  
	var $container = $(
		"<div class='select2-result-repository clearfix'>" +
		  "<div class='select2-result-repository__meta'>" +
			"<div><b>Código: </b><span class='select2-result-repository__code'></span></div>" +
			"<div><b>Descripción: </b><span class='select2-result-repository__title'></span></div>" +
			"<div class='d-flex justify-content-between'><div><b>Cantidad: </b><span class='select2-result-repository__quantity'></span></div>" +
			"<div><b>Precio unitario: </b> <span class='select2-result-repository__unit_cost'><i class='fa fa-flash'></i> </span></div>" +
			"<div><b>Subtotal: </b> <span class='select2-result-repository__sub_total'><i class='fa fa-star'></i> </span></div></div>" +
		  "</div>" +
		"</div>"
	  );
	
	  $container.find(".select2-result-repository__title").text(repo.text);
	  $container.find(".select2-result-repository__quantity").text(repo.quantity);
	  $container.find(".select2-result-repository__unit_cost").append(repo.unit_cost);
	  $container.find(".select2-result-repository__sub_total").append(repo.sub_total);
	  $container.find(".select2-result-repository__code").append(repo.product_code);
	
	  return $container;
  }