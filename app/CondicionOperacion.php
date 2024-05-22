<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class CondicionOperacion extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'conop_id';
    protected $table = 'condicion_operacion';
    protected $keyType = 'string';
}