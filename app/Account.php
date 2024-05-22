<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Account extends Model
{
    use SingleTenant;
    use LogsActivityTrait;

    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';


    public function openingDate(){
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($this->opening_date)->format("$date_format");
    }

}