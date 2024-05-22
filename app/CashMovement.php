<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class CashMovement extends Model
{
    use LogsActivityTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cash_movements';
    protected $primaryKey = 'cashmov_id';

    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function cash(){
        return $this->belongsTo('App\Cash', 'cash_id');
    }
}