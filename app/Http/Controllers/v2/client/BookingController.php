<?php

namespace App\Http\Controllers\v2\client;

use App\CaCapability;
use App\Events\Emails\EmailEvent;
use App\Exceptions\UpdateCardException;
use App\GuestImageDetail;
use App\Http\Controllers\v2\Guest\GuestController;
use App\Jobs\EmailJobs\EmailJob;
use App\Repositories\Upsells\UpsellRepository;
use App\Http\Resources\General\BookingDetail\GuestDocumentCollection;
use App\Services\CapabilityService;
use App\Services\UpdateCard;
use App\Unit;
use Exception;
use App\GuestData;
use Carbon\Carbon;
use App\GuestImage;
use App\BookingInfo;
use App\UserAccount;
use Illuminate\Support\Facades\Auth;
use NumberFormatter;
use App\PropertyInfo;
use App\RefundDetail;
use App\System\PMS\PMS;
use App\CreditCardInfo;
use App\TransactionInit;
use App\Mail\GenericEmail;
use App\TransactionDetail;
use App\GuestCommunication;
use App\PaymentGatewayForm;
use App\UserPaymentGateway;
use App\UserSettingsBridge;
use Illuminate\Http\Request;
use App\AuthorizationDetails;
use App\Events\RefundEmailEvent;
use App\CreditCardAuthorization;
use App\System\PMS\Models\Booking;
use App\Events\PMSPreferencesEvent;
use App\Exceptions\RefundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\System\PMS\Models\PmsOptions;
use App\Repositories\Bookings\BaRefund;
use App\Repositories\Bookings\Bookings;
use App\Repositories\NotificationAlerts;
use Illuminate\Support\Facades\Validator;
use App\System\PaymentGateway\Models\Card;
use App\Exceptions\ClientSettingsException;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Repositories\Bookings\BookingRepository;
use App\Http\Resources\BA\Booking\BookingListCollection;
use App\System\PaymentGateway\Models\Transaction;
use App\Repositories\BookingSources\BookingSources;
use App\Repositories\Settings\CreditCardValidation;
use App\Http\Resources\ClientBookingDetailsResource;
use App\Http\Resources\BA\Booking\BookingListDetailResource;
use App\Repositories\Bookings\BookingRepositoryInterface;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\Repositories\TransactionInit\TransactionInitRepository;
use App\Repositories\PaymentGateways\PaymentGateways as PaymentGatewayRepo;
use App\Entities\Card as CardObject;


class BookingController extends Controller
{
    use UpdateCard;

    /**
     * @var BookingRepository $booking
     */
    public $booking;
    public $upsell;
    public $user_account_id;

     public function __construct(BookingRepositoryInterface $bookingRepository, UpsellRepository $upsellRepository)
    {
        $this->middleware('auth', ['except' => ['cancelBdcBooking', 'cancelBdcBookingDetailPage', 'fetchGuestCc', 'updateCardNow']]);
        $this->booking = $bookingRepository;
        $this->upsell = $upsellRepository;
        //$this->user_account = Auth::user()->user_account;
    }

    /**
     *  PMS WISE
     * @param Request $request
     * @return BookingListCollection|\Illuminate\Http\JsonResponse
     */
    public function getBookingList(Request $request)
    {
        $this->isPermissioned('bookings');
        try {
            $raw_bookings = $this->booking->get_bookings_list_filtered($request->filter);
            if(!empty($raw_bookings)){
                return new BookingListCollection($raw_bookings);
            }
        } catch (Exception $e) {
            log_exception_by_exception_object($e, json_encode(['Class'=> __CLASS__, 'method'=> __FUNCTION__]), 'error');
        }

        return $this->apiErrorResponse('Something went wrong!');
    }

    /**
     * *  PMS WISE
     * @param $booking_info_id
     * @return BookingListDetailResource
     * @throws Exception
     */
    public function getBookingDetail($booking_info_id)
    {
        /**
         *@var BookingInfo $raw_booking
         */
        $this->isPermissioned('bookings');

        $filter = [
            'columns' => ["*"],
            'constraints' => [
                ['id',  $booking_info_id]
            ],
            'relations' => [
                "transaction_init_charged",
                "credit_card_authorization_sd_cc",
                "credit_card_authorization_sd_cc.ccinfo",
                "guest_images",
                "cc_Infos",
            ]
        ];

        $raw_booking = $this->booking->get_booking_detail($booking_info_id);
        $raw_booking->booking_list = $this->booking->get_bookings_list_filtered($filter);
        return new BookingListDetailResource($raw_booking);
    }

    public function index()
    {
        $this->isPermissioned('bookings');  //Having Permission to perform this act
        //return view('client.bookings.bookings');
        return view('v2.client.bookings.booking-list');
    }
    

    public function effectedBookings($property_info_id)
    {
        $this->isPermissioned('bookings');  //Having Permission to perform this act
        return view('client.bookings.effectedBookings', compact('property_info_id'));
    }

    /**
     * @param $property_info_id
     * @return mixed
     */
    public function effectedBookings_list($property_info_id)
    {

        $this->isPermissioned('bookings'); //Having Permission to perform this act
        $user_account_id = auth()->user()->user_account_id;
        $data = app()->make('Bookings', ['user_account_id' => $user_account_id]);
        return  $data->effectedBooking_data($property_info_id);
    }


    public function bookings_short_desc(Request $request)
    {
        $this->isPermissioned('bookings'); //Having Permission to perform this act

        $id = $request->id;
        $user_account_id = auth()->user()->user_account_id;
        $data = app()->make('Bookings', ['user_account_id' => $user_account_id]);

        return  $data->bookings_short_description($id);
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function details($id){

        $this->isPermissioned('bookings'); //Having Permission to perform this act
        $user_account = auth()->user()->user_account;
        $user_account_id = $user_account->id;
        $bookingInfo = $user_account->bookings_info->where('id',$id)->first();
        if(is_null($bookingInfo)){
            abort(403, "Unauthorized");
        }
        $propertyInfo = $user_account->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();
        $trans = TransactionInit::with('transactions_detail','refund_detail')->where('booking_info_id', $bookingInfo->id)->get();
        $collection = collect(['bookingInfo'=> $bookingInfo, 'transactionInit'=>$trans]);
        $data = [];
        $data['bookingInfo'] = $bookingInfo;
        $data['bookingDetails'] = json_encode(new ClientBookingDetailsResource($collection));
        /*  Manual Terminal URI Links  */
        $data['charge-more-uri'] = URL::signedRoute('client-charge-more');//
        $data['charge-for-damages-uri'] = URL::signedRoute('damagecharges');//
        $data['pay-now-uri'] = URL::signedRoute('pay-now-uri');//
        $data['update-card-by-client'] = URL::signedRoute('update-card-by-client');//
        /*
         * Get Property Currency
         * */
        $b = new Bookings($user_account_id);
        $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);
        $timezone = $b->getPropertyTimezone($user_account, $propertyInfo);
        $symbol = ((($currency_code != null ) && ($currency_code != '' )) ? $b->getCurrencySymbolByCurrencyCode($currency_code) : '');
        $totalPaidAmount = $bookingInfo->transaction_init->whereIn('type', ['C', 'M'])->where('payment_status', '1')->sum('price');
        $totalPaidAmountSDD = $bookingInfo->transaction_init->whereIn('type', ['S', 'CS'])->where('payment_status', '1')->sum('price');
        $refunded = $bookingInfo->transaction_init->where('type', 'R')->where('payment_status', '1')->sum('price');
        $refundedSDD = $bookingInfo->transaction_init->whereIn('type', ['SR'])->where('payment_status', '1')->sum('price');
        $guestCustomData= GuestData::where('booking_id',$id)->first();
        $guestImages = GuestImage::where('booking_id' , $bookingInfo->id)->get();

        return view('client.bookings.booking_details', [
            'id'=>$id,
            'data' => $data,
            'propertyInfo' => $propertyInfo,
            'symbol' => $symbol,
            'currency_code' => $currency_code,
            'guestImages' => $guestImages,
            'totalPaidAmount'=>$totalPaidAmount,
            'refunded'=> $refunded,
            'totalPaidAmountSDD'=> $totalPaidAmountSDD,
            'refundedSDD'=> $refundedSDD,
            'guestCustomData'=> $guestCustomData,
            'timezone'=> $timezone,
            'PaymentGatewaysArr' => [
                'allPG' => PaymentGatewayForm::pluck('name', 'id')->toArray(),
                'userPG' => $user_account->user_payment_gateways->pluck('payment_gateway_form_id', 'id')->toArray()
            ],
        ]);

    }

