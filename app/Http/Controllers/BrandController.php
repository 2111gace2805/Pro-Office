<?php

namespace App\Http\Controllers;

use App\Brand;
use Illuminate\Http\Request;
use Validator;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $brands = Brand::where('brand_status', '!=', 'Deleted')->orderBy("brand_id", "desc")->get();
        return view('backend.accounting.brand.list', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.brand.create');
        } else {
            return view('backend.accounting.brand.modal.create');
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
            'brand_name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('brands.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $brand = new Brand();
        $brand->brand_name = $request->input('brand_name');
        $brand->brand_status = $request->input('brand_status');
        $brand->save();

        if (!$request->ajax()) {
            return redirect()->route('brands.index')->with('success', _lang('Saved sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $brand]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $brand = Brand::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.brand.edit', compact('brand', 'id'));
        } else {
            return view('backend.accounting.brand.modal.edit', compact('brand', 'id'));
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
            'brand_name' => 'required|max:191',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('brands.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $brand = Brand::find($id);
        $brand->brand_name  = $request->input('brand_name');
        $brand->brand_status  = $request->input('brand_status');
        $brand->save();

        if (!$request->ajax()) {
            return redirect()->route('brands.index')->with('success', _lang('Updated sucessfully'));
        } 
        
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $brand]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $brand = Brand::find($id);
        $brand->brand_status = 'Deleted';
        $brand->save();
        return redirect('brands')->with('success', _lang('Updated sucessfully'));
    }
}
