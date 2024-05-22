@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Activos fijos') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" href="{{ route('assets.createModal') }}" data-title="{{ _lang('Añadir Activo') }}">
                    <i class="ti-plus"></i> {{ _lang('Add New') }}
                </a>                               
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Codigo') }}</th>
                            <th>{{ _lang('Nombre') }}</th>
                            <th>{{ _lang('Tipo') }}</th>
                            <th>{{ _lang('Marca') }}</th>
                            <th>{{ _lang('Sucursal') }}</th>
                            <th class="text-center">{{ _lang('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $key => $value)
                        <tr>
                            <td>{{$value->assettag}}</td>
                            <td>{{$value->name}}</td>
                            <td>{{$value->asset_type_name}}</td>
                            <td>{{$value->brand_name}}</td>
                            <td>{{$value->company_name}}</td>
                            <td>
                                <a href="{{ route('asset.show', $value->id) }}" class="btn btn-info btn-sm ml-auto"><i class="fas fa-info-circle"></i> </a>

                                <a href="{{ route('asset.edit', $value->id) }}" class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Añadir Activo') }}"><i class="fas fa-edit"></i> </a>

                                <form action="{{ route('asset.softdelete', $value->id) }}" method="POST" style="display: inline;" onsubmit="return confirmDelete(event)">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="motivo" id="motivo" value="">
                                    <button type="submit" class="btn btn-danger btn-sm ml-auto"><i class="fas fa-trash-alt"></i></button>
                                </form>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>


@endsection

@section('js-script')
<script>
    function confirmDelete(event) {
        event.preventDefault();

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Por favor, ingresa el motivo de la baja:',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar un motivo';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById("motivo").value = result.value;
                event.target.submit();
            }
        });
    }
</script>
@endsection
