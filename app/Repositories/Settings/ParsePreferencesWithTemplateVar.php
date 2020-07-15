<?php 
/**
 * Created by PhpStorm.
 * User: Suleman Afzal
 * Date: 21-March-19
 * Time: 4:47 PM
 */
namespace App\Repositories\Settings;

use App\PaymentGatewayForm;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PMS\Models\InvoiceItem;
use App\UserPms;
use App\UserAccount;
use App\BookingInfo;
use App\PropertyInfo;
use App\TransactionInit;
use App\TransactionDetail;
use App\UserBookingSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Repositories\Settings\ClientPreferencesSettings;



class ParsePreferencesWithTemplateVar 
{
    private $userAccount;
	private $userPreference;
	private $preferenceFormId;
	private $transactionInitId;
	private $bookingInfo;
	private $propertyInfo;
	private $authorizationId;

    /**
     * ParsePreferencesWithTemplateVar constructor.
     * @param UserAccount $userAccount
     * @param BookingInfo $bookingInfo
     * @param PropertyInfo $propertyInfo
     * @param int $transactionInitId
     * @param $preferenceFormId
     * @param int $authorizationId
     */

	function __construct(UserAccount $userAccount,  BookingInfo $bookingInfo, PropertyInfo $propertyInfo, $transactionInitId = 0,  $preferenceFormId, $authorizationId = 0)
  {
    $this->userAccount = $userAccount;
    $this->bookingInfo = $bookingInfo;
    $this->propertyInfo = $propertyInfo;
    $this->preferenceFormId = $preferenceFormId;
    $this->transactionInitId = $transactionInitId;
    $this->authorizationId = $authorizationId;
  	$this->userPreference = new ClientPreferencesSettings($userAccount->id);
    
  }

    /**
     * @return Object
     */
  public function parseTemplate()
  {
    try {

         $parseVarArr = $this->getParseVar();
         $userPreference = $this->userPreference->getPreferences( $this->preferenceFormId );

          if ($userPreference){

            $userPreference->guestEmail = $parseVarArr['parseVarArr']['[guestEmail]'];
            $userPreference->guestTitle = $parseVarArr['parseVarArr']['[guestTitle]'];
            $userPreference->guestFirstName = $parseVarArr['parseVarArr']['[guestFirstName]'];
            $userPreference->guestLastName = $parseVarArr['parseVarArr']['[guestLastName]'];
            $userPreference->guestPhone = $parseVarArr['parseVarArr']['[guestPhone]'];
            $userPreference->bookingID = $this->bookingInfo->pms_booking_id;

            foreach ($parseVarArr['parseVarArr'] as $key => $value) {

              $userPreference->flag_text    = trim(str_replace($key , $value , $userPreference->flag_text));
              $userPreference->notes        = trim(str_replace($key , $value , $userPreference->notes));
              $userPreference->invoice_discription = trim(str_replace($key ,$value , $userPreference->invoice_discription));
              $userPreference->information_title   = trim(str_replace($key , $value , $userPreference->information_title));
              $userPreference->information_detail  = trim(str_replace($key , $value , $userPreference->information_detail));
            }

            if(count($parseVarArr['invoiceArr']) > 0  && count($parseVarArr['invoiceArr']['invoice']) > 0 ) {

//              $parseVarArr['invoiceArr']['invoice']['description'] = $userPreference->invoice_discription;
              $parseVarArr['invoiceArr']['infoItems']['code'] = $userPreference->information_title;
              $parseVarArr['invoiceArr']['infoItems']['text'] = $userPreference->information_detail;

                /**
                 * @var $invoice InvoiceItem
                 */
              foreach($parseVarArr['invoiceArr']['invoice'] as $invoice)
                  if($invoice->type >= 200)
                      $invoice->description = $userPreference->invoice_discription;

            }

            $userPreference->invoiceArr = $parseVarArr['invoiceArr'];

          } else{
            Log::info("Kindly Add Default Preferences, No Preferences found in custom preference or default Preferences");
          }
       
    } catch (\Exception $e) {
     Log::error($e->getMessage() , array('file'=>'ParsePreferencesWithTemplateVar'));  
    }
    return $userPreference;
  }


