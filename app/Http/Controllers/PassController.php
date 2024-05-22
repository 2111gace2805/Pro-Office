<?php

namespace App\Http\Controllers;

use App\Company;
use App\Stock;
use App\Transfer;
use App\TransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PassController extends Controller
{
    public function index($send) {

        $recibido = ['company_receive', 'company_send'];

        $items = Transfer::join('companies as c', 'c.id', isset($recibido[!$send]) ? $recibido[!$send] : null);


        if(!$send){
            $items = $items->where('estado', 1);
        }
        $items = $items->get();

        return view('backend.accounting.transfers.list', compact('items', 'send'));
    }

    public function received($send)
{
    // Asegúrate de que 'company' en la sesión sea realmente el ID de la sucursal
    $sucursal_id = Session::get('company.id');

    // Imprime el valor de $sucursal_id para verificar
    // dd($sucursal_id);

    $items = Transfer::join('companies as c_receive', 'c_receive.id', '=', 'transfers.company_receive')
        ->join('companies as c_send', 'c_send.id', '=', 'transfers.company_send')
        ->where('transfers.estado', $send)
       
        ->where(function ($query) use ($sucursal_id) {
            $query->where('c_receive.id', $sucursal_id)
                ->orWhere('c_send.id', $sucursal_id);
        })
        ->get(['transfers.*', 'c_receive.company_name as company_receive_name', 'c_send.company_name']);

    return view('backend.accounting.transfers.list', compact('items', 'send'));
}

    public function show($transfer, $send){
        $info = Transfer::with(['sendingCompany', 'receivingCompany'])
            ->where('transfer_id', $transfer)
            ->firstOrFail();
    
            $items = TransferItem::join('items as i', 'transfer_items.product_id', '=', 'i.id')
            ->where('transfer_items.transfer_id', $transfer)
            ->select('i.item_name', 'transfer_items.quantity', 'transfer_items.product_recieve')
            ->get();
        // Cargar las relaciones para cada ítem individualmente
        // $items->each(function ($item) {
        //     $item->load('product.item');
        // });
    
        return view('backend.accounting.transfers.detail', compact('info', 'items'));
    }
    

    public function create(){
        $companies = Company::where('id', '!=', Session::get('company')->id)->get();
        return view('backend.accounting.transfers.create', compact('companies'));
    }

    public function incoming(){
        $companies = Company::where('id', '!=', Session::get('company')->id)->get();
        return view('backend.accounting.transfers.incoming', compact('companies'));
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'shipping_cost'  => 'nullable|numeric',
            'sub_total.*'    => 'required|numeric',
            'product_id'     => 'required',
            'company'     => 'required',
            'transfer_date'     => 'required',
            'reference'     => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('purchase_orders.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        $company = company_id();
        $transfer = new Transfer();
        $transfer->transfer_code = $request->input('reference');
        $transfer->transfer_datesend = $request->input('transfer_date');
        $transfer->user_send = Auth::user()->id;
        $transfer->company_send = $company;
        $transfer->company_receive = $request->input('company');
        $transfer->note = $request->input('note');

        $transfer->save();
        $items = [];

        //Save Purcahse item
        for ($i = 0; $i < count($request->product_id); $i++) {
            $items[] = array(
                'transfer_id' => $transfer->transfer_id,
                'product_id' => $request->product_id[$i],
                'quantity' => $request->quantity[$i],
                'unit_cost'=> $request->unit_cost[$i]
            );

            //Update Stock if Order Status is received
            if ($request->input('order_status') == '3') {
                $stock           = Stock::where("product_id", $request->product_id[$i])->where('company_id', $company)->first();
                $stock->quantity = $stock->quantity - $request->quantity[$i];
                $stock->save();
            }
        }

        TransferItem::insert($items);
        DB::commit();

        if (!$request->ajax()) {
            return redirect()->route('passes.index', $transfer->transfer_id)->with('success', _lang('Transfer Created Sucessfully'));
        }
        
        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Transfer Created Sucessfully'), 'data' => $transfer]);
    }

    public function ItemsReceived(Request $request){
        $info = Transfer::where('company_send', $request->company)
        ->where('transfer_code', $request->reference)->first();
        
        $items = TransferItem::where('transfer_id', $info->transfer_id ?? 0)
        ->join('products as p', 'p.id', 'transfer_items.product_id')
        ->join('items as i', 'i.id', 'p.item_id')->get();

        return view('backend.accounting.transfers.incoming-data', compact('info', 'items'));
    }

    public function SaveItemsReceived(Request $request){
        
        return DB::transaction(function() use($request){

            $company = company_id();
    
            foreach($request->products as $key => $item){
                $stock = Stock::where('product_id', $key)->where('company_id', $company)->first();
    
                if(isset($stock->id)){
                    $stock->quantity += $item;
                }else{
                    $stock             = new Stock();
                    $stock->product_id = $key;
                    $stock->quantity   = $item;
                    $stock->company_id = $company;
                }
                $stock->save();
    
                TransferItem::where('product_id', $key)->where('transfer_id', $request->transfer)
                ->update(['product_recieve' => $item]);
            }
            
            Transfer::where('transfer_id', $request->transfer)
            ->update(['estado' => 1, 'transfer_daterecive' => date('Y-m-d')]);
            
            return redirect()->route('passes.index', 0)->with('success', _lang('Transfer Saved Sucessfully'));
        });
    }
}
