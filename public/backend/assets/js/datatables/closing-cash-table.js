(function ($) {
  var closing_cash_table = $("#closing_cash_table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: _url + "/cash_movement/get_table_data_closing_cash",
      method: "POST",
      data: function (d) {
        d._token = $('meta[name="csrf-token"]').attr("content");

        if ($("#prodgrp_id").val() != "") {
          d.prodgrp_id = $("#prodgrp_id").val();
        }
        if ($("input[name=cashmov_date]").val() != "") {
          d.invoice_date = $("input[name=cashmov_date]").val();
        }
        if ($("#company_id").val() != "") {
          d.company_id = $("#company_id").val();
        }
      },
      error: function (request, status, error) {
        console.log(request.responseText);
      },
    },
    columns: [
      {
        data: "quantity",
        name: "Cantidad",
      },
      {
        data: "description",
        name: "Producto",
      },
      {
        data: "sub_total",
        name: "Subtotal",
      },
    ],
    responsive: true,
    bStateSave: true,
    bAutoWidth: false,
    ordering: false,
    searching: false,
    dom:
      "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-right'l>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    language: {
      decimal: "",
      emptyTable: $lang_no_data_found,
      info:
        $lang_showing +
        " _START_ " +
        $lang_to +
        " _END_ " +
        $lang_of +
        " _TOTAL_ " +
        $lang_entries,
      infoEmpty: $lang_showing_0_to_0_of_0_entries,
      infoFiltered: "(filtered from _MAX_ total entries)",
      infoPostFix: "",
      thousands: ",",
      lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
      loadingRecords: $lang_loading,
      processing: $lang_processing,
      search: $lang_search,
      zeroRecords: $lang_no_matching_records_found,
      paginate: {
        first: $lang_first,
        last: $lang_last,
        previous: "<i class='ti-angle-left'></i>",
        next: "<i class='ti-angle-right'></i>",
      },
      buttons: {
        copy: $lang_copy,
        excel: $lang_excel,
        pdf: $lang_pdf,
        print: $lang_print,
      },
    },
    buttons: [
      {
        extend: "excel",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
      },
      {
        extend: "copy",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
      },
      {
        extend: "pdf",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
      },
      {
        extend: "print",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
        customize: function (win) {
          $(win.document.body)
            .css("font-size", "10pt")
            .prepend('<h4 class="text-center">Ventas</h4>');
          $(win.document.body)
            .find("table")
            .addClass("compact")
            .css("font-size", "inherit");
        },
      },
    ],
    drawCallback: function () {
      $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
    },
  });
 
  var closing_cash_invoices_table = $("#closing_cash_invoices_table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: _url + "/cash_movement/get_table_data_closing_cash_invoices",
      method: "POST",
      data: function (d) {
        d._token = $('meta[name="csrf-token"]').attr("content");

        if ($("input[name=cashmov_date]").val() != "") {
          d.invoice_date = $("input[name=cashmov_date]").val();
        }
        if ($("#company_id").val() != "") {
          d.company_id = $("#company_id").val();
        }
      },
      error: function (request, status, error) {
        console.log(request.responseText);
      },
    },
    columns: [
      {
        data: "invoice_number",
        name: "Factura",
      },
      {
        data: "company_name",
        name: "Cliente",
      },
      {
        data: "grand_total",
        name: "Total",
      },
    ],
    responsive: true,
    bStateSave: true,
    bAutoWidth: false,
    ordering: false,
    searching: false,
    dom:
      "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-right'l>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    language: {
      decimal: "",
      emptyTable: $lang_no_data_found,
      info:
        $lang_showing +
        " _START_ " +
        $lang_to +
        " _END_ " +
        $lang_of +
        " _TOTAL_ " +
        $lang_entries,
      infoEmpty: $lang_showing_0_to_0_of_0_entries,
      infoFiltered: "(filtered from _MAX_ total entries)",
      infoPostFix: "",
      thousands: ",",
      lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
      loadingRecords: $lang_loading,
      processing: $lang_processing,
      search: $lang_search,
      zeroRecords: $lang_no_matching_records_found,
      paginate: {
        first: $lang_first,
        last: $lang_last,
        previous: "<i class='ti-angle-left'></i>",
        next: "<i class='ti-angle-right'></i>",
      },
      buttons: {
        copy: $lang_copy,
        excel: $lang_excel,
        pdf: $lang_pdf,
        print: $lang_print,
      },
    },
    buttons: [
      {
        extend: "excel",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
      },
      {
        extend: "copy",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
      },
      {
        extend: "pdf",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
      },
      {
        extend: "print",
        exportOptions: {
          columns: [0, 1, 2],
        },
        title: "Ventas",
        customize: function (win) {
          $(win.document.body)
            .css("font-size", "10pt")
            .prepend('<h4 class="text-center">Ventas</h4>');
          $(win.document.body)
            .find("table")
            .addClass("compact")
            .css("font-size", "inherit");
        },
      },
    ],
    drawCallback: function () {
      $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
    },
  });

  $(".select-filter").on("change", function (e) {
    closing_cash_table.draw();
    closing_cash_invoices_table.draw();
  });
})(jQuery);
