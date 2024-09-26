<?php

namespace App\Http\Controllers;

use App\CodigoArancelario;
use Illuminate\Http\Request;
use Validator;

class CodigoArancelarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $codigo_arancelarios = CodigoArancelario::orderBy("codaran_id", "desc")->get();
        return view('backend.accounting.codigo_arancelario.list', compact('codigo_arancelarios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.codigo_arancelario.create');
        } else {
            return view('backend.accounting.codigo_arancelario.modal.create');
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
            'codaran_codigo' => 'required',
            'codaran_descripcion' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('codigo_arancelarios.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $codigo_arancelario = new CodigoArancelario();
        $codigo_arancelario->codaran_codigo = $request->input('codaran_codigo');
        $codigo_arancelario->codaran_descripcion = $request->input('codaran_descripcion');
        $codigo_arancelario->save();

        if (!$request->ajax()) {
            return redirect()->route('codigo_arancelarios.index')->with('success', _lang('Saved sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $codigo_arancelario]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $codigo_arancelario = CodigoArancelario::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.codigo_arancelario.edit', compact('codigo_arancelario', 'id'));
        } else {
            return view('backend.accounting.codigo_arancelario.modal.edit', compact('codigo_arancelario', 'id'));
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
            'codaran_codigo' => 'required',
            'codaran_descripcion' => 'required|max:255',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('codigo_arancelarios.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $codigo_arancelario = CodigoArancelario::find($id);
        $codigo_arancelario->codaran_codigo = $request->input('codaran_codigo');
        $codigo_arancelario->codaran_descripcion  = $request->input('codaran_descripcion');
        $codigo_arancelario->save();

        if (!$request->ajax()) {
            return redirect()->route('codigo_arancelarios.index')->with('success', _lang('Updated sucessfully'));
        } 
        
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $codigo_arancelario]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $codigo_arancelario = CodigoArancelario::find($id);
        $codigo_arancelario->codaran_status = 'Deleted';
        $codigo_arancelario->save();
        return redirect('codigo_arancelarios')->with('success', _lang('Updated sucessfully'));
    }
}
