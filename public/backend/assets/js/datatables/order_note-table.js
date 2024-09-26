(function ($) {
  "use strict";

  $(function () {
    var order_table = $("#order_note-table").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: _url + "/order_notes/get_table_data",
        method: "POST",
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr("content");

          if ($("input[name=order_number]").val() != "") {
            d.order_number = $("input[name=order_number]").val();
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
        { data: "order_number", name: "order_number" },
        {
          data: "client.contact_name",
          name: "client.contact_name",
          defaultContent: "",
        },
        { data: "num_contract", name: "num_contract" },
        { data: "deliver_date_contract", name: "deliver_date_contract" },
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
          title: "Nota de pedido",
        },
        {
          extend: "copy",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Nota de pedido",
        },
        {
          extend: "pdf",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Nota de pedido",
        },
        {
          extend: "print",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Nota de pedido",
          customize: function (win) {
            $(win.document.body)
              .css("font-size", "10pt")
              .prepend('<h4 class="text-center">Nota de pedido</h4>');
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

    $("#order_number").on("keyup", function (e) {
      order_table.draw();
    });

    $(".select-filter").on("change", function (e) {
      order_table.draw();
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
      order_table.draw();
    });

    $("#date_range").on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
      order_table.draw();
    });
  });
})(jQuery);
