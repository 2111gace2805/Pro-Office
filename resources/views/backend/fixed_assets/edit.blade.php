
    
    <form method="POST" action="{{ route('asset.update', $asset->id) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $asset->name }}" required>
            </div>

            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Código') }}</label>
                <input type="text" name="assettag" id="assettag" class="form-control" value="{{ $asset->assettag }}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Proveedor') }}</label>
                <select name="supplierid" id="supplierid" class="form-control">
                    @foreach($supplier as $value)
                        <option value="{{ $value->id }}" {{ $asset->supplierid == $value->id ? 'selected' : '' }}>
                            {{ $value->supplier_name }}
                        </option>
                    @endforeach
                </select>
                
                
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Sucursal') }}</label>
                <select name="locationid" id="locationid" class="form-control">
                    @foreach($company as $value)
                        <option value="{{ $value->id }}" {{ $asset->locationid == $value->id ? 'selected' : '' }}>
                            {{ $value->company_name }}
                        </option>
                    @endforeach
                </select>
                
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Marca') }}</label>
                <select name="brandid" id="brandid" class="form-control">
                    @foreach($brand as $key => $value)
                        <option value="{{$value->brand_id}}" {{ $asset->brandid == $value->brand_id ? 'selected' : '' }}>
                            {{$value->brand_name}}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Número de serie') }}</label>
                <input type="text" name="serial" id="serial" class="form-control" value="{{ $asset->serial }}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Tipo de activo') }}</label>
                <select name="typeid" id="typeid" class="form-control">
                    @foreach($assetsType as $key => $value)
                        <option value="{{$value->id}}" {{ $asset->typeid == $value->id ? 'selected' : '' }}>
                            {{$value->name}}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Costo') }}</label>
                <input type="text" name="cost" id="cost" class="form-control" value="{{ $asset->cost }}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Fecha de compra') }}</label>
                <input type="date" name="purchasedate" id="purchasedate" class="form-control" value="{{ $asset->purchasedate }}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Garantia') }}</label>
                <div class="input-group">
                    <input type="text" name="quantity" id="quantity" class="form-control" value="{{ $asset->quantity }}" required>
                    <div class="input-group-append">
                        <span class="input-group-text">Meses</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Estado') }}</label>
                <select name="status" id="status" class="form-control status">
                    <option value="Listo para depreciar" {{ $asset->status == 'Listo para depreciar' ? 'selected' : '' }}>Listo para depreciar</option>
                    <option value="Pendiente" {{ $asset->status == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Archivado" {{ $asset->status == 'Archivado' ? 'selected' : '' }}>Archivado</option>
                    <option value="Con avería" {{ $asset->status == 'Con avería' ? 'selected' : '' }}>Con avería</option>
                    <option value="Perdido" {{ $asset->status == 'Perdido' ? 'selected' : '' }}>Perdido</option>
                    <option value="Reparación" {{ $asset->status == 'Reparación' ? 'selected' : '' }}>Reparación</option>
                    <option value="Robado" {{ $asset->status == 'Reparación' ? 'selected' : '' }}>Robado</option>
                </select>
                
            </div>
            <div class="form-group col-md-6" id="explicacion-container" style="display: none;">
                <label for="explicacion">{{ _lang('Explicación') }}</label>
                <textarea name="explicacion" id="explicacion" class="form-control"></textarea>
            </div>

            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Descripción') }}</label>
                <input type="text" name="description" id="description" class="form-control" value="{{ $asset->description }}" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>

{{-- @section('js-script') --}}
<script>
    // Escucha cambios en el campo status
    $('#status').on('change', function() {
        // Muestra u oculta el campo de explicación según la opción seleccionada
        if ($(this).val() === 'Robado' || $(this).val() === 'Con avería' || $(this).val() === 'Perdido') {
            $('#explicacion-container').show();
        } else {
            $('#explicacion-container').hide();
        }
    });

    // Verifica el estado inicial al cargar la página
    $(document).ready(function() {
        if ($('#status').val() === 'Robado' || $('#status').val() === 'Con avería' || $('#status').val() === 'Perdido') {
            $('#explicacion-container').show();
        }
    });
</script>
{{-- @endsection --}}
