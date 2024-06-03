<?php

namespace App\Http\Controllers;

use App\Item;
use App\Stock;
use Validator;
use DataTables;
use App\Product;
use App\OrderNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return view('backend.accounting.order_notes.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){

        $contador = OrderNote::count();
        $contador = ( $contador == 0 ) ? 1 : $contador;

        $num_order = str_pad($contador, 7, '0', STR_PAD_LEFT);

        if( !$request->ajax() ){
            return view('backend.accounting.order_notes.create', compact(['num_order']));
        }
        else{
            return view('backend.accounting.order_notes.modal.create', compact(['num_order']));
        }
    }

    public function get_table_data(Request $request){

        $currency = currency();

        $orders = OrderNote::with('client')
            ->orderBy('order_notes.id', 'desc');

        return Datatables::eloquent($orders)
            ->filter(function ($query) use ($request) {

                if ($request->has('order_number')) {
                    $query->where('order_number', 'like', "%{$request->get('order_number')}%");
                }

                if ($request->has('client_id')) {
                    $query->where('client_id', $request->get('client_id'));
                }

                if ($request->has('status')) {
                    $query->whereIn('status', json_decode($request->get('status')));
                }

                if ($request->has('date_range')) {
                    $date_range = explode(" - ", $request->get('date_range'));
                    $query->whereBetween('deliver_date_contract', [$date_range[0], $date_range[1]]);
                }
            })
            ->editColumn('deliver_date_contract', function ($order) {
                return Carbon::createFromFormat('Y-m-d', $order->deliver_date_contract)->format('d-m-Y');
            })
            ->editColumn('status', function ($order) {
                
                if( $order->status == 0 ){
                    return '<span class="badge badge-danger">Anulada</span>';
                }
                else if( $order->status == 1 ){
                    return '<span class="badge badge-warning">Ingresada</span>';
                }
                else if( $order->status == 2 ){
                    return '<span class="badge badge-success">Procesada</span>';
                }
            })
            ->addColumn('action', function ($order) {
                $actionHtml = '<div class="dropdown text-center">'
                    . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                    . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                    . '<div class="dropdown-menu">'
                    . '<a class="dropdown-item" href="' . action('OrderNoteController@show', $order->id) . '" data-title="' . _lang('View Note') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('View') . '</a>';

                $actionHtml .= ($order->status == 1 ? '<a class="dropdown-item ajax-modal" href="' . action('OrderNoteController@edit', $order->id) . '" data-title="' . _lang('Edit Note') . '" data-fullscreen="true"><i class="ti-pencil"></i> ' . _lang('Edit') . '</a>' : '')
                    .($order->status == 2 && $order->invoiced == 0 ? '<a class="dropdown-item" href="' . action('InvoiceController@create', ['id_nota_p' => $order->id]) . '" data-title="' . _lang('Nota Remisión') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Nota Remisión') . '</a>' : '')
                    .($order->invoiced > 0 ? '<a class="dropdown-item" href="' . action('InvoiceController@show', $order->invoiced) . '" data-title="' . _lang('Ver Nota Remisión') . '" data-fullscreen="true"><i class="ti-file"></i> ' . _lang('Ver Nota Remisión') . '</a>' : '')
                    . ($order->status == 1 ? '<button class="button-link" onclick="modalDescargo('. $order->id .');" type="button"><i class="ti-check"></i> ' . _lang('Procesar nota') . '</button>' : '')
                    .($order->status == 1 ? '<button class="button-link" onclick="modalAnulacion('. $order->id .');" type="button"><i class="ti-na"></i> ' . _lang('Anular') . '</button>' : '')
                    .'<form action="' . action('OrderNoteController@destroy', $order->id) . '" method="post" style="display: inline;">'
                    . csrf_field()
                    . method_field('DELETE')
                    . ($order->status == 0 ? '<button class="btn btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Eliminar') . '</button>' : '')
                    .'</form>'
                    . '</div>'
                    . '</div>';

                return $actionHtml;
            })

            ->setRowId(function ($order) {
                return "row_" . $order->id;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){


        $validator = Validator::make($request->all(), [
            'sales_company'         => 'required',
            'order_number'          => 'required|max:191',
            'client_id'             => 'required',
            'num_public_tender'     => 'required',
            'num_contract'          => 'required',
            'deliver_date_contract' => 'required',
            'product_id'            => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('order_notes.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $details = [];

        $orderNote                          = new OrderNote();
        $orderNote->order_number            = $request->order_number;
        $orderNote->sales_company           = $request->sales_company;
        $orderNote->client_id               = $request->client_id;
        $orderNote->num_public_tender       = $request->num_public_tender;
        $orderNote->num_contract            = $request->num_contract;
        $orderNote->deliver_date_contract   = $request->deliver_date_contract;

        for( $i = 0; $i < count($request->product_id); $i++ ){

            $details[] = [
                'line'                      => $request->line[$i],
                'payment_analysis'          => $request->payment_analysis[$i],
                'code_product_institution'  => $request->code_product_institution[$i],
                'product_id'                => $request->product_id[$i],
                'product_description'       => $request->product_description[$i],
                'quantity'                  => $request->quantity[$i],
                'samples'                   => $request->samples[$i],
                'delivery_number'           => $request->delivery_number[$i],
                'product_brand'             => $request->product_brand[$i],
                'product_origin'            => $request->product_origin[$i],
                'offered_expiry'            => $request->offered_expiry[$i],
                'product_lot'               => $request->product_lot[$i],
                'expires'                   => $request->expires[$i],
                'manufacture'               => $request->manufacture[$i],
                'analysis_certificate'      => $request->analysis_certificate[$i],
                'product_delivery_company'  => $request->product_delivery_company[$i],
                'product_stock'             => $request->product_stock[$i],
            ];
            
            $stock  = Stock::whereRaw("product_id = {$request->product_id[$i]} and company_id = " . company_id())->first();
            $item   = Item::findOrFail($request->product_id[$i]);

            if( $item->item_type === 'product' ){
                
                if( $stock != null && $stock->quantity < $request->quantity[$i] ){
                    if( $request->ajax() ){
                        return response()->json(['result' => 'error', 'message' => 'Stock máximo alcanzado']);
                    }
                    else{
                        return redirect()->route('invoices.create')
                            ->withErrors(['Sorry, Error Occured !', 'Stock máximo alcanzado.'])
                            ->withInput();
                    }
                }
            }

            if( $stock ){
                $stock->quantity = $stock->quantity - $request->quantity[$i] - $request->samples[$i];
                $stock->save();
            }
        }

        $orderNote->note    = $request->note;
        $orderNote->details = $details;
        $orderNote->save();

        if( !$request->ajax() ){
            return redirect()->route('order_notes.show', $orderNote->id)->with('success', _lang('Nota de pedido generada exitosamente '));
        }
        else{
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Nota de pedido generada exitosamente '), 'data' => $orderNote]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( Request $request, $id){

        $order = OrderNote::with('client')->find($id);

        $orderDetails = json_decode( json_encode($order->details) );

        $productIds = array_column($orderDetails, 'product_id');

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return view('backend.accounting.order_notes.view', compact('order', 'orderDetails', 'products'));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id){

        $order = OrderNote::with('client')->find($id);

        if( !$request->ajax() ){
            return view('backend.accounting.order_notes.edit', compact('id', 'order'));
        }
        else {
            return view('backend.accounting.order_notes.modal.edit', compact('id', 'order'));
        }
    }

    public function print(Request $request, $id){

        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $order = OrderNote::with('client')->find($id);

        $orderDetails = json_decode( json_encode($order->details) );

        $productIds = array_column($orderDetails, 'product_id');

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $data['order']          = $order;
        $data['orderDetails']   = $orderDetails;
        $data['products']       = $products;

        $pdf = PDF::loadView('backend.accounting.order_notes.print', $data);
        $customPaper = array(0, 0, 1275, 1650);
        $pdf->setPaper('letter', 'landscape');

        $pdf->setWarnings(false);

        return $pdf->stream('factura.pdf', array('Attachment' => 0));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'sales_company'         => 'required',
            'order_number'          => 'required|max:191',
            'client_id'             => 'required',
            'num_public_tender'     => 'required',
            'num_contract'          => 'required',
            'deliver_date_contract' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('order_notes.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $orderNote = OrderNote::find($id);

        $orderNote->order_number            = $request->order_number;
        $orderNote->sales_company           = $request->sales_company;
        $orderNote->client_id               = $request->client_id;
        $orderNote->num_public_tender       = $request->num_public_tender;
        $orderNote->num_contract            = $request->num_contract;
        $orderNote->deliver_date_contract   = $request->deliver_date_contract;
        $orderNote->save();

        if( !$request->ajax() ){
            return redirect()->route('order_notes.show', $orderNote->id)->with('success', _lang('Information has been updated sucessfully'));
        }
        else{
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Information has been updated sucessfully')]);
        }
    }

    public function updateStatus(Request $request){


        $id = $request->id;
        $order = OrderNote::find($id);

        if( !$order ){
            return response()->json(['result' => 'error', 'action' => 'update', 'message' => _lang('Oops! Nota no encontrada')]);
        }

        $order->status = 2;
        $order->save();

        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Information has been updated sucessfully')]);
    }


    public function cancelNote(Request $request){


        $id = $request->id;
        $order = OrderNote::find($id);

        if( !$order ){
            return response()->json(['result' => 'error', 'action' => 'update', 'message' => _lang('Oops! Nota no encontrada')]);
        }

        $order->status = 0;

        $orderDetails = json_decode( json_encode($order->details) );

        foreach( $orderDetails as $p_item ){
            $items = floatval( $p_item->quantity ) + floatval( $p_item->samples );

            update_stock($p_item->product_id, $items, '+');
        }

        $order->save();

        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Information has been updated sucessfully')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        $order = OrderNote::find($id);

        if( !$order ){
            return back()->with('success', _lang('Oops! Nota no encontrada'));
        }

        $order->delete();

        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }
}
