<?php

namespace App\Http\Controllers;

use App\ProductGroup;
use Illuminate\Http\Request;
use Validator;

class ProductGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $product_groups = ProductGroup::where('prodgrp_status', '!=', 'Deleted')->orderBy("prodgrp_id", "desc")->get();
        return view('backend.accounting.product_group.list', compact('product_groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.product_group.create');
        } else {
            return view('backend.accounting.product_group.modal.create');
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
            'prodgrp_name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('product_group.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $product_group = new ProductGroup();
        $product_group->prodgrp_name = $request->input('prodgrp_name');
        $product_group->prodgrp_status = $request->input('prodgrp_status');
        $product_group->save();

        if (!$request->ajax()) {
            return redirect()->route('product_group.index')->with('success', _lang('Saved sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $product_group]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $product_group = ProductGroup::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.product_group.edit', compact('product_group', 'id'));
        } else {
            return view('backend.accounting.product_group.modal.edit', compact('product_group', 'id'));
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
            'prodgrp_name' => 'required|max:191',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('product_group.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $product_group = ProductGroup::find($id);
        $product_group->prodgrp_name  = $request->input('prodgrp_name');
        $product_group->prodgrp_status = $request->input('prodgrp_status');
        $product_group->save();

        if (!$request->ajax()) {
            return redirect()->route('product_group.index')->with('success', _lang('Updated sucessfully'));
        } 
        
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $product_group]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $product_group = ProductGroup::find($id);
        $product_group->prodgrp_status = 'Deleted';
        $product_group->save();
        return redirect('product_group')->with('success', _lang('Updated sucessfully'));
    }
}
