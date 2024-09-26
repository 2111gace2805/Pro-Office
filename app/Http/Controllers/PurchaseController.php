<?php

namespace App\Http\Controllers;

use App\Item;
use App\Purchase;
use App\PurchaseOrderItem;
use App\PurchaseOrderItemTax;
use App\Stock;
use App\Supplier;
use App\Tax;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('backend.accounting.purchase_order.list');
    }

    public function get_table_data(Request $request) {
        $currency = currency();

        $purchases = Purchase::with('supplier')->where('company_id', Session::get('company')->id)
            ->orderBy("id", "desc");

        return DataTables::eloquent($purchases)
            ->filter(function ($query) use ($request) {
                if ($request->has('supplier_id')) {
                    $query->where('supplier_id', $request->get('supplier_id'));
                }

                if ($request->has('order_status')) {
                    $query->whereIn('order_status', json_decode($request->get('order_status')));
                }

                if ($request->has('payment_status')) {
                    $query->whereIn('payment_status', json_decode($request->get('payment_status')));
                }

                if ($request->has('date_range')) {
                    $date_range = explode(" - ", $request->get('date_range'));
                    $query->whereBetween('order_date', [$date_range[0], $date_range[1]]);
                }
            })

            ->editColumn('order_status', function ($purchase) {
                if ($purchase->order_status == 1) {
                    return '<span class="badge badge-info">' . _lang('Ordered') . '</span>';
                } else if ($purchase->order_status == 2) {
                    return '<span class="badge badge-danger">' . _lang('Pending') . '</span>';
                } else if ($purchase->order_status == 3) {
                    return '<span class="badge badge-success">' . _lang('Received') . '</span>';
                }
            })
            ->editColumn('grand_total', function ($purchase) use ($currency) {
                return '<span class="float-right">' . decimalPlace($purchase->grand_total, $currency) . '</span>';
            })
            ->editColumn('paid', function ($purchase) use ($currency) {
                return '<span class="float-right">' . decimalPlace($purchase->paid, $currency) . '</span>';
            })
            ->editColumn('payment_status', function ($purchase) {
                if ($purchase->payment_status == 0) {
                    return '<span class="badge badge-danger">' . _lang('Due') . '</span>';
                } else {
                    return '<span class="badge badge-success">' . _lang('Paid') . '</span>';
                }
            })
            ->addColumn('action', function ($purchase) {
                $html = '<div class="dropdown text-center">'
                . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                . '<div class="dropdown-menu">';
                
                if ($purchase['order_status'] != 3) {
                    $html .= '<a class="dropdown-item" href="' . action('PurchaseController@edit', $purchase->id) . '"><i class="ti-pencil-alt"></i> ' . _lang('Edit') . '</a>';
                }else{
                    $html .= '<a class="dropdown-item" href="' . route('purchase_orders.editOrder', $purchase->id) . '"><i class="ti-pencil-alt"></i> ' . _lang('Edit') . '</a>';
                }
                
                $html .= '<a class="dropdown-item" href="' . action('PurchaseController@show', $purchase->id) . '" data-title="' . _lang('View Invoice') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('View') . '</a>'
                . '<a href="' . route('purchase_orders.create_payment', $purchase->id) . '" data-title="' . _lang('Make Payment') . '" class="dropdown-item ajax-modal"><i class="ti-credit-card"></i> ' . _lang('Make Payment') . '</a>'
                . '<a href="' . route('purchase_orders.view_payment', $purchase->id) . '" data-title="' . _lang('View Payments') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="ti-credit-card"></i> ' . _lang('View Payment History') . '</a>'
                . '<form action="' . action('PurchaseController@destroy', $purchase['id']) . '" method="post">'
                .csrf_field()
                . '<input name="_method" type="hidden" value="DELETE">'
                . '<button class="button-link btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Delete') . '</button>'
                    . '</form>'
                    . '</div>'
                    . '</div>';

                return $html;
            })
            ->setRowId(function ($purchase) {
                return "row_" . $purchase->id;
            })
            ->rawColumns(['action', 'grand_total', 'paid', 'order_status', 'payment_status'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.purchase_order.create');
        } else {
            return view('backend.accounting.purchase_order.modal.create');
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
            'order_date'     => 'required',
            'supplier_id'    => 'required',
            'order_status'   => 'required',
            'order_discount' => 'nullable|numeric',
            'shipping_cost'  => 'nullable|numeric',
            'sub_total.*'    => 'required|numeric',
            'attachemnt'     => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
            'product_id'     => 'required'
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

        $attachemnt = "";
        if ($request->hasfile('attachemnt')) {
            $file       = $request->file('attachemnt');
            $attachemnt = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/attachments/", $attachemnt);
        }

        $purchase                 = new Purchase();
        $purchase->order_date     = $request->input('order_date');
        $purchase->supplier_id    = $request->input('supplier_id');
        $purchase->order_status   = $request->input('order_status');
        $purchase->order_discount = $request->input('order_discount');
        $purchase->shipping_cost  = $request->input('shipping_cost');
        $purchase->product_total  = $request->input('product_total');
        $purchase->order_tax      = $request->tax_total;
        $purchase->grand_total    = ($purchase->product_total + $purchase->shipping_cost + $purchase->order_tax) - $purchase->order_discount;
        $purchase->paid           = 0;
        $purchase->payment_status = 0;
        $purchase->attachemnt     = $attachemnt;
        $purchase->note           = $request->input('note');

        $purchase->save();

        $taxes = Tax::all();

        //Save Purcahse item
        for ($i = 0; $i < count($request->product_id); $i++) {
            $purchaseItem                    = new PurchaseOrderItem();
            $purchaseItem->purchase_order_id = $purchase->id;
            $purchaseItem->product_id        = $request->product_id[$i];
            $purchaseItem->description       = $request->product_description[$i];
            $purchaseItem->quantity          = $request->quantity[$i];
            $purchaseItem->unit_cost         = $request->unit_cost[$i];
            $purchaseItem->discount          = $request->discount[$i];
            $purchaseItem->tax_amount        = $request->product_tax[$i];
            $purchaseItem->sub_total         = $request->sub_total[$i];
            $purchaseItem->save();

            //Store Purchase Order Taxes
            if (isset($request->tax[$purchaseItem->product_id])) {
                foreach ($request->tax[$purchaseItem->product_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $purchaseOrderItemTax                         = new PurchaseOrderItemTax();
                    $purchaseOrderItemTax->purchase_order_id      = $purchaseItem->purchase_order_id;
                    $purchaseOrderItemTax->purchase_order_item_id = $purchaseItem->id;
                    $purchaseOrderItemTax->tax_id                 = $tax->id;
                    $tax_type                                     = $tax->type == 'percent' ? '%' : '';
                    $purchaseOrderItemTax->name                   = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $purchaseOrderItemTax->amount                 = $tax->type == 'percent' ? ($purchaseItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $purchaseOrderItemTax->save();
                }
            }

            //Update Stock if Order Status is received
            if ($request->input('order_status') == '3') {
                // $stock           = Stock::where("product_id", $purchaseItem->product_id)->where('company_id', company_id())->first();
                // // $stock = Stock::where("item_id", $purchaseItem->product_id)->where('company_id', company_id())
                // // ->join('products as p', 'p.id', 'product_id')->select('current_stocks.*')->first();
                
                // if ($stock == null) {
                //     $stock = new Stock(['product_id'=>$purchaseItem->product_id, 'quantity'=>0, 'company_id'=>company_id()]);
                // }
                // $stock->quantity = $stock->quantity + $purchaseItem->quantity;
                // $stock->save();
                update_stock($purchaseItem->product_id, $purchaseItem->quantity);
            }
        }
        DB::commit();

        if (!$request->ajax()) {
            return redirect()->route('purchase_orders.show', $purchase->id)->with('success', _lang('Purchase Order Created Sucessfully'));
        }
        
        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Purchase Order Created Sucessfully'), 'data' => $purchase]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $purchase       = Purchase::find($id);
        $purchase_taxes = PurchaseOrderItemTax::where('purchase_order_id', $id)
            ->selectRaw('purchase_order_item_taxes.*,sum(purchase_order_item_taxes.amount) as tax_amount')
            ->groupBy('purchase_order_item_taxes.tax_id')
            ->get();
        $transactions = Transaction::where("purchase_id", $id)->get();

        return view('backend.accounting.purchase_order.view', compact('purchase', 'purchase_taxes', 'transactions', 'id'));

    }

    /**
     * Generate PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_pdf(Request $request, $id) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $data                   = array();
        $data['purchase']       = Purchase::find($id);
        $data['purchase_taxes'] = PurchaseOrderItemTax::where('purchase_order_id', $id)
            ->selectRaw('purchase_order_item_taxes.*,sum(purchase_order_item_taxes.amount) as tax_amount')
            ->groupBy('purchase_order_item_taxes.tax_id')
            ->get();
        $data['transactions'] = Transaction::where("purchase_id", $id)->get();

        $pdf = PDF::loadView("backend.accounting.purchase_order.pdf_export", $data);
        $pdf->setWarnings(false);

        //return $pdf->stream();
        return $pdf->download("Orden de compra{$data['purchase']->id}.pdf");

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $purchase = Purchase::find($id);
        $suppliers = Supplier::all();
        $productos = Item::query()
        ->with('category')
        ->whereRelation('product_stock', 'company_id', '=', Session::get('company')->id)
        ->orderBy("id", "desc")
        ->take(20)
        ->get();

        if (!$request->ajax()) {
            return view('backend.accounting.purchase_order.edit', compact('purchase', 'id', 'suppliers', 'productos'));
        } else {
            return view('backend.accounting.purchase_order.modal.edit', compact('purchase', 'id', 'suppliers'));
        }

    }

    public function editOrder(Request $request, $id) {
        $purchase = Purchase::find($id);

        $productos = Item::query()
        ->with('category')
        ->whereRelation('product_stock', 'company_id', '=', Session::get('company')->id)
        ->orderBy("id", "desc")
        ->take(20)
        ->get();

        if (!$request->ajax()) {
            return view('backend.accounting.purchase_order.editOrder', compact('purchase', 'id', 'productos'));
        } else {
            return view('backend.accounting.purchase_order.modal.edit', compact('purchase', 'id'));
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
            'order_date'     => 'required',
            'supplier_id'    => 'required',
            'order_status'   => 'required',
            'order_discount' => 'nullable|numeric',
            'shipping_cost'  => 'nullable|numeric',
            'sub_total.*'    => 'required|numeric',
            'attachemnt'     => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ],
        [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('purchase_orders.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        if ($request->hasfile('attachemnt')) {
            $file       = $request->file('attachemnt');
            $attachemnt = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/attachments/", $attachemnt);
        }

        $purchase = Purchase::find($id);

        $previous_status = $purchase->order_status;

        if( $previous_status == 3 ){
            $purchase->supplier_id    = $request->input('supplier_id');
            $purchase->payment_status = $request->input('payment_status');
            
            if ($request->hasfile('attachemnt')) {
                $purchase->attachemnt = $attachemnt;
            }
            $purchase->note = $request->input('note');
    
            $purchase->save();

            if (!$request->ajax()) {
                return redirect()->route('purchase_orders.show', $purchase->id)->with('success', _lang('Purchase Order Updated Sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Purchase Order Updated Sucessfully'), 'data' => $purchase]);
            }
        }

        $purchase->order_date     = $request->input('order_date');
        $purchase->supplier_id    = $request->input('supplier_id');
        $purchase->order_status   = $request->input('order_status');
        $purchase->order_discount = $request->input('order_discount');
        $purchase->shipping_cost  = $request->input('shipping_cost');
        $purchase->product_total  = $request->input('product_total');
        $purchase->order_tax      = $request->tax_total;
        $purchase->grand_total    = ($purchase->product_total + $purchase->shipping_cost + $purchase->order_tax) - $purchase->order_discount;

        $purchase->payment_status = $request->input('payment_status');

        // if (round($purchase->paid, 2) < $purchase->grand_total) {
        //     $purchase->payment_status = 0;
        // }

        if ($request->hasfile('attachemnt')) {
            $purchase->attachemnt = $attachemnt;
        }
        $purchase->note = $request->input('note');

        $purchase->save();

        $taxes = Tax::all();

        //Update Purcahse item
        $purchaseItems = PurchaseOrderItem::where("purchase_order_id", $id)->get();
        foreach ($purchaseItems as $p_item) {
            $orderItem = PurchaseOrderItem::find($p_item->id);
            
            // update_stock($p_item->product_id, $orderItem->quantity, '-');
            $orderItem->delete();
        }

        $purchaseOrderItemTax = PurchaseOrderItemTax::where("purchase_order_id", $id);
        $purchaseOrderItemTax->delete();

        for ($i = 0; $i < count($request->product_id); $i++) {
            $purchaseItem                    = new PurchaseOrderItem();
            $purchaseItem->purchase_order_id = $purchase->id;
            $purchaseItem->product_id        = $request->product_id[$i];
            $purchaseItem->description       = $request->product_description[$i];
            $purchaseItem->quantity          = $request->quantity[$i];
            $purchaseItem->unit_cost         = $request->unit_cost[$i];
            $purchaseItem->discount          = $request->discount[$i];
            $purchaseItem->tax_amount        = $request->product_tax[$i];
            $purchaseItem->sub_total         = $request->sub_total[$i];
            $purchaseItem->save();

            //Store Purchase Order Taxes
            if (isset($request->tax[$purchaseItem->product_id])) {
                foreach ($request->tax[$purchaseItem->product_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $purchaseOrderItemTax                         = new PurchaseOrderItemTax();
                    $purchaseOrderItemTax->purchase_order_id      = $purchaseItem->purchase_order_id;
                    $purchaseOrderItemTax->purchase_order_item_id = $purchaseItem->id;
                    $purchaseOrderItemTax->tax_id                 = $tax->id;
                    $tax_type                                     = $tax->type == 'percent' ? '%' : '';
                    $purchaseOrderItemTax->name                   = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $purchaseOrderItemTax->amount                 = $tax->type == 'percent' ? ($purchaseItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $purchaseOrderItemTax->save();
                }
            }

            //Update Stock if Order Status is received
            if ($request->input('order_status') == '3') {
                update_stock($request->product_id[$i], $request->quantity[$i]);
            }
        }

        DB::commit();

        if (!$request->ajax()) {
            return redirect()->route('purchase_orders.show', $purchase->id)->with('success', _lang('Purchase Order Updated Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Purchase Order Updated Sucessfully'), 'data' => $purchase]);
        }

    }

    public function updateOrder(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'order_date'     => 'required',
            'supplier_id'    => 'required',
            'order_status'   => 'required',
            'order_discount' => 'nullable|numeric',
            'shipping_cost'  => 'nullable|numeric',
            'sub_total.*'    => 'required|numeric',
            'attachemnt'     => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ],
        [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('purchase_orders.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        if ($request->hasfile('attachemnt')) {
            $file       = $request->file('attachemnt');
            $attachemnt = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/attachments/", $attachemnt);
        }

        $purchase = Purchase::find($id);

        $previous_status = $purchase->order_status;

        $purchase->order_date     = $request->input('order_date');
        $purchase->supplier_id    = $request->input('supplier_id');
        $purchase->order_status   = $request->input('order_status');
        $purchase->order_discount = $request->input('order_discount');
        $purchase->shipping_cost  = $request->input('shipping_cost');
        $purchase->product_total  = $request->input('product_total');
        $purchase->order_tax      = $request->tax_total;
        $purchase->grand_total    = ($purchase->product_total + $purchase->shipping_cost + $purchase->order_tax) - $purchase->order_discount;

        $purchase->payment_status = $request->input('payment_status');

        // if (round($purchase->paid, 2) < $purchase->grand_total) {
        //     $purchase->payment_status = 0;
        // }

        if ($request->hasfile('attachemnt')) {
            $purchase->attachemnt = $attachemnt;
        }
        $purchase->note = $request->input('note');

        $purchase->save();

        $taxes = Tax::all();

        //Update Purcahse item
        $purchaseItems = PurchaseOrderItem::where("purchase_order_id", $id)->get();
        foreach ($purchaseItems as $p_item) {
            $orderItem = PurchaseOrderItem::find($p_item->id);
            
            // update_stock($p_item->product_id, $orderItem->quantity, '-');
            $orderItem->delete();
        }
        

        $purchaseOrderItemTax = PurchaseOrderItemTax::where("purchase_order_id", $id);
        $purchaseOrderItemTax->delete();

        
        for ($i = 0; $i < count($request->product_id); $i++) {
            $purchaseItem                    = new PurchaseOrderItem();
            $purchaseItem->purchase_order_id = $purchase->id;
            $purchaseItem->product_id        = $request->product_id[$i];
            $purchaseItem->description       = $request->product_description[$i];
            $purchaseItem->quantity          = $request->quantity[$i];
            $purchaseItem->unit_cost         = $request->unit_cost[$i];
            $purchaseItem->discount          = $request->discount[$i];
            $purchaseItem->tax_amount        = $request->product_tax[$i];
            $purchaseItem->sub_total         = $request->sub_total[$i];
            $purchaseItem->save();

            //Store Purchase Order Taxes
            if (isset($request->tax[$purchaseItem->product_id])) {
                foreach ($request->tax[$purchaseItem->product_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $purchaseOrderItemTax                         = new PurchaseOrderItemTax();
                    $purchaseOrderItemTax->purchase_order_id      = $purchaseItem->purchase_order_id;
                    $purchaseOrderItemTax->purchase_order_item_id = $purchaseItem->id;
                    $purchaseOrderItemTax->tax_id                 = $tax->id;
                    $tax_type                                     = $tax->type == 'percent' ? '%' : '';
                    $purchaseOrderItemTax->name                   = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $purchaseOrderItemTax->amount                 = $tax->type == 'percent' ? ($purchaseItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $purchaseOrderItemTax->save();
                }
            }

        }

        DB::commit();

        if (!$request->ajax()) {
            return redirect()->route('purchase_orders.show', $purchase->id)->with('success', _lang('Purchase Order Updated Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Purchase Order Updated Sucessfully'), 'data' => $purchase]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        DB::beginTransaction();

        $purchase = Purchase::find($id);
        $purchase->delete();

        //Remove Purchase Item
        $purchaseItems = PurchaseOrderItem::where("purchase_order_id", $id)->get();
        foreach ($purchaseItems as $p_item) {
            $returnItem = PurchaseOrderItem::find($p_item->id);
            update_stock($p_item->product_id, $returnItem->quantity, '-');
            $returnItem->delete();
        }

        $purchaseOrderItemTax = PurchaseOrderItemTax::where('purchase_order_id', $id);
        $purchaseOrderItemTax->delete();

        DB::commit();

        return back()->with('success', _lang('Deleted Sucessfully'));
    }


    public function store_payment(Request $request, $id = '') {
		if($request->isMethod('get')){
			$purchase = Purchase::find($id);

			if ($request->ajax()) {
				return view('backend.accounting.purchase_order.modal.create_payment', compact('purchase', 'id'));
			}
		}

        $validator = Validator::make($request->all(), [
            'purchase_id'       => 'required',
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
        $transaction->type              = 'expense';
        $transaction->dr_cr             = 'dr';
        $transaction->amount            = $request->input('amount');
        $transaction->payment_method_id = $request->input('payment_method_id');
        $transaction->purchase_id       = $request->input('purchase_id');
        $transaction->reference         = $request->input('reference');
        $transaction->note              = $request->input('note');
        $transaction->attachment        = $attachment;

        $transaction->save();

        //Update Purchase Order Table
        $purchase       = Purchase::find($transaction->purchase_id);
        $purchase->paid = $purchase->paid + $transaction->amount;
        if (round($purchase->paid, 2) >= $purchase->grand_total) {
            $purchase->payment_status = 1;
        }
        $purchase->save();

        if ($request->ajax()) {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Payment was made Sucessfully'), 'data' => $transaction]);
        }
    }

    public function view_payment(Request $request, $purchase_id) {

        $transactions = Transaction::where("purchase_id", $purchase_id)->get();

        if (!$request->ajax()) {
            return view('backend.accounting.purchase_order.view_payment', compact('transactions'));
        } else {
            return view('backend.accounting.purchase_order.modal.view_payment', compact('transactions'));
        }
    }


}
