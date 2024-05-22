@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <div class="card-title text-black">{{ _lang('Transfer Detail') }}</div>
                </div>

                <div class="card-body row">

                    <div class="form-group col-xl-4 col-md-4 col-sm-12">
                        <label for="customer_id" class="control-label text-black">{{_lang('Company Name')}}</label>
                        <input type="text" class="form-control pull-right" value="{{$info->sendingCompany->company_name}}" disabled>
                    </div>

                    <div class="form-group col-xl-4 col-md-4 col-sm-12">
                        
                        <label for="sales_date" class="control-label text-black">{{ _lang('Departure date') }} </label>
                        <input type="text" class="form-control pull-right datepicker" 
                        disabled value="{{$info->transfer_datesend}}">
                    </div>

                    <div class="form-group col-xl-4 col-md-4 col-sm-12">
                        <label for="reference_no" class="control-label text-black">{{ _lang('Reference') }}</label>
                        <input type="text" value="{{$info->transfer_code}}" class="form-control"
                         id="reference_no" name="reference_no" placeholder="" disabled>
                    </div>

                    <div class="form-group col-12">
                        <label for="reference_no" class="control-label text-black">{{ _lang('Note') }}</label>
                        <textarea name="" disabled class="form-control">{{$info->note}}</textarea>
                    </div>

                    <br>

                    <table class="table table-hover table-bordered" style="width:100%;" id="sales_table">
                        <thead class="custom_thead">
                            <tr class="bg-primary">
                                <th style="width:15%">{{ _lang('Product') }}</th>
                                <th style="width:10%">{{ _lang('Quantity') }}</th>
                                <th style="width:10%">{{ _lang('Receive') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="text-center" style="width:15%">{{ $item->item_name ?? '' }}</td>
                                    <td class="text-center" style="width:10%">{{ $item->quantity ?? '' }}</td>
                                    <td class="text-center" style="width:10%">{{ $item->product_recieve ?? 0 }}</td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection