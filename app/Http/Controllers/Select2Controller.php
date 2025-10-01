<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Select2Controller extends Controller
{

	public function __construct()
    {
		// date_default_timezone_set(get_option('timezone','Asia/Dhaka'));	
		date_default_timezone_set(get_option('timezone','America/El_Salvador'));
	}
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_table_data(Request $request)
    {
		$data_where = array(
		   //'1' => array('company_id'=> company_id()), //general company Data
		   '2' => array('item_type'=> 'product'), //Item Type Product
		   '3' => array('type'=> 'income'), //Income Category
		   '4' => array('type'=> 'expense'), //Expense Category
		   '5' => array('item_type'=> 'service'), //Item Type Service,
		   '6' => array('descargado'=> 'no'), // invoices,
		);
	
		
        $table = $request->get('table');
        $value = $request->get('value');
        $display = $request->get('display');
        $display2 = $request->get('display2');
        $display2label = $request->get('display2label')??'';
        $where = $request->get('where');
		
        $q = $request->get('q')??$request->get('term')??'';
	
	    $display_option = "$display as text";
		if($display2 != ''){
			// $display_option = "CONCAT(ifnull($display,''),' - ', '$display2label', ifnull($display2,'')) AS text";
			$display_option = "IF(LTRIM(ifnull($display2, '')) != '', CONCAT($display, ' - ', '$display2label: ', $display2), $display) AS text";
		}

		$where_extra = $request->get('where_extra');

		$query = DB::table($table)->when($where_extra != NULL, function($q) use ($where_extra) {
			return $q->whereRaw($where_extra);
		})->when(Schema::hasColumn($table, 'deleted_at'), function($q) use ($table) {
			return $q->whereRaw($table.'.deleted_at is null');
		});
		
		// $query = $query->when($table == 'contacts', function($q) {
		// 	return $q->where('company_id', company_id());
		// });
		
		if($request->items != "undefined" && $request->items != null){
			$result = $query->selectRaw("$table.$value as id, concat(p.product_code, ' - ', $table.item_name, ' | ', if(p.original = 'si', 'Original', ifnull(p.generic, '')), ' | ', IFNULL(cs.quantity, 0), ' disponibles') as text")
			->join('products as p', 'p.item_id', "$table.id")
			->when($request->items == 'con_stock', function($query){
				return $query->join('current_stocks as cs', 'cs.product_id', 'p.item_id')
							->where("cs.company_id", company_id())
							->where('quantity', '>', 0);
			})
			->when($request->items == 'all', function($query){
				return $query->join('current_stocks as cs', function($join){
					return $join->on('cs.product_id', '=', 'p.item_id')
					->where("cs.company_id", company_id());
				});
				// return $query->leftJoin('current_stocks as cs', 'cs.product_id', 'p.item_id')
				// 			->whereRaw("(cs.company_id is null or cs.company_id = ".company_id().")");
			})
			->whereRaw("(product_code LIKE '%$q%' OR item_name LIKE '%$q%' OR description LIKE '%$q%')")
			->when(($where != '' && $where != null), function($query) use ($data_where, $where) {
				return $query->where($data_where[$where]);
			});
		}else{
			if ($table == 'invoices') {
				$display_option = "(concat('#', invoice_number, ' Fecha: ', invoice_date, ' - ', ifnull((select c.company_name from contacts c where c.id = invoices.client_id), ''))) as text";
			}
			if ($table == 'invoice_items') {
				$display_option = "quantity, (select prod.product_code from products prod where prod.item_id = invoice_items.item_id) as product_code, description as text, unit_cost, sub_total";
				// $display_option = "(concat('CANT:  ', quantity, '    CODIGO:  ', (select prod.product_code from products prod where prod.item_id = invoice_items.item_id), '    DESCRIPCION:  ', description, '    PRECIO U:  ', unit_cost, '    SUBTOTAL:  ', sub_total)) as text";
			}

			$result = $query
				->select("$table.$value as id", DB::raw($display_option))
				->where(function($query) use ($q, $display, $display2){
					return $query->where($display,'LIKE',"%$q%")
					->when(($display2 != '' && $display2 != null), function($query) use ($display2, $q){
						return $query->orWhere($display2,'LIKE',"%$q%");
					});
				})
				->when($table === 'items', function($query) use ($table){
					return $query->where("$table.company_id", company_id());
				})
				->when(($where != '' && $where != null), function($query) use ($data_where, $where){
					return $query->where($data_where[$where]);
				});
		}

		if (isset($request->no_paginate)) {
			return $result->get();
		}
	    
		// $result = $result->get();	  
		$result = $result->simplePaginate(20);	  

		$morePages=true;
           if (empty($result->nextPageUrl())){
            $morePages=false;
           }
            $result = array(
              "results" => $result->items(),
              "pagination" => array(
                "more" => $morePages
              )
            );
					  
		return $result;   
    }


    public function get_table_data_ccf(Request $request){	
		
        $table = $request->get('table');
        $value = $request->get('value');
        $display = $request->get('display');
        $display2 = $request->get('display2');
        $where = $request->get('where');
        $action = $request->get('action');

		$where_1 = 'tipodoc_id';
		$condition_1 = '=';
		$where_2 = 'client_id';
		$condition_2 = '=';


		$q = $request->get('q')??$request->get('term')??'';

		if( $action != '' ){
			$where_1 = 'status';
			$condition_1 = '!=';
			$where_2 = 'numero_control';
			$condition_2 = '!=';
		}
	
	    $display_option = "CONCAT($display, '-', contacts.company_name, ' - $', (subtotal - iva_retenido )) as text";

		$where_extra = $request->get('where_extra');

		$result = DB::table($table)
				->select("$table.id as id", DB::raw($display_option))
				->join('contacts', 'contacts.id', '=', $table . '.client_id')
				->where($where_1, $condition_1, $where)
				->where($where_2, $condition_2, $where_extra);

		if (!empty($q)) {
			$result = $result->where(function($query) use ($display, $display2, $q) {
				$query->where($display, 'LIKE', '%' . $q . '%')
						->orWhere($display2, 'LIKE', '%' . $q . '%')
						->orWhere('contacts.company_name', 'LIKE', '%' . $q . '%')
						->orWhere(DB::raw("FORMAT(subtotal - iva_retenido, 2)"), 'LIKE', '%' . $q . '%');
			});
		}
				
		if (isset($request->no_paginate)) {
			return $result->get();
		}
		
		$result = $result->simplePaginate(20);	  

		$morePages=true;
           if (empty($result->nextPageUrl())){
            $morePages=false;
           }
            $result = array(
              "results" => $result->items(),
              "pagination" => array(
                "more" => $morePages
              )
            );

		return $result;   
    }
	  
}
