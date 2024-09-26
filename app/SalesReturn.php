<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class SalesReturn extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_return';

    public function sales_return_items()
    {
        return $this->hasMany('App\SalesReturnItem',"sales_return_id");
    }

    public function customer()
    {
        return $this->belongsTo('App\Contact',"customer_id")->withDefault();
    }
	
	public function account()
    {
        return $this->belongsTo('App\Account',"account_id")->withDefault();
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax',"tax_id")->withDefault();
    }

    public function getReturnDateAttribute($value)
    {
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }
    
    public function invoice()
    {
        return $this->belongsTo('App\Invoice', 'invoice_id');
    }

}