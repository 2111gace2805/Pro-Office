<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class ChartOfAccount extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chart_of_accounts';
}