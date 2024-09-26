<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Category extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    public $timestamps = false;
}
