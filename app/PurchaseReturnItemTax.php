<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class PurchaseReturnItemTax extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_return_item_taxes';

}