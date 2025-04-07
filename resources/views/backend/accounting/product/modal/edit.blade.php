<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('ProductController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row p-2">
        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">Tipo de Item (Producto o servicio)</label>
                <input type="text" class="form-control"  name="tipoitem_nombre" id="tipoitem_nombre" value="{{ $tipoItemNombre }}" readonly>
            </div>
        </div>
        
        <div class="col-xl-6 col-md-6 col-sm-12">
            <div class="card shadow-none" style="border:1px solid #e0e0e0;">
                <div class="card-body">
                    <div class="card-title text-black"><strong>Imagen</strong></div>

                    <input type="file" class="form-control dropify" name="imagen"
                        data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG"
                        data-default-file="{{ asset($item->product->image) }}">
                </div>
            </div>
        </div>


        <div class="col-xl-6 col-md-6 col-sm-12">

            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Product Name') }}</label>
                    <input maxlength="1000" type="text" class="form-control" name="item_name"
                        value="{{ old('item_name', $item->item_name) }}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Codigo producto') }}</label>
                    <input type="text" class="form-control" name="product_code"
                        value="{{ old('product_code', $item->product->product_code) }}" required>
                </div>
            </div>

            <div class="col-md-12"  id="peso">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Weight') }}</label>
                    <input type="number" class="form-control" name="weight"
                        value="{{ old('weight', $item->product->weight) }}">
                </div>
            </div>

        </div>


        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Description') }}</label>
                <textarea required maxlength="1000" class="form-control" name="description">{{ old('description', $item->product->description) }}</textarea>
            </div>
        </div>

        <div class="col-md-6 mt-3"  id="proveedores">
            <div class="form-group">
                <label class="control-label">{{ _lang('Supplier') }}</label>
                <select class="form-control select2" name="supplier_id">
                    {{-- <option value="{{$item->product->supplier_id}}" selected>{{$item->product->supplier_name}}</option> --}}
                    {{ create_option('suppliers', 'id', 'supplier_name', $item->product->supplier_id, ['company_id=' => company_id()]) }}
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3">

            <div class="form-group">
                <label class="control-label">{{ _lang('Product Price') . ' ' . currency() }}</label>
                <input type="number" class="form-control" name="product_price"
                    value="{{ old('product_price', $item->product->product_price) }}" required step="0.01"
                    min="0">
            </div>
        </div>

        <div class="col-md-6 mt-3"  id="costoProduc">
            <div class="form-group">
                <label class="control-label">{{ _lang('Product Cost') . ' ' . currency() }}</label>
                <input type="number" class="form-control" name="product_cost"
                    value="{{ old('product_cost', $item->product->product_cost) }}"  step="0.01"
                    min="0">
            </div>
        </div>
        

        <div class="col-md-6 mt-3"  id="unidadProduc">
            <div class="form-group">
                <a href="{{ route('product_units.create') }}" data-reload="false"
                    data-title="{{ _lang('Add Product Unit') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Product Unit') }}</label>
                <select class="form-control select2-ajax" data-value="unit_name" data-display="unit_name"
                    data-table="product_units" name="product_unit" >
                    {{-- <option value="{{$item->product->product_unit}}" selected>{{$item->product->product_unit}}</option> --}}
                    {{ create_option('product_units', 'unit_name', 'unit_name', $item->product->product_unit) }}
                    {{-- {{ create_option('product_units', 'unit_name', 'unit_name', $item->product->product_unit, ['company_id=' => company_id()]) }} --}}
                </select>
            </div>
        </div>


        <div class="col-md-6 mt-3"  id="category">
            <div class="form-group">
                <a href="{{ route('categories.create') }}" data-reload="false"
                    data-title="{{ _lang('Add Category') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i>
                    {{ _lang('Add New') }}</a>
                <label class="control-label text-black">{{ _lang('Category') }}</label>
                <select class="form-control select2-ajax" data-value="id" data-display="category_name"
                    data-table="categories" name="category" >
                    {{-- <option value="{{$item->category_id}}" selected>{{$item->product->category_name}}</option> --}}
                    {{ create_option('categories', 'id', 'category_name', $item->product->category_id, []) }}
                </select>
            </div>
        </div>


        <div class="col-md-6 mt-3"  id="marca">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Marca') }}</label>
                <select class="form-control select2" name="brand_id">
                    <option value="">{{_lang('Select one')}}</option>
                    {{ create_option("brands","brand_id","brand_name",old('brand_id', $item->product->brand_id),array("brand_status="=>'Active')) }}
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3"  id="original">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Es original') }}</label>
                <select class="form-control" name="original" id="original" >
                    <option value="si" @if(old('original', $item->product->original)== 'si') selected @endif>Sí</option>
                    <option value="no" @if(old('original', $item->product->original)== 'no') selected @endif>No</option>
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3"  id="modelo">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Modelo') }}</label>
                <input type="text" class="form-control" name="model" id="model" 
                value="{{ old('model', $item->product->model) }}">
            </div>
        </div>

        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Grupo') }}</label>
                <select class="form-control" name="prodgrp_id">
                    <option value="">{{_lang('Select One')}}</option>
                    {{ create_option("product_groups","prodgrp_id","prodgrp_name",old('prodgrp_id', $item->product->prodgrp_id),array("prodgrp_status="=>'Active')) }}
                </select>
            </div>
        </div>


        
        <div class="col-md-6 mt-3"  id="garantia">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Warranty type') }}</label>
                <select class="form-control auto-select" name="warranty_type" id="warranty_type" data-selected="{{ old('warranty_type', $item->product->warranty_type) }}">
                    <option value="Dias">{{ _lang('Dias') }}</option>
                    <option value="Meses">{{ _lang('Meses') }}</option>
                    <option value="Años">{{ _lang('Años') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-3" " id="warranty_value">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Warranty value') }}</label>
                <input type="number" class="form-control" name="warranty_value" id="warranty_value" 
                value="{{ old('warranty_value', $item->product->warranty_value) }}">
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Note') }}</label>
                <textarea class="form-control" name="note">{{ old('note', $item->product->note) }}</textarea>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Comment') }}</label>
                <textarea class="form-control" name="comment">{{ old('comment', $item->product->comment) }}</textarea>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                    {{ _lang('Save Changes') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
   
        var tipoItemNombre = document.getElementById("tipoitem_nombre").value;
        console.log("Tipo de item: " + tipoItemNombre);

        if (tipoItemNombre === "Producto") {
            console.log("Mostrando campos de producto.");
            mostrarCamposProductos();
        } else if (tipoItemNombre === "Servicio") {
            console.log("Mostrando campos de servicio.");
            ocultarCamposProductos();
        }

    function ocultarCamposProductos() {
        console.log("Ocultando campos de producto.");
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
        console.log("Mostrando campos de producto.");
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
</script>
