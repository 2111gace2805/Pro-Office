<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class TipoDocumento extends Model
{
    use SingleTenant;
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'tipodoc_id';
    protected $table = 'tipo_documento';
    protected $keyType = 'string';
}