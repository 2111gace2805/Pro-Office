@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Recieve Transfer') }}</h4>
            </div>

            @php $currency = currency(); @endphp

            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('passes.recieved') }}"
                    enctype="multipart/form-data" id="FormTransfer">
                    {{ csrf_field() }}

                    <div class="row">

                        <div class="col-xl-4 col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Company') }}</label>
                                <select class="form-control select2" name="company" required>
                                    @foreach ($companies as $item)
                                        <option value="{{$item->id}}">{{$item->company_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Reference') }}</label>
                                <input type="text" class="form-control" required name="reference" value="{{old('reference')}}">
                            </div>
                        </div>
                        
                        <div class="col-auto m-auto">
                            <button class="btn btn-success" type="button" 
                                onclick="PeticionAjax('#FormTransfer', '{{route('recieved-items')}}')">
                                <i class="fa fa-search"></i> &nbsp; {{_lang('Search')}}
                            </button>
                        </div>

                        <!--Order table -->
                        <div class="col-12" id="content-replace">
                            <div class="table-responsive">
                                <table id="order-table" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ _lang('Product') }}</th>
                                            <th class="text-center">{{ _lang('Quantity') }}</th>
                                            <th class="text-center">{{ _lang('Receive') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<select class="form-control d-none" id="tax-selector">
    @foreach(App\Tax::where("company_id",company_id())->get() as $tax)
    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">
        {{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
    @endforeach
</select>

@endsection

@section('js-script')

<script>

    function MetodoReload(response){
        let base_url = $("#base_url").val().trim();
        location.replace(base_url + "transfers/recibidos");
    }

    function MetodoReplace(response){
        $('#content-replace').html(response);
    }
    
    function PeticionAjax(form, Url, type = 'POST', metodo = MetodoReplace){
        let infoForm = new FormData($(form)[0]);

        $.ajax({
            type: 'POST',
            url: Url,
            data: infoForm,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                metodo(response);
            },error: function(error){ console.log(error); }
        });
    }
</script>
@endsection