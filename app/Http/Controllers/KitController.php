<?php

namespace App\Http\Controllers;

use App\Kit;
use Validator;
use DataTables;
use App\Product;
use Illuminate\Http\Request;

class KitController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return view('backend.accounting.kit.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){

        if( !$request->ajax() ){
            return view('backend.accounting.kit.create');
        }
        else{
            return view('backend.accounting.kit.modal.create');
        }
    }

    public function get_table_data(Request $request){

        $currency = currency();

        $kits = Kit::orderBy('kits.id', 'desc');

        return Datatables::eloquent($kits)
            ->filter(function ($query) use ($request) {

                if( $request->has('code') ){
                    $query->where('code', 'like', "%{$request->get('code')}%");
                }

                if( $request->has('name') ){
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }

            })
            ->editColumn('products', function ($kit) {

                $products = json_decode( json_encode($kit->products));

                $list = "";
                foreach( $products as $product ){

                    $productDetails = Product::with('item')->find($product->product_id);

                    if( $productDetails ){
                        $list .=  '<li> '. $productDetails->item->item_name . ' ( Cantidad: ' . $product->quantity . ' )</li>';
                    }
                }
    
                return $list;
            })
            ->editColumn('amount', function ($kit) {

                return '$ '.$kit->amount;
            })
            ->addColumn('action', function ($kit) {
                $actionHtml = '<div class="dropdown text-center">'
                    . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                    . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                    . '<div class="dropdown-menu">'
                    . '<a class="dropdown-item ajax-modal" href="' . action('KitController@show', $kit->id) . '" data-title="' . _lang('Detalles de Kit') . '" data-fullscreen="true"><i class="ti-eye"></i> ' . _lang('View') . '</a>';

                $actionHtml .= '<a class="dropdown-item" href="' . action('KitController@edit', $kit->id) . '" data-title="' . _lang('Edit Note') . '" data-fullscreen="true"><i class="ti-pencil"></i> ' . _lang('Edit') . '</a>'
                    .'<form action="' . action('KitController@destroy', $kit->id) . '" method="post" style="display: inline;">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button class="btn btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Eliminar') . '</button>'
                    .'</form>'
                    . '</div>'
                    . '</div>';

                return $actionHtml;
            })

            ->setRowId(function ($kit) {
                return "row_" . $kit->id;
            })
            ->rawColumns(['amount', 'products', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        
        $validator = Validator::make($request->all(), [
            'code'         => 'required',
            'name'         => 'required',
            'amount'       => 'required',
            'product_id'   => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('kits.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $products = [];

        $kit            = new Kit();
        $kit->code      = $request->code;
        $kit->name      = $request->name;
        $kit->amount    = $request->amount;

        for( $i = 0; $i < count($request->product_id); $i++ ){

            $products[] = [
                'product_id'    => $request->product_id[$i],
                'quantity'      => $request->quantity[$i],
            ];
        }

        $kit->products = $products;
        $kit->save();

        if( !$request->ajax() ){
            return redirect()->route('kits.index')->with('success', _lang('Kit creado exitosamente '));
        }
        else{
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Kit creado exitosamente ')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id){

        $kit = Kit::find($id);

        $products = json_decode( json_encode($kit->products) );


        $productDetails = [];

        foreach( $products as $product ){

            $details = Product::with('item')->find($product->product_id);

            if( $details ){

                $productDetails[] = [
                    'product' => $details->item->item_name,
                    'quantity' => $product->quantity,
                ];
            }
        }

        if( !$request->ajax() ){
            return view('backend.accounting.kit.view', compact('kit', 'id', 'productDetails'));
        } else {
            return view('backend.accounting.kit.modal.view', compact('kit', 'id', 'productDetails'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id){
        
        $kit = Kit::find($id);

        $products = json_decode( json_encode($kit->products) );

        if( !$request->ajax() ){
            return view('backend.accounting.kit.edit', compact('id', 'kit', 'products'));
        }
        else {
            return view('backend.accounting.kit.modal.edit', compact('id', 'kit', 'products'));
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'code'         => 'required',
            'name'         => 'required',
            'amount'       => 'required',
            'product_id'   => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('kits.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $products = [];

        $kit            = Kit::find($id);
        $kit->code      = $request->code;
        $kit->name      = $request->name;
        $kit->amount    = $request->amount;

        for( $i = 0; $i < count($request->product_id); $i++ ){

            $products[] = [
                'product_id'    => $request->product_id[$i],
                'quantity'      => $request->quantity[$i],
            ];
        }

        $kit->products = $products;
        $kit->save();

        if( !$request->ajax() ){
            return redirect()->route('kits.index')->with('success', _lang('Kit actualizado exitosamente '));
        }
        else{
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Kit actualizado exitosamente ')]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        
        $kit = Kit::find($id);

        if( !$kit ){
            return back()->with('success', _lang('Oops! Kit no encontrado'));
        }

        $kit->delete();

        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }
}
