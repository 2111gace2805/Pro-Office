<?php

namespace App\Http\Controllers;

use Validator;
use App\SaleInstitution;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SaleInstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return view('backend.accounting.sales_institutions.list');
    }


    public function get_table_data(Request $request) {

        $institutions = SaleInstitution::orderBy('id', 'asc');

        $data = Datatables::eloquent($institutions)
            ->addColumn('codes', function ($institutions) {

                $list = '<p>Listado de códigos</p>';

                if( $institutions->code_isss != '' ){
                    $list .= '<li> Código ISSS: ' . $institutions->code_isss . '  </li>';
                }

                if( $institutions->code_minsal != '' ){
                    $list .= '<li> Código MINSAL: ' . $institutions->code_minsal . '  </li>';
                }

                if( $institutions->code_onu != '' ){
                    $list .= '<li> Código ONU: ' . $institutions->code_onu . '  </li>';
                }
                
                return $list;

            })
            ->addColumn('action', function ($institutions) {
                return '<form action="' . action('SaleInstitutionController@destroy', $institutions['id']) . '" class="text-center" method="post">'
                . '<a href="' . action('SaleInstitutionController@edit', $institutions['id']) . '" data-title="Editar Institución" class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a> '
                . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button class="btn btn-danger btn-sm btn-remove" type="submit"><i class="ti-trash"></i></button>'
                    . '</form>';
            })
            ->setRowId(function ($institutions) {
                return "row_" . $institutions->id;
            })
            ->rawColumns(['codes', 'action'])
            ->make(true);

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        return view('backend.accounting.sales_institutions.modal.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name'          => 'required|max:255',
            'code_isss'     => 'required_if:active_code_isss,1|max:25',
            'code_minsal'   => 'required_if:active_code_minsal,1|max:25',
            'code_onu'      => 'required_if:active_code_onu,1|max:25',
        ],[
            'code_isss.required_if' => 'El campo código ISSS es obligatorio',
            'code_isss.max' => 'El campo código ISSS no puede tener más de 25 caracteres.',

            'code_minsal.required_if' => 'El campo código MINSAL es obligatorio',
            'code_minsal.max' => 'El campo código MINSAL no puede tener más de 25 caracteres.',

            'code_onu.required_if' => 'El campo código ONU es obligatorio',
            'code_onu.max' => 'El campo código ONU no puede tener más de 25 caracteres.',
        ]);

        if( $validator->fails() ){
            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }

        $institution                = new SaleInstitution();
        $institution->name          = $request->input('name');
        $institution->code_isss     = $request->input('code_isss');
        $institution->code_minsal   = $request->input('code_minsal');
        $institution->code_onu      = $request->input('code_onu');

        $institution->save();

        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Nueva institución agregada correctamente'), 'data' => $institution]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $institution_id) {

        $institution = SaleInstitution::find($institution_id);

        return view('backend.accounting.sales_institutions.modal.edit', compact('institution'));
    }

    public function update(Request $request, $institution_id) {

        $institution = SaleInstitution::find($institution_id);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|max:255',
            'code_isss'     => 'required_if:active_code_isss,1|max:25',
            'code_minsal'   => 'required_if:active_code_minsal,1|max:25',
            'code_onu'      => 'required_if:active_code_onu,1|max:25',
        ],[
            'code_isss.required_if' => 'El campo código ISSS es obligatorio',
            'code_isss.max' => 'El campo código ISSS no puede tener más de 25 caracteres.',

            'code_minsal.required_if' => 'El campo código MINSAL es obligatorio',
            'code_minsal.max' => 'El campo código MINSAL no puede tener más de 25 caracteres.',

            'code_onu.required_if' => 'El campo código ONU es obligatorio',
            'code_onu.max' => 'El campo código ONU no puede tener más de 25 caracteres.',
        ]);

        if( $validator->fails() ){
            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }

        $institution->name          = $request->input('name');
        $institution->code_isss     = $request->input('code_isss');
        $institution->code_minsal   = $request->input('code_minsal');
        $institution->code_onu      = $request->input('code_onu');

        $institution->save();

        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Institución actualizada correctamente'), 'data' => $institution]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        $institution = SaleInstitution::find($id);

        $institution->delete();
        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }
}
