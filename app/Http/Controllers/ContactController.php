<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Invoice;
use App\Mail\GeneralMail;
use App\Quotation;
use App\Transaction;
use App\User;
use App\Utilities\Overrider;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('backend.accounting.contacts.contact.list');
    }

    public function get_table_data(Request $request) {

        $contacts = Contact::with("group")->with('tipo_persona')
                    // ->where('company_id', Session::get('company')->id)
                    ->orderBy("contacts.id", "desc");

        $data = Datatables::eloquent($contacts)

            ->editColumn('contact_image', function ($contact) {
                return '<img class="thumb-sm img-thumbnail" src="' . asset('public/uploads/contacts/' . $contact->contact_image) . '">';
            })
            ->addColumn('action', function ($contact) {
                return '<form action="' . action('ContactController@destroy', $contact['id']) . '" class="text-center" method="post">'
                . '<a href="' . action('ContactController@show', $contact['id']) . '" class="btn btn-primary btn-sm"><i class="ti-eye"></i></a> '
                . '<a href="' . action('ContactController@edit', $contact['id']) . '" class="btn btn-warning btn-sm"><i class="ti-pencil-alt"></i></a> '
                . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button class="btn btn-danger btn-sm btn-remove" type="submit"><i class="ti-trash"></i></button>'
                    . '</form>';
            })
            ->setRowId(function ($contact) {
                return "row_" . $contact->id;
            })
            ->rawColumns(['action', 'contact_image'])
            ->make(true);

            return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.contacts.contact.create');
        } else {
            return view('backend.accounting.contacts.contact.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            //'profile_type'  => 'required|max:20',
            'tpers_id'      => 'required',
            // 'company_name'  => 'nullable|max:50',
            'contact_email' => 'required|email|max:100',
            'contact_phone' => 'nullable|max:20',
            //'country'       => 'nullable|max:50',
            //'city'          => 'nullable|max:50',
            //'state'         => 'nullable|max:50',
            'pais_id'       => 'required',
            'munidepa_id'       => 'nullable',
            'depa_id'       => 'nullable',
            'zip'           => 'nullable|max:20', //User Login Attribute
            'payment_period' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('contacts.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $contact_image = "avatar.png";
        if ($request->hasfile('contact_image')) {
            $file          = $request->file('contact_image');
            $contact_image = "contact_image" . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . "/uploads/contacts/", $contact_image);
        }

        //Create Login details
        if ($request->client_login == 1) {
            $user                    = new User();
            $user->name              = $request->input('name');
            $user->email             = $request->input('email');
            $user->password          = Hash::make($request->password);
            $user->user_type         = 'client';
            $user->status            = $request->input('status');
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->company_id        = company_id();
            $user->save();
        }

        $contact                = new Contact();
        $contact->tpers_id      = $request->input('tpers_id');
        $contact->company_name  = $request->input('company_name');
        $contact->firstName     = $request->input('first_name');
        $contact->lastName      = $request->input('lastName');
        $contact->contact_name  = $request->input('contact_name');
        $contact->contact_email = $request->input('contact_email');
        $contact->contact_phone = $request->input('contact_phone');
        $contact->pais_id       = $request->input('pais_id');
        $contact->munidepa_id   = $request->input('munidepa_id');
        $contact->depa_id       = $request->input('depa_id');
        $contact->zip           = $request->input('zip');
        $contact->address       = $request->input('address');
        $contact->facebook      = $request->input('facebook');
        $contact->twitter       = $request->input('twitter');
        $contact->linkedin      = $request->input('linkedin');
        $contact->remarks       = $request->input('remarks');

        $contact->plazo_id      = $request->input('plazo_id');
        $contact->payment_period= $request->input('payment_period');
        $contact->tradename     = $request->input('tradename');
        // $contact->business_line = $request->input('business_line');
        $contact->actie_id      = $request->input('actie_id');
        $contact->descActividad = $request->input('descActividad');

        $contact->exento_iva = $request->input('exento_iva');
        $contact->nosujeto_iva = $request->input('nosujeto_iva');
        $contact->gran_contribuyente = $request->input('gran_contribuyente');

        $contact->nit = $request->input('nit');
        $contact->dui = $request->input('dui');
        $contact->nrc = $request->input('nrc');

        if ($request->client_login == 1) {
            $contact->user_id = $user->id;
        }
        $contact->group_id      = $request->input('group_id');
        $contact->contact_image = $contact_image;
        // $contact->condition_sales = $request->input('condition_sales');

        $contact->save();

        if (!$request->ajax()) {
            return redirect()->route('contacts.show', $contact->id)->with('success', _lang('New client added sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('New client added sucessfully'), 'data' => $contact]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $contact = Contact::find($id);

        $invoices = Invoice::where('client_id', $id)->get();

        $quotations = Quotation::where('client_id', $id)->get();

        $transactions = Transaction::where('payer_payee_id', $id)->get();

        if (!$request->ajax()) {
            return view('backend.accounting.contacts.contact.view', compact('contact', 'invoices', 'quotations', 'transactions', 'id'));
        } else {
            return view('backend.accounting.contacts.contact.modal.view', compact('contact', 'invoices', 'quotations', 'transactions', 'id'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $contact = Contact::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.contacts.contact.edit', compact('contact', 'id'));
        } else {
            return view('backend.accounting.contacts.contact.modal.edit', compact('contact', 'id'));
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $contact = Contact::find($id);

        $validator = Validator::make($request->all(), [
            'tpers_id'  => 'required',
            'company_name'  => 'nullable|max:50',
            'contact_email' => 'required|email|max:100',
            'contact_phone' => 'nullable|max:20',
            'pais_id'       => 'nullable',
            'munidepa_id'       => 'nullable',
            'depa_id'       => 'nullable',
            'zip'           => 'nullable|max:20',

            'name'          => 'required_if:client_login,1|max:191', //User Login Attribute
            'email' => [
                'required_if:client_login,1',
                Rule::unique('users')->ignore($contact->user_id),
            ], //User Login Attribute
            'password' => 'nullable|max:20|min:6|confirmed', //User Login Attribute
            'status' => 'required_if:client_login,1', //User Login Attribute
            'payment_period' => 'nullable|integer'
        ], [
            'name.required_if'     => 'Name is required',
            'email.required_if'    => 'Email is required',
            'password.required_if' => 'Password is required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('contacts.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        if ($request->hasfile('contact_image')) {
            $file          = $request->file('contact_image');
            $contact_image = "contact_image" . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . "/uploads/contacts/", $contact_image);
        }

        if ($request->client_login == 1) {
            if ($contact->user_id != NULL) {
                $user = User::find($contact->user_id);
            } else {
                $user = new User();
            }
            $user->name   = $request->input('name');
            $user->email  = $request->input('email');
            $user->status = $request->input('status');
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->user_type  = 'client';
            $user->company_id = company_id();
            $user->save();
        }

        $contact->tpers_id  = $request->input('tpers_id');
        $contact->company_name  = $request->input('company_name');
        $contact->contact_name  = $request->input('contact_name');
        $contact->contact_email = $request->input('contact_email');
        $contact->contact_phone = $request->input('contact_phone');
        $contact->pais_id       = $request->input('pais_id');
        $contact->munidepa_id       = $request->input('munidepa_id');
        $contact->depa_id       = $request->input('depa_id');
        $contact->zip           = $request->input('zip');
        $contact->address       = $request->input('address');
        $contact->facebook      = $request->input('facebook');
        $contact->twitter       = $request->input('twitter');
        $contact->linkedin      = $request->input('linkedin');
        $contact->remarks       = $request->input('remarks');
        $contact->group_id      = $request->input('group_id');

        $contact->plazo_id      = $request->input('plazo_id');
        $contact->payment_period= $request->input('payment_period');
        $contact->tradename     = $request->input('tradename');
        // $contact->business_line = $request->input('business_line');
        $contact->actie_id      = $request->input('actie_id');
        $contact->descActividad = $request->input('descActividad');

        $contact->exento_iva = $request->input('exento_iva');
        $contact->nosujeto_iva = $request->input('nosujeto_iva');
        $contact->gran_contribuyente = $request->input('gran_contribuyente');

        $contact->nit = $request->input('nit');
        $contact->dui = $request->input('dui');
        $contact->nrc = $request->input('nrc');

        if ($request->client_login == 1) {
            $contact->user_id = $user->id;
        }
        if ($request->hasfile('contact_image')) {
            $contact->contact_image = $contact_image;
        }

        $contact->save();

        if (!$request->ajax()) {
            return redirect()->route('contacts.show', $contact->id)->with('success', _lang('Client information updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Client information updated sucessfully'), 'data' => $contact]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $contact = Contact::find($id);
        $user    = User::find($contact->user_id);
        if ($user) {
            $user->delete();
        }
        $contact->delete();
        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }

    public function send_email(Request $request, $id) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        Overrider::load("Settings");

        $validator = Validator::make($request->all(), [
            'email_subject' => 'required',
            'email_message' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)
                    ->withInput();
            }
        }

        $contact = Contact::find($id);

        //Send email
        $subject = $request->input("email_subject");
        $message = $request->input("email_message");

        $mail          = new \stdClass();
        $mail->subject = $subject;
        $mail->body    = $message;

        try {
            Mail::to($contact->contact_email)
                ->send(new GeneralMail($mail));
        } catch (\Exception$e) {
            if (!$request->ajax()) {
                return back()->with('error', _lang('Sorry, Error Occured !'));
            } else {
                return response()->json(['result' => 'error', 'message' => _lang('Sorry, Error Occured !')]);
            }
        }

        if (!$request->ajax()) {
            return back()->with('success', _lang('Email Send Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Email Send Sucessfully'), 'data' => $contact]);
        }
    }

    public function get_contact(Request $request, $id) {
        $contact = Contact::with('actividad_economica')->with('departamento')
            ->with('municipio')->with('pais')->find($id);

        echo $contact;
    }


    public function cargarClientes(){
        return view('backend.accounting.contacts.load.create');
    }

    public function cargarClientesExcel(Request $request){
 
        $request->validate([
            'contacts_excel' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            set_time_limit(0);

            DB::beginTransaction();

            $file = $request->file('contacts_excel');
    
            // Leer el archivo Excel
            $spreadsheet = IOFactory::load($file);
    
            // Obtener la hoja por su nombre
            $sheet = $spreadsheet->getSheetByName('Clientes');
    
            // Verificar si la hoja existe
            if( !$sheet ){
                return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'La hoja especificada no fue encontrada en el archivo Excel.']);
            }
    
            // Obtener las filas y columnas máximas de la hoja
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
    
            // Inicializar un array para almacenar los datos de las filas
            $data = [];
            $columnas = [
                            'company_name',
                            'tradename',
                            'contact_name',
                            'contact_email',
                            'contact_phone',
                            'pais_id',
                            'zip',
                            'address',
                            'nit',
                            'business_line',
                            'nrc',
                            'tpers_id',
                            'dui',
                            'munidepa_id',
                            'depa_id',
                            'actie_id',
                            'plazo_id',
                            'payment_period',
                            'exento_iva',
                            'nosujeto_iva',
                            'gran_contribuyente',
                            'firstName',
                            'lastName'
                        ];
    
            // Obtener los nombres de las columnas (encabezados)
            $header = [];
            foreach( $sheet->getRowIterator(1, 1) as $row ){
                foreach( $row->getCellIterator() as $cell ){
                    $header[] = $cell->getValue();
                }
            }

            // Verificar que todas las columnas esperadas estén presentes en el archivo Excel
            foreach( $columnas as $columna ){
                if( !in_array($columna, $header) ){
                    return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'La columna ' . $columna . ' no está presente en el archivo Excel.']);
                }
            }

            $batchSize  = 100;
            $batchCount = 0;

            $lastColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
            $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);

            // dd($lastColumnLetter);
    
            for( $row = 2; $row <= $highestRow; $row++ ){
                $rowData = [];
    
                // Iterar sobre las columnas
                for( $col = 'A'; $col <= $highestColumn; $col++ ){
                    // Obtener el valor de la celda

                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    
                    $rowData[] = $cellValue;

                    if( $col === 'W' ){
                        break;
                    }
                }
                
                // Combinar los nombres de las columnas con los valores de esta fila
                $data[] = array_combine($header, $rowData);
                
                // Procesar el lote actual si se ha alcanzado el tamaño del lote
                if (count($data) >= $batchSize) {
                    $this->procesarLote($data, $batchCount);
                    $data = [];
                    $batchCount++;
                }
            }

            if (!empty($data)) {
                $this->procesarLote($data, $batchCount);
            }

            DB::commit();
            
            Log::info("Se guardan clientes");

            return response()->json(['result' => 'success', 'action' => 'store', 'message' => 'Clientes cargados correctamente']);
        }
        catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error al importar clientes: ' . $th->getMessage());

            // Imprimir el mensaje de error específico de la base de datos (si hay)
            if ($th instanceof \Illuminate\Database\QueryException && $th->errorInfo) {
                Log::error('Error de base de datos: ' . print_r($th->errorInfo, true));
            }

            return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'Error al guardar clientes, intente nuevamente']);

        }
    }


    private function procesarLote($data, $batchCount){

        Log::info("Procesando lote de clientes " . ($batchCount + 1));

        // Convertir el array de datos en una colección de objetos
        $clients = collect($data)->map(function ($item) {
            return (object) $item;
        });

        foreach( $clients as $key => $client ){

            // Verificar si todos los campos de la fila están vacíos
            $isEmptyRow = true;
            foreach ($client as $value) {
                if (!empty($value)) {
                    $isEmptyRow = false;
                    break;
                }
            }

            // Si todos los campos de la fila están vacíos, omitir esta fila
            if ($isEmptyRow) {
                Log::info("Fila vacía omitida: " . json_encode($client));
                continue;
            }

            $depa_id = $client->depa_id;

            // Verificar si $depa_id es un solo dígito y no está vacío
            $depa_id = (strlen($depa_id) === 1 && $depa_id !== '') ? '0' . $depa_id : $depa_id;

            // Ahora, se asegura de que $depa_id no esté vacío
            $depa_id = ($depa_id !== '') ? $depa_id : '06';


            $contact                        = new Contact();
            $contact->company_name          = $client->company_name;
            $contact->tradename             = $client->tradename;
            $contact->contact_name          = $client->contact_name;
            $contact->contact_email         = $client->contact_email;
            $contact->contact_phone         = $client->contact_phone;
            $contact->pais_id               = $client->pais_id;
            $contact->zip                   = $client->zip;
            $contact->address               = $client->address;
            $contact->company_id            = 1;
            $contact->nit                   = $client->nit;
            $contact->business_line         = $client->business_line;
            $contact->nrc                   = $client->nrc;
            $contact->tpers_id              = ( $client->tpers_id != '' ) ? $client->tpers_id : 1;
            $contact->dui                   = $client->dui;
            $contact->munidepa_id           = ( $client->munidepa_id != '' ) ? $client->munidepa_id : 182;
            $contact->depa_id               = $depa_id;
            $contact->actie_id              = ( $client->actie_id != '' ) ? $client->actie_id :  '01111';
            $contact->plazo_id              = $client->plazo_id;
            $contact->payment_period        = $client->payment_period;
            $contact->exento_iva            = $client->exento_iva;
            $contact->nosujeto_iva          = $client->nosujeto_iva;
            $contact->gran_contribuyente    = $client->gran_contribuyente;
            $contact->descActividad         = $client->business_line;
            $contact->firstName             = $client->first_name ?? '';
            $contact->lastName              = $client->lastName ?? '';

            Log::info("contact: " . json_encode($contact));

            $contact->save();

            Log::info("Se crea cliente: ".json_encode($contact));
        }
    }

    public function verifyClientExist(Request $request){

        $documento              = $request->documento;
        $tipo_perfil            = $request->tipo_perfil;
        $documento_sin_guion    = str_replace('-', '', $documento);
        $column                 = ( $tipo_perfil == 1 ) ? 'dui' : 'nrc';

        $contacts = [];
        
        $contacts = Contact::where('tpers_id', $tipo_perfil)
                    ->where(function ($query) use ($column, $documento, $documento_sin_guion) {
                        $query->where($column, $documento)
                            ->orWhere($column, $documento_sin_guion);
                    })
                    ->get();

        if( !$contacts->isEmpty() ){
            return response()->json(['result' => 'success', 'contacts' => $contacts]);
        }
        else{
            return response()->json(['result' => 'error', 'contacts' => $contacts]);
        }
    }
}