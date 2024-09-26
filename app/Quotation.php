<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Quotation extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quotations';
    
	public function quotation_items()
    {
        return $this->hasMany('App\QuotationItem',"quotation_id");
    }

    public function client()
    {
        return $this->belongsTo('App\Contact',"client_id")->withDefault();
    }

    public function getQuotationDateAttribute($value)
    {
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }

    public function taxes()
    {
        return $this->hasMany('App\QuotationItemTax',"quotation_id");
    }

}