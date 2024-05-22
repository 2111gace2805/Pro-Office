<?php

namespace App\Traits;

use App\Log;

trait LogsActivityTrait
{

    public static function bootLogsActivityTrait() {
        static::created(function($model){
            $user = auth()->user(); // Obtenemos el usuario autenticado
            $table_name = $model->getTable();
            $action = 0; //1=create
            Log::createLog($user, $table_name,$action);
        });
        
        static::updated(function($model) {
            $user = auth()->user(); // Obtenemos el usuario autenticado
            $table_name = $model->getTable();
            
            $action = 1; // 1 => update

            Log::createLog($user,$table_name, $action);
        });

        static::deleted(function($model) {
            $user = auth()->user(); // Obtenemos el usuario autenticado
            $table_name = $model->getTable();
            $action = 2; // 2=> delete

            Log::createLog($user,$table_name, $action );
        });
    }
}
