<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Purchase extends Model
{
    use SingleTenant;
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_orders';

    public function purchase_items()
    {
        return $this->hasMany('App\PurchaseOrderItem',"purchase_order_id");
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier',"supplier_id")->withDefault();
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax',"tax_id")->withDefault();
    }

    public function getOrderDateAttribute($value)
    {
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }

}