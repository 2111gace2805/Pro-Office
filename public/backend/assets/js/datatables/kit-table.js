(function ($) {
  "use strict";

  $(function () {
    var kit_table = $("#kit-table").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: _url + "/kits/get_table_data",
        method: "POST",
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr("content");

          if ($("input[name=code]").val() != "") {
            d.code = $("input[name=code]").val();
          }

          if ($("select[name=name]").val() != "") {
            d.name = $("select[name=name]").val();
          }
        },
        error: function (request, status, error) {
          console.log(request.responseText);
        },
      },
      columns: [
        { data: "code", name: "name" },
        { data: "name", name: "name"},
        { data: "products", name: "products" },
        { data: "amount", name: "amount" },
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

    $("#code").on("keyup", function (e) {
      kit_table.draw();
    });

    $("#name").on("keyup", function (e) {
      kit_table.draw();
    });


  });
})(jQuery);
