<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Item extends Model
{
    // use SingleTenant; //SE COMENTA PARA PODER CREAR LOS ITEMS Y STOCK EN TODAS LAS SUCURSALES CUANDO SE CREA UN PRODUCTO
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $table = 'items';

    public function product()
    {
        return $this->hasOne('App\Product',"item_id")->withDefault();
    }
	
	
	public function service()
    {
        return $this->hasOne('App\Service',"item_id")->withDefault();
    }
	
    public function product_stock()
    {
        return $this->hasMany('App\Stock',"product_id");
    }

    public function stock(){
        return $this->product_stock()->where('company_id', company_id())->first();
    }

      public function transferItems()
    {
        return $this->hasMany('App\TransferItem', 'item_id', 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, "cate_id", "id");
    }

    
}