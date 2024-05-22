<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivityTrait;


class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use LogsActivityTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
        'name', 'email', 'password', 'user_type', 'email_verified_at', 'status', 'profile_picture',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
	
	public function getCreatedAtAttribute($value)
    {
		$date_format = get_date_format();
		$time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }
	
	public function role(){
		return $this->belongsTo('App\Role','role_id')->withDefault();
	}

    public function client(){
		return $this->hasOne('App\Contact','user_id')->withDefault();
	}
}
