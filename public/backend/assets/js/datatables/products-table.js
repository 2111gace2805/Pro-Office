(function ($) {
  "use strict";

  $(function () {

    var products_table = $("#tabla-productos-new").DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      ajax: {
        url: _url + "/products/get_table_data",
        method: "POST",
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr("content");

          d.item_type = $("#item_type").val();
          
          console.log(d);
        },
        error: function (request, status, error) {
          console.log(request.responseText);
        },
      },
      columns: [
        { data: "product_code", name: "product_code" },
        { data: "image", name: "image" },
        { data: "item_name", name: "item_name" },
        { data: "description", name: "description" },
        { data: "product_cost", name: "product_cost" },
        { data: "product_price", name: "product_price" },
        { data: "product_stock", name: "product_stock" },
        { data: "action", name: "action", className: "text-center" },
      ],
      responsive: true,
      bStateSave: true,
      bAutoWidth: false,
      ordering: false,
      dom:
        "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4 text-center'l><'col-sm-12 col-md-4 text-right'f>>" +
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
            columns: [0, 2, 3, 4, 5, 6],
          },
          title: "Products",
        },
        {
          extend: "copy",
          exportOptions: {
            columns: [0, 2, 3, 4, 5, 6],
          },
          title: "Products",
        },
        {
          extend: "pdf",
          exportOptions: {
            columns: [0, 2, 3, 4, 5, 6],
          },
          title: "Products",
        },
        {
          extend: "print",
          exportOptions: {
            columns: [0, 2, 3, 4, 5, 6],
          },
          title: "Products",
          customize: function (win) {
            $(win.document.body)
              .css("font-size", "10pt")
              .prepend('<h4 class="text-center">Products</h4>');
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

    $("#item_type").on("change", function (e) {
      products_table.draw();
    });
  });
})(jQuery);
