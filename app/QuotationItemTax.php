<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class QuotationItemTax extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quotation_item_taxes';

    public function tax(){
        return $this->belongsTo('App\Tax',"tax_id")->withDefault();
    }

}