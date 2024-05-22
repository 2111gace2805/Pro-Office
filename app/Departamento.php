<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Departamento extends Model
{
    use SingleTenant;
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'depa_id';
    protected $table = 'departamentos';
    protected $keyType = 'string';
}