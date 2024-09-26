

    <form method="POST" action="{{ route('assets.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">{{ _lang('Nombre') }}</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Código') }}</label>
                <input type="text" name="assettag" id="assettag" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Proveedor') }}</label>
                <select name="supplierid" id="supplierid" class="form-control">
                    @foreach($supplier as $value)
                        <option value="{{ $value->id }}">{{ $value->supplier_name }}</option>
                    @endforeach
                </select>
                
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Sucursal') }}</label>
                <select name="locationid" id="locationid" class="form-control">
                    @foreach($company as $key => $value)
                        <option value="{{$value->id}}">{{$value->company_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Marca') }}</label>
                <select name="brandid" id="brandid" class="form-control">
                    @foreach($brand as $key => $value)
                        <option value="{{$value->brand_id}}">{{$value->brand_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Número de serie') }}</label>
                <input type="text" name="serial" id="serial" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Tipo de activo') }}</label>
                <div class="input-group">
                    <select name="typeid" id="typeid" class="form-control">
                        @foreach($assetsType as $key => $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                        @endforeach
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary btn-sm ml-auto ajax-modal" href="{{ route('type-active') }}" data-title="{{ _lang('Add Product') }}" data-toggle="modal" data-target="#addAssetTypeModal">
                            Nuevo tipo de activo
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Costo') }}</label>
                <input type="text" name="cost" id="cost" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Fecha de compra') }}</label>
                <input type="date" name="purchasedate" id="purchasedate" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Garantia') }}</label>
                <div class="input-group">
                    <input type="text" name="quantity" id="quantity" class="form-control" required>
                    <div class="input-group-append">
                        <span class="input-group-text">Meses</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Estado') }}</label>
                <select name="status" id="status" class="form-control">
                    <option value="Listo para depreciar">Listo para depreciar</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Archivado">Archivado</option>
                    <option value="Con avería">Con avería</option>
                    <option value="Perdido">Perdido</option>
                    <option value="Reparación">Reparación</option>
                    <option value="Robado">Robado</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="assettag">{{ _lang('Descripción') }}</label>
                <input type="text" name="description" id="description" class="form-control" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
    </form>
