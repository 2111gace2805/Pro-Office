<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Stock extends Model
{
    // use SingleTenant; //SE COMENTA PARA PODER CREAR LOS ITEMS Y STOCK EN TODAS LAS SUCURSALES CUANDO SE CREA UN PRODUCTO
    use LogsActivityTrait;
    protected $guarded = ['id'];
    protected $fillable = ['product_id', 'quantity', 'company_id'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'current_stocks';


    public function product()
    {
        return $this->belongsTo('App\Product',"product_id")->withDefault();
    }

    public function company(){
        return $this->belongsTo('App\Company', 'company_id');
    }

}