<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class PurchaseReturn extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_return';

    public function purchase_return_items()
    {
        return $this->hasMany('App\PurchaseReturnItem',"purchase_return_id");
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier',"supplier_id")->withDefault();
    }
	
	public function account()
    {
        return $this->belongsTo('App\Account',"account_id");
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax',"tax_id");
    }

    public function getReturnDateAttribute($value)
    {
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }

}