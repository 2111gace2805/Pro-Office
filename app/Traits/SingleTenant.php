<?php

namespace App\Traits;

trait SingleTenant {

    public static function bootSingleTenant() {

        if (auth()->check()) {
            static::saving(function ($model) {
                $model->company_id = company_id();
            });
        }   
    }

}