<?php

namespace App;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlockDte extends Model
{
    protected $fillable = [
        'type_dte',
        'correlativo',
    ];
}
