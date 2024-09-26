<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $jsonFilePath;
    public $pdf_path;
    public $invoice;
    public $invoice_taxes;
    public $transactions;
    public $url;
    public $anulacion;
    public $numero_control;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content, $jsonFilePath, $pdf_path, $invoice_id, $anulacion, $numero_control)
    {
        $this->content          = $content;
        $this->jsonFilePath     = $jsonFilePath;
        $this->pdf_path         = $pdf_path;
        $this->invoice          = $invoice_id;
        $this->anulacion        = $anulacion;
        $this->numero_control   = $numero_control;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->content['subject'])
        ->markdown('email.general_template')
        ->with([
            'invoice'       => $this->invoice,
            'anulacion'     => $this->anulacion
        ])
        ->attach(Storage::path('pdf_invoices/'.$this->pdf_path), [
            'as'    => $this->numero_control.'.pdf',
            'mime'  => 'application/pdf',
        ])
        ->attach(Storage::path('json_invoices/'.$this->jsonFilePath), [
            'as'    => $this->numero_control.'.json',
            'mime'  => 'application/json',
        ]);
    
    }
}
