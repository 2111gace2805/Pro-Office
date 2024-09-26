<?php

namespace App;
use App\Traits\SingleTenant;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class InvoiceItem extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_items';

    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo('App\Item',"item_id")->withDefault();
    }

    public function taxes()
    {
        return $this->hasMany('App\InvoiceItemTax',"invoice_item_id");
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'type_dte_rel');
    }

}