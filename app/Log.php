<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $table = 'logs';

    public static function createLog($user, $table_name, $action) {
        $log = new Log();
        $log->user_id = $user->id;
        $log->table_name = $table_name;;
        $log->action = $action;

        $log->save();
    }

    //para acceder a los  usuarios que realizaron la acción
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //para mostrar la accion 
    public function getActionDescriptionAttribute()
    {
        switch ($this->action) {
            case 0:
                return 'Insert';
                break;
            case 1:
                return 'Update';
                break;
            case 2:
                return 'Delete';
                break;
            default:
                return 'Unknown';
        }
    }
}
