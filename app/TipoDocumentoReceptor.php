<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SingleTenant;
use App\Traits\LogsActivityTrait;


class TipoDocumentoReceptor extends Model
{
    use SingleTenant;
    use LogsActivityTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'tdocrec_id';
    protected $table = 'tipo_doc_ident_receptor';
    protected $keyType = 'string';
}