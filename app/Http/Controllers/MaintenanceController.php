<?php

namespace App\Http\Controllers;

use App\Assets;
use App\AssetsType;
use App\Maintenance;
use App\Supplier;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $maintenance = Maintenance::leftJoin('assets', 'maintenance.assetid', '=', 'assets.id')
        ->leftJoin('suppliers', 'maintenance.supplierid', '=', 'suppliers.id')
        ->select('maintenance.*','assettag', 'assets.name as asset_name', 'suppliers.supplier_name')
        ->get();


        return view('backend.maintenance.index', compact('maintenance'));
    }

    public function create(Request $request)
    {
        // $assetsType = Assets::all();
        $assetsType = Assets::leftJoin('companies', 'assets.locationid', '=', 'companies.id')
        ->select('assets.name', 'companies.company_name', 'assets.checkstatus', 'assets.id')
        ->get();
        $supplier = Supplier::all();

        if (!$request->ajax()) {
            // return view('backend.fixed_assets.create_modal');
        } else {
            return view('backend.maintenance.create', compact('supplier', 'assetsType'));
        }
        // return view('backend.maintenance.create', compact('supplier', 'assetsType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assetid' => 'required|integer',
            'supplierid' => 'required|integer',
            'cost' => 'required',
            'status' => 'required',
            'startdate' => 'required|date',
            'enddate' => 'required|date',
            'type' => 'required|string|max:255',
        ]);

        $maintenance = new Maintenance([
            'assetid' => $request->input('assetid'),
            'supplierid' => $request->input('supplierid'),
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'type' => $request->input('type'),
            'cost' => $request->input('cost'),
            'status' => $request->input('status')
        ]);

        $maintenance->save();

        return redirect()->route('lista-mantenimiento')->with('success', 'Mantenimiento creado exitosamente');
    }

    public function edit(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $assetsType = Assets::leftJoin('companies', 'assets.locationid', '=', 'companies.id')
        ->select('assets.name', 'companies.company_name', 'assets.checkstatus', 'assets.id')
        ->get();
        $supplier = Supplier::all();
        
        
        if (!$request->ajax()) {
            // return view('backend.fixed_assets.create_modal');
        } else {
            return view('backend.maintenance.edit', compact('maintenance', 'assetsType', 'supplier'));
        }
        // return view('backend.maintenance.edit', compact('maintenance', 'assetsType', 'supplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'assetid' => 'required|integer',
            'supplierid' => 'required|integer',
            'cost' => 'required',
            'status' => 'required',
            'startdate' => 'required|date',
            'enddate' => 'required|date',
            'type' => 'required|string|max:255',
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'assetid' => $request->input('assetid'),
            'supplierid' => $request->input('supplierid'),
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'type' => $request->input('type'),
            'cost' => $request->input('cost'),
            'status' => $request->input('status')
        ]);

        return redirect()->route('lista-mantenimiento')->with('success', 'Mantenimiento actualizado exitosamente');
    }

    public function destroy($id)
    {
        // Obtener el mantenimiento que se va a eliminar
        $maintenance = Maintenance::findOrFail($id);

        // Eliminar el mantenimiento
        $maintenance->delete();

        // Redireccionar con un mensaje de éxito
        return redirect()->route('lista-mantenimiento')->with('success', 'Mantenimiento eliminado exitosamente');
    }


}
