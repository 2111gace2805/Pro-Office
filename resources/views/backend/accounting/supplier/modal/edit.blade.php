<form method="post" class="ajax-submit" autocomplete="off" action="{{action('SupplierController@update', $id)}}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row p-2">
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Supplier Name') }}</label>
                <input type="text" class="form-control" name="supplier_name" value="{{ $supplier->supplier_name }}"
                    required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Company Name') }}</label>
                <input type="text" class="form-control" name="company_name" value="{{ $supplier->company_name }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Vat Number') }}</label>
                <input type="text" class="form-control" name="vat_number" value="{{ $supplier->vat_number }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Email') }}</label>
                <input type="text" class="form-control" name="email" value="{{ $supplier->email }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Phone') }}</label>
                <input type="text" class="form-control" name="phone" value="{{ $supplier->phone }}" required>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Address') }}</label>
                <input type="text" class="form-control" name="address" value="{{ $supplier->address }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Country') }}</label>
                <select class="form-control select2" name="country" onchange="toggleFields()">
                    {{ get_country_list($supplier->country) }}
                </select>
            </div>
        </div>

        <div class="col-md-6 form-group-state">
            <div class="form-group">
                <label class="control-label">{{ _lang('State') }}</label>
                {{-- <input type="text" class="form-control" name="state" value="{{ $supplier->state }}"> --}}
                <select class="form-control select2" name="state" id="depa_id">
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("departamentos", "depa_id", "depa_nombre", old('depa_id', $supplier->state)) }}
                </select>
            </div>
        </div>

        <div class="col-md-6 form-group-city">
            <div class="form-group">
                <label class="control-label">{{ _lang('City') }}</label>
                {{-- <input type="text" class="form-control" name="city" value="{{ $supplier->city }}"> --}}
                <select class="form-control select2-ajax" data-value="munidepa_id" data-display="muni_nombre"
                    data-table="municipios" name="city" id="munidepa_id" data-where_extra="depa_id = '-1'">
                    <option value="">{{ _lang('- Select One -') }}</option>
                    {{ create_option("municipios", "munidepa_id", "muni_nombre", old('munidepa_id', $supplier->city),
                    ['munidepa_id=' => old('munidepa_id', $supplier->city)]) }}
                </select>
            </div>
        </div>



        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Postal Code') }}</label>
                <input type="text" class="form-control" name="postal_code" value="{{ $supplier->postal_code }}">
            </div>
        </div>


        <div class="form-group">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save
                    Changes') }}</button>
            </div>
        </div>
    </div>
</form>
<script>
    function depa_idChanged(inicio = false){
        if(inicio == false) $('#munidepa_id').val('');
        $('#munidepa_id').attr('data-where_extra', "depa_id = '"+$('#depa_id').val()+"'");
        $('#munidepa_id').data('where_extra', "depa_id = '"+$('#depa_id').val()+"'");
        setSelect2Ajax();
    }

    $('#depa_id').on('change', e=>depa_idChanged());

    $(()=>depa_idChanged(true));

    function toggleFields() {
            var countrySelect = document.querySelector('select[name="country"]');
            var stateFormGroup = document.querySelector('.form-group-state');
            var cityFormGroup = document.querySelector('.form-group-city');

            // Ocultar campos de estado y ciudad al cargar la página
            stateFormGroup.style.display = 'none';
            cityFormGroup.style.display = 'none';

            var stateSelect = stateFormGroup.querySelector('select');
            var citySelect = cityFormGroup.querySelector('select');

            if (countrySelect.value === 'El Salvador') {
                stateFormGroup.style.display = 'block';
                cityFormGroup.style.display = 'block';
                stateSelect.setAttribute('name', 'state');
                citySelect.setAttribute('name', 'city');
            } else {
                stateFormGroup.style.display = 'none';
                cityFormGroup.style.display = 'none';
                stateSelect.removeAttribute('name');
                citySelect.removeAttribute('name');
            }
        }

</script>