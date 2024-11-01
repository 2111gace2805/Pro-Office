<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;
use DateTime;

class Invoice extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';
    protected $guarded = ['id'];
    
	public function invoice_items()
    {
        return $this->hasMany('App\InvoiceItem',"invoice_id")->orderBy('line');
    }

    public function taxes()
    {
        return $this->hasMany('App\InvoiceItemTax',"invoice_id");
    }

    public function client()
    {
        return $this->belongsTo('App\Contact',"client_id")->withDefault();
    }

    public function getDueDateAttribute($value)
    {
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }

    public function getInvoiceDateAttribute($value)
    {
		$date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }
    
    public function raw_invoice_date()
    {
        $date_format = get_date_format();
		return DateTime::createFromFormat($date_format, $this->invoice_date)->format('Y-m-d');
    }

    public function tipo_documento_receptor()
    {
        return $this->belongsTo('App\TipoDocumentoReceptor',"tdocrec_id");
    }

    public function plazo()
    {
        return $this->belongsTo('App\Plazo',"plazo_id");
    }
    
    public function statusMh()
    {
        return $this->belongsTo('App\InvoiceStatusMh',"iemh_id");
    }

    public function actividad_economica()
    {
        return $this->belongsTo('App\ActividadEconomica', 'actie_id');
    }

    public function condicion_operacion(){
        return $this->belongsTo('App\CondicionOperacion', 'conop_id');
    }
    
    public function tipo_documento(){
        return $this->belongsTo('App\TipoDocumento', 'tipodoc_id');
    }
    
    public function company(){
        return $this->belongsTo(Company::class, 'company_id')->with(['departamento', 'municipio', 'tipoEstablecimiento']);
    }

    public function incoterm(){
        return $this->belongsTo('App\Incoterm', 'id_incoterms');
    }

    public function seller_code()
    {
        return $this->belongsTo('App\User',"user_id")->withDefault();
    }

    public function seller_code2()
    {
        return $this->belongsTo('App\User',"second_user_id")->withDefault();
    }

    public function getFormattedSellerCode2Attribute()
    {

        if( $this->seller_code2 && !empty($this->seller_code2->seller_code) ){
            return '- ' . $this->seller_code2->seller_code;
        }
        else{
            return '';
        }
    }

    public function general_discount(){
        return $this->belongsTo(GeneralDiscount::class, 'general_discount_id');
    }
}