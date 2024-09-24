<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('warehouses.update_warehouse', ['id_warehouse' => $warehouse->id]) }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    @method('PUT')
    <div class="col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="header-title">{{ _lang('Edit Warehouse') }}</h4>
                        <input type="hidden" name="client_id" value="{{ $warehouse->client_id }}">
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12" id="dui">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Name') }}</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ $warehouse->name }}">
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