    public function paynowSecurityDamageDeposit(Request $request) {

//        if (!$request->hasValidSignature()){
//
//            return response()->json('invalid authentication', 401);
//        }
        
        $this->isPermissioned('bookings');
        $user_account  = auth()->user()->user_account;
        $user_account_id = $user_account->id;

        $cAuth = CreditCardAuthorization::findOrFail($request->cc_auth_id);
        $paymentType = new PaymentTypeMeta();
        $sDAA = $paymentType->getSecurityDepositAutoAuthorize();
        $sDMA = $paymentType->getSecurityDepositManualAuthorize();

        $currency_code = '';
        $propertyInfo = null;


        try{

            $gwTransaction = $cAuth->transaction_obj;

            $shouldAutoReAuth = $cAuth->is_auto_re_auth == 1;
            $isAuthCancelable = $gwTransaction != null && $gwTransaction->token != null && $gwTransaction->token != '' && $cAuth->token != null && $shouldAutoReAuth;
            
            $bookingInfo = $cAuth->ccinfo->booking_info;

            $pg = new PaymentGateway();
            $propertyInfo = $bookingInfo->property_info;

            $upg = new PaymentGatewayRepo();
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

            $ccInfo_latest = CreditCardInfo::where('booking_info_id', $bookingInfo->id)->latest('id')->limit(1)->first();

            //check if card not available
            if(!$ccInfo_latest || $ccInfo_latest->is_vc == 1 )
            {
                return $this->errorResponse('Guest credit card not available', 422);
            }
            elseif($ccInfo_latest->customer_object->token == '' || in_array($ccInfo_latest->status, [3, 4]))
            {
                return $this->errorResponse("Can't charge! Guest credit card is invalid", 422);
            }
        
            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);

            if($isAuthCancelable) {
                $cancellationResponse = $pg->cancelAuthorization($gwTransaction, $userPaymentGateway);
                $this->insertAuthLog($cAuth, $cancellationResponse, $userPaymentGateway, 'Canceled Authorization, for ReAuthorization.');
            }

            $card = new Card();
            $card->amount = $cAuth->hold_amount;
            $card->currency = $currency_code;
            $card->order_id = round(microtime(true) * 1000);

            PaymentGatewayRepo::addMetadataInformation($bookingInfo, $card, BookingController::class);

            /**
             * @var $reAuthResponse Transaction
             */
            $reAuthResponse = $pg->authorizeWithCustomer($ccInfo_latest->customer_object, $card, $userPaymentGateway);

            $numAttempts = $cAuth->attempts;
            $cAuth->attempts = $numAttempts + 1;
            $message = '';

            if($reAuthResponse->status) {

                $cAuth->token = $reAuthResponse->token;
                $cAuth->transaction_obj = json_encode($reAuthResponse);

                if ($shouldAutoReAuth) {
                    $dueDate = $cAuth->next_due_date;
                    $dateFinder = new Carbon($dueDate);
                    $nextDueDate = $dateFinder->addDays(CreditCardValidation::$autoReauthorizeDays);
                    $cAuth->next_due_date = $nextDueDate;

                } else {
                    $cAuth->next_due_date = null;
                }

                $cAuth->status = CreditCardAuthorization::STATUS_ATTEMPTED;
                $message = 'Successfully Authorized';

                $AuthorizedSuccessPMSPreferences = true;

            } elseif($cAuth->attempts == CreditCardAuthorization::TOTAL_ATTEMPTS) {
                $cAuth->status = CreditCardAuthorization::STATUS_FAILED; // Fail
                $cAuth->next_due_date = null;
                $message = 'Failed after reattempts';
                $AuthorizedSuccessPMSPreferences = false;
            } else {

                $cAuth->next_due_date = (new Carbon($cAuth->next_due_date))->addHours($numAttempts + 1)->toDateTimeString();
                $message = 'Will be reattempted at ' . $cAuth->next_due_date;
                $AuthorizedSuccessPMSPreferences = false;
            }

            $cAuth->save();
            $this->insertAuthLog($cAuth, $reAuthResponse, $userPaymentGateway, $message);

            /**
             * Update Preferences PMS
             */
            if ( isset($AuthorizedSuccessPMSPreferences) && $AuthorizedSuccessPMSPreferences ){

                if (($cAuth->type == $sDAA) || ($cAuth->type == $sDMA)){
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'), $cAuth->id));
                } else {
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'), $cAuth->id));
                }
            } else if ( isset($AuthorizedSuccessPMSPreferences) && !$AuthorizedSuccessPMSPreferences ){
                if (($cAuth->type == $sDAA) || ($cAuth->type == $sDMA)){
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'), $cAuth->id));
                } else {
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'), $cAuth->id));
                }
            }

            return $this->successResponse($message, 200);

        } catch (GatewayException $e) {
            report($e);

            if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {
                $resp = new Transaction();
                $resp->amount = $cAuth->hold_amount;
                $resp->currency_code = $currency_code;
                event(new EmailEvent(config('db_const.emails.heads.sd_3ds_required.type'),$cAuth->id ));
                return $this->errorResponse("This card is protected with 3DS, Email has been sent to guest to Authorize and pay this transaction.", 422);
            }

            return $this->errorResponse(setCardExceptionReadable($e->getDescription()), 422);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    private function insertAuthLog(CreditCardAuthorization $ccAuth, Transaction $transaction, UserPaymentGateway $userPaymentGateway, string $message) {
        try {
            $ccAuthDetail = new AuthorizationDetails();
            $ccAuthDetail->cc_auth_id = $ccAuth->id;
            $ccAuthDetail->user_account_id = $ccAuth->user_account_id;
            $ccAuthDetail->payment_processor_response = json_encode($transaction);
            $ccAuthDetail->payment_gateway_name = (new GateWay($userPaymentGateway->gateway))->name;
            $ccAuthDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
            $ccAuthDetail->payment_status = $transaction->status;
            $ccAuthDetail->amount = $ccAuth->hold_amount;
            $ccAuthDetail->charge_ref_no = $transaction->token;
            $ccAuthDetail->order_id = $transaction->order_id;
            $ccAuthDetail->client_remarks = $message;
            $ccAuthDetail->error_msg = ($transaction->exceptionMessage != '' ? $transaction->exceptionMessage : $transaction->message);
            $ccAuthDetail->save();
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    
    public function guestImages($id)
    {
        $this->isPermissioned('bookings');

        $guestImages = GuestImage::where('booking_id' , $id)->get();

        return response()->json(['data'=>$guestImages]);
    }

    public function fetchGuestCc(Request $request)
    {

        if (!$request->hasValidSignature()) {
            $this->isPermissioned('bookings');
        }

        try {

            $booking_id = $request->booking_id;

            if(!empty($booking_id) && is_numeric($booking_id) && $booking_id>0) {
                
                $credit_card_details = [];
                $credit_card_details['card_available'] = false;

                //check if guest credit card available
                $booking_info = BookingInfo::find($booking_id);
                $guest_credit_card = $booking_info->cc_Infos->last();
                
                if($guest_credit_card) {
                    
                    if(($guest_credit_card->cc_last_4_digit != '') 
                            && ($guest_credit_card->card_name != '') 
                            && ($guest_credit_card->is_vc == 0)) {

                        $credit_card_details['card_available'] = true;
                        $credit_card_details['card_name'] = $guest_credit_card->card_name;
                        $credit_card_details['last_4_digits'] = $guest_credit_card->cc_last_4_digit;
                        $credit_card_details['expiry_month'] = $guest_credit_card->cc_exp_month;
                        $credit_card_details['expiry_year'] = $guest_credit_card->cc_exp_year;
                    }

                    if($guest_credit_card->customer_object->token == '' && in_array($guest_credit_card->status, [3, 4])) {
                        $credit_card_details['invalid'] = true;
                    }
                    
                } else {
                    $credit_card_details['invalid'] = true;
                }

                $upg = new PaymentGatewayRepo();
                $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($booking_info);
                
                $paymentGateway = new PaymentGateway();
                $terminalData = $paymentGateway->getTerminal($userPaymentGateway);
                
                $credit_card_details['pgTerminal'] = $terminalData;
                $credit_card_details['pgTerminal']['first_name'] = $booking_info->guest_name;
                $credit_card_details['pgTerminal']['last_name'] = $booking_info->guest_last_name;
                $credit_card_details['pgTerminal']['booking_id'] = $booking_id;
                $credit_card_details['pgTerminal']['with3DsAuthentication'] = true;
                $credit_card_details['pgTerminal']['show_authentication_button'] = str_contains($request->server('HTTP_REFERER'), 'pre-checkin-step') ? false : true;
                
                return response()->json($credit_card_details);
            }
        }
        catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function StatusUpdate(Request $request, $classified = false)
    {
        $this->isPermissioned('bookings');

        $value = $request->value;
        $booking_id = $request->booking_id;
        $id = $request->id;
        $description = !empty($request->description)? $request->description : '';
        $booking_info = BookingInfo::where('id', $booking_id)->where('user_account_id', \auth()->user()->user_account_id)->first();

        if (empty($booking_info) || empty($guest_image = $booking_info->guest_images->where('id' , $id)->first()))
            return $this->errorResponse('Invalid Record!', 422);

        // return response()->json(['success'=>$value]);
        try{

            $guest_image->update(['status'=>$value, 'description'=> $description]);

            if($value == GuestImage::STATUS_REJECTED){
                event(new EmailEvent(config('db_const.emails.heads.guest_document_rejected.type'), $id));
            }

            $documents =  GuestImage::where('booking_id' , $booking_id)->get();

            if($classified){
                return response()->json($documents);
            }

            GuestDocumentCollection::withoutWrapping();
            return new GuestDocumentCollection($documents);

        }catch (\Exception $e){
            Log::error($e->getMessage(), ['File: ' => __FILE__, 'Function: '=> __FUNCTION__]);
            return $this->errorResponse('Something went wrong during update!', 422);
        }
    }

    public function communication(Request $request){

        try {
            $msg = trim($request->get('msgtext'));
            if ($msg == null)
                $this->errorResponse(trans('messages.client.chat.error.empty_message'),500);

            $u_id = auth()->user()->id;
            $uac_id = auth()->user()->user_account_id;
            $bookingInfo = BookingInfo::where('id', $request->get('bookingInfoId'))->where('user_account_id', $uac_id)->first();
            if (empty($bookingInfo))
                return $this->errorResponse(trans('messages.client.chat.error.failed'),402);

            if ($bookingInfo->pms_booking_status == 0) //Cancelled
                return $this->errorResponse(trans('messages.client.chat.error.booking_cancelled'), 500);
            if ($bookingInfo->check_out_date <= now()->toDateTimeString())
                return $this->errorResponse(trans('messages.client.chat.error.checkout_date_passed'),500);

            //use common repo to create alert
            $notificationRepo = new NotificationAlerts($u_id, $uac_id);
            $chat = $notificationRepo->create($request->get('bookingInfoId'), $request->get('is_guest'),
                'chat', $request->get('pms_booking_id'), 0, $msg);
            if ($chat) {
                //inform guest
                event(new EmailEvent(config('db_const.emails.heads.new_chat_message.type'), $chat->id ));
            }

            return $this->apiSuccessResponse(200,[trans('messages.client.chat.success.sent')], 'success');
        } catch (Exception $exception) {
           return $this->errorResponse(trans('messages.client.chat.error.failed'),500);
        }

    }

    public function allmsgs(Request $request) {

        $bookingInfoId      = $request->bookingInfoId;
        $booking = BookingInfo::where('id', $bookingInfoId)
                ->where('user_account_id', auth()->user()->user_account_id)
            //->with('room_info')
            ->first();

        if(!empty($booking)) {
            $booking->room_info;
            if (isset($request->lastSeenMessageId) && ($request->lastSeenMessageId != 0))
                GuestCommunication::where(
                    [
                        ['booking_info_id', $bookingInfoId],
                        ['user_account_id', auth()->user()->user_account_id],
                        ['id','<', $request->lastSeenMessageId+1],
                        ['alert_type', 'chat'],
                    ]
                )->update(['message_read_by_user' => 1]);

                $user_account       = auth()->user()->user_account;
                $messages           = $user_account->messages->where('booking_info_id', $bookingInfoId)->where('alert_type', 'chat');
                $unseenMessages     = $messages->where('message_read_by_user', 0)->where('is_guest', 1);
                $lastUnSeenMessage  = $unseenMessages->first();
                $unit = Unit::where([
                    'pms_room_id'=> $booking->room_id,
                    'property_info_id'=> $booking->property_info->id,
                    'unit_no'=> $booking->unit_id
                ])->first();
                $booking->unit = $unit;
                return $this->apiSuccessResponse(200,
                    [
                        'messages' => $messages->sortByDesc('created_at')->toArray(),
                        'unSeenMessagesCount' => $unseenMessages->count(),
                        'lastUnSeenMessageId' => (!is_null($lastUnSeenMessage) ? $lastUnSeenMessage->id : 0),
                        'booking'=> $booking,
                    ],
                    'success'
                );
        }
        else{
            return $this->apiSuccessResponse(500,
                [
                    'messages' => [],
                    'unSeenMessagesCount' => [],
                    'lastUnSeenMessageId' => 0,
                    'booking'=> [],
                ],
                'error'
            );
        }

    }

    public function chargeNowBooking(Request $request) {

        $this->isPermissioned('bookings');

//        if (!$request->hasValidSignature())
//        {
//            return response()->json('invalid authentication', 401);
//        }

        try{

            $user_account_id = auth()->user()->user_account_id;
            $transaction_init = TransactionInit::with('booking_info', 'user_account')
                ->where('id', $request->transaction_init_id)
                ->where('user_account_id', $user_account_id)->first();
            if(is_null($transaction_init)) {
                return $this->errorResponse('Transaction not found.', 422);
            } elseif ($transaction_init->payment_status == TransactionInit::PAYMENT_STATUS_SUCCESS) {
                return $this->errorResponse("Transaction # $transaction_init->id  Already Charged.", 422);
            }elseif ($transaction_init->in_processing != TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS) {
                return $this->errorResponse("Please Wait Transaction # $transaction_init->id  Already in Processing.", 422);
            }

            $user_account = $transaction_init->user_account;
            $booking_info = $transaction_init->booking_info; //BookingInfo::where('id', $transaction_init->booking_info_id)->first();
            $isVC = ($booking_info->is_vc == 'VC');
            $isAutoChargeTransactionType = in_array($transaction_init->transaction_type, [1,2,3]);

            $queryConstraints = [
                ['booking_info_id', $booking_info->id],
                ['is_vc', ($isVC && $isAutoChargeTransactionType) ? 1 : 0]
            ];

            $cc_info = resolve(CreditCardInfo::class)->where($queryConstraints)->latest('id')->limit(1)->first();
            $property_info = PropertyInfo::where('pms_property_id', $booking_info->property_id)
                ->where('user_account_id', $user_account_id)->first();

            if (($isVC && (!$isAutoChargeTransactionType)) || (!$isVC)) { //If Booking is VC and Transaction is Auto
                if (!$this->booking::isCCInfoValidAndCustomerObjectCreated($cc_info))
                    return $this->errorResponse(BookingInfo::CREDIT_CARD_NOT_VALID_MESSAGE, 422);
            } elseif ($isVC && $isAutoChargeTransactionType) { //If Booking is VC and Transaction is Manual
                if (!$this->booking::isCCInfoValidAndCustomerObjectCreated($cc_info))
                    return $this->errorResponse('Virtual Card will be available to charge on '.
                        Carbon::parse($cc_info->due_date,'GMT')->setTimezone($property_info->time_zone)
                            ->format('M d, Y'), 422);
            }

            $repo_bookings = new Bookings($user_account_id);
            $booking_from_pms = $repo_bookings->fetch_Booking_Details($property_info, $user_account, $booking_info->pms_booking_id);
            if($booking_from_pms != null && is_array($booking_from_pms) && count($booking_from_pms) > 0) {
                /**
                 * @var Booking $bookingFromPmsObject
                 */
                $bookingFromPmsObject = $booking_from_pms[0];
                if (!$bookingFromPmsObject->isValidToChargeByCheckingBalanceOnPMS($transaction_init->price)['status'])
                    return $this->errorResponse(trans('messages.client.booking.balance_amount_less_than_charge'), 422);
            }

            $transaction_init->update(['in_processing' => TransactionInit::TRANSACTION_ADDED_IN_MANUAL_PROCESSING]);
            $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($property_info);

            $currency_code = $repo_bookings->getCurrencyCode($booking_info, $property_info);

            $card = new Card();
            $card->firstName = $cc_info->customer_object->first_name;
            $card->lastName = $cc_info->customer_object->last_name;
            $card->token = $cc_info->customer_object->token;
            $card->amount = $transaction_init->price;
            $card->currency = $currency_code;
            $card->order_id = round(microtime(true) * 1000);
            $card->cc_last_4_digit = $cc_info->cc_last_4_digit;

            PaymentGatewayRepo::addMetadataInformation($booking_info, $card, BookingController::class);

            try{
                $pg = new PaymentGateway();
                $resp = $pg->chargeWithCustomer($cc_info->customer_object, $card, $userPaymentGateway);

                if($resp->status){
                    $this->updateTransaction($transaction_init, $resp, $userPaymentGateway, $card, $cc_info->id, true);
                    
                    $msg = 'Payment successfully charged.';
                    //Preferences
                    $preference_form_id = config('db_const.user_preferences.preferences.PAYMENT_SUCCESS');
                    event(new PMSPreferencesEvent($user_account, $booking_info, $transaction_init->id, $preference_form_id));

                    return $this->successResponse($msg, 200);
                    
                }else{
                    $this->updateTransaction($transaction_init, $resp, $userPaymentGateway, $card, $cc_info->id, false);
                    //Preferences
                    $preference_form_id = config('db_const.user_preferences.preferences.PAYMENT_FAILED');
                    event(new PMSPreferencesEvent($user_account, $booking_info, $transaction_init->id, $preference_form_id));

                    $msg = 'Payment status failed. Try with another Card.';
                    return $this->errorResponse($msg, 422);
                }

            }catch(GatewayException $e){
                $transaction_init->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);

                if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {
                    $resp = new Transaction();
                    $resp->amount = $card->amount;
                    $resp->currency_code = $currency_code;

                    //inform guest for 3DS charge authentication
                    event(new EmailEvent(config('db_const.emails.heads.charge_3ds_required.type'),$transaction_init->id ));

                    return $this->errorResponse("This card is protected with 3DS, Email has been sent to guest to Authorize and pay this transaction.", 422);
                }

                return $this->errorResponse(setCardExceptionReadable($e->getDescription()), 422);
            }

        }catch(Exception $e){
            $transaction_init->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * @param TransactionInit $oldTran
     * @param Transaction $resp
     * @param UserPaymentGateway $userPaymentGateway
     * @param Card $card
     * @param $ccInfoId
     * @param bool $attemptedSuccessfully
     */

    private function updateTransaction(TransactionInit $oldTran, Transaction $resp, UserPaymentGateway $userPaymentGateway, Card $card, $ccInfoId , bool $attemptedSuccessfully)
    {
        //$client_remarks='Manual Pay now booking amount';
        $client_remarks = 'Payment of '.get_currency_symbol($card->currency).$card->amount.' on card ending with**'.$card->cc_last_4_digit;
        if ($attemptedSuccessfully) {
            /*** Transaction Init Entry Update on success*/
            $oldTran->charge_ref_no = $resp->token;
            $oldTran->last_success_trans_obj = $resp;
            $oldTran->lets_process = 0;
            $oldTran->payment_status = $resp->status;
            $oldTran->client_remarks = $client_remarks;
        }
        $oldTran->in_processing  = TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS;
        $oldTran->save();

        $transactionDetail = new TransactionDetail();
        $transactionDetail->transaction_init_id = $oldTran->id;
        $transactionDetail->cc_info_id = $ccInfoId;
        $transactionDetail->user_id = $oldTran->user_id;
        $transactionDetail->user_account_id = $oldTran->user_account_id;
        $transactionDetail->name = $oldTran->booking_info->guest_name;
        $transactionDetail->payment_processor_response = json_encode($resp);
        $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
        $transactionDetail->payment_status = $resp->status;
        $transactionDetail->charge_ref_no = ($attemptedSuccessfully ? $resp->token : '');
        $transactionDetail->client_remarks = $client_remarks;
        $transactionDetail->order_id = $card->order_id;
        $transactionDetail->error_msg = ($resp->exceptionMessage != '' ? $resp->exceptionMessage : $resp->message); 
        $transactionDetail->save();

    }



    public function chargeMoreBooking(Request $request){

        $this->isPermissioned('bookings');

//        if (!$request->hasValidSignature()){
//
//            return response()->json('invalid authentication', 401);
//        }
        if(empty($request->data['booking_info_id'])){
            return $this->errorResponse('Booking No. is missing!', 422);
        }
        if(empty($request->data['amount'])){
            return $this->errorResponse('Transaction amount is missing!', 422);
        }
        if(empty($request->data['description'])){
            return $this->errorResponse('Description is missing', 422);
        }

        try{

        $bookingInfo = BookingInfo::with('user_account')->where('id', $request->data['booking_info_id'])
            ->where('user_account_id', auth()->user()->user_account_id)->first();

        $capabilities = CapabilityService::allCapabilities($bookingInfo);

        if (!$capabilities[CaCapability::AUTO_PAYMENTS] && !$capabilities[CaCapability::MANUAL_PAYMENTS])
            return $this->errorResponse('Booking Source not Supported', 422);

        $useraccount = $bookingInfo->user_account;
        $propertyInfo = $bookingInfo->property_info;

        /*
         * Note Change made to CreditCardInfo query, to get last card added by client/guest.
         * This change was made after deciding that we will store multiple cards.
         */

            $ccInfo = resolve(CreditCardInfo::class)
                ->where([['booking_info_id', $bookingInfo->id], ['is_vc', 0]])->latest('id')->limit(1)->first();

            if (!$this->booking::isCCInfoValidAndCustomerObjectCreated($ccInfo))
                return $this->errorResponse(BookingInfo::CREDIT_CARD_NOT_VALID_MESSAGE, 422);

        $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
        $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

        $b = new Bookings($bookingInfo->user_account_id);
        $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);

        $card = new Card();
        $card->firstName = $ccInfo->customer_object->first_name;
        $card->lastName = $ccInfo->customer_object->last_name;
        $card->token = $ccInfo->customer_object->token;
        $card->amount = abs($request->data['amount']);
        $card->currency = $currency_code;
        $card->general_description = $request->data['description'];
        $card->order_id = round(microtime(true) * 1000);

        // $firstName = '';
        // $lastName = '';

        // if($request->data['full_name'] != null && $request->data['full_name'] != '') {
        //     $split = explode(' ', $request->data['full_name']);
        //     if(count($split) > 1)
        //         $firstName =  $split[0];
        // }

        // if($request->data['full_name'] != null && $request->data['full_name'] != '') {
        //     $split = explode(' ', $request->data['full_name']);
        //     if(count($split) > 1) {
        //         $last = '';
        //         for($i = 1; $i < count($split); $i++)
        //             $last .= ' ' . $split[$i];
        //         $lastName =  trim($last);
        //     }
        // }

        // $card->firstName = $firstName == '' ? $bookingInfo->guest_name : $firstName;
        // $card->lastName = $lastName == '' ? $bookingInfo->guest_last_name : $lastName;

            PaymentGatewayRepo::addMetadataInformation($bookingInfo, $card, BookingController::class);

            $isCard3DS = false;
            $trans = new Transaction();

        try {
            $pg = new PaymentGateway();
            $trans = $pg->chargeWithCustomer($ccInfo->customer_object, $card, $userPaymentGateway);
        } catch (GatewayException $e) {
            report($e);
            if($e->getCode() != PaymentGateway::ERROR_CODE_3D_SECURE) {
                return $this->errorResponse(setCardExceptionReadable($e->getMessage()), 422);
            }

            $isCard3DS = true;
            $trans->amount = abs($request->data['amount']);
            $trans->status = TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL;
            $trans->token = "";
            $trans->exceptionMessage = $e->getDescription();
            $trans->message = $e->getMessage();
        }
        /**
         * Get Transaction type id from PaymentTypeMeta
         */

        $paymentTypeMeta = new PaymentTypeMeta();
        $additionalChargeTransId = $paymentTypeMeta->getBookingPaymentManualAdditionalCharge();

        $transactionInit = new TransactionInit();
        $transactionInit->booking_info_id = $request->data['booking_info_id'];
        $transactionInit->due_date = Carbon::now()->toDateTimeString();
        $transactionInit->pms_id = $propertyInfo->pms_id;
        $transactionInit->price = $trans->amount;
        $transactionInit->payment_status = $trans->status;
        $transactionInit->user_id = $bookingInfo->user_account->user->id;
        $transactionInit->user_account_id = $bookingInfo->user_account_id;
        $transactionInit->charge_ref_no = $trans->token;
        $transactionInit->lets_process = 0;
        $transactionInit->final_tick = 1;
        $transactionInit->split = 1;
        $transactionInit->last_success_trans_obj = $trans;
        $transactionInit->type = TransactionInit::TRANSACTION_TYPE_ADDITIONAL_CHARGE;
        $transactionInit->status = TransactionInit::PAYMENT_STATUS_SUCCESS;
        $transactionInit->transaction_type = $additionalChargeTransId;
        $transactionInit->client_remarks = $request->data['description'];//
        // $transactionInit->auth_token = //
        $transactionInit->next_attempt_time = Carbon::now()->toDateTimeString();
        $transactionInit->attempt = 1; //
        $transactionInit->save();

        /**
         * Transaction Details Entry
         */

         $transactionDetail = new TransactionDetail();
         $transactionDetail->transaction_init_id = $transactionInit->id;
         $transactionDetail->cc_info_id = $ccInfo->id;
         $transactionDetail->user_id = $transactionInit->user_id;
         $transactionDetail->user_account_id = $transactionInit->user_account_id;
         $transactionDetail->name = $bookingInfo->guest_name;
         $transactionDetail->payment_processor_response = json_encode($trans);
         $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
         $transactionDetail->payment_status = $trans->status;
         $transactionDetail->charge_ref_no = $trans->token;
         $transactionDetail->client_remarks = $request->data['description'];
         $transactionDetail->order_id = $card->order_id;
         $transactionDetail->error_msg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message); 
         $transactionDetail->save();

            if($isCard3DS) {

                $trans->amount = abs($request->data['amount']);
                $trans->currency_code = $currency_code;

                //inform guest for 3DS charge authentication
                return $this->errorResponse('This card is protected with 3DS, Email has been sent to guest to Authorize and pay this transaction.', 422);

            }

         //Preferences
         $preferenceFormId = config('db_const.user_preferences.preferences.PAYMENT_SUCCESS');
         event(new PMSPreferencesEvent($useraccount, $bookingInfo, $transactionInit->id, $preferenceFormId));

         $s_msg = 'Payment Successfully Charged';
         return $this->successResponse($s_msg, 200);

    }catch(Exception $e){

        return $this->errorResponse($e->getMessage(), 422);
    }

    }

    public function refund(Request $request){

        $this->isPermissioned('bookings');

//        if (!$request->hasValidSignature()){
//
//            return response()->json('invalid authentication', 401);
//        }
        
        try{

        if(!empty($request->transaction_id)){

            $transaction_init = TransactionInit::find($request->transaction_id);
            //TODO: Must check the status of transaction is paid.
            $userAccountId = auth()->user()->user_account_id;

            $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($transaction_init->booking_info);


            $refunded = RefundDetail::where('against_charge_ref_no', $transaction_init->charge_ref_no)->sum('amount');
            $amountToRefund = $transaction_init->price - $refunded;

            if($amountToRefund <= 0){
                $e_msg = "Transaction is Already Refunded.";
                return $this->errorResponse($e_msg, 422);
            }

            if($refunded > 0 && $request->partialFlag == false){

                $b = new Bookings($userAccountId);
                $currency_code = $b->getCurrencyCode($transaction_init->booking_info);

                $locale= config('app.locale');
                $currency= $currency_code;
                $fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
                $symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

                $p_msg = "Transaction amount is already partially refunded. Amount available to refund is {$symbol} {$amountToRefund} from {$symbol} {$transaction_init->price}";

                return $this->provisionResponse($p_msg, 449);

            }
            // dd(['refunded'=> $refunded, 'price'=> $transaction_init->price, 'amountToRefund'=> $amountToRefund, 'transaction'=> $transaction_init]);

            if(!isset($transaction_init->charge_ref_no)){
                
                $e_msg = "Transaction is Already Refunded.";
                return $this->errorResponse($e_msg, 422);
            }

            $transaction = new Transaction();
            $transaction->token = $transaction_init->charge_ref_no;
            $transaction->amount = $transaction_init->price - $refunded;
            $transaction->currency_code = $transaction_init->last_success_trans_obj->currency_code;
            $transaction->order_id = $transaction_init->last_success_trans_obj->order_id;
            $transaction->isPartial = true;
            // $transaction->description = 'B id ' . $transaction_init->booking_info->pms_booking_id . ' refund. ' . $request->data['description'];
            $transaction->description = "requested_by_customer"; // There is need to consult with Ammar bhai on this change.
            
            try{
                $pg = new PaymentGateway();
                $trans = $pg->refund($transaction, $userPaymentGateway);

            } catch (GatewayException $e) {
                report($e);
                return $this->errorResponse($e->getMessage(), 422);
            }

            if(!$trans->status){
                $erMsg = $trans->message != null ? $trans->message : '';
                $erMsg .= $trans->exceptionMessage != null ? $trans->exceptionMessage : '';
                return $this->errorResponse($erMsg, 422);
            }

            $paymentTypeMeta = new PaymentTypeMeta();
            $manualRefundFullTransId = $paymentTypeMeta->getBookingPaymentManualRefundFull();

            $transactionInit = new TransactionInit();
            $transactionInit->booking_info_id = $transaction_init->booking_info->id;
            $transactionInit->pms_id = $transaction_init->booking_info->pms_id;
            $transactionInit->due_date = Carbon::now()->toDateTimeString();
            $transactionInit->price = $trans->amount;
            if($trans->status){
                $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_SUCCESS;  
            }else{
                $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_FAIL;
            }  
            $transactionInit->user_id = $transaction_init->user_id;
            $transactionInit->user_account_id = $transaction_init->user_account_id;
            $transactionInit->charge_ref_no = $trans->token;
            $transactionInit->last_success_trans_obj = $trans;
            $transactionInit->lets_process = 0;
            $transactionInit->final_tick = 1;
            $transactionInit->split = 1;
            // $transactionInit->against_charge_ref_no = 
            if($request->sddFlag){
                $paymentTypeMeta = new PaymentTypeMeta();
                $manualRefundSDDId = $paymentTypeMeta->getSecurityDepositManualRefundFull();

                $transactionInit->type = TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND;
                $transactionInit->transaction_type = $manualRefundSDDId;
                $SDRefundTrans = true;

            }else{
                $transactionInit->type = TransactionInit::TRANSACTION_TYPE_REFUND;
                $transactionInit->transaction_type = $manualRefundFullTransId;
            }
            
            $transactionInit->status = 1;
            // $transactionInit->client_remarks = $request->data['description'];//
            $transactionInit->against_charge_ref_no = $transaction_init->charge_ref_no;
            $transactionInit->next_attempt_time = Carbon::now()->toDateString();
            $transactionInit->attempt = 1; //

            $transactionInit->save();

            /**
             * Transaction Details Entry
             */

            $transactionDetail = new TransactionDetail();
            $transactionDetail->transaction_init_id = $transactionInit->id;
            // $transactionDetail->cc_info_id = $transaction_init->transactions_detail->where('charge_ref_no', $transaction_init->charge_ref_no)->first()->cc_info_id;
            $transactionDetail->cc_info_id = 0; /* Refund Case Token used instead of cc_info */
            $transactionDetail->user_id = $transactionInit->user_id;
            $transactionDetail->user_account_id = $transactionInit->user_account_id;
            $transactionDetail->name = $transaction_init->booking_info->guest_name;
            $transactionDetail->payment_processor_response = json_encode($trans);
            $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
            $transactionDetail->payment_status = $trans->status;
            $transactionDetail->charge_ref_no = $trans->token;
            $transactionDetail->client_remarks = $request->data['description'];
            $transactionDetail->order_id = round(microtime(true) * 1000);
            $transactionDetail->error_msg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message); 
            $transactionDetail->save();

            /**
             * Updation of old transaction (update payment status => void)
             */
            $refundDetail = new RefundDetail();
            $refundDetail->transaction_init_id = $transactionInit->id;
            $refundDetail->booking_info_id = $transaction_init->booking_info_id;
            $refundDetail->user_id = $transactionInit->user_id;
            $refundDetail->user_account_id = $transactionInit->user_account_id;
            $refundDetail->name = $transaction_init->booking_info->guest_name;
            $refundDetail->payment_processor_response = json_encode($trans);
            $refundDetail->user_payment_gateway_id = $userPaymentGateway->id;
            $refundDetail->payment_status = $trans->status;
            $refundDetail->charge_ref_no = $trans->token;
            $refundDetail->against_charge_ref_no = $transaction_init->charge_ref_no;
            $refundDetail->amount = $trans->amount;
            $refundDetail->order_id = round(microtime(true) * 1000);
            $refundDetail->save();

            if(isset($SDRefundTrans) && ($SDRefundTrans === true)) {
                $preferenceFormId = config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_SUCCESS');
                event(new PMSPreferencesEvent($transactionInit->user_account, $transactionInit->booking_info, $transactionInit->id, $preferenceFormId, 0));
            }

//TODO
            $s_msg = 'Payment Refunded Successfully.';
            event(new RefundEmailEvent($transaction_init->booking_info, $trans->amount));
            return $this->successResponse($s_msg, 200);
        }
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function refundAmount(Request $request) {

        $this->isPermissioned('bookings');

        try {
            $validator = Validator::make($request->all(), [
                'amount'=> 'required|numeric',
                'booking_id'=> 'required',
                'description' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }

            $bookingInfo = BookingInfo::where('id', $request->booking_id)->first();

            /**
             * Below line was commented by Ammar, while adjusting CreditCardInfo for multiple cards.
             * As below line of code was not being used.
             */
//            $ccInfo = CreditCardInfo::where('booking_info_id', $decryptTransaction['booking_info_id'])->first();
            
            $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);

            $refund = new BaRefund();
            $response = $refund->refundAmount($bookingInfo, $request->amount, $userPaymentGateway, $request->description, $request->transaction_id);
            
            if($response['status']){
                $msg = "Payment Refunded Successfully.";

                //this is not the safe way
                //TODO need to pass refund details entry primary key from Refund class
                $refund_details = RefundDetail::where('booking_info_id', $request->booking_id)->where('amount', $request->amount)->latest('id')->limit(1)->first();
                //if entry found then pass to email event
                if($refund_details) {
                    // To Both Client And Guest
                    event(new EmailEvent(config('db_const.emails.heads.manual_refund_successful.type'), $refund_details->id));
                }

                return $this->successResponse($msg, 200);
            }else{
                return $this->successResponse($response['transaction']->message, 422);
            }
        } catch (GatewayException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (RefundException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTraceAsString()]);
            return $this->errorResponse('Something Went Wrong.', 500);
        }
    }

    public function refundAmountSDD(Request $request) {

        $this->isPermissioned('bookings');

        if (!$request->hasValidSignature()){
            
            return response()->json('invalid authentication', 401);
        }

        try {
            $decryptTransaction = decrypt($request->transaction); //Decrypt Transaction
            $bookingInfo = BookingInfo::where('id', $decryptTransaction['booking_info_id'])->first();

            /**
             * Below line was commented by Ammar, while adjusting CreditCardInfo for multiple cards.
             * As below line of code was not being used.
             */
//            $ccInfo = CreditCardInfo::where('booking_info_id', $decryptTransaction['booking_info_id'])->first();

            $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);

            $refund = new BaRefund();
            $response = $refund->refundAmountSDD($bookingInfo, $request->amount, $userPaymentGateway);
            
            if($response['status']){
                event(new RefundEmailEvent($bookingInfo, $request->amount));
                $msg = "Payment Refunded Successfully.";
                return $this->successResponse($msg, 200);
            }else{
                $msg = "Refund Status is False.";
                return $this->errorResponse($msg, 422);
            }
            
        } 
        catch (GatewayException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (RefundException $e) {

            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function chargeForSecurityDamages(Request $request) {

        $this->isPermissioned('bookings');

        if (!$request->hasValidSignature()){
            
            return response()->json('invalid authentication', 401);
        }

        try{
    
            $decryptTransaction = decrypt($request->data['transaction']);
    
    
            $bookingInfo = BookingInfo::where('id', $decryptTransaction['booking_info_id'])->first();
            $propertyInfo = $bookingInfo->property_info;

            // $isVC = false;
            // if($bookingInfo->is_vc == 'VC')
            //     $isVC = true;

            // $ccInfo = null;
            // if($isVC)
            //     $ccInfo = CreditCardInfo::where('booking_info_id', $decryptTransaction['booking_info_id'])->where('is_vc', 1)->first();
            // else
                $ccInfo = CreditCardInfo::where('booking_info_id', $decryptTransaction['booking_info_id'])->latest('id')->limit(1)->first();

            $isUserPGGlobal = ($propertyInfo->use_pg_settings == 0) ? true : false;
            if($isUserPGGlobal){
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', '0')->first();
            }else{
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', $propertyInfo->id)->first();
            }

            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);

            $card = new Card();
            $card->firstName = $ccInfo->customer_object->first_name;
            $card->lastName = $ccInfo->customer_object->last_name;
            $card->token = $ccInfo->customer_object->token;
            $card->amount = abs($request->data['amount']);
            $card->currency = $currency_code;
            $card->order_id = round(microtime(true) * 1000);

            // if($request->data['full_name'] != null && $request->data['full_name'] != '') {
            // $split = explode(' ', $request->data['full_name']);
            // if(count($split) > 1)
            //         $firstName =  $split[0];
            // }

            // if($request->data['full_name'] != null && $request->data['full_name'] != '') {
            //     $split = explode(' ', $request->data['full_name']);
            //     if(count($split) > 1) {
            //         $last = '';
            //         for($i = 1; $i < count($split); $i++)
            //             $last .= ' ' . $split[$i];
            //         $lastName =  trim($last);
            //     }
            // }

            // $card->firstName = $firstName == '' ? $bookingInfo->guest_name : $firstName;
            // $card->lastName = $lastName == '' ? $bookingInfo->guest_last_name : $lastName;

            PaymentGatewayRepo::addMetadataInformation($bookingInfo, $card, BookingController::class);

            $isCard3DS = false;
            $trans = new Transaction();

            try {
                $pg = new PaymentGateway();
                $trans = $pg->chargeWithCustomer($ccInfo->customer_object, $card, $userPaymentGateway);

            } catch (GatewayException $e) {
                report($e);

                if($e->getCode() != PaymentGateway::ERROR_CODE_3D_SECURE) {
                    //$e_msg = 'Your Card was Declined or you attempted with Failed card. Try to add a new card.';
                    $e_msg = $e->getDescription();
                    return $this->errorResponse($e_msg, 422);
                }

                $isCard3DS = true;
                $trans->amount = abs($request->data['amount']);
                $trans->status = TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL;
                $trans->token = "";
            }
            /**
             * Get Transaction type id from PaymentTypeMeta
             */
            $paymentTypeMeta = new PaymentTypeMeta();
            $additionalChargeTransId = $paymentTypeMeta->getSecurityDepositManualCollection();
    
            $transactionInit = new TransactionInit();
            $transactionInit->booking_info_id = $decryptTransaction['booking_info_id'];
            $transactionInit->due_date = Carbon::now()->toDateTimeString();
            $transactionInit->pms_id = $propertyInfo->pms_id;
            $transactionInit->price = $trans->amount;
            $transactionInit->payment_status = $trans->status;
            $transactionInit->user_id = $bookingInfo->user_id;
            $transactionInit->user_account_id = $bookingInfo->user_account_id;
            $transactionInit->charge_ref_no = $trans->token;
            $transactionInit->lets_process = 0;
            $transactionInit->final_tick = 1;
            $transactionInit->split = 1;
            $transactionInit->last_success_trans_obj = $trans;
            $transactionInit->type = TransactionInit::TRANSACTION_TYPE_ADDITIONAL_SECURITY_DAMAGE_CHARGE;
            $transactionInit->status = TransactionInit::PAYMENT_STATUS_SUCCESS;
            $transactionInit->transaction_type = $additionalChargeTransId;
            $transactionInit->client_remarks = $request->data['description'];//
            // $transactionInit->auth_token = //
            $transactionInit->next_attempt_time = Carbon::now()->toDateTimeString();
            $transactionInit->attempt = 1; //
    
            $transaction = $transactionInit->save();
    
            /**
             * Transaction Details Entry
             */
    
             $transactionDetail = new TransactionDetail();
             $transactionDetail->transaction_init_id = $transactionInit->id;
             $transactionDetail->cc_info_id = $ccInfo->id;
             $transactionDetail->user_id = $transactionInit->user_id;
             $transactionDetail->user_account_id = $transactionInit->user_account_id;
             $transactionDetail->name = $bookingInfo->guest_name;
             $transactionDetail->payment_processor_response = json_encode($trans);
             $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
             $transactionDetail->payment_status = $trans->status;
             $transactionDetail->charge_ref_no = $trans->token;
             $transactionDetail->client_remarks = $request->data['description'];
             $transactionDetail->order_id = $card->order_id;
             $transactionDetail->error_msg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message); 
             $transactionDetail->save();

            if($isCard3DS) {

                $trans->amount = abs($request->data['amount']);
                $trans->currency_code = $currency_code;

                //inform guest for 3DS charge authentication
                event(new EmailEvent(config('db_const.emails.heads.charge_3ds_required.type'),$transactionInit->id ));
                return $this->errorResponse('This card is protected with 3DS, Email has been sent to guest to Authorize and pay this transaction.', 422);

            }

            //Preferences
            $preferenceFormId = config('db_const.user_preferences.preferences.PAYMENT_SUCCESS');
            event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, $transactionInit->id, $preferenceFormId));

            return $this->successResponse('Payment Successfully Charged', 200);
    
    
        }catch(Exception $e){

            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function makeTransactionStatusVoid(Request $request) {

        $this->isPermissioned('bookings');

        if (!$request->hasValidSignature()){
            
            return response()->json('invalid authentication', 401);
        }

        try {
            $ccAuth = CreditCardAuthorization::findOrFail($request->cc_auth_id);
            if($ccAuth) {

                if($ccAuth->status == '3'){
                    return response()->json([
                        'status'=> true,
                        'status_code'=> 422,
                        'message'=> 'Transaction Status is already Void.'
                    ]);
                }
                
                $ccAuth->status = CreditCardAuthorization::STATUS_VOID;
                $ccAuth->save();

                return response()->json([
                    'status'=> true,
                    'status_code'=> 200,
                    'message'=> 'Transaction Status Turns to Void.'
                ]);
            }else{
                return response()->json([
                    'status'=> false,
                    'status_code'=> 422,
                    'message'=> 'Authorization Amount Not Found.'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'=> false,
                'status_code'=> 422,
                'message'=> $e->getMessage(),
                'stack_trace'=> $e->getTraceAsString()
            ]);
        }
    }

    public function captureAuthAmount(Request $request){

        $this->isPermissioned('bookings');

        $this->validate($request, [
            'amount' => 'required|numeric',
            'description' => 'required',
        ]);

        try {

            /**
             * @var $ccauth CreditCardAuthorization
             */
            $ccauth = CreditCardAuthorization::findOrFail($request->cc_auth_id);

            if($ccauth->captured == 1) {
                return response()->json([
                    'status'=> false,
                    'status_code'=> 422,
                    'message'=> 'Amount Already Captured'
                ]);
            }

            $bookingInfo = BookingInfo::where('id', $request->booking_info_id)->first();
            $propertyInfo = $bookingInfo->property_info;
            $ccInfo = CreditCardInfo::where('id', $ccauth->cc_info_id)->first();

            if ($request->amount > $ccauth->hold_amount) {
                return $this->apiErrorResponse('You can capture maximum ' . $ccauth->hold_amount . ' of remaining hold amount.', 400);
            }
            
            $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

            /**
            * Get Currency code from Repository class 
            */
            $bookings = new Bookings($bookingInfo->user_account_id);
            $currency_code = $bookings->getCurrencyCode($bookingInfo, $propertyInfo);

            $card = new Card();
            $card->amount = $request->amount;
            $card->currency = $currency_code;

            PaymentGatewayRepo::addMetadataInformation($bookingInfo, $card, BookingController::class);

            $pg = new PaymentGateway();
            $tran = new Transaction();

            // First try for capture
            try {

                /**
                 * @var $tranDb Transaction
                 */
                $tranDb = $ccauth->transaction_obj;
                $tranDb->amount = $request->amount;
                $tranDb->isPartial = true;
                $tranDb->message = $request->description;
                $tran = $pg->capture($tranDb, $userPaymentGateway);

            } catch (GatewayException $e) {

                Log::error($e->getDescription(), [
                    'File' => BookingController::class,
                    'Function' => __FUNCTION__,
                    'ccAuthId' => $request->cc_auth_id,
                    'Line' => 'SDD Capture Failure']);

                if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {
                    $resp = new Transaction();
                    $resp->amount = $request->amount;
                    $resp->currency_code = $currency_code;

                    //inform guest
                    event(new EmailEvent(config('db_const.emails.heads.sd_3ds_required.type'), $ccauth->id));
                    return response()->json([
                        'status'=> false,
                        'status_code'=> 422,
                        'message'=> "This card is protected with 3DS, Email has been sent to guest to Authorize and pay this transaction.",
                        'stack_trace'=> $e->getTraceAsString()
                    ]);
                }
            }

            // Second try with cancel previous SDD auth then try with charge attempt
            if(!$tran->status) {
                try {

                    $pg->cancelAuthorization($ccauth->transaction_obj, $userPaymentGateway);

                    sleep(5);

                    $tran = $pg->chargeWithCustomer($ccauth->ccinfo->customer_object, $card, $userPaymentGateway);

                } catch (GatewayException $e) {

                    Log::error($e->getDescription(), [
                        'File' => BookingController::class,
                        'Function' => __FUNCTION__,
                        'ccAuthId' => $request->cc_auth_id,
                        'Line' => 'SDD Cancel or Charge Failure']);

                    if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {
                        $resp = new Transaction();
                        $resp->amount = $request->amount;
                        $resp->currency_code = $currency_code;

                        //inform guest
                        event(new EmailEvent(config('db_const.emails.heads.sd_3ds_required.type'), $ccauth->id));

                        return response()->json([
                            'status'=> false,
                            'status_code'=> 422,
                            'message'=> "This card is protected with 3DS, Email has been sent to guest to Authorize and pay this transaction.",
                            'stack_trace'=> $e->getTraceAsString()
                        ]);
                    }
                }
            }

            try {

                if($tran->status) {

                    if ($ccauth->hold_amount == $request->amount) {
                        $ccauth->captured = 1;
                        $ccauth->is_auto_re_auth = 0;
                    } elseif ($ccauth->hold_amount > $request->amount && $ccauth->is_auto_re_auth == 0) {
                        $ccauth->captured = 1;
                    } elseif ($ccauth->hold_amount > $request->amount && $ccauth->is_auto_re_auth == 1) {
                        $ccauth->captured = 2;
                        $ccauth->next_due_date = Carbon::now()->timezone($propertyInfo->time_zone)->toDateTimeString();
                    }

                    $ccauth->save();
                    $transaction_init = $this->newTransactionInitEntry($tran, $bookingInfo, $card, $userPaymentGateway, $ccInfo, $propertyInfo, $request->description);

                    //Preferences
                    $preferenceFormId = config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS');
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, $transaction_init->id, $preferenceFormId, 0));

                    return response()->json([
                        'status'=> true,
                        'status_code'=> 200,
                        'message'=> 'Amount Captured Successfully.']);

                } else {
                    $transaction_init = $this->newTransactionInitEntry($tran, $bookingInfo, $card, $userPaymentGateway, $ccInfo, $propertyInfo, $request->description);

                    $preferenceFormId = config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED');
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, $transaction_init->id, $preferenceFormId, 0));

                    return response()->json([
                        'status'=> false,
                        'status_code'=> 422,
                        'message'=> 'Capture status Failed.'
                    ]);
                }

            } catch(Exception $e) {
                Log::error($e->getMessage(), ['File'=>BookingController::class,'ccAuthId' => $request->cc_auth_id, 'Stack'=>$e->getTraceAsString()]);

                return response()->json([
                'status'=> false,
                'status_code'=> 422,
                'message'=> $e->getMessage(),
                'stack_trace'=> $e->getTraceAsString()]);
            }

        } catch(Exception $e) {
            Log::error($e->getMessage(), ['File'=>BookingController::class,'ccAuthId' => $request->cc_auth_id, 'Stack'=>$e->getTraceAsString()]);
            return response()->json([
                'status'=> false,
                'status_code'=> 422,
                'message'=> $e->getMessage(),
                'stack_trace'=> $e->getTraceAsString()]);
        }
    }

