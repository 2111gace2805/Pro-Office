<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Service extends Model
{
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';


    public function tax()
    {
        return $this->belongsTo('App\Tax',"tax_id")->withDefault();
    }
}