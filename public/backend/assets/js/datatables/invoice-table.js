(function ($) {
  "use strict";

  $(function () {
    var invoice_table = $("#invoice-table").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: _url + "/invoices/get_table_data",
        method: "POST",
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr("content");

          if ($("input[name=invoice_number]").val() != "") {
            d.invoice_number = $("input[name=invoice_number]").val();
          }

          console.log($("select[name=tipodoc_id]").val());

          if ($("select[name=tipodoc_id]").val() != "") {
            d.tipodoc_id = $("select[name=tipodoc_id]").val();
          }

          if ($("select[name=client_id]").val() != "") {
            d.client_id = $("select[name=client_id]").val();
          }

          if ($("select[name=status]").val() != "") {
            d.status = JSON.stringify($("select[name=status]").val());
          }

          if ($("input[name=date_range]").val() != "") {
            d.date_range = $("input[name=date_range]").val();
          }
        },
        error: function (request, status, error) {
          console.log(request.responseText);
        },
      },
      columns: [
        { data: "invoice_number", name: "invoice_number" },
        { data: "tipodoc_nombre", name: "tipodoc_nombre" },
        {
          data: "client.company_name",
          name: "client.company_name",
          defaultContent: "",
        },
        { data: "invoice_date", name: "invoice_date" },
        // { data: "due_date", name: "due_date" },
        { data: "grand_total", name: "grand_total" },
        { data: "status", name: "status" },
        { data: "action", name: "action" },
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
      },
      buttons: [
        {
          extend: "excel",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Invoice",
        },
        {
          extend: "copy",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Invoice",
        },
        {
          extend: "pdf",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Invoice",
        },
        {
          extend: "print",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Invoice",
          customize: function (win) {
            $(win.document.body)
              .css("font-size", "10pt")
              .prepend('<h4 class="text-center">Invoice</h4>');
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

    $("#invoice-number").on("keyup", function (e) {
      invoice_table.draw();
    });

    $(".select-filter").on("change", function (e) {
      invoice_table.draw();
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
      invoice_table.draw();
    });

    $("#date_range").on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
      invoice_table.draw();
    });
  });
})(jQuery);
