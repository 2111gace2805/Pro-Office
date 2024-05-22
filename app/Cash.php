<?php

namespace App;

use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Cash extends Model
{
    use LogsActivityTrait;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cashs';
    protected $primaryKey = 'cash_id';
    protected $guarded = ['cash_id'];

    protected $fillable = [
        'cash_id',
        'cash_name',
        'cash_value',
        'cash_status',
        'company_id',
    ];

    public function cash_movements(){
        return $this->hasMany('App\CashMovement', 'cash_id');
    }

    public function company(){
        return $this->belongsTo('App\Company', 'company_id');
    }
}