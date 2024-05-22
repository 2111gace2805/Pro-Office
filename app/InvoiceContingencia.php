<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivityTrait;

class InvoiceContingencia extends Model
{
    use LogsActivityTrait;


    protected $fillable = [
        'invoice_id',
        'type_dte',
        'codigo_generacion',
        'sello_recepcion',
        'json_contingencia',
        'response_mh',
        'estado',
        'created_at',
        'updated_at',
    ];
}
