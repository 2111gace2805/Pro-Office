
            <form method="POST" action="{{ route('maintenance.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Activo') }}</label>
                    <select name="assetid" id="assetid" class="form-control">
                        <option value="" selected>Seleccione Activo</option>
                        @foreach($assetsType as $key => $value)
                        @if($value->checkstatus === 0)
                        <option value="{{ $value->id }}">{{ $value->name }} - {{ $value->company_name}}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Proveedor') }}</label>
                    <select name="supplierid" id="supplierid" class="form-control">
                        <option value="" selected>Seleccione Proveedor</option>
                        @foreach($supplier as $key => $value)
                        <option value="{{ $value->id }}">{{ $value->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Tipo') }}</label>
                    <select name="type" id="type" class="form-control">
                        <option value="" selected>Seleccione el tipo de mantenimiento</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Reparación">Reparación</option>
                    <option value="Mejora">Mejora</option>
                    <option value="Pruebas">Pruebas</option>
                    <option value="Calibración">Calibración</option>
                    <option value="Soporte de hardware">Soporte de hardware</option>
                    <option value="Soporte de software">Soporte de software</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Costo') }}</label>
                    <input type="number" id="cost" name="cost" class="form-control">
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Estado') }}</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Activo">Activo</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Fecha de inicio') }}</label>
                    <input type="date" id="startdate" name="startdate" class="form-control">
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ _lang('Fecha de finalización') }}</label>
                    <input type="date" id="enddate" name="enddate" class="form-control">
                </div>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
            </form>
        