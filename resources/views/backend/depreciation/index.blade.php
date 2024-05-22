@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Depreciaciones') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="#" data-toggle="modal" data-target="#myModal">
                    <i class="ti-plus"></i> {{ _lang('Add New') }}
                </a>                              
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Costo</th>
                            <th>Periodo (mes)</th>
                            <th>Valor del desecho</th>
                            <th>Depreciación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $shownAssets = [];
                        @endphp

                        @foreach($depreciation as $depreciationItem)
                            @if (!in_array($depreciationItem->assetid, $shownAssets))
                                <tr>
                                    <td>{{ optional($depreciationItem->asset)->name }}</td>
                                    <td>{{ optional($depreciationItem->asset)->cost }}</td>
                                    <td>{{ $depreciationItem->period }}</td>
                                    <td>{{ $depreciationItem->assetvalue }}</td>
                                    <td>{{ $depreciationItem->deTotal}}</td>
                                     <td>
                                        <div style="display: flex;">
                                            <button class="btn btn-primary btn-editar btn-sm ml-auto mr-2" data-id="{{ $depreciationItem->id }}" data-typeid="{{ $depreciationItem->asset->typeid }}" data-assetid="{{ $depreciationItem->assetid }}" data-cost="{{ $depreciationItem->asset->cost }}" data-period="{{ $depreciationItem->period }}" data-assetvalue="{{ $depreciationItem->assetvalue }}" data-toggle="modal" data-target="#staticBackdrop">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        
                                            <form id="deleteForm" action="{{ route('depreciation.destroy', ['id' => $depreciationItem->id]) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm ml-auto" style="margin-left: auto;" onclick="confirmDelete()">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        
                                    </td>
                                </tr>
                                @php
                                    $shownAssets[] = $depreciationItem->assetid;
                                @endphp
                            @endif
                        @endforeach
                    
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>
<!-- Modal de Guardar -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar Nueva depreciación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="depreciationForm" action="{{ route('guardar.depreciacion') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="typeid">Tipo de Activo</label>
                        <select name="typeid" id="typeid" class="form-control" required>
                            <option value="" selected>Seleccionar</option>
                          @foreach($assetsType as $key => $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                          @endforeach  
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assetid">Nombre del Activo</label>
                        <select name="assetid" id="assetid" class="form-control" required>
                            <option value="" selected>Seleccionar</option>
                            @foreach($asset as $key => $value)
                                @if($value->checkstatus === 0)
                                    <option value="{{$value->id}}" data-cost="{{$value->cost}}">{{$value->name}} - {{$value->company_name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assetCost">Costo del Activo</label>
                        <input type="text" class="form-control" id="assetCost" name="assetCost" readonly>
                    </div>

                    <div class="form-group">
                        <label for="period">Periodo</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="period" name="period" required>
                            <div class="input-group-append">
                                <span class="input-group-text">meses</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="period">Valor del desecho</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="assetvalue" name="assetvalue" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Editar Depreciación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-actualizar" action="{{ url('/update-depreciation/' . $value->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Cambiar a POST y agregar el campo _method -->
                    <input type="hidden" name="_method" value="PUT">
                    <!-- Campo oculto para el ID -->
                    <input type="hidden" id="id_depreciation" name="id_depreciation" >
                    <div class="form-group">
                        <label for="typeid">Tipo de Activo</label>
                        <select name="typeidEdit" id="typeidEdit" class="form-control" required>
                            <option value="" selected>Seleccionar</option>
                            @foreach($assetsType as $key => $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach  
                        </select>
                    </div>
                    

                    <div class="form-group">
                        <label for="assetid">Nombre del Activo</label>
                        {{-- <input type="text" class="form-control" id="assetid" name="assetid" required> --}}
                        <select name="assetidEdit" id="assetidEdit" class="form-control" required>
                            <option value="" selected>Seleccionar</option>
                            @foreach($asset as $key => $value)
                                <option value="{{$value->id}}" data-cost="{{$value->cost}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assetCost">Costo del Activo</label>
                        <input type="text" class="form-control" id="assetCostEdit" name="assetCostEdit" readonly>
                    </div>

                    <div class="form-group">
                        <label for="period">Periodo</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="periodEdit" name="periodEdit" required>
                            <div class="input-group-append">
                                <span class="input-group-text">meses</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="assetvalue">Valor del activo</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="assetvalueEdit" name="assetvalueEdit" required>
                        </div>
                    </div>

                    
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="btn-actualizar">Actualizar</button>
            </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js-script')
<script>

    $(document).ready(function() {
        $('#assetid').change(function() {
            var selectedAssetId = $(this).val();

            var selectedOption = $(this).find('option:selected');
            var assetCost = selectedOption.data('cost');
            console.log("Costo del activo:", assetCost);

            $('#assetCost').val(assetCost);
        });
    });

    $(document).on('click', '.btn-editar', function() {
    var idDepreciation = $(this).data('id');
    var typeid = $(this).data('typeid');
    var assetid = $(this).data('assetid');
    var cost = $(this).data('cost');
    var period = $(this).data('period');
    var assetvalue = $(this).data('assetvalue');

    console.log("ID Depreciation: " + idDepreciation);

    // Para el modal de edición
    $('#id_depreciation').val(idDepreciation);
    $('#typeidEdit').val(typeid);
    $('#assetidEdit').val(assetid);
    $('#assetCostEdit').val(cost);
    $('#periodEdit').val(period);
    $('#assetvalueEdit').val(assetvalue);

    $('#staticBackdrop').modal('show');
});

$(document).on('click', '#btn-actualizar', function(e) {
    e.preventDefault();

    var id = $('#id_depreciation').val();
    var assetid = $('#assetidEdit').val();
    var period = $('#periodEdit').val();
    var assetvalue = $('#assetvalueEdit').val();

    console.log("ID:", id);
    // Realizar la solicitud AJAX
        $.ajax({
        url: 'update-depreciation/' + id,
        method: 'POST', 
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT', // Mantén el campo _method, pero ahora dentro de un array
            id: id,
            assetidEdit: assetid,
            periodEdit: period,
            assetvalueEdit: assetvalue
        },
        success: function(response) {
            console.log(response);
            $('#staticBackdrop').modal('hide');
            window.location.reload();
        },
        error: function(error) {
            console.error(error);
        }
    });
});

// funcion para preguntar confirmar eliminacion
function confirmDelete() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminarlo'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById("deleteForm").submit();
            }
        });
    }

 
</script>
@endsection