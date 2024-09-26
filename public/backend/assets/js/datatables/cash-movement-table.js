(function ($) {
	"use strict";

	var cash_movement_table = $('#cash-movement-table').DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: "/cash_movement/get_table_data",
			method: "POST",
			data: function (d) {
			  d._token = $('meta[name="csrf-token"]').attr("content");
	  
			  if ($("#cashmov_type").val() != "") {
				d.cashmov_type = $("#cashmov_type").val();
			  }
			  if ($("#company_id").val() != "") {
				d.company_id = $("#company_id").val();
			  }
			  if ($("input[name=date_range]").val() != "") {
				d.date_range = $("input[name=date_range]").val();
			  }
			},
			error: function (request, status, error) {
			  console.log(request.responseText);
			},
		  },
		"columns" : [
			{ data : "cashmov_type", name : "Tipo" },
			{ data : "cashmov_date", name : "Fecha" },
			{ data : "cashmov_concept", name : "Concepto" },
			{ data : "cashmov_value", name : "Valor" },
			{ data : "cash.cash_name", name : "Caja" },
			{ data : "user.name", name : "Usuario" },
			{ data : "action", name : "action" },
		],
		responsive: true,
		"bStateSave": true,
		"bAutoWidth": false,
		"ordering": false,
		"language": {
			"decimal": "",
			"emptyTable": $lang_no_data_found,
			"info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
			"infoEmpty": $lang_showing_0_to_0_of_0_entries,
			"infoFiltered": "(filtered from _MAX_ total entries)",
			"infoPostFix": "",
			"thousands": ",",
			"lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
			"loadingRecords": $lang_loading,
			"processing": $lang_processing,
			"search": $lang_search,
			"zeroRecords": $lang_no_matching_records_found,
			"paginate": {
				"first": $lang_first,
				"last": $lang_last,
				"previous": "<i class='ti-angle-left'></i>",
				"next": "<i class='ti-angle-right'></i>"
			}
		},
		drawCallback: function () {
			$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
		}
	});

	$(".select-filter").on("change", function (e) {
		cash_movement_table.draw();
	  });

	  $("#date_range").daterangepicker({
		autoUpdateInput: false,
		locale: {
		  format: "YYYY-MM-DD",
		  cancelLabel: "Clear",
		},
	  });
	
	  $("#date_range").on("apply.daterangepicker", function (ev, picker) {
		$(this).val(
		  picker.startDate.format("YYYY-MM-DD") +
			" - " +
			picker.endDate.format("YYYY-MM-DD")
		);
		cash_movement_table.draw();
	  });
	
	  $("#date_range").on("cancel.daterangepicker", function (ev, picker) {
		$(this).val("");
		cash_movement_table.draw();
	  });

})(jQuery);