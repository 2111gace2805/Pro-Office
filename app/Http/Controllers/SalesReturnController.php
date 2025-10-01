<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceItemTax;
use Illuminate\Http\Request;
use App\SalesReturn;
use App\SalesReturnItem;
use App\SalesReturnItemTax;
use App\Stock;
use App\Tax;
use Validator;
use DataTables;
use DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Company;
use Carbon\Carbon;
use App\PasarelaToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.accounting.sales_return.list');
    }

	public function get_table_data() {

        $currency = currency();

        $salesReturns = SalesReturn::with('customer')->where('company_id', Session::get('company')->id)
						->select('sales_return.*')->orderBy("id", "desc");

        return Datatables::eloquent($salesReturns)
            ->editColumn('grand_total', function ($salesReturn) use ($currency) {
                return "<span class='float-right'>" . decimalPlace($salesReturn->grand_total, $currency) . "</span>";
            })
            ->addColumn('action', function ($salesReturn) {
                return '<div class="dropdown text-center">'
                . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                . '<i class="mdi mdi-chevron-down"></i></button>'
                . '<div class="dropdown-menu">'
                // . '<a class="dropdown-item" href="' . action('SalesReturnController@edit', $salesReturn->id) . '"><i class="ti-pencil-alt"></i> ' . _lang('Edit') . '</a></li>'
                . '<a class="dropdown-item" href="' . action('SalesReturnController@show', $salesReturn->id) . '"><i class="ti-eye"></i> ' . _lang('View') . '</a></li>'
                . '<form action="' . action('SalesReturnController@destroy', $salesReturn->id) . '" method="post">'
                . csrf_field()
                . '<input name="_method" type="hidden" value="DELETE">'
                . '<button class="button-link btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Delete') . '</button>'
                    . '</form>'
                    . '</div>'
                    . '</div>';
            })
            ->setRowId(function ($salesReturn) {
                return "row_" . $salesReturn->id;
            })
            ->rawColumns(['grand_total', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $invoice = Invoice::with('client')->find($request->id);
        $invoiceDate = \Carbon\Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d');

        if (! $request->ajax()) {
            return view('backend.accounting.sales_return.create', compact('invoice', 'id', 'invoiceDate'));
        } else {
            return view('backend.accounting.sales_return.modal.create', compact('invoice', 'id', 'invoiceDate'));
        }
    }

    


	public function store(Request $request) {
		$invoice_id = $request->invoice_id;
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|max:191',
            'client_id'      => 'required',
            'invoice_date'   => 'required',
            'product_id'     => 'required',
            'nombre_soli'    => 'required',
            'tipo_doc_soli'  => 'required',
            'num_doc_soli'   => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('sales_returns.create', ['id'=>$invoice_id])
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $invoiceToCancel = Invoice::find($invoice_id);

            $response = $this->anularInvoiceMH($invoiceToCancel->id, $request);
            $response_mh = json_decode(json_encode($response));


            if ($response_mh->estado === 'RECHAZADO') {

                Log::info('Error al anular DTE: ' . json_encode($response_mh->observaciones));

                if ($request->ajax()) {
                    return response()->json(['result' => 'errorMH', 'message' => _lang('Error al anular DTE'), 'data' => $response]);
                } else {
                    return redirect()->route('sales_returns.create', ['id'=>$invoice_id])->with('error', 'Error al anular DTE');
                }
            }
            else{
                // if($request->anular_factura == 1){
                    
                    $invoiceToCancel->status = 'Canceled';
                    $invoiceToCancel->save();
        
                    if ($invoiceToCancel->forp_id == '01') { // 01 efectivo
                        $cash = get_cash();
                        $cash->cash_value -= $invoiceToCancel->grand_total;
                    }
        
                    $invoiceItemsToCancel = InvoiceItem::where("invoice_id", $invoice_id)->get();
                    foreach ($invoiceItemsToCancel as $p_item) {
                        $invoiceItem = InvoiceItem::find($p_item->id);
                        update_stock($p_item->item_id, $invoiceItem->quantity, '+');
                    }
                // }
        
                $invoice                 = new SalesReturn();
                // $invoice->invoice_number = $request->input('invoice_number');
                $invoice->customer_id      = $request->input('client_id');
                $invoice->return_date   = $request->input('invoice_date');
                // si es CCF
                // if ($request->input('tipodoc_id') == '03') {
                //     $invoice->grand_total    = $request->product_total + $request->tax_total;
                // }else{
                    $invoice->product_total    = $request->product_total;
                    $invoice->iva_retenido    = $request->iva_retenido;
                    $invoice->iva_percibido    = $request->iva_percibido;
                    $invoice->grand_total    = $request->grand_total;
                // }
                $invoice->tax_amount      = $request->input('tax_total');
                $invoice->note           = $request->input('note');
                // $invoice->tipodoc_id     = $request->input('tipodoc_id');
                // $invoice->tpers_id       =  $request->input('tpers_id');
                // $invoice->num_documento  = $request->input('num_documento');
                // $invoice->plazo_id  = $request->input('plazo_id');
                // $invoice->periodo  = $request->input('periodo');
                $invoice->invoice_id  = $request->input('invoice_id');
        
                // cliente exento, nosujeto, gran contribuyente
                // $infoCliente = Contact::find($invoice->client_id);
                // $invoice->exento_iva  = $infoCliente->exento_iva;
                // $invoice->nosujeto_iva  = $infoCliente->nosujeto_iva;
                // $invoice->gran_contribuyente  = $infoCliente->gran_contribuyente;
                $invoice->save();
        
                $taxes = Tax::all();
        
                $ivaRetenido = 0;
        
                for ($i = 0; $i < count($request->product_id); $i++) {
                    $stock = Stock::whereRaw("product_id = {$request->product_id[$i]} and company_id = ".company_id())->first();
                    // if ($stock != null && $stock->quantity < $request->quantity[$i]) {
                    //     if ($request->ajax()) {
                    //         return response()->json(['result' => 'error', 'message' => 'Stock máximo alcanzado']);
                    //     } else {
                    //         return redirect()->route('invoices.create')
                    //              ->withErrors(['Sorry, Error Occured !', 'Stock máximo alcanzado.'])
                    //             ->withInput();
                    //     }
                    // }
                    $invoiceItem              = new SalesReturnItem();
                    $invoiceItem->sales_return_id  = $invoice->id;
                    $invoiceItem->product_id     = $request->product_id[$i];
                    $invoiceItem->description = $request->product_description[$i];
                    $invoiceItem->quantity    = $request->quantity[$i];
                    $invoiceItem->unit_cost   = $request->unit_cost[$i];
                    $invoiceItem->discount    = $request->discount[$i];
                    $invoiceItem->tax_amount  = $request->product_tax[$i];
                    $invoiceItem->sub_total   = $request->sub_total[$i];
                    $invoiceItem->invoice_item_id   = $request->invoice_item_id[$i];
                    // $invoiceItem->product_price  = $request->product_price[$i];
        
                    
        
        
        
                    // $invoiceItem->no_declaracion = $request->no_declaracion[$i];
                    // $invoiceItem->aduana_registro = $request->aduana_registro[$i];
                    // $invoiceItem->fecha_registro = $request->fecha_registro[$i];
                    // $invoiceItem->codigo_arancelario = $request->codigo_arancelario[$i];
                    // $invoiceItem->observacion = $request->observacion[$i];
        
                    $invoiceItem->save();
        
                    //Store Invoice Taxes
                    if (isset($request->tax[$invoiceItem->product_id])) {
                        foreach ($request->tax[$invoiceItem->product_id] as $taxId) {
                            $tax = $taxes->firstWhere('id', $taxId);
        
                            $invoiceItemTax                  = new SalesReturnItemTax();
                            $invoiceItemTax->sales_return_id      = $invoiceItem->sales_return_id;
                            $invoiceItemTax->sales_return_item_id = $invoiceItem->id;
                            $invoiceItemTax->tax_id          = $tax->id;
                            $tax_type                        = $tax->type == 'percent' ? '%' : '';
                            $invoiceItemTax->name            = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                            
                            // si es CCF
                            if ($request->input('tipodoc_id') == '03') {
                                $invoiceItemTax->amount          = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                            }else{
                                $invoiceItemTax->amount = $tax->type == 'percent' ? (($invoiceItem->quantity*$request->product_price[$i]-$invoiceItem->discount) / 100) * $tax->rate : $tax->rate;
                            }
                            $invoiceItemTax->save();
        
                            if ($tax->trib_id == '20') { // 20 es tributo IVA
                                $ivaRetenido += round(($invoiceItem->quantity*$request->product_price[$i]-$invoiceItem->discount)*(floatval(get_option('retencion_iva'))/100), 2);
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
                    return redirect()->route('invoices.index', $invoice->id)->with('success', _lang('Devolución de productos registrada.'));
                } else {
                    return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Devolución de productos registrada'), 'data' => $invoice]);
                }

            }
    
        }
        catch (UsuarioSinDUIException $e) {

            DB::rollBack();

            Log::info($e->getMessage());

            if ($request->ajax()) {
                return response()->json(['result' => 'error_usuario', 'message' => $e->getMessage()]);
            } else {
                return redirect()->route('sales_returns.create', ['id'=>$invoice_id])->withErrors($validator)->withInput();
            }
        }
        catch (\Throwable $th) {

            DB::rollBack();

            Log::info($th->getMessage());

            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $th->getMessage()]);
            } else {
                return redirect()->route('sales_returns.create', ['id'=>$invoice_id])->withErrors($validator)->withInput();
            }
        }


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_(Request $request)
    {	
		$validator = Validator::make($request->all(), [
			'return_date' => 'required',
			'customer_id' => 'required',
			'sub_total.*' => 'required|numeric',
			'attachemnt' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
			'product_id'     => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect()->route('sales_returns.create')
							->withErrors($validator)
							->withInput();
			}			
		}
		
		DB::beginTransaction();
			
		$attachemnt = "";
	    if($request->hasfile('attachemnt'))
		{
			$file = $request->file('attachemnt');
			$attachemnt = time().$file->getClientOriginalName();
			$file->move(public_path()."/uploads/attachments/", $attachemnt);
		}
		
        $salesReturn = new SalesReturn();
	    $salesReturn->return_date = $request->input('return_date');
		$salesReturn->customer_id = $request->input('customer_id');
		$salesReturn->tax_amount = $request->tax_total;
		$salesReturn->product_total = $request->input('product_total');
		$salesReturn->grand_total = ($salesReturn->product_total + $salesReturn->tax_amount);
		$salesReturn->attachemnt = $attachemnt;
		$salesReturn->note = $request->input('note');
	
		$salesReturn->save();
		
		$taxes = Tax::all();

		//Save Sales Return item
		for($i = 0; $i < count($request->product_id); $i++ ){
			$salesReturnItem = new SalesReturnItem();
			$salesReturnItem->sales_return_id = $salesReturn->id;
			$salesReturnItem->product_id = $request->product_id[$i];
			$salesReturnItem->description = $request->product_description[$i];
			$salesReturnItem->quantity = $request->quantity[$i];
			$salesReturnItem->unit_cost = $request->unit_cost[$i];
			$salesReturnItem->discount = $request->discount[$i];
			$salesReturnItem->tax_amount = $request->product_tax[$i];
			$salesReturnItem->sub_total = $request->sub_total[$i];
			$salesReturnItem->save();
			
			//Store Sales Return Taxes
			if(isset($request->tax[$salesReturnItem->product_id])){
				foreach($request->tax[$salesReturnItem->product_id] as $taxId){
					$tax = $taxes->firstWhere('id', $taxId);
					
					$salesReturnItemTax = new SalesReturnItemTax();
					$salesReturnItemTax->sales_return_id = $salesReturnItem->sales_return_id;
					$salesReturnItemTax->sales_return_item_id = $salesReturnItem->id;
					$salesReturnItemTax->tax_id = $tax->id;
					$tax_type = $tax->type == 'percent' ? '%' : '';
					$salesReturnItemTax->name = $tax->tax_name.' @ '.$tax->rate.$tax_type;
					$salesReturnItemTax->amount = $tax->type == 'percent' ? ($salesReturnItem->sub_total / 100) * $tax->rate : $tax->rate;
					$salesReturnItemTax->save();
				}
			}

			//Update Stock
			$stock = Stock::whereRaw("product_id = $salesReturnItem->product_id and company_id = ".company_id())->first();
			$stock->quantity = $stock->quantity + $salesReturnItem->quantity;
			$stock->save();
		}
		
		DB::commit();

        
		if(! $request->ajax()){
           return redirect()->route('sales_returns.show', $salesReturn->id)->with('success', _lang('Sales Returned Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Sales Returned Sucessfully'),'data'=>$purchase]);
		}
        
   }
	

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $sales_return = SalesReturn::find($id);
		$sales_return_taxes = SalesReturnItemTax::where('sales_return_id',$id)
												->selectRaw('sales_return_item_taxes.*,sum(sales_return_item_taxes.amount) as tax_amount')
												->groupBy('sales_return_item_taxes.tax_id')
												->get();
		
		return view('backend.accounting.sales_return.view',compact('sales_return','sales_return_taxes','id'));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $sales = SalesReturn::find($id);
		if(! $request->ajax()){
		   return view('backend.accounting.sales_return.edit',compact('sales','id'));
		}else{
           return view('backend.accounting.sales_return.modal.edit',compact('sales','id'));
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
			'return_date' => 'required',
			'customer_id' => 'required',
			'sub_total.*' => 'required|numeric',
			'attachemnt' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
			'product_id'     => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect()->route('sales_returns.edit', $id)
							->withErrors($validator)
							->withInput();
			}			
		}

		DB::beginTransaction();
		
			
		$attachemnt = "";
	    if($request->hasfile('attachemnt'))
		{
			$file = $request->file('attachemnt');
			$attachemnt = time().$file->getClientOriginalName();
			$file->move(public_path()."/uploads/attachments/", $attachemnt);
		}
		

        $salesReturn = SalesReturn::find($id);
	    $previous_amount = $salesReturn->grand_total;
		$salesReturn->return_date = $request->input('return_date');
		$salesReturn->customer_id = $request->input('customer_id');
		$salesReturn->tax_amount = $request->tax_total;
		$salesReturn->product_total = $request->input('product_total');
		$salesReturn->grand_total = ($salesReturn->product_total + $salesReturn->tax_amount);
		$salesReturn->attachemnt = $attachemnt;
		$salesReturn->note = $request->input('note');
	
		$salesReturn->save();
		
		$taxes = Tax::all();


		//Remove Previous Purcahse item
		$previous_items = SalesReturnItem::where("sales_return_id",$id)->get();
		foreach($previous_items as $p_item){
			$returnItem = SalesReturnItem::find($p_item->id);
			update_stock($p_item->product_id, $returnItem->quantity, '-');
			$returnItem->delete();
		}
		
		$salesReturnItemTax = SalesReturnItemTax::where("sales_return_id",$id);
		$salesReturnItemTax->delete();

		for( $i = 0; $i < count($request->product_id); $i++ ){
			$returnItem = new SalesReturnItem();
			$returnItem->sales_return_id = $salesReturn->id;
			$returnItem->product_id = $request->product_id[$i];
			$returnItem->description = $request->product_description[$i];
			$returnItem->quantity = $request->quantity[$i];
			$returnItem->unit_cost = $request->unit_cost[$i];
			$returnItem->discount = $request->discount[$i];
			$returnItem->tax_amount = $request->product_tax[$i];
			$returnItem->sub_total = $request->sub_total[$i];
			$returnItem->save();
			
			//Store Sales Return Taxes
			if(isset($request->tax[$returnItem->product_id])){
				foreach($request->tax[$returnItem->product_id] as $taxId){
					$tax = $taxes->firstWhere('id', $taxId);
					
					$salesReturnItemTax = new SalesReturnItemTax();
					$salesReturnItemTax->sales_return_id = $returnItem->sales_return_id;
					$salesReturnItemTax->sales_return_item_id = $returnItem->id;
					$salesReturnItemTax->tax_id = $tax->id;
					$tax_type = $tax->type == 'percent' ? '%' : '';
					$salesReturnItemTax->name = $tax->tax_name.' @ '.$tax->rate.$tax_type;
					$salesReturnItemTax->amount = $tax->type == 'percent' ? ($returnItem->sub_total / 100) * $tax->rate : $tax->rate;
					$salesReturnItemTax->save();
				}
			}

			update_stock($request->product_id[$i], $request->quantity[$i]);

		}
		
		DB::commit();

				
		if(! $request->ajax()){
           return redirect()->route('sales_returns.show', $salesReturn->id)->with('success', _lang('Updated Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Sucessfully'),'data'=>$purchase]);
		}
	    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		DB::beginTransaction();

        $salesReturnItemTax = SalesReturnItemTax::where('sales_return_id',$id);
		$salesReturnItemTax->delete();
		
        $salesReturn = SalesReturn::find($id);
		
		//Remove Sales Return Items
		$salesReturnItems = SalesReturnItem::where("sales_return_id",$id)->get();
		foreach($salesReturnItems as $p_item){
			$returnItem = SalesReturnItem::find($p_item->id);
			update_stock($p_item->product_id, $returnItem->quantity, '+');
			$returnItem->delete();
		}
		
		
        $salesReturn->delete();
		
		DB::commit();

        return back()->with('success',_lang('Deleted Sucessfully'));
	}

	public function getInvoiceItem(Request $request){
		$item = InvoiceItem::with(['item', 'taxes'])->find($request->invoice_item_id);
		return $item;
	}
	
    public function generateTokenPasarela(){

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

    public function anularInvoiceMH($invoice_id, Request $request){

        $invoice        = Invoice::find($invoice_id);
        $company        = Company::find($invoice->company_id);
        $cash           = get_cash();
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
            "codEstableMH"          => $company->codigo_sucursal,
            "codEstable"            => $company->codigo_sucursal,
            "codPuntoVentaMH"       => $cash->cash_code,
            "codPuntoVenta"         => $cash->cash_code,
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
            "nombre"            => $invoice->client->company_name,
            "telefono"          => $invoice->client->contact_phone,
            "correo"            => $invoice->client->contact_email
        ];

        $usuario = Auth::user();

        if ($usuario->dui == '') {
            throw new UsuarioSinDUIException();
        }


        $motivo = [
            "tipoAnulacion"     => 2,
            "motivoAnulacion"   => "Rescindir De La Operación Realizada.",
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
	
}