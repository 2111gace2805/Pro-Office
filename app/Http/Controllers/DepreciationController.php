<?php

namespace App\Http\Controllers;

use App\Assets;
use App\AssetsType;
use App\Depreciation;
use Illuminate\Http\Request;

class DepreciationController extends Controller
{
    public function index()
    {
        $depreciation = Depreciation::with('asset')->get();
        $assetsType   = AssetsType::all();
        
        $asset = Assets::leftJoin('companies', 'assets.locationid', '=', 'companies.id')
        ->select('assets.name', 'companies.company_name', 'assets.checkstatus', 'assets.id', 'assets.cost')
        ->get();
       

        return view('backend.depreciation.index', compact('depreciation', 'assetsType', 'asset'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assetid' => 'required',
            'period' => 'required',
            'assetvalue' => 'required'
        ]);
    
        // Calcular depreciacion
        $deTotal = ($request->input('assetCost') - $request->input('assetvalue')) / $request->input('period');
    
        $depreciation = new Depreciation([
            'assetid'       => $request->input('assetid'),
            'period'        => $request->input('period'),
            'assetvalue'    => $request->input('assetvalue'),
            'deTotal'       => $deTotal,
        ]);
    
        $depreciation->save();
    
        return redirect()->route('depreciation.index');
    }
    

    public function update(Request $request, $id)
{
    $request->validate([
        'assetidEdit' => 'required|integer',
        'periodEdit' => 'required|integer',
        'assetvalueEdit' => 'required',
    ]);

    $depreciation = Depreciation::findOrFail($id);

    $depreciation->update([
        'assetid' => $request->input('assetidEdit'),
        'period' => $request->input('periodEdit'),
        'assetvalue' => $request->input('assetvalueEdit'),
    ]);

    return redirect()->route('depreciation.index')->with('success', 'Registro actualizado exitosamente.');
}

    
    public function destroy($id)
    {
        $depreciation = Depreciation::find($id);

        if (!$depreciation) {
            return redirect()->route('depreciation.index')->with('error', 'Registro no encontrado.');
        }

        $depreciation->delete();

        return redirect()->route('depreciation.index')->with('success', 'Registro eliminado exitosamente.');
    }




}
