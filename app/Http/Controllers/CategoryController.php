<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $categories = Category::orderBy("id", "desc")->get();
        return view('backend.accounting.category.list', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.category.create');
        } else {
            return view('backend.accounting.category.modal.create');
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
            'category_name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('categories.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $productunit = new Category();
        $productunit->category_name = $request->input('category_name');
        $productunit->save();

        if (!$request->ajax()) {
            return redirect()->route('categories.index')->with('success', _lang('Saved sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $productunit]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $productunit = Category::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.category.edit', compact('productunit', 'id'));
        } else {
            return view('backend.accounting.category.modal.edit', compact('productunit', 'id'));
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
            'category_name' => 'required|max:191',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('categories.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $productunit = Category::find($id);
        $productunit->category_name  = $request->input('category_name');
        $productunit->save();

        if (!$request->ajax()) {
            return redirect()->route('categories.index')->with('success', _lang('Updated sucessfully'));
        } 
        
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $productunit]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $productunit = Category::find($id);
        $productunit->delete();
        return redirect('categories')->with('success', _lang('Updated sucessfully'));
    }
}
