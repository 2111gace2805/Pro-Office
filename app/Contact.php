<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Contact extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts';
	
	public function group()
    {
        return $this->belongsTo('App\ContactGroup', 'group_id')->withDefault(['name' => '']);
    }
	
	public function user()
    {
        return $this->belongsTo('App\User')->withDefault();
    }

    public function tipo_persona()
    {
        return $this->belongsTo('App\TipoPersona', 'tpers_id')->withDefault();
    }
    
    public function municipio()
    {
        return $this->belongsTo($this, '_')->withDefault(function($municipio, $contact){
            $contact = $contact;
            return District::find($contact->munidepa_id)->municipio;
        });
        // return $this->district->municipio();
    }
    
    public function departamento()
    {
        return $this->belongsTo('App\Departamento', 'depa_id');
    }
    
    public function pais()
    {
        return $this->belongsTo('App\Pais', 'pais_id');
    }
    
    public function actividad_economica()
    {
        return $this->belongsTo('App\ActividadEconomica', 'actie_id');
    }
    
    public function plazo()
    {
        return $this->belongsTo('App\Plazo', 'plazo_id');
    }

    public function district(){
        return $this->belongsTo(District::class, 'munidepa_id');
    }
}