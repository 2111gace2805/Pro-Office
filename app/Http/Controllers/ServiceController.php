<?php

namespace App\Http\Controllers;

use App\Item;
use App\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Validator;

class ServiceController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $items = Item::where("item_type", "service")->orderBy("id", "desc")
        ->where('company_id', Session::get('company')->id)->get();
        return view('backend.accounting.service.list', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.service.create');
        } else {
            return view('backend.accounting.service.modal.create');
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
            'item_name'  => 'required',
            'cost'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('services.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        //Create Item
        $item             = new Item();
        $item->item_name  = $request->input('item_name');
        $item->item_type  = 'service';
        $item->tipoitem_id = '2'; // ref tabla tipo_item 2 es: Servicios
        $item->save();

        //Create Product
        $service              = new Service();
        $service->item_id     = $item->id;
        $service->cost        = $request->input('cost');
        $service->description = $request->input('description');

        $service->save();

        if (!$request->ajax()) {
            return redirect()->route('services.index')->with('success', _lang('Information has been added sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Information has been added sucessfully'), 'data' => $item]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $item = Item::find($id);

        if (!$request->ajax()) {
            return view('backend.accounting.service.view', compact('item', 'id'));
        } else {
            return view('backend.accounting.service.modal.view', compact('item', 'id'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $item = Item::find($id);

        if (!$request->ajax()) {
            return view('backend.accounting.service.edit', compact('item', 'id'));
        } else {
            return view('backend.accounting.service.modal.edit', compact('item', 'id'));
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
            'item_name'  => 'required',
            'cost'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('services.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        //Update item
        $item = Item::find($id);

        if ($item) {

            $item->item_name  = $request->input('item_name');
            $item->item_type  = 'service';
            $item->tipoitem_id = '2'; // ref tabla tipo_item 2 es: Servicios
            $item->save();

            $service              = Service::where("item_id", $id)->first();
            $service->item_id     = $item->id;
            $service->cost        = $request->input('cost');
            $service->description = $request->input('description');

            $service->save();
        } else {
            if (!$request->ajax()) {
                return redirect()->route('services.index')->with('error', _lang('Update Failed !'));
            } else {
                return response()->json(['result' => 'error', 'message' => _lang('Update Failed !')]);
            }
        }

        if (!$request->ajax()) {
            return redirect()->route('services.index')>with('success', _lang('Information has been updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Information has been updated sucessfully'), 'data' => $service]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $item = Item::find($id);
        $item->delete();

        $service = Service::where("item_id", $id);
        $service->delete();
        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }
}