    /**
     * @return array
     */
  private function getParseVar()
  {
    try{

      $configValue = array(
      'PaymentSuccess' => config('db_const.user_preferences.preferences.PAYMENT_SUCCESS'),
      'PaymentFailed' => config('db_const.user_preferences.preferences.PAYMENT_FAILED'),
      'FutureCharge'  => config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_BE_CHARGED_IN_FUTURE'),
      'PaymentCollectionOnCancellation' => config('db_const.user_preferences.preferences.PAYMENT_COLLECTION_ON_CANCELLATION'),
      'CancellationAdjustmentEntry' => config('db_const.user_preferences.preferences.ADJUSTING_ENTRIES_FOR_CANCELED_BOOKINGS'),
      'SecurityAuthCaptureSuccess' => config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'),
      'SecurityAuthCaptureFailed' => config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'),
      'SecurityAuthRefundSuccess' => config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_SUCCESS'),
      'SecurityAuthRefundFailed' => config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_FAILED'));

      $SD_EntriesInTransactionInit = [$configValue['SecurityAuthCaptureSuccess'], $configValue['SecurityAuthCaptureFailed'], $configValue['SecurityAuthRefundSuccess'], $configValue['SecurityAuthRefundFailed']];

      $setParseArr = array();
      $invoiceArr =  array('invoice'=>[], 'infoItems'=>[]);

      $PaymentTypeMeta = new PaymentTypeMeta();

      $setParseArr['[guestEmail]'] = $this->bookingInfo->guest_email;
      $setParseArr['[guestTitle]'] = $this->bookingInfo->guest_title;
      $setParseArr['[guestFirstName]'] = $this->bookingInfo->guest_name;
      $setParseArr['[guestLastName]'] = $this->bookingInfo->guest_last_name;
      $setParseArr['[guestPhone]'] = $this->bookingInfo->guest_phone;
      $setParseArr['[pmsPropertyId]'] = $this->propertyInfo->pms_property_id;
      $setParseArr['[propertyName]'] = $this->propertyInfo->name;
      $setParseArr['[bookingID]'] = $this->bookingInfo->id;
      $setParseArr['[pmsBookingID]'] = $this->bookingInfo->pms_booking_id;
      $setParseArr['[bookingTotalAmount]'] = $this->bookingInfo->total_amount;
      $setParseArr['[checkInDate]'] = $this->bookingInfo->check_in_date;
      $setParseArr['[checkOutDate]'] =   $this->bookingInfo->check_out_date;
      $setParseArr['[currencySymbol]'] = $this->bookingInfo->guest_currency_code;

      $this->bookingInfo->load('transaction_init');

        /**
         * @var $transactionInit TransactionInit
         */
      $transactionInit = $this->bookingInfo->transaction_init->where('id' , $this->transactionInitId)->first();

      if (($this->authorizationId != null) && ($this->authorizationId != 0)) {

          $this->bookingInfo->load('credit_card_authorization');
          $auth = $this->bookingInfo->credit_card_authorization->where('id', $this->authorizationId)->first();

          if ($auth != null) {
              $setParseArr['[authTotalAttempts]'] = $auth->attempts;
              $setParseArr['[authAmount]'] = $auth->hold_amount;
              $setParseArr['[transactionAmount]'] = $auth->hold_amount;

              $authDetail =  $auth->authorization_details->last();

              if ($authDetail != null) {
                  $paymentGateway = PaymentGatewayForm::find($authDetail->payment_gateway_form_id);
                  $setParseArr['[paymentProcessorName]'] = (($paymentGateway != null) ? $paymentGateway->name : '');
                  $setParseArr['[authDatetime]'] = \Carbon\Carbon::parse($authDetail->created_at, 'GMT')->setTimezone($this->bookingInfo->property_time_zone)->format('D d M Y H:i');
                  $setParseArr['[GatewayReferenceNo]'] = $authDetail->charge_ref_no;
                  $setParseArr['[GatewayOrderId]'] = $authDetail->order_id;
                  $setParseArr['[transactionID]'] = $setParseArr['[GatewayReferenceNo]'];
                  $setParseArr['[authFailedReason]'] = $authDetail->error_msg;
                  $setParseArr['[transactionType]'] = $PaymentTypeMeta->getTransactionTypeNameForUser($auth->type);
                  $setParseArr['[chargedAtDateTime]'] = $setParseArr['[authDatetime]'];
                  $setParseArr['[chargeOn]'] = $setParseArr['[authDatetime]'];
                  $setParseArr['[SDAuthRefundDatetime]'] = $setParseArr['[authDatetime]'];
                  $setParseArr['[authType]'] = $setParseArr['[transactionType]'];
                  $creditCardInfo = $authDetail->ccinfo;
              }
          }
      }

      if ($transactionInit != null) {

          $PaymentTypeMeta = new PaymentTypeMeta();
          $setParseArr['[chargeOn]'] = Carbon::parse($transactionInit->due_date, 'GMT')->setTimezone($this->bookingInfo->property_time_zone)->format('D d M Y H:i');
          $setParseArr['[transactionID]'] = $transactionInit->id;
          $setParseArr['[transactionAmount]'] = $transactionInit->price;
          $setParseArr['[transactionType]'] = $PaymentTypeMeta->getTransactionTypeNameForUser($transactionInit->transaction_type);
          $setParseArr['[paymentSplitType]'] = $setParseArr['[transactionType]'];

          if (in_array($this->preferenceFormId, $SD_EntriesInTransactionInit)) {
              $setParseArr['[authTotalAttempts]'] = $transactionInit->attempt;
              $setParseArr['[authAmount]'] = $setParseArr['[transactionAmount]'];
              $authTrans = true;
          }

          $transactionInit->load('transactions_detail');
          /**
           * @var $transactionDetails TransactionDetail
           */
          $transactionDetails = $transactionInit->transactions_detail->last();

          if ($this->preferenceFormId == $configValue['PaymentSuccess']) {

              if($transactionInit->transaction_type == $PaymentTypeMeta->getBookingPaymentManualAdditionalCharge()) {

                  $invoice1 = new InvoiceItem();
                  $invoice1->description = empty($transactionDetails->client_remarks) ? 'Additional Charges.' : $transactionDetails->client_remarks;
                  $invoice1->status = "1";
                  $invoice1->price = $setParseArr['[transactionAmount]'];
                  $invoice1->quantity = "1";
                  $invoice1->type = "199";

                  $invoice2 = new InvoiceItem();
                  $invoice2->description = $setParseArr['[paymentSplitType]'];
                  $invoice2->status = 1;
                  $invoice2->price = $setParseArr['[transactionAmount]'];
                  $invoice2->quantity = -1;
                  $invoice2->type = 200;

                  $invoiceArr['invoice'] = [$invoice1, $invoice2];

              } else {

                  $invoice3 = new InvoiceItem();
                  $invoice3->description = $setParseArr['[paymentSplitType]'];
                  $invoice3->status = 1;
                  $invoice3->price = $setParseArr['[transactionAmount]'];
                  $invoice3->quantity = -1;
                  $invoice3->type = 200;

                  $invoiceArr['invoice'] = [$invoice3];
                  $invoiceArr['infoItems'] = array("code" => '', "text" => '');
              }


          }

        /* $paymentStatus = ($this->preferenceFormId != $configValue['PaymentFailed'] ? TransactionInit::PAYMENT_STATUS_SUCCESS : TransactionInit::PAYMENT_STATUS_FAIL); */


        if (!is_null($transactionDetails)) {

            $paymentGateway = PaymentGatewayForm::find($transactionDetails->payment_gateway_form_id);

            $setParseArr['[chargedAtDateTime]'] = Carbon::parse($transactionDetails->created_at, 'GMT')->setTimezone($this->bookingInfo->property_time_zone)->format('D d M Y H:i');
            $setParseArr['[paymentProcessorName]'] = (($paymentGateway != null) ? $paymentGateway->name : '');
            $setParseArr['[GatewayReferenceNo]'] = $transactionDetails->charge_ref_no;
            $setParseArr['[chargeDescription]'] = $transactionDetails->client_remarks ?:'';
            $setParseArr['[transactionID]'] = $setParseArr['[GatewayReferenceNo]'];
            $setParseArr['[GatewayOrderId]'] = $transactionDetails->order_id;
            $setParseArr['[paymentFailedReason]'] = $transactionDetails->error_msg;

            if ((isset($authTrans)) && ($authTrans === true)) {
                $setParseArr['[authDatetime]'] = $setParseArr['[chargedAtDateTime]'];
                $setParseArr['[SDAuthRefundDatetime]'] = $setParseArr['[chargedAtDateTime]'];
                $setParseArr['[authFailedReason]'] = $setParseArr['[paymentFailedReason]'];
                $setParseArr['[authType]'] =$setParseArr['[transactionType]'];
            }

            $creditCardInfo = $transactionDetails->ccinfo;
        }

        if (isset($creditCardInfo) && ($creditCardInfo != null)) {
            $setParseArr['[cardNumber]'] = 'xxxx-xxxx-xxxx-' . $creditCardInfo->cc_last_4_digit;
            $setParseArr['[cardExpiry]'] = $creditCardInfo->cc_exp_month . '/' . $creditCardInfo->cc_exp_year;
        }
      }
    }catch(\Exception $e){
        Log::error($e->getTraceAsString(), array('file' => 'ParsePreferencesWithTemplateVar' , 'detail' => json_encode($setParseArr)));
        Log::error($e->getMessage(), array('file' => 'ParsePreferencesWithTemplateVar' , 'detail' => json_encode($setParseArr)));
    }
   return  array('invoiceArr' => $invoiceArr, 'parseVarArr' => $setParseArr);
  }
}
