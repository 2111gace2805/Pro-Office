<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Depreciation extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    protected $primaryKey = 'id';
    protected $table = 'depreciation';

    protected $fillable = [
        'typeid',
        'assetid',
        'period',
        'assetvalue',
        'deTotal',
        'bookValue'
    ];

    public function asset()
    {
        return $this->belongsTo(Assets::class, 'assetid', 'id');
    }

    public function assetType()
    {
        return $this->hasOne(AssetsType::class, 'id', 'typeid');
    }
}
