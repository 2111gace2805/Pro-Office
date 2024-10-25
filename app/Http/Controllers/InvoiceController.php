<?php

namespace App\Http\Controllers;

// use PDF;
use App\Kit;
use App\Tax;
use App\Cash;
use App\Item;
use App\User;
use DateTime;
use App\Stock;
use Validator;
use DataTables;
use ZipArchive;
use App\Company;
use App\Contact;
use App\Invoice;
use App\BlockDte;
use App\Municipio;
use App\OrderNote;
use App\Quotation;
use Carbon\Carbon;
use App\InvoiceItem;
use App\Transaction;
use App\PasarelaToken;
use App\TipoDocumento;
use App\InvoiceItemTax;
use App\Mail\GeneralMail;
use App\Mail\MailMailable;
use App\InvoiceContingencia;
use App\Utilities\Overrider;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\UsuarioSinDUIException;
use App\GeneralDiscount;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.accounting.invoice.list');
    }

    public function get_table_data(Request $request)
    {

        $currency = currency();

        $invoices = Invoice::with("client")
            ->select('invoices.*', 'tipo_documento.tipodoc_nombre')
            ->leftJoin('tipo_documento', 'invoices.tipodoc_id', '=', 'tipo_documento.tipodoc_id')
            ->where('invoices.company_id', company_id())
            ->where(function ($query) {
                $query->where('invoices.status_mh', '=', 1)
                    ->orWhere('invoices.contingencia', '=', 1)
                    ->orWhere('invoices.postpone_invoice', '=', 1);
            })
            ->orderBy("invoices.id", "desc");

        return Datatables::eloquent($invoices)
            ->filter(function ($query) use ($request) {
                if ($request->has('invoice_number')) {
                    $query->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
                }

                if ($request->has('tipodoc_id')) {
                    $query->where('invoices.tipodoc_id', $request->get('tipodoc_id'));
                }

                if ($request->has('client_id')) {
                    $query->where('client_id', $request->get('client_id'));
                }

                if ($request->has('status')) {
                    $query->whereIn('status', json_decode($request->get('status')));
                }

                if ($request->has('date_range')) {
                    $date_range = explode(" - ", $request->get('date_range'));
                    $query->whereBetween('invoice_date', [$date_range[0], $date_range[1]]);
                }
            })
            ->editColumn('grand_total', function ($invoice) use ($currency) {
                return "<span class='float-right'>" . decimalPlace($invoice->grand_total, $currency) . "</span>";
            })
            ->editColumn('status', function ($invoice) {
                return invoice_status($invoice->status);
            })
            ->addColumn('action', function ($invoice) {
                $actionHtml = '<div class="dropdown text-center">'
                    . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                    . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                    . '<div class="dropdown-menu">'
                    . '<a class="dropdown-item" href="' . action('InvoiceController@show', $invoice->id) . '" data-title="' . _lang('View Invoice') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('View') . '</a>'
                    . '<button class="dropdown-item" onclick="reenviarCorreo(' . $invoice->id . ');" data-title="' . _lang('Reenviar DTE') . '" data-fullscreen="true"><i class="ti-email"></i> ' . _lang('Reenviar DTE') . '</button>';

                if ($invoice->tipodoc_id == '03' && $invoice->status != 'Canceled' && $invoice->contingencia == 0) {
                    $actionHtml .= '<a class="dropdown-item" href="' . action('InvoiceController@create', ['id_ccf' => $invoice->id, 'type' => '05']) . '" data-title="' . _lang('Nota Crédito') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Nota Crédito') . '</a>';
                    $actionHtml .= '<a class="dropdown-item" href="' . action('InvoiceController@create', ['id_ccf' => $invoice->id, 'type' => '06']) . '" data-title="' . _lang('Nota Débito') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Nota Débito') . '</a>';
                }
                if ($invoice->tipodoc_id == '05') {
                    $actionHtml .= '<a class="dropdown-item" href="' . action('InvoiceController@show', $invoice->id_ccf_rel) . '" data-title="' . _lang('View Invoice') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('CCF relacionado') . '</a>';
                }
                if ($invoice->tipodoc_id == '03' && $invoice->id_nr_rel > 0 ) {
                    $actionHtml .= '<a class="dropdown-item" href="' . action('InvoiceController@show', $invoice->id_nr_rel) . '" data-title="' . _lang('Ver Nota Remisión') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('Ver Nota Remisión') . '</a>';
                }
                if ($invoice->tipodoc_id == '04' && $invoice->status != 'Canceled' && $invoice->contingencia == 0 ) {
                    $actionHtml .= '<a class="dropdown-item" href="' . action('InvoiceController@create', ['id_nr' => $invoice->id, 'type' => '03' ]) . '" data-title="' . _lang('Generar CCF') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Generar CCF') . '</a>';
                    $actionHtml .= '<a class="dropdown-item" href="' . action('InvoiceController@create', ['id_nr' => $invoice->id, 'type' => '01' ]) . '" data-title="' . _lang('Generar Factura') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Generar Factura') . '</a>';
                }

                if ($invoice->sello_recepcion == ''  && $invoice->postpone_invoice == 1 ) {
                    $actionHtml .= '<button class="dropdown-item" onclick="re_enviarDTE(' . $invoice->id . ');" data-title="' . _lang('Obtener Sello') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Obtener Sello') . '</button>';
                }

                if ($invoice->status != 'Canceled') {
                    $actionHtml .= '<a href="' . route('invoices.create_payment', $invoice->id) . '" data-title="' . _lang('Make Payment') . '" class="dropdown-item ajax-modal"><i class="ti-credit-card"></i> ' . _lang('Make Payment') . '</a>';
                }


                $client_name = $invoice->client->contact_name;

                $actionHtml .= '<a href="' . route('invoices.view_payment', $invoice->id) . '" data-title="' . _lang('View Payment') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="ti-credit-card"></i> ' . _lang('View Payment') . '</a>'
                    // . '<form action="' . action('InvoiceController@destroy', $invoice->id) . '" method="post">'
                    . csrf_field()
                    // . '<input name="_method" type="hidden" value="DELETE">'
                    // . ($invoice->status != 'Canceled' && $invoice->contingencia == 0 ? '<button class="button-link" onclick="modalAnulacion(' . $invoice->id . ', \'' . $client_name . '\', \'' . $invoice->tdocrec_id . '\', \'' . $invoice->num_documento . '\');" data-title="¿Anular factura?" data-text="Los productos serán reintegrados al stock." data-confirmtext="Sí, anular" type="submit"><i class="ti-trash"></i> ' . _lang('Anular') . '</button>' : '')
                    . ($invoice->status != 'Canceled' ? '<button class="button-link" onclick="modalAnulacion(' . $invoice->id . ', \'' . $client_name . '\', \'' . $invoice->tdocrec_id . '\', \'' . $invoice->num_documento . '\');" data-title="¿Anular factura?" data-text="Los productos serán reintegrados al stock." data-confirmtext="Sí, anular" type="submit"><i class="ti-trash"></i> ' . _lang('Anular') . '</button>' : '')
                    . ($invoice->status != 'Canceled' && $invoice->contingencia == 1 && $invoice->sello_recepcion == '' ? '<button class="button-link" onclick="modalContingencia(' . $invoice->id . ');" ><i class="ti-reload"></i> ' . _lang('Evento contingencia') . '</button>' : '')
                    . ($invoice->status != 'Canceled' ? '<a class="dropdown-item" href="' . action('SalesReturnController@create', ['id' => $invoice->id]) . '"><i class="ti-pencil-alt"></i> ' . _lang('Devolución garantía') . '</a>' : '')
                    // . '</form>'
                    . '</div>'
                    . '</div>';

                return $actionHtml;
            })

            ->setRowId(function ($invoice) {
                return "row_" . $invoice->id;
            })
            ->rawColumns(['grand_total', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $invoice = null;
        $invoiceCCF = null;
        $invoiceNR = null;
        $type = null;
        $nota_pedido = null;
        $user = Auth::user();
        if ($request->quotation_id != null) {
            $quotation = Quotation::find($request->quotation_id);
            $invoiceItems = [];
            foreach ($quotation->quotation_items as $key => $value) {
                if (str_contains($value->delivery_time, _lang('Empty stock')) || str_contains($value->delivery_time, _lang('Not found'))) {
                    continue;
                }
                $quantity = 0;
                if (str_contains($value->delivery_time, _lang('Immediate'))) {
                    $quantity = $value->quantity;
                } else {
                    $quantity = trim(substr(
                        $value->delivery_time,
                        0,
                        strpos($value->delivery_time, _lang('Minor stock'))
                    ));
                }
                $invoiceItem = new InvoiceItem([
                    // 'id'=>null, 
                    'invoice_id' => null,
                    'item_id' => $value->item_id,
                    'description' => $value->description,
                    'quantity' => $quantity,
                    'unit_cost' => $value->unit_cost,
                    'discount' => $value->discount,
                    'tax_id' => $value->tax_id,
                    'tax_amount' => $value->tax_amount,
                    'sub_total' => $quantity * $value->unit_cost,
                    'company_id' => $value->company_id,
                    'company_id' => $value->company_id,
                ]);
                $invoiceItem->taxes = $value->taxes;

                array_push($invoiceItems, $invoiceItem);
            }
            $invoice = new Invoice(['client_id' => $quotation->client_id, 'tipodoc_id' => $request->tipodoc_id, 'refisc_id' => get_option('refisc_id_default')]);
            $invoice->invoice_items = $invoiceItems;
            $invoice->client = Contact::with('actividad_economica')->with('departamento')
                ->with('municipio')->with('pais')->find($quotation->client_id);
        }

        if ($request->id_ccf != null) {
            $invoiceCCF = new Invoice();
            $dte = Invoice::with('invoice_items', 'client')->find($request->id_ccf);
            $invoiceCCF->dte_asociado = $dte;

            $notasEmitidas = Invoice::where('id_ccf_rel', $request->id_ccf)
                ->where('status_mh', '=', 1)
                // ->selectRaw('SUM(subtotal) as total_subtotal, SUM(tax_total) as total_tax')
                // ->first();
                ->selectRaw('SUM(CASE WHEN tipodoc_id = "05" THEN subtotal ELSE 0 END) as total_subtotal_nc,
                SUM(CASE WHEN tipodoc_id = "05" THEN tax_total ELSE 0 END) as total_tax_nc,
                SUM(CASE WHEN tipodoc_id = "05" THEN general_discount ELSE 0 END) as total_desc_nc,
                SUM(CASE WHEN tipodoc_id = "05" THEN iva_retenido ELSE 0 END) as iva_retenido_nc,
                SUM(CASE WHEN tipodoc_id = "06" THEN subtotal ELSE 0 END) as total_subtotal_nd,
                SUM(CASE WHEN tipodoc_id = "06" THEN general_discount ELSE 0 END) as total_desc_nd,
                SUM(CASE WHEN tipodoc_id = "06" THEN tax_total ELSE 0 END) as total_tax_nd,
                SUM(CASE WHEN tipodoc_id = "06" THEN iva_retenido ELSE 0 END) as iva_retenido_nd')
                ->first();

                
            $invoiceCCF->total_notas_nc  = $notasEmitidas->total_subtotal_nc ?? 0;
            $invoiceCCF->total_taxs_nc   = $notasEmitidas->total_tax_nc ?? 0;
            $invoiceCCF->total_notas_nd  = $notasEmitidas->total_subtotal_nd ?? 0;
            $invoiceCCF->total_taxs_nd   = $notasEmitidas->total_tax_nd ?? 0;
            $invoiceCCF->total_desc_nc   = $notasEmitidas->total_desc_nc ?? 0;
            $invoiceCCF->total_desc_nd   = $notasEmitidas->total_desc_nd ?? 0;
            $invoiceCCF->iva_retenido_nc = $notasEmitidas->iva_retenido_nc ?? 0;
            $invoiceCCF->iva_retenido_nd = $notasEmitidas->iva_retenido_nd ?? 0;
            $invoiceCCF->type            = $request->type;

        }

        if ($request->id_nr != null) {
            $invoiceNR = new Invoice();
            $dte = Invoice::with('invoice_items', 'client')->find($request->id_nr);
            $invoiceNR->dte_asociado = $dte;
            $type = $request->type;
        }

        if( $request->id_nota_p != null ){
            
            $nota_pedido = new Invoice();
            $nota = OrderNote::with('client')->find($request->id_nota_p);
            $nota_pedido->datos = $nota;
        }

        $generalDiscounts = GeneralDiscount::where('status', 'Active')->get();

        // if (!$request->ajax()) {
            return view('backend.accounting.invoice.create', compact(['invoice', 'invoiceCCF', 'invoiceNR', 'type', 'nota_pedido', 'generalDiscounts']));
        // } else {
        //     return view('backend.accounting.invoice.modal.create', compact(['invoice', 'invoiceCCF', 'invoiceNR', 'type', 'nota_pedido']));
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'invoice_number' => 'required|max:191',
            // 'client_id'      => 'required',
            'invoice_date'   => 'required',
            // 'due_date'       => 'required',
            'product_id'     => 'required',
            'modfact_id'     => 'required',
            'name_invoice'   => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('invoices.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        try {
            
            $sucursal_id = Session::get('company.id');
            $company = Company::find($sucursal_id);
            DB::beginTransaction();
    
            $invoice                 = new Invoice();
            // $invoice->invoice_number = get_invoice_number($request->tipodoc_id);
            // $invoice->invoice_number = $request->invoice_number;
            $invoice->client_id      = $request->input('client_id');
            $invoice->name_invoice   = $request->input('name_invoice');
            $invoice->invoice_date   = Carbon::createFromFormat('d/m/Y', $request->input('invoice_date'))->format('Y-m-d');
            $invoice->invoice_time   = $request->input('invoice_time');
            // $invoice->due_date       = $request->input('due_date');
            // si es CCF
            // if ($request->input('tipodoc_id') == '03') {
            //     $invoice->grand_total    = $request->product_total + $request->tax_total;
            // }else{
            $invoice->subtotal    = $request->product_total;
            $invoice->subtotal_2    = $request->_subtotal_2;
            $invoice->general_discount    = $request->discount_general??0; // valor en dolares del descuento de la venta
            $invoice->general_discount_type    = $request->general_discount_type;
            $invoice->general_discount_id    = $request->general_discount_id != 'other' ? $request->general_discount_id : null;
            $invoice->general_discount_value    = $request->general_discount_value??0; // valor en porcentaje o dolares (caso fijo)
            $invoice->iva_retenido    = $request->iva_retenido;
            $invoice->iva_percibido    = $request->iva_percibido;
            $invoice->isr_retenido    = $request->isr_retenido;
            $invoice->retencion_renta = $request->retencion_renta;
            $invoice->grand_total    = $request->grand_total;
            // }
            $invoice->tax_total      = $request->input('tax_total');
            $invoice->paid           = 0;
            $invoice->status         = $request->input('status');
            $invoice->note           = $request->input('note');
            $invoice->tipodoc_id     = $request->input('tipodoc_id');
            $invoice->tpers_id       =  $request->input('tpers_id');
            $invoice->num_documento  = $request->input('num_documento');
    
            $tipoDocumento = $request->input('tipodoc_id');
            $invoice->numero_control = generateNumeroControl($tipoDocumento);
            $invoice->codigo_generacion = strtoupper(generateUUID());
    
            if( $tipoDocumento == '05' || $tipoDocumento == '06' ) {
                if ($request->has('id_ccf_rel')) {
                    $invoice->id_ccf_rel  = $request->input('id_ccf_rel');
                }
            }
    
            if( $tipoDocumento == '01' || $tipoDocumento == '03'  ) {
                if ($request->has('id_nr_rel')) {
                    $invoice->id_nr_rel  = $request->input('id_nr_rel');
                }
            }
    
            if( $tipoDocumento == '11' ) {
                if ($request->has('id_incoterms') && $request->input('id_incoterms') !== '') {
                    $invoice->id_incoterms  = $request->input('id_incoterms');
                }
            }
    
            $invoice->modfact_id = $request->input('modfact_id');
            $invoice->tipotrans_id = $request->input('tipotrans_id');
            $invoice->actie_id = $request->input('actie_id');
            $invoice->desc_actividad = $request->input('desc_actividad');
            $invoice->complemento = $request->input('complemento');
            $invoice->telefono = $request->input('telefono');
            $invoice->correo = $request->input('correo');
            $invoice->correo_alterno = $request->input('correo_alterno');
            $invoice->plazo_id  = $request->input('plazo_id');
            $invoice->periodo  = $request->input('periodo');
            $invoice->conop_id = $request->input('conop_id');
            $invoice->tdocrec_id  = $request->input('tdocrec_id');
            $invoice->postpone_invoice  = $request->input('postpone_invoice');
    
            // cliente exento, nosujeto, gran contribuyente
            $infoCliente = Contact::find($invoice->client_id);
            if( $request->has('chkVentaExenta') ){
                $invoice->exento_iva  = 'si';
            }
            else{
                $invoice->exento_iva  = $infoCliente->exento_iva ?? 'no';
            }
            $invoice->nosujeto_iva  = $infoCliente->nosujeto_iva ?? 'no';
            $invoice->gran_contribuyente  = $infoCliente->gran_contribuyente ?? 'no';
    
            $invoice->forp_id  = $request->forp_id;
            if ($request->input('tipodoc_id') == '01') { // 01 = FE
                $invoice->ticket_number = get_option('ticket_starting');
            }

            $sellers_code = $request->input('seller_code', []);

            $user_id        = null;
            $second_user_id = null;
        
            if( count( $sellers_code ) > 0 ) {
                $user_id = $sellers_code[0];
            }
            if (count($sellers_code) > 1) {
                $second_user_id = $sellers_code[1];
            }

            $invoice->user_id           = $user_id;
            $invoice->second_user_id    = $second_user_id;
            $invoice->save();
    
            $user = Auth::user();
            // Log::createLog($user, 'invoices', 'create'); 
            $taxes = Tax::all();
    
            $ivaRetenido = 0;
    
            //Save Invoice Item
            for ($i = 0; $i < count($request->product_id); $i++) {


                if( $request->kit[$i] == 0 ){
                    $stock = Stock::whereRaw("product_id = {$request->product_id[$i]} and company_id = " . company_id())->first();
                    $item = Item::findOrFail($request->product_id[$i]);
                    if ($item->item_type === 'product') {
                        if ($stock != null && $stock->quantity < $request->quantity[$i]) {
                            if ($request->ajax()) {
                                return response()->json(['result' => 'error', 'message' => 'Stock máximo alcanzado']);
                            } else {
                                return redirect()->route('invoices.create')
                                    ->withErrors(['Sorry, Error Occured !', 'Stock máximo alcanzado.'])
                                    ->withInput();
                            }
                        }
                    }
                }
                else{

                    $kit = Kit::find( $request->product_id[$i] );

                    $products = json_decode( json_encode($kit->products) );

                    foreach( $products as $product ){

                        $cantidad_item_kit  = $product->quantity;
                        $cantidad_kits      = $request->quantity[$i];
    
                        $total_unidades     = intval( $cantidad_item_kit ) * intval( $cantidad_kits );

                        $stock = Stock::whereRaw("product_id = {$product->product_id} and company_id = " . company_id())->first();
                        $item = Item::findOrFail($product->product_id);

                        if( $item->item_type === 'product' ){

                            if( $stock != null && $stock->quantity < $total_unidades ){
                                if ($request->ajax()) {
                                    return response()->json(['result' => 'error', 'message' => 'Stock máximo alcanzado']);
                                }
                                else {
                                    return redirect()->route('invoices.create')
                                        ->withErrors(['Sorry, Error Occured !', 'Stock máximo alcanzado.'])
                                        ->withInput();
                                }
                            }
                        }
                    }
                }

                $invoiceItem              = new InvoiceItem();
                $invoiceItem->invoice_id  = $invoice->id;
                $invoiceItem->item_id     = ( $request->kit[$i] == 0 ) ? $request->product_id[$i] : 0;
                $invoiceItem->kit_id      = ( $request->kit[$i] == 1 ) ? $request->product_id[$i] : 0;
                $invoiceItem->description = $request->product_description[$i];
                $invoiceItem->quantity    = $request->quantity[$i];
                $invoiceItem->unit_cost   = $request->unit_cost[$i];
                $invoiceItem->discount    = $request->discount[$i];
                $invoiceItem->tax_amount  = $request->product_tax[$i];
                $invoiceItem->sub_total   = $request->sub_total[$i];
                $invoiceItem->line   = $request->line[$i];
                $invoiceItem->product_price   = $request->product_price[$i];
    
                if( $request->input('tipodoc_id') == '05' || $request->input('tipodoc_id') == '06' ){
    
                    $dte = Invoice::find($request->dtes_relacionados[$i]);
                    $invoiceItem->cod_dte_rel   = $dte->codigo_generacion;
                    $invoiceItem->type_dte_rel  = $dte->tipodoc_id;
                    $invoiceItem->date_dte_rel  = Carbon::createFromFormat('d/m/Y', $dte->invoice_date)->format('Y-m-d');
                }
    
                if( $request->input('tipodoc_id') == '01' || $request->input('tipodoc_id') == '03' ){
                    if ($request->has('id_nr_rel')) {
                        $dte = Invoice::find($request->input('id_nr_rel'));
                        $invoiceItem->cod_dte_rel   = $dte->codigo_generacion;
                        $invoiceItem->type_dte_rel  = $dte->tipodoc_id;
                        $invoiceItem->date_dte_rel  = Carbon::createFromFormat('d/m/Y', $dte->invoice_date)->format('Y-m-d');
                    }
                }
    
                $invoiceItem->save();
    
                //Store Invoice Taxes
                if (isset($request->tax[$invoiceItem->item_id])) {
                    foreach ($request->tax[$invoiceItem->item_id] as $taxId) {
                        $tax = $taxes->firstWhere('id', $taxId);
    
                        $invoiceItemTax                  = new InvoiceItemTax();
                        $invoiceItemTax->invoice_id      = $invoiceItem->invoice_id;
                        $invoiceItemTax->invoice_item_id = $invoiceItem->id;
                        $invoiceItemTax->tax_id          = $tax->id;
                        $tax_type                        = $tax->type == 'percent' ? '%' : '';
                        $invoiceItemTax->name            = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                        // si es CCF
                        if ($request->input('tipodoc_id') == '03') {
                            $invoiceItemTax->amount          = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                        } else {
                            $invoiceItemTax->amount = $tax->type == 'percent' ? (($invoiceItem->quantity * $request->product_price[$i] - $invoiceItem->discount) / 100) * $tax->rate : $tax->rate;
                        }
                        $invoiceItemTax->save();
    
                        // if ($tax->trib_id == '20') { // 20 es tributo IVA
                        //     $ivaRetenido += round(($invoiceItem->quantity*$request->product_price[$i]-$invoiceItem->discount)*(floatval(get_option('retencion_iva'))/100), 2);
                        // }
                    }
                }

                if (isset($request->tax[$invoiceItem->kit_id])) {
                    foreach ($request->tax[$invoiceItem->kit_id] as $taxId) {
                        $tax = $taxes->firstWhere('id', $taxId);
    
                        $invoiceItemTax                  = new InvoiceItemTax();
                        $invoiceItemTax->invoice_id      = $invoiceItem->invoice_id;
                        $invoiceItemTax->invoice_item_id = $invoiceItem->id;
                        $invoiceItemTax->tax_id          = $tax->id;
                        $tax_type                        = $tax->type == 'percent' ? '%' : '';
                        $invoiceItemTax->name            = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                        // si es CCF
                        if ($request->input('tipodoc_id') == '03') {
                            $invoiceItemTax->amount          = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                        } else {
                            $invoiceItemTax->amount = $tax->type == 'percent' ? (($invoiceItem->quantity * $request->product_price[$i] - $invoiceItem->discount) / 100) * $tax->rate : $tax->rate;
                        }
                        $invoiceItemTax->save();
    
                        // if ($tax->trib_id == '20') { // 20 es tributo IVA
                        //     $ivaRetenido += round(($invoiceItem->quantity*$request->product_price[$i]-$invoiceItem->discount)*(floatval(get_option('retencion_iva'))/100), 2);
                        // }
                    }
                }
    
    
    
                //Update Stock if Order Status is received
                if ($request->input('status') != 'Canceled') {
    
                    // $stock = Stock::where("item_id", $invoiceItem->item_id)
                    // ->join('products as p', 'p.id', 'current_stocks.product_id')
                    // ->where('company_id', company_id())->select('current_stocks.*')->first();
                    if( $stock ){
                        if( $request->input('tipodoc_id') != '05' && $request->input('tipodoc_id') != '06' && !$request->has('id_nr_rel') && !$request->has('id_nota_p') ){

                            if( $invoiceItem->kit_id > 0 ){

                                $kit = Kit::find( $invoiceItem->kit_id );

                                $products = json_decode( json_encode($kit->products) );
            
                                foreach( $products as $product ){
            
                                    $cantidad_item_kit  = $product->quantity;
                                    $cantidad_kits      = $invoiceItem->quantity;
                
                                    $total_unidades     = intval( $cantidad_item_kit ) * intval( $cantidad_kits );

                                    $stock = Stock::whereRaw("product_id = {$product->product_id} and company_id = " . company_id())->first();

                                    $stock->quantity = $stock->quantity - $total_unidades;
                                    $stock->save();
                                }

                            }
                            else{
                                $stock->quantity = $stock->quantity - $invoiceItem->quantity;
                                $stock->save();
                            }
                        }
                    }
                }
            }
    
            //Increment Invoice Starting number
            increment_invoice_number($request->input('tipodoc_id'), intval($request->invoice_number) + 1);
    
            // if ($infoCliente->gran_contribuyente == 'si' &&  get_option('techo_retencion_iva')) {
            //     $invoice->iva_retenido  = $ivaRetenido;
            //     $invoice->grand_total = $invoice->grand_total-round($ivaRetenido, 2);
            //     $invoice->save();
            // }
    
            if ($request->forp_id == '01') { // 01 efectivo
                $cash = get_cash();
                $cash->cash_value += $request->grand_total;
            }
    
            $msg = '';
            if ($invoice->tipotrans_id == '2') {
    
                $msg = 'en modo contingencia';
                $invoice->contingencia  = 1;
                $invoice->status_mh     = 2; //SE AGREGA ESTADO 2 PARA COLA DE TRABAJO
                $invoice->modfact_id    = 2;
                $invoice->save();
    
                BlockDte::where('type_dte', '=', $tipoDocumento)->increment('correlativo');

                $correlativo_mh             = $invoice->numero_control;
                $invoice->invoice_number    = substr($correlativo_mh, -6);
                $invoice->save();


                if( $request->has('id_nota_p') ){

                    $id_nota = $request->id_nota_p;
    
                    $orderNote = OrderNote::find($id_nota);
                    $orderNote->invoiced = $invoice->id;
                    $orderNote->save();
                }
    
                log::info("Invoice con ID: " . $invoice->id . " creado en modo contingencia");
            }
            else if( $invoice->postpone_invoice == 1 ){

                $msg = 'en modo Posponer, se enviará al siguiente día a las 00:00 horas para obtener sello de recepción';
                $invoice->save();
    
                BlockDte::where('type_dte', '=', $tipoDocumento)->increment('correlativo');

                $correlativo_mh             = $invoice->numero_control;
                $invoice->invoice_number    = substr($correlativo_mh, -6);
                $invoice->save();
    
                log::info("Invoice con ID: " . $invoice->id . " creado en modo Posponer, se enviará al siguiente día a las 00:00 horas");
            }
            else {
                $response = $this->sendInvoiceToHacienda($invoice->id);
                $response_mh = json_decode(json_encode($response));
    
                if (!property_exists($response_mh, 'estado')) {
                    log::info('Error al procesar DTE en store (propiedad estado no existe en respuesta apihacienda): ' . json_encode($response));
                    DB::rollBack();
                    return response()->json(['result' => 'error', 'message' => 'Error en respuesta de pasarela']);
    
                    // log::info('Error en respuesta de pasarela, verificar!');
    
                    // $invoice->status            = 'Canceled';
                    // $invoice->note              = 'Motivo de anulación: Error en respuesta de pasarela';
                    // $invoice->numero_control = null;
                    // $invoice->save();
    
                    // log::info('Se inicia proceso de devolución de items');
    
                    // $invoiceItems = InvoiceItem::where("invoice_id", $invoice->id)->get();
                    // foreach ($invoiceItems as $p_item) {

                    //     if( $p_item->kit_id > 0 ){
    
                    //         $kit = Kit::find( $p_item->kit_id );
    
                    //         $products = json_decode( json_encode($kit->products) );
        
                    //         foreach( $products as $product ){
        
                    //             $cantidad_item_kit  = $product->quantity;
                    //             $cantidad_kits      = $p_item->quantity;
            
                    //             $total_unidades     = intval( $cantidad_item_kit ) * intval( $cantidad_kits );

                    //             //log::info('Se hace devolución de item con ID: ' . $product->product_id);
    
                    //             update_stock($product->product_id, $total_unidades, '+');
                    //         }
    
                    //     }
                    //     else{
                    //         $invoiceItem = InvoiceItem::find($p_item->id);
                    //         //log::info('Se hace devolución de item con ID: ' . $p_item->id);
                    //         update_stock($p_item->item_id, $invoiceItem->quantity, '+');
                    //     }
                    // }
    
                    // log::info('Se finaliza proceso de devolución de items');
    
                    // if ($invoice->forp_id == '01') { // 01 efectivo
                    //     $cash = get_cash();
                    //     $cash->cash_value -= $invoice->grand_total;
                    // }
    
                    // if ($request->ajax()) {
                    //     return response()->json(['result' => 'error', 'message' => 'Error en respuesta de pasarela']);
                    // } else {
                    //     return redirect()->route('invoices.create')
                    //         ->withErrors(['Sorry, Error Occured !', 'Error en pasarela'])
                    //         ->withInput();
                    // }
                }
    
                $invoice->status_mh         = ($response_mh->estado === 'RECHAZADO') ? 0 : 1;
                $invoice->response_mh       = json_encode($response);
                $invoice->sello_recepcion   = ($response_mh->estado === 'PROCESADO') ? $response_mh->selloRecibido : null;
                $invoice->json_dte          = json_encode($response_mh->json);
                $invoice->save();
    
                if ($response_mh->estado === 'RECHAZADO') {
                    log::info('Error al procesar DTE en store (estado: rechazado): ' . json_encode($response));
                    DB::rollBack();
                    return response()->json(['result' => 'errorMH', 'action' => 'store', 'message' => _lang('Error al procesar DTE'), 'data' => $response]);
    
                    // log::info('Error al procesar DTE: ' . json_encode($response));
    
                    // $invoice->status    = 'Canceled';
                    // $invoice->note      = 'Motivo de anulación: ' . json_encode($response);
                    // $invoice->numero_control = null;
                    // $invoice->codigo_generacion = null;
                    // $invoice->save();
    
                    // log::info('Se inicia proceso de devolución de items');
    
                    // $invoiceItems = InvoiceItem::where("invoice_id", $invoice->id)->get();
                    // foreach ($invoiceItems as $p_item) {

                    //     if( $p_item->kit_id > 0 ){
    
                    //         $kit = Kit::find( $p_item->kit_id );
    
                    //         $products = json_decode( json_encode($kit->products) );
        
                    //         foreach( $products as $product ){
        
                    //             $cantidad_item_kit  = $product->quantity;
                    //             $cantidad_kits      = $p_item->quantity;
            
                    //             $total_unidades     = intval( $cantidad_item_kit ) * intval( $cantidad_kits );

                    //             //log::info('Se hace devolución de item con ID: ' . $product->product_id);
    
                    //             update_stock($product->product_id, $total_unidades, '+');
                    //         }
    
                    //     }
                    //     else{
                    //         $invoiceItem = InvoiceItem::find($p_item->id);
                    //         //log::info('Se hace devolución de item con ID: ' . $p_item->id);
                    //         update_stock($p_item->item_id, $invoiceItem->quantity, '+');
                    //     }
                    // }
    
                    // log::info('Se finaliza proceso de devolución de items');
    
                    // if ($invoice->forp_id == '01') { // 01 efectivo
                    //     $cash = get_cash();
                    //     $cash->cash_value -= $invoice->grand_total;
                    // }
    
                    // if ($request->ajax()) {
                    //     return response()->json(['result' => 'errorMH', 'action' => 'store', 'message' => _lang('Error al procesar DTE'), 'data' => $response]);
                    // } else {
                    //     return redirect()->route('invoices.create', $invoice->id)->with('error', 'Error al procesar DTE');
                    // }
                } else if ($response_mh->estado === 'PROCESADO') {

                    if( $request->has('id_nota_p') ){

                        $id_nota = $request->id_nota_p;
        
                        $orderNote = OrderNote::find($id_nota);
                        $orderNote->invoiced = $invoice->id;
                        $orderNote->save();
                    }
    
                    BlockDte::where('type_dte', '=', $tipoDocumento)->increment('correlativo');

                    $correlativo_mh             = $invoice->numero_control;
                    $invoice->invoice_number    = substr($correlativo_mh, -6);
                    $invoice->save();
    
                    if( $invoice->correo != '' ){
                        $this->sendEmailFactura($invoice->id);
                    }
    
                }
    
                //log::info("Lo que envio de store hacia sendInviceToHacienda" . json_encode($response));
            }

            DB::commit();
    
            if (!$request->ajax()) {
                return redirect()->route('invoices.show', $invoice->id)->with('success', _lang('Factura Generada Exitosamente ' . $msg));
            } else {
                return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Factura Generada Exitosamente ' . $msg), 'data' => $invoice]);
            }
        }
        catch (\Throwable $th) {

            DB::rollBack();

            Log::error('Error al crear Factura: ' . $th);

            // Imprimir el mensaje de error específico de la base de datos (si hay)
            if ($th instanceof \Illuminate\Database\QueryException && $th->errorInfo) {
                Log::error('Error de base de datos: ' . print_r($th->errorInfo, true));
            }

            return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'Error al crear Factura, intente nuevamente']);

        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $invoice = Invoice::with('client')->find($id);

        if (!$request->ajax()) {
            return view('backend.accounting.invoice.edit', compact('invoice', 'id'));
        } else {
            return view('backend.accounting.invoice.modal.edit', compact('invoice', 'id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|max:191',
            'client_id'      => 'required',
            'invoice_date'   => 'required',
            // 'due_date'       => 'required',
            'product_id'     => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('invoices.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        $invoice                 = Invoice::find($id);
        $invoice->invoice_number = $request->input('invoice_number');
        $invoice->client_id      = $request->input('client_id');
        $invoice->invoice_date   = $request->input('invoice_date');
        // $invoice->due_date       = $request->input('due_date');
        // si es CCF
        // if ($request->input('tipodoc_id') == '03') {
        //     $invoice->grand_total    = $request->product_total + $request->tax_total;
        // }else{
        $invoice->subtotal    = $request->product_total;
        $invoice->iva_retenido    = $request->iva_retenido;
        $invoice->iva_percibido    = $request->iva_percibido;
        $invoice->isr_retenido    = $request->isr_retenido;
        $invoice->grand_total    = $request->grand_total;
        // }
        $invoice->tax_total      = $request->input('tax_total');
        $invoice->status         = $request->input('status');
        $invoice->note           = $request->input('note');
        $invoice->tipodoc_id     = $request->input('tipodoc_id');
        $invoice->tpers_id       =  $request->input('tpers_id');
        $invoice->num_documento  = $request->input('num_documento');
        $invoice->plazo_id  = $request->input('plazo_id');
        $invoice->periodo  = $request->input('periodo');

        // cliente exento, nosujeto, gran contribuyente
        $infoCliente = Contact::find($invoice->client_id);
        $invoice->exento_iva  = $infoCliente->exento_iva;
        $invoice->nosujeto_iva  = $infoCliente->nosujeto_iva;
        $invoice->gran_contribuyente  = $infoCliente->gran_contribuyente;
        $invoice->save();

        $taxes = Tax::all();

        //Update Invoice item
        $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
        foreach ($invoiceItems as $p_item) {
            $invoiceItem = InvoiceItem::find($p_item->id);
            update_stock($p_item->item_id, $invoiceItem->quantity, '+');
            $invoiceItem->delete();
        }

        $invoiceItemTax = InvoiceItemTax::where("invoice_id", $id);
        $invoiceItemTax->delete();

        $ivaRetenido = 0;

        for ($i = 0; $i < count($request->product_id); $i++) {
            $stock = Stock::whereRaw("product_id = {$request->product_id[$i]} and company_id = " . company_id())->first();
            // if ($stock != null && $stock->quantity < $request->quantity[$i]) {
            //     if ($request->ajax()) {
            //         return response()->json(['result' => 'error', 'message' => 'Stock máximo alcanzado']);
            //     } else {
            //         return redirect()->route('invoices.create')
            //              ->withErrors(['Sorry, Error Occured !', 'Stock máximo alcanzado.'])
            //             ->withInput();
            //     }
            // }
            $invoiceItem              = new InvoiceItem();
            $invoiceItem->invoice_id  = $invoice->id;
            $invoiceItem->item_id     = $request->product_id[$i];
            $invoiceItem->description = $request->product_description[$i];
            $invoiceItem->quantity    = $request->quantity[$i];
            $invoiceItem->unit_cost   = $request->unit_cost[$i];
            $invoiceItem->discount    = $request->discount[$i];
            $invoiceItem->tax_amount  = $request->product_tax[$i];
            $invoiceItem->sub_total   = $request->sub_total[$i];
            $invoiceItem->line        = $request->line[$i];
            $invoiceItem->product_price  = $request->product_price[$i];

            // $invoiceItem->no_declaracion = $request->no_declaracion[$i];
            // $invoiceItem->aduana_registro = $request->aduana_registro[$i];
            // $invoiceItem->fecha_registro = $request->fecha_registro[$i];
            // $invoiceItem->codigo_arancelario = $request->codigo_arancelario[$i];
            // $invoiceItem->observacion = $request->observacion[$i];

            $invoiceItem->save();

            //Store Invoice Taxes
            if (isset($request->tax[$invoiceItem->item_id])) {
                foreach ($request->tax[$invoiceItem->item_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $invoiceItemTax                  = new InvoiceItemTax();
                    $invoiceItemTax->invoice_id      = $invoiceItem->invoice_id;
                    $invoiceItemTax->invoice_item_id = $invoiceItem->id;
                    $invoiceItemTax->tax_id          = $tax->id;
                    $tax_type                        = $tax->type == 'percent' ? '%' : '';
                    $invoiceItemTax->name            = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;

                    // si es CCF
                    if ($request->input('tipodoc_id') == '03') {
                        $invoiceItemTax->amount          = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                    } else {
                        $invoiceItemTax->amount = $tax->type == 'percent' ? (($invoiceItem->quantity * $request->product_price[$i] - $invoiceItem->discount) / 100) * $tax->rate : $tax->rate;
                    }
                    $invoiceItemTax->save();

                    if ($tax->trib_id == '20') { // 20 es tributo IVA
                        $ivaRetenido += round(($invoiceItem->quantity * $request->product_price[$i] - $invoiceItem->discount) * (floatval(get_option('retencion_iva')) / 100), 2);
                    }
                }
            }

            update_stock($request->product_id[$i], $request->quantity[$i], '-');
        }

        // if ($infoCliente->gran_contribuyente == 'si') {
        //     $invoice->iva_retenido  = $ivaRetenido;
        //     $invoice->grand_total = $invoice->grand_total-round($ivaRetenido, 2);
        //     $invoice->save();
        // }

        DB::commit();

        if (!$request->ajax()) {
            return redirect()->route('invoices.show', $invoice->id)->with('success', _lang('Invoice updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Invoice updated sucessfully'), 'data' => $invoice]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        try {

            DB::beginTransaction();

            $invoice = Invoice::find($id);

            if ($invoice->contingencia == 1) {
                $response = [];
                $response_mh = (object)['estado'=>'PROCESADO'];
            }else{
                $response = $this->anularInvoiceMH($invoice->id, $request);
                $response_mh = json_decode(json_encode($response));
            }

            if ($response_mh->estado === 'RECHAZADO') {

                log::info('Error al anular DTE: ' . json_encode($response_mh->observaciones));

                if ($request->ajax()) {
                    return response()->json(['result' => 'errorMH', 'message' => _lang('Error al anular DTE'), 'data' => $response]);
                } else {
                    return redirect()->route('invoices.index')->with('error', 'Error al anular DTE');
                }
            } else {

                $invoice->status = 'Canceled';
                $invoice->note = $invoice->note . (($invoice->note != null && trim($invoice->note) != '') ? ' | Motivo de anulación' . $request->motivo_anulacion : 'Motivo de anulación: ' . $request->motivo_anulacion);
                // $invoice->note = 'Motivo de anulación: '.$request->motivo_anulacion;
                $invoice->save();

                $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
                foreach ($invoiceItems as $p_item) {

                    if( $p_item->type_dte_rel == '' ){
                        if( $p_item->kit_id > 0 ){
    
                            $kit = Kit::find( $p_item->kit_id );
    
                            $products = json_decode( json_encode($kit->products) );
        
                            foreach( $products as $product ){
        
                                $cantidad_item_kit  = $product->quantity;
                                $cantidad_kits      = $p_item->quantity;
            
                                $total_unidades     = intval( $cantidad_item_kit ) * intval( $cantidad_kits );
    
                                update_stock($product->product_id, $total_unidades, '+');
                            }
    
                        }
                        else{
                            $invoiceItem = InvoiceItem::find($p_item->id);
                            update_stock($p_item->item_id, $invoiceItem->quantity, '+');
                        }
                    }

                    // $invoiceItem->delete();
                }

                // $invoiceItemTax = InvoiceItemTax::where('invoice_id', $id);
                // $invoiceItemTax->delete();

                if ($invoice->forp_id == '01') { // 01 efectivo
                    $cash = get_cash();
                    $cash->cash_value -= $invoice->grand_total;
                }

                DB::commit();

                try {
                    $this->sendEmailFactura($invoice->id, true);
                } catch (\Throwable $th) {
                    //throw $th;
                }

                if ($request->ajax()) {
                    return response()->json(['result' => 'success', 'message' => _lang('Invoice deleted sucessfully'), 'data' => $response]);
                } else {
                    return redirect()->route('invoices.index')->with('success', _lang('Invoice deleted sucessfully'));
                }
            }
        } catch (UsuarioSinDUIException $e) {

            log::info($e->getMessage());

            if ($request->ajax()) {
                return response()->json(['result' => 'error_usuario', 'message' => $e->getMessage()]);
            } else {
                return redirect()->route('invoices.index')->with('error', $e->getMessage());
            }
        } catch (\Throwable $th) {
            log::info($th->getMessage());

            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $th->getMessage()]);
            } else {
                return redirect()->route('invoices.index')->with('error', $th->getMessage());
            }
        }
    }

    public function store_payment(Request $request, $id = '')
    {
        if ($request->isMethod('get')) {
            $invoice = Invoice::find($id);

            if ($request->ajax()) {
                return view('backend.accounting.invoice.modal.create_payment', compact('invoice', 'id'));
            }
        } else if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'invoice_id'        => 'required',
                'account_id'        => 'required',
                'chart_id'          => 'required',
                'amount'            => 'required|numeric',
                'payment_method_id' => 'required',
                'reference'         => 'nullable|max:50',
                'attachment'        => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                } else {
                    return back()->withErrors($validator)
                        ->withInput();
                }
            }

            $invoice = Invoice::find($request->invoice_id);
            if ($invoice->status == 'Paid') {
                return back()->with('error', _lang('Invoice is already paid !'));
            }

            $attachment = "";
            if ($request->hasfile('attachment')) {
                $file       = $request->file('attachment');
                $attachment = time() . $file->getClientOriginalName();
                $file->move(public_path() . "/uploads/transactions/", $attachment);
            }

            $transaction                    = new Transaction();
            $transaction->trans_date        = date('Y-m-d');
            $transaction->account_id        = $request->input('account_id');
            $transaction->chart_id          = $request->input('chart_id');
            $transaction->type              = 'income';
            $transaction->dr_cr             = 'cr';
            $transaction->amount            = $request->input('amount');
            $transaction->payer_payee_id    = $request->input('client_id');
            $transaction->payment_method_id = $request->input('payment_method_id');
            $transaction->invoice_id        = $request->input('invoice_id');
            $transaction->reference         = $request->input('reference');
            $transaction->note              = $request->input('note');
            $transaction->attachment        = $attachment;

            $transaction->save();

            //Update Invoice Table
            $invoice->paid = $invoice->paid + $transaction->amount;
            if ($invoice->paid >= $invoice->grand_total) {
                $invoice->status = 'Paid';
            } else if ($invoice->paid > 0 && ($invoice->paid < $invoice->grand_total)) {
                $invoice->status = 'Partially_Paid';
            }
            $invoice->save();

            if ($request->ajax()) {
                return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Payment was made Sucessfully'), 'data' => $transaction]);
            }
        }
    }

    public function view_payment(Request $request, $invoice_id)
    {

        $transactions = Transaction::where('invoice_id', $invoice_id)->get();

        if (!$request->ajax()) {
            return view('backend.accounting.invoice.view_payment', compact('transactions'));
        } else {
            return view('backend.accounting.invoice.modal.view_payment', compact('transactions'));
        }
    }

    public function send_email(Request $request, $invoice_id = '')
    {
        if ($request->isMethod('get')) {
            $invoice = Invoice::find($invoice_id);

            $client_email = $invoice->client->contact_email;

            if ($request->ajax()) {
                return view('backend.accounting.invoice.modal.send_email', compact('client_email', 'invoice'));
            }
            return back();
        } else if ($request->isMethod('post')) {

            @ini_set('max_execution_time', 0);
            @set_time_limit(0);
            Overrider::load("Settings");

            $validator = Validator::make($request->all(), [
                'email_subject' => 'required',
                'email_message' => 'required',
                'contact_email' => 'required',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                } else {
                    return back()->withErrors($validator)
                        ->withInput();
                }
            }

            //Send email
            $subject       = $request->input("email_subject");
            $message       = $request->input("email_message");
            $contact_email = $request->input("contact_email");

            $contact = \App\Contact::where('contact_email', $contact_email)->first();
            $invoice = Invoice::find($request->invoice_id);

            $currency = currency();

            if ($contact) {
                //Replace Paremeter
                $replace = array(
                    '{customer_name}'  => $contact->contact_name,
                    '{invoice_no}'     => $invoice->invoice_number,
                    '{invoice_date}'   => $invoice->invoice_date,
                    '{due_date}'       => $invoice->due_date,
                    '{payment_status}' => _dlang(str_replace('_', ' ', $invoice->status)),
                    '{grand_total}'    => decimalPlace($invoice->grand_total, $currency),
                    '{amount_due}'     => decimalPlace(($invoice->grand_total - $invoice->paid), $currency),
                    '{total_paid}'     => decimalPlace($invoice->paid, $currency),
                    '{invoice_link}'   => route('client.view_invoice', encrypt($invoice->id)),
                );
            }

            $mail          = new \stdClass();
            $mail->subject = $subject;
            $mail->body    = process_string($replace, $message);

            try {
                Mail::to($contact_email)->send(new GeneralMail($mail));
            } catch (\Exception $e) {
                dd($e);
                if (!$request->ajax()) {
                    return back()->with('error', _lang('Sorry, Error Occured !'));
                } else {
                    return response()->json(['result' => 'error', 'message' => _lang('Sorry, Error Occured !')]);
                }
            }

            if (!$request->ajax()) {
                return back()->with('success', _lang('Email Send Sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Email Send Sucessfully'), 'data' => $contact]);
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $invoice       = Invoice::leftJoin('tipo_transmision', 'invoices.tipotrans_id', '=', 'tipo_transmision.tipotrans_id')
            ->leftJoin('modelo_facturacion', 'invoices.modfact_id', '=', 'modelo_facturacion.modfact_id')
            ->leftJoin('recinto_fiscal as rf', 'invoices.refisc_id', '=', 'rf.refisc_id')
            ->leftJoin('regimen as reg', 'invoices.regi_id', '=', 'reg.regi_id')
            ->leftJoin('condicion_operacion as co', 'invoices.conop_id', '=', 'co.conop_id')
            ->select(
                'invoices.*',
                DB::raw('SUBSTRING_INDEX(tipo_transmision.tipotrans_nombre, " ", -1) as transmision'),
                DB::raw('SUBSTRING_INDEX(modelo_facturacion.modfact_nombre, " ", -1) AS modelo_facturacion'),
                'rf.refisc_nombre AS nombre_recinto',
                'reg.regi_nombre AS nombre_regimen',
                'co.conop_nombre AS condicion_op',
            )
            ->find($id);

        // dd($invoice->company->company_name);
        $invoice_taxes = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();
        $transactions = Transaction::where("invoice_id", $id)->get();

        $url = generateUrl($invoice);

        switch ($invoice->tipodoc_id) {
            case '01': // FE
                return view('backend.accounting.invoice.fe.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;
            case '03': // CCFE
                return view('backend.accounting.invoice.ccf.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;
            case '04': // NR
                return view('backend.accounting.invoice.nr.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;   
            case '05': // NC
                return view('backend.accounting.invoice.nc.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;
            case '06': // ND
                return view('backend.accounting.invoice.nc.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;
            case '11': // FEXE
                return view('backend.accounting.invoice.fex.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;
            case '14': // FSE
                return view('backend.accounting.invoice.fse.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;

            default:
                return view('backend.accounting.invoice.view', compact('invoice', 'transactions', 'invoice_taxes', 'id', 'url'));
                break;
        }
    }


    /**
     * Generate PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_pdf(Request $request, $id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $invoice       = Invoice::leftJoin('tipo_transmision', 'invoices.tipotrans_id', '=', 'tipo_transmision.tipotrans_id')
            ->leftJoin('modelo_facturacion', 'invoices.modfact_id', '=', 'modelo_facturacion.modfact_id')
            ->leftJoin('recinto_fiscal as rf', 'invoices.refisc_id', '=', 'rf.refisc_id')
            ->leftJoin('regimen as reg', 'invoices.regi_id', '=', 'reg.regi_id')
            ->leftJoin('condicion_operacion as co', 'invoices.conop_id', '=', 'co.conop_id')
            ->select(
                'invoices.*',
                DB::raw('SUBSTRING_INDEX(tipo_transmision.tipotrans_nombre, " ", -1) as transmision'),
                DB::raw('SUBSTRING_INDEX(modelo_facturacion.modfact_nombre, " ", -1) AS modelo_facturacion'),
                'rf.refisc_nombre AS nombre_recinto',
                'reg.regi_nombre AS nombre_regimen',
                'co.conop_nombre AS condicion_op',
            )
            ->find($id);

        $data['invoice']       = $invoice;
        $data['invoice_taxes'] = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();
        $data['transactions'] = Transaction::where("invoice_id", $id)->get();
        $data['prueba'] = 'prueba____';

        $url = generateUrl($invoice); // Genera la URL

        $codigoQR = QrCode::size(100)->generate($url); //Lo convierte a QR
        if (isset($request->ticket)) {
            $codigoQR = QrCode::size(500)->generate($url); //Lo convierte a QR
        }


        $data['codigoQR'] = $codigoQR;

        switch ($invoice->tipodoc_id) {
            case '01': // FE
                if (isset($request->ticket)) {
                    $customPaper = array(0, 0, 50, 80);
                    $pdf = PDF::loadView("backend.accounting.invoice.fe.pdf_export_ticket", $data);
                    // $pdf->setPaper('b7', 'portrait');
                    // $page_count = $pdf->getDomPDF()->get_canvas( )->get_page_number();
                    $pdf->setPaper([0, 0, 276.77, 650]);
                } else {
                    $pdf = PDF::loadView("backend.accounting.invoice.fe.facturaFe", $data);
                    $pdf->setPaper('letter', 'portrait');
                }
                break;
            case '03': // CCFE
                if (!isset($request->ticket)) {
                    $pdf = PDF::loadView("backend.accounting.invoice.ccf.facturaCCF", $data);
                    $customPaper = array(0, 0, 1275, 1650);
                    $pdf->setPaper('letter', 'portrait');
                } else {
                    $pdf = PDF::loadView("backend.accounting.invoice.ccf.pdf_export_ticket", $data);
                    $customPaper = array(0, 0, 50, 80);
                    $pdf->setPaper([0, 0, 276.77, 650]);
                }
                break;
            case '04': // NR
                $pdf = PDF::loadView("backend.accounting.invoice.nr.facturaNR", $data);
                $customPaper = array(0, 0, 1275, 1650);
                $pdf->setPaper('letter', 'portrait');
                break;
            case '05': // NC
                $pdf = PDF::loadView("backend.accounting.invoice.nc.pdf_export", $data);
                $customPaper = array(0, 0, 1275, 1650);
                $pdf->setPaper('letter', 'portrait');
                break;
            case '06': // ND
                $pdf = PDF::loadView("backend.accounting.invoice.nc.pdf_export", $data);
                $customPaper = array(0, 0, 1275, 1650);
                $pdf->setPaper('letter', 'portrait');
                break;
            case '11': // FEXE
                $pdf = PDF::loadView("backend.accounting.invoice.fex.facturaEX", $data);
                $customPaper = array(0, 0, 1275, 1650);
                $pdf->setPaper('letter', 'portrait');
                break;
            case '14': // SUJETO EXCLUIDO 
                $pdf = PDF::loadView("backend.accounting.invoice.fse.facturaSE", $data);
                $customPaper = array(0, 0, 1275, 1650);
                $pdf->setPaper('letter', 'portrait');
                break;
        }

        $pdf->setWarnings(false);

        return $pdf->stream('factura.pdf', array('Attachment' => 0));
        // return $pdf->download("invoice_{$invoice->invoice_number}.pdf");

    }


    /**
     * descargar pdf anexo descargo
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_anexo_descargo(Request $request, $id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $invoice               = Invoice::find($id);
        $data['invoice']       = $invoice;
        $data['invoice_taxes'] = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();
        $data['transactions'] = Transaction::where("invoice_id", $id)->get();

        $pdf = PDF::loadView("backend.accounting.invoice.fex.anexo_descargo", $data);

        $pdf->setPaper('letter', 'landscape');


        $pdf->setWarnings(false);

        return $pdf->stream();
    }

    public function sendInvoiceToHacienda($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        $versionJson = $invoice->tipo_documento->version_json;
        $ambiente = env('API_AMBIENTE_MH');
        // $dteJson = self::getDteJsonCCF($invoice, $versionJson, $ambiente);
        $dteJson = '';
        switch ($invoice->tipodoc_id) {
            case '01':
                $dteJson = self::getDteJsonFE($invoice, $versionJson, $ambiente);
                break;
            case '03':
                $dteJson = self::getDteJsonCCF($invoice, $versionJson, $ambiente);
                break;
            case '04':
                $dteJson = self::getDteJsonNotaRemision($invoice, $versionJson, $ambiente);
                break;
            case '05':
                $dteJson = self::getDteJsonNotaDebitoCredito($invoice->tipodoc_id, $invoice, $versionJson, $ambiente);
                break;
            case '06':
                $dteJson = self::getDteJsonNotaDebitoCredito($invoice->tipodoc_id, $invoice, $versionJson, $ambiente);
                break;
            case '11':
                $dteJson = self::getDteJsonFEX($invoice, $versionJson, $ambiente);
                break;
            case '14':
                $dteJson = self::getDteJsonSujetoExcluido($invoice, $versionJson, $ambiente);
                break;
            default:
                break;
        }

        // Log::info('Carga Útil de la Solicitud: ' . json_encode([
        //     "nit" => str_replace('-', '', get_option('nit')),
        //     "ambiente" => $ambiente,
        //     "idEnvio" => 1,
        //     "version" => intval($versionJson),
        //     "tipoDte" => $invoice->tipodoc_id,
        //     'dteJson' => $dteJson
        // ]));

        try {


            $oldToken = PasarelaToken::where('status', '=', 1)->first();
            if (!$oldToken) {
                Log::info('No existe token antiguo, se solicita token');
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                $token = PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            } else {
                $fechaActual = Carbon::now();
                $fechaExpiracion = $oldToken->expired_token;
                if ($fechaActual->gt($fechaExpiracion)) {
                    Log::info('Token expirado, se solicita nuevo token');
                    $tokenPasarela = $this->generateTokenPasarela();
                    $tokenPasarela = json_decode(json_encode($tokenPasarela));
                    $oldToken->update([
                        'status' => 0
                    ]);
                    $oldToken->delete();
                    PasarelaToken::create([
                        'token'         => $tokenPasarela->token,
                        'created_token' => $tokenPasarela->created,
                        'expired_token' => $tokenPasarela->expired,
                    ]);
                    Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                } else {
                    Log::info('Token aun sin expirar');
                    $tokenPasarela = $oldToken;
                    Log::info('Token: Bearer ' . $tokenPasarela->token);
                }
            }

            if (!property_exists($tokenPasarela, 'token')) {
                Log::info('No existe propiedad token, por lo que se solicita nuevo token');
                PasarelaToken::where('status', '=', 1)->update(['status' => 0]);
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            }

            // Log::info(json_encode($tokenPasarela));

            // Log::info('Datos de token enviado: ' . json_encode($tokenPasarela));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenPasarela->token,
                'x-key-nit' => env('API_KEY_NIT')
            ])
                ->post(
                    env('API_PASARELA_FE'),
                    [
                        "nit" => str_replace('-', '', get_option('nit')),
                        "ambiente" => $ambiente,
                        "idEnvio" => 1,
                        "version" => $versionJson,
                        "tipoDte" => $invoice->tipodoc_id,
                        'dteJson' => $dteJson
                    ]
                )
                ->json();

            // Log::info('Respuesta API MH: ' . json_encode($response));

            return $response;
        } catch (\Exception $e) {

            Log::error('Error en la solicitud HTTP: ' . $e->getMessage());

            return response()->json(['error' => 'Hubo un problema en la solicitud HTTP'], 500);
        }
    }

    public function anularInvoiceMH($invoice_id, Request $request)
    {

        $invoice        = Invoice::find($invoice_id);
        $company        = Company::find($invoice->company_id);
        $ambiente       = env('API_AMBIENTE_MH');
        $dteJson        = [];

        $identificacion = [
            "version"           => 2,
            "ambiente"          => $ambiente,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "fecAnula"          => Carbon::now()->format('Y-m-d'),
            "horAnula"          => Carbon::now()->format('H:i:s')
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nombre"                => get_option('company_name'),
            "tipoEstablecimiento"   => $company->tipoest_id,
            "nomEstablecimiento"    => get_option('tradename'),
            "codEstableMH"          => null,
            "codEstable"            => null,
            "codPuntoVentaMH"       => null,
            "codPuntoVenta"         => null,
            "telefono"              => $company->cellphone,
            "correo"                => $company->email
        ];

        $codigo_dte = null;

        if ($request->dte_reemplaza != '' && $request->tipo_anulacion != 2) {
            $dte = Invoice::find($request->dte_reemplaza);
            $codigo_dte = $dte->codigo_generacion;
        }

        $documento = [
            "tipoDte"           => $invoice->tipodoc_id,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "selloRecibido"     => $invoice->sello_recepcion,
            "numeroControl"     => $invoice->numero_control,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "montoIva"          => floatval($invoice->tax_total),
            "codigoGeneracionR" => $codigo_dte,
            "tipoDocumento"     => $invoice->tdocrec_id,
            "numDocumento"      => $invoice->num_documento,
            "nombre"            => $invoice->name_invoice,
            "telefono"          => $invoice->client->contact_phone,
            "correo"            => $invoice->client->contact_email
        ];

        $usuario = Auth::user();

        if ($usuario->dui == '') {
            throw new UsuarioSinDUIException();
        }


        $motivo = [
            "tipoAnulacion"     => intval($request->tipo_anulacion),
            "motivoAnulacion"   => $request->motivo_anulacion,
            "nombreResponsable" => $usuario->name,
            "tipDocResponsable" => "13",
            "numDocResponsable" => $usuario->dui,
            "nombreSolicita"    => $request->nombre_soli,
            "tipDocSolicita"    => $request->tipo_doc_soli,
            "numDocSolicita"    => $request->num_doc_soli
        ];

        $dteJson = [
            "identificacion"    => $identificacion,
            "emisor"            => $emisor,
            "documento"         => $documento,
            "motivo"            => $motivo,
        ];

        Log::info('Carga Útil de la Solicitud: ' . json_encode([
            "nit"       => str_replace('-', '', get_option('nit')),
            "ambiente"  => $ambiente,
            "idEnvio"   => 1,
            "version"   => 2,
            'dteJson'   => $dteJson
        ]));

        try {

            $oldToken = PasarelaToken::where('status', '=', 1)->first();
            if (!$oldToken) {
                Log::info('No existe token antiguo, se solicita token');
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                $token = PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            } else {
                $fechaActual = Carbon::now();
                $fechaExpiracion = $oldToken->expired_token;
                if ($fechaActual->gt($fechaExpiracion)) {
                    Log::info('Token expirado, se solicita nuevo token');
                    $tokenPasarela = $this->generateTokenPasarela();
                    $tokenPasarela = json_decode(json_encode($tokenPasarela));
                    $oldToken->update([
                        'status' => 0
                    ]);
                    $oldToken->delete();
                    PasarelaToken::create([
                        'token'         => $tokenPasarela->token,
                        'created_token' => $tokenPasarela->created,
                        'expired_token' => $tokenPasarela->expired,
                    ]);
                    Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                } else {
                    Log::info('Token aun sin expirar');
                    $tokenPasarela = $oldToken;
                    Log::info('Token: Bearer ' . $tokenPasarela->token);
                }
            }

            if (!property_exists($tokenPasarela, 'token')) {
                Log::info('No existe propiedad token, por lo que se solicita nuevo token');
                PasarelaToken::where('status', '=', 1)->update(['status' => 0]);
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            }

            Log::info(json_encode($tokenPasarela));

            Log::info('Datos de token enviado: ' . json_encode($tokenPasarela));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenPasarela->token,
                'x-key-nit' => env('API_KEY_NIT')
            ])
                ->post(
                    env('API_PASARELA_ANULACION'),
                    [
                        "nit"       => str_replace('-', '', get_option('nit')),
                        "ambiente"  => $ambiente,
                        "idEnvio"   => 1,
                        "version"   => 2,
                        'dteJson'   => $dteJson
                    ]
                )
                ->json();

            Log::info('Respuesta API MH: ' . json_encode($response));

            return $response;
        } catch (\Exception $e) {

            Log::error('Error en la solicitud HTTP: ' . $e->getMessage());

            return response()->json(['error' => 'Hubo un problema en la solicitud HTTP'], 500);
        }
    }

    public function contingenciaInvoiceMH($id, Request $request)
    {

        $invoice    = Invoice::find($id);
        $company    = Company::find($invoice->company_id);
        $ambiente   = env('API_AMBIENTE_MH');
        $dteJson    = [];

        $identificacion = [
            "version"           => 3,
            "ambiente"          => $ambiente,
            "codigoGeneracion"  => strtoupper(generateUUID()),
            "fTransmision"      => Carbon::now()->format('Y-m-d'),
            "hTransmision"      => Carbon::now()->format('H:i:s')
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nombre"                => get_option('company_name'),
            "nombreResponsable"     => $request->responsableEstablecimiento,
            "tipoDocResponsable"    => $request->tipoDocRespEstablecimiento,
            "numeroDocResponsable"  => $request->numDocRespEstablecimiento,
            "tipoEstablecimiento"   => $company->tipoest_id,
            "codEstableMH"          => null,
            "telefono"              => str_replace(['-', '+'], '', $company->cellphone),
            "correo"                => $company->email
        ];

        $detalleDTE = [
            'noItem'             => 1, //SE DEJA 1 YA QUE SE UTILIZARA CONTINGENCIA DE 1 A 1 
            'codigoGeneracion'   => $invoice->codigo_generacion,
            'tipoDoc'            => $invoice->tipodoc_id,
        ];

        $motivo = [
            'fInicio'            => $request->fecha_inicio_contingencia,
            'fFin'               => $request->fecha_fin_contingencia,
            'hInicio'            => Carbon::createFromFormat('H:i', $request->hora_inicio_contingencia)->format('H:i:s'),
            'hFin'               => Carbon::createFromFormat('H:i', $request->hora_fin_contingencia)->format('H:i:s'),
            'tipoContingencia'   => intval($request->tipoContingencia),
            'motivoContingencia' => $request->motivo_contingencia,
        ];

        $dteJson = [
            "identificacion"    => $identificacion,
            "emisor"            => $emisor,
            "detalleDTE"        => [$detalleDTE],
            "motivo"            => $motivo,
        ];

        Log::info('Carga Útil de la Solicitud: ' . json_encode([
            "nit"       => str_replace('-', '', get_option('nit')),
            "ambiente"  => $ambiente,
            "idEnvio"   => 1,
            "version"   => 3,
            'dteJson'   => $dteJson
        ]));

        try {

            $invoice_cod = InvoiceContingencia::where('codigo_generacion', '=', $invoice->codigo_generacion)->first();

            if (!$invoice_cod) {

                Log::info('No existe código de generación para este DTE por lo que se envia a evento de contingencia');

                $oldToken = PasarelaToken::where('status', '=', 1)->first();
                if (!$oldToken) {
                    Log::info('No existe token antiguo, se solicita token');
                    $tokenPasarela = $this->generateTokenPasarela();
                    $tokenPasarela = json_decode(json_encode($tokenPasarela));
                    $token = PasarelaToken::create([
                        'token'         => $tokenPasarela->token,
                        'created_token' => $tokenPasarela->created,
                        'expired_token' => $tokenPasarela->expired,
                    ]);
                    Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                } else {
                    $fechaActual = Carbon::now();
                    $fechaExpiracion = $oldToken->expired_token;
                    if ($fechaActual->gt($fechaExpiracion)) {
                        Log::info('Token expirado, se solicita nuevo token');
                        $tokenPasarela = $this->generateTokenPasarela();
                        $tokenPasarela = json_decode(json_encode($tokenPasarela));
                        $oldToken->update([
                            'status' => 0
                        ]);
                        $oldToken->delete();
                        PasarelaToken::create([
                            'token'         => $tokenPasarela->token,
                            'created_token' => $tokenPasarela->created,
                            'expired_token' => $tokenPasarela->expired,
                        ]);
                        Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                    } else {
                        Log::info('Token aun sin expirar');
                        $tokenPasarela = $oldToken;
                        Log::info('Token: Bearer ' . $tokenPasarela->token);
                    }
                }

                Log::info(json_encode($tokenPasarela));

                Log::info('Datos de token enviado: ' . json_encode($tokenPasarela));

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenPasarela->token,
                    'x-key-nit' => env('API_KEY_NIT')
                ])
                    ->post(
                        env('API_PASARELA_CONTINGENCIA'),
                        [
                            "nit"       => str_replace('-', '', get_option('nit')),
                            "ambiente"  => $ambiente,
                            "idEnvio"   => 1,
                            "version"   => 2,
                            'dteJson'   => $dteJson
                        ]
                    )
                    ->json();

                Log::info('Respuesta API MH: ' . json_encode($response));
                $response_mh = json_decode(json_encode($response));

                if ($response_mh->estado === 'RECIBIDO') {

                    $contingencia = InvoiceContingencia::create([
                        'invoice_id'        => $id,
                        'type_dte'          => $invoice->tipodoc_id,
                        'codigo_generacion' => $invoice->codigo_generacion,
                        'sello_recepcion'   => $response_mh->selloRecibido,
                        'json_contingencia' => json_encode($dteJson),
                        'response_mh'       => json_encode($response),
                    ]);

                    $fecha_contingencia = $request->fecha_inicio_contingencia . ' ' . Carbon::createFromFormat('H:i', $request->hora_inicio_contingencia)->format('H:i:s');

                    $invoice->tconting_id           = $request->tipoContingencia;
                    $invoice->motivo_contingencia   = $request->motivo_contingencia;
                    $invoice->invoice_date          = $request->fecha_inicio_contingencia;
                    $invoice->created_at            = $fecha_contingencia;
                    $invoice->save();

                    $reenvio_dte = $this->sendInvoiceToHacienda($id);
                    $reenvio_mh = json_decode(json_encode($reenvio_dte));


                    if (!property_exists($reenvio_mh, 'estado')) {

                        log::info('Error al reenviar DTE en contingenciaInvoiceMH (propiedad estado no existe en respuesta apihacienda): ' . json_encode($response));

                        if ($request->ajax()) {
                            return response()->json(['result' => 'error', 'message' => 'Error en respuesta de pasarela']);
                        } else {
                            return redirect()->route('invoices.create')
                                ->withErrors(['Sorry, Error Occured !', 'Error en pasarela'])
                                ->withInput();
                        }
                    }

                    $invoice->status_mh         = ($reenvio_mh->estado === 'RECHAZADO') ? 0 : 1;


                    if ($reenvio_mh->estado === 'RECHAZADO') {

                        log::info('Error al reenviar DTE en contingenciaInvoiceMH (estado: rechazado): ' . json_encode($response));

                        if ($request->ajax()) {
                            return response()->json(['result' => 'errorMH', 'action' => 'store', 'message' => _lang('Error al procesar DTE'), 'data' => $response]);
                        } else {
                            return redirect()->route('invoices.create', $invoice->id)->with('error', 'Error al procesar DTE');
                        }
                    } else if ($reenvio_mh->estado === 'PROCESADO') {

                        $invoice->response_mh       = json_encode($reenvio_dte);
                        $invoice->sello_recepcion   = $reenvio_mh->selloRecibido;
                        $invoice->json_dte          = json_encode($reenvio_mh->json);
                        $invoice->save();

                        $contingencia->estado = 1;
                        $contingencia->save();

                        $this->sendEmailFactura($invoice->id);

                        log::info('DTE CON ID: ' . $invoice->id . ' Procesado luego de contingencia y enviado por correo a:' . $invoice->correo);

                        if (!$request->ajax()) {
                            return redirect()->route('invoices.show', $invoice->id)->with('success', _lang('Invoice Signed Sucessfully '));
                        } else {
                            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Invoice Signed Sucessfully '), 'data' => $invoice]);
                        }
                    }
                }
                if ($response_mh->estado === 'RECHAZADO') {

                    log::info('Error al reenviar DTE en contingencia: ' . $response_mh->mensaje);

                    if ($request->ajax()) {
                        return response()->json(['result' => 'errorMH', 'action' => 'store', 'message' => _lang($response_mh->mensaje), 'data' => $reenvio_dte]);
                    } else {
                        return redirect()->route('invoices.create', $invoice->id)->with('error', _lang($response_mh->mensaje));
                    }
                }
            } else {

                Log::info('Existe codigo de generación registrado de contingencia para DTE con ID: ' . $invoice->id);

                $fecha_contingencia = $request->fecha_inicio_contingencia . ' ' . Carbon::createFromFormat('H:i', $request->hora_inicio_contingencia)->format('H:i:s');

                $invoice->tconting_id           = $request->tipoContingencia;
                $invoice->motivo_contingencia   = $request->motivo_contingencia;
                $invoice->invoice_date          = $request->fecha_inicio_contingencia;
                $invoice->created_at            = $fecha_contingencia;
                $invoice->save();

                $reenvio_dte = $this->sendInvoiceToHacienda($id);
                $reenvio_mh = json_decode(json_encode($reenvio_dte));


                if (!property_exists($reenvio_mh, 'estado')) {

                    log::info('Error en respuesta de pasarela, verificar!');

                    if ($request->ajax()) {
                        return response()->json(['result' => 'error', 'message' => 'Error en respuesta de pasarela']);
                    } else {
                        return redirect()->route('invoices.create')
                            ->withErrors(['Sorry, Error Occured !', 'Error en pasarela'])
                            ->withInput();
                    }
                }

                $invoice->status_mh         = ($reenvio_mh->estado === 'RECHAZADO') ? 0 : 1;

                if ($reenvio_mh->estado === 'RECHAZADO') {

                    log::info('Error al reenviar DTE en contingencia: ' . json_encode($reenvio_dte));

                    if ($request->ajax()) {
                        return response()->json(['result' => 'errorMH', 'action' => 'store', 'message' => _lang('Error al procesar DTE'), 'data' => $reenvio_dte]);
                    } else {
                        return redirect()->route('invoices.create', $invoice->id)->with('error', 'Error al procesar DTE');
                    }
                } else if ($reenvio_mh->estado === 'PROCESADO') {

                    $invoice->response_mh       = json_encode($reenvio_dte);
                    $invoice->sello_recepcion   = $reenvio_mh->selloRecibido;
                    $invoice->json_dte          = json_encode($reenvio_mh->json);
                    $invoice->save();


                    $invoice_cod->estado = 1;
                    $invoice_cod->save();

                    $this->sendEmailFactura($invoice->id);

                    log::info('DTE CON ID: ' . $invoice->id . ' Procesado luego de contingencia y enviado por correo a:' . $invoice->correo);

                    if (!$request->ajax()) {
                        return redirect()->route('invoices.show', $invoice->id)->with('success', _lang('Invoice Signed Sucessfully'));
                    } else {
                        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Invoice Signed Sucessfully '), 'data' => $invoice]);
                    }
                }
            }
        } catch (\Exception $e) {

            Log::error('Error en la solicitud HTTP: ' . $e->getMessage());

            return response()->json(['error' => 'Hubo un problema en la solicitud HTTP'], 500);
        }
    }

    public function generateTokenPasarela()
    {

        $user       = env('PASARELA_API_USER');
        $password   = env('PASARELA_API_PWD');
        $url        = env('URL_API_LOGIN');

        try {
            $token = Http::post(
                $url,
                [
                    'name'      => $user,
                    'password'  => $password,
                ]
            )
                ->json();

            return $token;
        } catch (\Throwable $th) {
            Log::error('ERROR EN LOGIN API PASARELA: ' . $th->getMessage());
            return response()->json(['Error al iniciar sesion en API pasarela'], 500);
        }
    }


    private static function getDteJsonCCF($invoice, $versionJson, $ambiente)
    {
        // dd($invoice);
        $company = Company::find($invoice->company_id);
        $details = [];
        $documentosRelacionados = null;

        $noSujetoSum = 0.0;
        $exentoSum = 0.0;
        $gravadoSum = 0.0;
        $descNoSujetoSum = 0.0;
        $descExentoSum = 0.0;
        $descGravadoSum = 0.0;
        $descGlobalExento = 0.0;
        $descGlobalNoSujeto = 0.0;
        $descGlobalGravado = 0.0;
        $ivaPercibido = 0.0;
        $rentaRetenida = 0.0;

        // caso CCF
        $tributos = InvoiceItemTax::select('tributos.trib_id as codigo', 'tributos.trib_nombre as descripcion')
            ->join('taxs', 'invoice_item_taxes.tax_id', '=', 'taxs.id')
            ->join('tributos', 'taxs.trib_id', '=', 'tributos.trib_id')
            ->where('invoice_id', $invoice->id)
            ->groupBy('tributos.trib_id')
            ->selectRaw('SUM(invoice_item_taxes.amount) as valor')
            ->get();

        $resultadosFormateados = [];
        foreach ($tributos as $tributo) {
            $resultadosFormateados[] = [
                'codigo' => $tributo->codigo,
                'descripcion' => $tributo->descripcion,
                'valor' => floatval(number_format($tributo->valor, 2, '.', '')),
            ];
        }

        // Separar los componentes de la fecha
        $fechaComponents = explode('/', $invoice->invoice_date);

        // Construir la fecha en el formato 'yyyy-mm-dd'
        $fechaFormateada = $fechaComponents[2] . '-' . $fechaComponents[1] . '-' . $fechaComponents[0];

        // Crear un objeto DateTime con la fecha formateada
        $fechaDateTime = new DateTime($fechaFormateada);

        foreach ($invoice->invoice_items as $key => $value) {
            $exento = 0.0;
            $noSujeto = 0.0;
            $gravado = 0.0;
            if ($invoice->exento_iva == 'si') {
                $exento = $value->sub_total;
                $exentoSum += $value->sub_total;
                $descExentoSum += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto = $value->sub_total;
                $noSujetoSum += $value->sub_total;
                $descNoSujetoSum += $value->discount;
            } else {
                $gravado = $value->sub_total;
                $gravadoSum += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            $totalTributos = 0;

            // Calcular el total de los valores de tributos
            foreach ($tributos as $tributo) {
                $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
            }

            // Calcular el subtotal
            $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

            $tributos_ = null;

            if( $invoice->exento_iva == 'no' ){
                $tributos_ = Tax::whereIn('id', $value->taxes->pluck('tax_id')->toArray())
                ->pluck('trib_id')->toArray();
            }

            array_push($details, [
                "numItem" => $value->line,
                "tipoItem" => ( $value->item_id > 0 ) ? intval($value->item->tipoitem_id) : 1,
                "numeroDocumento" => ( $value->cod_dte_rel != '') ? $value->cod_dte_rel : null,
                "codigo" => $value->item->product->product_code,
                "codTributo" => null,
                "descripcion" => $value->description,
                "cantidad" => intval($value->quantity),
                "uniMedida" => ( $value->item_id > 0 ) ? intval($value->item->product->unim_id) : 59,
                "precioUni" => floatval(number_format($value->unit_cost, 6, '.', '')),
                "montoDescu" => intval($value->discount),
                "ventaNoSuj" => floatval(number_format($noSujeto, 2, '.', '')),
                "ventaExenta" => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada" => floatval(number_format($gravado, 2, '.', '')),
                "tributos" => $tributos_,
                "psv" => 0.0,
                "noGravado" => 0.0
            ]);

            if( $value->cod_dte_rel != '' ){

                $documentosRelacionados = [];
                $documentosRelacionados = collect($documentosRelacionados);

                $documentos = [
                    'tipoDocumento'      => $value->type_dte_rel,
                    'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
                    'numeroDocumento'    => $value->cod_dte_rel,
                    'fechaEmision'       => $value->date_dte_rel,
                ];

                if( !$documentosRelacionados->contains('numeroDocumento', $value->cod_dte_rel) ){
                    $nuevoDocumento = [
                        'tipoDocumento'      => $value->type_dte_rel,
                        'tipoGeneracion'     => 2, // 1= físico, 2= electrónico
                        'numeroDocumento'    => $value->cod_dte_rel,
                        'fechaEmision'       => $value->date_dte_rel,
                    ];
                
                    $documentosRelacionados->push($nuevoDocumento);
                }
            }
        }

        $descuentos = $invoice->general_discount;
        $descuentos = $descuentos;

        $total_gravadas    = $gravadoSum;
        $total_subtotal    = $total_gravadas - $descuentos;
        $total_totalPagar  = $total_subtotal - $invoice->iva_retenido;

        if( $descuentos > 0 ){

            // $calculo_iva            = $total_subtotal * 1.13;
            // $total_totalIva_items   = $calculo_iva - $total_subtotal;
            $totalIva               = floatval($invoice->tax_total);
            $totalTributos          = floatval($invoice->tax_total);

            foreach( $resultadosFormateados as &$value ){
                if( $value['codigo'] == 20 ){
                    $value['valor'] = floatval(number_format($totalIva, 2, '.', ''));
                }
            }
            unset($value);
        }

        $dteJson = [
            "identificacion" => [
                "version" => intval($versionJson),
                "ambiente" => $ambiente,
                "tipoDte" => $invoice->tipodoc_id,
                "numeroControl" => $invoice->numero_control,
                "codigoGeneracion" => $invoice->codigo_generacion,
                "tipoModelo" => intval($invoice->modfact_id),
                "tipoOperacion" => intval($invoice->tipotrans_id),
                "tipoContingencia" => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
                "motivoContin" => $invoice->motivo_contingencia,
                "fecEmi" => $fechaDateTime->format('Y-m-d'),
                "horEmi" => (new DateTime($invoice->created_at))->format('H:i:s'),
                "tipoMoneda" => "USD"
            ],
            "documentoRelacionado" => $documentosRelacionados,
            "emisor" => [
                "nit" => str_replace('-', '', get_option('nit')),
                "nrc" => str_replace('-', '', get_option('nrc')),
                "nombre" => get_option('name_company'),
                "codActividad" => get_option('cod_actividad'),
                "descActividad" => get_option('desc_actividad'),
                "nombreComercial" => get_option('tradename'),
                "tipoEstablecimiento" => $company->tipoest_id,
                "direccion" => [
                    "departamento" => $company->depa_id,
                    "municipio" => Municipio::find($company->munidepa_id)->muni_id,
                    "complemento" => $company->address
                ],
                // "telefono"=> $company->cellphone,
                "telefono" => str_replace(['-', '+'], '', $company->cellphone),
                "correo" => $company->email,
                "codEstableMH" => null,
                "codEstable" => null,
                "codPuntoVentaMH" => null,
                "codPuntoVenta" => null
            ],
            "receptor" => [
                "nit" => str_replace('-', '', $invoice->client->nit),
                "nrc" => str_replace('-', '',  $invoice->client->nrc),
                "nombre" => $invoice->name_invoice,
                "codActividad" => $invoice->client->actie_id,
                "descActividad" => $invoice->client->descActividad,
                "nombreComercial" => $invoice->client->tradename,
                "direccion" => !isset($invoice->client->munidepa_id) ? null : [ 
                    "departamento" => $invoice->client->depa_id ?? '06',
                    "municipio" => Municipio::find($invoice->client->munidepa_id ?? 0)->muni_id ?? '14',
                    "complemento" => $invoice->complemento
                ],
                // "telefono"=> $invoice->telefono,
                "telefono" => str_replace(['-', '+'], '', $invoice->telefono),
                "correo" => $invoice->correo
            ],
            "otrosDocumentos" => null,
            "ventaTercero" => null,
            "cuerpoDocumento" => $details,
            "resumen" => [
                "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
                "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
                "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
                "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
                "descuNoSuj"            => floatval(number_format(0.0, 2, '.', '')), // este campo es diferente al descuento por item no sujeto, aca debe ir un valor que en el 
                // formulario de creacion de invoice diga "Descuento global a ventas no sujetas"
                "descuExenta"           => floatval(number_format(0.0, 2, '.', '')),
                "descuGravada"          => floatval(number_format($descuentos, 2, '.', '')),
                "porcentajeDescuento"   => floatval(number_format(0.0, 2, '.', '')),
                // "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')), // uso informativo descuentos por item + descuentos globales por tipo de venta ej $descGlobalExento+$descGlobalNoSujeto
                "totalDescu"            => floatval(number_format($descuentos, 2, '.', '')), // uso informativo descuentos por item + descuentos globales por tipo de venta ej $descGlobalExento+$descGlobalNoSujeto
                "tributos"              => $resultadosFormateados,


                // [ // colocar aca los mismo tributos de los items
                // {
                //     "codigo"=> "20",
                //     "descripcion"=> "Impuesto al Valor Agregado 13%",
                //     "valor"=> 0.59
                // }
                //],
                // "subTotal"              => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado), 2, '.', '')), // menos $descGlobalNoSujeto, etc,
                "subTotal"              => floatval(number_format($total_subtotal, 2, '.', '')), // menos $descGlobalNoSujeto, etc,
                "ivaPerci1"             => floatval(number_format($invoice->iva_percibido, 2, '.', '')),
                "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
                "reteRenta"             => floatval(number_format($invoice->retencion_renta, 2, '.', '')),
                // "montoTotalOperacion"   => floatval(number_format($subTotal + $totalTributos, 2, '.', '')),
                "montoTotalOperacion"   => floatval(number_format($total_subtotal + $totalTributos, 2, '.', '')),
                "totalNoGravado"        => floatval(number_format(0.0, 2, '.', '')),
                // "totalPagar"            => floatval(number_format($subTotal + $totalTributos - $invoice->iva_retenido - $invoice->retencion_renta, 2, '.', '')),
                "totalPagar"            => floatval(number_format($total_totalPagar + $totalTributos - $invoice->retencion_renta, 2, '.', '')),
                "totalLetras"           => _lang('It is') . ' ' . dollarToText(floatval(number_format($total_totalPagar + $totalTributos - $invoice->retencion_renta, 2, '.', ''))) . ' USD',
                "saldoFavor"            => floatval(number_format(0.0, 2, '.', '')),
                "condicionOperacion"    => intval($invoice->conop_id),
                // "pagos"=> $invoice->conop_id == 1 || $invoice->conop_id == 3?['codigo'=>intval($invoice->forp_id), 'montoPago'=>intval($invoice->grand_total)] : null,
                "pagos"                 => null,
                "numPagoElectronico"    => null
            ],
            "extension" => null,
            "apendice" =>
            [
                [
                    "campo" => "sucursal",
                    "etiqueta" => "Sucursal",
                    "valor" => $company->company_name
                ],
                [
                    "campo" => "condicion_operacion",
                    "etiqueta" => "Condicion de la operacion",
                    // "valor"=> $invoice->condicion_operacion->conop_nombre
                    "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,

                ],
                // [
                //     "campo"=> "vendedor",
                //     "etiqueta"=> "Vendedor",
                //     "valor"=> "0000S60"
                // ],
                // [
                //     "campo"=> "codigo_cxc",
                //     "etiqueta"=> "Codigo CXC",
                //     "valor"=> "0"
                // ]
            ]
        ];
        // log::info(json_encode($dteJson));
        return $dteJson;
    } // PROBAR CCF

    // FE -> Factura electronica(consumidor final)
    private static function getDteJsonFE($invoice, $versionJson, $ambiente)
    {
        $company = Company::find($invoice->company_id);
        $details = [];
        $documentosRelacionados = null;

        $noSujetoSum = 0;
        $exentoSum = 0;
        $gravadoSum = 0;
        $descNoSujetoSum = 0;
        $descExentoSum = 0;
        $descGravadoSum = 0;
        $descGlobalExento = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado = 0;
        $ivaPercibido = 0;
        $rentaRetenida = 0;

        // caso FE
        $tributos = DB::table('invoice_item_taxes as iit')
            ->join('taxs as t', 'iit.tax_id', '=', 't.id')
            ->join('tributos as tribs', 't.trib_id', '=', 'tribs.trib_id')
            ->where('invoice_id', $invoice->id)
            ->select('tribs.trib_id as codigo', 'tribs.trib_nombre as descripcion', DB::raw('SUM(iit.amount) as valor'))
            ->groupBy('tribs.trib_id')
            ->get();

        $resultadosFormateados = [];
        foreach ($tributos as $tributo) {
            $resultadosFormateados[] = [
                'codigo' => $tributo->codigo,
                'descripcion' => $tributo->descripcion,
                'valor' => floatval(number_format($tributo->valor, 2, '.', '')),
            ];
        }

        foreach ($invoice->invoice_items as $key => $value) {
            $exento = 0;
            $noSujeto = 0;
            $gravado = 0;
            if ($invoice->exento_iva == 'si') {
                $exento = $value->sub_total;
                $exentoSum += $value->sub_total;
                $descExentoSum += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto = $value->sub_total;
                $noSujetoSum += $value->sub_total;
                $descNoSujetoSum += $value->discount;
            } else {
                $gravado = $value->sub_total;
                $gravadoSum += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            $taxIVA = Tax::where('trib_id', '20')->first();

            // Separar los componentes de la fecha
            $fechaComponents = explode('/', $invoice->invoice_date);

            // Construir la fecha en el formato 'yyyy-mm-dd'
            $fechaFormateada = $fechaComponents[2] . '-' . $fechaComponents[1] . '-' . $fechaComponents[0];

            // Crear un objeto DateTime con la fecha formateada
            $fechaDateTime = new DateTime($fechaFormateada);

            $CalculoIvaIem = (($gravado) / ($taxIVA->rate / 100 + 1)) * ($taxIVA->rate / 100);

            $totalTributos = 0;

            // Calcular el total de los valores de tributos
            // foreach ($tributos as $tributo) {
            //     $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
            // }

            // Calcular el subtotal
            $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

            array_push($details, [
                "numItem" => $value->line,
                "tipoItem" =>  ( $value->item_id > 0 ) ? intval($value->item->tipoitem_id) : 1,
                "numeroDocumento" => ( $value->cod_dte_rel != '') ? $value->cod_dte_rel : null,
                "codigo" => $value->item->product->product_code,
                "codTributo" => null,
                "descripcion" => $value->description,
                "cantidad" => intval($value->quantity),
                "uniMedida" => ( $value->item_id > 0 ) ? intval($value->item->product->unim_id) : 59,
                "precioUni" => floatval(number_format($value->unit_cost, 6, '.', '')),
                "montoDescu" => floatval(number_format($value->discount, 2, '.', '')),
                "ventaNoSuj" => floatval(number_format($noSujeto, 2, '.', '')),
                "ventaExenta" => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada" => floatval($gravado),
                "tributos" => /* Tax::whereIn('id', $value->taxes->pluck('tax_id')->toArray())
                    ->pluck('trib_id')->toArray() */ null,
                "psv" => 0.0,
                "noGravado" => 0.0,
                "ivaItem" => round($CalculoIvaIem, 2)
            ]);

            if( $value->cod_dte_rel != '' ){

                $documentosRelacionados = [];
                $documentosRelacionados = collect($documentosRelacionados);

                $documentos = [
                    'tipoDocumento'      => $value->type_dte_rel,
                    'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
                    'numeroDocumento'    => $value->cod_dte_rel,
                    'fechaEmision'       => $value->date_dte_rel,
                ];

                if( !$documentosRelacionados->contains('numeroDocumento', $value->cod_dte_rel) ){
                    $nuevoDocumento = [
                        'tipoDocumento'      => $value->type_dte_rel,
                        'tipoGeneracion'     => 2, // 1= físico, 2= electrónico
                        'numeroDocumento'    => $value->cod_dte_rel,
                        'fechaEmision'       => $value->date_dte_rel,
                    ];
                
                    $documentosRelacionados->push($nuevoDocumento);
                }
            }
        }
        $totalIva = 0.0;
        foreach ($details as $detail) {
            $totalIva += $detail['ivaItem'];
        }

        //Número de documento de receptor
        $documento = null;
        if( $invoice->num_documento != '' ){
            $documento = $invoice->num_documento;
            $documento = str_replace('-', '', $documento);
    
            if ($invoice->tdocrec_id == 13) {
                $parte1 = substr($documento, 0, 8);
                $parte2 = substr($documento, 8);
                $documento = $parte1 . '-' . $parte2;
            }
        }

        $direccion = null;
        if( $invoice->client->depa_id != '' ){
            $direccion = !isset($invoice->client->munidepa_id) ? null : [
                "departamento"  => $invoice->client->depa_id ?? '06',
                "municipio"     => Municipio::find($invoice->client->munidepa_id ?? 0)->muni_id ?? '14',
                "complemento"   => $invoice->complemento
            ];
        }

        $descuentos = $invoice->general_discount;

        $total_gravadas    = $gravadoSum;
        $total_subtotal    = $total_gravadas - $descuentos;
        $total_totalPagar  = $total_subtotal - $invoice->iva_retenido;

        if( $descuentos > 0 ){
         
            $calculo_iva            = $total_subtotal / 1.13;
            $total_totalIva_items   = $total_subtotal - $calculo_iva;
            $totalIva               = floatval($total_totalIva_items);
        }

        $dteJson = [
            "identificacion" => [
                "version" => intval($versionJson),
                "ambiente" => $ambiente,
                "tipoDte" => $invoice->tipodoc_id,
                "numeroControl" => $invoice->numero_control,
                "codigoGeneracion" => $invoice->codigo_generacion,
                "tipoModelo" => intval($invoice->modfact_id),
                "tipoOperacion" => intval($invoice->tipotrans_id),
                "tipoContingencia" => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
                "motivoContin" => $invoice->motivo_contingencia,
                "fecEmi" => $fechaDateTime->format('Y-m-d'),
                "horEmi" => (new DateTime($invoice->created_at))->format('H:i:s'),
                "tipoMoneda" => "USD"
            ],
            "documentoRelacionado" => $documentosRelacionados,
            "emisor" => [
                "nit" => str_replace('-', '', get_option('nit')),
                "nrc" => str_replace('-', '', get_option('nrc')),
                "nombre" => get_option('company_name'),
                "codActividad" => get_option('cod_actividad'),
                "descActividad" => get_option('desc_actividad'),
                "nombreComercial" => get_option('tradename'),
                "tipoEstablecimiento" => $company->tipoest_id,
                "direccion" => [
                    "departamento" => $company->depa_id,
                    "municipio" => Municipio::find($company->munidepa_id)->muni_id,
                    "complemento" => $company->address
                ],
                "telefono" => $company->cellphone,
                "correo" => $company->email,
                "codEstableMH" => null,
                "codEstable" => null,
                "codPuntoVentaMH" => null,
                "codPuntoVenta" => null
            ],
            "receptor" => [
                "tipoDocumento" => $invoice->tdocrec_id ?? null,
                // DUI debe ir con guion
                "numDocumento"  => $documento,
                "nrc"           => null,
                "nombre"        => $invoice->name_invoice ?? null,
                "codActividad"  => null,
                "descActividad" => $invoice->desc_actividad ?? null,
                "direccion"     => $direccion,
                "telefono"      => str_replace(['-', '+'], '', $invoice->telefono) ?? null,
                "correo"        => $invoice->correo ?? null
            ],
            "otrosDocumentos" => null,
            "ventaTercero" => null,
            "cuerpoDocumento" => $details,
            "resumen" => [
                "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
                "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
                "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
                "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
                "descuNoSuj"            => 0.0, // este campo es diferente al descuento por item no sujeto, aca debe ir un valor que en el 
                // formulario de creacion de invoice diga "Descuento global a ventas no sujetas"
                "descuExenta"           => 0.0,
                "descuGravada"          => floatval(number_format($descuentos, 2, '.', '')),
                "porcentajeDescuento"   => 0.0,
                // "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')), // uso informativo descuentos por item + descuentos globales por tipo de venta ej $descGlobalExento+$descGlobalNoSujeto
                "totalDescu"            => floatval(number_format($descuentos, 2, '.', '')), // uso informativo descuentos por item + descuentos globales por tipo de venta ej $descGlobalExento+$descGlobalNoSujeto
                // "tributos"=> $tributos,
                "tributos"              => null,
                // [ // colocar aca los mismo tributos de los items
                // {
                //     "codigo"=> "20",
                //     "descripcion"=> "Impuesto al Valor Agregado 13%",
                //     "valor"=> 0.59
                // }
                //],
                "subTotal"              => floatval(number_format($total_subtotal, 2, '.', '')), // menos $descGlobalNoSujeto, etc,
                // "ivaPerci1"=> $invoice->iva_percibido,
                "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
                "reteRenta"             => floatval(number_format($invoice->retencion_renta, 2, '.', '')),
                // "montoTotalOperacion" => floatval(number_format($invoice->grand_total, 2, '.', '')),
                "montoTotalOperacion"   => floatval(number_format($total_subtotal, 2, '.', '')),
                "totalNoGravado"        => floatval(number_format(0.0, 2, '.', '')),
                // "totalPagar"            => floatval(number_format($subTotal - floatval($invoice->iva_retenido) - floatval($invoice->retencion_renta), 2, '.', '')),
                "totalPagar"            => floatval(number_format($total_totalPagar-floatval($invoice->retencion_renta), 2, '.', '')),
                "totalLetras"           => _lang('It is') . ' ' . dollarToText(floatval(number_format($total_totalPagar-floatval($invoice->retencion_renta), 2, '.', ''))) . ' USD',
                "saldoFavor"            => 0.0,
                "condicionOperacion"    =>  intval($invoice->conop_id),
                // "pagos"=> $invoice->conop_id == 1 || $invoice->conop_id == 3?['codigo'=>$invoice->forp_id, 'montoPago'=>$invoice->grand_total] : null,
                // "numPagoElectronico"=> null,
                "pagos"                 => null,
                "totalIva"              => floatval(number_format($totalIva, 2, '.', '')),
                "numPagoElectronico"    => null
            ],
            "extension" => null,
            "apendice" =>
            [
                [
                    "campo" => "sucursal",
                    "etiqueta" => "Sucursal",
                    "valor" => $company->company_name
                ],
                [
                    "campo" => "condicion_operacion",
                    "etiqueta" => "Condicion de la operacion",
                    "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
                ],
                // [
                //     "campo"=> "vendedor",
                //     "etiqueta"=> "Vendedor",
                //     "valor"=> "0000S60"
                // ],
                // [
                //     "campo"=> "codigo_cxc",
                //     "etiqueta"=> "Codigo CXC",
                //     "valor"=> "0"
                // ]
            ]
        ];
        // log::info('DTE FE', json_encode($dteJson));
        return $dteJson;
    } // CONTINUAR EN RESUMEN

    private static function getDteJsonNotaDebitoCredito($tipoDte, $invoice, $versionJson, $ambiente)
    {

        $company = Company::find($invoice->company_id);
        $details = [];
        $documentosRelacionados = [];
        $documentosRelacionados = collect($documentosRelacionados);

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $totalTributos      = 0;

        $tributos = DB::select("SELECT tribs.trib_id as codigo, tribs.trib_nombre as descripcion, SUM(iit.amount) as valor
                                FROM invoice_item_taxes iit
                                join taxs t on iit.tax_id = t.id 
                                join tributos tribs on t.trib_id = tribs.trib_id 
                                where invoice_id = $invoice->id 
                                GROUP BY tribs.trib_id");

        $arrTributos = [];

        foreach ($tributos as $tributo) {

            $arrTributos[] = [
                'codigo'        => $tributo->codigo,
                'descripcion'   => $tributo->descripcion,
                'valor'         => floatval(number_format($tributo->valor, 2, '.', '')),
            ];

            $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $tipoDte,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContin"      => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nrc"                   => str_replace('-', '', get_option('nrc')),
            "nombre"                => get_option('company_name'),
            "codActividad"          => get_option('cod_actividad'),
            "descActividad"         => get_option('desc_actividad'),
            "nombreComercial"       => get_option('tradename'),
            "tipoEstablecimiento"   => $company->tipoest_id,
            "direccion" => [
                "departamento"  => $company->depa_id,
                "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                "complemento"   => $company->address
            ],
            "telefono"          => $company->cellphone,
            "correo"            => $company->email,
        ];

        $receptor = [
            "nit"               => str_replace('-', '', $invoice->client->nit),
            "nrc"               => str_replace('-', '', $invoice->client->nrc),
            "nombre"            => $invoice->name_invoice,
            "codActividad"      => $invoice->client->actie_id,
            "descActividad"     => $invoice->client->descActividad,
            "nombreComercial"   => $invoice->client->tradename,
            "direccion" => !isset($invoice->client->munidepa_id) ? null : [
                "departamento"  => $invoice->client->depa_id ?? '06',
                "municipio"     => Municipio::find($invoice->client->munidepa_id ?? 0)->muni_id ?? '23',
                "complemento"   => $invoice->client->address ?? ''
            ],
            "telefono"  => $invoice->client->contact_phone,
            "correo"    => $invoice->client->contact_email
        ];

        foreach ($invoice->invoice_items as $key => $value) {

            $exento         = 0;
            $noSujeto       = 0;
            $gravado        = 0;
            $tributos_item  = [];

            if ($invoice->exento_iva == 'si') {
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            } else {
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            //SE AGREGA TRIBUTO DE IVA
            array_push($tributos_item, "20");

            $data = [
                "numItem"           => $value->line,
                "tipoItem"          => ( $value->item_id > 0 ) ? intval($value->item->tipoitem_id) : 1,
                "numeroDocumento"   => $value->cod_dte_rel,
                "codigo"            => $value->item->product->product_code,
                "codTributo"        => null,
                "descripcion"       => $value->description,
                "cantidad"          => intval($value->quantity),
                "uniMedida"         => ( $value->item_id > 0 ) ? intval($value->item->product->unim_id) : 59,
                "precioUni"         => floatval(number_format($value->unit_cost, 6, '.', '')),
                "montoDescu"        => floatval(number_format($value->discount, 2, '.', '')),
                "ventaNoSuj"        => floatval(number_format($noSujeto, 2, '.', '')),
                "ventaExenta"       => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada"      => floatval(number_format($gravado, 2, '.', '')),
                "tributos"          => ( $invoice->exento_iva == 'no' ) ? $tributos_item : null,
            ];

            $documentos = [
                'tipoDocumento'      => $value->type_dte_rel,
                'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
                'numeroDocumento'    => $value->cod_dte_rel,
                'fechaEmision'       => $value->date_dte_rel,
            ];

            array_push($details, $data);
            // array_push($documentosRelacionados, $documentos);

            if( !$documentosRelacionados->contains('numeroDocumento', $value->cod_dte_rel) ){
                $nuevoDocumento = [
                    'tipoDocumento'      => $value->type_dte_rel,
                    'tipoGeneracion'     => 2, // 1= físico, 2= electrónico
                    'numeroDocumento'    => $value->cod_dte_rel,
                    'fechaEmision'       => $value->date_dte_rel,
                ];
            
                $documentosRelacionados->push($nuevoDocumento);
            }
        }

        $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

        $montoTotal = $invoice->grand_total;

        $descuentos = $invoice->general_discount;
        $descuentos = $descuentos;

        $total_gravadas    = $gravadoSum;
        $total_subtotal    = $total_gravadas - $descuentos;
        $total_totalPagar  = $total_subtotal - $invoice->iva_retenido;

        if( $descuentos > 0 ){

            $calculo_iva            = $total_subtotal * 1.13;
            $total_totalIva_items   = $calculo_iva - $total_subtotal;
            $totalIva               = floatval($total_totalIva_items);
            $totalTributos          = floatval($totalIva);

            foreach( $arrTributos as &$value ){
                if( $value['codigo'] == 20 ){
                    $value['valor'] = floatval(number_format($totalIva, 2, '.', ''));
                }
            }
            unset($value);
        }

        $resumen = [
            "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
            "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
            "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
            // "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
            "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $total_gravadas, 2, '.', '')),
            "descuNoSuj"            => 0.0,
            "descuExenta"           => 0.0,
            "descuGravada"          => floatval(number_format($descuentos, 2, '.', '')),
            // "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
            "totalDescu"            => floatval(number_format($descuentos, 2, '.', '')),
            "tributos"              => $arrTributos,
            // "subTotal"              => floatval(number_format($subTotal, 2, '.', '')),
            "subTotal"              => floatval(number_format($total_subtotal, 2, '.', '')),
            "ivaPerci1"             => 0,
            "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
            "reteRenta"             => floatval(number_format($invoice->retencion_renta, 2, '.', '')),
            "montoTotalOperacion"   => floatval(number_format($montoTotal, 2, '.', '')),
            "totalLetras"           => _lang('It is') . ' ' . dollarToText($montoTotal) . ' USD',
            "condicionOperacion"    =>  intval($invoice->conop_id),
        ];

        if ($tipoDte == 06) {
            $resumen['numPagoElectronico'] = null;
        }

        $extension = [
            'nombEntrega'     => get_option('company_name'),
            'docuEntrega'     => str_replace('-', '', get_option('nit')),
            'nombRecibe'      => $invoice->name_invoice,
            'docuRecibe'      => str_replace('-', '', $invoice->client->nit),
            'observaciones'   => $invoice->note
        ];

        $apendice = [
            [
                "campo" => "sucursal",
                "etiqueta" => "Sucursal",
                "valor" => $company->company_name
            ],
            [
                "campo" => "condicion_operacion",
                "etiqueta" => "Condicion de la operacion",
                "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
            ],
        ];

        $dteJson = [
            "identificacion"        => $identificacion,
            "documentoRelacionado"  => $documentosRelacionados,
            "emisor"                => $emisor,
            "receptor"              => $receptor,
            "ventaTercero"          => null,
            "cuerpoDocumento"       => $details,
            "resumen"               => $resumen,
            "extension"             => $extension,
            "apendice"              => $apendice
        ];

        return $dteJson;
    }

    private static function getDteJsonFEX($invoice, $versionJson, $ambiente)
    {
        $company = Company::find($invoice->company_id);
        $details = [];

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $ivaPercibido       = 0;
        $rentaRetenida      = 0;
        $descuento          = 0;

        // Caso FEXE (Factura de exportación electronica)
        $tributos = DB::table('invoice_item_taxes as iit')
            ->join('taxs as t', 'iit.tax_id', '=', 't.id')
            ->join('tributos as tribs', 't.trib_id', '=', 'tribs.trib_id')
            ->join('invoice_items as ii', 'iit.invoice_id', '=', 'ii.invoice_id')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->join('recinto_fiscal as rf', 'inv.refisc_id', '=', 'rf.refisc_id')
            ->join('regimen as reg', 'inv.regi_id', '=', 'reg.regi_id')
            ->where('iit.invoice_id', '=', $invoice->id)
            ->groupBy('tribs.trib_id')
            ->select('tribs.trib_id as codigo', 'tribs.trib_nombre as descripcion', DB::raw('SUM(iit.amount) as valor'))
            ->get();


        foreach( $invoice->invoice_items as $key => $value ){

            $exento     = 0;
            $noSujeto   = 0;
            $gravado    = 0;

            if( $invoice->exento_iva == 'si' ){
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            }
            else if( $invoice->nosujeto_iva == 'si' ){
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            }
            else{
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            array_push($details, [
                "numItem"       => $value->line,
                "cantidad"      => intval($value->quantity),
                "codigo"        => $value->item->product->product_code,
                "uniMedida"     => ( $value->item_id > 0 ) ? intval($value->item->product->unim_id) : 59,
                "descripcion"   => $value->description,
                "precioUni"     => floatval(number_format($value->unit_cost, 6, '.', '')),
                "montoDescu"    => floatval(number_format($value->discount, 2, '.', '')),
                "ventaGravada"  => floatval(number_format($gravado, 2, '.', '')),
                "tributos"      => Tax::whereIn('id', $value->taxes->pluck('tax_id')->toArray())
                                        ->pluck('trib_id')->toArray(),
                "noGravado"     => 0.0
            ]);
        }

        //Número de documento de receptor
        $documento = $invoice->num_documento;
        $documento = str_replace('-', '', $documento);

        if ($invoice->tdocrec_id == 13) {
            $parte1 = substr($documento, 0, 8);
            $parte2 = substr($documento, 8);
            $documento = $parte1 . '-' . $parte2;
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $invoice->tipodoc_id,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContigencia" => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $descuentos = $invoice->general_discount;

        $total_gravadas    = $gravadoSum;
        $total_subtotal    = $total_gravadas - $descuentos;
        $total_totalPagar  = $total_subtotal;

        $dteJson = [
            "identificacion" => $identificacion,
            "emisor" => [
                "nit"                   => str_replace('-', '', get_option('nit')),
                "nrc"                   => str_replace('-', '', get_option('nrc')),
                "nombre"                => get_option('company_name'),
                "codActividad"          => get_option('cod_actividad'),
                "descActividad"         => get_option('desc_actividad'),
                "nombreComercial"       => get_option('tradename'),
                "tipoEstablecimiento"   => $company->tipoest_id,
                "direccion" => [
                    "departamento"  => $company->depa_id,
                    "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                    "complemento"   => $company->address
                ],
                "telefono"          => $company->cellphone,
                "correo"            => $company->email,
                "codEstableMH"      => null,
                "codEstable"        => null,
                "codPuntoVentaMH"   => null,
                "codPuntoVenta"     => null,
                //TIPO DE ITEM  1 = BIENES; 2 = SERVICIOS; 3 = AMBOS
                "tipoItemExpor"     => 3,
                "recintoFiscal"     => ( isset($tributos->refisc_id) && $tributos->refisc_id != '' ) ? $tributos->refisc_id : null,
                "regimen"           => ( isset($tributos->regi_id) && $tributos->regi_id != '' ) ? $tributos->regi_id : null,
            ],
            "receptor" => [
                "nombre"            => $invoice->name_invoice,
                "tipoDocumento"     => $invoice->tdocrec_id,
                "numDocumento"      => $documento,
                "nombreComercial"   => $invoice->client->tradename,
                "codPais"           => $invoice->client->pais_id,
                "nombrePais"        => $invoice->client->pais->pais_nombre,
                "complemento"       => $invoice->client->address.', '.($invoice->client->municipio->muni_nombre ?? '').', '.($invoice->client->departamento->depa_nombre ?? ''),
                "tipoPersona"       => intval($invoice->client->tpers_id),
                "descActividad"     => $invoice->client->descActividad,
                "telefono"          => $invoice->telefono,
                "correo"            => $invoice->correo
            ],
            "otrosDocumentos"   => null,
            "ventaTercero"      => null,
            "cuerpoDocumento"   => $details,
            "resumen" => [
                "totalGravada"              => floatval(number_format($gravadoSum, 2, '.', '')),
                // "descuento"                 => floatval(number_format($descuento, 2, '.', '')),
                "descuento"                 => floatval(number_format($descuentos, 2, '.', '')),
                "porcentajeDescuento"       => 0.0,
                // "totalDescu"                => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
                "totalDescu"                => floatval(number_format($descuentos, 2, '.', '')),
                "seguro"                    => 0,
                "flete"                     => 0,
                // "montoTotalOperacion"       => floatval(number_format($invoice->grand_total, 2, '.', '')),
                "montoTotalOperacion"       => floatval(number_format($total_totalPagar, 2, '.', '')),
                "totalNoGravado"            => 0,
                "totalPagar"                => floatval(number_format($invoice->grand_total, 2, '.', '')),
                "totalLetras"               => _lang('It is') . ' ' . dollarToText($invoice->grand_total) . ' USD',
                "condicionOperacion"        => intval($invoice->conop_id),
                "pagos" => [
                    [
                        "codigo"        => $invoice->forp_id,
                        "montoPago"     => floatval(number_format($invoice->grand_total, 2, '.', '')),
                        "referencia"    => null,
                        "plazo"         => null,
                        "periodo"       => null
                    ]
                ],
                "codIncoterms"          => ( $invoice->id_incoterms != '' ) ? $invoice->id_incoterms : null,
                "descIncoterms"         => ( $invoice->id_incoterms != '' ) ? $invoice->incoterm->nombre_incoterms : null,
                "numPagoElectronico"    => null,
                "observaciones"         => $invoice->note
            ],
            "apendice" => null
        ];

        log::info(json_encode($dteJson));
        return $dteJson;
    }

    private static function getDteJsonNotaRemision($invoice, $versionJson, $ambiente)
    {

        $company = Company::find($invoice->company_id);
        $details = [];
        $documentosRelacionados = [];
        $documentosRelacionados = collect($documentosRelacionados);

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $totalTributos      = 0;

        $tributos = DB::select("SELECT tribs.trib_id as codigo, tribs.trib_nombre as descripcion, SUM(iit.amount) as valor
                                FROM invoice_item_taxes iit
                                join taxs t on iit.tax_id = t.id 
                                join tributos tribs on t.trib_id = tribs.trib_id 
                                where invoice_id = $invoice->id 
                                GROUP BY tribs.trib_id");

        $arrTributos = [];

        foreach ($tributos as $tributo) {

            $arrTributos[] = [
                'codigo'        => $tributo->codigo,
                'descripcion'   => $tributo->descripcion,
                'valor'         => floatval(number_format($tributo->valor, 2, '.', '')),
            ];

            $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $invoice->tipodoc_id,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContin"      => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nrc"                   => str_replace('-', '', get_option('nrc')),
            "nombre"                => get_option('company_name'),
            "codActividad"          => get_option('cod_actividad'),
            "descActividad"         => get_option('desc_actividad'),
            "nombreComercial"       => get_option('tradename'),
            "tipoEstablecimiento"   => $company->tipoest_id,
            "direccion" => [
                "departamento"  => $company->depa_id,
                "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                "complemento"   => $company->address
            ],
            "telefono"          => $company->cellphone,
            "correo"            => $company->email,
            "codEstableMH"      => null,
            "codEstable"        => null,
            "codPuntoVentaMH"   => null,
            "codPuntoVenta"     => null
        ];

        //Número de documento de receptor
        $documento = $invoice->num_documento;
        $documento = str_replace('-', '', $documento);

        if ($invoice->tdocrec_id == 13) {
            $parte1 = substr($documento, 0, 8);
            $parte2 = substr($documento, 8);
            $documento = $parte1 . '-' . $parte2;
        }

        $receptor = [
            "tipoDocumento"     => $invoice->tdocrec_id,
            "numDocumento"      => $documento,
            "nrc"               => str_replace('-', '', $invoice->client->nrc),
            "nombre"            => $invoice->name_invoice,
            "codActividad"      => $invoice->client->actie_id,
            "descActividad"     => $invoice->client->descActividad,
            "nombreComercial"   => $invoice->client->tradename,
            "direccion" => !isset($invoice->client->munidepa_id) ? null : [
                "departamento"  => $invoice->client->depa_id ?? '06',
                "municipio"     => Municipio::find($invoice->client->munidepa_id ?? 0)->muni_id ?? '23',
                "complemento"   => $invoice->client->address ?? ''
            ],
            "telefono"          => $invoice->telefono,
            "correo"            => $invoice->correo,
            "bienTitulo"        => 'Sr'
        ];

        foreach ($invoice->invoice_items as $key => $value) {

            $exento         = 0;
            $noSujeto       = 0;
            $gravado        = 0;
            $tributos_item  = [];

            if ($invoice->exento_iva == 'si') {
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            } else {
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            //SE AGREGA TRIBUTO DE IVA
            array_push($tributos_item, "20");

            $data = [
                "numItem"           => $value->line,
                "tipoItem"          => ( $value->item_id > 0 ) ? intval($value->item->tipoitem_id) : 1,
                "numeroDocumento"   => null,
                "codigo"            => $value->item->product->product_code,
                "codTributo"        => null,
                "descripcion"       => $value->description,
                "cantidad"          => intval($value->quantity),
                "uniMedida"         => ( $value->item_id > 0 ) ? intval($value->item->product->unim_id) : 59,
                "precioUni"         => floatval(number_format($value->unit_cost, 6, '.', '')),
                "montoDescu"        => floatval(number_format($value->discount, 2, '.', '')),
                "ventaNoSuj"        => floatval(number_format($noSujeto, 2, '.', '')),
                "ventaExenta"       => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada"      => floatval(number_format($gravado, 2, '.', '')),
                "tributos"          => ( $invoice->exento_iva == 'no' ) ? $tributos_item : null,
            ];

            // $documentos = [
            //     'tipoDocumento'      => $value->type_dte_rel,
            //     'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
            //     'numeroDocumento'    => $value->cod_dte_rel,
            //     'fechaEmision'       => $value->date_dte_rel,
            // ];

            array_push($details, $data);
        }

        $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

        $montoTotal = $invoice->grand_total;

        $descuentos = $invoice->general_discount;
        $descuentos = $descuentos;

        $total_gravadas    = $gravadoSum;
        $total_subtotal    = $total_gravadas - $descuentos;
        $total_totalPagar  = $total_subtotal - $invoice->iva_retenido;

        if( $descuentos > 0 ){

            // $calculo_iva            = $total_subtotal * 1.13;
            // $total_totalIva_items   = $calculo_iva - $total_subtotal;
            // $totalIva               = floatval($total_totalIva_items);
            // $totalTributos          = floatval($totalIva);

            // foreach( $arrTributos as &$value ){
            //     if( $value['codigo'] == 20 ){
            //         $value['valor'] = floatval(number_format($totalIva, 2, '.', ''));
            //     }
            // }
            // unset($value);
            

            // $calculo_iva            = $total_subtotal * 1.13;
            // $total_totalIva_items   = $calculo_iva - $total_subtotal;
            $totalIva               = floatval($invoice->tax_total);
            $totalTributos          = floatval($invoice->tax_total);

            foreach( $arrTributos as &$value ){
                if( $value['codigo'] == 20 ){
                    $value['valor'] = floatval(number_format($totalIva, 2, '.', ''));
                }
            }
            unset($value);
        }

        $resumen = [
            "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
            "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
            "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
            "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
            "descuNoSuj"            => 0.0,
            "descuExenta"           => 0.0,
            "descuGravada"          => floatval(number_format($descuentos, 2, '.', '')),
            "porcentajeDescuento"   => 0.0,
            // "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
            "totalDescu"            => floatval(number_format($descuentos, 2, '.', '')),
            "tributos"              => $arrTributos,
            // "subTotal"              => floatval(number_format($subTotal, 2, '.', '')),
            "subTotal"              => floatval(number_format($total_subtotal, 2, '.', '')),
            "montoTotalOperacion"   => floatval(number_format($montoTotal, 2, '.', '')),
            "totalLetras"           => _lang('It is') . ' ' . dollarToText($montoTotal) . ' USD',
        ];

        $extension = [
            'nombEntrega'     => get_option('company_name'),
            'docuEntrega'     => str_replace('-', '', get_option('nit')),
            'nombRecibe'      => $invoice->name_invoice,
            'docuRecibe'      => str_replace('-', '', $invoice->client->nit),
            'observaciones'   => $invoice->note
        ];

        $apendice = [
            [
                "campo" => "sucursal",
                "etiqueta" => "Sucursal",
                "valor" => $company->company_name
            ],
            [
                "campo" => "condicion_operacion",
                "etiqueta" => "Condicion de la operacion",
                "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
            ],
        ];

        $dteJson = [
            "identificacion"        => $identificacion,
            "documentoRelacionado"  => null,
            "emisor"                => $emisor,
            "receptor"              => $receptor,
            "ventaTercero"          => null,
            "cuerpoDocumento"       => $details,
            "resumen"               => $resumen,
            "extension"             => $extension,
            "apendice"              => $apendice
        ];

        return $dteJson;
    }

    private static function getDteJsonSujetoExcluido($invoice, $versionJson, $ambiente)
    {

        $company = Company::find($invoice->company_id);
        $details = [];
        $documentosRelacionados = [];
        $documentosRelacionados = collect($documentosRelacionados);

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $totalTributos      = 0;

        $tributos = DB::select("SELECT tribs.trib_id as codigo, tribs.trib_nombre as descripcion, SUM(iit.amount) as valor
                                FROM invoice_item_taxes iit
                                join taxs t on iit.tax_id = t.id 
                                join tributos tribs on t.trib_id = tribs.trib_id 
                                where invoice_id = $invoice->id 
                                GROUP BY tribs.trib_id");

        $arrTributos = [];

        foreach ($tributos as $tributo) {

            $arrTributos[] = [
                'codigo'        => $tributo->codigo,
                'descripcion'   => $tributo->descripcion,
                'valor'         => floatval(number_format($tributo->valor, 2, '.', '')),
            ];

            $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $invoice->tipodoc_id,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContin"      => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nrc"                   => str_replace('-', '', get_option('nrc')),
            "nombre"                => get_option('company_name'),
            "codActividad"          => get_option('cod_actividad'),
            "descActividad"         => get_option('desc_actividad'),
            "direccion" => [
                "departamento"  => $company->depa_id,
                "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                "complemento"   => $company->address
            ],
            "telefono"          => $company->cellphone,
            "codEstableMH"      => null,
            "codEstable"        => null,
            "codPuntoVentaMH"   => null,
            "codPuntoVenta"     => null,
            "correo"            => $company->email,
        ];

        //Número de documento de receptor
        $documento = $invoice->num_documento;
        $documento = str_replace('-', '', $documento);

        // if ($invoice->tdocrec_id == 13) {
        //     $parte1 = substr($documento, 0, 8);
        //     $parte2 = substr($documento, 8);
        //     $documento = $parte1 . '-' . $parte2;
        // }

        $sujetoExcluido = [
            "tipoDocumento"     => $invoice->tdocrec_id,
            "numDocumento"      => $documento,
            "nombre"            => $invoice->name_invoice,
            "codActividad"      => $invoice->client->actie_id,
            "descActividad"     => $invoice->client->descActividad,
            "direccion" => !isset($invoice->client->munidepa_id) ? null : [
                "departamento"  => $invoice->client->depa_id ?? '06',
                "municipio"     => Municipio::find($invoice->client->munidepa_id ?? 0)->muni_id ?? '23',
                "complemento"   => $invoice->client->address ?? ''
            ],
            "telefono"          => $invoice->telefono,
            "correo"            => $invoice->correo,
        ];

        foreach ($invoice->invoice_items as $key => $value) {

            $exento         = 0;
            $noSujeto       = 0;
            $gravado        = 0;
            $tributos_item  = [];

            if ($invoice->exento_iva == 'si') {
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            } else {
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            //SE AGREGA TRIBUTO DE IVA
            array_push($tributos_item, "20");

            $data = [
                "numItem"       => $value->line,
                "tipoItem"      => ( $value->item_id > 0 ) ? intval($value->item->tipoitem_id) : 1,
                "cantidad"      => intval($value->quantity),
                "codigo"        => $value->item->product->product_code,
                "uniMedida"     => ( $value->item_id > 0 ) ? intval($value->item->product->unim_id) : 59,
                "descripcion"   => $value->description,
                "precioUni"     => floatval(number_format($value->unit_cost, 6, '.', '')),
                "montoDescu"    => floatval(number_format($value->discount, 2, '.', '')),
                "compra"        => floatval(number_format($gravado, 2, '.', '')),
            ];

            array_push($details, $data);
        }

        $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

        $montoTotal = $invoice->grand_total;

        $descuentos = $invoice->general_discount;

        $total_gravadas    = $gravadoSum;
        $total_subtotal    = $total_gravadas - $descuentos;
        $total_totalPagar  = $total_subtotal;

        $resumen = [
            "totalCompra"           => floatval(number_format($montoTotal + $invoice->retencion_renta, 2, '.', '')),
            // "descu"                 => 0.0,
            "descu"                 => floatval(number_format($descuentos, 2, '.', '')),
            // "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
            "totalDescu"            => floatval(number_format($descuentos, 2, '.', '')),
            // "subTotal"              => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
            "subTotal"              => floatval(number_format($total_subtotal, 2, '.', '')),
            "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
            "reteRenta"             => floatval(number_format($invoice->retencion_renta, 2, '.', '')),
            "totalPagar"            => floatval(number_format($montoTotal, 2, '.', '')),
            "totalLetras"           => _lang('It is') . ' ' . dollarToText($montoTotal) . ' USD',
            "condicionOperacion"    =>  intval($invoice->conop_id),
            "pagos" => [
                [
                    "codigo"        => $invoice->forp_id,
                    "montoPago"     => floatval(number_format($montoTotal, 2, '.', '')),
                    "referencia"    => null,
                    "plazo"         => null,
                    "periodo"       => null
                ]
            ],
            "observaciones"         => $invoice->note
        ];

        $apendice = [
            [
                "campo" => "sucursal",
                "etiqueta" => "Sucursal",
                "valor" => $company->company_name
            ],
            [
                "campo" => "condicion_operacion",
                "etiqueta" => "Condicion de la operacion",
                "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
            ],
        ];

        $dteJson = [
            "identificacion"        => $identificacion,
            "emisor"                => $emisor,
            "sujetoExcluido"        => $sujetoExcluido,
            "cuerpoDocumento"       => $details,
            "resumen"               => $resumen,
            "apendice"              => $apendice
        ];

        return $dteJson;
    }

    public static function get_no_anexo($invoice_date)
    {

        $data               = array();
        if ((new DateTime($invoice_date))->format('Y') != date('Y')) {
            $noAnexo      = '001';
        } else {
            $noAnexo      = str_pad(intval(get_option('no_anexo_desc_starting', 0)), 3, '0', STR_PAD_LEFT);
        }

        $data['value'] = intval($noAnexo) + 1;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (\App\Setting::where('name', "no_anexo_desc_starting")->exists()) {
            \App\Setting::where('name', 'no_anexo_desc_starting')->update($data);
        } else {
            $data['name']       = 'no_anexo_desc_starting';
            $data['created_at'] = date('Y-m-d H:i:s');
            \App\Setting::insert($data);
        }

        return $noAnexo . '-' . (new DateTime($invoice_date))->format('Y');
    }

    public function sendMessage()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('API_WHATSAPP_TOKEN')
            ])->post(env('API_WHATSAPP'), [
                'messaging_product' => 'whatsapp',
                'text' => 'template',
                'to' => '+50361677017', // Número de teléfono de destino
                'text' => ['body' => 'Hola, este es un mensaje de prueba desde mi aplicación']
            ]);

            // Verificar la respuesta y devolverla
            return $response->json();
        } catch (\Exception $e) {
            // Manejo de errores
            \Log::error('Error al enviar mensaje de WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Hubo un problema al enviar el mensaje de WhatsApp'], 500);
        }
    }


    public function sendEmailFactura($id, $anulacion = false, $reenvio = false)
    {
        try {

            $anulacion  = filter_var($anulacion, FILTER_VALIDATE_BOOLEAN);
            $reenvio    = filter_var($reenvio, FILTER_VALIDATE_BOOLEAN);


            $invoice = Invoice::find($id);

            // Generar el PDF utilizando la función separada
            $pdf = $this->downloadPdf($id);

            Log::info('Se genera el PDF temporal: ' . $pdf);

            $jsonFilePath = $this->downloadJson($id);

            Log::info('Se genera el JSON temporal: ' . $jsonFilePath);

            // Preparar el contenido del correo electrónico
            $subject = 'Factura Electrónica';

            if( $anulacion ){
                $subject = 'Anulación de Factura Electrónica';
            }

            if( $reenvio ){
                $client = Contact::find($invoice->client_id);
                $invoice->correo = $client->contact_email;
                $invoice->save();
            }

            $content = [
                'subject' => $subject,
                'body' => 'Estimado cliente: ' . $invoice->name_invoice,
            ];

            // Enviar el correo electrónico con el archivo adjunto
            $mail = Mail::to($invoice->correo)->send(new MailMailable($content, $jsonFilePath, $pdf, $id, $anulacion, $invoice->numero_control));

            if( isset($invoice->correo_alterno) && $invoice->correo_alterno != '' ){
                $mail2 = Mail::to($invoice->correo_alterno)->send(new MailMailable($content, $jsonFilePath, $pdf, $id, $anulacion, $invoice->numero_control));
            }

            try {
                Storage::delete('pdf_invoices/' . $pdf);
                Log::info('Se elimina el PDF temporal: ' . $pdf);
            } catch (\Exception $e) {
                Log::info('Error al eliminar el PDF temporal: ' . $pdf);
            }

            Log::info('Envio por correo de DTE con ID: ' . $id);

            Log::info('Correos a los que se envio el DTE: ' . $invoice->correo. ' '. $invoice->correo_alterno);

            $mail = $mail.($mail2 ?? '');

            return $mail;
        } catch (\Exception $e) {
            // Manejo de errores si el correo electrónico no se pudo enviar
            \Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
        }
    }


    public function downloadJson($id_invoice)
    {


        $invoice = Invoice::find($id_invoice);

        $json = json_decode(json_decode($invoice->json_dte));
        $json->identificacion->selloRecibido = $invoice->sello_recepcion;
        $json = json_encode($json);

        $json_temp = 'invoice_' . $invoice->id . '.json';

        if (Storage::exists('json_invoices' . $json_temp)) {
            Storage::delete($json_temp);
        }

        Storage::put('json_invoices/' . $json_temp, $json);

        $json_path = storage_path('app/json_invoices/' . $json_temp);

        try {

            // Verificar si el archivo existe
            if (file_exists($json_path)) {
                // Devolver el archivo para su descarga

                if (request()->has('download')) {
                    return response()->download($json_path, 'invoice.json', [], 'inline');
                } else {
                    return $json_temp;
                }
            } else {
                // Manejar el caso en que el archivo no exista
                abort(404);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error al generar y almacenar el JSON: ' . $e->getMessage());
            Log::error('Error al generar y almacenar el JSON: ' . $e->getMessage());
        }
    }

    public function downloadPdf($id)
    {

        $invoice       = Invoice::leftJoin('tipo_transmision', 'invoices.tipotrans_id', '=', 'tipo_transmision.tipotrans_id')
            ->leftJoin('modelo_facturacion', 'invoices.modfact_id', '=', 'modelo_facturacion.modfact_id')
            ->leftJoin('recinto_fiscal as rf', 'invoices.refisc_id', '=', 'rf.refisc_id')
            ->leftJoin('regimen as reg', 'invoices.regi_id', '=', 'reg.regi_id')
            ->leftJoin('condicion_operacion as co', 'invoices.conop_id', '=', 'co.conop_id')
            ->select(
                'invoices.*',
                DB::raw('SUBSTRING_INDEX(tipo_transmision.tipotrans_nombre, " ", -1) as transmision'),
                DB::raw('SUBSTRING_INDEX(modelo_facturacion.modfact_nombre, " ", -1) AS modelo_facturacion'),
                'rf.refisc_nombre AS nombre_recinto',
                'reg.regi_nombre AS nombre_regimen',
                'co.conop_nombre AS condicion_op',
            )
            ->find($id);

        $invoice_taxes = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();
        $transactions = Transaction::where("invoice_id", $id)->get();
        $url = generateUrl($invoice);

        $codigoQR = QrCode::size(100)->generate($url);

        $pdfView = '';
        // Lógica para determinar la vista del PDF basada en el tipo de documento
        switch ($invoice->tipodoc_id) {
            case '01': // FE
                $pdfView = 'backend.accounting.invoice.fe.facturaFe';
                break;
            case '03': // CCFE
                $pdfView = 'backend.accounting.invoice.ccf.facturaCCF';
                break;
            case '04': // NR
                $pdfView = 'backend.accounting.invoice.nr.facturaNR';
                break;
            case '05': // NC
                $pdfView = 'backend.accounting.invoice.nc.pdf_export';
                break;
            case '06': // ND
                $pdfView = 'backend.accounting.invoice.nc.pdf_export';
                break;
            case '11': // FEXE
                $pdfView = 'backend.accounting.invoice.fex.facturaEX';
                break;
            case '14': // FSE
                $pdfView = 'backend.accounting.invoice.fse.facturaSE';
                break;
            default:
                // Definir una vista predeterminada si no se encuentra el tipo de documento
                $pdfView = 'backend.accounting.invoice.default.pdf';
                break;
        }

        // Generar el contenido del PDF
        $pdf = PDF::loadView($pdfView, compact('invoice', 'invoice_taxes', 'transactions', 'url', 'codigoQR'))
            ->setPaper('letter', 'portrait');


        // Almacenar el PDF en el directorio de almacenamiento con un nombre único
        $pdf_temp = 'invoice_' . $invoice->id . '.pdf';

        if (Storage::exists('pdf_invoices' . $pdf_temp)) {
            Storage::delete($pdf_temp);
        }

        Storage::put('pdf_invoices/' . $pdf_temp, $pdf->output());

        $pdf_path = storage_path('app/pdf_invoices/' . $pdf_temp);

        try {

            if (request()->has('download')) {
                return $pdf->download('invoice_' . $invoice->numero_control . '.pdf');
            } else {
                $pdf->save($pdf_path);

                return $pdf_temp;
            }
        } catch (\Exception $e) {
            throw new \Exception('Error al generar y almacenar el PDF: ' . $e->getMessage());
            Log::error('Error al generar y almacenar el PDF: ' . $e->getMessage());
        }
    }


    public function enviarPruebas($tipoDte)
    {

        $json               = [];
        $informacion_dte    = TipoDocumento::where('tipodoc_id', '=', $tipoDte)->first();
        $company            = Company::find(1);
        $ambiente           = env('API_AMBIENTE_MH');

        $user = User::find(7);
        Auth::login($user);

        //FACTURA
        if ($tipoDte == '01') {

            $json = [
                "identificacion" => [
                    "version"           => intval($informacion_dte->version_json),
                    "ambiente"          => $ambiente,
                    "tipoDte"           => $informacion_dte->tipodoc_id,
                    "numeroControl"     => generateNumeroControl($informacion_dte->tipodoc_id),
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "tipoModelo"        => 1,
                    "tipoOperacion"     => 1,
                    "tipoContingencia"  => null,
                    "motivoContin"      => null,
                    "fecEmi"            => Carbon::now()->format('Y-m-d'),
                    "horEmi"            => Carbon::now()->format('H:i:s'),
                    "tipoMoneda"        => "USD"
                ],
                "documentoRelacionado"  => null,
                "emisor" => [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nrc"                   => str_replace('-', '', get_option('nrc')),
                    "nombre"                => get_option('company_name'),
                    "codActividad"          => get_option('cod_actividad'),
                    "descActividad"         => get_option('desc_actividad'),
                    "nombreComercial"       => get_option('tradename'),
                    "tipoEstablecimiento"   => $company->tipoest_id,
                    "direccion" => [
                        "departamento"  => $company->depa_id,
                        "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                        "complemento"   => $company->address
                    ],
                    "telefono"          => $company->cellphone,
                    "correo"            => $company->email,
                    "codEstableMH"      => null,
                    "codEstable"        => null,
                    "codPuntoVentaMH"   => null,
                    "codPuntoVenta"     => null
                ],
                "receptor" => [
                    "tipoDocumento" => "36",
                    "numDocumento"  =>  "06140301171038",
                    "nrc"           => null,
                    "nombre"        => "Tec101 S.A. de C.V.",
                    "codActividad"  => null,
                    "descActividad" => null,
                    "direccion" => [
                        "departamento"  => "01",
                        "municipio"     => "01",
                        "complemento"   => "39 Av. Norte, Urb. Universitaria Norte #925urbanizacion universitaria norte"
                    ],
                    "telefono"  => "61677017",
                    "correo"    => "cviscarra@masconazo.com"
                ],
                "otrosDocumentos"   => null,
                "ventaTercero"      => null,
                "cuerpoDocumento"   => [
                    [
                        "numItem"           => 1,
                        "tipoItem"          => 1,
                        "numeroDocumento"   => null,
                        "codigo"            => "68546351",
                        "codTributo"        => null,
                        "descripcion"       => "Alarmas Eagle con Bluetooth - Alarmas Eagle con Bluetooth | original",
                        "cantidad"          => 1,
                        "uniMedida"         => 59,
                        "precioUni"         => 73.45,
                        "montoDescu"        => 0,
                        "ventaNoSuj"        => 0,
                        "ventaExenta"       => 0,
                        "ventaGravada"      => 73.45,
                        "tributos"          => null,
                        "psv"               => 0,
                        "noGravado"         => 0,
                        "ivaItem"           => 8.45
                    ]
                ],
                "resumen" => [
                    "totalNoSuj"            => 0,
                    "totalExenta"           => 0,
                    "totalGravada"          => 73.45,
                    "subTotalVentas"        => 73.45,
                    "descuNoSuj"            => 0,
                    "descuExenta"           => 0,
                    "descuGravada"          => 0,
                    "porcentajeDescuento"   => 0,
                    "totalDescu"            => 0,
                    "tributos"              => null,
                    "subTotal"              => 73.45,
                    "ivaRete1"              => 0,
                    "reteRenta"             => 0,
                    "montoTotalOperacion"   => 73.45,
                    "totalNoGravado"        => 0.0,
                    "totalPagar"            => 73.45,
                    "totalLetras"           => "Son SETENTA Y TRES CON 45/100 USD",
                    "saldoFavor"            => 0,
                    "condicionOperacion"    => 1,
                    "pagos"                 => null,
                    "totalIva"              => 8.45,
                    "numPagoElectronico"    => null
                ],
                "extension" => null,
                "apendice" => [
                    [
                        "campo"     => "sucursal",
                        "etiqueta"  => "Sucursal",
                        "valor"     => $company->company_name
                    ],
                    [
                        "campo"     => "condicion_operacion",
                        "etiqueta"  => "Condicion de la operacion",
                        "valor"     => "Contado",
                    ],
                ]
            ];
        }
        else if ($tipoDte == '03') {

            $json = [
                "identificacion" => [
                    "version"           => intval($informacion_dte->version_json),
                    "ambiente"          => $ambiente,
                    "tipoDte"           => $informacion_dte->tipodoc_id,
                    "numeroControl"     => generateNumeroControl($informacion_dte->tipodoc_id),
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "tipoModelo"        => 1,
                    "tipoOperacion"     => 1,
                    "tipoContingencia"  => null,
                    "motivoContin"      => null,
                    "fecEmi"            => Carbon::now()->format('Y-m-d'),
                    "horEmi"            => Carbon::now()->format('H:i:s'),
                    "tipoMoneda"        => "USD"
                ],
                "documentoRelacionado"  => null,
                "emisor" => [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nrc"                   => str_replace('-', '', get_option('nrc')),
                    "nombre"                => get_option('company_name'),
                    "codActividad"          => get_option('cod_actividad'),
                    "descActividad"         => get_option('desc_actividad'),
                    "nombreComercial"       => get_option('tradename'),
                    "tipoEstablecimiento"   => $company->tipoest_id,
                    "direccion" => [
                        "departamento"  => $company->depa_id,
                        "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                        "complemento"   => $company->address
                    ],
                    "telefono"          => $company->cellphone,
                    "correo"            => $company->email,
                    "codEstableMH"      => null,
                    "codEstable"        => null,
                    "codPuntoVentaMH"   => null,
                    "codPuntoVenta"     => null
                ],
                "receptor" => [
                    "nit"               =>  "06140301171038",
                    "nrc"               => "2587939",
                    "nombre"            => "Tec101 S.A. de C.V.",
                    "codActividad"      => "01111",
                    "descActividad"     => "Cultivo de cereales excepto arroz y para forrajes",
                    "nombreComercial"   => "Tec101",
                    "direccion" => [
                        "departamento"  => "01",
                        "municipio"     => "01",
                        "complemento"   => "39 Av. Norte, Urb. Universitaria Norte #925urbanizacion universitaria norte"
                    ],
                    "telefono"  => "61677017",
                    "correo"    => "cviscarra@masconazo.com"
                ],
                "otrosDocumentos"   => null,
                "ventaTercero"      => null,
                "cuerpoDocumento"   => [
                    [
                        "numItem"           => 1,
                        "tipoItem"          => 1,
                        "numeroDocumento"   => null,
                        "codigo"            => "68546351",
                        "codTributo"        => null,
                        "descripcion"       => "Alarmas Eagle con Bluetooth - Alarmas Eagle con Bluetooth | original",
                        "cantidad"          => 1,
                        "uniMedida"         => 59,
                        "precioUni"         => 65,
                        "montoDescu"        => 0,
                        "ventaNoSuj"        => 0,
                        "ventaExenta"       => 0,
                        "ventaGravada"      => 65,
                        "tributos"          => ["20"],
                        "psv"               => 0,
                        "noGravado"         => 0,
                    ]
                ],
                "resumen" => [
                    "totalNoSuj"            => 0,
                    "totalExenta"           => 0,
                    "totalGravada"          => 65,
                    "subTotalVentas"        => 65,
                    "descuNoSuj"            => 0,
                    "descuExenta"           => 0,
                    "descuGravada"          => 0,
                    "porcentajeDescuento"   => 0,
                    "totalDescu"            => 0,
                    "tributos"              => [
                        [
                            "codigo"        => "20",
                            "descripcion"   => "Impuesto al Valor Agregado 13%",
                            "valor"         => 8.45
                        ]
                    ],
                    "subTotal"              => 65,
                    "ivaPerci1"             => 0,
                    "ivaRete1"              => 3.5,
                    "reteRenta"             => 0,
                    "montoTotalOperacion"   => 73.45,
                    "totalNoGravado"        => 0,
                    "totalPagar"            => 69.95,
                    "totalLetras"           => "Son SESENTA Y NUEVE CON 95/100 USD",
                    "saldoFavor"            => 0,
                    "condicionOperacion"    => 1,
                    "pagos"                 => null,
                    "numPagoElectronico"    => null
                ],
                "extension" => null,
                "apendice" => [
                    [
                        "campo"     => "sucursal",
                        "etiqueta"  => "Sucursal",
                        "valor"     => $company->company_name
                    ],
                    [
                        "campo"     => "condicion_operacion",
                        "etiqueta"  => "Condicion de la operacion",
                        "valor"     => "Contado",
                    ],
                ]
            ];
        }
        else if ($tipoDte == '04') {

            $json = [
                "identificacion" => [
                    "version"           => intval($informacion_dte->version_json),
                    "ambiente"          => $ambiente,
                    "tipoDte"           => $informacion_dte->tipodoc_id,
                    "numeroControl"     => generateNumeroControl($informacion_dte->tipodoc_id),
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "tipoModelo"        => 1,
                    "tipoOperacion"     => 1,
                    "tipoContingencia"  => null,
                    "motivoContin"      => null,
                    "fecEmi"            => Carbon::now()->format('Y-m-d'),
                    "horEmi"            => Carbon::now()->format('H:i:s'),
                    "tipoMoneda"        => "USD"
                ],
                "documentoRelacionado"  => null,
                "emisor" => [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nrc"                   => str_replace('-', '', get_option('nrc')),
                    "nombre"                => get_option('company_name'),
                    "codActividad"          => get_option('cod_actividad'),
                    "descActividad"         => get_option('desc_actividad'),
                    "nombreComercial"       => get_option('tradename'),
                    "tipoEstablecimiento"   => $company->tipoest_id,
                    "direccion" => [
                        "departamento"  => $company->depa_id,
                        "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                        "complemento"   => $company->address
                    ],
                    "telefono"          => $company->cellphone,
                    "correo"            => $company->email,
                    "codEstableMH"      => null,
                    "codEstable"        => null,
                    "codPuntoVentaMH"   => null,
                    "codPuntoVenta"     => null
                ],
                "receptor" => [
                    "tipoDocumento"     => "36",
                    "numDocumento"      => "06140301171038",
                    "nrc"               => "2587939",
                    "nombre"            => "Tec101 S.A. de C.V.",
                    "codActividad"      => "01111",
                    "descActividad"     => "Cultivo de cereales excepto arroz y para forrajes",
                    "nombreComercial"   => "Tec101",
                    "direccion" => [
                        "departamento"  => "01",
                        "municipio"     => "01",
                        "complemento"   => "39 Av. Norte, Urb. Universitaria Norte #925urbanizacion universitaria norte"
                    ],
                    "telefono"  => "61677017",
                    "correo"    => "cviscarra@masconazo.com",
                    "bienTitulo"        => 'Sr'
                ],
                "ventaTercero"      => null,
                "cuerpoDocumento"   => [
                    [
                        "numItem"           => 1,
                        "tipoItem"          => 1,
                        "numeroDocumento"   => null,
                        "codigo"            => "68546351",
                        "codTributo"        => null,
                        "descripcion"       => "Alarmas Eagle con Bluetooth - Alarmas Eagle con Bluetooth | original",
                        "cantidad"          => 1,
                        "uniMedida"         => 59,
                        "precioUni"         => 65,
                        "montoDescu"        => 0,
                        "ventaNoSuj"        => 0,
                        "ventaExenta"       => 0,
                        "ventaGravada"      => 65,
                        "tributos"          => ["20"],
                    ]
                ],
                "resumen" => [
                    "totalNoSuj"            => 0,
                    "totalExenta"           => 0,
                    "totalGravada"          => 65,
                    "subTotalVentas"        => 65,
                    "descuNoSuj"            => 0,
                    "descuExenta"           => 0,
                    "descuGravada"          => 0,
                    "porcentajeDescuento"   => 0,
                    "totalDescu"            => 0,
                    "tributos"              => [
                        [
                            "codigo"        => "20",
                            "descripcion"   => "Impuesto al Valor Agregado 13%",
                            "valor"         => 8.45
                        ]
                    ],
                    "subTotal"              => 65,
                    "montoTotalOperacion"   => 73.45,
                    "totalLetras"           => "Son SESENTA Y NUEVE CON 95/100 USD"
                ],
                "extension" => null,
                "apendice" => [
                    [
                        "campo"     => "sucursal",
                        "etiqueta"  => "Sucursal",
                        "valor"     => $company->company_name
                    ],
                    [
                        "campo"     => "condicion_operacion",
                        "etiqueta"  => "Condicion de la operacion",
                        "valor"     => "Contado",
                    ],
                ]
            ];
        }
        else if ($tipoDte == '05' || $tipoDte == '06' ) {

            $json = [
                "identificacion" => [
                    "version"           => intval($informacion_dte->version_json),
                    "ambiente"          => $ambiente,
                    "tipoDte"           => $informacion_dte->tipodoc_id,
                    "numeroControl"     => generateNumeroControl($informacion_dte->tipodoc_id),
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "tipoModelo"        => 1,
                    "tipoOperacion"     => 1,
                    "tipoContingencia"  => null,
                    "motivoContin"      => null,
                    "fecEmi"            => Carbon::now()->format('Y-m-d'),
                    "horEmi"            => Carbon::now()->format('H:i:s'),
                    "tipoMoneda"        => "USD"
                ],
                "documentoRelacionado" => [
                    [
                        "tipoDocumento"     => "03",
                        "tipoGeneracion"    => 2,
                        "numeroDocumento"   => "56EB7965-132D-49FE-9117-E269ED592443",
                        "fechaEmision"      => "2024-03-02"
                    ]
                ],
                "emisor" => [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nrc"                   => str_replace('-', '', get_option('nrc')),
                    "nombre"                => get_option('company_name'),
                    "codActividad"          => get_option('cod_actividad'),
                    "descActividad"         => get_option('desc_actividad'),
                    "nombreComercial"       => get_option('tradename'),
                    "tipoEstablecimiento"   => $company->tipoest_id,
                    "direccion" => [
                        "departamento"  => $company->depa_id,
                        "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                        "complemento"   => $company->address
                    ],
                    "telefono"          => $company->cellphone,
                    "correo"            => $company->email,
                ],
                "receptor" => [
                    "nit"               =>  "06140301171038",
                    "nrc"               => "2587939",
                    "nombre"            => "Tec101 S.A. de C.V.",
                    "codActividad"      => "01111",
                    "descActividad"     => "Cultivo de cereales excepto arroz y para forrajes",
                    "nombreComercial"   => "Tec101",
                    "direccion" => [
                        "departamento"  => "01",
                        "municipio"     => "01",
                        "complemento"   => "39 Av. Norte, Urb. Universitaria Norte #925urbanizacion universitaria norte"
                    ],
                    "telefono"  => "61677017",
                    "correo"    => "cviscarra@masconazo.com"
                ],
                "ventaTercero"      => null,
                "cuerpoDocumento"   => [
                    [
                        "numItem"           => 1,
                        "tipoItem"          => 1,
                        "numeroDocumento"   => "56EB7965-132D-49FE-9117-E269ED592443",
                        "codigo"            => "A598BT",
                        "codTributo"        => null,
                        "descripcion"       => "Alarma Eagle - Alarmas Eagle 2023-04 | original",
                        "cantidad"          => 1,
                        "uniMedida"         => 59,
                        "precioUni"         => 0.88,
                        "montoDescu"        => 0,
                        "ventaNoSuj"        => 0,
                        "ventaExenta"       => 0,
                        "ventaGravada"      => 0.88,
                        "tributos"          => ["20"]
                    ]
                ],
                "resumen"   => [
                    "totalNoSuj"        => 0,
                    "totalExenta"       => 0,
                    "totalGravada"      => 0.88,
                    "subTotalVentas"    => 0.88,
                    "descuNoSuj"        => 0,
                    "descuExenta"       => 0,
                    "descuGravada"      => 0,
                    "totalDescu"        => 0,
                    "tributos"  => [
                        [
                            "codigo"      => "20",
                            "descripcion" => "Impuesto al Valor Agregado 13%",
                            "valor"       => 0.12
                        ]
                    ],
                    "subTotal"              => 0.88,
                    "ivaPerci1"             => 0,
                    "ivaRete1"              => 0,
                    "reteRenta"             => 0,
                    "montoTotalOperacion"   => 1,
                    "totalLetras"           => "It is UN USD",
                    "condicionOperacion"    => 1
                ],
                "extension" => [
                    "nombEntrega"   => "Cerrajeria 2000",
                    "docuEntrega"   => "11012111771014",
                    "nombRecibe"    => "Tec101 S.A. de C.V.",
                    "docuRecibe"    => "06140301171038",
                    "observaciones" => null
                ],
                "apendice" => [
                    [
                        "campo"     => "sucursal",
                        "etiqueta"  => "Sucursal",
                        "valor"     => "Casa Matriz Merliot"
                    ],
                    [
                        "campo"     => "condicion_operacion",
                        "etiqueta"  => "Condicion de la operacion",
                        "valor"     => "Contado"
                    ]
                ]
            ];

            if ($tipoDte == 06) {
                $json['resumen']['numPagoElectronico'] = null;
            }
        }
        else if ($tipoDte == '11') {

            $json = [
                "identificacion" => [
                    "version"           => intval($informacion_dte->version_json),
                    "ambiente"          => $ambiente,
                    "tipoDte"           => $informacion_dte->tipodoc_id,
                    "numeroControl"     => generateNumeroControl($informacion_dte->tipodoc_id),
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "tipoModelo"        => 1,
                    "tipoOperacion"     => 1,
                    "tipoContingencia"  => null,
                    "motivoContigencia" => null,
                    "fecEmi"            => Carbon::now()->format('Y-m-d'),
                    "horEmi"            => Carbon::now()->format('H:i:s'),
                    "tipoMoneda"        => "USD"
                ],
                "emisor" => [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nrc"                   => str_replace('-', '', get_option('nrc')),
                    "nombre"                => get_option('company_name'),
                    "codActividad"          => get_option('cod_actividad'),
                    "descActividad"         => get_option('desc_actividad'),
                    "nombreComercial"       => get_option('tradename'),
                    "tipoEstablecimiento"   => $company->tipoest_id,
                    "direccion" => [
                        "departamento"  => $company->depa_id,
                        "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                        "complemento"   => $company->address
                    ],
                    "telefono"          => $company->cellphone,
                    "correo"            => $company->email,
                    "codEstableMH"      => null,
                    "codEstable"        => null,
                    "codPuntoVentaMH"   => null,
                    "codPuntoVenta"     => null,
                    "tipoItemExpor"     => 3,
                    "recintoFiscal"     => null,
                    "regimen"           => null,
                ],
                "receptor" => [
                    "nombre"            => "Tec101 S.A. de C.V.",
                    "tipoDocumento"     => "36",
                    "numDocumento"      => "06140301171038",
                    "nombreComercial"   => "Tec101",
                    "codPais"           => "9300",
                    "nombrePais"        => "EL SALVADOR",
                    "complemento"       => "39 Av. Norte, Urb. Universitaria Norte #925rnurbanizacion universitaria norte, AHUACHAPu00c1N, Ahuachapu00e1n",
                    "tipoPersona"       => 2,
                    "descActividad"     => "Cultivo de cereales excepto arroz y para forrajes",
                    "telefono"          => "61677017",
                    "correo"            => "jflores@tec101.com"
                ],
                "otrosDocumentos"   => null,
                "ventaTercero"      => null,
                "cuerpoDocumento"   => [
                    [
                        "numItem"           => 1,
                        "cantidad"          => 1,
                        "codigo"            => "25",
                        "uniMedida"         => 59,
                        "descripcion"       => "Llaves Valet Corte Sencilllo - llaves | original",
                        "precioUni"         => 25,
                        "montoDescu"        => 0,
                        "ventaGravada"      => 25,
                        "tributos"          => ["C3"],
                        "noGravado"         => 0,
                    ]
                ],
                "resumen" => [
                    "totalGravada"          => 25,
                    "descuento"             => 0,
                    "porcentajeDescuento"   => 0,
                    "totalDescu"            => 0,
                    "seguro"                => 0,
                    "flete"                 => 0,
                    "montoTotalOperacion"   => 25,
                    "totalNoGravado"        => 0,
                    "totalPagar"            => 25,
                    "totalLetras"           => "Son VEINTICINCO CON 00/100 USD",
                    "condicionOperacion"    => 1,
                    "pagos" => [
                      [
                        "codigo"            => "01",
                        "montoPago"         => 25,
                        "referencia"        => null,
                        "plazo"             => null,
                        "periodo"           => null
                      ]
                    ],
                    "codIncoterms"          => null,
                    "descIncoterms"         => null,
                    "numPagoElectronico"    => null,
                    "observaciones"         => null
                ],
                "apendice" => [
                    [
                        "campo"     => "sucursal",
                        "etiqueta"  => "Sucursal",
                        "valor"     => $company->company_name
                    ],
                    [
                        "campo"     => "condicion_operacion",
                        "etiqueta"  => "Condicion de la operacion",
                        "valor"     => "Contado",
                    ],
                ]
            ];
        }
        else if ($tipoDte == '14') {

            $json = [
                "identificacion" => [
                    "version"           => intval($informacion_dte->version_json),
                    "ambiente"          => $ambiente,
                    "tipoDte"           => $informacion_dte->tipodoc_id,
                    "numeroControl"     => generateNumeroControl($informacion_dte->tipodoc_id),
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "tipoModelo"        => 1,
                    "tipoOperacion"     => 1,
                    "tipoContingencia"  => null,
                    "motivoContin"      => null,
                    "fecEmi"            => Carbon::now()->format('Y-m-d'),
                    "horEmi"            => Carbon::now()->format('H:i:s'),
                    "tipoMoneda"        => "USD"
                ],
                "emisor" => [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nrc"                   => str_replace('-', '', get_option('nrc')),
                    "nombre"                => get_option('company_name'),
                    "codActividad"          => get_option('cod_actividad'),
                    "descActividad"         => get_option('desc_actividad'),
                    "direccion" => [
                        "departamento"  => $company->depa_id,
                        "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                        "complemento"   => $company->address
                    ],
                    "telefono"          => $company->cellphone,
                    "codEstableMH"      => null,
                    "codEstable"        => null,
                    "codPuntoVentaMH"   => null,
                    "codPuntoVenta"     => null,
                    "correo"            => $company->email,
                ],
                "sujetoExcluido" => [
                    "tipoDocumento"     => "36",
                    "numDocumento"      => "06140301171038",
                    "nombre"            => "Tec101 S.A. de C.V.",
                    "codActividad"      => "01111",
                    "descActividad"     => "Cultivo de cereales excepto arroz y para forrajes",
                    "direccion"     => [
                      "departamento"    => "01",
                      "municipio"       => "01",
                      "complemento"     => "39 Av. Norte, Urb. Universitaria Norte #925rnurbanizacion universitaria norte"
                    ],
                    "telefono"          => "61677017",
                    "correo"            => "jflores@tec101.com"
                ],
                "cuerpoDocumento"   => [
                    [
                        "numItem"       => 1,
                        "tipoItem"      => 1,
                        "cantidad"      => 1,
                        "codigo"        => "55315",
                        "uniMedida"     => 59,
                        "descripcion"   => "Pantalla Android - Pantallas Android | original",
                        "precioUni"     => 350,
                        "montoDescu"    => 0,
                        "compra"        => 350
                    ]
                ],
                "resumen" => [
                    "totalCompra"           => 350,
                    "descu"                 => 0,
                    "totalDescu"            => 0,
                    "subTotal"              => 350,
                    "ivaRete1"              => 0,
                    "reteRenta"             => 0,
                    "totalPagar"            => 350,
                    "totalLetras"           => "Son TRESCIENTOS CINCUENTA CON 00/100 USD",
                    "condicionOperacion"    => 1,
                    "pagos" => [
                      [
                        "codigo"        => "01",
                        "montoPago"     => 350,
                        "referencia"    => null,
                        "plazo"         => null,
                        "periodo"       => null
                      ]
                    ],
                    "observaciones" => null
                ],
                "apendice" => [
                    [
                        "campo"     => "sucursal",
                        "etiqueta"  => "Sucursal",
                        "valor"     => $company->company_name
                    ],
                    [
                        "campo"     => "condicion_operacion",
                        "etiqueta"  => "Condicion de la operacion",
                        "valor"     => "Contado",
                    ],
                ]
            ];
        }

        Log::info('Carga Útil de la Solicitud: ' . json_encode([
            "nit" => str_replace('-', '', get_option('nit')),
            "ambiente" => $ambiente,
            "idEnvio" => 1,
            "version" => intval($informacion_dte->version_json),
            "tipoDte" => $informacion_dte->tipodoc_id,
            'dteJson' => $json
        ]));

        try {

            $oldToken = PasarelaToken::where('status', '=', 1)->first();
            if (!$oldToken) {
                Log::info('No existe token antiguo, se solicita token');
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                $token = PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            } else {
                $fechaActual = Carbon::now();
                $fechaExpiracion = $oldToken->expired_token;
                if ($fechaActual->gt($fechaExpiracion)) {
                    Log::info('Token expirado, se solicita nuevo token');
                    $tokenPasarela = $this->generateTokenPasarela();
                    $tokenPasarela = json_decode(json_encode($tokenPasarela));
                    $oldToken->update([
                        'status' => 0
                    ]);
                    $oldToken->delete();
                    PasarelaToken::create([
                        'token'         => $tokenPasarela->token,
                        'created_token' => $tokenPasarela->created,
                        'expired_token' => $tokenPasarela->expired,
                    ]);
                    Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                } else {
                    Log::info('Token aun sin expirar');
                    $tokenPasarela = $oldToken;
                    Log::info('Token: Bearer ' . $tokenPasarela->token);
                }
            }

            Log::info(json_encode($tokenPasarela));

            Log::info('Datos de token enviado: ' . json_encode($tokenPasarela));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenPasarela->token,
                'x-key-nit' => env('API_KEY_NIT')
            ])
                ->post(
                    env('API_PASARELA_FE'),
                    [
                        "nit" => str_replace('-', '', get_option('nit')),
                        "ambiente" => $ambiente,
                        "idEnvio" => 1,
                        "version" => $informacion_dte->version_json,
                        "tipoDte" => $informacion_dte->tipodoc_id,
                        'dteJson' => $json
                    ]
                )
                ->json();

            Log::info('Respuesta API MH: ' . json_encode($response));

            Auth::logout();

            $response_mh = json_decode(json_encode($response));

            if ($response_mh->estado === 'PROCESADO') {
                BlockDte::where('type_dte', '=', $tipoDte)->increment('correlativo');
            }

            return $response;
        } catch (\Exception $e) {

            Log::error('Error en la solicitud HTTP: ' . $e->getMessage());

            return response()->json(['error' => 'Hubo un problema en la solicitud HTTP'], 500);
        }
    }


    public function verificarCajaAbierta(){

        $id_company = Session::get('company')->id;

        $company = Company::find($id_company);

        $cash = Cash::where('company_id', $id_company)
                    ->where('cash_status', 'Opened')
                    ->first();

        if( !$cash ){
            return response()->json(['result' => 'error', 'message' => 'No existe una caja abierta para sucursal '.$company->company_name]);
        }
        else{
            return response()->json(['result' => 'success', 'message' => 'Caja abierta para sucursal '.$company->company_name]);
        }
    }

    public function obtenerSelloHacienda(Request $request){

        $id_invoice = $request->id_invoice;

        $msg = '';

        $invoice = Invoice::find($id_invoice);

        $invoice->invoice_date  =  Carbon::now()->format('Y-m-d');
        $invoice->created_at    =  Carbon::now()->format('Y-m-d H:i:s');
        $invoice->invoice_time  =  Carbon::now()->format('H:i:s');
        $invoice->save();

        $response = $this->sendInvoiceToHacienda($invoice->id);
        $response_mh = json_decode(json_encode($response));

        if (!property_exists($response_mh, 'estado')) {

            log::info('Error al procesar DTE en obtenerSelloHacienda (propiedad estado no existe en respuesta apihacienda): ' . json_encode($response));

            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => 'Error en respuesta de pasarela']);
            } else {
                return redirect()->route('invoices.create')
                    ->withErrors(['Sorry, Error Occured !', 'Error en pasarela'])
                    ->withInput();
            }
        }

        $invoice->status_mh         = ($response_mh->estado === 'RECHAZADO') ? 0 : 1;
        $invoice->response_mh       = json_encode($response);
        $invoice->sello_recepcion   = ($response_mh->estado === 'PROCESADO') ? $response_mh->selloRecibido : null;
        $invoice->json_dte          = json_encode($response_mh->json);
        $invoice->postpone_invoice  = 0;
        $invoice->save();

        if ($response_mh->estado === 'RECHAZADO') {

            log::info('Error al procesar DTE en obtenerSelloHacienda: ' . json_encode($response));

            if( $request->ajax() ){
                return response()->json(['result' => 'errorMH', 'action' => 'store', 'message' => _lang('Error al procesar DTE'), 'data' => $response]);
            } else {
                return redirect()->route('invoices.create', $invoice->id)->with('error', 'Error al procesar DTE');
            }
        }
        else if ($response_mh->estado === 'PROCESADO') {

            if( $invoice->correo != '' ){
                $this->sendEmailFactura($invoice->id);
            }

        }

        // log::info("Lo que envio de store hacia sendInviceToHacienda" . json_encode($response));

        if( !$request->ajax() ){
            return redirect()->route('invoices.show', $invoice->id)->with('success', _lang('Factura Generada Exitosamente ' . $msg));
        }
        else{
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Factura Generada Exitosamente ' . $msg), 'data' => $invoice]);
        }

    }

    function downloadJsons(Request $request){



        $tipo_factura   = $request->input('tipo_factura');
        $numero_factura = $request->input('numero_factura');
        $client_id      = $request->input('client_id');
        $status         = $request->input('status');
        $rangos         = $request->input('rangos');
        $pdf            = $request->input('pdf');

        $fechas         = explode(' - ', $rangos);

        $fecha_inicio   = $fechas[0];
        $fecha_fin      = $fechas[1];

        $invoices = Invoice::whereBetween('invoice_date', [$fecha_inicio, $fecha_fin])->where('status_mh', 1);

        if( $tipo_factura != '' ){
            $invoices->where('tipodoc_id', $tipo_factura);
        }

        if( $numero_factura != '' ){
            $invoices->where('invoice_number', $numero_factura);
        }

        if( $client_id != '' ){
            $invoices->where('client_id', $client_id);
        }

        if( $status != '' ){
            $invoices->where('status', $status);
        }

        
        $invoices = $invoices->pluck('id', 'numero_control');
                            
        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'No se encontraron registros para las fechas proporcionadas'], 404);
        }


        if( $pdf == 1 ){
            $zip_file_name = 'pdf_'. $rangos .'_.zip';
        }
        else{
            $zip_file_name = 'json_'. $rangos .'_.zip';
        }

        $zip_path = storage_path($zip_file_name);
        
        // Crear una nueva instancia de ZipArchive
        $zip = new ZipArchive;
        
        // Abrir el archivo ZIP (si no existe, se crea uno nuevo)
        if( $zip->open($zip_path, ZipArchive::CREATE) === TRUE ){

            foreach( $invoices as $numero_control => $id ){

                $json_name = 'invoice_' . $id . '.json';
                
                if( $pdf == 1 ){
                    
                    $generate_pdf = $this->downloadPdf($id);

                    $json_name = 'invoice_' . $id . '.pdf';

                    $json_path = storage_path('app/pdf_invoices/' . $json_name);
                }
                else{
                    $json_path = storage_path('app/json_invoices/' . $json_name);
                }

                if( file_exists($json_path) ){

                    if( $pdf == 1 ){

                        $json_name_legible = 'invoice_' . $id . '_' . $numero_control . '.pdf';
                    }
                    else{
                        $json_name_legible = 'invoice_' . $id . '_' . $numero_control . '.json';
                    }
                    $zip->addFile($json_path, $json_name_legible);
                }
            }

            $zip->close();


            if( $pdf == 1 ){
                $files = Storage::files('pdf_invoices');
    
                foreach( $files as $file ){
                    Storage::delete($file);
                }
            }


            return response()->json(['url' => url('/download-zip-json/' . $zip_file_name)]);
        }
        else{
            return response()->json(['error' => 'No se pudo crear el archivo ZIP'], 500);
        }
    }

    public function descargarZip($zip_file_name){

        $zip_path = storage_path($zip_file_name);

        return response()->download($zip_path)->deleteFileAfterSend(true);
    }
}
