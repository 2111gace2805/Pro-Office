<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Setting extends Model
{
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';
}