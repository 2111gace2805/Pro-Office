

function modalAnulacion( id ){

    Swal.fire({
        title: "¿Seguro de anular nota de pedido?",
        text: "Los productos de la nota serán reintegrados al stock",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, ¡Invalidar!",
        cancelButtonText: "Cancelar",
        allowOutsideClick: false,
        allowEscapeKey: false
    })
    .then((result) => {

        if( result.isConfirmed ){

            let csrfToken = document.querySelector('input[name="_token"]').value;

            $.ajax({
                url: '/order_notes/cancelNote',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                data: {
                    id
                },
                beforeSend: function () {
                    Swal.fire({
                        title: '<b>Actualizando nota de pedido...</b>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading()
                        },
                    });
                },
                success: function(response) {

                    if( response.result === 'error' ){    
                        Swal.fire({
                            title: "Error",
                            text: response.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            icon: "error"
                        });
                    }
                    else{
                        Swal.fire({
                            title: "Realizado!",
                            text: response.message,
                            icon: "success",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            timer: 2000,
                            showConfirmButton: false,
                        })
                        
                        setTimeout(() => {
                            let url = '/order_notes';
                            window.location.href = url; 
                        }, 2000);

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


function modalDescargo( id ){
    
    Swal.fire({
        title: "¿Desea actualizar estado de nota a procesada?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, actualizar!",
        cancelButtonText: "Cancelar",
        allowOutsideClick: false,
        allowEscapeKey: false
    })
    .then((result) => {

        if( result.isConfirmed ){

            let csrfToken = document.querySelector('input[name="_token"]').value;

            $.ajax({
                url: '/order_notes/updateStatus',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                data: {
                    id
                },
                beforeSend: function () {
                    Swal.fire({
                        title: '<b>Actualizando nota de pedido...</b>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading()
                        },
                    });
                },
                success: function(response) {

                    if( response.result === 'error' ){    
                        Swal.fire({
                            title: "Error",
                            text: response.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            icon: "error"
                        });
                    }
                    else{
                        Swal.fire({
                            title: "Realizado!",
                            text: response.message,
                            icon: "success",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            timer: 2000,
                            showConfirmButton: false,
                        })
                        
                        setTimeout(() => {
                            let url = '/order_notes';
                            window.location.href = url; 
                        }, 2000);

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