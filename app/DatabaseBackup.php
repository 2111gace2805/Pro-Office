<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class DatabaseBackup extends Model
{
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'database_backups';
	
	public function created_by(){
		return $this->belongsTo('App\User','user_id')->withDefault();
	}
	
	public function getCreatedAtAttribute($value)
    {
		$date_format = get_date_format();
		$time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }
}