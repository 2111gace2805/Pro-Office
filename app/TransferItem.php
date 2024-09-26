<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class TransferItem extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    public $timestamps = false;
    protected $table = 'transfer_items';

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
