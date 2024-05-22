<?php

namespace App\Http\Controllers;

use App\Cash;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $companies = Company::orderBy("id", "desc")->get();
        return view('backend.accounting.company.list', compact('companies'));
    }

    public function change(Request $request, $company) {
        $request->session()->put('company', Company::find($company));
        return redirect()->back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.company.create');
        } else {
            return view('backend.accounting.company.modal.create');
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
            'company_name' => 'required|max:255',
            'address' => 'required|max:250',
            'phone' => 'required|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('companies.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $company = new Company();
        $company->company_name = $request->input('company_name');
        $company->address = $request->input('address');
        $company->cellphone = $request->input('phone');
        $company->email = $request->input('email');
        $company->status = $request->input('status');

        $company->depa_id = $request->input('depa_id');
        $company->munidepa_id = $request->input('munidepa_id');
        $company->tipoest_id = $request->input('tipoest_id');

        if(isset($request->imagen)){
            $company->logo = SubirImagen($request->imagen, 'companies');
        }

        $company->save();

        Cash::create(['cash_name'=>'Caja General', 'company_id'=>$company->id,'cash_value'=>0]);

        if (!$request->ajax()) {
            return redirect()->route('companies.index')->with('success', _lang('Saved sucessfully'));
        }
        
        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $company]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $info = Company::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.company.edit', compact('info', 'id'));
        } else {
            return view('backend.accounting.company.modal.edit', compact('info', 'id'));
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
            'company_name' => 'required|max:100',
            'address' => 'required|max:250',
            'phone' => 'required|max:20',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('companies.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $company = Company::find($id);
        $company->company_name = $request->input('company_name');
        $company->address = $request->input('address');
        $company->cellphone = $request->input('phone');
        $company->email = $request->input('email');
        $company->status = $request->input('status');

        $company->depa_id = $request->input('depa_id');
        $company->munidepa_id = $request->input('munidepa_id');
        $company->tipoest_id = $request->input('tipoest_id');

        if(isset($request->imagen)){
            if(isset($company->image)){
                Storage::delete(str_replace('storage', 'public', $company->logo));
            }
            $company->logo = SubirImagen($request->imagen, 'companies');
        }

        $company->save();

        if (!$request->ajax()) {
            return redirect()->route('companies.index')->with('success', _lang('Updated sucessfully'));
        } 
        
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $company]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $productunit = Company::find($id);
        $productunit->delete();
        return redirect('companies')->with('success', _lang('Deleted'));
    }
}
