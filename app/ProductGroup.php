<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class ProductGroup extends Model
{
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_groups';
    protected $primaryKey = 'prodgrp_id';

}