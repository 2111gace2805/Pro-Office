<?php

namespace App\Http\Controllers;

use App\Cash;
use Validator;
use DataTables;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.accounting.cash.list');
    }

    public function get_table_data() {

        $currency = currency();

        $cajas = Cash::orderBy("cash_id", "desc");

        return Datatables::eloquent($cajas)
            ->editColumn('cash_value', function ($caja) use ($currency) {
                return '<span class="float-right">' . decimalPlace($caja->cash_value, $currency) . '</span>';
            })
            ->editColumn('cash_status', function ($caja) {
                if ($caja->cash_status === 'Opened') {
                    return '<span class="badge badge-success">Abierta</span>';
                }
                elseif ($caja->cash_status === 'Closed') {
                    return '<span class="badge badge-danger">Cerrada</span>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('company_name', function ($cash) {
                return $cash->company->company_name ?? '-';
            })
            ->addColumn('action', function ($caja) {

                $btnEdit = '';
                
                if( $caja->date_closed == '' ){
                    $btnEdit = '<a href="'. action('CashController@edit', $caja->cash_id) .'"  data-title="Editar Caja" class="dropdown-item ajax-modal"><i class="ti-pencil"></i> ' . _lang('Editar') . '</a>';
                }
                return '<div class="dropdown text-center">'
                . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                . '<i class="mdi mdi-chevron-down"></i></button>'
                . '<div class="dropdown-menu">'
                . '<form action="' . action('CashController@destroy', $caja->cash_id) . '" method="post">'
                . csrf_field()
                . '<input name="_method" type="hidden" value="DELETE">'
                . $btnEdit
                . '<a href="'. action('CashController@show', $caja->cash_id) .'"  data-title="Ver Caja" class="dropdown-item ajax-modal"><i class="ti-eye"></i> ' . _lang('Ver') . '</a>'
                . '<button class="button-link btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Delete') . '</button>'
                . '</form>'
                . '</div>'
                . '</div>';
            })
            ->setRowId(function ($caja) {
                return "row_" . $caja->cash_id;
            })
            ->rawColumns(['cash_value', 'cash_status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.accounting.cash.modal.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cash_name' => 'required',
            'cash_value' => 'required',
            'company_id' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
        }
	

        $caja = new Cash();
        $caja->cash_name    = $request->input('cash_name');
		$caja->cash_value   = $request->input('cash_value');
		$caja->cash_status  = 'Opened';
		$caja->company_id   = $request->input('company_id');

        $caja->save();

        return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Sucessfully'),'data'=>$caja, 'table' => '#tblCajas']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cash = Cash::find($id);
        return view('backend.accounting.cash.modal.view',compact('cash','id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cash = Cash::find($id);
        return view('backend.accounting.cash.modal.edit', compact('cash','id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cash_name' => 'required',
            'cash_value' => 'required',
            'cash_status' => 'required',
            'company_id' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
        }
	

        $caja = Cash::find($id);
        $caja->cash_name    = $request->input('cash_name');
		$caja->cash_value   = $request->input('cash_value');
		$caja->cash_status  = $request->input('cash_status');
        
        if( $request->input('cash_status') == 'Closed' ){
            $caja->date_closed   = Carbon::now();
        }

        $caja->save();

        return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Updated Sucessfully'),'data'=>$caja, 'table' => '#tblCajas']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cash = Cash::find($id);
        $cash->delete();
        return redirect()->route('cash.index')->with('success',_lang('Deleted Sucessfully'));
    }
}
