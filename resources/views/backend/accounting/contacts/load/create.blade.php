@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Agregar Clientes</h4>
            </div>

            <div class="card-body">
                <div class="alert alert-primary" role="alert">
                    Desde este apartado puede realizar la carga inicial de sus ciebtes. Puede cargar los clientes agregandolos a la plantilla brindada previamente según el formato indicado.
                </div>
                <form id="frmProducts" enctype="multipart/form-data">
                    @csrf

                    <div class="col-xl-12 col-md-12 col-sm-12">
                        <div class="card shadow-none" style="border:1px solid #e0e0e0;">
                            <div class="card-body">
                                <div class="card-title text-black"><strong>Plantilla Excel</strong></div>
                                <input type="file" class="form-control dropify" name="contacts_excel" id="contacts_excel" data-allowed-file-extensions="xlsx xls">
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button onclick="uploadProducts(event)" class="btn btn-primary mt-4">Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function uploadProducts(e){

        e.preventDefault();

        Swal.fire({
            title: "¿Estas seguro de cargar productos?",
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, cargar!",
            cancelButtonText: "Cancelar",
            allowOutsideClick: false,
            allowEscapeKey: false
        })
        .then((result) => {
            if( result.isConfirmed ){

                var file = $('#contacts_excel');
    
                if( file.prop('files').length === 0 ){
                    Swal.fire({
                        title: "Oops!",
                        text: "No se ah cargado la plantilla correspondiente",
                        icon: "error",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });
                    return;
                } 

                let data = new FormData($("#frmProducts")[0]);
    
                $.ajax({
                    url: "{{ route('contacts.load_excel') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: data,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        Swal.fire({
                            title: '<b>Cargando clientes...</b>',
                            html: '<h5>Por favor no cierre esta ventana</h5>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        });
                    },
                    success: function(response) {

                        if( response.result == "success"){
                            Swal.fire({
                                title: "Realizado!",
                                text: response.message,
                                icon: "success",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                timer: 3000,
                                showConfirmButton: false,
                            })

                            setTimeout(() => {
                                let url = '/contacts/';
                                window.location.href = url; 
                            }, 3000);
                        }
                        else{
                            Swal.fire({
                                title: "Error",
                                html: response.message,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                icon: "error"
                            });
                        }


                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: "Error",
                            text: errorThrown,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }
</script>
@endsection