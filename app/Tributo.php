<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Tributo extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'trib_id';
    protected $table = 'tributos';
    protected $keyType = 'string';
}