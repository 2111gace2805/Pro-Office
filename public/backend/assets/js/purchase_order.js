var total_quantity = 0;
var total_discount = 0;
var total_tax = 0;
var product_total = 0;
var grand_total = 0;
var current_row;

(function($) {
    "use strict";

	update_summary();

	$(document).on('change', '#product', function() {
	    var product_id = $(this).val();
		let sell = true;
		let limit = '';
		let readprice = false;

		if( product_id == '' ){
			return;
		}

	    //if product has already in order table
	    if ($("#order-table > tbody > #product-" + product_id).length > 0) {
			var line = $("#order-table > tbody > #product-" + product_id);
			var quantity = parseFloat($(line).find(".input-quantity").val());
			
			$(line).find(".input-quantity").val(quantity + 1).trigger('change');
			$("#product").val("").trigger('change');;
			
			return;		
	    }
		
		if (typeof $(this).data('nosell') !== "undefined") {
			sell = false;
			readprice = true;
		}

		let input = this;

	    //Ajax request for getting product details
	    $.ajax({
	        method: "GET",
	        url: _url + '/products/get_product/' + product_id,
	        beforeSend: function() {
	            $("#preloader").fadeIn(100);
	        },
	        success: function(data) {
	            $("#preloader").fadeOut(100);
	            var json = JSON.parse(data);
	            var item = json['item'];
	            var product = json['product'];
	            //var tax = json['tax'];

	            if (item['item_type'] == 'product') {
	                var product_price = parseFloat(product['product_cost']);
	            } else if (item['item_type'] == 'service') {
	                var product_price = parseFloat(product['cost']);
	            }
	            
	            //Tax Value calculation
	            var unit_cost = product_price;
	            var sub_total = product_price;
			
				var tax_selector = $("#tax-selector").html();
			
				if (typeof $(input).data('haslimit') !== "undefined") {
					limit = ` max="${json['available_quantity']}"`;
					//item['id'] = product['id'];
				}
				
	            var product_row = `<tr id="product-${item['id']}">
											<td><b>${item['item_name']}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description inputs_tbl" value="${product['description'] != null ? product['description'] : ''}"></td>
											<td class="text-center quantity"><input type="number" name="quantity[]" min="1" class="form-control input-quantity text-center inputs_tbl" ${limit} value="1"></td>
											<td class="text-right unit-cost"><input type="text" name="unit_cost[]" class="form-control input-unit-cost text-right inputs_tbl" ${ readprice ? 'readonly' : '' } value="${unit_cost.toFixed(2)}"></td>`;
				if(sell){
					product_row += `<td class="text-right discount"><input type="text" name="discount[]" class="form-control input-discount text-right inputs_tbl" value="0.00"></td>
					<td class="text-right tax"><select class="form-control selectpicker input-tax" name="tax[${item['id']}][]" title="${$lang_select_tax}" multiple="true">${tax_selector}</select></td>`;
				}

				product_row += `<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(2)}" readonly></td>
						<td class="text-center">
							<button type="button" class="btn btn-danger btn-xs remove-product"><i class='ti-trash'></i></button>
						</td>
						<input type="hidden" name="product_id[]" value="${item['id']}">
						<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
						<input type="hidden" name="product_price[]" class="input-product-price" value="${product_price}">
				</tr>`;
	            $("#order-table > tbody").append(product_row);
	            update_summary();

	            $("#product").val("").trigger('change');
	            $("#service").val("").trigger('change');

				// quitar lineas si precio ya no incluira IVA
				// let selects = $(".input-tax").find('select');
				// for (let i = 0; i < selects.length; i++) {
        		// 	selects[i].value = 1; // 1 TAX IVA
				// 	taxSelected(selects[i]);
				// }
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
		// line_discount = isNaN(line_discount) ? 0 : line_discount;
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
		
		// update_summary();
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

	let order_status = $("#order_status").val();

	if( order_status == 3 ){
		$(".inputs_tbl").attr("readonly", true);

		$("#product").on("select2:opening", function(e) {
			e.preventDefault();
		});

		$("#slOrder_status").on("select2:opening", function(e) {
			e.preventDefault();
		});
	}

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

	total_discount = isNaN(total_discount) ? 0 : total_discount;
	total_tax = isNaN(total_tax) ? 0 : total_tax;

    $("#total-qty").html(total_quantity);
    $("#total-discount").html(_currency + ' ' + total_discount.toFixed(2));
    $("#total-tax").html(_currency + ' ' + total_tax.toFixed(2));
    $("#total").html(_currency + ' ' + product_total.toFixed(2));
    $("#product_total").val(product_total.toFixed(2));
    $("#tax_total").val(total_tax.toFixed(2));

}