    private function newTransactionInitEntry(Transaction $trans, BookingInfo $bookingInfo, Card $card, UserPaymentGateway $userPaymentGateway, CreditCardInfo $ccInfo, PropertyInfo $propertyInfo, $description = ''){

        $paymentTypeMeta = new PaymentTypeMeta();
        $additionalChargeTransId = $paymentTypeMeta->getSecurityDepositManualCollection();

        $transactionInit = new TransactionInit();
        $transactionInit->booking_info_id = $bookingInfo->id;
        $transactionInit->due_date = Carbon::now()->toDateTimeString();
        $transactionInit->pms_id = $propertyInfo->pms_id;
        $transactionInit->price = $trans->amount;
        $transactionInit->payment_status = $trans->status;
        $transactionInit->user_id = $bookingInfo->user_id;
        $transactionInit->user_account_id = $bookingInfo->user_account_id;
        $transactionInit->charge_ref_no = $trans->token;
        $transactionInit->lets_process = 0;
        $transactionInit->final_tick = 1;
        $transactionInit->split = 1;
        $transactionInit->last_success_trans_obj = $trans;
        $transactionInit->type = TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE;
        $transactionInit->status = $trans->status;
        $transactionInit->transaction_type = $additionalChargeTransId;
         $transactionInit->client_remarks = 'Security auth captured';//
        // $transactionInit->auth_token = //
        $transactionInit->next_attempt_time = Carbon::now()->toDateTimeString();
        $transactionInit->attempt = 0; //

        $transactionInit->save();

        /**
         * Transaction Details Entry
         */

         $transactionDetail = new TransactionDetail();
         $transactionDetail->transaction_init_id = $transactionInit->id;
         $transactionDetail->cc_info_id = $ccInfo->id;
         $transactionDetail->user_id = $transactionInit->user_id;
         $transactionDetail->user_account_id = $transactionInit->user_account_id;
         $transactionDetail->name = $bookingInfo->guest_name;
         $transactionDetail->payment_processor_response = json_encode($trans);
         $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
         $transactionDetail->payment_status = $trans->status;
         $transactionDetail->charge_ref_no = $trans->token;
         $transactionDetail->client_remarks = $description;
         $transactionDetail->order_id = round(microtime(true) * 1000);
         $transactionDetail->error_msg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message); 
         $transactionDetail->save();

