@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Create Invoice') }}</h4>
            </div>

            <div class="card-body">
                <form method="post" class="validate" id="frmInvoices" autocomplete="off" action="{{ route('invoices.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de factura') }}</label>
                                <select class="form-control" name="tipodoc_id" id="tipodoc_id" required onchange="validoDatos(this)">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_documento", "tipodoc_id", "tipodoc_nombre", old('tipodoc_id'), ["tipodoc_estado=" => 'Activo']) }}
                                </select>
                            </div>
                        </div>

                        {{-- <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Invoice Number') }}</label>
                                <input type="text" class="form-control" name="invoice_number" id="invoice_number"
                                    value="{{ old('invoice_number') }}" required onkeydown="validoDatos(this)" readonly>
                                <input type="hidden" name="invoice_starting_number" id="invoice_starting_number" 
                                    value="{{ old('invoice_number') }}">
                            </div>
                        </div> --}}

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Invoice Date') }}</label>
                                <input type="text" class="form-control" name="invoice_date"
                                    value="{{ old('invoice_date') ?? now()->format('d/m/Y') }}" required readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Hora') }}</label>
                                <input type="time" class="form-control" name="invoice_time" id="invoice_time" readonly value="{{ now()->format('H:i:s') }}" required>
                            </div>
                        </div>
                        
                    
                          <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Status') }}</label>
                                <select class="form-control select2" name="status">
                                    <option value="Unpaid">{{ _lang('Unpaid') }}</option>
                                    <option value="Paid">{{ _lang('Paid') }}</option>
                                    <option value="Partially_Paid">{{ _lang('Partially Paid') }}</option>
                                    <option value="Canceled">{{ _lang('Canceled') }}</option>
                                </select>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de modelo') }}</label>
                                <select class="form-control" name="modfact_id" id="modfact_id" required>
                                    <option value="">{{ _lang('Select one') }}</option>
                                    {{ create_option("modelo_facturacion", "modfact_id", "modfact_nombre", old('modfact_id', '1')) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de transmisión') }}</label>
                                <select class="form-control" name="tipotrans_id" id="tipotrans_id" required>
                                    <option value="">{{ _lang('Select one') }}</option>
                                    {{ create_option("tipo_transmision", "tipotrans_id", "tipotrans_nombre", old('tipotrans_id', '1')) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de condición') }}</label>
                                <select class="form-control" name="conop_id" id="conop_id" required onchange="setPlazo(this);">
                                    <option value="">{{ _lang('Select one') }}</option>
                                    {{ create_option("condicion_operacion", "conop_id", "conop_nombre", old('conop_id', '1')) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">¿Desea posponer transmisión de DTE?</label>
                                <select class="form-control" name="postpone_invoice" id="postpone_invoice" required>
                                    <option value="">{{ _lang('Select one') }}</option>
                                    <option value="0" selected>No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                        </div>

                        <div style="background-color: #efefef; height: 2px; width: 100%; margin-bottom: 25px; margin-top: 12px;"></div>

                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <a href="{{ route('contacts.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Client') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="company_name" data-table="contacts"  name="client_id" id="client_id" required onchange="validoDatos(this);getNombre(this);">
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4" id="dvLicitacion" style="display:none;">
                            <div class="form-group">
                                <label class="control-label">¿Es una Licitación?</label>
                                <select class="form-control" name="venta_licitacion" id="venta_licitacion" required>
                                    <option value="">{{ _lang('Select one') }}</option>
                                    <option value="0" selected>No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre en Factura') }}</label>
                                <input type="text" class="form-control" name="name_invoice" id="name_invoice" value="{{ old('name_invoice') }}" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nombre comercial') }}</label>
                                <input type="text" class="form-control" name="nombre_comercial" id="nombre_comercial" readonly
                                value="{{ old('nombre_comercial') }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de documento') }}</label>
                                <select class="form-control" name="tdocrec_id" id="tdocrec_id" style="pointer-events: none;">
                                <option value="">{{ _lang('Select One') }}</option>
                                {{ create_option("tipo_doc_ident_receptor", "tdocrec_id", "tdocrec_nombre", old('tdocrec_id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Numero de documento') }}</label>
                                <input type="text" class="form-control" name="num_documento" id="num_documento"
                                value="{{ old('num_documento') }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Correo') }}</label>
                                <input type="text" class="form-control" name="correo" id="correo"
                                value="{{ old('correo') }}" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Correo alterno ( opcional )') }}</label>
                                <input type="text" class="form-control" name="correo_alterno" id="correo_alterno"
                                value="{{ old('correo') }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Forma de pago') }}</label>
                                <select class="form-control select-filter" name="forp_id" id="forp_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("forma_pago", "forp_id", "forp_nombre", old('forp_id', '01'), ['forp_status='=> 'Active']) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Plazo') }}</label>
                                <select class="form-control" name="plazo_id" id="plazo_id_invoice">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("plazo", "plazo_id", "plazo_nombre", old('plazo_id')) }}
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Valor del plazo') }}</label>
                                <input type="text" class="form-control" name="periodo" id="periodo"
                                value="{{ old('periodo') }}">
                            </div>
                        </div>
                            


                        <p class="pl-3 col-12">
                        <a class="collapse-ti-angle" data-toggle="collapse" href="#campoExtrasReceptor" data-target="#clientFields" aria-expanded="false">
                            <i class="ti-angle-up no-sidebar"> Ver menos campos del receptor</i>
                            <i class="ti-angle-down no-sidebar"> Ver más campos del receptor</i>
                        </a>
                        </p>
                        <div class="collapse multi-collapse row" style="width: 100%; padding-left: 15px;" id="clientFields">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                        <label class="control-label">{{ _lang('Tipo de persona') }}</label>
                                        <select readonly="true" class="form-control" name="tpers_id" id="tpers_id_invoice" style="pointer-events: none;">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option("tipo_persona", "tpers_id", "tpers_nombre", old('tpers_id')) }}
                                        </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Pais') }}</label>
                                    <select readonly="true" class="form-control" name="pais_id" id="pais_id_invoice" style="pointer-events: none;">
                                      <option value="">{{ _lang('Select One') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Teléfono') }}</label>
                                    <input type="text" class="form-control" name="telefono" id="telefono"
                                    value="{{ old('telefono') }}" readonly>
                                </div>
                            </div>
                            
                            

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Dirección') }}</label>
                                    <input type="text" class="form-control" name="complemento" id="complemento"
                                    value="{{ old('complemento') }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Departamento') }}</label>
                                    <select readonly="true" class="form-control" name="depa_id" id="depa_id_invoice"
                                     style="pointer-events: none;">
                                      <option value="">{{ _lang('Select One') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Municipio') }}</label>
                                    <select readonly="true" class="form-control" name="munidepa_id" id="munidepa_id_invoice"
                                     style="pointer-events: none;">
                                      <option value="">{{ _lang('Select One') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 row p-0 m-0">
                                <div class="col">
                                     <div class="form-group">
                                        <label class="control-label">{{ _lang('Actividad económica') }}</label>
                                        <select class="form-control select2" name="actie_id" id="actie_id_invoice" style="pointer-events: none;">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option("actividad_economica", "actie_id", "actie_nombre", old('actie_id'), ['actie_padre='=>"no"]) }}
                                        </select>
                                     </div>
                                 </div>

                                {{-- <div class="col">
                                     <div class="form-group">
                                           <label class="control-label">{{ _lang('Descripción de actividad economica') }}</label>
                                          <input type="text" class="form-control" name="desc_actividad" id="desc_actividad" value="{{ old('desc_actividad') }}">
                                        </div>
                                  </div> --}}
                            </div>
                            
                        </div>
                        
                        {{-- <div id="dividerRecinto" style="background-color: #efefef; height: 2px; width: 100%; margin-bottom: 25px; margin-top: 12px;"></div>

                        
                        <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Recinto fiscal') }}</label>
                                    <select class="form-control" name="refisc_id" id="refisc_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("recinto_fiscal", "refisc_id", "refisc_nombre", old('refisc_id', get_option('refisc_id_default'))) }}
                                    </select>
                                </div>
                            </div>
                        
                        <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Régimen') }}</label>
                                    <select class="form-control select2" name="regi_id" id="regi_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("regimen", "regi_id", "regi_nombre", old('regi_id', get_option('regi_id_default'))) }}
                                    </select>
                                </div>
                            </div> --}}
                        
                        {{-- <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('No. Anexo de descargo') }}</label>
                                    <input type="text" class="form-control" name="no_anexo_descargo" id="no_anexo_descargo"
                                    value="{{ old('no_anexo_descargo', str_pad(get_option('no_anexo_desc_starting', 1), 3, '0', STR_PAD_LEFT)) }}">
                                </div>
                            </div> --}}


                        <div style="background-color: #efefef; height: 2px; width: 100%; margin-bottom: 25px; margin-top: 12px;"></div>

                        <div class="col-md-12" id="dvIncoterms" style="display:none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('INCOTERMS') }}</label>
                                <select class="form-control" name="id_incoterms" id="id_incoterms">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("incoterms", "id", "nombre_incoterms", old('id')) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-11" id="dvDteRelacionado" style="display:none;">
                            <div class="form-group select-product-container">
                                <label class="control-label">{{ _lang('DTE relacionado') }}</label>
                                <select class="form-control select2_ajax" data-value="tipodoc_id" data-display="numero_control"
                                    data-table="invoices" data-where="03" name="doc_relacionado" id="doc_relacionado">
                                    <option value="">{{ _lang('Select') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group select-product-container">
                                <label class="control-label">{{ _lang('Vendedor') }}</label>
                                <select class="form-control" name="seller_code[]" id="seller_code" multiple>
                                    {{ create_option("users", "id", "name", old('id'), array("seller_code!="=>""))  }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group select-product-container">
                                <label class="control-label">{{ _lang('Kits') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="name"
                                    data-table="kits" name="kit" id="kit">
                                    <option value="">{{ _lang('Seleccionar Kit') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group select-product-container">
                                <a href="{{ route('products.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Product') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="item_name"
                                    data-table="items" data-where="2" name="product" id="product" data-items="con_stock">
                                    <option value="">{{ _lang('Select Product') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group select-product-container">
                                <a href="{{ route('products.createService') }}" data-reload="false"
                                    data-title="{{ _lang('Add Service') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Service') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="item_name"
                                    data-table="items" data-where="5" name="service" id="service">
                                    <option value="">{{ _lang('Select Service') }}</option>
                                </select>
                            </div>
                        </div>
                        @if( $invoiceCCF != null )
                            <div class="col-md-12">
                                <div class="alert alert-primary" role="alert">
                                    La nota de crédito puede emitirse por el valor total o por el valor parcial del Crédito Fiscal seleccionado.
                                    <br>
                                    Total de Crédito Fiscal emitido  con IVA: <b id="nc_tot_ccf" >$0</b>
                                    <br>
                                    Disponible de Crédito Fiscal con IVA + Notas de débito generadas: <b id="nc_disp_ccf" >$0</b>
                                </div>
                                <input type="hidden" id="nc_total_ccf" value="">
                                <input type="hidden" id="nc_disponible_ccf" value="">
                            </div>
                        @endif
                        <!--Order table -->
                        @php $currency = currency(); @endphp

                        @php $taxes = App\Tax::all(); @endphp

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="order-table" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Línea') }}</th>
                                            <th class="d-none">{{ _lang('Código') }}</th>
                                            <th>{{ _lang('Description') }}</th>
                                            <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                                            <th class="text-right">{{ _lang('Unit Cost') }}</th>
                                            {{-- <th class="text-right wp-100">{{ _lang('Discount') }}</th> --}}
                                            <th class="text-right">{{ _lang('Tax') }}</th>
                                            <th class="text-right">{{ _lang('Line Total') }}</th>
                                            <th class="text-center">{{ _lang('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($invoice != null)
                                            @foreach($invoice->invoice_items as $key => $item)
                                            @php $stock = $item->item->product_stock->where('company_id', company_id())->first()->quantity??0; @endphp
											{{-- <tr id="product-{{ $item->item_id }}">
												<td style="width: 95px"><input type="number" name="line[]" class="form-control input-line" value="{{ $key+1 }}"></td>
												<td class="input-product-code" title="Producto original: {{$item->item->product->original}}">{{ $item->item->product->product_code }}</td>
												<td class="description">
                                                    <textarea cols="1" rows="2" name="product_description[]" class="form-control input-description">{{ $item->item->item_name.' - '.$item->description }}</textarea>
                                                </td>
												<td class="text-center quantity" title="Cantidad máxima: {{$stock}}"><input type="number" name="quantity[]" min="1" class="form-control input-quantity text-center" value="{{ $item->quantity }}"></td>
												<td class="text-right unit-cost" style="width:110px"><input type="text" name="unit_cost[]" class="form-control input-unit-cost text-right" value="{{ $item->unit_cost }}"></td>
												<td class="text-right discount d-none"><input type="text" name="discount[]" class="form-control input-discount text-right" value="{{ $item->discount }}"></td>
												<td class="text-right tax">
													<select class="form-control auto-multiple-select selectpicker input-tax" name="tax[{{ $item->item_id }}][]" title="{{ _lang('Select TAX') }}" data-selected="{{ $item->taxes->pluck('tax_id') }}" multiple="true">
														@foreach($taxes as $tax)
															<option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">{{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
														@endforeach
													</select>
												</td>
												<td class="text-right sub-total" style="width:115px"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="{{ $item->sub_total }}" readonly></td>
												<td class="text-center">
													<button type="button" class="btn btn-danger btn-xs remove-product"><i class='ti-trash'></i></button>
                                                    <button type="button" class="btn btn-info btn-sm boton-descargo d-none" onclick="modalInfoDescargo({{$item->item_id}})" title="Información de anexo de descargo"><i class='ti-list'></i></button>
                                                    @include('backend.accounting.invoice.modal.info_descargo')
												</td>
												<input type="hidden" name="product_id[]" value="{{ $item->item_id }}">
												<input type="hidden" name="product_tax[]" class="input-product-tax" value="{{ $item->tax_amount }}">
                                                <input type="hidden" name="product_stock[]" class="input-product-stock" value="{{$stock}}">
                                                <input type="hidden" name="product_price[]" class="input-product-price" value="{{$item->item->product->product_price}}">
											</tr> --}}
										@endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="tfoot active">
                                        <tr>
                                            <th>{{ _lang('Sumas') }}</th>
                                            <th class="d-none"></th>
                                            <th></th>
                                            <th class="text-center" id="total-qty">0</th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right d-none" id="total-discount">0.00</th>
                                            <th class="text-right" id="total">0.00</th>
                                            <th class="text-center"></th>
                                            <input type="hidden" name="product_total" id="product_total" value="0">
                                        </tr>
                                    </tfoot>
                                </table>

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-check" id="dvIvaRetenido">
                                            <input class="form-check-input" type="checkbox" value="" name="chkIvaRetenido" id="chkIvaRetenido">
                                            <label class="form-check-label" for="chkIvaRetenido">
                                                IVA RETENIDO
                                            </label>
                                        </div>
                                        <div class="form-check mt-2" id="dvVentaExcenta">
                                            <input class="form-check-input" type="checkbox" value="1" name="chkVentaExenta" id="chkVentaExenta">
                                            <label class="form-check-label" for="chkVentaExenta">
                                                VENTA EXENTA
                                            </label>
                                        </div>
                                        <div class="form-check mt-2" id="dvRetencionRenta">
                                            <input class="form-check-input" type="checkbox" value="1" name="chkRetencionRenta" id="chkRetencionRenta">
                                            <label class="form-check-label" for="chkRetencionRenta">
                                                RETENCIÓN RENTA
                                            </label>
                                        </div>
                                        <div class="mt-3" id="dvGeneralDiscounts">
                                            <label class="control-label">Descuentos</label>
                                            <select class="form-control" name="general_discount_id" id="general_discount_id">
                                                <option value="">{{_lang('Select One')}}</option>
                                                @foreach ($generalDiscounts as $generalDiscount)
                                                    <option value="{{$generalDiscount->id}}" data-type="{{$generalDiscount->type}}" data-value="{{$generalDiscount->value}}">{{$generalDiscount->name}}</option>
                                                @endforeach
                                                <option value="other">Otro</option>
                                            </select>
                                            <div class="w-100" style="display: none" id="dvGeneralDiscountOther">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="general_discount_type" id="PercentageOption" value="Percentage" checked>
                                                    <label class="form-check-label" for="PercentageOption">Porcentaje</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="general_discount_type" id="FixedOption" value="Fixed">
                                                    <label class="form-check-label" for="FixedOption">Fijo</label>
                                                </div>
                                                <input type="number" step="0.01" class="form-control" name="general_discount_value" id="general_discount_value"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <table class="table table-bordered">
                                            <tfoot class="tfoot active">
                                                <tr>
                                                    <th>{{ _lang('Descuento general') }}</th>
                                                    <th class="text-right">$ <span id="th_discount_general">0.00</span></th>
                                                    <input type="hidden" name="discount_general" id="discount_general" value="0">
                                                </tr>
                                                <tr id="tr-_subtotal_2">
                                                    <th>{{ _lang('Subtotal') }}</th>
                                                    <th class="text-right">$ <span id="subtotal_2">0.00</span></th>
                                                    <input type="hidden" name="_subtotal_2" id="_subtotal_2" value="0">
                                                </tr>
                                                <tr id="tr-impuesto">
                                                    <th>{{ _lang('Impuesto') }}</th>
                                                    <th class="text-right" id="total-tax">0.00</th>
                                                    <input type="hidden" name="tax_total" id="tax_total" value="0">
                                                </tr>
                                                <tr id="tr-subtotal">
                                                    <th>{{ _lang('Sumatorias con impuestos') }}</th>
                                                    <th class="text-right" id="sumatorias_impuestos">0.00</th>
                                                </tr>
                                                <tr id="tr-iva-retenido">
                                                    <th>{{ _lang('IVA retenido') }}</th>
                                                    <th class="text-right" id="iva-retenido">0.00</th>
                                                    <input type="hidden" name="iva_retenido" id="iva_retenido" value="0">
                                                </tr>
                                                <tr id="tr-iva-percibido">
                                                    <th>{{ _lang('IVA percibido') }}</th>
                                                    <th class="text-right" id="iva-percibido">0.00</th>
                                                    <input type="hidden" name="iva_percibido" id="iva_percibido" value="0">
                                                </tr>
                                                <tr id="tr-retencion-renta">
                                                    <th>{{ _lang('Retención Renta') }}</th>
                                                    <th class="text-right" id="retencion-renta">0.00</th>
                                                    <input type="hidden" name="retencion_renta" id="retencion_renta" value="0">
                                                </tr>
                                                <tr id="tr-isr-retenido">
                                                    {{-- <th>{{ _lang('Renta retenida') }}</th> --}}
                                                    {{-- <th class="d-flex justify-content-end"> --}}
                                                        <input type="number" class="form-control w-25" hidden name="isr_retenido" id="isr_retenido" value="0" />
                                                    {{-- </th> --}}
                                                </tr>
                                                <tr>
                                                    <th>{{ _lang('Total a pagar') }}</th>
                                                    <th class="text-right" id="grand-total">0.00</th>
                                                    <input type="hidden" name="grand_total" id="grand_total" value="0">
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!--End Order table -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Note') }}</label>
                                <textarea class="form-control" rows="4" name="note">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        {{-- <div class="alert alert-warning mx-2 w-100">* Los datos del cliente son obligatorios si el total de la factura es igual o mayor a tres salarios mínimos.</div> --}}

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="button" onclick="saveInvoice(event)" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                                    {{ _lang('Save Invoice') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<select class="form-control d-none" id="tax-selector">
    @foreach(App\Tax::all() as $tax)
    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">
        {{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
    @endforeach
</select>

@endsection


@section('js-script')
<script src="{{ asset('public/backend/plugins/jquery-alphanum/jquery.alphanum.js') }}"></script>
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/invoice.js') . '?v=' . uniqid() }}"></script>

<script>
    @if ($invoice != null)
        $(document).ready(function(){
            let invoice = @json($invoice);
            selectedContact = invoice.client;
            $('#tipodoc_id').val(invoice.tipodoc_id);
            $('#tipodoc_id').trigger('change');
            $('#client_id').append($('<option>', {
                value: selectedContact.id,
                text: selectedContact.company_name+' NRC:'+selectedContact.nrc,
                selected: 'selected'
            }));
            $('#client_id').trigger('change');
        });
    @endif

    @if( $invoiceCCF != null )

        $("#product").closest(".col-md-6").hide()
        $("#service").closest(".col-md-6").hide()
        $("#kit").closest(".col-md-6").hide()

        let invoiceCCF = @json($invoiceCCF);
        let dte = invoiceCCF.dte_asociado;
        let items = dte.invoice_items;

        $(document).ready(function(){

            $("#tipodoc_id").val(invoiceCCF.type).trigger("change");
            $("#tipodoc_id").attr("style", "pointer-events: none;");

            let total = parseFloat( dte.subtotal ) - ( parseFloat( dte.general_discount ) ) + parseFloat( dte.tax_total )-parseFloat( dte.iva_retenido );
            let notas_creadas_nc = parseFloat( invoiceCCF.total_notas_nc ) - ( parseFloat( invoiceCCF.total_desc_nc ) ) + parseFloat( invoiceCCF.total_taxs_nc )-parseFloat( invoiceCCF.iva_retenido_nc );
            let notas_creadas_nd = parseFloat( invoiceCCF.total_notas_nd ) - ( parseFloat( invoiceCCF.total_desc_nd ) ) + parseFloat( invoiceCCF.total_taxs_nd )-parseFloat( invoiceCCF.iva_retenido_nd );
            
            let disponible = parseFloat( total ) - parseFloat( notas_creadas_nc ) + parseFloat( notas_creadas_nd );

            $("#nc_tot_ccf").html("$"+total.toFixed(2))
            $("#nc_disp_ccf").html("$"+disponible.toFixed(2))


            let total_sin_iva = parseFloat( dte.subtotal ) - ( parseFloat( dte.general_discount ) );
            let notas_creadas_nc_sin_iva = parseFloat( invoiceCCF.total_notas_nc ) - ( parseFloat( invoiceCCF.total_desc_nc ) );
            let notas_creadas_nd_sin_iva = parseFloat( invoiceCCF.total_notas_nd ) - ( parseFloat( invoiceCCF.total_desc_nd ) );
            
            let disponible_sin_iva = parseFloat( total_sin_iva ) - parseFloat( notas_creadas_nc_sin_iva ) + parseFloat( notas_creadas_nd_sin_iva );

            $("#nc_total_ccf").val(total_sin_iva.toFixed(2))
            $("#nc_disponible_ccf").val(disponible_sin_iva.toFixed(2))

            setSelect2CCF().then(() => {
                $.each(items, function(index, value){
                    
                    $("#client_id").select2("trigger", "select", {
                        data: { id: dte.client_id, text: dte.client.company_name + ' - NIT: ' + dte.client.nit }
                    });
                    
                    $("#doc_relacionado").select2("trigger", "select", {
                        data: { id: dte.id }
                    });

                    if( value.item_id > 0 ){
                        $("#product").select2("trigger", "select", {
                            data: { id: value.item_id }
                        });
                    }
                    else{
                        $("#kit").select2("trigger", "select", {
                            data: { id: value.kit_id }
                        });
                    }

                })
            }).catch((error) => {
                console.error('Error durante la ejecución de la función:', error);
            });
        });

        $(document).ajaxStop(function() {
            $.each(items, function(index, value){

                let cantidad = parseInt( value.quantity  )
                $("#order-table").find("#product-"+value.item_id).find(".input-quantity").val(cantidad).trigger("change");
            });
        });
    @endif


    @if( $invoiceNR != null )

        $("#product").closest(".col-md-6").hide()
        $("#service").closest(".col-md-6").hide()
        $("#kit").closest(".col-md-6").hide()

        let invoiceNR = @json($invoiceNR);
        let dte = invoiceNR.dte_asociado;
        let items = dte.invoice_items;

        $(document).ready(function(){
            let type = {{ $type }};
            type = ( type == 3 ) ? "03" : "01";


            $("#tipodoc_id").val(type).trigger("change");
            $("#tipodoc_id").attr("style", "pointer-events: none;");

            $("#seller_code").val(dte.user_id).trigger("change")

            $.each(items, function(index, value){
                
                $("#client_id").select2("trigger", "select", {
                    data: { id: dte.client_id, text: dte.client.company_name + ' - NIT: ' + dte.client.nit }
                });

                if( value.item_id > 0 ){
                    $("#product").select2("trigger", "select", {
                        data: { id: value.item_id }
                    });
                }
                else{
                    $("#kit").select2("trigger", "select", {
                        data: { id: value.kit_id }
                    });
                }
            })
        });

        $(document).ajaxStop(function() {
            $.each(items, function(index, value){

                let cantidad = parseInt( value.quantity  )
                $("#order-table").find("#product-"+value.item_id).find(".input-quantity").val(cantidad).trigger("change");
            });
        });
    @endif

    @if( $nota_pedido != null )

        $("#product").closest(".col-md-6").hide()
        $("#service").closest(".col-md-6").hide()
        $("#kit").closest(".col-md-6").hide()

        let nota = @json($nota_pedido);
        let datos = nota.datos;
        let items = datos.details;
        
        $(document).ready(function(){

            $("#tipodoc_id").val("04").trigger("change");
            $("#tipodoc_id").attr("style", "pointer-events: none;");

            $("#client_id").select2("trigger", "select", {
                data: { id: datos.client_id, text: datos.client.company_name + ' - NIT: ' + datos.client.nit }
            });   

            $.each(items, function(index, value){

                $("#product").select2("trigger", "select", {
                    data: { id: value.product_id }
                });
            });

            
        });

        $(document).ajaxStop(function() {
            $.each(items, function(index, value){
                $("#order-table").find("#product-"+value.product_id).find(".input-quantity").val( value.quantity ).trigger("change");
            });
        });
    @endif

    percepcion_iva = parseFloat(@json(get_option('percepcion_iva')));
    retencion_iva = parseFloat(@json(get_option('retencion_iva')));
    retencion_isr = parseFloat(@json(get_option('retencion_isr')));
    techo_percepcion_iva = parseFloat(@json(get_option('techo_percepcion_iva')));
    techo_retencion_iva = parseFloat(@json(get_option('techo_retencion_iva')));
    techo_retencion_isr = parseFloat(@json(get_option('techo_retencion_isr')));
    gran_contribuyente = @json(get_option('gran_contribuyente'));

    // Obtener la hora actual
    // var now = new Date();
    // var options = { timeZone: 'America/El_Salvador' };
    // var formattedTime = now.toLocaleTimeString('es-SV', options);

    // Establecer el valor del campo de entrada de hora
    // document.getElementById("invoice_time").value = formattedTime;

    function setPlazo( select ){

        let value       = $(select).val();
        let plazo       = $("#plazo_id_invoice");
        let plazo_Valor = $("#periodo");

        if( value == 2 ){
            plazo.attr("required", true);
            plazo_Valor.attr("required", true);
        }
        else{
            plazo.attr("required", false);
            plazo_Valor.attr("required", false);
            
            plazo.removeClass("parsley-error");
            plazo_Valor.removeClass("parsley-error");
        }
    }
</script>
@endsection