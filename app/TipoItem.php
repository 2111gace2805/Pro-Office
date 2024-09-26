<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class TipoItem extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    protected $primaryKey = 'tipoitem_id';
    protected $table = 'tipo_item';
    protected $keyType = 'string';
}
