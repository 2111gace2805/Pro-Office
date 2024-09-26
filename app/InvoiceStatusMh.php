<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class InvoiceStatusMh extends Model
{
    use HasFactory;
    use LogsActivityTrait;

    protected $table = 'invoice_status_mh';

    protected $guarded = ['id'];
    protected $primaryKey = 'iemh_id';
    protected $keyType = 'string';
}