         return $transactionInit;
    }

    public function manualReattempt(Request $request){

        $this->isPermissioned('bookings');

        if (!$request->hasValidSignature()){
            
            return response()->json('invalid authentication', 401);
        }

        $tran = TransactionInit::findOrFail($request->transaction_init_id);

        if($tran){
            if($tran->attempt <= TransactionInit::TOTAL_ATTEMPTS) {

                $userAccount = UserAccount::where('id', $tran->user_account_id)->get();

                if($userAccount[0]->status == config('db_const.user.status.active.value')
                    or $userAccount[0]->status == config('db_const.user.status.suspended.value')) {

                    $bookingInfo = BookingInfo::where('id', $tran->booking_info_id)->get();
                    $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo[0]->property_id)
                        ->where('pms_id', $bookingInfo[0]->pms_id)
                        ->where('user_account_id', $tran->user_account_id)
                        ->get();

                    $isVC = false;
                    if($bookingInfo[0]->is_vc == 'VC')
                        $isVC = true;

                    $ccInfo = null;
                    if($isVC)
                        $ccInfo = CreditCardInfo::where('booking_info_id', $tran->booking_info_id)->where('is_vc', 1)->latest('id')->limit(1)->get();
                    else
                        $ccInfo = CreditCardInfo::where('booking_info_id', $tran->booking_info_id)->latest('id')->limit(1)->get();

                    $upg = new PaymentGatewayRepo(); /** To get UserPaymentGateway*/
                    $gateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo[0]);


                    $propertyStatus = isset($propertyInfo[0]->status) ? $propertyInfo[0]->status : false;

                    if($propertyStatus && isset($ccInfo[0]) && isset($gateway)) {

                        $bookingSourceRepo = new BookingSources();
                        $is_activeBookingSource = $bookingSourceRepo->isBookingSourceActive($propertyInfo[0],
                            $bookingSourceRepo::getBookingSourceFormIdByChannelCode($bookingInfo[0]->pms_id, $bookingInfo[0]->channel_code));

                        if( !$is_activeBookingSource ){

                            return response()->json([
                                'status'=> false,
                                'status_code'=> 422,
                                'message'=> 'Booking Source is not Active.' 
                            ]);
                        }
                        // dd($ccInfo[0]);

                        $card = new Card();
                        $card->firstName = $ccInfo[0]->customer_object->first_name;
                        $card->lastName = $ccInfo[0]->customer_object->last_name;
                        $card->token = $ccInfo[0]->customer_object->token;
                        $card->amount = $tran->price;
                        $repBookings = new Bookings($userAccount[0]->id);
                        $card->currency = $repBookings->getCurrencyCode($bookingInfo[0], $propertyInfo[0]);
                        $card->order_id = round(microtime(true) * 1000);
                        $pg = new PaymentGateway();

                        /**
                         * @var $authTransactionObj Transaction
                         */
                        $auths = $ccInfo[0]->ccauth;
                        $paymentTypeMeta = new PaymentTypeMeta();
                        $securityId = $paymentTypeMeta->getAuthTypeSecurityDamageValidation();

                        if($auths != null && isset($auths->token) && $auths->token != ''){
                            foreach($auths as $auth){
                                if($auth->type != $securityId) {

                                    try {
                                        $cancelAuth = $pg->cancelAuthorization($auth->transaction_obj, $gateway);

                                    } catch (GatewayException | \Exception $e) {
                                        Log::debug($e->getMessage());
                                    }
                                }
                            }
                        }


                        $resp = new Transaction();
                        $msgForEvent = '';
                        if($card->amount > 0) {

                            PaymentGatewayRepo::addMetadataInformation($bookingInfo[0], $card, BookingController::class);

                            try {
                                $resp = $pg->chargeWithCustomer($ccInfo[0]->customer_object, $card, $gateway);
                                $msgForEvent = $resp->message . ' ' . $resp->exceptionMessage;

                            } catch (GatewayException $e) {

                                report($e);
                                $msgForEvent = "Charge failed for Transaction ID: " . $tran->id . " with amount of " . $card->amount . " Reason: " . $e->getDescription();
                                $resp->exceptionMessage = $msgForEvent;
                                $resp->order_id = $card->order_id;
                                Log::notice($msgForEvent, ['File'=>BookingController::class]);

                                //Email & SMS Sending Event
                                event(new EmailEvent(config('db_const.emails.heads.payment_failed.type'),$tran->id, [ 'reason' =>  $e->getDescription() ]));

                                return response()->json([
                                    'status'=> false,
                                    'status_code'=> 422,
                                    'message'=> 'Charge failed due to : '.$e->getDescription()
                                ]);
                            }

                        } else {
                            $resp->fullResponse = '';
                            $resp->status = true;
                            $resp->token = '';
                            $resp->order_id = $card->order_id;
                            $resp->message = "Amount Was zero, So not tried for charge";
                            $msgForEvent = $resp->message;
                        }

                        $transDetail = new TransactionDetail();
                        $transDetail->transaction_init_id = $tran->id;
                        $transDetail->cc_info_id = $ccInfo[0]->id;
                        //$transDetail->user_id = $trans[0]->user_id; // Only when manual operation is performed
                        $transDetail->user_account_id = $tran->user_account_id;
                        // $transDetail->name =
                        $transDetail->payment_processor_response = $resp->fullResponse;
                        $transDetail->payment_gateway_form_id = $gateway->payment_gateway_form->id;
                        $transDetail->payment_status = $resp->status;
                        $transDetail->charge_ref_no = $resp->token;
                        //$transDetail->client_remarks = $tran->client_remarks; // Only when manual operation is performed
                        $transDetail->order_id = $resp->order_id;
                        $transDetail->error_msg = ($resp->exceptionMessage != '' ? $resp->exceptionMessage : $resp->message); 
                        $transDetail->save();
                        // turn processing OFF after charge

                        if($resp->status) {
                            $tran->lets_process = 0;
                            $tran->payment_status = TransactionInit::PAYMENT_STATUS_SUCCESS;
                            $tran->charge_ref_no = $resp->token;
                            $tran->last_success_trans_obj = $resp;

                            //Email & SMS Sending Event
                            event(new EmailEvent(config('db_const.emails.heads.payment_successful.type'), $transDetail->id ));

                        } else {
                            $tran->payment_status = TransactionInit::PAYMENT_STATUS_REATTEMPT;
                            $oldAttemptTime = $tran->next_attempt_time;
                            $nextAttemptTime = new Carbon($oldAttemptTime);
                            $tran->attempt = $tran->attempt + 1;
                            $tran->next_attempt_time = $nextAttemptTime->addHours($tran->attempt);

                            //Email & SMS Sending Event
                            event(new EmailEvent(config('db_const.emails.heads.payment_failed.type'),$tran->id, [ 'reason' =>  $transDetail->error_msg ]));

                            if($tran->attempt == TransactionInit::TOTAL_ATTEMPTS) {
                                $tran->payment_status = TransactionInit::PAYMENT_STATUS_VOID;
                            }
                        }

                        $tran->save();
                    }
                }

            } else {
                // Total attempts reach
                $tran->lets_process = 0;
                $tran->payment_status = TransactionInit::PAYMENT_STATUS_VOID;
                $tran->save();
            }
        }
    }

    public function updateCardNow(Request $request)
    {
        // Check Permission If Signed User else for guest url already signed
        if (!Auth::guest())
            $this->isPermissioned('bookings');

        if (empty($request->data['first_name']) || empty($request->data['last_name'])) {
            return $this->errorResponse('Full name required', 422);
        }

        $validator = Validator::make($request->all(), [
            'data.first_name'=> 'required',
            'data.booking_id'=> 'required|numeric',
            'data.last_name'=> 'required',
        ], [
            'data.first_name'=> 'Full name required',
            'data.booking_id'=> 'Booking ID is required',
            'data.last_name'=> 'Last Name required',
        ]);

        if (!$validator->passes()){

            return $this->errorResponse($validator->errors()->first(), 422);

        }

        try {
            $card_object = new CardObject();
            $card_object->full_name = $request->data['first_name'] . ' ' . $request->data['last_name'];
            $card_object->token = $request->data['payment_method'];
            $card_object->first_name = $request->data['first_name'];
            $card_object->last_name = $request->data['last_name'];

            $card = $this->updateCard($card_object, $request->data['booking_id']);

            if (!empty($card)) {
                //send added card details back
                return $this->successResponse('Card Added Successfully.', 200,
                    [
                        'card_available' => true,
                        'invalid' => false,
                        'card_name' => $card->card_name,
                        'last_4_digits' => $card->cc_last_4_digit,
                        'expiry_month' => $card->month,
                        'expiry_year' => $card->year
                    ]
                );
            } else {
                return $this->errorResponse('Failed to Add Card.', 500);
            }
        } catch (GatewayException $exception) {
            log_exception_by_exception_object($exception);
            return $this->errorResponse($exception->getMessage(), 422);

        } catch (UpdateCardException $exception) {
            log_exception_by_exception_object($exception);
            return $this->errorResponse($exception->getMessage(), 422);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);
        }
    }


    public function cancelBdcBookingDetailPage(Request $request) {

        $booking_info_id = $request->booking_info_id;
        $bookingInfo = BookingInfo::select('id', 'pms_booking_id', 'user_account_id', 'guest_name', 'guest_last_name')->where('id',$booking_info_id)->first();

        //dd($bookingInfo);
        if(is_null($bookingInfo))
            abort(403, "Unauthorized");

        $data = [];
        $data['bookingInfo'] = $bookingInfo;

        $url = URL::signedRoute(
            'cancelBdcBooking',
            ['b_id'=>$booking_info_id, 'u_id'=> $bookingInfo->user_account_id]);

        return view('v2.client.bookings.cancel-bdc-booking-detail', [
            'data' => $data,
            'url' => $url,
        ]);
    }

    public function cancelBdcBooking(Request $request) {

        $res = ['flag' => true, 'message' => 'Successfully canceled booking on <strong>Booking.com</strong>'];
        $status = 200;

        try {

            $userAccount = UserAccount::where('id', $request->u_id)->first();
            if($userAccount == null) {
                $res['flag'] = false;
                $res['message'] = 'Malformed Request detected';
                return response($res, $status);
            }

            $bookingInfo = BookingInfo::where('id', $request->b_id)->first();
            if($bookingInfo == null) {
                $res['flag'] = false;
                $res['message'] = 'Malformed Request detected';
                return response($res, $status);
            }

            $propertyInfo = $userAccount->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();
            if($propertyInfo == null) {
                $res['flag'] = false;
                $res['message'] = 'Malformed Request detected';
                return response($res, $status);
            }

            $pms = new PMS($userAccount);

            $pmsOptions = new PmsOptions();
            $pmsOptions->propertyKey = $propertyInfo->property_key;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $pmsOptions->bookingID = $bookingInfo->pms_booking_id;
            $pmsOptions->bookingReportCancel = true;

            $bookingToUpdate = new Booking();
            $bookingToUpdate->id = $bookingInfo->pms_booking_id;

            $result = $pms->update_booking($pmsOptions, $bookingToUpdate);

            if($result !== true) {

                Log::notice('Booking.com booking cancellation failed', [
                    'File'=>BookingController::class,
                    'Function'=>__FUNCTION__,
                    'BookingInfoId'=>$request->b_id,
                    'UserAccountId'=>$request->u_id
                ]);

                $res['flag'] = false;
                $res['message'] = $result;
            }
            else {
                $bookingInfo->manual_canceled = 1;
                $bookingInfo->save();
            }

            return response($res, $status);
        }
        catch (PmsExceptions $e) {
            $res['flag'] = false;
            $res['message'] = $e->getMessage();
            Log::error($e->getMessage(), [
                'File'=>BookingController::class,
                'Function'=>__FUNCTION__,
                'BookingInfoId'=>$request->b_id,
                'UserAccountId'=>$request->u_id,
                'Stack'=>$e->getTraceAsString()
            ]);
            return response($res, 500);
        }
    }

    public function reduceAmount(Request $request) {

        $this->isPermissioned('bookings');

        if($request->has('data')) {
            if(key_exists('newAmount', $request->data)) {

                try {

                    //$d = decrypt($request->data['transaction']);

                    $newAmount = abs($request->data['newAmount']);
                    $booking_info_id = $request->data['booking_info_id'];
                    $transaction_init_id = $request->data['transaction_init_id'];

                    if (!is_numeric($newAmount) && !is_float($newAmount) && !is_double($newAmount))
                        return $this->errorResponse('Not a valid amount', 400);

                        //return response(['status' => false, 'message' => 'Not a valid amount'], 400);

                    $user = auth()->user();
                    $userAccount = $user->user_account;

                    /**
                     * @var $transactionInit TransactionInit
                     */
                    $transactionInit = TransactionInit::where('booking_info_id', $booking_info_id)
                        ->where('id', $transaction_init_id)
                        ->where('user_account_id', $userAccount->id)
                        ->first();

                    if ($transactionInit == null)
                        return $this->errorResponse('Transaction record not found', 400);

                        //return response(['status' => false, 'message' => 'Transaction record not found'], 400);

                    $system_remarks = $transactionInit->system_remarks;
                    $oldAmount = $transactionInit->price;

                    if ($newAmount > $oldAmount)
                        return $this->errorResponse('New amount greater than old amount', 400);
                    elseif ($newAmount == $oldAmount)
                        return $this->successResponse('Old and New amounts are same', 200);

                    //return response(['status' => false, 'message' => 'New amount greater than old amount'], 400);
                        //return response(['status' => true, 'message' => 'Old and New amounts are same'], 200);

                    $msg = " Amount set to " . $newAmount . ", previous amount was " . $oldAmount;

                    $payment_status = $transactionInit->payment_status;
                    if ($newAmount == 0) {
                        $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_VOID;
                        $payment_status = TransactionInit::PAYMENT_STATUS_VOID;
                        $transactionInit->lets_process = 0;
                        $transactionInit->system_remarks = $system_remarks . $msg;
                        $transactionInit->price = 0;

                    } else {
                        $transactionInit->price = $newAmount;
                        $transactionInit->system_remarks = $system_remarks . $msg;

                        if($transactionInit->payment_status == TransactionInit::PAYMENT_STATUS_ABORTED) {
                            $payment_status = TransactionInit::PAYMENT_STATUS_PENDING;
                            $transactionInit->payment_status = $payment_status;
                            $transactionInit->lets_process = 1;
                            // $transactionInit->next_attempt_time = now()->addMinute(5)->toDateTimeString();
                            $transactionInit->due_date = now()->addMinute(5)->toDateTimeString();
                            $transactionInit->attempt = 0;
                        }
                    }

                    $transactionInit->save();

                    $tDetail = new TransactionDetail();
                    $tDetail->transaction_init_id = $transaction_init_id;
                    $tDetail->user_id = $user->id;
                    $tDetail->user_account_id = $userAccount->id;
                    $tDetail->client_remarks = $msg;
                    $tDetail->error_msg = $msg;
                    $tDetail->payment_status = $payment_status;
                    $tDetail->amount = abs($oldAmount - $newAmount);
                    $tDetail->save();

                    //get new amount and balance after reducing price
                    $booking_info = BookingInfo::with('transaction_init_charged')
                                                ->where('id', $booking_info_id)->first();
                    $symbol = get_currency_symbol($booking_info->property_info->currency_code);
                    $balance = $booking_info->transaction_init_charged
                                ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)
                                ->sum('price') 
                                - 
                                $booking_info->transaction_init_charged
                                ->where('payment_status', TransactionInit::PAYMENT_STATUS_SUCCESS)
                                ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)
                                ->sum('price');

                    $amount_to_show = $booking_info->transaction_init_charged
                                        ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)
                                        ->sum('price');

                    return $this->successResponse(
                            'Transaction Amount Reduced Successfully', 
                            200,    
                            [
                                'new_amount' => $symbol.number_format($amount_to_show, 2),
                                'new_balance' => $symbol.number_format($balance, 2),
                            ]
                        );

                } catch (\Exception $e) {
                    //return response(['status' => false, 'message' => $e->getMessage()], 400);
                    return $this->errorResponse($e->getMessage(), 400);
                }
            }
        }

        return $this->errorResponse('Something went wrong!', 400);
        //return response(['status' => false, 'message' => 'Something went wrong!'], 400);
    }

    public function resend_pre_checkin_wizard_email(Request $request)
    {
        $this->isPermissioned('bookings');
        $booking_info = BookingInfo::where([['id', $request->id], ['user_account_id', auth()->user()->user_account_id]])->first();
        if (!empty($booking_info)) {
            //send email to guest
            EmailJob::dispatch(config('db_const.emails.heads.new_booking.type'), 'guest', $request->id)->onQueue('send_email');
            return $this->successResponse('Email was sent successfully.', 200, '');
        } else {
            return $this->apiErrorResponse('Booking Id not valid', 422);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsPaid(Request $request)
    {
        $this->isPermissioned('bookings');
        $transactionInit = TransactionInit::with('booking_info')
            ->where('user_account_id',auth()->user()->user_account_id)
            ->where('id',$request->transaction_id)->first();

        if (!is_null($transactionInit)) {
            if (TransactionInitRepository::isTransactionStatusValidToMarkAsPaid($transactionInit->payment_status)) {
                $description = 'Manually Marked as Paid by '.auth()->user()->name;
                resolve(TransactionDetail::class)::create(
                ['transaction_init_id'          => $transactionInit->id,
                'name'                          => auth()->user()->name,
                'user_account_id'               => auth()->user()->user_account_id,
                'user_id'                       => auth()->user()->id,
                'payment_processor_response'    => $description,
                'payment_status'                => 1,
                'client_remarks'                => $description,
                'charge_ref_no'                 => '',
                'error_msg'                     => $description,
                'amount'                        => $transactionInit->price,
                'order_id'                      => null,
                'payment_gateway_form_id'       => 0,
                'cc_info_id'                    => 0 ]);

                $transactionInit->update(['payment_status' => TransactionInit::PAYMENT_MARKED_AS_PAID]);

                event(new PMSPreferencesEvent(auth()->user()->user_account, $transactionInit->booking_info,
                    $transactionInit->id,
                    config('db_const.user_preferences.preferences.PAYMENT_MANUALLY_MARKED_AS_PAID')));

                return $this->successResponse('Successfully Marked as Paid', 200, '');
            } else
                return $this->errorResponse('This type of transaction not allowed to Mark as Paid', 402);
        } else
            return $this->errorResponse('Transaction not Valid', 402);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manuallyVoidTransaction(Request $request)
    {
        $this->isPermissioned('bookings');

        $transactionInit = TransactionInit::with('booking_info')
            ->where('user_account_id',auth()->user()->user_account_id)
            ->where('id',$request->transaction_id)->first();

        if (!is_null($transactionInit)) {
            if (TransactionInitRepository::isTransactionStatusValidToManuallyVoid($transactionInit->payment_status)) {
                $description = 'Manually Voided by '.auth()->user()->name;
                resolve(TransactionDetail::class)::create(
                ['transaction_init_id'       => $transactionInit->id,
                'name'                       => auth()->user()->name,
                'user_account_id'            => auth()->user()->user_account_id,
                'user_id'                    => auth()->user()->id,
                'payment_processor_response' => $description,
                'payment_status'             => 1,
                'client_remarks'             => $description,
                'charge_ref_no'              => '',
                'error_msg'                  => $description,
                'amount'                     => $transactionInit->price,
                'order_id'                   => null,
                'payment_gateway_form_id'    => 0,
                'cc_info_id'                 => ($transactionInit->booking_info->cc_Infos != null
                            ? $transactionInit->booking_info->cc_Infos->last()->id : 0)]);

                $transactionInit->update(['payment_status' => TransactionInit::PAYMENT_STATUS_MANUALLY_VOID]);

                event(new PMSPreferencesEvent(auth()->user()->user_account, $transactionInit->booking_info,
                    $transactionInit->id,
                    config('db_const.user_preferences.preferences.PAYMENT_MANUALLY_VOIDED')));

                return $this->successResponse('Successfully Marked as Paid', 200, '');
            } else
                return $this->errorResponse('This type of transaction not allowed to Manually Void', 402);
        } else
            return $this->errorResponse('Transaction not Valid', 402);
    }

    public  function manuallyVoidAuth(Request $request)
    {
        $this->isPermissioned('bookings');
        /** Getting CC_Auth */
        $ccAuth = resolve(CreditCardAuthorization::class)::where('user_account_id', auth()->user()->user_account_id)
            ->where('id',$request->cc_auth_id)->first();
        if($ccAuth->status != CreditCardAuthorization::STATUS_VOID  ) {
            /** Getting Booking Infos */
            $bookingInfo = BookingInfo::where('id', $request->booking_info_id)->first();
            /** Getting Booking Propertyinfo */
            $propertyInfo = $bookingInfo->property_info;
            /** To get UserPaymentGateway*/
            $upg = new PaymentGatewayRepo();
            /** Getting Property PaymentGateway Sittings*/
            $gateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);
            /** Initlizing PaymentGateway Papper Class  */
            $pg = new PaymentGateway();

            if(!empty($ccAuth->token)) {
                try {
                    $cancelAuth = $pg->cancelAuthorization($ccAuth->transaction_obj, $gateway);
                      if($cancelAuth->status){
                        $ccAuth->update(['status' => CreditCardAuthorization::STATUS_VOID]);
                      }else{
                          return $this->errorResponse($cancelAuth->message, 402);
                      }
                } catch (GatewayException $e) {
                    Log::notice($e->getMessage(), ['File'=>BookingController::class, 'Function' => __FUNCTION__,"Booking Id: "=>$bookingInfo->id,"Property Id"=>$propertyInfo,'Account Id'=>auth()->user()->user_account_id]);
                    return $this->errorResponse($e->getMessage(), 402);
                }
            } else {
                $ccAuth->update(['status' => CreditCardAuthorization::STATUS_VOID]);
            }
        } else{
            return $this->errorResponse('Auth Already Voided', 402);
        }
        // Success If Not Failed
        return $this->successResponse('Successfully Voided', 200, '');
    }

    public function getBookingUpsellOrders(Request $request)
    {
        $this->validate($request, ['book_id' => 'required|integer']);
        return $this->upsell->upsellOrderList($request->book_id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function guestImageDelete(Request $request)
    {
        $booking_info = BookingInfo::where([
            ['id', $request->booking_id],
            ['user_account_id', \auth()->user()->user_account_id]
        ])->first();

        if (empty($booking_info))
            return $this->apiErrorResponse('Booking Info Id not valid', 422);

        $guest_image = $booking_info->guest_images->where('id', $request->id)->first();

        if (empty($guest_image))
            return $this->apiErrorResponse('Image Id not valid', 422);


        GuestImageDetail::create([
            'guest_image_id' => $guest_image->id,
            'booking_info_id' => $booking_info->id,
            'user_account_id' => $booking_info->user_account_id,
            'user_id' => \auth()->user()->id,
            'image' => $guest_image->image,
            'type' => $guest_image->type,
            'description' => $guest_image->description,
            'status' => $guest_image->status
        ]);

        if ($guest_image->delete()) {
            return $this->apiSuccessResponse(200,
                $booking_info->guest_images->sortByDesc('created_at'),
                'Image deleted.'
            );
        } else {
            return $this->apiErrorResponse('Something went wrong during image deletion.');
        }
    }
}
