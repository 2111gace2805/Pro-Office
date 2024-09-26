<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Assets extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    protected $table = 'assets';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id', 'name','supplierid', 'assettag', 'brandid', 'locationid', 'typeid', 'serial', 'purchasedate', 'cost', 'status', 'quantity', 'description'];

    public function depreciation()
    {
        return $this->hasOne(Depreciation::class, 'assetid', 'id');
    }

    public function assetType()
    {
        return $this->belongsTo(AssetsType::class, 'typeid', 'id');
    }
}
