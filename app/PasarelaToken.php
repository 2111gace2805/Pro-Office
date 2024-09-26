<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivityTrait;


class PasarelaToken extends Model
{
    use SoftDeletes;
    use LogsActivityTrait;


    protected $fillable = [
        'token',
        'created_token',
        'expired_token',
        'status',
    ];
}
