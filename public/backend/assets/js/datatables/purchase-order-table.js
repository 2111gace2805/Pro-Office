(function ($) {
  var purchase_table = $("#purchase-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: _url + "/purchase_orders/get_table_data",
      method: "POST",
      data: function (d) {
        d._token = $('meta[name="csrf-token"]').attr("content");

        if ($("select[name=supplier_id]").val() != "") {
          d.supplier_id = $("select[name=supplier_id]").val();
        }

        if ($("select[name=order_status]").val() != "") {
          d.order_status = JSON.stringify($("select[name=order_status]").val());
        }

        if ($("select[name=payment_status]").val() != "") {
          d.payment_status = JSON.stringify(
            $("select[name=payment_status]").val()
          );
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
      {
        data: "order_date",
        name: "order_date",
      },
      {
        data: "supplier.supplier_name",
        name: "supplier.supplier_name",
        defaultContent: "",
      },
      {
        data: "order_status",
        name: "order_status",
      },
      {
        data: "grand_total",
        name: "grand_total",
      },
      {
        data: "paid",
        name: "paid",
      },
      {
        data: "payment_status",
        name: "payment_status",
      },
      {
        data: "action",
        name: "action",
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
          columns: [0, 1, 2, 3, 4, 5],
        },
        title: "Purchase Orders",
      },
      {
        extend: "copy",
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5],
        },
        title: "Purchase Orders",
      },
      {
        extend: "pdf",
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5],
        },
        title: "Ordenes de compra",
      },
      {
        extend: "print",
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5],
        },
        title: "Purchase Orders",
        customize: function (win) {
          $(win.document.body)
            .css("font-size", "10pt")
            .prepend('<h4 class="text-center">Purchase Orders</h4>');
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
    purchase_table.draw();
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
    purchase_table.draw();
  });

  $("#date_range").on("cancel.daterangepicker", function (ev, picker) {
    $(this).val("");
    purchase_table.draw();
  });
})(jQuery);
