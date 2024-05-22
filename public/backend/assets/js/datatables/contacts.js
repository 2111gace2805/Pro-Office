(function ($) {
	"use strict";

	$(function () {
		$('#contacts-table').DataTable({
			processing: true,
			serverSide: true,
			ajax: _url + '/contacts/get_table_data',
			"columns": [
				{
					data: "contact_image",
					name: "contact_image"
				},
				{
					data: "tipo_persona.tpers_nombre",
					name: "tpers_id", // Utiliza el nombre correcto de la columna
					defaultContent: "<i>No establecido</i>"
				},
				{
					data: "company_name",
					name: "company_name"
				},
				{
					data: "contact_email",
					name: "contact_email"
				},
				{
					data: "contact_phone",
					name: "contact_phone"
				},
				{
					data: "group.name",
					name: "group.name"
				},
				{
					data: "action",
					name: "action"
				},
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
	});

})(jQuery);