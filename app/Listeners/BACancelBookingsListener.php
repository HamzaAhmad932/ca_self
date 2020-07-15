<?php

namespace App\Listeners;


use App\CancellationSetting;
use App\Events\Emails\EmailEvent;
use App\Events\PMSPreferencesEvent;
use App\Events\SendEmailEvent;
use App\System\PMS\PMS;
use App\TransactionInit;
use App\BookingSourceForm;
use App\CreditCardAuthorization;
use App\System\PMS\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\BACancelBookingsEvent;
use App\System\PMS\Models\PmsOptions;
use App\Repositories\Bookings\BaRefund;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\Settings\PaymentSettings;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Repositories\Settings\PaymentSettingsOptions;


class BACancelBookingsListener
{
    /**
     * @var BACancelBookingsEvent
     */
    private $event;
    private $detailsArray;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  BACancelBookingsEvent  $event
     * @return void
     */
    
    public function handle(BACancelBookingsEvent $event){
       
        try {
            
            $this->event = $event;       
            $this->detailsArray = array(
                'PropertyInfo ID' => $this->event->propertyInfo->id,
                'pms_property_id' => $this->event->propertyInfo->pms_property_id,
                'PMS BookingId' => $this->event->bookingInfo->pms_booking_id ,
                'File' => 'BACancelBookingsListener');
            $this->cancelBooking();  //Entertain Booking 
            $this->updatePMS();            

        } catch (\Exception $e) {
            
            Log::error($e->getMessage(), $this->detailsArray);
        }
    }
    


    private function cancelBooking(){
        
        try {

            $cancellationSetting = $this->getSettings();
            $idsArr = $this->event->bookingInfo->transaction_init->pluck('id')->toArray();
            switch ($cancellationSetting['transactionType']) {
                case CancellationSetting::TRANSACTION_TYPE_VOID:
                    $this->voidAllTransactions($idsArr);
                    $this->voidAllAuths();
                    break;
                case CancellationSetting::TRANSACTION_TYPE_REFUND:
                    $this->voidAllTransactions($idsArr);
                    $this->voidAllAuths();
                    $amountToRefund = $this->preChargedAmount($idsArr);
                    if ($amountToRefund > 0) {
                        $this->refundAmount($amountToRefund, $this->alreadyRefunded());
                    }
                    break;

                case CancellationSetting::TRANSACTION_TYPE_CHARGE:
                    $this->voidAllTransactions($idsArr);
                    $this->voidAllAuths();
                    if ($cancellationSetting['amount'] > 0) {
                       $this->amountToCharge($idsArr, $this->preChargedAmount($idsArr), $cancellationSetting['amount'], $this->alreadyRefunded());
                    }
                    break;

                case CancellationSetting::NON_REFUNDABLE_BOOKING:
                    $this->voidAllAuths();
                     break;
            }

            event(new EmailEvent(config('db_const.emails.heads.booking_cancelled.type'), $this->event->bookingInfo->id ));
            /*
            * |--------------------------------------------------|
            * |  AutoRefundJobOld Will Auto Refund this OR BACharge  |
            * |  will auto Charge.                               |
            * |--------------------------------------------------|    
            */


        } catch (\Exception $e) {
            Log::error($e->getMessage() , $this->detailsArray);
        }
    }


