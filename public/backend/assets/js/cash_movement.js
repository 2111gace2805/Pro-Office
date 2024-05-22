jQuery(function(){
    $('#company_id').on('change', e=>setSelectDataAjax('cashs', 'cash_id', 'cash_name', '#cash_id', {showSelectOption: false, where_extra: 'company_id='+e.target.value}));
    // $('#prodgrp_id').on('change', e=>getResumenProductos());

    $('#company_id').trigger('change');
    // $('#prodgrp_id').trigger('change');
    getResumenVentas();

    $(document).on('change', '#cashmov_date, #company_id, #cash_id', (e)=>getResumenVentas());
});

function getResumenVentas(){
    if (!$('#cashmov_date') || !$('#company_id')) {
        return;
    }
    $.ajax({
        method: "GET",
        url: '/cash_movement_get_resumen_ventas',
        data: {
            invoice_date: $('#cashmov_date').val(),
            company_id: $('#company_id').val()
        },
        dataType: 'json',
        beforeSend: function () {
            $("#preloader").css("display", "block");
        }, 
        success: function (data) {
            $("#preloader").css("display", "none");

            let rows = ``;
            data.sales_groups.forEach(element => {
                rows+= `<tr>
                            <td>${element.prodgrp_name}</td>
                            <td>${element.quantity}</td>
                            <td>$ ${parseFloat(element.subtotal).toFixed(2)}</td>
                        </tr>`;
            });
            if (rows == ``) {
                rows = `<tr><td colspan="100%" class="text-center">Sin registros</td></tr>`;
            }
            $('#groups_table tbody').html(rows);
            
            rows = ``;
            let sumTotal = 0;
            data.sales_pay_way.forEach(element => {
                rows+= `<tr>
                            <td>${element.forp_nombre}</td>
                            <td>$ ${parseFloat(element.grand_total).toFixed(2)}</td>
                        </tr>`;
                sumTotal += parseFloat(element.grand_total);
            });
            rows+= `<tr>
                            <td><b>TOTAL</b></td>
                            <td><b>$ ${sumTotal.toFixed(2)}</b></td>
                        </tr>`;
            if (rows == ``) {
                rows = `<tr><td colspan="100%" class="text-center">Sin registros</td></tr>`;
            }
            
            $('#formas_pago tbody').html(rows);
        },
        error: function (request, status, error) {
            console.log(request);
        }
    });
}