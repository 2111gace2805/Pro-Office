@extends('layouts.app')

@section('content')
    <h4 style="text-align: center">DETALLE DEL ACTIVO - {{ $asset->name }} ({{ $asset->assettag }})</h4>
    <hr>

    <!-- Pestañas de navegación -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info">Información General</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="detalle-tab" data-toggle="tab" href="#detalle">Mantenimientos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="depreciacion-tab" data-toggle="tab" href="#depreciacion">Depreciaciones</a>
        </li>
        <!-- Agrega más pestañas según sea necesario -->
    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content">
        <!-- Pestaña de Información General -->
        <div class="tab-pane fade show active" id="info">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $asset->name }} ({{$asset->assettag}})</h4>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row">Tipo</th>
                                <td>{{ $asset->asset_type_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Estado</th>
                                <td>{{ $asset->status }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Número de serie</th>
                                <td>{{ $asset->serial }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Marca</th>
                                <td>{{ $asset->brand_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Fecha de compra</th>
                                <td>{{ $asset->purchasedate }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Costo</th>
                                <td>{{ $asset->cost }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Garantía</th>
                                <td>
                                    {{ $asset->quantity }} Mes(ses) 

                                    @php
                                        $purchaseDate = \Carbon\Carbon::parse($asset->purchasedate);

                                        // Añade la duración de la garantía a la fecha de compra
                                        $warrantyExpirationDate = $purchaseDate->addMonths($asset->quantity);
                                    @endphp

                                    <!-- Muestra la fecha de vencimiento de la garantía -->
                                    <span class="text-muted"> (Vence el {{ $warrantyExpirationDate->format('d/m/Y') }})</span>
                                </td>

                            </tr>
                            <tr>
                                <th scope="row">Sucursal</th>
                                <td>{{ $asset->company_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Proveedor</th>
                                <td>{{ $asset->supplier_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Descripción</th>
                                <td>{{ $asset->description }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pestaña de Detalles Adicionales -->
        <div class="tab-pane fade" id="detalle">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Activo</th>
                                <th>Proveedor</th>
                                <th>Tipo</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th>Fecha de inicio</th>
                                <th>Fecha de finalización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceRecords as $key => $value)
                            <tr>
                                <td>{{$value->assettag}}</td>
                                <td>{{$value->asset_name}}</td>
                                <td>{{$value->supplier_name}}</td>
                                <td>{{$value->type}}</td>
                                <td>{{$value->cost}}</td>
                                <td>{{$value->status}}</td>
                                <td>{{$value->startdate}}</td>
                                <td>{{$value->enddate}}</td>
                                <td>
                                    <div style="display: flex;">
                                        <a href="{{ route('maintenance.edit', ['id' => $value->id]) }}" class="btn btn-primary mr-2 btn-sm ml-auto ajax-modal"><i class="fas fa-edit"></i> </a>
                                        <form action="{{ route('delete.maintenance', ['id' => $value->id]) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm ml-auto"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="depreciacion">
            <div class="card">
                <div class="card-body">

                    @if($depreciations->isEmpty())
    <p>No hay información de depreciación disponible.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Valor en libros</th>
                <th>Valor del desecho</th>
                <th>Depreciación Calculada</th>
                <th>Depreciación Acumulada</th>
            </tr>
        </thead>
        <tbody>
            @php
                $valorEnLibrosAnterior = $asset->cost;
                $depreciacionAcumulada = 0;
            @endphp

            <!-- Agregar una fila para el período 0 con el valor inicial -->
           

            @foreach($depreciations as $depreciation)
            <tr>
                <td>0</td>
                <td>{{ $asset->cost }}</td>
                <td>{{ $depreciation->assetvalue }}</td>
                <td>{{ $depreciation->deTotal }}</td>
                <td>{{ $depreciacionAcumulada }}</td>
            </tr>
                @for($i = 1; $i <= $depreciation->period; $i++)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>
                            {{ $calcularValorEnLibros($valorEnLibrosAnterior, $depreciation->deTotal, $i) }}
                        </td>
                        <td>{{ $depreciation->assetvalue }}</td>
                        <td>{{ $depreciation->deTotal }}</td>
                        <td>
                            @php
                                // Acumula la depreciación en cada iteración
                                $depreciacionAcumulada += $depreciation->deTotal;
                            @endphp
                            {{ $depreciacionAcumulada }}
                        </td>
                    </tr>

                    @php
                        // Actualiza el valor en libros anterior para la próxima iteración
                        $valorEnLibrosAnterior = $calcularValorEnLibros($valorEnLibrosAnterior, $depreciation->deTotal, $i);
                    @endphp
                @endfor
            @endforeach
        </tbody>
    </table>
@endif

                

                

                </div>
            </div>
        </div>
        <!-- Agrega más pestañas según sea necesario -->
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Atrás</a>
@endsection
