<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class ActividadEconomica extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'actie_id';
    protected $table = 'actividad_economica';
    protected $keyType = 'string';
}