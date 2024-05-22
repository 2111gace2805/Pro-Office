<?php

namespace App\Http\Controllers;

use App\Assets;
use App\AssetsType;
use App\Brand;
use App\Company;
use App\Depreciation;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivoController extends Controller
{
    public function index()
    {
        $assets = DB::table('assets')
        ->join('asset_type', 'assets.typeid', '=', 'asset_type.id')
        ->join('brands', 'assets.brandid', '=', 'brands.brand_id')
        ->join('companies', 'assets.locationid', '=', 'companies.id')
        ->where('assets.checkstatus', 0) 
        ->select('assets.id', 'assets.assettag', 'assets.name', 'asset_type.name as asset_type_name', 'brands.brand_name', 'companies.company_name')
        ->get();


        return view('backend.fixed_assets.index', compact('assets'));
    }

    public function createModal(Request $request)
    {
        $supplier = Supplier::all();
        $company = Company::all();
        $brand = Brand::all();
        $assetsType = AssetsType::all();

        if (!$request->ajax()) {
            // return view('backend.fixed_assets.create_modal');
        } else {
            return view('backend.fixed_assets.create_modal', compact('supplier', 'company', 'brand', 'assetsType'));
        }
        // return view('backend.fixed_assets.create_modal', compact('supplier', 'company', 'brand', 'assetsType'));
    }

    public function tipoActivo(Request $request)
    {
        if (!$request->ajax()) {
            // return view('backend.fixed_assets.create_modal');
        } else {
            return view('backend.fixed_assets.tipo-activo');
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'assettag' => 'required|string',
            'supplierid' => 'required|exists:suppliers,id',
            'locationid' => 'required|exists:companies,id',
            'brandid' => 'required|exists:brands,brand_id',
            'serial' => 'required|string',
            'typeid' => 'required|exists:asset_type,id',
            'cost' => 'required|numeric',
            'purchasedate' => 'required|date',
            'quantity' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'required|string',
        ]);

        $asset = new Assets([
            'name' => $request->input('name'),
            'assettag' => $request->input('assettag'),
            'supplierid' => $request->input('supplierid'),
            'locationid' => $request->input('locationid'),
            'brandid' => $request->input('brandid'),
            'serial' => $request->input('serial'),
            'typeid' => $request->input('typeid'),
            'cost' => $request->input('cost'),
            'purchasedate' => $request->input('purchasedate'),
            'quantity' => $request->input('quantity'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
        ]);

        $asset->save();

        return redirect()->route('activos.index');
    }

    public function show($id)
    {
        $asset = DB::table('assets')
            ->join('asset_type', 'assets.typeid', '=', 'asset_type.id')
            ->join('brands', 'assets.brandid', '=', 'brands.brand_id')
            ->join('companies', 'assets.locationid', '=', 'companies.id')
            ->join('suppliers', 'assets.supplierid', '=', 'suppliers.id')
            ->select(
                'assets.id',
                'assets.assettag',
                'assets.name',
                'asset_type.name as asset_type_name',
                'brands.brand_name',
                'companies.company_name',
                'serial',
                'purchasedate',
                'assets.status',
                'cost',
                'quantity',
                'suppliers.supplier_name',
                'assets.description'
            )
            ->where('assets.id', $id) 
            ->first();
    
        $maintenanceRecords = DB::table('maintenance')
            ->join('assets', 'maintenance.assetid', '=', 'assets.id')
            ->join('suppliers', 'maintenance.supplierid', '=', 'suppliers.id')
            ->select(
                'maintenance.id',
                'assets.assettag',
                'assets.name as asset_name',
                'suppliers.supplier_name',
                'maintenance.type',
                'maintenance.cost',
                'maintenance.status',
                'maintenance.startdate',
                'maintenance.enddate'
            )
            ->where('assetid', $id)
            ->get();
    
            $depreciations = DB::table('depreciation')
            ->select(
                'id',
                'assetid',
                'period',
                'assetvalue',
                'deTotal',
            )
            ->where('assetid', $id)
            ->get();

        $calcularValorEnLibros = function($valorAnterior, $depreciacionAcumulada, $periodo) {
            return $valorAnterior - $depreciacionAcumulada;
        };

            
        return view('backend.fixed_assets.show', compact('asset', 'maintenanceRecords', 'depreciations', 'calcularValorEnLibros'));
    }

    
    public function edit(Request $request, $id)
    {
        $asset = DB::table('assets')->where('id', $id)->first();
        $supplier = Supplier::all();
        $company = Company::all();
        $brand = Brand::all();
        $assetsType = AssetsType::all();

        if (!$request->ajax()) {
            // return view('backend.fixed_assets.create_modal');
        } else {
            return view('backend.fixed_assets.edit', compact('asset', 'supplier', 'company', 'brand', 'assetsType'));
        }
        // return view('backend.fixed_assets.edit', compact('asset', 'supplier', 'company', 'brand', 'assetsType'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'assettag' => 'required|string',
            'supplierid' => 'required|exists:suppliers,id',
            'locationid' => 'required|exists:companies,id',
            'brandid' => 'required|exists:brands,brand_id',
            'serial' => 'required|string',
            'typeid' => 'required|exists:asset_type,id',
            'cost' => 'required|numeric',
            'purchasedate' => 'required|date',
            'quantity' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'required|string',
        ]);

        DB::table('assets')->where('id', $id)->update([
            'name' => $request->input('name'),
            'assettag' => $request->input('assettag'),
            'supplierid' => $request->input('supplierid'),
            'locationid' => $request->input('locationid'),
            'brandid' => $request->input('brandid'),
            'serial' => $request->input('serial'),
            'typeid' => $request->input('typeid'),
            'cost' => $request->input('cost'),
            'purchasedate' => $request->input('purchasedate'),
            'quantity' => $request->input('quantity'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
            'explicacion' => $request->input('explicacion')
        ]);

        return redirect()->route('asset.show', $id)->with('success', 'Activo actualizado exitosamente');
    }

    public function softDelete($id, Request $request)
    {
         // Validar que el campo "motivo" esté presente
        $request->validate([
            'motivo' => 'required',
        ]);

        // Actualiza el campo checkstatus a 1 y guarda el motivo de la baja
        DB::table('assets')->where('id', $id)->update([
            'checkstatus' => 1,
            'explicacion' => $request->input('motivo'),
        ]);

        return redirect()->route('activos.index')->with('success', 'Activo eliminado exitosamente');
    }

    public function deBaja()
    {
        // en checkstatus = 1 los que estan de baja
        $assets = DB::table('assets')
        ->join('asset_type', 'assets.typeid', '=', 'asset_type.id')
        ->join('brands', 'assets.brandid', '=', 'brands.brand_id')
        ->join('companies', 'assets.locationid', '=', 'companies.id')
        ->where('assets.checkstatus', 1) 
        ->select('assets.id', 'assets.assettag','assets.explicacion', 'assets.name', 'asset_type.name as asset_type_name', 'brands.brand_name', 'companies.company_name')
        ->get();


        return view('backend.fixed_assets.activos-baja', compact('assets'));
    }

    public function createTipoActivo()
    {
        return view('asset_types.create');
    }

    public function storeTipoActivo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        AssetsType::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('activos.index')->with('success', 'Tipo de activo creado exitosamente');
    }

    
}
