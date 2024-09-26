<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivityTrait;


class Company extends Model
{
    use HasFactory;
    use LogsActivityTrait;

    public function tipo_establecimiento(){
        return $this->belongsTo('App\TipoEstablecimiento', 'tipoest_id');
    }

    public function cashs(){
        return $this->hasMany('App\Cash', 'cash_id');
    }

    public function transfersSent()
    {
        return $this->hasMany(Transfer::class, 'company_send');
    }

    // Relación inversa con Transfer (compañía que recibe)
    public function transfersReceived()
    {
        return $this->hasMany(Transfer::class, 'company_receive');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'depa_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'munidepa_id');
    }

    public function tipoEstablecimiento()
    {
        return $this->belongsTo(TipoEstablecimiento::class, 'tipoest_id');
    }
}
