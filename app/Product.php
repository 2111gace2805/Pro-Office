<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Product extends Model
{
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';
    protected $guarded = ['id'];

    public function supplier()
    {
        return $this->belongsTo('App\Supplier',"supplier_id")->withDefault();
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax',"tax_id")->withDefault();
    }

    public function item()
    {
        // return $this->belongsTo(Item::class, 'product_id', 'item_id');
        return $this->belongsTo('App\Item', 'item_id')->withDefault();
    }

    public function brand(){
        return $this->belongsTo('App\Brand', 'brand_id');
    }

    public function unidad_medida(){
        return $this->belongsTo('App\UnidadMedida', 'unim_id');
    }
    
    public function product_group(){
        return $this->belongsTo('App\ProductGroup', 'prodgrp_id');
    }

    public function transferItem()
    {
        return $this->hasOne(TransferItem::class, 'product_id');
    }
	
}