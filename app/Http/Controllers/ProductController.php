<?php

namespace App\Http\Controllers;

use App\Company;
use App\Item;
use App\Product;
use App\Stock;
use App\TipoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Supplier;
use App\Category;
use App\Brand;
use App\ProductGroup;
use DataTables;
use Illuminate\Support\Facades\Auth;
use App\Kit;
use Illuminate\Support\Collection;

class ProductController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        // $query = Item::where('company_id', Session::get('company')->id)
        //               ->orderBy("id", "desc");
    
        // if ($request->has('item_type')) {
        //     $itemType = $request->input('item_type');
        //     if ($itemType != 'all') {
        //         $query = Item::where('company_id', Session::get('company')->id)
        //         ->where('item_type', $itemType)
        //         ->orderBy("id", "desc");

        //     }
        // }
    
        // $items = $query->take(50)->get();
    
        return view('backend.accounting.product.list');
    }

    public function get_table_data(Request $request)
    {

        $currency = currency();

        $items = Item::where('company_id', Session::get('company')->id)
                      ->orderBy("id", "desc");

        return Datatables::eloquent($items)
            ->filter(function ($query) use ($request) {

                if( $request->has('item_type') ){
                    $itemType = $request->get('item_type');

                    if( $itemType != 'all' ){
                        $query->where('item_type', $itemType);
                    }
                }

                if ($request->has('search.value')) {
                    $searchValue = $request->input('search.value');
                    $query->where('item_name', 'like', "%{$searchValue}%");
                }
            })
            ->addColumn('product_code', function($item) {
                return $item->product->product_code;
            })
            ->addColumn('image', function($item) {
                return '<img src="' . asset($item->product->image) . '" alt="image" height="50">';
            })
            ->addColumn('item_name', function($item) {
                return $item->item_name;
            })
            ->addColumn('description', function($item) {
                return $item->product->description;
            })
            ->addColumn('product_cost', function($item)  use ($currency) {
                return decimalPlace($item->product->product_cost, $currency);
            })
            ->addColumn('product_price', function($item)  use ($currency) {
                return decimalPlace($item->product->product_price, $currency);
            })
            ->addColumn('product_stock', function($item) {
                $stock = $item->product_stock->where('company_id', session('company')->id)->first();
                return $stock ? $stock->quantity : '';
            })
            ->addColumn('action', function ($item) {
                $actions = '<a href="' . route('products.edit', $item->id) . '" class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>';
                $actions .= '<a href="' . route('products.show', $item->id) . '" class="btn btn-primary btn-sm ajax-modal"><i class="ti-eye"></i></a>';
                $actions .= '<form action="' . route('products.destroy', $item->id) . '" method="post" style="display: inline;">';
                $actions .= csrf_field();
                $actions .= method_field('DELETE');
                $actions .= '<button type="submit" class="btn btn-danger btn-sm btn-remove"><i class="ti-trash"></i></button>';
                $actions .= '</form>';
                
                if (Auth::user()->user_type == 'admin') {
                    $actions .= '<a href="' . route('stock.modal', ['id' => $item->id]) . '" class="btn btn-primary btn-sm ajax-modal"><i class="ti-settings"></i></a>';
                }
                
                return $actions;
            })

            ->setRowId(function ($item) {
                return "row_" . $item->id;
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.product.create');
        } else {
            return view('backend.accounting.product.modal.create');
        }
    }

    public function createService(Request $request) {

        if( !$request->ajax() ){
            return view('backend.accounting.product.create_services',);
        }
        else{
            return view('backend.accounting.product.modal.create_services');
        }
    }

    public function storeService(Request $request) {

        return $this->store($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        return DB::transaction(function() use ($request){
            $validator = Validator::make($request->all(), [
                // 'item_name'     => 'required',
                // 'product_cost'  => 'required|numeric',
                // 'product_price' => 'required|numeric',
                // 'product_unit'  => 'required',
                // // 'imagen'  => 'required',
                // 'category'  => 'required',
                // // 'weight' => 'numeric',
                //  'product_code'  => 'required',
            ]);
    
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                } else {
                    return redirect()->route('products.create')
                        ->withErrors($validator)
                        ->withInput();
                }
            }

            $companies = Company::get();
            $ids_productos = new Collection();
            $path = '';

            foreach( $companies as $key => $company ){

                //Create Item
                $item            = new Item();
                $item->item_name = $request->input('item_name')??'';
                if($request->input('tipoitem_id') == 1){
                    $item->item_type = 'product';
                } else {
                    $item->item_type = 'service';
                }
                // $item->company_id = Session::get('company')->id;
                $item->company_id = $company->id;
                $item->tipoitem_id = $request->input('tipoitem_id'); // ref tabla tipo_item 1 es: Bienes
                $item->save();
        
                //Create Product
                $product                = new Product();
                $product->item_id       = $item->id;
                $product->supplier_id   = $request->input('supplier_id');
                $product->product_cost  = $request->input('product_cost');
                $product->product_price = $request->input('product_price');
                $product->product_unit  = $request->input('product_unit');
                $product->description   = $request->input('description');
                $product->category_id   = $request->input('category');
                $product->weight        = $request->input('weight');
                $product->note          = $request->input('note');
                $product->comment       = $request->input('comment');
                $product->product_code  = $request->input('product_code');
    
                $product->brand_id  = $request->input('brand_id');
                $product->original  = $request->input('original');
                // $product->generic  = $request->input('generic');
                $product->model  = $request->input('model');
                // $product->lawson_number  = $request->input('lawson_number');
                $product->prodgrp_id  = $request->input('prodgrp_id');
                // $product->codaran_id  = $request->input('codaran_id');
                if ($request->input('tipoitem_id') == 1) {
                    // Si es igual a 1(producto) debe ser 59
                    $product->unim_id = 59;
                } else {
                    //si no, debe ser 99 para que la factura electronica diferencie que es
                    $product->unim_id = 99;
                }
    
                $product->warranty_value  = $request->input('warranty_value');
                $product->warranty_type  = $request->input('warranty_type');
        
                if(isset($request->imagen)){
                    $product->image = SubirImagen($request->imagen, 'products');
                }
        
                $product->save();

                $ids_productos->push($product->id);
        
                //Create Stock Row
                $stock             = new Stock();
                // $stock->product_id = $product->id;
                $stock->product_id =  $item->id;
                $stock->quantity   = 0;
                $stock->company_id = $company->id;
                $stock->save();
            }

            if(isset($request->imagen)){
                $path = SubirImagen($request->imagen, 'products');
                Product::whereIn('id', $ids_productos)->update(['image' => $path]);
            }
    
    
            if (!$request->ajax()) {
                return redirect()->route('products.create')->with('success', _lang('Information has been added sucessfully'));
            }
            
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Information has been added sucessfully'), 'data' => $item]);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $item = Item::find($id);
        // $item = Item::join('products as p', 'p.item_id', 'items.id')
        // ->leftjoin('categories as c', 'p.category_id', 'c.id')
        // ->join('suppliers as s', 's.id', 'p.supplier_id')
        // ->leftjoin('current_stocks as cs', 'cs.product_id', 'p.id')
        // ->where('items.id', $id)->select('category_name', 'supplier_name', 'item_name', 'p.*', 'quantity')->first();

        if (!$request->ajax()) {
            return view('backend.accounting.product.view', compact('item', 'id'));
        } else {
            return view('backend.accounting.product.modal.view', compact('item', 'id'));
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
         // Mapeo de los valores de tipo de item
        $tipoItemMap = [
            'service' => 'Servicio',
            'product' => 'Producto'
        ];

        // Verificar si el valor está en el mapeo, si no, utilizar el valor original
        $tipoItemNombre = array_key_exists($item->item_type, $tipoItemMap) ? $tipoItemMap[$item->item_type] : $item->item_type;

        if (!$request->ajax()) {
            return view('backend.accounting.product.edit', compact('item', 'id', 'tipoItemNombre'));
        } else {
            return view('backend.accounting.product.modal.edit', compact('item', 'id', 'tipoItemNombre'));
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
            // 'item_name'     => 'required',
            'product_cost'  => 'required|numeric',
            'product_price' => 'required|numeric',
            'product_unit'  => 'required',
            'category'  => 'required',
            // 'weight'        => 'numeric',
            'product_code'  => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('products.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        //Update item
        $item = Item::find($id);

        if ($item) {

            $item->item_name = $request->input('item_name')??'';
            if ($request->input('tipoitem_nombre') === 'Servicio') {
                $item->item_type = 'service';
                $item->tipoitem_id = 2; // ref tabla tipo_item 1 es: Bienes
            } elseif ($request->input('tipoitem_nombre') === 'Producto') {
                $item->item_type = 'product';
                $item->tipoitem_id = 1; // ref tabla tipo_item 1 es: Bienes
            }
            $item->save();

            $product                = Product::where("item_id", $id)->first();
            $product->item_id       = $item->id;
            $product->supplier_id   = $request->input('supplier_id');
            $product->product_cost  = $request->input('product_cost');
            $product->product_price = $request->input('product_price');
            $product->product_unit  = $request->input('product_unit');
            $product->description   = $request->input('description');
            $product->category_id   = $request->input('category');
            $product->weight        = $request->input('weight');
            $product->note          = $request->input('note');
            $product->comment       = $request->input('comment');
            $product->product_code  = $request->input('product_code');

            if(isset($request->imagen)){
                if(isset($product->image)){
                    Storage::delete(str_replace('storage', 'public', $product->image));
                }
                $product->image = SubirImagen($request->imagen, 'products');
            }

            $product->brand_id  = $request->input('brand_id');
            $product->original  = $request->input('original');
            $product->generic  = $request->input('generic');
            $product->model  = $request->input('model');
            $product->lawson_number  = $request->input('lawson_number');
            $product->prodgrp_id  = $request->input('prodgrp_id');
            // $product->codaran_id  = $request->input('codaran_id');
            $product->warranty_value  = $request->input('warranty_value');
            $product->warranty_type  = $request->input('warranty_type');

            $product->save();
        } else {
            if (!$request->ajax()) {
                return redirect()->route('products.index')->with('error', _lang('Update Failed !'));
            } else {
                return response()->json(['result' => 'error', 'message' => _lang('Update Failed !')]);
            }
        }

        if (!$request->ajax()) {
            return redirect()->route('products.index')->with('success', _lang('Information has been updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Information has been updated sucessfully'), 'data' => $product]);
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

        $product = Product::where("item_id", $id);
        $product->delete();
        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }

    public function get_product(Request $request, $id) {


        $esKit = $request->query('kit');

        if( $esKit == 1 ){

            $kit = Kit::find($id);

            $item = [
                'id'        => $kit->id,
                'item_name' => $kit->name,
                'item_type' => 'kit'
            ];

            $product = [
                'description'   => $kit->name,
                'product_code'  => $kit->code,
                'unim_id'       => '59',
                'product_price' => $kit->amount,
                'tax'           => null
            ];

            echo json_encode(array("item" => $item, "product" => $product, "tax" => null, "unit_cost" => $kit->amount));

        }
        else{

            $item = Item::find($id);
            $product = Product::where('item_id', $id)->first();
            // Log::info($product);
            $product_stock = Stock::where('product_id', $item->id)->where('company_id', company_id())->first();
    
            if( $item->item_type == 'product' ){
                echo json_encode(array("item" => $item, "product" => $product, "brand_name" => optional($product->brand)->brand_name, "tax" => $product->tax, "available_quantity" => $product_stock->quantity??0));
            }
            else if( $item->item_type == 'service' ){
                echo json_encode(array("item" => $item, "product" => $product, "tax" => $product->tax, "unit_cost" => $product->product_price));
            }
        }

    }

    public function stock(Request $request, $id)
    {
        $item = Item::find($id);
        $companies = DB::table('companies')->get();

        $existingStock = Stock::where('product_id', $item->id)
            ->first();

        if (!$request->ajax()) {
            return view('backend.accounting.product.stock', compact('item', 'id', 'companies', 'existingStock'));
        } else {
            return view('backend.accounting.product.modal.stock', compact('item', 'id', 'companies', 'existingStock'));
        }
    }


    public function saveStock(Request $request, $id)
    {
        $request->validate([
            
            'minimo' => 'required|numeric',
            'maximo' => 'required|numeric',
        ]);

        // Encontrar el producto actual
        $item = Item::find($id);

        // Buscar si ya existe un registro con el mismo product_id y company_id
        $existingStock = Stock::where('product_id', $item->id)
            ->where('company_id', $request->sucursal)
            ->first();

        if ($existingStock) {
            $existingStock->minimo = $request->minimo;
            $existingStock->maximo = $request->maximo;

            $existingStock->save();
        }

        if (!$request->ajax()) {
            return redirect()->route('products.index')->with('success', 'La información se ha actualizado correctamente');
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => 'La información se ha actualizado correctamente', 'data' => $item]);
        }
    }

    public function cargarProductos(){
        return view('backend.accounting.product.load.create');
    }

    public function cargarProductosExcel(Request $request){
 
        $request->validate([
            'products_excel' => 'required|mimes:xlsx,xls',
        ]);

        try {
            set_time_limit(0);

            DB::beginTransaction();

            $file = $request->file('products_excel');
    
            // Leer el archivo Excel
            $spreadsheet = IOFactory::load($file);
    
            // Obtener la hoja por su nombre
            $sheet = $spreadsheet->getSheetByName('Inventario');
    
            // Verificar si la hoja existe
            if( !$sheet ){
                return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'La hoja especificada no fue encontrada en el archivo Excel.']);
            }
    
            // Obtener las filas y columnas máximas de la hoja
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
    
            // Inicializar un array para almacenar los datos de las filas
            $data = [];
            $columnas = ['nombre_item', 'tipo_item', 'company_id', 'tipo_item_mh', 'proveedor', 'costo_producto', 'precio_producto', 'unidad_producto', 'descripcion', 'categoria', 'peso', 'nota', 'comentario', 'codigo_producto', 'marca', 'original', 'modelo', 'grupo', 'unidad_medida_id', 'tipo_garantia', 'valor_garantia', 'cantidad_stock'];
    
            // Obtener los nombres de las columnas (encabezados)
            $header = [];
            foreach( $sheet->getRowIterator(1, 1) as $row ){
                foreach( $row->getCellIterator() as $cell ){
                    $header[] = $cell->getValue();
                }
            }

            // Verificar que todas las columnas esperadas estén presentes en el archivo Excel
            foreach( $columnas as $columna ){
                if( !in_array($columna, $header) ){
                    return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'La columna ' . $columna . ' no está presente en el archivo Excel.']);
                }
            }

            $batchSize  = 100;
            $batchCount = 0;
    
            for( $row = 2; $row <= $highestRow; $row++ ){
                $rowData = [];
    
                // Iterar sobre las columnas
                for( $col = 'A'; $col <= $highestColumn; $col++ ){
                    // Obtener el valor de la celda
                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    
                    // Agregar el valor de la celda al array de datos de la fila
                    $rowData[] = $cellValue;
                }
    
                // Combinar los nombres de las columnas con los valores de esta fila
                $data[] = array_combine($header, $rowData);

                // Procesar el lote actual si se ha alcanzado el tamaño del lote
                if (count($data) >= $batchSize) {
                    $this->procesarLote($data, $batchCount);
                    $data = [];
                    $batchCount++;
                }
            }

            if (!empty($data)) {
                $this->procesarLote($data, $batchCount);
            }

            DB::commit();
            
            Log::info("Se guardan productos");

            return response()->json(['result' => 'success', 'action' => 'store', 'message' => 'Productos cargados correctamente']);
        }
        catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error al importar productos: ' . $th->getMessage());

            // Imprimir el mensaje de error específico de la base de datos (si hay)
            if ($th instanceof \Illuminate\Database\QueryException && $th->errorInfo) {
                Log::error('Error de base de datos: ' . print_r($th->errorInfo, true));
            }

            return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'Error al guardar productos, intente nuevamente']);

        }
    }


    private function procesarLote($data, $batchCount){

        Log::info("Procesando lote " . ($batchCount + 1));

        // Convertir el array de datos en una colección de objetos
        $products = collect($data)->map(function ($item) {
            return (object) $item;
        });

        foreach( $products as $key => $product ){

            // Verificar si todos los campos de la fila están vacíos
            $isEmptyRow = true;
            foreach ($product as $value) {
                if (!empty($value)) {
                    $isEmptyRow = false;
                    break;
                }
            }

            // Si todos los campos de la fila están vacíos, omitir esta fila
            if ($isEmptyRow) {
                Log::info("Fila vacía omitida: " . json_encode($product));
                continue;
            }

            //Creo el item
            $item               = new Item();
            $item->item_name    = $product->nombre_item ?? '';
            $item->item_type    = ( $product->tipo_item == 1) ? 'product' : 'service';
            $item->company_id   = $product->company_id;
            $item->tipoitem_id  = $product->tipo_item_mh;
            $item->save();

            Log::info("Se crea item: ".json_encode($item));

            if( $product->proveedor != '' ){
                $proveedor  = Supplier::where('supplier_name', $product->proveedor)->first();
                
                if( !$proveedor ){
                    Log::info('No se encontro proveedor, se crea proveedor');

                    $proveedor = new Supplier();
                    $proveedor->supplier_name   = $product->proveedor;
                    $proveedor->company_name    = $product->proveedor;
                    $proveedor->company_id      = 1;
                    $proveedor->save();
                }

                Log::info('Datos de proveedor: '. json_encode($proveedor));
            }

            if( $product->categoria != '' ){
                $categoria  = Category::where('category_name', $product->categoria)->first();

                if( !$categoria ){
                    Log::info('No se encontro categoria, se crea categoria');
                    $categoria = new Category();
                    $categoria->category_name   = $product->categoria;
                    $categoria->save();
                }

                Log::info('Datos de categoria: '. json_encode($categoria));
            }

            if( $product->marca != '' ){
                $marca      = Brand::where('brand_name', $product->marca)->first();

                if( !$marca ){
                    Log::info('No se encontro marca, se crea marca');
                    $marca = new Brand();
                    $marca->brand_name   = $product->marca;
                    $marca->brand_status = 'Active';
                    $marca->save();
                }

                Log::info('Datos de marca: '. json_encode($marca));
            }

            if( $product->grupo != '' ){
                $grupo      = ProductGroup::where('prodgrp_name', $product->grupo)->first();

                if( !$grupo ){
                    Log::info('No se encontro grupo, se crea grupo');
                    $grupo = new ProductGroup();
                    $grupo->prodgrp_name   = $product->grupo;
                    $grupo->prodgrp_status = 'Active';
                    $grupo->save();
                }

                Log::info('Datos de grupo: '. json_encode($grupo));
            }
    
            //Creo el producto
            $pr                 = new Product();
            $pr->item_id        = $item->id;
            $pr->supplier_id    = ( $product->proveedor != '' ) ? $proveedor->id : null;
            $pr->product_cost   = $product->costo_producto;
            $pr->product_price  = $product->precio_producto;
            $pr->product_unit   = $product->unidad_producto;
            $pr->description    = $product->descripcion;
            $pr->category_id    = ( $product->categoria != '' ) ? $categoria->id : null;
            $pr->weight         = $product->peso;
            $pr->note           = $product->nota;
            $pr->comment        = $product->comentario;
            $pr->product_code   = $product->codigo_producto;
            $pr->brand_id       = ( $product->marca != '' ) ? $marca->brand_id : null;
            $pr->original       = $product->original;
            $pr->model          = $product->modelo;
            $pr->prodgrp_id     = ( $product->grupo != '' ) ? $grupo->prodgrp_id : null;
            $pr->unim_id        = $product->unidad_medida_id;
            $pr->warranty_type  = ( $product->tipo_garantia != '' ) ? $product->tipo_garantia : '';
            $pr->warranty_value = ( $product->valor_garantia != '' ) ? intval($product->valor_garantia) : '';
            $pr->save();

            Log::info("Se crea producto: ".json_encode($pr));
            
            //Create Stock Row
            $stock             = new Stock();
            $stock->product_id = $item->id;
            $stock->quantity   = $product->cantidad_stock;
            $stock->company_id = $product->company_id;
            $stock->save();
            Log::info("Se crea stock: ".json_encode($stock));
        }
    }

}