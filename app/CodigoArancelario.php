<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class CodigoArancelario extends Model
{
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'codigos_arancelarios';
    protected $primaryKey = 'codaran_id';
    public $timestamps = false;
}