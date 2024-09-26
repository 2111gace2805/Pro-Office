
    <form action="{{ route('maintenance.update', ['id' => $maintenance->id]) }}" method="post">
        @csrf
        @method('PUT') 
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="">Activo</label>
                <select name="assetid" id="assetid" class="form-control">
                    <option value="" disabled>Seleccione Activo</option>
                    @foreach($assetsType as $key => $value)
                        <option value="{{ $value->id }}" {{ $value->id == $maintenance->assetid ? 'selected' : '' }}>
                            {{ $value->name }} - {{ $value->company_name}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6">
                <label for="name">{{ _lang('Proveedor') }}</label>
                <select name="supplierid" id="supplierid" class="form-control">
                    @foreach($supplier as $key => $value)
                        <option value="{{ $value->id }}" {{ $value->id == $maintenance->supplierid ? 'selected' : '' }}>
                            {{ $value->supplier_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-6">
                <label for="type">{{ _lang('Tipo') }}</label>
                <select name="type" id="type" class="form-control">
                    <option value="" disabled>Seleccione el tipo de mantenimiento</option>
                    <option value="Mantenimiento" {{ $maintenance->type == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="Reparación" {{ $maintenance->type == 'Reparación' ? 'selected' : '' }}>Reparación</option>
                    <option value="Mejora" {{ $maintenance->type == 'Mejora' ? 'selected' : '' }}>Mejora</option>
                    <option value="Pruebas" {{ $maintenance->type == 'Pruebas' ? 'selected' : '' }}>Pruebas</option>
                    <option value="Calibración" {{ $maintenance->type == 'Calibración' ? 'selected' : '' }}>Calibración</option>
                    <option value="Soporte de hardware" {{ $maintenance->type == 'Soporte de hardware' ? 'selected' : '' }}>Soporte de hardware</option>
                    <option value="Soporte de software" {{ $maintenance->type == 'Soporte de software' ? 'selected' : '' }}>Soporte de software</option>
                </select>

            </div>

            <div class="form-group col-md-6">
                <label for="cost">Costo</label>
                <input type="number" id="cost" name="cost" value="{{ $maintenance->cost }}" class="form-control">
            </div>

            <div class="form-group col-md-6">
                <label for="status">Estado</label>
                <select name="status" id="status" class="form-control">
                    <option value="Activo" {{ $maintenance->status === 'Activo' ? 'selected' : '' }}>Activo</option>
                    <option value="Finalizado" {{ $maintenance->status === 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
            </div>
        
            <div class="form-group col-md-6">
                <label for="name">{{ _lang('Fecha de inicio') }}</label>
                <input type="date" id="startdate" name="startdate" class="form-control" value="{{$maintenance->startdate}}">
            </div>

            <div class="form-group col-md-6">
                <label for="name">{{ _lang('Fecha de finalización') }}</label>
                <input type="date" id="enddate" name="enddate" class="form-control" value="{{$maintenance->enddate}}">
            </div>

        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
