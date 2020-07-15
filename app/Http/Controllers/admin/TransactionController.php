<?php

namespace App\Http\Controllers\admin;

use App\Http\Resources\Admin\Transaction\BookingTransactionDetailResource;
use App\TransactionInit;
use App\TransactionDetail;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;


class TransactionController extends Controller
{
    public function transactionInits($booking_info_id){

        return view('admin.clients.bookings.transactions.transactions')->with('booking_info_id', $booking_info_id);
    }

    public function transactionDetails($booking_info_id, $transaction_init_id){

        return view('admin.clients.bookings.transactions.transaction-details')->with('booking_info_id', $booking_info_id)->with('transaction_init_id', $transaction_init_id);
    }

    public function transactionInitsListing($booking_info_id)
    {
        $transaction_detail = TransactionInit::with(['transactions_detail', 'transactions_detail.payment_gateway_form', 'pmsForm', 'user_account'])->where('booking_info_id', $booking_info_id)->get();
        return [
            'data' => BookingTransactionDetailResource::collection($transaction_detail),
        ];
    }

    public function transactionDetailsListing($booking_info_id, $transaction_init_id) {

      $transaction_details = TransactionDetail::where('transaction_init_id', $transaction_init_id)->get();
      return Datatables::of($transaction_details)
                ->editColumn('payment_status', function($row) {
                    return '<span class="'.config("db_const.transactions_init.status_button_color.".$row['payment_status']).'">'.config("db_const.transactions_init.payment_status.".$row['payment_status']).'</span>';
                })
                ->rawColumns(['payment_status'])
                ->addIndexColumn()
                ->make(true);
    }

    public function transaction_audit_logs($transaction_init_id) {
        $transaction_init = TransactionInit::find($transaction_init_id);
        $audits = $transaction_init->audits;

        $final_audits = [];

        $i = 0;
        foreach ($audits as $audit) {
          foreach($audit->getModified() as $field => $value) {

            $final_audits[$i]['field'] = $field;
            $final_audits[$i]['event'] = ucfirst($audit->event);
            $final_audits[$i]['created_at'] = isset($audit->created_at) ? $audit->created_at->toDateTimeString(): '-';
            
            //for old value column arrange data
            if(isset($value['old']) && !is_array($value['old']) && !is_object($value['old']))
              $final_audits[$i]['old_value'] = (strlen($value['old'])>0 && $value['old'][0] != "{") ? $value['old']:'This is a large object.'; //if its start with { then its a aobject 
            elseif(isset($value['old']) && is_array($value['old']))
              $final_audits[$i]['old_value'] = isset($value['old']['date']) ? $value['old']['date'] : '-';
            elseif(isset($value['old']) && is_object($value['old']))
              $final_audits[$i]['old_value'] = 'This is a large object.';
            else
              $final_audits[$i]['old_value'] = '-';
            
            //for new value column arrange data
            if(isset($value['new']) && !is_array($value['new']) && !is_object($value['new']))
              $final_audits[$i]['new_value'] = (strlen($value['new'])>0  && $value['new'][0] != "{") ? $value['new']:'This is a large object.'; //if its start with { then its a aobject 
            elseif(isset($value['new']) && is_array($value['new']))
              $final_audits[$i]['new_value'] = isset($value['new']['date']) ? $value['new']['date'] : '-';
            elseif(isset($value['new']) && is_object($value['new']))
              $final_audits[$i]['new_value'] = 'This is a large object.';
            else
              $final_audits[$i]['new_value'] = '-';

            //increment counter 
            $i++;

          }
        }    
        return Datatables::of($final_audits)
                ->addIndexColumn()
                ->make(true);
    }

    public function transaction_detail_audit_logs($transaction_detail_id){
        $transaction_detail = TransactionDetail::find($transaction_detail_id);
        $audits = $transaction_detail->audits;

        $final_audits = [];

        $i = 0;
        foreach ($audits as $audit) {
          foreach($audit->getModified() as $field => $value) {

            $final_audits[$i]['field'] = $field;
            $final_audits[$i]['event'] = ucfirst($audit->event);
            $final_audits[$i]['created_at'] = isset($audit->created_at) ? $audit->created_at->toDateTimeString(): '-';
            
            //for old value column arrange data
            if(isset($value['old']) && !is_array($value['old']) && !is_object($value['old']))
              $final_audits[$i]['old_value'] = $value['old'][0] != "{" ? $value['old']:'This is a large object.'; //if its start with { then its a aobject 
            elseif(isset($value['old']) && is_array($value['old']))
              $final_audits[$i]['old_value'] = isset($value['old']['date']) ? $value['old']['date'] : '-';
            elseif(isset($value['old']) && is_object($value['old']))
              $final_audits[$i]['old_value'] = 'This is a large object.';
            else
              $final_audits[$i]['old_value'] = '-';
            
            //for new value column arrange data
            if(isset($value['new']) && !is_array($value['new']) && !is_object($value['new']))
              $final_audits[$i]['new_value'] = $value['new'][0] != "{" ? $value['new']:'This is a large object.'; //if its start with { then its a aobject 
            elseif(isset($value['new']) && is_array($value['new']))
              $final_audits[$i]['new_value'] = isset($value['new']['date']) ? $value['new']['date'] : '-';
            elseif(isset($value['new']) && is_object($value['new']))
              $final_audits[$i]['new_value'] = 'This is a large object.';
            else
              $final_audits[$i]['new_value'] = '-';

            //increment counter 
            $i++;

          }
        }
        
        return Datatables::of($final_audits)
                ->addIndexColumn()
                ->make(true);
    }
}
