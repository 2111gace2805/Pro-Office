<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('OrderNoteController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row p-2">

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Empresa por la cual se está vendiendo') }}</label>
                <input type="text" class="form-control" name="sales_company" value="{{ $order->sales_company }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Order Note Number') }}</label>
                <input type="text" class="form-control" name="order_number" id="order_number" value="{{ $order->order_number }}" required readonly>
            </div>
        </div>


        <div class="col-12 col-md-8">
            <div class="form-group">
                <a href="{{ route('contacts.create') }}" data-reload="false"
                    data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Select Client') }}</label>
                <select class="form-control select2-ajax" data-value="id" data-display="company_name" data-table="contacts"  name="client_id" id="client_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("contacts","id","company_name", $order->client_id ) }}
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Número de licitación pública') }}</label>
                <input type="text" class="form-control" name="num_public_tender" value="{{ $order->num_public_tender }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Número de contrato') }}</label>
                <input type="text" class="form-control" name="num_contract" value="{{ $order->num_contract }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de entrega según contrato') }}</label>
                <input type="date" class="form-control" name="deliver_date_contract" value="{{ $order->deliver_date_contract }}" required>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>{{ _lang('Save Changes') }}</button>
            </div>
        </div>
    </div>
</form>
