<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class District extends Model
{
    // use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'dist_id';

    public function municipio(){
        return $this->belongsTo(Municipio::class, 'munidepa_id', 'munidepa_id');
    }
    
}