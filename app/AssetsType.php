<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class AssetsType extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    protected $table = 'asset_type';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
    ];

    public function depreciation()
    {
        return $this->belongsTo(Depreciation::class, 'id', 'typeid');
    }

    public function assets()
    {
        return $this->hasMany(Assets::class, 'typeid', 'id');
    }
}
