<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderNote extends Model {


    use HasFactory;
    use SoftDeletes;


    protected $casts = [
        'details' => 'array',
    ];

    public function client(){
        return $this->belongsTo('App\Contact',"client_id")->withDefault();
    }

    public function product(){
        return $this->belongsTo('App\Product', 'product_id', 'id')->withDefault();
    }

    public function getProductIdFromDetailsAttribute(){
        return $this->details['product_id'] ?? null;
    }
}
