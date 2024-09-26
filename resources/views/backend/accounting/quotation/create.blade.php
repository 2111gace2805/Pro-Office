@extends('layouts.app')

@section('content')
    <link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">{{ _lang('Create Quotation') }}</h4>
                    <span class="position-absolute" style="top: 15px; right: 30px"><a style="font-size: 14px;"
                            href="/quotations/download_example/1">{{ _lang('Download template') }}</a></span>
                </div>

                <div class="card-body">
                    <form onsubmit="validateFile()" method="post" class="validate" autocomplete="off"
                        action="{{ route('quotations.store') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Quotation Number') }}</label>
                                    <input type="text" class="form-control" name="quotation_number"
                                        value="{{ old('invoice_number', get_option('quotation_prefix') . get_option('quotation_starting', 1001)) }}"
                                        required>
                                    <input type="hidden" name="quotation_starting_number"
                                        value="{{ get_option('quotation_starting', 1001) }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Quotation Date') }}</label>
                                    <input type="text" class="form-control datepicker" name="quotation_date"
                                        value="{{ old('quotation_date') }}" required>
                                </div>
                            </div>

                            <div class="col-8">
                                <div class="form-group">
                                    <a href="{{ route('contacts.create') }}" data-reload="false"
                                        data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i
                                            class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                    <label class="control-label">{{ _lang('Select Client') }}</label>
                                    <select class="form-control select2-ajax" data-value="id"
                                        data-table="contacts" name="client_id" id="client_id" data-display="company_name" data-display2="nrc" data-display2label="NRC" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Incluir IVA') }}</label>
                                    <select class="form-control" name="incluir_iva" id="incluir_iva">
                                        <option value="si" selected>Sí</option>
                                        <option value="no">No</option>
                                    </select>
                                    <small>* Se mostrará en precios del archivo EXCEL</small>
                                </div>
                            </div>

                            


                            {{-- <input type="file" name="excelFile" class="form-control d-none" id="excelFile" onchange="chosenFile(this)"> --}}
                            {{ csrf_field() }}

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Attachment') }} (.xlsx, .xls)</label>
                                    <input type="file" class="form-control dropify" name="excelFile" id="excelFile"
                                        accept=".xlsx, .xls">
                                </div>
                            </div>

                            <div class="col-md-12 mt-5">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                                        {{ _lang('Save Quotation') }}</button>
                                </div>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('public/backend/assets/js/quotation.js') }}"></script>
    <script>
        function validateFile() {
            if ($('#excelFile').prop('files').length == 0) {
                $.toast({
                    heading: 'Archivo no adjuntado',
                    text: 'No has adjuntado ningún archivo de tipo EXCEL (.xlsx, xls)',
                    hideAfter: false,
                    icon: 'error',
                    position: 'bottom-left',
                });
                event.preventDefault();
            }
        }
    </script>
@endsection
