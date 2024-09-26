<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kit extends Model{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'products' => 'array',
    ];
}
