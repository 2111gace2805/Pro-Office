@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('public/backend/assets/css/invoice.css') }}">

    <div class="row">
        <div class="col-md-12">
            <div class="row g-2">
                <div class="col-md">
                  <div class="form-floating">
                                   {{-- <a class="btn btn-info btn-sm ajax-modal" data-title="{{ _lang('Send Email') }}"
                    href="{{ route('invoices.send_email',$invoice->id) }}"><i class="ti-email"></i>
                    {{ _lang('Send Email') }}</a> --}}
                    {{-- <a class="btn btn-primary btn-sm print" href="#" data-print="invoice-view"><i class="ti-printer"></i>
                    {{ _lang('Imprimir pagina') }}</a> --}}
                    <a class="btn btn-primary btn-sm" href="{{ route('invoices.download_pdf', $invoice->id) }}" target="_blank"><i
                        class="ti-file"></i> {{ _lang('Imprimir DTE') }}</a>
                    <a class="btn btn-secondary btn-sm ml-2 mr-2"
                        href="{{ route('invoices.download_pdf', $invoice->id) }}?ticket=true" target="_blank"><i
                            class="fa-solid fa-receipt"></i> {{ _lang('Imprimir ticket') }}</a>
                    @if ($invoice->status != 'Paid')
                        <a class="btn btn-success btn-sm ajax-modal" data-title="{{ _lang('Make Payment') }}"
                            href="{{ route('invoices.create_payment', $invoice->id) }}"><i class="ti-receipt"></i>
                            {{ _lang('Make Payment') }}</a>
                    @endif
                    {{-- <a class="btn btn-warning btn-sm" href="{{ action('InvoiceController@edit', $invoice->id) }}"><i
                        class="ti-pencil-alt"></i> {{ _lang('Edit') }}</a> --}} 
                  </div>
                </div>
                <div class="col-md">
                  <div class="form-floating">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-file-invoice"></i> Nueva factura
                    </a> 
                    <a href="{{ route('invoices.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lista de factura
                    </a> 
                  </div>
                </div>
            </div>
            <div class="card" style="height:100%; overflow-y: scroll;">
                <div class="card-body" style="height:100%">
                    @include('backend.accounting.invoice.ccf.invoice-view')
                </div>
            </div>
            <div class="mt-3 p-2 bg-white"><b>NOTA:</b> {{ $invoice->note }}</div>
        </div>

    </div>
@endsection
