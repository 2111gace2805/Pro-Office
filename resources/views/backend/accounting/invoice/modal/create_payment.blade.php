<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('invoices.create_payment') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="row p-2">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Credit Account') }}</label>
                <select class="form-control select2" name="account_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("accounts","id","account_title",old('account_id'),array("company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Income Type') }}</label>
                <select class="form-control select2" name="chart_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("chart_of_accounts","id","name",old('chart_id'),array("type="=>"income","AND company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Pending Amount')." ".currency() }}</label>
                <input type="text" class="form-control float-field"
                    value="{{ ($invoice->grand_total - $invoice->paid) }}" readOnly="true">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Amount')." ".currency() }}</label>
                <input type="text" class="form-control float-field" name="amount" value="{{ old('amount') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Payment Method') }}</label>
                <select class="form-control select2" name="payment_method_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("payment_methods","id","name",old('payment_method_id'),array("company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Reference') }}</label>
                <input type="text" class="form-control" name="reference" value="{{ old('reference') }}">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Attachment') }}</label>
                <input type="file" class="form-control dropify" name="attachment">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Note') }}</label>
                <textarea class="form-control" name="note">{{ old('note') }}</textarea>
            </div>
        </div>

        <input type="hidden" name="invoice_id" value="{{ $id }}">
        <input type="hidden" name="client_id" value="{{ $invoice->client_id }}">

        <div class="col-md-12">
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
            </div>
        </div>
    </div>
</form>