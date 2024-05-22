<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Brand extends Model
{
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'brands';
    protected $primaryKey = 'brand_id';

}