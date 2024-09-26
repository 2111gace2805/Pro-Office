<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class UnidadMedida extends Model
{
    use SingleTenant;
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'unim_id';
    protected $table = 'unidades_medida';
    protected $keyType = 'string';
}