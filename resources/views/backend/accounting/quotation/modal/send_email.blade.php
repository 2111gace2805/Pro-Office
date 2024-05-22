<form action="{{ route('quotations.send_email') }}" class="validate" method="post">
    {{ csrf_field() }}
    <div class="row p-2">
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Email Template') }}</label>
                <select class="form-control select2" id="select_company_email_template">
                    <option value="">{{ _lang('Select Email Template') }}</option>
                    {{ create_option("company_email_template","id","name",old('email_template'),array("company_id=" => company_id(), " and related_to=" => "quotation")) }}
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label
                    class="control-label">{{ _lang('Client Quotation Link').' ('._lang('You can send this link').')' }}</label>
                <input type="text" class="form-control" value="{{ route('client.view_quotation', encrypt($quotation->id)) }}"
                    readOnly="true">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Email Subject') }}</label>
                <input type="text" class="form-control" id="email_subject" name="email_subject"
                    value="{{ old('email_subject') }}" required>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Email Message') }}</label>
                <textarea class="form-control summernote" id="email_message" name="email_message">{{ old('email_message') }}</textarea>
            </div>
        </div>

        <input type="hidden" name="contact_email" value="{{ $client_email }}">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

        <div class="col-md-12">
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg">{{ _lang('Send Email') }}</button>
            </div>
        </div>
    </div>
</form>