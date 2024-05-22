<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incoterm extends Model
{
    protected $fillable = [
        'id',
        'nombre_incoterms',
    ];
}
