@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Update Invoice') }}</h4>
            </div>

            <div class="card-body">
                <form method="post" class="validate" autocomplete="off"
                    action="{{ action('InvoiceController@update', $id) }}" enctype="multipart/form-data">
                    {{ csrf_field()}}
                    <input name="_method" type="hidden" value="PATCH">

                    <div class="row">
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de factura') }}</label>
                                <select class="form-control" name="tipodoc_id" id="tipodoc_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_documento", "tipodoc_id", "tipodoc_nombre", old('tipodoc_id', $invoice->tipodoc_id), ["tipodoc_estado=" => 'Activo']) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Invoice Number') }}</label>
                                <input type="text" class="form-control" name="invoice_number" id="invoice_number"
                                    value="{{ old('invoice_number', $invoice->invoice_number) }}" required>
                                <input type="hidden" name="invoice_starting_number" id="invoice_starting_number" 
                                    value="{{ old('invoice_number', $invoice->invoice_number) }}">
                                {{-- <input type="text" class="form-control" name="invoice_number"
                                    value="{{ old('invoice_number',get_option('invoice_prefix').get_option('invoice_starting',1001)) }}"
                                    required readonly>
                                <input type="hidden" name="invoice_starting_number"
                                    value="{{ get_option('invoice_starting',1001) }}"> --}}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Invoice Date') }}</label>
                                <input type="text" class="form-control datepicker" name="invoice_date"
                                    value="{{ old('invoice_date', $invoice->invoice_date) }}" required>
                            </div>
                        </div>

                    
                          <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Status') }}</label>
                                <select class="form-control select2" name="status">
                                    <option @if($invoice->status == "Unpaid") selected @endif value="Unpaid">{{ _lang('Unpaid') }}</option>
                                    <option @if($invoice->status == "Paid") selected @endif value="Paid">{{ _lang('Paid') }}</option>
                                    <option @if($invoice->status == "Partially_Paid") selected @endif value="Partially_Paid">{{ _lang('Partially Paid') }}</option>
                                    <option @if($invoice->status == "Canceled") selected @endif value="Canceled">{{ _lang('Canceled') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Due Date') }}</label>
                                <input type="text" class="form-control datepicker" name="due_date"
                                    value="{{ old('due_date', $invoice->due_date) }}" required>
                            </div>
                        </div>

                        
                        
                        
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de modelo') }}</label>
                                <select class="form-control" name="modfact_id" id="modfact_id">
                                    <option value="">{{ _lang('Select one') }}</option>
                                    {{ create_option("modelo_facturacion", "modfact_id", "modfact_nombre", old('modfact_id', $invoice->modfact_id)) }}
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
                                <select class="form-control select2-ajax" data-value="id" data-display="company_name" data-display2="nrc" data-display2label="NRC"
                                    data-table="contacts"  name="client_id" id="client_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nombre comercial') }}</label>
                                    <input type="text" class="form-control" name="nombre_comercial" id="nombre_comercial"
                                    value="{{ old('nombre_comercial', $invoice->nombre_comercial) }}">
                                </div>
                            </div>

                        <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Tipo de persona') }}</label>
                                    <select readonly="true" class="form-control" name="tpers_id" id="tpers_id_invoice" required style="pointer-events: none;">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_persona", "tpers_id", "tpers_nombre", old('tpers_id', $invoice->tpers_id)) }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Tipo de documento') }}</label>
                                    <select class="form-control" name="tdocrec_id" id="tdocrec_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("tipo_doc_ident_receptor", "tdocrec_id", "tdocrec_nombre", old('tdocrec_id', $invoice->tdocrec_id)) }}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Numero de documento') }}</label>
                                    <input type="text" class="form-control" name="num_documento" id="num_documento"
                                    value="{{ old('num_documento', $invoice->num_documento) }}">
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Plazo') }}</label>
                                    <select class="form-control" name="plazo_id" id="plazo_id_invoice">
                                      <option value="">{{ _lang('Select One') }}</option>
                                      {{ create_option("plazo", "plazo_id", "plazo_nombre", old('plazo_id', $invoice->plazo_id)) }}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Valor del plazo') }}</label>
                                    <input type="text" class="form-control" name="periodo" id="periodo"
                                    value="{{ old('periodo', $invoice->periodo) }}">
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
                                    value="{{ old('telefono') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Correo') }}</label>
                                    <input type="text" class="form-control" name="correo" id="correo"
                                    value="{{ old('correo') }}">
                                </div>
                            </div>
                            

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Dirección') }}</label>
                                    <input type="text" class="form-control" name="complemento" id="complemento"
                                    value="{{ old('complemento') }}">
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
                                        <select class="form-control select2" name="actie_id" id="actie_id_invoice">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option("actividad_economica", "actie_id", "actie_nombre", old('actie_id', $invoice->actie_id), ['actie_padre='=>"no"]) }}
                                        </select>
                                     </div>
                                 </div>

                                <div class="col">
                                     <div class="form-group">
                                           <label class="control-label">{{ _lang('Descripción de actividad economica') }}</label>
                                          <input type="text" class="form-control" name="desc_actividad" id="desc_actividad" value="{{ old('desc_actividad', $invoice->desc_actividad) }}">
                                        </div>
                                  </div>
                            </div>
                            
                        </div>
                        
                        <div id="dividerRecinto" style="background-color: #efefef; height: 2px; width: 100%; margin-bottom: 25px; margin-top: 12px;"></div>

                        
                        <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Recinto fiscal') }}</label>
                                    <select class="form-control" name="refisc_id" id="refisc_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("recinto_fiscal", "refisc_id", "refisc_nombre", old('refisc_id', $invoice->refisc_id)) }}
                                    </select>
                                </div>
                            </div>
                        
                        <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Régimen') }}</label>
                                    <select class="form-control select2" name="regi_id" id="regi_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("regimen", "regi_id", "regi_nombre", old('regi_id', $invoice->regi_id)) }}
                                    </select>
                                </div>
                            </div>


                        <div style="background-color: #efefef; height: 2px; width: 100%; margin-bottom: 25px; margin-top: 12px;"></div>


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
                                <a href="{{ route('services.create') }}" data-reload="false"
                                    data-title="{{ _lang('Add Service') }}" class="ajax-modal select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <label class="control-label">{{ _lang('Select Service') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="item_name"
                                    data-table="items" data-where="5" name="service" id="service">
                                    <option value="">{{ _lang('Select Service') }}</option>
                                </select>
                            </div>
                        </div>

                        <!--Order table -->
                        @php $currency = currency(); @endphp

                        @php $taxes = App\Tax::all(); @endphp
						
						<div class="col-md-12">
							<div class="table-responsive">
								<table id="order-table" class="table table-bordered">
									<thead>
										<tr>
                                            <th>{{ _lang('Línea') }}</th>
                                            <th>{{ _lang('Código') }}</th>
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
										@foreach($invoice->invoice_items as $key=>$item)
                                            @php $stock = $item->item->product_stock->where('company_id', company_id())->first()->quantity??0; @endphp
											<tr id="product-{{ $item->item_id }}">
												<td style="width: 95px"><input type="number" name="line[]" class="form-control input-line" value="{{ $item->line }}"></td>
                                                <td class="input-product-code" title="Producto original: {{$item->item->product->original}}">{{ $item->item->product->product_code }}</td>
												<td class="description">
                                                    <textarea cols="1" rows="2" name="product_description[]" class="form-control input-description">{{ $item->description }}</textarea>
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
													<button type="button" class="btn btn-danger btn-sm remove-product m-1" title="Remover ítem"><i class='ti-trash'></i></button>
                                                    <button type="button" class="btn btn-info btn-sm boton-descargo d-none" onclick="modalInfoDescargo({{$item->item_id}})" title="Información de anexo de descargo"><i class='ti-list'></i></button>
                                                    @include('backend.accounting.invoice.modal.info_descargo')
												</td>
												<input type="hidden" name="product_id[]" value="{{ $item->item_id }}">
												<input type="hidden" name="product_tax[]" class="input-product-tax" value="{{ $item->tax_amount }}">
                                                <input type="hidden" name="product_stock[]" class="input-product-stock" value="{{$stock}}">
                                                <input type="hidden" name="product_price[]" class="input-product-price" value="{{$item->item->product->product_price}}">
											</tr>
										@endforeach
									</tbody>
                                    <tfoot class="tfoot active">
                                        <tr>
                                            <th>{{ _lang('Sumas') }}</th>
                                            <th></th>
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
									{{-- <tfoot class="tfoot active">
										<tr>
											<th>{{ _lang('Total') }}</th>
											<th></th>
											<th></th>
											<th class="text-center" id="total-qty">0</th>
											<th></th>
                                            <th class="text-right d-none" id="total-discount">0.00</th>											
											<th class="text-right" id="total-tax">0.00</th>
											<th class="text-right" id="total">0.00</th>
											<th class="text-center"></th>
											<input type="hidden" name="product_total" id="product_total" value="0">
											<input type="hidden" name="tax_total" id="tax_total" value="0">
										</tr>
									</tfoot> --}}
								</table>
                                <table class="table table-bordered">
                                    <tfoot class="tfoot active">
                                        <tr id="tr-impuesto">
                                            <th>{{ _lang('Impuesto') }}</th>
                                            <th class="text-right" id="total-tax">0.00</th>
                                            <input type="hidden" name="tax_total" id="tax_total" value="0">
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
                                        <tr>
                                            <th>{{ _lang('Total a pagar') }}</th>
                                            <th class="text-right" id="grand-total">0.00</th>
                                            <input type="hidden" name="grand_total" id="grand_total" value="0">
                                        </tr>
                                    </tfoot>
                                </table>
							</div>
						</div>

                        <!--End Order table -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Note') }}</label>
                                <textarea class="form-control" rows="4" name="note">{{ $invoice->note }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Update Invoice') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<select class="form-control d-none" id="tax-selector">
    @foreach($taxes as $tax)
    <option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">
        {{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
    @endforeach
</select>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/invoice.js') }}"></script>
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
    percepcion_iva = parseFloat(@json(get_option('percepcion_iva')));
    retencion_iva = parseFloat(@json(get_option('retencion_iva')));
    techo_percepcion_iva = parseFloat(@json(get_option('techo_percepcion_iva')));
    techo_retencion_iva = parseFloat(@json(get_option('techo_retencion_iva')));
    editing = true;
</script>
@endsection
