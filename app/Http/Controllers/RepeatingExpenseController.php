<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RepeatTransaction;
use App\Transaction;
use Validator;
use Illuminate\Validation\Rule;
use DateTime;
use DataTables;
use Illuminate\Support\Facades\Session;

class RepeatingExpenseController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.accounting.repeating_expense.list');
	}
	
	public function get_table_data(){
		
		$currency = currency();

		$transactions = RepeatTransaction::with("account")->with("expense_type")
										 ->with("payee")->with("payment_method")
										 ->select('repeating_transactions.*')
										 ->where("repeating_transactions.type","expense")
										 ->orderBy("repeating_transactions.id","desc")
										 ->where('company_id', Session::get('company')->id);

		return Datatables::eloquent($transactions)
						->editColumn('amount', function ($trans) use ($currency){
							return "<span class='float-right'>" . decimalPlace($trans->amount, $currency) . "</span>";
						})
						->editColumn('payee.contact_name', function ($trans) {
							return isset($trans->payee->contact_name) ? $trans->payee->contact_name : '';
						})
						->editColumn('status', function ($trans) {
                            return $trans->status == 0 ? '<span class="badge badge-danger">'._lang('Pending').'</span>' : '<span class="badge badge-success">'._lang('Completed').'</span>';
						})
						->addColumn('action', function ($trans) {
							return '<form action="'.action('RepeatingExpenseController@destroy', $trans['id']).'" class="text-center" method="post">'
							.'<a href="'.action('RepeatingExpenseController@edit', $trans['id']).'" data-title="'._lang('Update Income') .'" class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a> '
							.'<a href="'.action('RepeatingExpenseController@show', $trans['id']).'" data-title="'._lang('View Income') .'" class="btn btn-primary btn-sm ajax-modal"><i class="ti-eye"></i></a> '
							.csrf_field()
							.'<input name="_method" type="hidden" value="DELETE">'
							.'<button class="btn btn-danger btn-sm btn-remove" type="submit"><i class="ti-trash"></i></button>'
							.'</form>';
						})
						->setRowId(function ($trans) {
							return "row_".$trans->id;
						})
						->rawColumns(['status','action','amount'])
						->make(true);							    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
		if( ! $request->ajax()){
		   return view('backend.accounting.repeating_expense.create');
		}else{
           return view('backend.accounting.repeating_expense.modal.create');
		}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {	
	    @ini_set('max_execution_time', 0);
		@set_time_limit(0);
		
		$validator = Validator::make($request->all(), [
			'trans_date' => 'required',
			'account_id' => 'required',
			'chart_id' => 'required',
			'amount' => 'required|numeric',
			'payment_method_id' => 'required',
			'rotation' => 'required',
			'num_of_rotation' => 'required|integer|min:1',
			'reference' => 'nullable|max:50',
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect()->route('repeating_expense.create')
							->withErrors($validator)
							->withInput();
			}			
		}
			

		$date = $request->input('trans_date');
		$increment = $request->rotation;
		$loop      = $request->num_of_rotation;
		
		
		for ($i = 0; $i < $loop; $i++) {	
			$transaction= new RepeatTransaction();
			$transaction->trans_date = $date;
			$transaction->account_id = $request->input('account_id');
			$transaction->chart_id = $request->input('chart_id');
			$transaction->type = 'expense';
			$transaction->dr_cr = 'dr';
			$transaction->amount = $request->input('amount');
			$transaction->payer_payee_id = $request->input('payer_payee_id');
			$transaction->payment_method_id = $request->input('payment_method_id');
			$transaction->reference = $request->input('reference');
			$transaction->note = $request->input('note');
			$transaction->save();	
			
			$date = date('Y-m-d', strtotime($date . ' + ' . $increment));
		
			$d = new DateTime( $request->input('trans_date') );
			
			if($d->format('d')=='31'){
				$dd = new DateTime( $date );
				if( (int)$dd->format('d') < 31 &&  $dd->format('m') != '03'){
					$temp_date = new DateTime( date('Y-m-d', strtotime($date . ' - 1 day') ));								
					$temp_date->modify("last day of this month");
					$date = $temp_date->format( 'Y-m-d' );
				}else if((int)$dd->format('d') == 28 && $dd->format('m') == '03'){
					$dd->modify("last day of this month");
					$date = $dd->format("Y-m-d");
				}else if((int)$dd->format('d') < 31 && $dd->format('m') == '03'){
					$dd->modify("last day of previous month");
					$date = $dd->format("Y-m-d");
				}
			}else if($d->format('d')=='30'){
				$dd = new DateTime( $date );
				if( (int)$dd->format('d') < 30 &&  $dd->format('m') != '03'){
					$temp_date = new DateTime( date('Y-m-d', strtotime($date . ' - 5 day') ));								
					$temp_date->modify("last day of this month");
					$date = $temp_date->format( 'Y-m' )."-30";
				}else if((int)$dd->format('d') == 28 && $dd->format('m') == '03'){
					$dd->modify("last day of this month");
					$date = $dd->format("Y-m")."-30";
				}else if((int)$dd->format('d') < 30 && $dd->format('m') == '03'){
					$dd->modify("last day of previous month");
					$date = $dd->format("Y-m-d");
				}
			}
			//echo $data['date']."<br>";
		}
		

		if(! $request->ajax()){
           return redirect()->route('repeating_expense.index')->with('success', _lang('Saved Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Sucessfully'),'data'=>$transaction]);
		}
        
   }
	

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $transaction = RepeatTransaction::find($id);
		if(! $request->ajax()){
		    return view('backend.accounting.repeating_expense.view',compact('transaction','id'));
		}else{
			return view('backend.accounting.repeating_expense.modal.view',compact('transaction','id'));
		} 
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $transaction = RepeatTransaction::find($id);
		if(! $request->ajax()){
		   return view('backend.accounting.repeating_expense.edit',compact('transaction','id'));
		}else{
           return view('backend.accounting.repeating_expense.modal.edit',compact('transaction','id'));
		}  
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$validator = Validator::make($request->all(), [
			'trans_date' => 'required',
			'account_id' => 'required',
			'chart_id' => 'required',
			'amount' => 'required|numeric',
			'payment_method_id' => 'required',
			'reference' => 'nullable|max:50',
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect()->route('repeating_expense.edit', $id)
							->withErrors($validator)
							->withInput();
			}			
		}
		

		$transaction = RepeatTransaction::find($id);
		$transaction->trans_date = $request->input('trans_date');
		$transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'expense';
		$transaction->dr_cr = 'dr';
		$transaction->amount = $request->input('amount');
		$transaction->payer_payee_id = $request->input('payer_payee_id');
		$transaction->payment_method_id = $request->input('payment_method_id');
		$transaction->reference = $request->input('reference');
		$transaction->note = $request->input('note');
		$transaction->status = $request->input('status');
		
        $transaction->save();
		
		if($transaction->status == 1 ){
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
			$trans->save();
			
			$transaction->trans_id = $trans->id;
			$transaction->save();
			
		}else if( $transaction->status == 0 && $transaction->trans_id != "" ){
			$tran = Transaction::find($transaction->trans_id);
			$tran->delete();
			
			$transaction->trans_id = NULL;
			$transaction->save();
		}
	    
		//Set Related Data	
	    $transaction->trans_date = date('d M, Y',strtotime($transaction->trans_date));
	    $transaction->amount = currency()." ".decimalPlace($transaction->amount);
		$transaction->account_id = $transaction->account->account_title;
	    $transaction->chart_id = $transaction->income_type->name;
	    $transaction->payer_payee_id = isset($transaction->payer->contact_name) ? $transaction->payer->contact_name : '';
	    $transaction->payment_method_id = $transaction->payment_method->name;
        $transaction->status = $transaction->status == 0 ? '<span class="badge badge-danger">'._lang('Pending').'</span>' : '<span class="badge badge-success">'._lang('Completed').'</span>';
		
		if(! $request->ajax()){
           return redirect()->route('repeating_expense.index')->with('success', _lang('Updated Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Sucessfully'),'data'=>$transaction]);
		}
	    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		$transaction = RepeatTransaction::find($id);
        $transaction->delete();
        return back()->with('success',_lang('Removed Sucessfully'));
    }
}
