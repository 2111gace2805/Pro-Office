<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Maintenance extends Model
{
    use HasFactory;
    use LogsActivityTrait;

    protected $primaryKey = 'id';
    protected $table = 'maintenance';
    protected $fillable = [
        'assetid',
        'supplierid',
        'startdate',
        'enddate',
        'type',
        'cost', 
        'status',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'assetid');
    }
}
