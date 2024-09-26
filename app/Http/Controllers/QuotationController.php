<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\InvoiceItem;
use App\InvoiceItemTax;
use App\Mail\GeneralMail;
use App\Product;
use App\Quotation;
use App\QuotationItem;
use App\QuotationItemTax;
use App\Stock;
use App\Tax;
use App\TipoDocumento;
use App\Utilities\Overrider;
use Exception;
//use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PDF;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('backend.accounting.quotation.list');
    }

    public function get_table_data() {

        $currency = currency();

        $quotations = Quotation::with("client")
            ->select('quotations.*')
            ->orderBy("quotations.id", "desc");


        return DataTables::eloquent($quotations)
            ->editColumn('quotation_number', function ($quotation) {
                if ($quotation->status == 0) {
                    return $quotation->quotation_number;
                } else {
                    return $quotation->quotation_number . "<a href='" . route('invoices.show', $quotation->invoice_id) . "'><small class='badge badge-secondary float-right'>" . _lang('Converted') . "</small></a>";
                }

            })
            ->editColumn('grand_total', function ($quotation) use ($currency) {
                return "<span class='float-right'>" . decimalPlace($quotation->grand_total, $currency) . "</span>";
            })
            ->addColumn('action', function ($quotation) {
                return '<div class="dropdown text-center">'
                . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                . '<i class="mdi mdi-chevron-down"></i></button>'
                . '<div class="dropdown-menu">'
                . '<a class="dropdown-item" href="' . action('QuotationController@edit', $quotation->id) . '"><i class="ti-pencil-alt"></i> ' . _lang('Edit') . '</a></li>'
                . '<a class="dropdown-item" href="' . action('QuotationController@show', $quotation->id) . '"><i class="ti-eye"></i> ' . _lang('View') . '</a></li>'
                . '<a class="dropdown-item" href="#" onclick="convertirAFactura('.$quotation['id'].')"><i class="ti-exchange-vertical"></i> ' . _lang('Convert to Invoice') . '</a></li>'
                . '<a class="dropdown-item" href="' . action('QuotationController@exportToExcel', $quotation->id) . '"><img width="15" height="15" src="https://img.icons8.com/ios/50/1A1A1A/ms-excel.png" alt="ms-excel"/> ' . _lang('Export to Excel') . '</a></li>'
                . '<form action="' . action('QuotationController@destroy', $quotation['id']) . '" method="post">'
                . csrf_field()
                . '<input name="_method" type="hidden" value="DELETE">'
                . '<button class="button-link btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Delete') . '</button>'
                    . '</form>'
                    . '</div>'
                    . '</div>';
            })
            ->setRowId(function ($invoice) {
                return "row_" . $invoice->id;
            })
            ->rawColumns(['quotation_number', 'grand_total', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.quotation.create');
        } else {
            return view('backend.accounting.quotation.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request) {
    //     if (!$request->ajax()) {
    //         return redirect()->route('quotations.show', $quotation->id)->with('success', _lang('Quotation Created Sucessfully'));
    //     } else {
    //         return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Quotation Created Sucessfully'), 'data' => $quotation]);
    //     }
    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $quotation       = Quotation::find($id);
        $quotation_taxes = QuotationItemTax::where('quotation_id', $id)
            ->selectRaw('quotation_item_taxes.*,sum(quotation_item_taxes.amount) as tax_amount')
            ->groupBy('quotation_item_taxes.tax_id')
            ->get();

        return view('backend.accounting.quotation.view', compact('quotation', 'quotation_taxes', 'id'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $quotation = Quotation::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.quotation.edit', compact('quotation', 'id'));
        } else {
            return view('backend.accounting.quotation.modal.edit', compact('quotation', 'id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'quotation_number' => 'required|max:191',
            'client_id'        => 'required',
            'quotation_date'   => 'required',
            'product_id'       => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('quotations.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        $quotation                   = Quotation::find($id);
        $quotation->quotation_number = $request->input('quotation_number');
        $quotation->client_id        = $request->input('client_id');
        $quotation->quotation_date   = $request->input('quotation_date');
        $quotation->grand_total      = $request->product_total;
        // descomentar linea si precio ya no incluira IVA
        // $quotation->grand_total      = $request->product_total + $request->tax_total;
        $quotation->tax_total        = $request->input('tax_total');
        $quotation->note             = $request->input('note');

        $quotation->save();

        $taxes = Tax::all();

        //Update quotation item
        $quotationItem = QuotationItem::where("quotation_id", $id);
        $quotationItem->delete();

        $quotationItemTax = QuotationItemTax::where("quotation_id", $id);
        $quotationItemTax->delete();

        for ($i = 0; $i < count($request->product_id); $i++) {
            $quotationItem               = new quotationItem();
            $quotationItem->quotation_id = $quotation->id;
            $quotationItem->item_id      = $request->product_id[$i];
            $quotationItem->description  = $request->product_description[$i];
            $quotationItem->quantity     = $request->quantity[$i];
            $quotationItem->unit_cost    = $request->unit_cost[$i];
            $quotationItem->discount     = $request->discount[$i];
            $quotationItem->tax_amount   = $request->product_tax[$i];
            $quotationItem->sub_total    = $request->sub_total[$i];
            $quotationItem->delivery_time = $request->delivery_time[$i];
            $quotationItem->save();

            //Store Quotation Taxes
            if (isset($request->tax[$quotationItem->item_id])) {
                foreach ($request->tax[$quotationItem->item_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $quotationItemTax                    = new QuotationItemTax();
                    $quotationItemTax->quotation_id      = $quotationItem->quotation_id;
                    $quotationItemTax->quotation_item_id = $quotationItem->id;
                    $quotationItemTax->tax_id            = $tax->id;
                    $tax_type                            = $tax->type == 'percent' ? '%' : '';
                    $quotationItemTax->name              = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    // descomentar esta linea si precio ya no incluira IVA
                    // $quotationItemTax->amount            = $tax->type == 'percent' ? ($quotationItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $quotationItemTax->amount = $tax->type == 'percent' ? (($quotationItem->quantity*$request->product_price[$i]-$quotationItem->discount) / 100) * $tax->rate : $tax->rate;
                    $quotationItemTax->save();
                }
            }
        }

        DB::commit();

        if (!$request->ajax()) {
            return redirect()->route('quotations.show', $quotation->id)->with('success', _lang('Quotation updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Quotation updated sucessfully'), 'data' => $quotation]);
        }

    }

    /**
     * Generate PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_pdf($id) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $quotation               = Quotation::find($id);
        $data['quotation']       = $quotation;
        $data['quotation_taxes'] = QuotationItemTax::where('quotation_id', $id)
            ->selectRaw('quotation_item_taxes.*,sum(quotation_item_taxes.amount) as tax_amount')
            ->groupBy('quotation_item_taxes.tax_id')
            ->get();

        $pdf = PDF::loadView("backend.accounting.quotation.pdf_export", $data);
        $pdf->setWarnings(false);

        //return $pdf->stream();
        return $pdf->download("quotation_{$quotation->quotation_number}.pdf");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        DB::beginTransaction();

        $quotation = Quotation::find($id);
        $quotation->delete();

        $quotationItem = QuotationItem::where("quotation_id", $id);
        $quotationItem->delete();

        $quotationItemTax = QuotationItemTax::where('quotation_id', $id);
        $quotationItemTax->delete();

        DB::commit();

        return redirect()->route('quotations.index')->with('success', _lang('Quotation Removed Sucessfully'));
    }

    public function convert_invoice($quotation_id, Request $request) {

        // $tipoDocumento = TipoDocumento::find($request->tipodoc_id);
        $taxesInvoice = [];

        $invoice_number = get_invoice_number($request->tipodoc_id);

        if ($request->tipodoc_id == '03' || $request->tipodoc_id == '01') { // 03=CCF  |  01=fact consumidor
            $tx = Tax::where('trib_id', '20')->first();
        }else if ($request->tipodoc_id == '11'){ // 11=FEX
            $tx = Tax::where('trib_id', 'C3')->first();
        }

        if($tx != null) $taxesInvoice[] = $tx;

        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        DB::beginTransaction();

        $quotation = Quotation::where('id', $quotation_id)->where('status', 0)->first();

        if (!$quotation) {
            return back()->with('error', _lang('Sorry, Quotation is already converted to Invoice !'));
        }

        $invoice                 = new Invoice();
        $invoice->invoice_number = $invoice_number;
        // $invoice->invoice_number = get_option('invoice_prefix') . get_option('invoice_starting');
        $invoice->client_id      = $quotation->client_id;
        $invoice->invoice_date   = date('Y-m-d');
        $invoice->due_date       = date('Y-m-d');
        $invoice->grand_total    = $quotation->grand_total;
        $invoice->tax_total      = $quotation->tax_total;
        $invoice->paid           = 0;
        $invoice->status         = 'Unpaid';
        $invoice->note           = $quotation->note;
        $invoice->tipodoc_id     = $request->tipodoc_id;

        $invoice->save();

        $taxes = Tax::all();

        //Save Invoice Item
        foreach ($quotation->quotation_items as $quotation_item) {
            $invoiceItem              = new InvoiceItem();
            $invoiceItem->invoice_id  = $invoice->id;
            $invoiceItem->item_id     = $quotation_item->item_id;
            $invoiceItem->description = $quotation_item->description;
            $invoiceItem->quantity    = $quotation_item->quantity;
            $invoiceItem->unit_cost   = $quotation_item->unit_cost;
            $invoiceItem->discount    = $quotation_item->discount;
            $invoiceItem->tax_amount  = $quotation_item->tax_amount;
            $invoiceItem->sub_total   = $quotation_item->sub_total;
            $invoiceItem->save();

            $quotation_item->taxes = $taxesInvoice;

            //Store Invoice Taxes
            foreach ($quotation_item->taxes as $quotation_tax) {

                $tax = $taxes->find($quotation_tax->id);

                $invoiceItemTax                  = new InvoiceItemTax();
                $invoiceItemTax->invoice_id      = $invoiceItem->invoice_id;
                $invoiceItemTax->invoice_item_id = $invoiceItem->id;
                $invoiceItemTax->tax_id          = $tax->id;
                $tax_type                        = ($tax->type == 'percent' ? '%' : '');
                $invoiceItemTax->name            = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                $invoiceItemTax->amount          = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                $invoiceItemTax->save();
            }

            //Update Stock
            $stock = Stock::where("product_id", $invoiceItem->item_id)->first();
            if (!empty($stock)) {
                $stock->quantity = $stock->quantity - $invoiceItem->quantity;
                $stock->save();
            }

        }
        //Increment Invoice Starting number
        increment_invoice_number($request->tipodoc_id);

        $quotation->status     = 1;
        $quotation->invoice_id = $invoice->id;
        $quotation->save();

        DB::commit();

        return redirect('invoices/' . $invoice->id)->with('success', _lang('Quotation Converted Sucessfully'));

    }

    public function send_email(Request $request, $quotation_id = '') {
        if ($request->isMethod('get')) {
            $quotation = Quotation::find($quotation_id);

            $client_email = $quotation->client->contact_email;

            if ($request->ajax()) {
                return view('backend.accounting.quotation.modal.send_email', compact('client_email', 'quotation'));
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

            $currency = currency();

            $contact   = \App\Contact::where('contact_email', $contact_email)->first();
            $quotation = Quotation::find($request->quotation_id);

            if ($contact) {
                //Replace Paremeter
                $replace = array(
                    '{customer_name}'  => $contact->contact_name,
                    '{quotation_no}'   => $quotation->quotation_number,
                    '{quotation_date}' => $quotation->quotation_date,
                    '{grand_total}'    => decimalPlace($quotation->grand_total, $currency),
                    '{quotation_link}' => route('client.view_quotation', encrypt($quotation->id)),
                );
            }

            $mail          = new \stdClass();
            $mail->subject = $subject;
            $mail->body    = process_string($replace, $message);

            try {
                Mail::to($contact_email)->send(new GeneralMail($mail));
            } catch (\Exception$e) {
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





    public function download_example($id) {
        return response()->download(public_path('quotations/file.xlsx'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'quotation_number' => 'required|max:191',
            'client_id'        => 'required',
            'quotation_date'   => 'required'
        ], [
            'excelFile.required' => _lang('Selecciona un archivo de tipo .xslx'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('quotations.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        try {

            $taxesInvoice = [];

            // 03 comprobante de credito fiscal
            // 11 factura exportacion
            if ($request->incluir_iva == 'si') {
                $tx = Tax::where('trib_id', '20')->first();
                if($tx != null) $taxesInvoice[] = $tx;
            }


            $extension = $request->file('excelFile')->getClientOriginalExtension();
            $request->file('excelFile')->storeAs('quotation', 'file.' . $extension, 'public');

            $reader = new XlsxReader;
            $spreadsheet = $reader->load(Storage::path('public/quotation/file.' . $extension));
            $worksheet = $spreadsheet->getActiveSheet();
            $listado = $worksheet->toArray(null, true);

            DB::beginTransaction();
        $quotation                   = new Quotation();
        $quotation->quotation_number = $request->input('quotation_number');
        $quotation->client_id        = $request->input('client_id');
        $quotation->quotation_date   = $request->input('quotation_date');
        $quotation->grand_total      = 0;
        $quotation->tax_total        = 0;
        // $quotation->note             = $request->input('note');

        $grandTotal = 0;
        $taxTotal = 0;

        $quotation->save();

        $taxes = Tax::all();

            for ($i=1; $i < count($listado); $i++) {
                $items = Product::where('product_code', '=', strval(trim($listado[$i][1])))->get();

                if (count($items)==0) {
                    $items[] = new Product(['product_code'=>strval(trim($listado[$i][1])), 'description'=>strval(trim($listado[$i][1])).' | '.$listado[$i][0], 'product_price'=>0]);
                }

                foreach ($items as $index => $item) {
                    try { 
                        $stock = $item->item->stock()->quantity??0; 
                    } catch (\Throwable $th) { 
                        $stock = 0; 
                    }
                    $tiempoEntrega = _lang('Immediate');

                    // tiempo de entrega
                    if($item->id == null){
                        $tiempoEntrega = _lang('Not found');
                    }elseif($stock == 0){
                        $tiempoEntrega = _lang('Empty stock');
                    }else if($stock< $listado[$i][2]) {
                        $tiempoEntrega = $stock.' '._lang('Minor stock');
                    }

                    $quotationItem = new QuotationItem();
                    $quotationItem->quotation_id = $quotation->id;
                    $quotationItem->item_id      = $item->item->id??null;
                    $quotationItem->description  = $item->description;
                    $quotationItem->quantity     = $listado[$i][2];
                    $quotationItem->unit_cost    = 0;
                    $quotationItem->discount     = 0;
                    $quotationItem->tax_amount   = 0;
                    
                    $quotationItem->company_id   = company_id();
                    $quotationItem->delivery_time = $tiempoEntrega;
                    $quotationItem->save();


                    $quotationItem->taxes = $taxesInvoice;
                    $quotationItemTaxAmount = 0;

                    $unit_cost = $item->product_price;
                    
                    $fixedTaxAmount = 0;
                    foreach ($quotationItem->taxes as $quotation_tax) {
                        $impuesto = $taxes->find($quotation_tax->id);
                        $unit_cost += $impuesto->type  == 'percent' ? ($impuesto->rate/100)*$item->product_price : 0;
                        $unit_cost = round($unit_cost, 2);
                        $fixedTaxAmount += $impuesto->type  == 'percent' ? 0 : $impuesto->rate;
                    }

                    //Store Invoice Taxes
                    foreach ($quotationItem->taxes as $quotation_tax) {
                        $tax = $taxes->find($quotation_tax->id);
                        $quotationItemTax                    = new QuotationItemTax();
                        $quotationItemTax->quotation_id      = $quotationItem->quotation_id;
                        $quotationItemTax->quotation_item_id = $quotationItem->id;
                        $quotationItemTax->tax_id            = $tax->id;

                        $tax_type                            = ($tax->type == 'percent' ? '%' : '');
                        $quotationItemTax->name              = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                        $quotationItemTax->amount            = $tax->type == 'percent' ? (($quotationItem->quantity*$item->product_price-$quotationItem->discount) / 100) * $tax->rate : $tax->rate;
                        $quotationItemTax->save();
                        $taxTotal += $quotationItemTax->amount;
                        $quotationItemTaxAmount += $quotationItemTax->amount;
                    }

                    unset($quotationItem->taxes);

                    
                    $quotationItem->sub_total    = (($listado[$i][2]*$unit_cost)+$fixedTaxAmount)-$quotationItem->discount;
                    $quotationItem->tax_amount   = $quotationItemTaxAmount;
                    $quotationItem->unit_cost    = $unit_cost;
                    $grandTotal                 += $quotationItem->sub_total;
                    $quotationItem->save();
                }
            }

        $quotation->tax_total        = $taxTotal;
        $quotation->grand_total      = $grandTotal;
        $quotation->save();

        if ($grandTotal == 0) {
            return redirect()->route('quotations.create')
                    ->withErrors(['Sorry, Error Occured !', 'Asegúrese que el archivo tenga el formato correcto.'])
                    ->withInput();
        }

        //Increment quotation Starting number
        increment_quotation_number();

        DB::commit();

        Storage::disk('public')->delete('quotation/file.' . $extension);

        if (!$request->ajax()) {
            return redirect()->route('quotations.show', $quotation->id)->with('success', _lang('Quotation Created Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Quotation Created Sucessfully'), 'data' => $quotation]);
        }
        // return response()->download(public_path('quotations/uploads/file.xlsx'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('quotations.create')
                    ->withErrors(['Sorry, Error Occured !', 'Asegúrese que el archivo tenga el formato correcto.'])
                    ->withInput();
            //$this->CloseModal();
            $error = $e;
            // Storage::disk('public')->delete('quotation/file.' . $extension);
        }
    }

    public function exportToExcel($id)
    {
        try {
            $quotation = Quotation::find($id);

            $listado = $quotation->quotation_items;

            $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (range('A', 'J') as $column) {
            $columnDimension = $sheet->getColumnDimension($column);
            $columnDimension->setAutoSize(true);
        }


        $sheet->setCellValue('A1', 'No. Cotización:');
        $sheet->setCellValue('B1', $quotation->quotation_number);

        $sheet->setCellValue('A2', 'Fecha de cotización:');
        $sheet->setCellValue('B2', $quotation->quotation_date);

        $sheet->setCellValue('A3', 'Cliente:');
        $sheet->setCellValue('B3', $quotation->client->company_name);
        $sheet->setCellValue('A4', 'Contacto del cliente:');
        $sheet->setCellValue('B4', $quotation->client->contact_name.' ('.$quotation->client->contact_phone.')');

        $sheet->setCellValue('A6', 'Marca');
        $sheet->setCellValue('B6', 'Modelo');
        $sheet->setCellValue('C6', 'No. Lawson');
        $sheet->setCellValue('D6', 'No parte');
        $sheet->setCellValue('E6', 'Descripcion');
        $sheet->setCellValue('F6', 'Cantidad');
        $sheet->setCellValue('G6', 'Precio unitario');
        $sheet->setCellValue('H6', 'Original');
        $sheet->setCellValue('I6', 'Generico');
        $sheet->setCellValue('J6', 'Tiempo de entrega');

        for ($i=0; $i < count($listado); $i++) {
            $indexRow = $i+7;

            // $taxs = $listado[$i]->taxes()->get();

            // $taxsVals = 0;

            // foreach ($taxs as $key => $value) {
            //     $taxsVals += $value->tax->type == 'percent' ? ($listado[$i]->unit_cost / 100) * $value->tax->rate : $value->tax->rate;
            // }

            $brandName = $listado[$i]->item->product->brand;

            $sheet->setCellValue('A' . $indexRow, $listado[$i]->item->product->brand->brand_name??'');
            $sheet->setCellValue('B' . $indexRow, $listado[$i]->item->product->model);
            $sheet->setCellValue('C' . $indexRow, $listado[$i]->item->product->lawson_number);
            $sheet->setCellValue('D' . $indexRow, $listado[$i]->item->product->product_code);
            $sheet->setCellValue('E' . $indexRow, $listado[$i]->description);
            $sheet->setCellValue('F' . $indexRow, $listado[$i]->quantity);
            // $sheet->setCellValue('E' . $indexRow, $listado[$i]->unit_cost+$taxsVals);
            $sheet->setCellValue('G' . $indexRow, round(floatval($listado[$i]->unit_cost), 2));
            $sheet->setCellValue('H' . $indexRow, $listado[$i]->item->product->original);
            $sheet->setCellValue('I' . $indexRow, $listado[$i]->item->product->generic);
            $sheet->setCellValue('J' . $indexRow, $listado[$i]->delivery_time);
        }

        $writer = new Xlsx($spreadsheet);

        $sheet->getStyle("A6:J6")->getFont()->setBold(true);

        $sheet->getStyle("A1")->getFont()->setBold(true);
        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->getStyle("A4")->getFont()->setBold(true);

        unlink(public_path('quotations/uploads/file.xlsx'));

        $writer->save(public_path('quotations/uploads/file.xlsx'));
        return response()->download(public_path('quotations/uploads/file.xlsx'));

        } catch (Exception $e) {
            //$this->CloseModal();
            $error = $e;
            // Storage::disk('public')->delete('quotation/file.' . $extension);
        }
    }
}