<?php

namespace App;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralDiscount extends Model
{
    // use SingleTenant; //SE COMENTA PARA PODER CREAR LOS ITEMS Y STOCK EN TODAS LAS SUCURSALES CUANDO SE CREA UN PRODUCTO
    use LogsActivityTrait;

    protected $primaryKey = 'id';
}
