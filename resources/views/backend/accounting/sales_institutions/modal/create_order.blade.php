<form method="post" class="ajax-submit" id="frmWarehouse" autocomplete="off" action="{{ route('warehouses.store_warehouse') }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Select Client') }}</label>
                                    <select class="form-control select2-ajax" data-value="id" data-display="company_name" data-table="contacts"  name="client_id" id="client_id_modal" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Name') }}</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-4">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>  {{ _lang('Save Warehouse') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
        
    $(document).ready( function(){

        let cliente = $("#client_id").val();

        if( cliente != "" ){
    
            let text = $("#client_id option:selected").text();
    
            $("#client_id_modal").select2("trigger", "select", {
                data: { id: cliente, text: text }
            });

            $("#client_id_modal").on('select2:opening', function (e) {
                e.preventDefault();
            });
        }
        else{
            
            $('#frmWarehouse').on('submit', function (e) {

                let cliente_selected    = $("#client_id_modal").val();
                let text_selected       = $("#client_id_modal option:selected").text();

                $("#client_id").select2("trigger", "select", {
                    data: { id: cliente_selected, text: text_selected }
                });
            });
        }

    });

</script>