    private function voidAllTransactions(array $idsArr)
    {
        TransactionInit::whereIn( 'id' , $idsArr)
        ->where('type' ,'C')
        ->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_PENDING, TransactionInit::PAYMENT_STATUS_REATTEMPT, TransactionInit::PAYMENT_STATUS_PAUSED])
        ->update(['lets_process'=>0, 'payment_status'=>TransactionInit::PAYMENT_STATUS_VOID, 'due_date'=> Carbon::now()->toDateTimeString()]);
    }

    private function voidAllAuths()
    {
        $auths = $this->event->bookingInfo->booking_auths();
        foreach($auths as $auth){
            $auth->status = CreditCardAuthorization::STATUS_VOID;
            $auth->due_date = Carbon::now()->toDateTimeString();
            $auth->save();
        }
    }


    private function alreadyRefunded()
    {
      return (($this->event->bookingInfo->transaction_init->where('type' ,'R')->count() ) > 0 ? true : false ); 
    }


    /**
     * @param $amountToRefund
     * @param $alreadyRefunded
     */
    private function refundAmount($amountToRefund, $alreadyRefunded)
    {
         
        $paymentTypeMeta = new PaymentTypeMeta();
        
        $tran = TransactionInit::create([
        'booking_info_id'=> $this->event->bookingInfo->id,
        'pms_id' => $this->event->bookingInfo->pms_id,
        'due_date'=> date('Y-m-d H:i:s'),
        'price'=> $amountToRefund,
        'is_modified'=>'',
        'payment_status'=> ($alreadyRefunded ?  TransactionInit::PAYMENT_STATUS_VOID : TransactionInit::PAYMENT_STATUS_PENDING),
        'user_id'=> $this->event->userAccount->users->first()->id,
        'user_account_id'=> $this->event->userAccount->id,
        'charge_ref_no'=> '',
        'lets_process'=> ($alreadyRefunded ? 0 : 1), //0 => 'No' , 1 => 'Yes' 
        'final_tick'=> 0,
        'system_remarks'=>'', ($alreadyRefunded ? 'Already Refunded Something so System can not process' : ''),
        'split'=> '',
        'against_charge_ref_no'=> '',
        'type'=> 'R',
        'status'=> 1,
        'transaction_type'=> $paymentTypeMeta->getAutoRefund(),
        'client_remarks'=> '',
        'auth_token'=> '',
        'error_code_id'=>'' ]);

        event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfo, $tran->id, config('db_const.user_preferences.preferences.ADJUSTING_ENTRIES_FOR_CANCELED_BOOKINGS')));

    }


    private function preChargedAmount(array $idsArr)
    {
        $paymentTypeMeta = new PaymentTypeMeta();
        $autoChargeTransactionTypes = array($paymentTypeMeta->getBookingPaymentAutoCollectionFull(), 
                                            $paymentTypeMeta->getBookingPaymentAutoCollectionPartial1of2(), 
                                            $paymentTypeMeta->getBookingPaymentAutoCollectionPartial2of2());
        return $this->event->bookingInfo->transaction_init->whereIn( 'id' , $idsArr)->where('payment_status' , 1)->where('type' ,'C')->whereIn( 'transaction_type' , $autoChargeTransactionTypes)->sum('price');
    }


    /**
     * @param $amountToCharge
     * @param $alreadyRefunded
     */
    private function chargeAmountEntry($amountToCharge, $alreadyRefunded)
    {
        $paymentTypeMeta = new PaymentTypeMeta();
        $tran = TransactionInit::create([
        'booking_info_id'=> $this->event->bookingInfo->id,
        'pms_id' => $this->event->bookingInfo->pms_id,
        'due_date'=> date('Y-m-d H:i:s'),
        'price'=> $amountToCharge,
        'is_modified'=>'', 
        'payment_status'=> ($alreadyRefunded ?  TransactionInit::PAYMENT_STATUS_VOID : TransactionInit::PAYMENT_STATUS_PENDING),
        'user_id'=> $this->event->userAccount->users->first()->id,
        'user_account_id'=> $this->event->userAccount->id,
        'charge_ref_no'=> '',
        'lets_process'=> ($alreadyRefunded ? 0 : 1), //0 => 'No' , 1 => 'Yes'
        'final_tick'=> 0,
        'system_remarks'=> ($alreadyRefunded ? 'Already Refunded Something so System can not process' : ''), 
        'split'=> '',
        'against_charge_ref_no'=> '',
        'type'=> 'C',
        'status'=> 1,
        'transaction_type'=> $paymentTypeMeta->getBookingCancellationAutoCollectionFull(),
        'client_remarks'=> '',
        'auth_token'=> '',
        'error_code_id'=>'' ]);

        event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfo, $tran->id, config('db_const.user_preferences.preferences.ADJUSTING_ENTRIES_FOR_CANCELED_BOOKINGS')));
    }


    /**
     * @param array $idsArr
     * @param $chargedAmount
     * @param $amountToChargeFromSettings
     * @param $alreadyRefunded
     */
    private function amountToCharge(array $idsArr, $chargedAmount, $amountToChargeFromSettings, $alreadyRefunded) {

        try{

            $amountToCharge = ( $amountToChargeFromSettings - $chargedAmount );
            $amountToRefund = ( $chargedAmount - $amountToChargeFromSettings );

            if($amountToCharge > 0){
                $this->chargeAmountEntry($amountToCharge, $alreadyRefunded);

            } else if($amountToRefund > 0){
                $this->refundAmount($amountToRefund, $alreadyRefunded);

            }

           /*
            * |--------------------------------------------------|
            * |     AutoRefundJobOld Will Auto Refud this amount    |
            * |--------------------------------------------------|
            */
        } catch (\Exception $e) {
          Log::error($e->getMessage() , $this->detailsArray);
        }

    }



    private function getSettings(){
        
        try {
            $booking_source_form_id = BookingSourceForm::select('id')
                ->where('channel_code',$this->event->bookingChannelCode)
                ->where('pms_form_id', $this->event->bookingInfo->pms_id)->first()->id;

            $options = new PaymentSettingsOptions();
            $options->property_info_id = $this->event->propertyInfo->id;
            $options->user_account_id = $this->event->userAccount->id;
            $options->booking_id = $this->event->bookingInfo->id;
            $options->booking_source_id = $booking_source_form_id;
            $options->totalAmount = $this->event->bookingInfo->total_amount;
            $options->bookingTime = $this->event->bookingInfo->booking_time;
            $options->checkInDate = $this->event->bookingInfo->check_in_date;
            $options->checkOutDate = $this->event->bookingInfo->check_out_date;

            $options->cancellationTime = now()->toDateTimeString();

            $options->timeZone = $this->event->bookingInfo->property_time_zone;
            $paymentSetting = new PaymentSettings($options);
            return ($paymentSetting->cancellationTransaction());
        } catch (\Exception $e) {
            $this->detailsArray['stack'] = $e->getTraceAsString();
            Log::error($e->getMessage(),$this->detailsArray);
        }
        return null;
    }

    private function updatePMS()
    {
        try{
            $pms = new PMS($this->event->userAccount);
            $pmsOptions = new PmsOptions();
            $bookingToUpdateData = new Booking();
            $pmsOptions->propertyID =  $this->event->propertyInfo->pms_property_id;
            $pmsOptions->propertyKey = $this->event->propertyInfo->property_key;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $bookingToUpdateData->id = $this->event->bookingInfo->pms_booking_id; /* pms_booking_id */           
            $bookingToUpdateData->bookingStatus = 'cancelled';
            $bookingToUpdateData->adjustBookingStatusForXmlTextToInteger();
            $response = $pms->update_booking($pmsOptions, $bookingToUpdateData);            
        }catch (PmsExceptions $e) {
            Log::error($e->getTraceAsString(), ['File'=> BACancelBookingsListener::class]);
            report($e);
        }catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>BACancelBookingsListener::class]);
            Log::error($e->getTraceAsString(), ['File'=>BACancelBookingsListener::class]);
        }
    }
}
