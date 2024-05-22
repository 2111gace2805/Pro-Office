<?php

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use Validator;

class TransferController extends Controller {

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.transfer.create');
        } else {
            return view('backend.accounting.transfer.modal.create');
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
            'trans_date'        => 'required',
            'account_from'      => 'required',
            'account_to'        => 'required|different:account_from',
            'amount'            => 'required|numeric',
            'payment_method_id' => 'required',
            'reference'         => 'nullable|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('transfer.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        //Add Credit Transaction
        $transaction                    = new Transaction();
        $transaction->trans_date        = $request->input('trans_date');
        $transaction->account_id        = $request->input('account_to');
        $transaction->chart_id          = 0;
        $transaction->type              = 'transfer';
        $transaction->dr_cr             = 'cr';
        $transaction->amount            = $request->input('amount');
        $transaction->payment_method_id = $request->input('payment_method_id');
        $transaction->reference         = $request->input('reference');
        $transaction->note              = $request->input('note');

        $transaction->save();

        //Add Debit Transaction
        $transaction                    = new Transaction();
        $transaction->trans_date        = $request->input('trans_date');
        $transaction->account_id        = $request->input('account_from');
        $transaction->chart_id          = 0;
        $transaction->type              = 'transfer';
        $transaction->dr_cr             = 'dr';
        $transaction->amount            = $request->input('amount');
        $transaction->payment_method_id = $request->input('payment_method_id');
        $transaction->reference         = $request->input('reference');
        $transaction->note              = $request->input('note');

        $transaction->save();

        if (!$request->ajax()) {
            return redirect()->route('transfer.create')->with('success', _lang('Saved Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $transaction]);
        }

    }

}
