<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Transfer extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    public $timestamps = false;
    protected $table = 'transfers';
    protected $primaryKey = 'transfer_id';

    public function transferItems()
    {
        return $this->hasMany(TransferItem::class, 'transfer_id');
    }

    public function sendingCompany()
    {
        return $this->belongsTo(Company::class, 'company_send');
    }

    public function receivingCompany()
    {
        return $this->belongsTo(Company::class, 'company_receive');
    }
    public function items()
    {
        return $this->hasMany(TransferItem::class, 'transfer_id');
    }
    public function product()
    {
        return $this->hasOne('App\Product',"item_id")->withDefault();
    }
}
