<?php

namespace App\Jobs;

use App\Tax;
use App\User;
use DateTime;
use App\Company;
use App\Invoice;
use App\Municipio;
use Carbon\Carbon;
use App\Transaction;
use App\PasarelaToken;
use App\InvoiceItemTax;
use Barryvdh\DomPDF\PDF;
use App\Mail\MailMailable;
use App\InvoiceContingencia;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\InvoiceController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class enviarContingencia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        
        $invoices = Invoice::where('contingencia', '=', 1)
        ->where('status_mh', '=', 2)
        ->get();
            
        foreach($invoices as $invoice){

            $id = $invoice->id;
            if( $id ){
                $user = User::find(9);
                Auth::Login($user);

                $requestData = [
                    'responsableEstablecimiento' => $user->name,
                    'tipoDocRespEstablecimiento' => '13',
                    'numDocRespEstablecimiento'  => $user->dui,
                    'fecha_inicio_contingencia'  =>  Carbon::now()->format('Y-m-d'),
                    'fecha_fin_contingencia'     =>  Carbon::now()->format('Y-m-d'),
                    'hora_inicio_contingencia'   =>  Carbon::createFromFormat('H:i', '07:00')->format('H:i'),
                    'hora_fin_contingencia'      =>  Carbon::createFromFormat('H:i', '08:00')->format('H:i'),
                    'tipoContingencia'           =>  1,
                    'motivo_contingencia'        =>  'No disponibilidad de sistema del MH',
                ];
                
                $request = new Request($requestData);


                $invoice    = Invoice::find($id);
                $company    = Company::find($invoice->company_id);
                $ambiente   = env('API_AMBIENTE_MH');
                $dteJson    = [];
        
                $identificacion = [
                    "version"           => 3,
                    "ambiente"          => $ambiente,
                    "codigoGeneracion"  => strtoupper(generateUUID()),
                    "fTransmision"      => Carbon::now()->format('Y-m-d'),
                    "hTransmision"      => Carbon::now()->format('H:i:s')
                ];
        
                $emisor = [
                    "nit"                   => str_replace('-', '', get_option('nit')),
                    "nombre"                => get_option('company_name'),
                    "nombreResponsable"     => $request->responsableEstablecimiento,
                    "tipoDocResponsable"    => $request->tipoDocRespEstablecimiento,
                    "numeroDocResponsable"  => $request->numDocRespEstablecimiento,
                    "tipoEstablecimiento"   => $company->tipoest_id,
                    "codEstableMH"          => $company->codigo_sucursal,
                    "telefono"              => str_replace(['-', '+'], '', $company->cellphone),
                    "correo"                => $company->email
                ];
        
                $detalleDTE = [
                    'noItem'             => 1, //SE DEJA 1 YA QUE SE UTILIZARA CONTINGENCIA DE 1 A 1 
                    'codigoGeneracion'   => $invoice->codigo_generacion,
                    'tipoDoc'            => $invoice->tipodoc_id,
                ];
        
                $motivo = [
                    'fInicio'            => $request->fecha_inicio_contingencia,
                    'fFin'               => $request->fecha_fin_contingencia,
                    'hInicio'            => Carbon::createFromFormat('H:i', $request->hora_inicio_contingencia)->format('H:i:s'),
                    'hFin'               => Carbon::createFromFormat('H:i', $request->hora_fin_contingencia)->format('H:i:s'),
                    'tipoContingencia'   => intval($request->tipoContingencia),
                    'motivoContingencia' => $request->motivo_contingencia,
                ];
        
                $dteJson = [
                    "identificacion"    => $identificacion,
                    "emisor"            => $emisor,
                    "detalleDTE"        => [$detalleDTE],
                    "motivo"            => $motivo,
                ];
        
                Log::info('Carga Útil de la Solicitud: ' . json_encode([
                    "nit"       => str_replace('-', '', get_option('nit')),
                    "ambiente"  => $ambiente,
                    "idEnvio"   => 1,
                    "version"   => 3,
                    'dteJson'   => $dteJson
                ]));
        
                try {
        
                    $invoice_cod = InvoiceContingencia::where('codigo_generacion', '=', $invoice->codigo_generacion)->first();
        
                    if (!$invoice_cod) {
        
                        Log::info('No existe código de generación para este DTE por lo que se envia a evento de contingencia');
        
                        $oldToken = PasarelaToken::where('status', '=', 1)->first();
                        if (!$oldToken) {
                            Log::info('No existe token antiguo, se solicita token');
                            $tokenPasarela = $this->generateTokenPasarela();
                            $tokenPasarela = json_decode(json_encode($tokenPasarela));
                            $token = PasarelaToken::create([
                                'token'         => $tokenPasarela->token,
                                'created_token' => $tokenPasarela->created,
                                'expired_token' => $tokenPasarela->expired,
                            ]);
                            Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                        } else {
                            $fechaActual = Carbon::now();
                            $fechaExpiracion = $oldToken->expired_token;
                            if ($fechaActual->gt($fechaExpiracion)) {
                                Log::info('Token expirado, se solicita nuevo token');
                                $tokenPasarela = $this->generateTokenPasarela();
                                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                                $oldToken->update([
                                    'status' => 0
                                ]);
                                $oldToken->delete();
                                PasarelaToken::create([
                                    'token'         => $tokenPasarela->token,
                                    'created_token' => $tokenPasarela->created,
                                    'expired_token' => $tokenPasarela->expired,
                                ]);
                                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                            } else {
                                Log::info('Token aun sin expirar');
                                $tokenPasarela = $oldToken;
                                Log::info('Token: Bearer ' . $tokenPasarela->token);
                            }
                        }
        
                        Log::info(json_encode($tokenPasarela));
        
                        Log::info('Datos de token enviado: ' . json_encode($tokenPasarela));
        
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $tokenPasarela->token,
                            'x-key-nit' => env('API_KEY_NIT')
                        ])
                            ->post(
                                env('API_PASARELA_CONTINGENCIA'),
                                [
                                    "nit"       => str_replace('-', '', get_option('nit')),
                                    "ambiente"  => $ambiente,
                                    "idEnvio"   => 1,
                                    "version"   => 2,
                                    'dteJson'   => $dteJson
                                ]
                            )
                            ->json();
        
                        Log::info('Respuesta API MH: ' . json_encode($response));
                        $response_mh = json_decode(json_encode($response));
        
                        if ($response_mh->estado === 'RECIBIDO') {
        
                            $contingencia = InvoiceContingencia::create([
                                'invoice_id'        => $id,
                                'type_dte'          => $invoice->tipodoc_id,
                                'codigo_generacion' => $invoice->codigo_generacion,
                                'sello_recepcion'   => $response_mh->selloRecibido,
                                'json_contingencia' => json_encode($dteJson),
                                'response_mh'       => json_encode($response),
                            ]);
        
                            $fecha_contingencia = $request->fecha_inicio_contingencia . ' ' . Carbon::createFromFormat('H:i', $request->hora_inicio_contingencia)->format('H:i:s');
        
                            $invoice->tconting_id           = $request->tipoContingencia;
                            $invoice->motivo_contingencia   = $request->motivo_contingencia;
                            $invoice->invoice_date          = $request->fecha_inicio_contingencia;
                            $invoice->created_at            = $fecha_contingencia;
                            $invoice->save();
        
                            $reenvio_dte = $this->sendInvoiceToHacienda($id);
                            $reenvio_mh = json_decode(json_encode($reenvio_dte));
        
        
                            if (!property_exists($reenvio_mh, 'estado')) {
        
                                Log::info('Error en respuesta de pasarela, verificar!');
                            }
        
                            $invoice->status_mh         = ($reenvio_mh->estado === 'RECHAZADO') ? 0 : 1;
        
        
                            if ($reenvio_mh->estado === 'RECHAZADO') {
        
                                Log::info('Error al reenviar DTE en contingencia: ' . json_encode($response));
        
                            } else if ($reenvio_mh->estado === 'PROCESADO') {
        
                                $invoice->response_mh       = json_encode($reenvio_dte);
                                $invoice->sello_recepcion   = $reenvio_mh->selloRecibido;
                                $invoice->json_dte          = json_encode($reenvio_mh->json);
                                $invoice->save();
        
                                $contingencia->estado = 1;
                                $contingencia->save();
        
                                $this->sendEmailFactura($invoice->id);
        
                                Log::info('DTE CON ID: ' . $invoice->id . ' Procesado luego de contingencia y enviado por correo a:' . $invoice->correo);
    
                            }
                        }
                        if ($response_mh->estado === 'RECHAZADO') {
        
                            Log::info('Error al reenviar DTE en contingencia: ' . $response_mh->mensaje);
        
                        }

                        return $response;
                    }
                    else {
        
                        Log::info('Existe codigo de generación registrado de contingencia para DTE con ID: ' . $invoice->id);
        
                        $fecha_contingencia = $request->fecha_inicio_contingencia . ' ' . Carbon::createFromFormat('H:i', $request->hora_inicio_contingencia)->format('H:i:s');
        
                        $invoice->tconting_id           = $request->tipoContingencia;
                        $invoice->motivo_contingencia   = $request->motivo_contingencia;
                        $invoice->invoice_date          = $request->fecha_inicio_contingencia;
                        $invoice->created_at            = $fecha_contingencia;
                        $invoice->save();
        
                        $reenvio_dte = $this->sendInvoiceToHacienda($id);
                        $reenvio_mh = json_decode(json_encode($reenvio_dte));
        
        
                        if (!property_exists($reenvio_mh, 'estado')) {
        
                            Log::info('Error en respuesta de pasarela, verificar!');
    
                        }
        
                        $invoice->status_mh = ($reenvio_mh->estado === 'RECHAZADO') ? 0 : 1;
        
                        if ($reenvio_mh->estado === 'RECHAZADO') {
        
                            Log::info('Error al reenviar DTE en contingencia: ' . json_encode($reenvio_dte));
        
                        } else if ($reenvio_mh->estado === 'PROCESADO') {
        
                            $invoice->response_mh       = json_encode($reenvio_dte);
                            $invoice->sello_recepcion   = $reenvio_mh->selloRecibido;
                            $invoice->json_dte          = json_encode($reenvio_mh->json);
                            $invoice->save();
        
        
                            $invoice_cod->estado = 1;
                            $invoice_cod->save();
        
                            $this->sendEmailFactura($invoice->id);
        
                            Log::info('DTE CON ID: ' . $invoice->id . ' Procesado luego de contingencia y enviado por correo a:' . $invoice->correo);
                        }

                        return $reenvio_dte;
                    }
                } catch (\Exception $e) {
        
                    Log::error('Error en la solicitud HTTP: ' . $e->getMessage());
        
                }

                Auth::Logout();
            }
        }
    }

    protected function generateTokenPasarela()
    {

        $user       = env('PASARELA_API_USER');
        $password   = env('PASARELA_API_PWD');
        $url        = env('URL_API_LOGIN');

        try {
            $token = Http::post(
                $url,
                [
                    'name'      => $user,
                    'password'  => $password,
                ]
            )
                ->json();

            return $token;
        } catch (\Throwable $th) {
            Log::error('ERROR EN LOGIN API PASARELA: ' . $th->getMessage());
            return response()->json(['Error al iniciar sesion en API pasarela'], 500);
        }
    }

    protected function sendInvoiceToHacienda($invoice_id)
    {
        // $invoice = Invoice::find($invoice_id);
        $invoice = Invoice::with(['client', 'client.district', 'client.district.municipio'])->find($invoice_id);
        $versionJson = $invoice->tipo_documento->version_json;
        $ambiente = env('API_AMBIENTE_MH');
        // $dteJson = self::getDteJsonCCF($invoice, $versionJson, $ambiente);
        $dteJson = '';
        switch ($invoice->tipodoc_id) {
            case '01':
                $dteJson = InvoiceController::getDteJsonFE($invoice, $versionJson, $ambiente);
                break;
            case '03':
                $dteJson = InvoiceController::getDteJsonCCF($invoice, $versionJson, $ambiente);
                break;
            case '04':
                $dteJson = InvoiceController::getDteJsonNotaRemision($invoice, $versionJson, $ambiente);
                break;
            case '05':
                $dteJson = InvoiceController::getDteJsonNotaDebitoCredito($invoice->tipodoc_id, $invoice, $versionJson, $ambiente);
                break;
            case '06':
                $dteJson = InvoiceController::getDteJsonNotaDebitoCredito($invoice->tipodoc_id, $invoice, $versionJson, $ambiente);
                break;
            case '11':
                $dteJson = InvoiceController::getDteJsonFEX($invoice, $versionJson, $ambiente);
                break;
            case '14':
                $dteJson = InvoiceController::getDteJsonSujetoExcluido($invoice, $versionJson, $ambiente);
                break;
            default:
                break;
        }


        Log::info('Carga Útil de la Solicitud: ' . json_encode([
            "nit" => str_replace('-', '', get_option('nit')),
            "ambiente" => $ambiente,
            "idEnvio" => 1,
            "version" => intval($versionJson),
            "tipoDte" => $invoice->tipodoc_id,
            'dteJson' => $dteJson
        ]));

        try {


            $oldToken = PasarelaToken::where('status', '=', 1)->first();
            if (!$oldToken) {
                Log::info('No existe token antiguo, se solicita token');
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                $token = PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            } else {
                $fechaActual = Carbon::now();
                $fechaExpiracion = $oldToken->expired_token;
                if ($fechaActual->gt($fechaExpiracion)) {
                    Log::info('Token expirado, se solicita nuevo token');
                    $tokenPasarela = $this->generateTokenPasarela();
                    $tokenPasarela = json_decode(json_encode($tokenPasarela));
                    $oldToken->update([
                        'status' => 0
                    ]);
                    $oldToken->delete();
                    PasarelaToken::create([
                        'token'         => $tokenPasarela->token,
                        'created_token' => $tokenPasarela->created,
                        'expired_token' => $tokenPasarela->expired,
                    ]);
                    Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
                } else {
                    Log::info('Token aun sin expirar');
                    $tokenPasarela = $oldToken;
                    Log::info('Token: Bearer ' . $tokenPasarela->token);
                }
            }

            if (!property_exists($tokenPasarela, 'token')) {
                Log::info('No existe propiedad token, por lo que se solicita nuevo token');
                PasarelaToken::where('status', '=', 1)->update(['status' => 0]);
                $tokenPasarela = $this->generateTokenPasarela();
                $tokenPasarela = json_decode(json_encode($tokenPasarela));
                PasarelaToken::create([
                    'token'         => $tokenPasarela->token,
                    'created_token' => $tokenPasarela->created,
                    'expired_token' => $tokenPasarela->expired,
                ]);
                Log::info('Se genero el token: Bearer ' . $tokenPasarela->token);
            }

            Log::info(json_encode($tokenPasarela));

            Log::info('Datos de token enviado: ' . json_encode($tokenPasarela));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenPasarela->token,
                'x-key-nit' => env('API_KEY_NIT')
            ])
                ->post(
                    env('API_PASARELA_FE'),
                    [
                        "nit" => str_replace('-', '', get_option('nit')),
                        "ambiente" => $ambiente,
                        "idEnvio" => 1,
                        "version" => $versionJson,
                        "tipoDte" => $invoice->tipodoc_id,
                        'dteJson' => $dteJson
                    ]
                )
                ->json();

            Log::info('Respuesta API MH: ' . json_encode($response));

            return $response;
        } catch (\Exception $e) {

            Log::error('Error en la solicitud HTTP: ' . $e->getMessage());

            return response()->json(['error' => 'Hubo un problema en la solicitud HTTP'], 500);
        }
    }

    private static function getDteJsonCCF($invoice, $versionJson, $ambiente)
    {
        // dd($invoice);
        $company = Company::find($invoice->company_id);
        $cash = get_cash();
        $details = [];
        $documentosRelacionados = null;

        $noSujetoSum = 0.0;
        $exentoSum = 0.0;
        $gravadoSum = 0.0;
        $descNoSujetoSum = 0.0;
        $descExentoSum = 0.0;
        $descGravadoSum = 0.0;
        $descGlobalExento = 0.0;
        $descGlobalNoSujeto = 0.0;
        $descGlobalGravado = 0.0;
        $ivaPercibido = 0.0;
        $rentaRetenida = 0.0;

        // caso CCF
        $tributos = InvoiceItemTax::select('tributos.trib_id as codigo', 'tributos.trib_nombre as descripcion')
            ->join('taxs', 'invoice_item_taxes.tax_id', '=', 'taxs.id')
            ->join('tributos', 'taxs.trib_id', '=', 'tributos.trib_id')
            ->where('invoice_id', $invoice->id)
            ->groupBy('tributos.trib_id')
            ->selectRaw('SUM(invoice_item_taxes.amount) as valor')
            ->get();

        $resultadosFormateados = [];
        foreach ($tributos as $tributo) {
            $resultadosFormateados[] = [
                'codigo' => $tributo->codigo,
                'descripcion' => $tributo->descripcion,
                'valor' => floatval(number_format($tributo->valor, 2, '.', '')),
            ];
        }

        // Separar los componentes de la fecha
        $fechaComponents = explode('/', $invoice->invoice_date);

        // Construir la fecha en el formato 'yyyy-mm-dd'
        $fechaFormateada = $fechaComponents[2] . '-' . $fechaComponents[1] . '-' . $fechaComponents[0];

        // Crear un objeto DateTime con la fecha formateada
        $fechaDateTime = new DateTime($fechaFormateada);

        foreach ($invoice->invoice_items as $key => $value) {
            $exento = 0.0;
            $noSujeto = 0.0;
            $gravado = 0.0;
            if ($invoice->exento_iva == 'si') {
                $exento = $value->sub_total;
                $exentoSum += $value->sub_total;
                $descExentoSum += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto = $value->sub_total;
                $noSujetoSum += $value->sub_total;
                $descNoSujetoSum += $value->discount;
            } else {
                $gravado = $value->sub_total;
                $gravadoSum += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            $totalTributos = 0;

            // Calcular el total de los valores de tributos
            foreach ($tributos as $tributo) {
                $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
            }

            // Calcular el subtotal
            $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

            $tributos_ = null;

            if( $invoice->exento_iva == 'no' ){
                $tributos_ = Tax::whereIn('id', $value->taxes->pluck('tax_id')->toArray())
                ->pluck('trib_id')->toArray();
            }

            array_push($details, [
                "numItem" => $value->line,
                "tipoItem" => intval($value->item->tipoitem_id),
                "numeroDocumento" => ( $value->cod_dte_rel != '') ? $value->cod_dte_rel : null,
                "codigo" => $value->item->product->product_code,
                "codTributo" => null,
                "descripcion" => $value->description,
                "cantidad" => intval($value->quantity),
                "uniMedida" => intval($value->item->product->unim_id),
                "precioUni" => intval($value->unit_cost),
                "montoDescu" => intval($value->discount),
                "ventaNoSuj" => floatval(number_format($noSujeto, 2, '.', '')),
                "ventaExenta" => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada" => floatval(number_format($gravado, 2, '.', '')),
                "tributos" => $tributos_,
                "psv" => 0.0,
                "noGravado" => 0.0
            ]);

            if( $value->cod_dte_rel != '' ){

                $documentosRelacionados = [];
                $documentosRelacionados = collect($documentosRelacionados);

                $documentos = [
                    'tipoDocumento'      => $value->type_dte_rel,
                    'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
                    'numeroDocumento'    => $value->cod_dte_rel,
                    'fechaEmision'       => $value->date_dte_rel,
                ];

                if( !$documentosRelacionados->contains('numeroDocumento', $value->cod_dte_rel) ){
                    $nuevoDocumento = [
                        'tipoDocumento'      => $value->type_dte_rel,
                        'tipoGeneracion'     => 2, // 1= físico, 2= electrónico
                        'numeroDocumento'    => $value->cod_dte_rel,
                        'fechaEmision'       => $value->date_dte_rel,
                    ];
                
                    $documentosRelacionados->push($nuevoDocumento);
                }
            }
        }
        $dteJson = [
            "identificacion" => [
                "version" => intval($versionJson),
                "ambiente" => $ambiente,
                "tipoDte" => $invoice->tipodoc_id,
                "numeroControl" => $invoice->numero_control,
                "codigoGeneracion" => $invoice->codigo_generacion,
                "tipoModelo" => intval($invoice->modfact_id),
                "tipoOperacion" => intval($invoice->tipotrans_id),
                "tipoContingencia" => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
                "motivoContin" => $invoice->motivo_contingencia,
                "fecEmi" => $fechaDateTime->format('Y-m-d'),
                "horEmi" => (new DateTime($invoice->created_at))->format('H:i:s'),
                "tipoMoneda" => "USD"
            ],
            "documentoRelacionado" => $documentosRelacionados,
            "emisor" => [
                "nit" => str_replace('-', '', get_option('nit')),
                "nrc" => str_replace('-', '', get_option('nrc')),
                "nombre" => get_option('name_company'),
                "codActividad" => get_option('cod_actividad'),
                "descActividad" => get_option('desc_actividad'),
                "nombreComercial" => get_option('tradename'),
                "tipoEstablecimiento" => $company->tipoest_id,
                "direccion" => [
                    "departamento" => $company->depa_id,
                    "municipio" => Municipio::find($company->munidepa_id)->muni_id,
                    "complemento" => $company->address
                ],
                // "telefono"=> $company->cellphone,
                "telefono" => str_replace(['-', '+'], '', $company->cellphone),
                "correo" => $company->email,
                "codEstableMH" => $company->codigo_sucursal,
                "codEstable" => $company->codigo_sucursal,
                "codPuntoVentaMH" => $cash->cash_code,
                "codPuntoVenta" => $cash->cash_code
            ],
            "receptor" => [
                "nit" => str_replace('-', '', $invoice->client->nit),
                "nrc" => str_replace('-', '',  $invoice->client->nrc),
                "nombre" => $invoice->name_invoice,
                "codActividad" => $invoice->client->actie_id,
                "descActividad" => $invoice->client->descActividad,
                "nombreComercial" => $invoice->client->tradename,
                "direccion" => [
                    "departamento" => $invoice->client->depa_id,
                    "municipio" => Municipio::find($invoice->client->munidepa_id)->muni_id,
                    "complemento" => $invoice->complemento
                ],
                // "telefono"=> $invoice->telefono,
                "telefono" => str_replace(['-', '+'], '', $invoice->telefono),
                "correo" => $invoice->correo
            ],
            "otrosDocumentos" => null,
            "ventaTercero" => null,
            "cuerpoDocumento" => $details,
            "resumen" => [
                "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
                "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
                "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
                "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
                "descuNoSuj"            => floatval(number_format(0.0, 2, '.', '')), // este campo es diferente al descuento por item no sujeto, aca debe ir un valor que en el 
                // formulario de creacion de invoice diga "Descuento global a ventas no sujetas"
                "descuExenta"           => floatval(number_format(0.0, 2, '.', '')),
                "descuGravada"          => floatval(number_format(0.0, 2, '.', '')),
                "porcentajeDescuento"   => floatval(number_format(0.0, 2, '.', '')),
                "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')), // uso informativo descuentos por item + descuentos globales por tipo de venta ej $descGlobalExento+$descGlobalNoSujeto
                "tributos"              => $resultadosFormateados,


                // [ // colocar aca los mismo tributos de los items
                // {
                //     "codigo"=> "20",
                //     "descripcion"=> "Impuesto al Valor Agregado 13%",
                //     "valor"=> 0.59
                // }
                //],
                "subTotal"              => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado), 2, '.', '')), // menos $descGlobalNoSujeto, etc,
                "ivaPerci1"             => floatval(number_format($invoice->iva_percibido, 2, '.', '')),
                "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
                "reteRenta"             => floatval(number_format(0, 2, '.', '')),
                "montoTotalOperacion"   => floatval(number_format($subTotal + $totalTributos, 2, '.', '')),
                "totalNoGravado"        => floatval(number_format(0.0, 2, '.', '')),
                "totalPagar"            => floatval(number_format($subTotal + $totalTributos - $invoice->iva_retenido, 2, '.', '')),
                "totalLetras"           => _lang('It is') . ' ' . dollarToText($invoice->grand_total) . ' USD',
                "saldoFavor"            => floatval(number_format(0.0, 2, '.', '')),
                "condicionOperacion"    => intval($invoice->conop_id),
                // "pagos"=> $invoice->conop_id == 1 || $invoice->conop_id == 3?['codigo'=>intval($invoice->forp_id), 'montoPago'=>intval($invoice->grand_total)] : null,
                "pagos"                 => null,
                "numPagoElectronico"    => null
            ],
            "extension" => null,
            "apendice" =>
            [
                [
                    "campo" => "sucursal",
                    "etiqueta" => "Sucursal",
                    "valor" => $company->company_name
                ],
                [
                    "campo" => "condicion_operacion",
                    "etiqueta" => "Condicion de la operacion",
                    // "valor"=> $invoice->condicion_operacion->conop_nombre
                    "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,

                ],
                // [
                //     "campo"=> "vendedor",
                //     "etiqueta"=> "Vendedor",
                //     "valor"=> "0000S60"
                // ],
                // [
                //     "campo"=> "codigo_cxc",
                //     "etiqueta"=> "Codigo CXC",
                //     "valor"=> "0"
                // ]
            ]
        ];
        // log::info(json_encode($dteJson));
        return $dteJson;
    } // PROBAR CCF

    // FE -> Factura electronica(consumidor final)
    private static function getDteJsonFE($invoice, $versionJson, $ambiente)
    {
        $company = Company::find($invoice->company_id);
        $cash = get_cash();
        $details = [];
        $documentosRelacionados = null;

        $noSujetoSum = 0;
        $exentoSum = 0;
        $gravadoSum = 0;
        $descNoSujetoSum = 0;
        $descExentoSum = 0;
        $descGravadoSum = 0;
        $descGlobalExento = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado = 0;
        $ivaPercibido = 0;
        $rentaRetenida = 0;

        // caso FE
        $tributos = DB::table('invoice_item_taxes as iit')
            ->join('taxs as t', 'iit.tax_id', '=', 't.id')
            ->join('tributos as tribs', 't.trib_id', '=', 'tribs.trib_id')
            ->where('invoice_id', $invoice->id)
            ->select('tribs.trib_id as codigo', 'tribs.trib_nombre as descripcion', DB::raw('SUM(iit.amount) as valor'))
            ->groupBy('tribs.trib_id')
            ->get();

        $resultadosFormateados = [];
        foreach ($tributos as $tributo) {
            $resultadosFormateados[] = [
                'codigo' => $tributo->codigo,
                'descripcion' => $tributo->descripcion,
                'valor' => floatval(number_format($tributo->valor, 2, '.', '')),
            ];
        }

        foreach ($invoice->invoice_items as $key => $value) {
            $exento = 0;
            $noSujeto = 0;
            $gravado = 0;
            if ($invoice->exento_iva == 'si') {
                $exento = $value->sub_total;
                $exentoSum += $value->sub_total;
                $descExentoSum += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto = $value->sub_total;
                $noSujetoSum += $value->sub_total;
                $descNoSujetoSum += $value->discount;
            } else {
                $gravado = $value->sub_total;
                $gravadoSum += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            $taxIVA = Tax::where('trib_id', '20')->first();

            // Separar los componentes de la fecha
            $fechaComponents = explode('/', $invoice->invoice_date);

            // Construir la fecha en el formato 'yyyy-mm-dd'
            $fechaFormateada = $fechaComponents[2] . '-' . $fechaComponents[1] . '-' . $fechaComponents[0];

            // Crear un objeto DateTime con la fecha formateada
            $fechaDateTime = new DateTime($fechaFormateada);

            $CalculoIvaIem = (($gravado) / ($taxIVA->rate / 100 + 1)) * ($taxIVA->rate / 100);

            $totalTributos = 0;

            // Calcular el total de los valores de tributos
            // foreach ($tributos as $tributo) {
            //     $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
            // }

            // Calcular el subtotal
            $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

            array_push($details, [
                "numItem" => $value->line,
                "tipoItem" => intval($value->item->tipoitem_id),
                "numeroDocumento" => ( $value->cod_dte_rel != '') ? $value->cod_dte_rel : null,
                "codigo" => $value->item->product->product_code,
                "codTributo" => null,
                "descripcion" => $value->description,
                "cantidad" => intval($value->quantity),
                "uniMedida" => intval($value->item->product->unim_id),
                "precioUni" => floatval($value->unit_cost),
                "montoDescu" => intval($value->discount),
                "ventaNoSuj" => intval($noSujeto),
                "ventaExenta" => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada" => floatval($gravado),
                "tributos" => /* Tax::whereIn('id', $value->taxes->pluck('tax_id')->toArray())
                    ->pluck('trib_id')->toArray() */ null,
                "psv" => 0.0,
                "noGravado" => 0.0,
                "ivaItem" => round($CalculoIvaIem, 2)
            ]);

            if( $value->cod_dte_rel != '' ){

                $documentosRelacionados = [];
                $documentosRelacionados = collect($documentosRelacionados);

                $documentos = [
                    'tipoDocumento'      => $value->type_dte_rel,
                    'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
                    'numeroDocumento'    => $value->cod_dte_rel,
                    'fechaEmision'       => $value->date_dte_rel,
                ];

                if( !$documentosRelacionados->contains('numeroDocumento', $value->cod_dte_rel) ){
                    $nuevoDocumento = [
                        'tipoDocumento'      => $value->type_dte_rel,
                        'tipoGeneracion'     => 2, // 1= físico, 2= electrónico
                        'numeroDocumento'    => $value->cod_dte_rel,
                        'fechaEmision'       => $value->date_dte_rel,
                    ];
                
                    $documentosRelacionados->push($nuevoDocumento);
                }
            }
        }
        $totalIva = 0.0;
        foreach ($details as $detail) {
            $totalIva += $detail['ivaItem'];
        }

        //Número de documento de receptor
        $documento = null;
        if( $invoice->num_documento != '' ){
            $documento = $invoice->num_documento;
            $documento = str_replace('-', '', $documento);
    
            if ($invoice->tdocrec_id == 13) {
                $parte1 = substr($documento, 0, 8);
                $parte2 = substr($documento, 8);
                $documento = $parte1 . '-' . $parte2;
            }
        }

        $direccion = null;
        if( $invoice->client->depa_id != '' ){
            $direccion = [
                "departamento"  => $invoice->client->depa_id,
                "municipio"     => Municipio::find($invoice->client->munidepa_id)->muni_id,
                "complemento"   => $invoice->complemento
            ];
        }

        $dteJson = [
            "identificacion" => [
                "version" => intval($versionJson),
                "ambiente" => $ambiente,
                "tipoDte" => $invoice->tipodoc_id,
                "numeroControl" => $invoice->numero_control,
                "codigoGeneracion" => $invoice->codigo_generacion,
                "tipoModelo" => intval($invoice->modfact_id),
                "tipoOperacion" => intval($invoice->tipotrans_id),
                "tipoContingencia" => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
                "motivoContin" => $invoice->motivo_contingencia,
                "fecEmi" => $fechaDateTime->format('Y-m-d'),
                "horEmi" => (new DateTime($invoice->created_at))->format('H:i:s'),
                "tipoMoneda" => "USD"
            ],
            "documentoRelacionado" => $documentosRelacionados,
            "emisor" => [
                "nit" => str_replace('-', '', get_option('nit')),
                "nrc" => str_replace('-', '', get_option('nrc')),
                "nombre" => get_option('company_name'),
                "codActividad" => get_option('cod_actividad'),
                "descActividad" => get_option('desc_actividad'),
                "nombreComercial" => get_option('tradename'),
                "tipoEstablecimiento" => $company->tipoest_id,
                "direccion" => [
                    "departamento" => $company->depa_id,
                    "municipio" => Municipio::find($company->munidepa_id)->muni_id,
                    "complemento" => $company->address
                ],
                "telefono" => $company->cellphone,
                "correo" => $company->email,
                "codEstableMH"      => $company->codigo_sucursal,
                "codEstable"        => $company->codigo_sucursal,
                "codPuntoVentaMH"   => $cash->cash_code,
                "codPuntoVenta"     => $cash->cash_code,
            ],
            "receptor" => [
                "tipoDocumento" => $invoice->tdocrec_id ?? null,
                // DUI debe ir con guion
                "numDocumento"  => $documento,
                "nrc"           => null,
                "nombre"        => $invoice->name_invoice ?? null,
                "codActividad"  => null,
                "descActividad" => $invoice->desc_actividad ?? null,
                "direccion"     => $direccion,
                "telefono"      => str_replace(['-', '+'], '', $invoice->telefono) ?? null,
                "correo"        => $invoice->correo ?? null
            ],
            "otrosDocumentos" => null,
            "ventaTercero" => null,
            "cuerpoDocumento" => $details,
            "resumen" => [
                "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
                "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
                "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
                "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
                "descuNoSuj"            => 0.0, // este campo es diferente al descuento por item no sujeto, aca debe ir un valor que en el 
                // formulario de creacion de invoice diga "Descuento global a ventas no sujetas"
                "descuExenta"           => 0.0,
                "descuGravada"          => 0.0,
                "porcentajeDescuento"   => 0.0,
                "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')), // uso informativo descuentos por item + descuentos globales por tipo de venta ej $descGlobalExento+$descGlobalNoSujeto
                // "tributos"=> $tributos,
                "tributos"              => null,
                // [ // colocar aca los mismo tributos de los items
                // {
                //     "codigo"=> "20",
                //     "descripcion"=> "Impuesto al Valor Agregado 13%",
                //     "valor"=> 0.59
                // }
                //],
                "subTotal"              => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado), 2, '.', '')), // menos $descGlobalNoSujeto, etc,
                // "ivaPerci1"=> $invoice->iva_percibido,
                "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
                "reteRenta"             => floatval(number_format($invoice->isr_retenido, 2, '.', '')),
                // "montoTotalOperacion" => floatval(number_format($invoice->grand_total, 2, '.', '')),
                "montoTotalOperacion"   => floatval(number_format($subTotal, 2, '.', '')),
                "totalNoGravado"        => floatval(number_format(0.0, 2, '.', '')),
                "totalPagar"            => floatval(number_format($subTotal - floatval($invoice->iva_retenido), 2, '.', '')),
                "totalLetras"           => _lang('It is') . ' ' . dollarToText($invoice->grand_total) . ' USD',
                "saldoFavor"            => 0.0,
                "condicionOperacion"    =>  intval($invoice->conop_id),
                // "pagos"=> $invoice->conop_id == 1 || $invoice->conop_id == 3?['codigo'=>$invoice->forp_id, 'montoPago'=>$invoice->grand_total] : null,
                // "numPagoElectronico"=> null,
                "pagos"                 => null,
                "totalIva"              => floatval(number_format($totalIva, 2, '.', '')),
                "numPagoElectronico"    => null
            ],
            "extension" => null,
            "apendice" =>
            [
                [
                    "campo" => "sucursal",
                    "etiqueta" => "Sucursal",
                    "valor" => $company->company_name
                ],
                [
                    "campo" => "condicion_operacion",
                    "etiqueta" => "Condicion de la operacion",
                    "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
                ],
                // [
                //     "campo"=> "vendedor",
                //     "etiqueta"=> "Vendedor",
                //     "valor"=> "0000S60"
                // ],
                // [
                //     "campo"=> "codigo_cxc",
                //     "etiqueta"=> "Codigo CXC",
                //     "valor"=> "0"
                // ]
            ]
        ];
        // log::info('DTE FE', json_encode($dteJson));
        return $dteJson;
    } // CONTINUAR EN RESUMEN

    private static function getDteJsonNotaDebitoCredito($tipoDte, $invoice, $versionJson, $ambiente)
    {

        $company = Company::find($invoice->company_id);
        $details = [];
        $documentosRelacionados = [];
        $documentosRelacionados = collect($documentosRelacionados);

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $totalTributos      = 0;

        $tributos = DB::select("SELECT tribs.trib_id as codigo, tribs.trib_nombre as descripcion, SUM(iit.amount) as valor
                                FROM invoice_item_taxes iit
                                join taxs t on iit.tax_id = t.id 
                                join tributos tribs on t.trib_id = tribs.trib_id 
                                where invoice_id = $invoice->id 
                                GROUP BY tribs.trib_id");

        $arrTributos = [];

        foreach ($tributos as $tributo) {

            $arrTributos[] = [
                'codigo'        => $tributo->codigo,
                'descripcion'   => $tributo->descripcion,
                'valor'         => floatval(number_format($tributo->valor, 2, '.', '')),
            ];

            $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $tipoDte,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContin"      => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nrc"                   => str_replace('-', '', get_option('nrc')),
            "nombre"                => get_option('company_name'),
            "codActividad"          => get_option('cod_actividad'),
            "descActividad"         => get_option('desc_actividad'),
            "nombreComercial"       => get_option('tradename'),
            "tipoEstablecimiento"   => $company->tipoest_id,
            "direccion" => [
                "departamento"  => $company->depa_id,
                "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                "complemento"   => $company->address
            ],
            "telefono"          => $company->cellphone,
            "correo"            => $company->email,
        ];

        $receptor = [
            "nit"               => str_replace('-', '', $invoice->client->nit),
            "nrc"               => str_replace('-', '', $invoice->client->nrc),
            "nombre"            => $invoice->name_invoice,
            "codActividad"      => $invoice->client->actie_id,
            "descActividad"     => $invoice->client->descActividad,
            "nombreComercial"   => $invoice->client->tradename,
            "direccion" => [
                "departamento"  => $invoice->client->depa_id,
                "municipio"     => Municipio::find($invoice->client->munidepa_id)->muni_id,
                "complemento"   => $invoice->client->address
            ],
            "telefono"  => $invoice->client->contact_phone,
            "correo"    => $invoice->client->contact_email
        ];

        foreach ($invoice->invoice_items as $key => $value) {

            $exento         = 0;
            $noSujeto       = 0;
            $gravado        = 0;
            $tributos_item  = [];

            if ($invoice->exento_iva == 'si') {
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            } else {
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            //SE AGREGA TRIBUTO DE IVA
            array_push($tributos_item, "20");

            $data = [
                "numItem"           => $value->line,
                "tipoItem"          => intval($value->item->tipoitem_id),
                "numeroDocumento"   => $value->cod_dte_rel,
                "codigo"            => $value->item->product->product_code,
                "codTributo"        => null,
                "descripcion"       => $value->description,
                "cantidad"          => intval($value->quantity),
                "uniMedida"         => intval($value->item->product->unim_id),
                "precioUni"         => floatval(number_format($value->unit_cost, 2, '.', '')),
                "montoDescu"        => intval($value->discount),
                "ventaNoSuj"        => $noSujeto,
                "ventaExenta"       => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada"      => floatval(number_format($gravado, 2, '.', '')),
                "tributos"          => ( $invoice->exento_iva == 'no' ) ? $tributos_item : null,
            ];

            $documentos = [
                'tipoDocumento'      => $value->type_dte_rel,
                'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
                'numeroDocumento'    => $value->cod_dte_rel,
                'fechaEmision'       => $value->date_dte_rel,
            ];

            array_push($details, $data);
            // array_push($documentosRelacionados, $documentos);

            if( !$documentosRelacionados->contains('numeroDocumento', $value->cod_dte_rel) ){
                $nuevoDocumento = [
                    'tipoDocumento'      => $value->type_dte_rel,
                    'tipoGeneracion'     => 2, // 1= físico, 2= electrónico
                    'numeroDocumento'    => $value->cod_dte_rel,
                    'fechaEmision'       => $value->date_dte_rel,
                ];
            
                $documentosRelacionados->push($nuevoDocumento);
            }
        }

        $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

        $montoTotal = $invoice->grand_total;

        $resumen = [
            "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
            "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
            "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
            "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
            "descuNoSuj"            => 0.0,
            "descuExenta"           => 0.0,
            "descuGravada"          => 0.0,
            "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
            "tributos"              => $arrTributos,
            "subTotal"              => floatval(number_format($subTotal, 2, '.', '')),
            "ivaPerci1"             => 0,
            "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
            "reteRenta"             => floatval(number_format($invoice->isr_retenido, 2, '.', '')),
            "montoTotalOperacion"   => floatval(number_format($montoTotal, 2, '.', '')),
            "totalLetras"           => _lang('It is') . ' ' . dollarToText($montoTotal) . ' USD',
            "condicionOperacion"    =>  intval($invoice->conop_id),
        ];

        if ($tipoDte == 06) {
            $resumen['numPagoElectronico'] = null;
        }

        $extension = [
            'nombEntrega'     => get_option('company_name'),
            'docuEntrega'     => str_replace('-', '', get_option('nit')),
            'nombRecibe'      => $invoice->name_invoice,
            'docuRecibe'      => str_replace('-', '', $invoice->client->nit),
            'observaciones'   => $invoice->note
        ];

        $apendice = [
            [
                "campo" => "sucursal",
                "etiqueta" => "Sucursal",
                "valor" => $company->company_name
            ],
            [
                "campo" => "condicion_operacion",
                "etiqueta" => "Condicion de la operacion",
                "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
            ],
        ];

        $dteJson = [
            "identificacion"        => $identificacion,
            "documentoRelacionado"  => $documentosRelacionados,
            "emisor"                => $emisor,
            "receptor"              => $receptor,
            "ventaTercero"          => null,
            "cuerpoDocumento"       => $details,
            "resumen"               => $resumen,
            "extension"             => $extension,
            "apendice"              => $apendice
        ];

        return $dteJson;
    }

    private static function getDteJsonFEX($invoice, $versionJson, $ambiente)
    {
        $company = Company::find($invoice->company_id);
        $cash = get_cash();
        $details = [];

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $ivaPercibido       = 0;
        $rentaRetenida      = 0;
        $descuento          = 0;

        // Caso FEXE (Factura de exportación electronica)
        $tributos = DB::table('invoice_item_taxes as iit')
            ->join('taxs as t', 'iit.tax_id', '=', 't.id')
            ->join('tributos as tribs', 't.trib_id', '=', 'tribs.trib_id')
            ->join('invoice_items as ii', 'iit.invoice_id', '=', 'ii.invoice_id')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->join('recinto_fiscal as rf', 'inv.refisc_id', '=', 'rf.refisc_id')
            ->join('regimen as reg', 'inv.regi_id', '=', 'reg.regi_id')
            ->where('iit.invoice_id', '=', $invoice->id)
            ->groupBy('tribs.trib_id')
            ->select('tribs.trib_id as codigo', 'tribs.trib_nombre as descripcion', DB::raw('SUM(iit.amount) as valor'))
            ->get();


        foreach( $invoice->invoice_items as $key => $value ){

            $exento     = 0;
            $noSujeto   = 0;
            $gravado    = 0;

            if( $invoice->exento_iva == 'si' ){
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            }
            else if( $invoice->nosujeto_iva == 'si' ){
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            }
            else{
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            array_push($details, [
                "numItem"       => $value->line,
                "cantidad"      => intval($value->quantity),
                "codigo"        => $value->item->product->product_code,
                "uniMedida"     => intval($value->item->product->unim_id),
                "descripcion"   => $value->description,
                "precioUni"     => floatval(number_format($value->unit_cost, 2, '.', '')),
                "montoDescu"    => intval($value->discount),
                "ventaGravada"  => floatval(number_format($gravado, 2, '.', '')),
                "tributos"      => Tax::whereIn('id', $value->taxes->pluck('tax_id')->toArray())
                                        ->pluck('trib_id')->toArray(),
                "noGravado"     => 0.0
            ]);
        }

        //Número de documento de receptor
        $documento = $invoice->num_documento;
        $documento = str_replace('-', '', $documento);

        if ($invoice->tdocrec_id == 13) {
            $parte1 = substr($documento, 0, 8);
            $parte2 = substr($documento, 8);
            $documento = $parte1 . '-' . $parte2;
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $invoice->tipodoc_id,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContigencia" => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $dteJson = [
            "identificacion" => $identificacion,
            "emisor" => [
                "nit"                   => str_replace('-', '', get_option('nit')),
                "nrc"                   => str_replace('-', '', get_option('nrc')),
                "nombre"                => get_option('company_name'),
                "codActividad"          => get_option('cod_actividad'),
                "descActividad"         => get_option('desc_actividad'),
                "nombreComercial"       => get_option('tradename'),
                "tipoEstablecimiento"   => $company->tipoest_id,
                "direccion" => [
                    "departamento"  => $company->depa_id,
                    "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                    "complemento"   => $company->address
                ],
                "telefono"          => $company->cellphone,
                "correo"            => $company->email,
                "codEstableMH"      => $company->codigo_sucursal,
                "codEstable"        => $company->codigo_sucursal,
                "codPuntoVentaMH"   => $cash->cash_code,
                "codPuntoVenta"     => $cash->cash_code,
                //TIPO DE ITEM  1 = BIENES; 2 = SERVICIOS; 3 = AMBOS
                "tipoItemExpor"     => 3,
                "recintoFiscal"     => ( isset($tributos->refisc_id) && $tributos->refisc_id != '' ) ? $tributos->refisc_id : null,
                "regimen"           => ( isset($tributos->regi_id) && $tributos->regi_id != '' ) ? $tributos->regi_id : null,
            ],
            "receptor" => [
                "nombre"            => $invoice->name_invoice,
                "tipoDocumento"     => $invoice->tdocrec_id,
                "numDocumento"      => $documento,
                "nombreComercial"   => $invoice->client->tradename,
                "codPais"           => $invoice->client->pais_id,
                "nombrePais"        => $invoice->client->pais->pais_nombre,
                "complemento"       => $invoice->client->address.', '.$invoice->client->municipio->muni_nombre.', '.$invoice->client->departamento->depa_nombre,
                "tipoPersona"       => intval($invoice->client->tpers_id),
                "descActividad"     => $invoice->client->descActividad,
                "telefono"          => $invoice->telefono,
                "correo"            => $invoice->correo
            ],
            "otrosDocumentos"   => null,
            "ventaTercero"      => null,
            "cuerpoDocumento"   => $details,
            "resumen" => [
                "totalGravada"              => floatval(number_format($gravadoSum, 2, '.', '')),
                "descuento"                 => floatval(number_format($descuento, 2, '.', '')),
                "porcentajeDescuento"       => 0.0,
                "totalDescu"                => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
                "seguro"                    => 0,
                "flete"                     => 0,
                "montoTotalOperacion"       => floatval(number_format($invoice->grand_total, 2, '.', '')),
                "totalNoGravado"            => 0,
                "totalPagar"                => floatval(number_format($invoice->grand_total, 2, '.', '')),
                "totalLetras"               => _lang('It is') . ' ' . dollarToText($invoice->grand_total) . ' USD',
                "condicionOperacion"        => intval($invoice->conop_id),
                "pagos" => [
                    [
                        "codigo"        => $invoice->forp_id,
                        "montoPago"     => floatval(number_format($invoice->grand_total, 2, '.', '')),
                        "referencia"    => null,
                        "plazo"         => null,
                        "periodo"       => null
                    ]
                ],
                "codIncoterms"          => ( $invoice->id_incoterms != '' ) ? $invoice->id_incoterms : null,
                "descIncoterms"         => ( $invoice->id_incoterms != '' ) ? $invoice->incoterm->nombre_incoterms : null,
                "numPagoElectronico"    => null,
                "observaciones"         => $invoice->note
            ],
            "apendice" => null
        ];

        log::info(json_encode($dteJson));
        return $dteJson;
    }

    private static function getDteJsonNotaRemision($invoice, $versionJson, $ambiente)
    {

        $company = Company::find($invoice->company_id);
        $cash = get_cash();
        $details = [];
        $documentosRelacionados = [];
        $documentosRelacionados = collect($documentosRelacionados);

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $totalTributos      = 0;

        $tributos = DB::select("SELECT tribs.trib_id as codigo, tribs.trib_nombre as descripcion, SUM(iit.amount) as valor
                                FROM invoice_item_taxes iit
                                join taxs t on iit.tax_id = t.id 
                                join tributos tribs on t.trib_id = tribs.trib_id 
                                where invoice_id = $invoice->id 
                                GROUP BY tribs.trib_id");

        $arrTributos = [];

        foreach ($tributos as $tributo) {

            $arrTributos[] = [
                'codigo'        => $tributo->codigo,
                'descripcion'   => $tributo->descripcion,
                'valor'         => floatval(number_format($tributo->valor, 2, '.', '')),
            ];

            $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $invoice->tipodoc_id,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContin"      => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nrc"                   => str_replace('-', '', get_option('nrc')),
            "nombre"                => get_option('company_name'),
            "codActividad"          => get_option('cod_actividad'),
            "descActividad"         => get_option('desc_actividad'),
            "nombreComercial"       => get_option('tradename'),
            "tipoEstablecimiento"   => $company->tipoest_id,
            "direccion" => [
                "departamento"  => $company->depa_id,
                "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                "complemento"   => $company->address
            ],
            "telefono"          => $company->cellphone,
            "correo"            => $company->email,
            "codEstableMH" => $company->codigo_sucursal,
            "codEstable" => $company->codigo_sucursal,
            "codPuntoVentaMH" => $cash->cash_code,
            "codPuntoVenta" => $cash->cash_code
        ];

        //Número de documento de receptor
        $documento = $invoice->num_documento;
        $documento = str_replace('-', '', $documento);

        if ($invoice->tdocrec_id == 13) {
            $parte1 = substr($documento, 0, 8);
            $parte2 = substr($documento, 8);
            $documento = $parte1 . '-' . $parte2;
        }

        $receptor = [
            "tipoDocumento"     => $invoice->tdocrec_id,
            "numDocumento"      => $documento,
            "nrc"               => str_replace('-', '', $invoice->client->nrc),
            "nombre"            => $invoice->name_invoice,
            "codActividad"      => $invoice->client->actie_id,
            "descActividad"     => $invoice->client->descActividad,
            "nombreComercial"   => $invoice->client->tradename,
            "direccion" => [
                "departamento"  => $invoice->client->depa_id,
                "municipio"     => Municipio::find($invoice->client->munidepa_id)->muni_id,
                "complemento"   => $invoice->client->address
            ],
            "telefono"          => $invoice->telefono,
            "correo"            => $invoice->correo,
            "bienTitulo"        => 'Sr'
        ];

        foreach ($invoice->invoice_items as $key => $value) {

            $exento         = 0;
            $noSujeto       = 0;
            $gravado        = 0;
            $tributos_item  = [];

            if ($invoice->exento_iva == 'si') {
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            } else {
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            //SE AGREGA TRIBUTO DE IVA
            array_push($tributos_item, "20");

            $data = [
                "numItem"           => $value->line,
                "tipoItem"          => intval($value->item->tipoitem_id),
                "numeroDocumento"   => null,
                "codigo"            => $value->item->product->product_code,
                "codTributo"        => null,
                "descripcion"       => $value->description,
                "cantidad"          => intval($value->quantity),
                "uniMedida"         => intval($value->item->product->unim_id),
                "precioUni"         => floatval(number_format($value->unit_cost, 2, '.', '')),
                "montoDescu"        => intval($value->discount),
                "ventaNoSuj"        => $noSujeto,
                "ventaExenta"       => floatval(number_format($exento, 2, '.', '')),
                "ventaGravada"      => floatval(number_format($gravado, 2, '.', '')),
                "tributos"          => ( $invoice->exento_iva == 'no' ) ? $tributos_item : null,
            ];

            // $documentos = [
            //     'tipoDocumento'      => $value->type_dte_rel,
            //     'tipoGeneracion'     => 2, // 1= fisico, 2= Electronico
            //     'numeroDocumento'    => $value->cod_dte_rel,
            //     'fechaEmision'       => $value->date_dte_rel,
            // ];

            array_push($details, $data);
        }

        $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

        $montoTotal = $invoice->grand_total;

        $resumen = [
            "totalNoSuj"            => floatval(number_format($noSujetoSum, 2, '.', '')),
            "totalExenta"           => floatval(number_format($exentoSum, 2, '.', '')),
            "totalGravada"          => floatval(number_format($gravadoSum, 2, '.', '')),
            "subTotalVentas"        => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
            "descuNoSuj"            => 0.0,
            "descuExenta"           => 0.0,
            "descuGravada"          => 0.0,
            "porcentajeDescuento"   => 0.0,
            "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
            "tributos"              => $arrTributos,
            "subTotal"              => floatval(number_format($subTotal, 2, '.', '')),
            "montoTotalOperacion"   => floatval(number_format($montoTotal, 2, '.', '')),
            "totalLetras"           => _lang('It is') . ' ' . dollarToText($montoTotal) . ' USD',
        ];

        $extension = [
            'nombEntrega'     => get_option('company_name'),
            'docuEntrega'     => str_replace('-', '', get_option('nit')),
            'nombRecibe'      => $invoice->name_invoice,
            'docuRecibe'      => str_replace('-', '', $invoice->client->nit),
            'observaciones'   => $invoice->note
        ];

        $apendice = [
            [
                "campo" => "sucursal",
                "etiqueta" => "Sucursal",
                "valor" => $company->company_name
            ],
            [
                "campo" => "condicion_operacion",
                "etiqueta" => "Condicion de la operacion",
                "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
            ],
        ];

        $dteJson = [
            "identificacion"        => $identificacion,
            "documentoRelacionado"  => null,
            "emisor"                => $emisor,
            "receptor"              => $receptor,
            "ventaTercero"          => null,
            "cuerpoDocumento"       => $details,
            "resumen"               => $resumen,
            "extension"             => $extension,
            "apendice"              => $apendice
        ];

        return $dteJson;
    }

    private static function getDteJsonSujetoExcluido($invoice, $versionJson, $ambiente)
    {

        $company = Company::find($invoice->company_id);
        $cash = get_cash();
        $details = [];
        $documentosRelacionados = [];
        $documentosRelacionados = collect($documentosRelacionados);

        $noSujetoSum        = 0;
        $exentoSum          = 0;
        $gravadoSum         = 0;
        $descNoSujetoSum    = 0;
        $descExentoSum      = 0;
        $descGravadoSum     = 0;
        $descGlobalExento   = 0;
        $descGlobalNoSujeto = 0;
        $descGlobalGravado  = 0;
        $totalTributos      = 0;

        $tributos = DB::select("SELECT tribs.trib_id as codigo, tribs.trib_nombre as descripcion, SUM(iit.amount) as valor
                                FROM invoice_item_taxes iit
                                join taxs t on iit.tax_id = t.id 
                                join tributos tribs on t.trib_id = tribs.trib_id 
                                where invoice_id = $invoice->id 
                                GROUP BY tribs.trib_id");

        $arrTributos = [];

        foreach ($tributos as $tributo) {

            $arrTributos[] = [
                'codigo'        => $tributo->codigo,
                'descripcion'   => $tributo->descripcion,
                'valor'         => floatval(number_format($tributo->valor, 2, '.', '')),
            ];

            $totalTributos += floatval(number_format($tributo->valor, 2, '.', ''));
        }

        $identificacion = [
            "version"           => intval($versionJson),
            "ambiente"          => $ambiente,
            "tipoDte"           => $invoice->tipodoc_id,
            "numeroControl"     => $invoice->numero_control,
            "codigoGeneracion"  => $invoice->codigo_generacion,
            "tipoModelo"        => intval($invoice->modfact_id),
            "tipoOperacion"     => intval($invoice->tipotrans_id),
            "tipoContingencia"  => ($invoice->tconting_id != '') ? intval($invoice->tconting_id) : $invoice->tconting_id,
            "motivoContin"      => $invoice->motivo_contingencia,
            "fecEmi"            => Carbon::createFromFormat('d/m/Y', $invoice->invoice_date)->format('Y-m-d'),
            "horEmi"            => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->format('H:i:s'),
            "tipoMoneda"        => "USD"
        ];

        $emisor = [
            "nit"                   => str_replace('-', '', get_option('nit')),
            "nrc"                   => str_replace('-', '', get_option('nrc')),
            "nombre"                => get_option('company_name'),
            "codActividad"          => get_option('cod_actividad'),
            "descActividad"         => get_option('desc_actividad'),
            "direccion" => [
                "departamento"  => $company->depa_id,
                "municipio"     => Municipio::find($company->munidepa_id)->muni_id,
                "complemento"   => $company->address
            ],
            "telefono"          => $company->cellphone,
            "codEstableMH"      => $company->codigo_sucursal,
            "codEstable"        => $company->codigo_sucursal,
            "codPuntoVentaMH"   => $cash->cash_code,
            "codPuntoVenta"     => $cash->cash_code,
            "correo"            => $company->email,
        ];

        //Número de documento de receptor
        $documento = $invoice->num_documento;
        $documento = str_replace('-', '', $documento);

        if ($invoice->tdocrec_id == 13) {
            $parte1 = substr($documento, 0, 8);
            $parte2 = substr($documento, 8);
            $documento = $parte1 . '-' . $parte2;
        }

        $sujetoExcluido = [
            "tipoDocumento"     => $invoice->tdocrec_id,
            "numDocumento"      => $documento,
            "nombre"            => $invoice->name_invoice,
            "codActividad"      => $invoice->client->actie_id,
            "descActividad"     => $invoice->client->descActividad,
            "direccion" => [
                "departamento"  => $invoice->client->depa_id,
                "municipio"     => Municipio::find($invoice->client->munidepa_id)->muni_id,
                "complemento"   => $invoice->client->address
            ],
            "telefono"          => $invoice->telefono,
            "correo"            => $invoice->correo,
        ];

        foreach ($invoice->invoice_items as $key => $value) {

            $exento         = 0;
            $noSujeto       = 0;
            $gravado        = 0;
            $tributos_item  = [];

            if ($invoice->exento_iva == 'si') {
                $exento         = $value->sub_total;
                $exentoSum      += $value->sub_total;
                $descExentoSum  += $value->discount;
            } else if ($invoice->nosujeto_iva == 'si') {
                $noSujeto           = $value->sub_total;
                $noSujetoSum        += $value->sub_total;
                $descNoSujetoSum    += $value->discount;
            } else {
                $gravado        = $value->sub_total;
                $gravadoSum     += $value->sub_total;
                $descGravadoSum += $value->discount;
            }

            //SE AGREGA TRIBUTO DE IVA
            array_push($tributos_item, "20");

            $data = [
                "numItem"       => $value->line,
                "tipoItem"      => intval($value->item->tipoitem_id),
                "cantidad"      => intval($value->quantity),
                "codigo"        => $value->item->product->product_code,
                "uniMedida"     => intval($value->item->product->unim_id),
                "descripcion"   => $value->description,
                "precioUni"     => floatval(number_format($value->unit_cost, 2, '.', '')),
                "montoDescu"    => intval($value->discount),
                "compra"        => floatval(number_format($gravado, 2, '.', '')),
            ];

            array_push($details, $data);
        }

        $subTotal = $noSujetoSum + $exentoSum + $gravadoSum - ($descGlobalExento + $descGlobalNoSujeto + $descGlobalGravado);

        $montoTotal = $invoice->grand_total;

        $resumen = [
            "totalCompra"           => floatval(number_format($montoTotal, 2, '.', '')),
            "descu"                 => 0.0,
            "totalDescu"            => floatval(number_format($descExentoSum + $descGravadoSum + $descNoSujetoSum + $descGlobalExento + $descGlobalGravado + $descGlobalNoSujeto, 2, '.', '')),
            "subTotal"              => floatval(number_format($noSujetoSum + $exentoSum + $gravadoSum, 2, '.', '')),
            "ivaRete1"              => floatval(number_format($invoice->iva_retenido, 2, '.', '')),
            "reteRenta"             => floatval(number_format($invoice->isr_retenido, 2, '.', '')),
            "totalPagar"            => floatval(number_format($montoTotal, 2, '.', '')),
            "totalLetras"           => _lang('It is') . ' ' . dollarToText($montoTotal) . ' USD',
            "condicionOperacion"    =>  intval($invoice->conop_id),
            "pagos" => [
                [
                    "codigo"        => $invoice->forp_id,
                    "montoPago"     => floatval(number_format($invoice->grand_total, 2, '.', '')),
                    "referencia"    => null,
                    "plazo"         => null,
                    "periodo"       => null
                ]
            ],
            "observaciones"         => $invoice->note
        ];

        $apendice = [
            [
                "campo" => "sucursal",
                "etiqueta" => "Sucursal",
                "valor" => $company->company_name
            ],
            [
                "campo" => "condicion_operacion",
                "etiqueta" => "Condicion de la operacion",
                "valor" => $invoice->condicion_operacion ? $invoice->condicion_operacion->conop_nombre : null,
            ],
        ];

        $dteJson = [
            "identificacion"        => $identificacion,
            "emisor"                => $emisor,
            "sujetoExcluido"        => $sujetoExcluido,
            "cuerpoDocumento"       => $details,
            "resumen"               => $resumen,
            "apendice"              => $apendice
        ];

        return $dteJson;
    }

    protected function sendEmailFactura($id, $anulacion = false)
    {
        try {

            $invoice = Invoice::find($id);

            // Generar el PDF utilizando la función separada
            $pdf = $this->downloadPdf($id);

            Log::info('Se genera el PDF temporal: ' . $pdf);

            $jsonFilePath = $this->downloadJson($id);

            Log::info('Se genera el JSON temporal: ' . $jsonFilePath);

            // Preparar el contenido del correo electrónico
            $subject = 'Factura Electrónica';
            
            if( $anulacion ){
                $subject = 'Anulación de Factura Electrónica';
            }

            $content = [
                'subject' => $subject,
                'body' => 'Estimado cliente: ' . $invoice->name_invoice,
            ];

            // Enviar el correo electrónico con el archivo adjunto
            $mail = Mail::to($invoice->correo)->send(new MailMailable($content, $jsonFilePath, $pdf, $id, $anulacion));

            if( isset($invoice->correo_alterno) && $invoice->correo_alterno != '' ){
                $mail2 = Mail::to($invoice->correo_alterno)->send(new MailMailable($content, $jsonFilePath, $pdf, $id, $anulacion, $invoice->numero_control));
            }

            try {
                Storage::delete('pdf_invoices/' . $pdf);
                Log::info('Se elimina el PDF temporal: ' . $pdf);
            } catch (\Exception $e) {
                Log::info('Error al eliminar el PDF temporal: ' . $pdf);
            }

            Log::info('Envio por correo de DTE con ID: ' . $id);

            Log::info('Correos a los que se envio el DTE: ' . $invoice->correo. ' '. $invoice->correo_alterno);

            $mail = $mail.($mail2 ?? '');

            return $mail;
        } catch (\Exception $e) {
            // Manejo de errores si el correo electrónico no se pudo enviar
            \Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
        }
    }

    protected function downloadJson($id_invoice)
    {


        $invoice = Invoice::find($id_invoice);

        $json = json_decode($invoice->json_dte);

        $json_temp = 'invoice_' . $invoice->id . '.json';

        if (Storage::exists('json_invoices' . $json_temp)) {
            Storage::delete($json_temp);
        }

        Storage::put('json_invoices/' . $json_temp, $json);

        $json_path = storage_path('app/json_invoices/' . $json_temp);

        try {

            // Verificar si el archivo existe
            if (file_exists($json_path)) {
                // Devolver el archivo para su descarga

                if (request()->has('download')) {
                    return response()->download($json_path, 'invoice.json', [], 'inline');
                } else {
                    return $json_temp;
                }
            } else {
                // Manejar el caso en que el archivo no exista
                abort(404);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error al generar y almacenar el JSON: ' . $e->getMessage());
            Log::error('Error al generar y almacenar el JSON: ' . $e->getMessage());
        }
    }

    protected function downloadPdf($id)
    {

        $invoice = Invoice::find($id);

        $invoice_taxes = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();
        $transactions = Transaction::where("invoice_id", $id)->get();
        $url = generateUrl($invoice);

        $codigoQR = QrCode::size(100)->generate($url);

        $pdfView = '';
        // Lógica para determinar la vista del PDF basada en el tipo de documento
        switch ($invoice->tipodoc_id) {
            case '11': // FEXE
                $pdfView = 'backend.accounting.invoice.fex.facturaFex';
                break;
            case '03': // CCFE
                $pdfView = 'backend.accounting.invoice.ccf.facturaCCF';
                break;
            case '01': // FE
                $pdfView = 'backend.accounting.invoice.fe.facturaFe';
                break;
            case '05': // NC
                $pdfView = 'backend.accounting.invoice.nc.pdf_export';
                break;
            default:
                // Definir una vista predeterminada si no se encuentra el tipo de documento
                $pdfView = 'backend.accounting.invoice.default.pdf';
                break;
        }

        // Generar el contenido del PDF
        $pdf = \PDF::loadView($pdfView, compact('invoice', 'invoice_taxes', 'transactions', 'url', 'codigoQR'))
            ->setPaper('letter', 'portrait');
        
        if( $invoice->status == 'Canceled' ){
            $pdf->output();
            $canvas = $pdf->getDomPDF()->getCanvas();
    
            $height = $canvas->get_height();
            $width = $canvas->get_width();
    
            $canvas->set_opacity(.2,"Multiply");
    
            $canvas->page_text($width/5, $height/2, 'ANULADO', null,
            75, array(255,0,0),17,20,-30);
        }

        // Almacenar el PDF en el directorio de almacenamiento con un nombre único
        $pdf_temp = 'invoice_' . $invoice->id . '.pdf';

        if (Storage::exists('pdf_invoices' . $pdf_temp)) {
            Storage::delete($pdf_temp);
        }

        Storage::put('pdf_invoices/' . $pdf_temp, $pdf->output());

        $pdf_path = storage_path('app/pdf_invoices/' . $pdf_temp);

        try {

            if (request()->has('download')) {
                return $pdf->download('invoice_' . $invoice->numero_control . '.pdf');
            } else {
                $pdf->save($pdf_path);

                return $pdf_temp;
            }
        } catch (\Exception $e) {
            throw new \Exception('Error al generar y almacenar el PDF: ' . $e->getMessage());
            Log::error('Error al generar y almacenar el PDF: ' . $e->getMessage());
        }
    }
}
