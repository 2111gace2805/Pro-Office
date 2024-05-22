<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use App\RepeatTransaction;
use App\User;
use App\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertNotificationMail;
use App\Utilities\Overrider;
use DB;

class CronJobsController extends Controller
{
	
    /**
     * Show the application CronJobs.
     *
     * @return \Illuminate\Http\Response
     */
    public function run()
    {
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);
		
		//Process Repeat Transactions
		$date = date("Y-m-d");
		$repeat_transaction = RepeatTransaction::where('trans_date',$date)
		                                       ->where('status',0)->get();
											   
		foreach($repeat_transaction as $transaction){
			if($transaction->type == 'income'){
				$trans = new Transaction();
				$trans->trans_date = $transaction->trans_date;
				$trans->account_id = $transaction->account_id;
				$trans->chart_id = $transaction->chart_id;
				$trans->type = 'income';
				$trans->dr_cr = 'cr';
				$trans->amount = $transaction->amount;
				$trans->payer_payee_id = $transaction->payer_payee_id;
				$trans->payment_method_id = $transaction->payment_method_id;
				$trans->reference = $transaction->reference;
				$trans->note = $transaction->note;
				$trans->company_id = $transaction->company_id;
				$trans->save();
				
				$transaction->trans_id = $trans->id;
				$transaction->status = 1;
				$transaction->save();		
			}else if($transaction->type == 'expense'){
				$trans= new Transaction();
				$trans->trans_date = $transaction->trans_date;
				$trans->account_id = $transaction->account_id;
				$trans->chart_id = $transaction->chart_id;
				$trans->type = 'expense';
				$trans->dr_cr = 'dr';
				$trans->amount = $transaction->amount;
				$trans->payer_payee_id = $transaction->payer_payee_id;
				$trans->payment_method_id = $transaction->payment_method_id;
				$trans->reference = $transaction->reference;
				$trans->note = $transaction->note;
				$trans->company_id = $transaction->company_id;
				$trans->save();
				
				$transaction->trans_id = $trans->id;
				$transaction->status = 1;
				$transaction->save();
			}
		}
		
		
		echo 'Scheduled task runs successfully';
	
    }

}
