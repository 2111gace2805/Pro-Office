<?php

namespace App\Http\Controllers;

use App\CompanySetting;
use App\Invoice;
use App\InvoiceItemTax;
use App\Quotation;
use App\QuotationItemTax;
use App\Transaction;
use Auth;
use DB;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PDF;
use Stripe;

class ClientController extends Controller {

    public function invoices($status = '') {
        $data = array();
        if ($status != '') {
            $data['invoices'] = Invoice::where('client_id', Auth::user()->client->id)
                ->where('status', $status)->get();
        } else {
            $data['invoices'] = Invoice::where('client_id', Auth::user()->client->id)->get();
        }
        return view('backend.client_panel.invoices', $data);

    }

    public function view_invoice($id) {

        $id = decrypt($id);

        $invoice = Invoice::find($id);

        $invoice_taxes = InvoiceItemTax::where('invoice_id', $invoice->id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();

        $transactions = Transaction::where('invoice_id', $id)->get();

        return view('backend.client_panel.view_invoice', compact('invoice', 'invoice_taxes', 'transactions'));
    }

    /**
     * Generate PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_pdf_invoice($id) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $id = decrypt($id);

        $invoice               = Invoice::find($id);
        $data['invoice']       = $invoice;
        $data['invoice_taxes'] = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*, sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();
        $data['transactions'] = Transaction::where("invoice_id", $id)->get();

        $pdf = PDF::loadView("backend.accounting.invoice.pdf_export", $data);
        $pdf->setWarnings(false);

        return $pdf->download("invoice_{$invoice->invoice_number}.pdf");
    }

    /**
     * Generate PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_pdf_quotation($id) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $id = decrypt($id);

        $quotation = Quotation::find($id);

        $data['quotation'] = $quotation;

        $data['quotation_taxes'] = QuotationItemTax::where('quotation_id', $id)
            ->selectRaw('quotation_item_taxes.*,sum(quotation_item_taxes.amount) as tax_amount')
            ->groupBy('quotation_item_taxes.tax_id')
            ->get();

        $pdf = PDF::loadView("backend.accounting.quotation.pdf_export", $data);
        $pdf->setWarnings(false);

        //return $pdf->stream();
        return $pdf->download("quotation_{$quotation->quotation_number}.pdf");
    }

    public function quotations() {
        $data               = array();
        $data['quotations'] = Quotation::where('client_id', Auth::user()->client->id)->get();

        return view('backend.client_panel.quotations', $data);

    }

    public function view_quotation($id) {

        $id = decrypt($id);

        $quotation = Quotation::find($id);

        $quotation_taxes = QuotationItemTax::where('quotation_id', $quotation->id)
            ->selectRaw('quotation_item_taxes.*,sum(quotation_item_taxes.amount) as tax_amount')
            ->groupBy('quotation_item_taxes.tax_id')
            ->get();

        return view('backend.client_panel.view_quotation', compact('quotation', 'quotation_taxes'));
    }

    public function transactions() {
        $transactions = Transaction::where('payer_payee_id', Auth::user()->client->id)->get();
        return view('backend.client_panel.transactions', compact('transactions'));
    }

    public function view_transaction(Request $request, $id) {
        $transaction = Transaction::where('id', $id)
            ->where('payer_payee_id', Auth::user()->client->id)->first();
        if ($request->ajax()) {
            return view('backend.client_panel.view_transaction', compact('transaction', 'id'));
        }

    }

    /** Stripe Payment Authorize **/
    public function stripe_payment_authorize(Request $request, $invoice_id) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $invoice = Invoice::find($invoice_id);

        Stripe\Stripe::setApiKey(get_option('stripe_secret_key'));
        $charge = Stripe\Charge::create([
            "amount"      => ($invoice->grand_total - $invoice->paid) * 100,
            "currency"    => get_option('currency'),
            "source"      => $request->stripeToken,
            "description" => _lang('Payment of Invoice') . ' ' . $invoice->invoice_number,
        ]);

        // Retrieve Charge Details
        if ($charge->amount_refunded == 0 && $charge->failure_code == null && $charge->paid == true && $charge->captured == true) {

            $amount = $charge->amount / 100;

            DB::beginTransaction();

            //Create Transaction
            $transaction                    = new Transaction();
            $transaction->trans_date        = date('Y-m-d');
            $transaction->account_id        = get_option('default_account');
            $transaction->chart_id          = get_option('default_chart_id');
            $transaction->type              = 'income';
            $transaction->dr_cr             = 'cr';
            $transaction->amount            = $amount;
            $transaction->payer_payee_id    = $invoice->client_id;
            $transaction->payment_method_id = create_payment_method('Stripe');
            $transaction->invoice_id        = $invoice->id;
            $transaction->company_id        = $invoice->company_id;

            $transaction->save();

            //Update Invoice Table
            $invoice->paid   = $invoice->paid + $amount;
            $invoice->status = 'Paid';
            $invoice->save();

            //Trigger Invocie Paid Event
            //event(new \App\Events\InvoicePaid($invoice));

            DB::commit();
        }

        return back()->with('success', _lang('Thank You, Your payment was made sucessfully.'));

    }

    /* PayPal Payment Authorize */
    public function paypal_payment_authorize(Request $request, $paypalOrderId, $invoice_id) {

        $invoice = Invoice::find($invoice_id);

        // Creating an environment
        $clientId     = get_option('paypal_client_id');
        $clientSecret = get_option('paypal_secret');

        if (get_option('paypal_mode') == 'sandbox') {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }

        $client = new PayPalHttpClient($environment);

        $request = new OrdersCaptureRequest($paypalOrderId);
        $request->prefer('return=representation');

        try {
            $response = $client->execute($request);

            if ($response->result->status == 'COMPLETED') {

                DB::beginTransaction();

                $amount = $response->result->purchase_units[0]->amount->value;

                if ($amount >= ($invoice->grand_total - $invoice->paid)) {

                    $transaction                    = new Transaction();
                    $transaction->trans_date        = date('Y-m-d');
                    $transaction->account_id        = get_option('default_account');
                    $transaction->chart_id          = get_option('default_chart_id');
                    $transaction->type              = 'income';
                    $transaction->dr_cr             = 'cr';
                    $transaction->amount            = $amount;
                    $transaction->payer_payee_id    = $invoice->client_id;
                    $transaction->payment_method_id = create_payment_method('PayPal');
                    $transaction->invoice_id        = $invoice->id;
                    $transaction->company_id        = $invoice->company_id;

                    $transaction->save();

                    //Update Invoice Table
                    $invoice->paid   = ($invoice->paid + $amount);
                    $invoice->status = 'Paid';
                    $invoice->save();
                }

                //Trigger Invoice Paid Event
                //event(new \App\Events\InvoicePaid($invoice));

                DB::commit();

                return back()->with('success', _lang('Thank You, Your payment was made sucessfully.'));
            }

        } catch (HttpException $ex) {
            return back()->with('error', _lang('Sorry, Payment not completed !'));
        }

    }

}
