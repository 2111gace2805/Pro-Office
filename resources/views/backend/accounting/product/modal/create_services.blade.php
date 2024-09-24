<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('products.storeService') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="row p-2">
        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">Tipo de Item</label>
                <select class="form-control" name="tipoitem_id" id="tipoitem_id" required >
                    <option value="">{{_lang('Select One')}}</option>
                    {{ create_option( "tipo_item", "tipoitem_id", "tipoitem_nombre", old('tipoitem_id'), ['tipoitem_id=' => 2] )}}
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
                    <label class="control-label">{{ _lang('Service Name') }}</label>
                    <input maxlength="64" type="text" class="form-control" name="item_name" value="{{ old('item_name') }}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label id="codigo_producto_label" class="control-label">{{ _lang('Código de servicio') }}</label>
                    <input type="text" class="form-control" name="product_code" value="{{ old('product_code') }}"
                        required>
                </div>
            </div>

       
           
        </div>

        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Description') }}</label>
                <textarea required maxlength="128" class="form-control" name="description">{{ old('description') }}</textarea>
            </div>
        </div>
        
        <div class="col-md-6 mt-3">
            <div class="form-group">
                <label class="control-label text-black">{{ _lang('Precio') .' '.currency() }}</label>
                <input type="number" class="form-control" name="product_price" value="{{ old('product_price') }}"
                    required step="0.01" min="0">
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
</script>