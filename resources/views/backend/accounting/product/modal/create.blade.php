<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('products.store') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="row p-2">
        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">Tipo de Item (Producto o servicio)</label>
                <select class="form-control" name="tipoitem_id" id="tipoitem_id" required onchange="mostrarCampos()">
                    <option value="">{{_lang('Select One')}}</option>
                    {{ create_option("tipo_item", "tipoitem_id", "tipoitem_nombre", old('tipoitem_id'))}}
                </select>                    
            </div>
        </div>
        <div class="col-xl-6 col-md-6 col-sm-12">
            <div class="card shadow-none" style="border:1px solid #e0e0e0;">
                <div class="card-body">
                    <div class="card-title text-black"><strong>Imagen</strong></div>
                    <input type="file" class="form-control dropify" name="imagen" 
                        data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                </div>
            </div>
        </div>

        
        <div class="col-xl-6 col-md-6 col-sm-12 mb-3">
            

            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Product Name') }}</label>
                    <input maxlength="1000" type="text" class="form-control" name="item_name" value="{{ old('item_name') }}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label id="codigo_producto_label" class="control-label">{{ _lang('Código de producto') }}</label>
                    <input type="text" class="form-control" name="product_code" value="{{ old('product_code') }}"
                        required>
                </div>
            </div>

       
           
        </div>

        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Description') }}</label>
                <textarea required maxlength="1000" class="form-control" name="description">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="col-md-6 mt-3" style="display: none;" id="peso">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Weight') }}</label>
                <input type="number" class="form-control campo_requerido" name="weight"
                    value="{{ old('weight') }}">
            </div>
        </div>

        <div class="col-md-6 mt-3" style="display: none;" id="proveedores">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Supplier') }}</label>
                <select class="form-control select2 campo_requerido" name="supplier_id">
                    {{ create_option("suppliers","id","supplier_name",old('supplier_id')) }}
                </select>
            </div>
        </div>
        
        <div class="col-md-6 mt-3">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Product Price') .' '.currency() }}</label>
                <input type="number" class="form-control" name="product_price" value="{{ old('product_price') }}"
                    required step="0.01" min="0">
            </div>
        </div>
        <div class="col-md-6 mt-3">
            <div class="form-group" style="display: none;" id="costoProduc">
                <label class="control-label text-black">{{ _lang('Product Cost').' '.currency() }}</label>
                <input type="number" class="form-control campo_requerido" name="product_cost" value="{{ old('product_cost') }}" required step="0.01" min="0">
            </div>
        </div>
        

        <div class="col-md-6 mt-3 " style="display: none;" id="unidadProduc">
            <div class="form-group">
                <a href="{{ route('product_units.create') }}" data-reload="false"
                    data-title="{{ _lang('Add Product Unit') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label text-black">{{ _lang('Product Unit') }}</label>
                <select class="form-control select2-ajax campo_requerido" data-value="unit_name" data-display="unit_name"
                    data-table="product_units"  name="product_unit" required>
                    <option value="">{{ _lang('- Select Product Unit -') }}</option>
                </select>
            </div>
        </div>


        <div class="col-md-6 mt-3" style="display: none;" id="category">
            <div class="form-group">
                <a href="{{ route('categories.create') }}" data-reload="false"
                    data-title="{{ _lang('Add Category') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label text-black">{{ _lang('Category') }}</label>
                <select class="form-control select2-ajax campo_requerido" data-value="id" data-display="category_name"
                    data-table="categories" name="category" required>
                    <option value="">{{ _lang('- Select Category -') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3" style="display: none;" id="marca">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Marca') }}</label>
                <select class="form-control select2 campo_requerido" name="brand_id">
                    {{ create_option("brands","brand_id","brand_name",old('brand_id'),array("brand_status="=>'Active')) }}
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3" style="display: none;" id="original">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Es original') }}</label>
                <select class="form-control campo_requerido" name="original" id="original" required>
                    <option value="si" @if(old('original')== 'si') selected @endif>Sí</option>
                    <option value="no" @if(old('original')== 'no') selected @endif>No</option>
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3" style="display: none;" id="modelo">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Modelo') }}</label>
                <input type="text" class="form-control campo_requerido" name="model" id="model" 
                value="{{ old('model') }}">
            </div>
        </div>

        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Grupo') }}</label>
                <select class="form-control" name="prodgrp_id">
                    <option value="">{{_lang('Select One')}}</option>
                    {{ create_option("product_groups","prodgrp_id","prodgrp_name",old('prodgrp_id', ''),array("prodgrp_status="=>'Active')) }}
                </select>
            </div>
        </div>


        <div class="col-md-6 mt-3" style="display: none;" id="garantia">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Warranty type') }}</label>
                <select class="form-control auto-select campo_requerido" name="warranty_type" id="warranty_type" data-selected="{{ old('warranty_type', 'Days') }}" required>
                    <option value="Dias">{{ _lang('Dias') }}</option>
                    <option value="Meses">{{ _lang('Meses') }}</option>
                    <option value="Años">{{ _lang('Años') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3" style="display: none;" id="warranty_value">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Warranty value') }}</label>
                <input type="number" class="form-control campo_requerido" name="warranty_value" id="warranty_value" 
                value="{{ old('warranty_value') }}" required/>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Note') }}</label>
                <textarea class="form-control" name="note">{{ old('note') }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Comment') }}</label>
                <textarea class="form-control" name="comment">{{ old('comment') }}</textarea>
            </div>
        </div>
        

        <div class="form-group">
            <div class="col-md-12 text-end">

                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                    {{ _lang('Save Changes') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
    function mostrarCampos() {
    var tipoitem_id = document.getElementById("tipoitem_id").value;

    if (tipoitem_id === "") {
        
        ocultarTodosLosCampos();
    } else if (tipoitem_id === "1") {
        
        mostrarCamposProductos();
        agregarAtributoRequired();
    } else {
        ocultarCamposProductos();
        document.getElementById("codigo_producto_label").textContent = "{{ _lang('Codigo del servicio') }}";
        quitarAtributoRequired();
    }
}

function ocultarTodosLosCampos() {
    document.getElementById("peso").style.display = "none";
    document.getElementById("proveedores").style.display = "none";
    document.getElementById("costoProduc").style.display = "none";
    document.getElementById("unidadProduc").style.display = "none";
    document.getElementById("category").style.display = "none";
    document.getElementById("marca").style.display = "none";
    document.getElementById("original").style.display = "none";
    document.getElementById("modelo").style.display = "none";
    document.getElementById("garantia").style.display = "none";
    document.getElementById("warranty_value").style.display = "none";
}

function mostrarCamposProductos() {
    document.getElementById("peso").style.display = "block";
    document.getElementById("proveedores").style.display = "block";
    document.getElementById("costoProduc").style.display = "block";
    document.getElementById("unidadProduc").style.display = "block";
    document.getElementById("category").style.display = "block";
    document.getElementById("marca").style.display = "block";
    document.getElementById("original").style.display = "block";
    document.getElementById("modelo").style.display = "block";
    document.getElementById("garantia").style.display = "block";
    document.getElementById("warranty_value").style.display = "block";
}

function ocultarCamposProductos() {
    document.getElementById("peso").style.display = "none";
    document.getElementById("proveedores").style.display = "none";
    document.getElementById("costoProduc").style.display = "none";
    document.getElementById("unidadProduc").style.display = "none";
    document.getElementById("category").style.display = "none";
    document.getElementById("marca").style.display = "none";
    document.getElementById("original").style.display = "none";
    document.getElementById("modelo").style.display = "none";
    document.getElementById("garantia").style.display = "none";
    document.getElementById("warranty_value").style.display = "none";
}

function agregarAtributoRequired() {
    var campos = document.querySelectorAll('.campo_requerido');
    campos.forEach(function(campo) {
        campo.setAttribute("required", "required");
    });
}

function quitarAtributoRequired() {
    var campos = document.querySelectorAll('.campo_requerido');
    campos.forEach(function(campo) {
        campo.removeAttribute("required");
    });
}

</script>
