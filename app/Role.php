<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class Role extends Model
{
	use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'staff_roles';
	
	protected $fillable = [
        'name',
        'description',
        'company_id',
    ];
	
	public function permissions(){
		return $this->hasMany('App\AccessControl','role_id');
	}
}