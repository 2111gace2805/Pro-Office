<?php

namespace App\Http\Controllers;

use App\PaymentMethod;
use Illuminate\Http\Request;
use Validator;

class PaymentMethodController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $paymentmethods = PaymentMethod::orderBy("id", "desc")->get();
        return view('backend.accounting.payment_method.list', compact('paymentmethods'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.payment_method.create');
        } else {
            return view('backend.accounting.payment_method.modal.create');
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
            'name' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('payment_methods.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $paymentmethod             = new PaymentMethod();
        $paymentmethod->name       = $request->input('name');

        $paymentmethod->save();

        if (!$request->ajax()) {
            return redirect()->route('payment_methods.index')->with('success', _lang('Saved Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $paymentmethod]);
        }

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $paymentmethod = PaymentMethod::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.payment_method.edit', compact('paymentmethod', 'id'));
        } else {
            return view('backend.accounting.payment_method.modal.edit', compact('paymentmethod', 'id'));
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
            'name' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('payment_methods.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $paymentmethod = PaymentMethod::find($id);
        $paymentmethod->name       = $request->input('name');

        $paymentmethod->save();

        if (!$request->ajax()) {
            return redirect()->route('payment_methods.index')->with('success', _lang('Updated Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully'), 'data' => $paymentmethod]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $paymentmethod = PaymentMethod::find($id);
        $paymentmethod->delete();
        return back()->with('success', _lang('Removed Sucessfully'));
    }
}
