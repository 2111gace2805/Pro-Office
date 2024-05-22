<?php

namespace App\Http\Controllers;

use App\Cash;
use App\CashMovement;
use App\Invoice;
use App\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use DataTables;
use Illuminate\Support\Facades\Auth;

class CashMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $cash_movements = CashMovement::orderBy("cashmov_id", "desc")->get();
        return view('backend.accounting.cash.cash_movement.list', compact('cash_movements'));
    }

    public function get_table_data(Request $request) {
        $currency = currency();

        $items = CashMovement::with(['user', 'cash'])->orderBy('cashmov_id', 'desc');

        $DATA =  Datatables::eloquent($items)
            ->filter(function ($query) use ($request) {
                if ($request->has('cashmov_type')) {
                    $query->whereIn('cashmov_type', $request->cashmov_type);
                }
                if ($request->has('company_id')) {
                    $query->where('company_id', $request->get('company_id'));
                }
                if ($request->has('date_range')) {
                    $date_range = explode(" - ", $request->get('date_range'));
                    $query->whereBetween('cashmov_date', [$date_range[0], $date_range[1]]);
                }
            })
            ->setRowId(function ($item) {
                return "row_" . $item->cashmov_id;
            })
            ->editColumn('cashmov_type', function ($cash_movement) {
                return strtoupper(_lang($cash_movement->cashmov_type));
            })
            ->addColumn('action', function ($cash_movement) {
                $actions = '<div class="dropdown text-center">'
                . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                . '<div class="dropdown-menu">';
                if ($cash_movement->cashmov_type == 'Closing') {
                    $actions .= '<a class="dropdown-item" href="' . action('CashMovementController@show', $cash_movement->cashmov_id) . '" data-title="' . _lang('View movement') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('View') . '</a>';
                }
                if ($cash_movement->cashmov_type == 'In' || $cash_movement->cashmov_type == 'Out') {
                    $actions .= '<a href="' . action('CashMovementController@edit', $cash_movement['cashmov_id']) . '" class="dropdown-item ajax-modal" data-title="'._lang("Edit").'"><i class="ti-pencil-alt"></i>' . _lang('Edit') . '</a> ';
                }

                $actions    .= '</div>'. '</div>';
                

                return $actions;
            })
            ->rawColumns(['cashmov_type', 'action'])
            ->make(true);

        return $DATA;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $cashmov_type = $request->cashmov_type;

        $already_closed = DB::table('cash_movements')->whereRaw("cashmov_type = 'Closing' and cashmov_date = '".date('Y-m-d')."'")->exists();
        if (!$request->ajax()) {
            return view('backend.accounting.cash.cash_movement.create', compact('cashmov_type', 'already_closed'));
        } else {
            return view('backend.accounting.cash.cash_movement.modal.create_in_out');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required',
            'cash_id' => 'required',
            'cashmov_value' => 'required|numeric',
        ], [
            'company_id.required' => 'El campo sucursal es requerido.',
            'cash_id.required' => 'El campo Caja es requerido.',
            'cashmov_value.required' => 'El campo Value es requerido.',
            'cashmov_value.numeric' => 'El campo Value debe ser numérico.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('cash_movement.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $cash_movement = new CashMovement();
        $cash_movement->cashmov_concept = $request->input('cashmov_concept')??'';
        $cash_movement->cashmov_type = $request->input('cashmov_type');
        $cash_movement->cashmov_value = $request->cashmov_value??Cash::find($request->cash_id)->cash_value;
        $cash_movement->cash_id = $request->cash_id;
        $cash_movement->user_id = Auth::user()->id;
        $cash_movement->cashmov_date = date('Y-m-d');
        $cash_movement->cashmov_time = date('H:i:s');
        $cash_movement->company_id = $request->company_id;
        $cash_movement->save();

        $cash = get_cash();
        if ($cash_movement->cashmov_type == 'Closing') {
            $cash->cash_value = 0;
            $cash->save();
        }
        if ($cash_movement->cashmov_type == 'In') {
            $cash->cash_value += $cash_movement->cashmov_value;
            $cash->save();
        }
        if ($cash_movement->cashmov_type == 'Out') {
            $cash->cash_value -= $cash_movement->cashmov_value;
            $cash->save();
        }



        if (!$request->ajax()) {
            return redirect()->route('cash_movement.index')->with('success', _lang('Saved sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $cash_movement]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $cash_movement = CashMovement::find($id);
        // if (!$request->ajax()) {
        //     return view('backend.accounting.cash.cash_movement.edit', compact('cash_movement', 'id'));
        // } else {
            return view('backend.accounting.cash.cash_movement.modal.edit_in_out', compact('cash_movement', 'id'));
        // }

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
            'company_id' => 'required',
            'cash_id' => 'required',
            'cashmov_value' => 'required|numeric',
        ], [
            'company_id.required' => 'El campo sucursal es requerido.',
            'cash_id.required' => 'El campo Caja es requerido.',
            'cashmov_value.required' => 'El campo Value es requerido.',
            'cashmov_value.numeric' => 'El campo Value debe ser numérico.',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('cash_movements.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $cash_movement = CashMovement::find($id);
        $cash = get_cash();
        if ($cash_movement->cashmov_type == 'In') {
            $cash->cash_value -= $cash_movement->cashmov_value;
            $cash->save();
        }
        if ($cash_movement->cashmov_type == 'Out') {
            $cash->cash_value += $cash_movement->cashmov_value;
            $cash->save();
        }
        $cash->save();
        $cash_movement->cashmov_concept = $request->input('cashmov_concept')??'';
        $cash_movement->cashmov_type = $request->input('cashmov_type');
        $cash_movement->cashmov_value = $request->cashmov_value??Cash::find($request->cash_id)->cash_value;
        $cash_movement->cash_id = $request->cash_id;
        $cash_movement->user_id = Auth::user()->id;
        $cash_movement->cashmov_date = date('Y-m-d');
        $cash_movement->cashmov_time = date('H:i:s');
        $cash_movement->company_id = $request->company_id;
        $cash_movement->save();

        $cash = get_cash();
        if ($cash_movement->cashmov_type == 'In') {
            $cash->cash_value += $cash_movement->cashmov_value;
            $cash->save();
        }
        if ($cash_movement->cashmov_type == 'Out') {
            $cash->cash_value -= $cash_movement->cashmov_value;
            $cash->save();
        }

        if (!$request->ajax()) {
            return redirect()->route('cash_movement.index')->with('success', _lang('Updated sucessfully'));
        } 
        
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $cash_movement]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $codigo_arancelario = CashMovement::find($id);
        $codigo_arancelario->codaran_status = 'Deleted';
        $codigo_arancelario->save();
        return redirect('cash_movements')->with('success', _lang('Updated sucessfully'));
    }

    public function show(Request $request, $id) {
        $cash_movement = CashMovement::find($id);
        $cashmov_type = $cash_movement->cashmov_type;
        if (!$request->ajax()) {
            return view('backend.accounting.cash.cash_movement.show', compact('cashmov_type', 'cash_movement'));
        } else {
            return view('backend.accounting.cash.cash_movement.modal.show', compact('cashmov_type', 'cash_movement'));
        }
    }


    public function get_table_data_closing_cash(Request $request) {
        $currency = currency();

        $items = InvoiceItem::from('invoice_items as ii')->join('invoices as i', 'ii.invoice_id', '=', 'i.id')->join('items as it', 'it.id', '=', 'ii.item_id')->join('products as prod', 'prod.item_id', '=', 'it.id')->whereRaw("i.status != 'Canceled' and i.forp_id = '01'")->selectRaw("ii.id, ii.quantity, ii.description, (ii.sub_total-ii.discount) as sub_total");

        $DATA =  Datatables::eloquent($items)
            ->filter(function ($query) use ($request) {
                if ($request->has('invoice_date')) {
                    $query->where('i.invoice_date', $request->get('invoice_date'));
                }
                if ($request->has('prodgrp_id')) {
                    $query->where('prod.prodgrp_id', $request->get('prodgrp_id'));
                }
                if ($request->has('company_id')) {
                    $query->where('i.company_id', $request->get('company_id'));
                }
            })
            ->setRowId(function ($item) {
                return "row_" . $item->id;
            })
            ->make(true);

        return $DATA;
    }
    
    public function get_table_data_closing_cash_invoices(Request $request) {
        $currency = currency();

        $items = Invoice::join('contacts as c', 'invoices.client_id', '=', 'c.id')->whereRaw("status != 'Canceled' and forp_id = '01'")->selectRaw("invoices.*, c.company_name");

        $DATA =  Datatables::eloquent($items)
            ->filter(function ($query) use ($request) {
                if ($request->has('invoice_date')) {
                    $query->where('invoice_date', $request->get('invoice_date'));
                }
                if ($request->has('company_id')) {
                    $query->where('invoices.company_id', $request->get('company_id'));
                }
            })
            ->setRowId(function ($item) {
                return "row_" . $item->id;
            })
            ->make(true);

        return $DATA;
    }


    public function get_resumen_ventas(Request $request){
        $request = $request;
        $sales_groups = DB::select("select prod.prodgrp_id, (select pg.prodgrp_name from product_groups pg where pg.prodgrp_id = prod.prodgrp_id) as prodgrp_name, count(DISTINCT(i.id)) invoices, sum(ii.sub_total-ii.discount) as subtotal, sum(ii.quantity) as quantity from invoice_items ii join invoices i on ii.invoice_id = i.id join items it on ii.item_id = it.id join products prod on it.id = prod.item_id where i.invoice_date = '$request->invoice_date' and i.status != 'Canceled' and i.forp_id = '01' and i.company_id = $request->company_id GROUP BY prod.prodgrp_id");
        $sales_pay_way =  DB::select("select i.forp_id, fp.forp_nombre, sum(i.grand_total) as grand_total from invoices i join forma_pago fp on i.forp_id = fp.forp_id where i.invoice_date = '$request->invoice_date' and i.status != 'Canceled' and i.company_id = $request->company_id GROUP BY i.forp_id");
        return compact('sales_groups', 'sales_pay_way');
    }
}
