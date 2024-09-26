<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class PurchaseOrderItem extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_order_items';

    public function item()
    {
        return $this->belongsTo('App\Item',"product_id")->withDefault();
    }

    public function taxes()
    {
        return $this->hasMany('App\PurchaseOrderItemTax',"purchase_order_item_id");
    }
}