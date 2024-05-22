<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class PurchaseReturnItem extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_return_items';

    public function item()
    {
        return $this->belongsTo('App\Item',"product_id")->withDefault();
    }

    public function taxes()
    {
        return $this->hasMany('App\PurchaseReturnItemTax',"purchase_return_item_id");
    }
}