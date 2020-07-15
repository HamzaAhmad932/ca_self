<?php

namespace App\Http\Controllers\v2\Guest;

use App\AuthorizationDetails;
use App\BookingInfo;
use App\CreditCardAuthorization;
use App\CreditCardInfo;
use App\Entities\Card as CardObject;
use App\Events\Emails\EmailEvent;
use App\Events\GuestMailFor3DSecureEvent;
use App\Events\MessageSent;
use App\Events\PMSPreferencesEvent;
use App\Exceptions\ClientSettingsException;
use App\Exceptions\UpdateCardException;
use App\GuestCommunication;
use App\GuestData;
use App\GuestImage;
use App\Http\Controllers\Controller;
use App\Http\Resources\BA\Precheckin\CreditCardStepResource;
use App\Http\Resources\BA\Precheckin\StepZeroResource;
use App\Http\Resources\BA\Precheckin\PaymentSummaryResource;
use App\Http\Resources\General\GuestPortal\GuestPortalResource;
use App\Http\Resources\General\Precheckin\AddOnServiceCollection;
use App\Http\Resources\General\Precheckin\StepOneResource;
use App\Http\Resources\General\Precheckin\StepSevenSelfieResource;
use App\Http\Resources\General\Precheckin\StepThreeCollection;
use App\Http\Resources\General\Precheckin\StepTwoResource;
use App\Http\Resources\General\Precheckin\SummaryStepResource;
use App\Http\Resources\GuestBookingDetailsResource;
use App\Jobs\GuestDocumentsStatusUpdateOnPMSJob;
use App\Jobs\PaymentConfirmationJob;
use App\Jobs\Runtime\BAChargeJobRuntime;
use App\Jobs\Runtime\CCReAuthJobRuntime;
use App\PropertyInfo;
use App\Repositories\BookingDetail\BookingDetailRepository;
use App\Repositories\Bookings\Bookings;
use App\Repositories\Guest\GuestInterface;
use App\Repositories\NotificationAlerts;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Repositories\Upsells\UpsellRepository;
use App\Rules\AdultsCountRule;
use App\Services\CapabilityService;
use App\Services\UpdateCard;
use App\Services\UpsellService;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\TermsAndCondition;
use App\Traits\Resources\General\GuestPortal;
use App\TransactionDetail;
use App\TransactionInit;
use App\Unit;
use App\UpsellCart;
use App\UserAccount;
use App\UserPaymentGateway;
use App\UserSettingsBridge;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class GuestController extends Controller
{
    use UpdateCard;
    use GuestPortal;
    use UpsellService;

    public $guest_portal;
    public $upsell;

    public function __construct(GuestInterface $guest_repository, UpsellRepository $upsellListingRepository)
    {
        $this->guest_portal = $guest_repository;
        $this->upsell = $upsellListingRepository;
    }

    public function getBookingAndGuestChatByBookingId($booking_id)
    {

        try {
            $booking_info = BookingInfo::find($booking_id);
            if (!empty($booking_info)) {
                $booking_source_form = $booking_info->bookingSourceForm;
                $generalPreferencesSettings = new ClientGeneralPreferencesSettings($booking_info->user_account_id);
                $guest_chat_status = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'), $booking_source_form) == '1' ? true : false;
                return [
                    'booking' => $booking_info,
                    'is_chat_active' => $guest_chat_status,
                    'property_name' => $booking_info->property_info->name,
                    'email' => $booking_info->property_info->property_email,
                    'tel' => $booking_info->user_account->contact_number
                ];
            }
        } catch (Exception $e) {
            $string = __FUNCTION__ . ' value pass ' . $booking_id;
            log_exception_by_exception_object($e, $string, 'error');
            abort(404);
        }
        abort(404);
    }

    public function preCheckin($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);
        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function getPrecheckinPageTitle($booking_info, $adjacent = null)
    {

        $adj = !empty($adjacent) ? ' | ' . $adjacent : '';
        return !empty($booking_info) ? $booking_info->property_info->name . $adj : 'ChargeAutomation' . $adj;
    }

    public function preCheckinStep1($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);

        $g_preference_setting = $this->checkRequiredStatusOfMetaInformation($id);

        if (!$g_preference_setting['required_basic_info']) {
            return redirect(URL::signedRoute('step_0', $id));
        }

        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_1', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function preCheckinStep2($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);
        $g_preference_setting = $this->checkRequiredStatusOfMetaInformation($id);

        if (!$g_preference_setting['required_arrival_info']) {
            return redirect(URL::signedRoute('step_0', $id));
        }

        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_2', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function preCheckinStep3($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);
        $g_preference_setting = $this->checkRequiredStatusOfMetaInformation($id);

        if (!$g_preference_setting['required_passport_scan'] && !$g_preference_setting['required_credit_card_scan']) {
            return redirect(URL::signedRoute('step_0', $id));
        }

        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_3', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function preCheckinAddOnStep($id)
    {

        //add on services step
        $header = $this->getBookingAndGuestChatByBookingId($id);
        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_4', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function preCheckinCreditCardStep($id)
    {

        //credit card step
        $header = $this->getBookingAndGuestChatByBookingId($id);
        $guest_experience_setting = $this->checkRequiredStatusOfMetaInformation($id);
        if (!CapabilityService::isAnyPaymentOrSecuritySupported($guest_experience_setting['booking_info'])) {
            return redirect(URL::signedRoute('step_0', $id));
        }
        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_5', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title,
            'pms_prefix'=> 'ba'
        ]);
    }

    public function preCheckinSelfieStep($id)
    {

        //summary step
        $header = $this->getBookingAndGuestChatByBookingId($id);
        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_6', [
            'booking_id' => $id,
            'type' => GuestImage::TYPE_SELFIE,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function preCheckinThankYou($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);
        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_thank_you', [
            'booking_id' => $id,
            'next_url' => URL::signedRoute('guest_portal', $id),
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function preCheckinSummaryStep($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);
        $page_title = $this->getPrecheckinPageTitle($header['booking']);

        return view('v2.guest.guest_pre_checkin.pre_checkin_step_7', [
            'booking_id' => $id,
            'header' => $header,
            'title' => $page_title
        ]);
    }

    public function guestDetail($id)
    {

        $raw = $this->guest_portal->getGuestDetail($id);

        if (!empty($raw)) {

//            StepZeroResource::withoutWrapping();
            return new StepZeroResource($raw);
        }

        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function guestDataStep1($id)
    {

        $raw = $this->guest_portal->guestDataByBookingId($id);

        StepOneResource::withoutWrapping();
        return new StepOneResource($id, $raw);
    }

    public function guestDataStep2($id)
    {

        $raw = $this->guest_portal->guestDataByBookingId($id);

        StepTwoResource::withoutWrapping();
        return new StepTwoResource($id, $raw);
    }

    public function guestDataStep4($id)
    {

        $raw = $this->guest_portal->getCreditCardAndAuthOfBooking($id);
        $upsell_orders = $this->upsell->getUpsellOrdersAndCart($id);

        if (!empty($raw)) {

            CreditCardStepResource::withoutWrapping();
            return new CreditCardStepResource($id, $raw, $upsell_orders);
        }

        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function getBaPaymentSummary($id){

        $raw = $this->guest_portal->getCreditCardAndAuthOfBooking($id);

        if (!empty($raw)) {

            PaymentSummaryResource::withoutWrapping();
            return new PaymentSummaryResource($raw);
        }

        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function getAddOnServices($id)
    {

        $available_addons = $this->upsell->upsellListing($id);
        if (!empty($available_addons)) {

            AddOnServiceCollection::withoutWrapping();
            return new AddOnServiceCollection($id, $available_addons);
        }

        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function saveAddonsCart(Request $request)
    {

        try {

            $bookingInfo = BookingInfo::with('user_account')->where('id', $request->booking_info_id)->first();
            $is_guest = Auth::guest();
            $allow_edit = true;
            $mode = Session::get('precheckin');

            if (!$is_guest && !empty($mode) && $mode[$request->booking_info_id]['status'] == 1) {
                $allow_edit = false;
            }

            if ($allow_edit) {

                $generalPreferencesSettings = new ClientGeneralPreferencesSettings($bookingInfo->user_account_id);
                $add_on_service = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.upsell'), $bookingInfo->bookingSourceForm) == '1' ? true : false;

                if (!$add_on_service) {
                    return $this->apiErrorResponse('Add on services are no longer available.', 422);
                }
                $this->validate($request, [
                    'booking_info_id' => 'required',
                    'upsell_listing_ids' => 'array'
                ]);

                if (!empty($request->upsell_listing_ids)) {

                    $status = $this->upsell->saveAddonCart($request->all());

                    if (!empty($status)) {
                        $meta = $this->getNextPageData(
                            $request->meta,
                            $request->booking_info_id
                        );

                        return $this->apiSuccessResponse(200, $meta, 'Cart updated successfully.');
                    }

                    return $this->apiErrorResponse('Something went wrong during cart update, please try again.', 422);
                }
                $this->upsell->removeBookingCart($request->booking_info_id);
            }
            $meta = $this->getNextPageData($request->meta, $request->booking_info_id);
            return $this->apiSuccessResponse(200, $meta, 'Cart is empty.');
        } catch (Exception $e) {
            dd($e->getMessage());

            log_exception_by_exception_object($e);

            return $this->apiErrorResponse('Something went wrong during cart update. please contact support.');
        }

    }

    public function purchaseAddOnService(Request $request)
    {

        try {

            $bookingInfo = BookingInfo::with('user_account')->where('id', $request->booking_info_id)->first();
            $generalPreferencesSettings = new ClientGeneralPreferencesSettings($bookingInfo->user_account_id);
            $add_on_service = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.upsell'), $bookingInfo->bookingSourceForm) == '1' ? true : false;

            if (!$add_on_service) {
                return $this->apiErrorResponse('Add on services are no longer available.', 422);
            }
            $this->validate($request, [
                'booking_info_id' => 'required',
                'upsell_listing_ids' => 'array'
            ]);

            if (!empty($request->upsell_listing_ids)) {

                $status = $this->upsell->saveAddonCart($request->all());
                if (empty($status)) {
                    return $this->apiErrorResponse('Something went wrong during cart update, please try again.', 422);
                }

                $ccInfo = resolve(CreditCardInfo::class)
                    ->where([['booking_info_id', $request->booking_info_id], ['is_vc', 0]])->latest('id')->limit(1)->first();


                if (empty($ccInfo)) {
                    return $this->apiErrorResponse('Payment method not found, please attach a payment method at "Payment card" tab.', 422);
                }

                if (!empty($ccInfo) && $ccInfo->customer_object->token == '' || in_array($ccInfo->status, [3, 4])) {
                    return $this->errorResponse("Can't charge! Credit card is invalid", 422);
                }

                if (!empty($request->amount_due) && $request->amount_due) {
                    try {
                        $status = $this->chargeUpsellPayment($request->booking_info_id, $ccInfo);
                        if (!$status['status']) {
                            return $this->apiErrorResponse('Please verify the settings.', $status['status_code']);
                        }

                        return $this->apiSuccessResponse(200, null, 'Payment charged successfully.');
                    } catch (GatewayException $e) {
                        if ($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {

                            return $this->apiErrorResponse($e->getMessage(), $e->getCode());
                        }
                        return $this->apiErrorResponse($e->getMessage(), 422);
                    } catch (Exception $e) {
                        return $this->apiErrorResponse($e->getMessage(), 422);
                    }
                }
            }
        } catch (GatewayException $e) {
            if ($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {
                return $this->apiErrorResponse('Please verify the settings.', $e->getCode());
            }
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(), 422);
        }
    }

    public function guestDataStep7($id)
    {

        StepSevenSelfieResource::withoutWrapping();
        return new StepSevenSelfieResource($id, '');

//        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function getGuestDataAndGuestImagesByBookingId($id)
    {

        $raw = $this->guest_portal->getGuestDataAndGuestImagesByBookingId($id);

        if (!empty($raw)) {

            SummaryStepResource::withoutWrapping();
            return new SummaryStepResource($id, $raw);
        }

        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function precheckinComplete(Request $request)
    {

        $booking = BookingInfo::where('id', $request->booking_info_id)->first();

        if (!empty($booking)) {

            if (!$request->meta['is_guest'] && $request->meta['read_only_mode'] == 1) {
                $next_page_meta = $this->getNextPageData($request->meta, $request->booking_info_id);
                $booking->pre_checkin_status = 0;
                return $this->apiSuccessResponse(200, $next_page_meta, 'Read-only mode pre-checkin visited.');
            }
            else{
                $booking->pre_checkin_status = 1;
            }

            if ($request->has('tac')) {

                /** If Required Terms and Conditions Are Accepted By Guest
                 * If Not Then It Will Stop any Further Processing and
                 * Throws Errors To Guest To Accept given Terms and Conditions  */
                if ($request->tac['has_required_tac'] && !$request->tac['is_accepted_tac']) {
                    return $this->apiErrorResponse("You Should Accept Our Terms and Conditions");
                }
                else{
                    $booking->terms_and_conditions_accepted = $request->tac['is_accepted_tac'];

                    event(new PMSPreferencesEvent($booking->user_account, $booking, 0,
                        config('db_const.user_preferences.preferences.TERMS_AND_CONDITIONS_COMPLETE')));
                }
            }

            $booking->save();

            $guest_data = GuestData::where('booking_id', $request->booking_info_id)->first();

            if (!empty($guest_data)) {
                event(new EmailEvent(config('db_const.emails.heads.pre_checkin_completed.type'), $guest_data->id));
                event(new PMSPreferencesEvent($booking->user_account, $booking, 0,
                    config('db_const.user_preferences.preferences.PRE_CHECKIN_COMPLETE')));
            }

            $next_page_meta = $this->getNextPageData($request->meta, $request->booking_info_id);
            return $this->apiSuccessResponse(200, $next_page_meta, 'Pre-checkin verified successfully.');

        }
        else {
            return $this->apiErrorResponse('Data not found.', 404);
        }
    }

    public function guestDataStep3($id)
    {

        $raw = $this->guest_portal->getGuestImagesByBookingId($id);

        return new StepThreeCollection($id, $raw);
    }

    public function getPreviousStepMeta(Request $request)
    {

        $next_url = '';
        foreach ($request->meta['routes'] as $key => $meta_routes) {
            $back = 1;
            if (!empty($request->meta['routes'][$key - 1]['default_step_name'])
                && $meta_routes['default_step_name'] == 'step_' . $request->meta['current_step']) {

                if ($request->meta['routes'][$key - 1]['default_step_name'] == 'step_5'
                    && !$request->meta['routes'][$key - 1]['show_step']) {
                    $back = 2; // Move back to Add-ons if Credit Card Step hidden after add-ons.
                }

                $next_url = Url::signedRoute($request->meta['routes'][$key - $back]['default_step_name'], $request->booking_id);
                break;
            }
        }

        return [
            'meta' => [
                'current_step' => $request->meta['current_step'],
                'next_link' => $next_url,
                'is_completed' => true
            ]
        ];
    }

    public function getVerificationStepMetaInfo(Request $request)
    {

        $meta = $this->getNextPageData($request->meta, $request->booking_id);
        return $this->apiSuccessResponse(200, $meta, 'verified.');

    }

    public function getNextPageMeta(Request $request)
    {

        return $this->getNextPageData($request->meta, $request->booking_id);
    }

    public function getNextPageData($meta, $booking_id)
    {

        $next_url = '';

        // No Upsell Purchased then don't show Credit Card Step after Add-ons move to next step.
        if ($meta['current_step'] == 4
            && UpsellCart::where('booking_info_id', $booking_id)->count() == 0) {

            //dump($meta);
            $step = collect($meta['routes'])
                ->where('default_step_name', '!=', 'step_5')
                ->where('default_step_name', '!=', 'step_' . $meta['current_step'])
                ->where('default_step_num', '>', $meta['current_step'])->first()['default_step_name'];

            $next_url = Url::signedRoute($step, $booking_id);

        } else {
            // Upsell Purchased then show Credit Card Step after Add-ons
            foreach ($meta['routes'] as $key => $meta_routes) {
                if (!empty($meta['routes'][$key + 1]['default_step_name'])
                    && $meta_routes['default_step_name'] == 'step_' . $meta['current_step']) {
                    $next_url = Url::signedRoute($meta['routes'][$key + 1]['default_step_name'], $booking_id);
                    break;
                }
            }
        }

        return [
            'meta' => [
                'current_step' => $meta['current_step'],
                'next_link' => $next_url,
                'is_completed' => true
            ]
        ];
    }

    public function changeMode(Request $request)
    {

        if (!Auth::guest()) {
            $modes = Session::get('precheckin');
            $mode = $modes[$request->booking_id];
            if (!empty($mode)) {
                $modes[$request->booking_id]['status'] = $request->mode_value;
                $modes[$request->booking_id]['created_at'] = now();
            }

            Session::put(['precheckin' => $modes]);
            Session::save();
            $data = Session::get('precheckin')[$request->booking_id];

            return $this->apiSuccessResponse(200, $data, 'Mode Updated.');
        }
    }


    public function guestPortal($id)
    {

        $header = $this->getBookingAndGuestChatByBookingId($id);

        $booking = BookingInfo::where('id', $id)->with('guest_data')->first();

        //Guest Last seen
        if (Auth::guest()) {

            if ($booking->pre_checkin_status == 0) {
                $step_num = !empty($booking->guest_data) ? $booking->guest_data->step_completed : 0;
                $step_num = !empty($step_num) ? $step_num : 0;
                return redirect(URL::signedRoute('step_' . $step_num, $id));
            }
        }

        return view('v2.guest.guest_portal.guest_portal', [
            'booking_id' => $id,
            'header' => $header
        ]);
    }

    public function fetchGuestPortalData($id)
    {

        $raw = $this->guest_portal->getGuestDataAndGuestImagesAndTransactionsByBookingId($id);

        if (!empty($raw)) {

            GuestPortalResource::withoutWrapping();
            return new GuestPortalResource($id, $raw);
        }

        return $this->apiErrorResponse('Data not found.', 404);
    }

    public function fetchAddCardTerminalData(Request $request)
    {

        try {

            $bookingInfo = BookingInfo::where('id', $request->get('booking_id'))->first();

            $upg = new PaymentGateways();
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);

            $paymentGateway = new PaymentGateway();
            $terminalData = $paymentGateway->getTerminal($userPaymentGateway);

            $data = [];
            $data['pgTerminal'] = $terminalData;
            $data['pgTerminal']['first_name'] = $bookingInfo->guest_name;
            $data['pgTerminal']['last_name'] = $bookingInfo->guest_last_name;
            $data['pgTerminal']['booking_id'] = $bookingInfo->id;
            $data['pgTerminal']['with3DsAuthentication'] = true;
            $data['pgTerminal']['show_authentication_button'] = false;

            return $this->apiSuccessResponse(200, $data);

        } catch (GatewayException $e) {
            Log::error($e->getMessage(),
                [
                    'File' => __FILE__,
                    'Function' => __FUNCTION__,
                    'BookingInfoId' => $request->get('booking_id', 0),
                    'Stack' => $e->getTraceAsString()]);
            return $this->errorResponse($e->getDescription(), 422);
        }

    }

    public function guest_booking_detailsv1(Request $request, $id)
    {
        /*if (!$request->hasValidSignature())
           abort(401, 'This link is not valid.');*/

        $showVerifyAlert = false;
        $bookingInfo = BookingInfo::findOrFail($id);
        $guest_data = GuestData::where('booking_id', $id)->first();

        $repoBookings = new Bookings($bookingInfo->user_account->id);

        $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->where('pms_id', $bookingInfo->pms_id)->first();

        $currencyCode = $repoBookings->getCurrencyCode($bookingInfo, $propertyInfo);
        $tz = $repoBookings->getPropertyTimezone($bookingInfo->user_account, $propertyInfo);

        if (Auth::guest()) {
            $verification_image = GuestImage::where('booking_id', $bookingInfo->id)->first();//->toArray()
            if ($verification_image != null) {
                $verification_image = $verification_image->toArray();
            }
            $countries = countries();
            if ($bookingInfo->pre_checkin_status == '0') {
                return view('guest_panel_v2.booking-detail', [
                    'booking_info' => $bookingInfo,
                    'guest_data' => is_null($guest_data) ? 0 : $guest_data,
                    'show_cc' => $bookingInfo->credit_card_authorization->isEmpty() ? 0 : 1,
                    'card' => $bookingInfo->cc_Infos->last(),
                    'currencySymbol' => $repoBookings->getCurrencySymbolByCurrencyCode($currencyCode),
                    'verification_image' => json_encode($verification_image),
                    'timezone' => $tz,
                    'c' => $countries
                ]);
            }
        }
        $bookingDetails = json_encode(new GuestBookingDetailsResource(collect(['bookingInfo' => $bookingInfo, 'propertyInfo' => $propertyInfo])));


        //        'reTryURL' => URL::temporarySignedRoute('guest_booking_manual_retry', now()->addHour(3), ['id' => $this->collection['bookingInfo']->id]),
        //            'chargeURL' => URL::temporarySignedRoute('charge', now()->addHour(1), ['id' => $this->collection['bookingInfo']->id]),
        //            'authRoute' => route('authorizeWithTokenRoute'),
        //            'updateCardByGuest' => route('update-card-by-guest'),

        $authRoute = URL::signedRoute('authorizeWithTokenRoute');
        $update_card_by_guest = URL::signedRoute('update-card-by-guest-panel');

        $data = [
            'auth-url' => $authRoute,
            'update-card-by-guest' => $update_card_by_guest
        ];


        if ($bookingInfo->is_vc == 'CC')
            $showVerifyAlert = $this->checkVerificationUpdation($bookingInfo);

        return view('guest.bookings.booking_details', [
            'data' => $data,
            'bookingDetails' => $bookingDetails,
            'guestData' => $guest_data,
            'showCardUpdateAlert' => ($bookingInfo->pms_booking_status == 0 ? false : $this->checkCardUpdation($bookingInfo)),
            'showVerifyAlert' => ($bookingInfo->pms_booking_status == 0 ? false : $showVerifyAlert),
            'currencyCode' => $currencyCode,
            'currencySymbol' => $repoBookings->getCurrencySymbolByCurrencyCode($currencyCode),
            'timezone' => $tz,
        ]);

    }

    public function guest_booking_details(Request $request, $id) #Version 2
    {
//        if (!$request->hasValidSignature())
//            abort(401, 'This link is not valid.');

        $showVerifyAlert = false;
        $bookingInfo = BookingInfo::findOrFail($id);
        $guest_data = GuestData::where('booking_id', $id)->first();

        $repoBookings = new Bookings($bookingInfo->user_account->id);

        $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->where('pms_id', $bookingInfo->pms_id)->first();

        $currencyCode = $repoBookings->getCurrencyCode($bookingInfo, $propertyInfo);
        $tz = $repoBookings->getPropertyTimezone($bookingInfo->user_account, $propertyInfo);

        if (Auth::guest()) {
            $verification_image = GuestImage::where('booking_id', $bookingInfo->id)->first();//->toArray()
            if ($verification_image != null) {
                $verification_image = $verification_image->toArray();
            }
            $countries = countries();
            if ($bookingInfo->pre_checkin_status == '0') {
                return view('guest_panel_v2.booking-detail', [
                    'booking_info' => $bookingInfo,
                    'guest_data' => is_null($guest_data) ? 0 : $guest_data,
                    'show_cc' => $bookingInfo->credit_card_authorization->isEmpty() ? 0 : 1,
                    'card' => $bookingInfo->cc_Infos->last(),
                    'currencySymbol' => $repoBookings->getCurrencySymbolByCurrencyCode($currencyCode),
                    'verification_image' => json_encode($verification_image),
                    'timezone' => $tz,
                    'c' => $countries
                ]);
            }
        }
        $bookingDetails = json_encode(new GuestBookingDetailsResource(collect(['bookingInfo' => $bookingInfo, 'propertyInfo' => $propertyInfo])));
        $currencyCode = $repoBookings->getCurrencyCode($bookingInfo, $propertyInfo);

        //        'reTryURL' => URL::temporarySignedRoute('guest_booking_manual_retry', now()->addHour(3), ['id' => $this->collection['bookingInfo']->id]),
        //            'chargeURL' => URL::temporarySignedRoute('charge', now()->addHour(1), ['id' => $this->collection['bookingInfo']->id]),
        //            'authRoute' => route('authorizeWithTokenRoute'),
        //            'updateCardByGuest' => route('update-card-by-guest'),

        $authRoute = URL::signedRoute('authorizeWithTokenRoute');
        $update_card_by_guest = URL::signedRoute('update-card-by-guest-panel');

        $data = [
            'auth-url' => $authRoute,
            'update-card-by-guest' => $update_card_by_guest
        ];
        if ($bookingInfo->is_vc == 'CC')
            $showVerifyAlert = $this->checkVerificationUpdation($bookingInfo);

        return view('guest_v2.bookings.booking_detailsv2', [
            'bookingDetails' => $bookingDetails,
            'bookingInfo' => $bookingInfo,
            'guestData' => $guest_data,
            'showCardUpdateAlert' => ($bookingInfo->pms_booking_status == 0 ? false : $this->checkCardUpdation($bookingInfo)),
            'showVerifyAlert' => ($bookingInfo->pms_booking_status == 0 ? false : $showVerifyAlert),
            'currencyCode' => $currencyCode,
            'currencySymbol' => $repoBookings->getCurrencySymbolByCurrencyCode($currencyCode),
            'timezone' => $tz,
            'data' => $data,
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateGuestData(Request $request)
    {
        try {
//          if (!$request->hasValidSignature()){
//              return response()->json([
//                  'status'=> false,
//                  'status_code'=> 403,
//                  'message'=> 'Unauthorized access.'
//              ]);
//          }
            //Read-only mode '0' means OFF and '1' means ON
            $is_guest = Auth::guest();
            $allow_edit = true;
            $bookingInfo = BookingInfo::find($request->booking_info_id);
            if (empty($bookingInfo)) {
                return $this->apiErrorResponse('Booking not found.', 404);
            }
            $mode = Session::get('precheckin');

            if (!$is_guest && !empty($mode) && $mode[$request->booking_info_id]['status'] == 1) {
                $allow_edit = false;
            }

            if ($allow_edit) {
                $g_preference_setting = $this->checkRequiredStatusOfMetaInformation($request->booking_info_id);

                if ($request->current_tab == 1) {
                    //validations
                    if (!$g_preference_setting['required_basic_info']) {
                        return $this->apiErrorResponse('Basic information is disabled by Property administration, Please refresh page.');
                    }
                    $req = new Request($request->step_1);
                    $this->validate($req, [
                        "email" => 'required|email',
                        "phone" => 'required|regex:^[0-9-()\s]+$^|min:5',
                        "adults" => ['required', new AdultsCountRule()]
                    ], [
                        'email.required' => 'Email is required',
                        'email.email' => 'Input must be valid email',
                        'phone.required' => 'Phone is required',
                        'phone.min' => 'Phone contain atleast 5 numbers',
                        'phone.regex' => 'Enter valid phone number',
                    ]);
                }

                if ($request->current_tab == 2) {
                    $req = new Request($request->step_2);
                    $this->validate($req, [
                        "arriving_by" => 'required|alpha',
                        "arrival_time" => 'required|date_format:H:i',
                        "plane_number" => 'required_if:arriving_by,==,Plane',
                        'other' => 'required_if:arriving_by,==,Other'
                    ], [
                        'arriving_by.required' => 'Arriving by is required',
                        'arriving_by.alpha' => 'Arriving by should contain only alphabet',
                        'arrival_time.required' => 'Arrival time is required',
                        'plane_number.required_if' => 'Flight number is required',
                        'other.required_if' => 'Other detail is required',
                        'plane_number.regex' => 'Flight number should contain numbers and alphabet',
                        'arrival_time.date_format' => 'Arrival time date format should be H:i',
                    ]);
                }

                // Find guest data

                $remoteBookingDataUpdate = new \stdClass();

                $guest_data = GuestData::where('booking_id', $request->booking_info_id)->first();
                $guest_data = ($guest_data == true) ? $guest_data : new GuestData();

                if (!empty($request->booking_info_id)) {
                    $guest_data->booking_id = $request->booking_info_id;
                }

                if ($request->current_tab == 1) {

                    // if(!empty($request->data['basic_info']['full_name'])){
                    //     $guest_data->name = $request->data['basic_info']['full_name'];
                    // }

                    if (!empty($request->step_1['email'])) {
                        $guest_data->email = $request->step_1['email'];
                        $remoteBookingDataUpdate->guest_email = $request->step_1['email'];
                        $bookingInfo->guest_email = $request->step_1['email'];
                    }
                    if (!empty($request->step_1['phone'])) {
                        $guest_data->phone = $request->step_1['phone'];
                        $remoteBookingDataUpdate->phone = $request->step_1['phone'];
                        $bookingInfo->guest_phone = $request->step_1['phone'];
                    }
                    if (!empty($request->step_1['code'])) {
                        $guest_data->country_code = $request->step_1['code'];
                    }
                    if (!empty($request->step_1['adults'])) {
                        $guest_data->adults = $bookingInfo->num_adults = $request->step_1['adults'];
                    }
                    $guest_data->childern = $request->step_1['childern'];
                }

                if ($request->current_tab == 2) {

                    if (!$g_preference_setting['required_arrival_info']) {
                        return $this->apiErrorResponse('Arrival information is disabled by Property administration, Please refresh page.');
                    }

                    if (!empty($request->step_2['arriving_by'])) {
                        $guest_data->arriving_by = $request->step_2['arriving_by'];

                        if ($guest_data->arriving_by == 'Plane' && !empty($request->step_2['plane_number'])) {
                            $guest_data->plane_number = $request->step_2['plane_number'];
                        }
                    }
                    if ($guest_data->arriving_by == 'Other' && !empty($request->step_2['other'])) {
                        $guest_data->other_detail = $request->step_2['other'];
                    }

                    if (!empty($request->step_2['arrival_time'])) {

                        $guest_data->arrivaltime = $request->step_2['arrival_time'];
                        $remoteBookingDataUpdate->arrival_time = $request->step_2['arrival_time'];

                    }

                }

                if (empty($guest_data->step_completed) || $guest_data->step_completed < $request->current_tab) {

                    $guest_data->step_completed = $request->current_tab;
                }
                $guest_data->save();
                $bookingInfo->save();

                if (!empty((array)$remoteBookingDataUpdate)) {
                    BookingDetailRepository::updateBasicInfoAtBA($bookingInfo->user_account, $bookingInfo->property_info, $bookingInfo, $remoteBookingDataUpdate);
                }
            }

            $data = $this->getNextPageData($request->meta, $bookingInfo->id);


            return $this->successResponse('Data saved successfully.', 200, $data);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Exception $e) {

            Log::error($e->getMessage(),
                [
                    'booking_info_id' => $request->booking_info_id,
                    'File' => __FILE__,
                    'Function' => __FUNCTION__,
                    "Stacktrace" => $e->getTraceAsString()
                ]);

            if ($e instanceof PmsExceptions) {
                return $this->apiErrorResponse('Please contact your host or try again later', 400);
            } else {
                return $this->apiErrorResponse('Something went wrong. Please try again', 400);
            }
        }
    }

    public function precheckinVerify(Request $request)
    {

        $booking = BookingInfo::where('id', $request->booking_info_id)->update(['pre_checkin_status' => 1]);

        if (!$booking) {
            return $this->errorResponse('Something wrong with verification.', 422);
        } else {
            return response()->json([
                'message' => 'Pre-checkin verified successfully.',
                'status_code' => 200,
                'status' => true,
                'booking' => $booking
            ]);
        }


    }

    public function updateCardByGuest(Request $request)
    {

        $is_guest = Auth::guest();
        $allow_edit = true;
        $mode = Session::get('precheckin');

        if ($request->requested_by == 'pre_checkin') {
            $next_page_meta = $this->getNextPageData($request->meta, $request->booking_info_id);
        }

        if (!$is_guest && !empty($mode) && $mode[$request->booking_info_id]['status'] == 1) {
            $allow_edit = false;
        }

        if ($allow_edit) {

            if ($request->apply_validation) {

                if (isset($request->allow_to_go) && !$request->allow_to_go) {
                    return $this->apiErrorResponse('Credit card update is required, please update your credit card.', 422);
                }

                $req = new Request($request->card);

                $this->validate($req, [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'payment_method' => 'required'
                ], [
                    'first_name.required' => 'First name is required.',
                    'last_name.required' => 'Last name is required.',
                    'payment_method.required' => 'Card number is required.',
                ]);

                $card_object = new CardObject();
                $card_object->full_name = $request->card['first_name'] . ' ' . $request->card['last_name'];
                $card_object->token = $request->card['payment_method'];
                $card_object->first_name = $request->card['first_name'];
                $card_object->last_name = $request->card['last_name'];

                try {

                    $card = $this->updateCard($card_object, $request->booking_info_id);

                    if (!$card) {
                        return $this->apiErrorResponse('Something went wrong during card update!', 422);
                    }

                    if (!empty($request->amount_due) && $request->amount_due) {
                        $this->chargeUpsellPayment($request->booking_info_id, $card);
                    }

                    if ($request->requested_by == 'pre_checkin') {

                        $guest_data = GuestData::where('booking_id', $request->booking_info_id)->first();
                        if (!empty($guest_data)) {
                            $guest_data->step_completed = $request->current_tab;
                            $guest_data->save();
                        }
                        $next_page_meta = $this->getNextPageData($request->meta, $request->booking_info_id);

                        return $this->apiSuccessResponse(200, $next_page_meta, 'Card updated successfully.');

                    } else {

                        return $this->apiSuccessResponse(200, $card, 'Card updated successfully.');
                    }

                } catch (GatewayException $e) {
                    if ($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {

                        return $this->apiErrorResponse('Please verify the settings.', $e->getCode());
                    }
                    return $this->apiErrorResponse($e->getMessage(), 422);
                } catch (UpdateCardException $e) {
                    return $this->apiErrorResponse($e->getMessage(), 422);
                } catch (Exception $e) {
                    return $this->apiErrorResponse($e->getMessage(), 422);
                }
            }

            if (!empty($request->amount_due) && $request->amount_due) {

                $ccInfo = CreditCardInfo::where([['booking_info_id', $request->booking_info_id], ['is_vc', 0]])->latest('id')->limit(1)->first();

                if (empty($ccInfo)) {
                    return $this->apiErrorResponse('Payment method not found, please attach a payment method by clicking ', 422);
                }

                try {
                    $status = $this->chargeUpsellPayment($request->booking_info_id, $ccInfo);

                    if (!$status['status']) {
                        return $this->apiErrorResponse('Please verify the settings.', $status['status_code']);
                    }
                    return $this->apiSuccessResponse(200, $next_page_meta, 'Payment successfull.');

                } catch (GatewayException $e) {
                    if ($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {

                        return $this->apiErrorResponse($e->getMessage(), $e->getCode());
                    }
                    return $this->apiErrorResponse($e->getMessage(), 422);
                } catch (Exception $e) {
                    return $this->apiErrorResponse($e->getMessage(), 422);
                }
            }
        }

        if (empty($next_page_meta))
            $next_page_meta = [];

        return $this->apiSuccessResponse(200, $next_page_meta);
    }

    public function updateCard1(Request $request)
    {
        try {

            $validator = Validator::make($request->data, [
                'card_info.cc_email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Must provide a valid Email !', 422);
            }

            if ($request->data['card_info']['cc_name'] == null) {

                return $this->errorResponse('Full name required', 422);

            } elseif (strpos($request->data['card_info']['cc_name'], ' ') === false) {

                return $this->errorResponse('Last Name is missing in full name', 422);

            }

            $decryptedTran = decrypt($request->data['transaction']);

            $bookingInfo = BookingInfo::where('id', $decryptedTran['booking_info_id'])->first();

            $upg = new PaymentGateways();
            /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);

            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo);

            $dt = Carbon::createFromFormat('m/y', $request->data['card_info']['cc_exp']);


            $card = new Card();
            $card->cardNumber = str_replace(' ', '', $request->data['card_info']['cc_num']);
            $card->expiryMonth = $dt->month;
            $card->expiryYear = $dt->year;
            $card->cvvCode = $request->data['card_info']['cc_cvv'];
            $firstName = '';
            $lastName = '';

            if (isset($request->data['card_info']['cc_name']) && $request->data['card_info']['cc_name'] != '') {
                $split = explode(' ', $request->data['card_info']['cc_name']);
                $firstName = $split[0];
                unset($split[0]);
                $lastName = trim(implode($split, ' '));
            }

            $card->firstName = $firstName == '' ? $bookingInfo->guest_name : $firstName;
            $card->lastName = $lastName == '' ? $bookingInfo->guest_last_name : $lastName;
            $card->eMail = !empty($request->data['card_info']['cc_email']) ? $request->data['card_info']['cc_email'] : $bookingInfo->guest_email;

            PaymentGateways::addMetadataInformation($bookingInfo, $card, GuestController::class);

            try {
                $pg = new PaymentGateway();
                $resp = $pg->addAsCustomer($card, $userPaymentGateway);

            } catch (GatewayException $e) {

                return $this->errorResponse($e->getMessage(), 422);
            }

            if ($resp->succeeded) {

                $cc_info = CreditCardInfo::create([
                        'booking_info_id' => $bookingInfo->id,
                        'user_account_id' => $bookingInfo->user_account_id,
                        'card_name' => $card->firstName . ' ' . $card->lastName,
                        'f_name' => $resp->first_name,
                        'l_name' => $resp->last_name,
                        'cc_last_4_digit' => $resp->last_four_digits,
                        'cc_exp_month' => $dt->month,
                        'cc_exp_year' => $dt->year,
                        //'cc_cvc_num'=>$card->cvvCode,
                        'customer_object' => json_encode($resp),
//                    'system_usage' => Card::encrypt($card),
                        'system_usage' => '',
                        'auth_token' => $resp->token,
                        'attempts' => 1,
                        'is_vc' => 0,
                        'status' => 1]
                );

                /***********************************************************
                 * Updating Transaction Init Entries (turning lets_process 1)
                 */
                //if($bookingInfo->check_in_date > Carbon::now()->toDateTimeString()){
                $trans = $bookingInfo->transaction_init
                    ->whereIn('payment_status', [
                        TransactionInit::PAYMENT_STATUS_PENDING,
                        TransactionInit::PAYMENT_STATUS_REATTEMPT,
                        TransactionInit::PAYMENT_STATUS_FAIL
                    ])->where('transaction_type', '<', 4);


                /**
                 * Below check was added if customer object of VC took sometime to create and in that time
                 * Client or Guest add new card then transaction int's letsProcess becomes 1 and it is tried to charge.
                 * To avoid this scenario below check is added.
                 *
                 * isVc, skipFlagForBookingAmountTransactionInit
                 */
                $isVc = $bookingInfo->is_vc == BS_Generic::PS_VIRTUAL_CARD;
                $skipFlagForBookingAmountTransactionInit = false;
                $meta = null;

                if ($isVc) {
                    $ccInfoVc = $bookingInfo->cc_Infos->where('is_vc', 1)->first();
                    $skipFlagForBookingAmountTransactionInit = empty($ccInfoVc->auth_token);
                    $meta = new PaymentTypeMeta();
                }

                foreach ($trans as $tran) {

                    if ($skipFlagForBookingAmountTransactionInit) {
                        if (in_array($tran->transaction_type, $meta->getAllChargeTypes()))
                            continue;
                    }

                    $tran->lets_process = 1;

                    if (($tran->payment_status == TransactionInit::PAYMENT_STATUS_FAIL) || ($tran->payment_status == TransactionInit::PAYMENT_STATUS_REATTEMPT)) {
                        $tran->payment_status = TransactionInit::PAYMENT_STATUS_REATTEMPT;
                        $tran->attempt = 0;
                        $tran->next_attempt_time = now()->addMinute('-5')->toDateTimeString();
                    }

                    $tran->decline_email_sent = 0;
                    $tran->save();

                }

                //}
                $auths = $bookingInfo->booking_auths();
                foreach ($auths as $auth) {
                    if (($auth->status == CreditCardAuthorization::STATUS_FAILED) || ($auth->status == CreditCardAuthorization::STATUS_REATTEMPT)) {
                        $auth->next_due_date = now()->addMinute('-5')->toDateTimeString();
                        $auth->status = CreditCardAuthorization::STATUS_REATTEMPT;

                    } elseif ($auth->status == CreditCardAuthorization::STATUS_MANUAL_PENDING) {
                        $pt = new PaymentTypeMeta();
                        $auth->next_due_date = now()->addMinute('-5')->toDateTimeString();
                        $auth->status = CreditCardAuthorization::STATUS_PENDING;
                        $auth->type = $pt->getSecurityDepositAutoAuthorize();
                    }

                    $auth->cc_info_id = $cc_info->id;
                    $auth->decline_email_sent = 0;
                    $auth->save();
                }


                $msg = "Card Added Successfully.";
                return $this->successResponse($msg, 200);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => GuestController::class, 'Stack' => $e->getTraceAsString()]);
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function checkCardUpdation(BookingInfo $booking_info)
    {

        if (($booking_info->is_vc == BS_Generic::PS_BANK_TRANSFER) && ($booking_info->credit_card_authorization->count() == 0))
            return false;

        $flag = false;
        $cc_info = $booking_info->cc_Infos()
            ->where('is_vc', 0)
            ->latest('id')
            ->limit(1)
            ->with('transaction_details')
            ->with('ccauth')
            ->first();


        if (!is_null($cc_info)) {

            if ($cc_info->customer_object->token == '' or $cc_info->customer_object->token == null) {
                $flag = true;
            }

            foreach ($cc_info->transaction_details as $td) {
                if ($td->payment_status == 0 or $td->payment_status == 4) {
                    $flag = true;
                }
            }
            foreach ($cc_info->ccauth as $auth) {
                if ($auth->status == 5 or $auth->status == 7) {
                    $flag = true;
                }
            }

        }
//        elseif($booking_info->is_vc == BS_Generic::PS_VIRTUAL_CARD && $cc_info == null) {
//            /**
//             * Booking is VC and No, card had been added previously.
//             */
//            $flag = true;
//        }

        return $flag;
    }

    public function checkVerificationUpdation(BookingInfo $booking_info)
    {

        $flag = false;
        if (!$booking_info->guest_images->isEmpty()) {
            foreach ($booking_info->guest_images as $img) {
                if ($img->status == '2') {
                    $flag = true;
                }
            }

        } else {
            $flag = true;
        }

        return $flag;
    }

    /**
     * Fetch all BookingInfoId wise messages
     *
     * @return Message
     */
    public function fetchMessages(Request $request, $userId, $bookingInfoId)
    {
        if ($userId === 'undefined') {

            return GuestCommunication::with('user', 'booking_info')->where('booking_info_id', $bookingInfoId)->get();
        }

        return GuestCommunication::with('user', 'booking_info')->where('booking_info_id', $bookingInfoId)->get();
    }


    /**
     * Fetch all BookingInfoId wise messages
     *
     * @return Message
     */
    public function readMessages($userId, $bookingInfoId)
    {
        if ($userId === 'undefined') {


            GuestCommunication::where('booking_info_id', $bookingInfoId)->update(['message_read_by_guest' => 1]);
            return ['status' => 'Message Read Successfully by guest!'];
        }

        GuestCommunication::where('booking_info_id', $bookingInfoId)->update(['message_read_by_user' => 1]);
        return ['status' => 'Message Read Successfully by user!'];
    }

    /**
     * Persist message to database
     *
     * @param Request $request
     * @return Response
     */
    public function sendMessage(Request $request)
    {
        $name = $request->user['name'];

        if (!$name) {
            $name = 'Guest';
        }

        $booking_info_id = $request->booking_info_id;
        $guest_name = BookingInfo::find($booking_info_id)->guest_name;

        if (!$guest_name) {
            $guest_name = 'Guest';
        }

        if ($name === $guest_name) {
            $user_account_id = BookingInfo::find($booking_info_id)->user_account_id;

            $message = GuestCommunication::create(['user_account_id' => $user_account_id, 'booking_info_id' => $booking_info_id,
                'guest_name' => $guest_name, 'message' => $request->input('message'), 'message_read_by_guest' => 1]);

            $user = array('name' => $guest_name);
            broadcast(new MessageSent($user, $message))->toOthers();

            return ['status' => 'Message Sent!'];

        }


        $user = Auth::user();
        $user_id = Auth::user()->id;
        $user_account_id = BookingInfo::find($booking_info_id)->user_account_id;


        $message = GuestCommunication::create(['user_id' => $user_id, 'user_account_id' => $user_account_id, 'booking_info_id' => $booking_info_id,
            'message' => $request->input('message'), 'message_read_by_user' => 1]);


        broadcast(new MessageSent($user, $message))->toOthers();

        return ['status' => 'Message Sent!'];

    }


    public function guestchat()
    {
        return view('guest.chat.chat');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function communication(Request $request)
    {
        try {

            $msg = trim($request->get('msgtext'));

            if ($msg == null)
                $this->errorResponse(trans('messages.guest.chat.error.empty_message'), 500);

            /**
             * @var BookingInfo
             */
            $bookingInfo = BookingInfo::find($request->get('bookingInfoId'));
            if ($bookingInfo->pms_booking_status == 0) //Cancelled
                return $this->errorResponse(trans('messages.guest.chat.error.booking_cancelled'), 500);
            if ($bookingInfo->check_out_date <= now()->toDateTimeString())
                return $this->errorResponse(trans('messages.guest.chat.error.checkout_date_passed'), 500);

            $guestChat = new ClientGeneralPreferencesSettings($bookingInfo->user_account_id);
            $guestChatStatus = $guestChat->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'), $bookingInfo->bookingSourceForm);

            if ($guestChatStatus == 1) {
                $chat = GuestCommunication::create([

                    'user_id' => $bookingInfo->user_id,
                    'user_account_id' => $bookingInfo->user_account_id,
                    'booking_info_id' => $request->get('bookingInfoId'),
                    'is_guest' => $request->get('is_guest'),
                    'alert_type' => 'chat',
                    'pms_booking_id' => $bookingInfo->pms_booking_id,
                    'message' => $request->get('msgtext'),
                    'message_read_by_guest' => 0,
                    'message_read_by_user' => 0,]);
                if ($chat)
                    event(new EmailEvent(config('db_const.emails.heads.new_chat_message.type'), $chat->id));
                return $this->apiSuccessResponse(200, [$msg], 'success');
            } else {
                return $this->errorResponse(trans('messages.guest.chat.error.chat_not_active'), 500);
            }
        } catch (Exception $exception) {
            return $this->errorResponse(trans('messages.guest.chat.error.failed'), 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allmsgs(Request $request)
    {
        $bookingInfoId = $request->bookingInfoId;
        if (isset($request->lastSeenMessageId) && ($request->lastSeenMessageId != 0))
            GuestCommunication::where(
                [
                    ['booking_info_id', $bookingInfoId],
                    ['id', '<', $request->lastSeenMessageId + 1],
                    ['alert_type', 'chat'],
                ]
            )->update(['message_read_by_guest' => 1]);


        $bookingInfo = BookingInfo::where('id', $request->get('bookingInfoId'))
            //->with('room_info')
            ->first();
        $bookingInfo->room_info;
        $user_account_id = $bookingInfo->user_account_id;

        $guestChat = new ClientGeneralPreferencesSettings($user_account_id);

        //for all other types of booking we had stored 0 as booking_source_form
        //$booking_source_form_id = BookingSources::getBookingSourceFormIdForGuestExperience($bookingInfo->channel_code);

        $guestChatStatus = $guestChat->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'),
            $bookingInfo->bookingSourceForm);

        if ($guestChatStatus == 1) {
            $user_account = UserAccount::find($user_account_id);
            $messages = $user_account->messages->where('booking_info_id', $bookingInfoId)->where('alert_type', 'chat');
            $unseenMessages = $messages->where('message_read_by_guest', 0)->where('is_guest', 0);
            $lastUnSeenMessage = $unseenMessages->first();

            $unit = Unit::where([
                'pms_room_id' => $bookingInfo->room_id,
                'property_info_id' => $bookingInfo->property_info->id,
                'unit_no' => $bookingInfo->unit_id
            ])->first();
            $bookingInfo->unit = $unit;

            return $this->apiSuccessResponse(200,
                [
                    'messages' => $messages->sortByDesc('created_at')->toArray(),
                    'unSeenMessagesCount' => $unseenMessages->count(),
                    'lastUnSeenMessageId' => (!is_null($lastUnSeenMessage) ? $lastUnSeenMessage->id : 0),
                    'booking' => $bookingInfo
                ],
                'success'
            );
        } else {
            return $this->apiErrorResponse('Chat in-activated by Host', 402, ['messages' => [],
                'unSeenMessagesCount' => 0, 'lastUnSeenMessageId' => 0,]);
        }
    }


    public function guest_booking_manual_retry(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'This link is not valid.');
        }

    }

    public function charge(Request $request)
    {

        try {

            //    if (!$request->hasValidSignature()) {
            //         return [
            //             'status'=>false,
            //             'message' => 'Link Expired, Please reload/refresh page and try again!'
            //         ];
            //     }
            $toChargeData = decrypt($request->data['transaction']);
            $bookingInfo = BookingInfo::where('id', $toChargeData['booking_info_id'])->first();
            $transaction_init = TransactionInit::where('id', $toChargeData['transaction_init_id'])->first();
            $exceptionMsg = '';
            $isVC = false;

            if ($bookingInfo->is_vc == 'VC')
                $isVC = true;

            $ccInfo = null;
            if ($isVC) {
                $ccInfo = CreditCardInfo::where('booking_info_id', $bookingInfo->id)->where('is_vc', 1)->latest('id')->limit(1)->first();
            } else {
                $ccInfo = CreditCardInfo::where('booking_info_id', $bookingInfo->id)->latest('id')->limit(1)->first();
            }
            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->first();
            $isUserPGGlobal = ($propertyInfo->use_pg_settings == 0) ? true : false;
            if ($isUserPGGlobal) {
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', '0')->first();
            } else {
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', $propertyInfo->id)->first();
            }

            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);

            $card = new Card();
            $card->firstName = $ccInfo->customer_object->first_name;
            $card->lastName = $ccInfo->customer_object->last_name;
            $card->token = $ccInfo->customer_object->token;
            $card->amount = $transaction_init->price;
            $card->currency = $currency_code;
            $card->order_id = $toChargeData['transaction_init_id'];

            PaymentGateways::addMetadataInformation($bookingInfo, $card, GuestController::class);

            try {

                $pg = new PaymentGateway();
                $trans = $pg->chargeWithCustomer($ccInfo->customer_object, $card, $userPaymentGateway);
                $exceptionMsg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message);

            } catch (GatewayException $e) {
                $exceptionMsg = $e->getDescription();
                return $this->errorResponse($e->getMessage(), 422);
            }

            if ($trans->status) {
                /***
                 * Update Transaction status on Success
                 */
                $transaction_init->payment_status = TransactionInit::PAYMENT_STATUS_SUCCESS;
                $transaction_init->charge_ref_no = $trans->token;
                if (isset($request->data['description'])) {
                    $transaction_init->client_remarks = $request->data['description'];
                }
                $transaction_init->save();

            }

            $transaction = new TransactionDetail();
            $transaction->transaction_init_id = $toChargeData['transaction_init_id'];
            $transaction->user_id = $bookingInfo->user_id;
            $transaction->user_account_id = $bookingInfo->user_account_id;
            $transaction->name = $bookingInfo->guest_name;
            $transaction->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
            $transaction->payment_status = $trans->status;
            $transaction->charge_ref_no = $trans->token;
            $transaction->payment_processor_response = $trans->fullResponse;
            $transaction->error_msg = $exceptionMsg;
            if (isset($request->data['description'])) {
                $transaction->client_remarks = $request->data['description'];
            }
            $transaction->save();

            $msg = "Payment Charged Successfully.";

            return $this->successResponse($msg, 200);

        } catch (GatewayException $e) {

            report($e);
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function getPayingTerminalData(int $booking_info_id)
    {

        $payingTerminalData = array('status' => false, 'message' => '', 'terminal-data' => null);

        try {

            $booing_info = BookingInfo::where('id', $booking_info_id)->first();

            if ($booing_info->count() > 0) {

                $userSettingsBridge = UserSettingsBridge::where(
                    [
                        'user_account_id' => $booing_info->user_account_id,
                        'model_name' => UserPaymentGateway::class
                    ]
                )->get();

                if ($userSettingsBridge->count() > 0) {

                    $filtered = $userSettingsBridge->where('property_info_id', $booing_info->property_id);

                    $found = $filtered->count();

                    if ($found == 0) {
                        $filtered = $userSettingsBridge->where('property_info_id', 0);

                        if ($filtered->count() == 0)
                            throw new ClientSettingsException('No, Settings done yet');

                        if (count($filtered) > 1) {
                            throw new ClientSettingsException('Multiple records found, Should be only One Master setting for Gateway');
                        }


                    } else if ($found > 1) {
                        throw new ClientSettingsException('Multiple records found, Should be only One setting against One Property for Gateway');
                    }

                    $mId = $filtered->first()->model_id;
                    // dd($mId);

                    $userPaymentGateway = UserPaymentGateway::where('id', $mId)->get();

                    if ($userPaymentGateway->count() > 0) {

                        $gateway = new PaymentGateway();
                        $payingTerminalData['status'] = true;
                        $payingTerminalData['terminal-data'] = $gateway->getTerminal($userPaymentGateway->first());

                    } else {
                        throw new ClientSettingsException('No, Gateway found/configured');
                    }

                } else {
                    throw new ClientSettingsException('Error, No Gateway Settings done yet');
                }

            } else {
                throw new ClientSettingsException('Error, No Booking Record Found');
            }

        } catch (Exception $e) {
            $payingTerminalData['status'] = false;
            $payingTerminalData['message'] = $e->getMessage();
            report($e);
        }
        return $payingTerminalData;
    }

    public function authorizeWithToken(Request $request)
    {

        $decryptedTransaction = decrypt($request->data['transaction']);
        try {
            $ccAuth = CreditCardAuthorization::where('id', $decryptedTransaction['cc_auth_id'])->orderBy('due_date', 'DESC')->first();
            $bookingInfo = BookingInfo::where('id', $decryptedTransaction['booking_info_id'])->first();
            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->first();
            $isUserPGGlobal = ($propertyInfo->use_pg_settings == 0) ? true : false;
            if ($isUserPGGlobal) {
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', '0')->first();
            } else {
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', $propertyInfo->id)->first();
            }

            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);

            $card = new Card();
            $card->amount = $ccAuth->hold_amount;
            $card->currency = $currency_code;
            $card->order_id = round(microtime(true) * 1000);
            $card->token = $request->data['token'];
            $card->general_description = "Authorization through manual terminal";

            PaymentGateways::addMetadataInformation($bookingInfo, $card, GuestController::class);

            $pg = new PaymentGateway();
            $transaction = $pg->authorizeWithToken($card, $userPaymentGateway);

            if ($transaction->status == true) {

                /******** Update Credit Card Authorization status */
                $ccAuth->status = CreditCardAuthorization::STATUS_ATTEMPTED;
                $ccAuth->transaction_obj = json_encode($transaction);
                $ccAuth->token = $transaction->token;
                $ccAuth->save();
            }

            /******* Authorization Logs */
            $ccAuthDetail = new AuthorizationDetails();
            $ccAuthDetail->cc_auth_id = $decryptedTransaction['cc_auth_id'];
            $ccAuthDetail->user_account_id = $decryptedTransaction['user_account_id'];
            $ccAuthDetail->payment_processor_response = $transaction->fullResponse;
            $ccAuthDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
            $ccAuthDetail->payment_gateway_name = $userPaymentGateway->payment_gateway_form->name;
            $ccAuthDetail->amount = $ccAuth->hold_amount;
            $ccAuthDetail->name = $bookingInfo->guest;
            $ccAuthDetail->payment_status = $transaction->status;
            $ccAuthDetail->charge_ref_no = $transaction->token;
            $ccAuthDetail->order_id = $card->order_id;
            $ccAuthDetail->save();

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Authorization Attempted Successfully.'
            ]);

        } catch (GatewayException $e) {
            report($e);
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function reauthByGuest(Request $request)
    {

        $cAuth = CreditCardAuthorization::findOrFail($request->cc_auth_id);

        $paymentType = new PaymentTypeMeta();
        $cCAA = $paymentType->getCreditCardAutoAuthorize();
        $cCMA = $paymentType->getCreditCardManualAuthorize();

        $sDAA = $paymentType->getSecurityDepositAutoAuthorize();
        $sDMA = $paymentType->getSecurityDepositManualAuthorize();

        if ($cAuth->type == $cCAA || $cAuth->type == $cCMA) {
            return $this->attemptAuthorization($cAuth, true);
        }

        if ($cAuth->type != $sDAA || $cAuth->type != $sDMA) {
            return $this->attemptAuthorization($cAuth, false);
        }
    }

    private function insertAuthLog(CreditCardAuthorization $ccAuth, Transaction $transaction, UserPaymentGateway $userPaymentGateway, string $message)
    {
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

    private function attemptAuthorization($cAuth, $sddFlag)
    {

        if ($sddFlag) {
            if ($cAuth->ccinfo) {
                if ($cAuth->ccinfo->booking_info) {
                    if ($cAuth->ccinfo->booking_info->transaction_init) {
                        foreach ($cAuth->ccinfo->booking_info->transaction_init as $trans) {
                            if ($trans->payment_status == 1) {

                                $msg = "Transactions are already done or under process so, there is no need to authorize amount immediately.";

                                return $this->errorResponse($msg, 422);
                            }
                        }
                    }
                }
            }
        }
        try {

            /**
             * @var $gwTransaction Transaction
             */
            $paymentType = new PaymentTypeMeta();
            $sDAA = $paymentType->getSecurityDepositAutoAuthorize();
            $sDMA = $paymentType->getSecurityDepositManualAuthorize();

            $gwTransaction = $cAuth->transaction_obj;

            $shouldAutoReAuth = $cAuth->is_auto_re_auth == 1;
            $isAuthCancelable = $gwTransaction != null && $gwTransaction->token != null && $gwTransaction->token != '' && $cAuth->token != null && $shouldAutoReAuth;
            // $shouldAuth = $shouldAutoReAuth || (($gwTransaction != null && ($gwTransaction->token == '' || $gwTransaction->token == null)) && ($cAuth->token == null || $cAuth->token == ''));

            $bookingInfo = $cAuth->ccinfo->booking_info;

            $pg = new PaymentGateway();
            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->first();
            $isUserPGGlobal = ($propertyInfo->use_pg_settings == 0) ? true : false;
            if ($isUserPGGlobal) {
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', '0')->first();
            } else {
                $userPaymentGateway = UserPaymentGateway::where('user_account_id', $bookingInfo->user_account_id)->where('property_info_id', $propertyInfo->id)->first();
            }

            $ccInfo_latest = CreditCardInfo::where('booking_info_id', $bookingInfo->id)->latest('id')->limit(1)->first();

            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo, $propertyInfo);
            // dd($shouldAuth);

            if ($isAuthCancelable) {
                $cancellationResponse = $pg->cancelAuthorization($gwTransaction, $userPaymentGateway);
                $this->insertAuthLog($cAuth, $cancellationResponse, $userPaymentGateway, 'Canceled Authorization, for ReAuthorization.');

                if ((($cAuth->type == $sDAA) || ($cAuth->type == $sDMA)) && $cancellationResponse->status)
                    event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_SUCCESS'), $cAuth->id));
                else if ((($cAuth->type == $sDAA) || ($cAuth->type == $sDMA)) && !$cancellationResponse->status)
                    event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_FAILED'), $cAuth->id));
            }

            // if($shouldAuth) {
            $card = new Card();
            $card->amount = $cAuth->hold_amount;
            $card->currency = $currency_code;
            $card->order_id = round(microtime(true) * 1000);

            PaymentGateways::addMetadataInformation($bookingInfo, $card, GuestController::class);

            /**
             * @var $reAuthResponse Transaction
             */
            $reAuthResponse = $pg->authorizeWithCustomer($ccInfo_latest->customer_object, $card, $userPaymentGateway);

            $numAttempts = $cAuth->attempts;
            $cAuth->attempts = $numAttempts + 1;
            $message = '';

            if ($reAuthResponse->status) {

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
            } elseif ($cAuth->attempts == CreditCardAuthorization::TOTAL_ATTEMPTS) {
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
            if (isset($AuthorizedSuccessPMSPreferences) && $AuthorizedSuccessPMSPreferences) {

                if (($cAuth->type == $sDAA) || ($cAuth->type == $sDMA)) {
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'), $cAuth->id));
                } else {
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'), $cAuth->id));
                }
            } else if (isset($AuthorizedSuccessPMSPreferences) && !$AuthorizedSuccessPMSPreferences) {
                if (($cAuth->type == $sDAA) || ($cAuth->type == $sDMA)) {
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'), $cAuth->id));
                } else {
                    event(new PMSPreferencesEvent($bookingInfo->user_account, $bookingInfo, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'), $cAuth->id));
                }
            }

            return $this->successResponse($message, 200);


        } catch (GatewayException $e) {
            report($e);
            return $this->errorResponse($e->getMessage(), 422);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function guestUploaded($id)
    {
        $guestImages = GuestImage::where('booking_id', $id)->get();

        return response()->json(['data' => $guestImages]);

    }

    /**
     * @param Request $request
     * @param $id
     * @return false|string
     */
    public function guestImages(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg|max:5048',
            ], [
                'file.required' => 'Image is required!',
                'file.image' => 'The file must be an image!',
                'file.mimes' => 'The file must be of type jpeg, png, jpg.',
                'file.max' => 'File size must be less the 5 MB'
            ]);
            //not upload more than 5 documents
            $type = empty($request->get('name')) ? 'client_uploading' : $request->get('name');
            $booking_id = $request->booking_id;
            $bookingInfo = BookingInfo::find($booking_id);
            if ($bookingInfo->guest_images()->count() >= GuestImage::TOTAL_ALLOWED_IMAGES) {
                return $this->apiErrorResponse('You can\'t upload more than ' . GuestImage::TOTAL_ALLOWED_IMAGES . ' Documents.');
            }

            $image_exist = $bookingInfo->guest_images->where('type', $type)->first();
            if (!empty($image_exist) && $image_exist->status == GuestImage::STATUS_ACCEPTED) {
                return $this->apiErrorResponse('This type of document is already accepted by the authority.');
            }

            if ($request->hasFile('file')) {

                $file = $request->file('file');
                $filenameWithExt = $request->file('file')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

                $extension = $request->file('file')->getClientOriginalExtension();
                $fileNameToStore = $type . '_' . $booking_id . '_' . time() . '.' . $extension;
                $destination_path = public_path('/storage/uploads/guestImages');

                if (!empty($image_exist) && !empty($image_exist->image)) {

                    if (file_exists(GuestImage::PATH_IMAGES . $image_exist->image)) {
                        $deleted_image = unlink(GuestImage::PATH_IMAGES . $image_exist->image);
                        if (!$deleted_image) {
                            Log::critical('Old Guest ' . $type . ' deleting failed', ['File' => __FILE__, 'Function' => __FUNCTION__, 'BookingInfoId' => $booking_id, 'imageName' => $image_exist->image]);
                        }
                    }
                }

                $resize_image = Image::make($file->getRealPath());
                $resize_image->resize(450, 400, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destination_path . '/' . $fileNameToStore);

                //$path = $request->file('file')->move('storage/uploads/guestImages', $fileNameToStore);
            } else {
                $fileNameToStore = 'no_image.png';
            }


            if (!empty($image_exist)) {
                $old_status = $image_exist->status;
                $image_exist->image = $fileNameToStore;
                $image_exist->status = GuestImage::STATUS_PENDING;
                $image_exist->created_at = now()->toDateTimeString();
                $image_exist->save();

                if ($old_status == GuestImage::STATUS_REJECTED) {
                    event(new EmailEvent(config('db_const.emails.heads.document_uploaded.type'),
                            $bookingInfo->id,
                            ['type' => ucwords(str_replace('_', ' ', $image_exist->type))])
                    );
                }

            } else {

                $guestImage = new GuestImage();
                $guestImage->image = $fileNameToStore;
                $guestImage->type = ($request->get('type')) ? $request->get('type') : $request->get('name');
                $guestImage->status = 0;
                $guestImage->booking_id = $booking_id;
                $guestImage->created_at = now()->toDateTimeString();

                if ($guestImage->save() == true) {
                    /**
                     *  Event Trigger  Email, SMS to Client to inform guest uploaded his/her documents
                     */

                    event(new EmailEvent(config('db_const.emails.heads.document_uploaded.type'), $bookingInfo->id, ['type' => ucwords(str_replace('_', ' ', $guestImage->type))]));


                    /**
                     *  Job dispatch to update PMS that guest uploaded his/her documents
                     */
                    GuestDocumentsStatusUpdateOnPMSJob::dispatch($bookingInfo, true);

                    //create alert for the same to show notification
                    //use common repo to create alert
                    $alert = isset($request->alert_type) && !empty($request->alert_type) ? $request->alert_type : 'id_uploaded';
                    $notificationRepo = new NotificationAlerts($bookingInfo->user_id, $bookingInfo->user_account_id);
                    $notificationRepo->create($bookingInfo->id, 1, $alert, $bookingInfo->pms_booking_id, 1);
                }
            }

            $raw = $this->guest_portal->getGuestImagesByBookingId($booking_id);

            if ($request->requested_by == 'guest_portal') {
                $this->guestDocumentTransform($raw);
                return $this->apiSuccessResponse(200, $raw, 'Image uploaded successfully.');
            }

            if ($request->requested_by == 'client_document_upload') {

                //GuestDocumentCollection::withoutWrapping();
                //$documents =  GuestDocumentCollection($raw);

                return $this->apiSuccessResponse(200, $raw, 'Image uploaded successfully.');
            }

            StepThreeCollection::withoutWrapping();

            return $this->apiSuccessResponse(200, new StepThreeCollection($booking_id, $raw), 'Image uploaded successfully.');

        } catch (ValidationException $e) {
            return $this->apiErrorResponse($e->getMessage());
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $this->apiErrorResponse($e->getMessage());
        }
    }

    /**
     * This function is used to save encoded images which are not uploaded by guest rather generated.
     * e.g. Signature, Self portrait
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function guestEncodedImages(Request $request)
    {

        try {
            /**
             * @var $imageAction string value: old or new
             */
            $imageAction = $request->get('image_action');

            $booking_id = $request->get('booking_id');

            $payload = $request->get('image');

            /**
             * @var $bookingInfo BookingInfo
             */
            $bookingInfo = BookingInfo::find($booking_id);
            $is_guest = Auth::guest();
            if (!$is_guest && $bookingInfo->read_only_mode == 1) {
                $data = $this->getNextPageData($request->meta, $bookingInfo->id);
                return $this->apiSuccessResponse(200, $data, '');
            }

            $errorMessage_Param = 'Required Parameter(s) are missing';
            $errorMessage_type = 'Not a supported image type';

            if (!$request->has('meta'))
                return $this->apiErrorResponse($errorMessage_Param);

            if (!$request->has('type'))
                return $this->apiErrorResponse($errorMessage_Param);

            $type = $request->get('type');

            if (!GuestImage::isSupportedType($type))
                return $this->apiErrorResponse($errorMessage_type);

            if (!$request->has('booking_id'))
                return $this->apiErrorResponse($errorMessage_Param);

            if (!$request->has('image'))
                return $this->apiErrorResponse($errorMessage_Param);

            if (!$request->has('image_action'))
                return $this->apiErrorResponse($errorMessage_Param);


            if (strtolower($imageAction) == 'old') {
                $data = $this->getNextPageData($request->meta, $bookingInfo->id);
                $rep = $this->apiSuccessResponse(200, $data, 'Image uploaded successfully.');
                return $rep;
            }

            /**
             * @var $guestImage GuestImage
             */
            $guestImage = $bookingInfo->guest_images->where('type', $type)->first();

            $fileNameToStore = $type . '_' . $booking_id . '_' . time() . '.png';

            try {

                $payloads = explode(',', $payload);

                if (count($payloads) == 2) {

                    if (!empty($guestImage) && !empty($guestImage->image)) {

                        if (file_exists(GuestImage::PATH_IMAGES . $guestImage->image)) {
                            $deleted_image = unlink(GuestImage::PATH_IMAGES . $guestImage->image);
                            if (!$deleted_image) {
                                Log::critical('Old Guest Selfie deleting failed', ['File' => __FILE__, 'Function' => __FUNCTION__, 'BookingInfoId' => $booking_id, 'imageName' => $guestImage->image]);
                            }
                        }
                    }

                    $image = base64_decode($payloads[1]);
                    $result = file_put_contents(GuestImage::PATH_IMAGES . $fileNameToStore, $image);

                    if ($result === false) {
                        Log::critical('Guest Selfie saving failed', ['File' => __FILE__, 'Function' => __FUNCTION__, 'BookingInfoId' => $booking_id]);
                        return $this->apiErrorResponse('File saving failed');
                    }
                }

            } catch (Exception $e) {
                $fileNameToStore = 'no_image.png';
                Log::error($e->getMessage(), ['File' => __FILE__, 'Function' => __FUNCTION__, 'Stack' => $e->getTraceAsString()]);
            }

            if ($guestImage == null) {

                $guestImage = new GuestImage();
                $guestImage->image = $fileNameToStore;
                $guestImage->type = $type;
                $guestImage->status = 0;
                $guestImage->booking_id = $booking_id;
                $guestImage->created_at = now()->toDateTimeString();
                $guestImage->save();

            } else {
                $guestImage->image = $fileNameToStore;
                $guestImage->save();
            }

            $data = $this->getNextPageData($request->meta, $bookingInfo->id);
            $rep = $this->apiSuccessResponse(200, $data, 'Image uploaded successfully.');
            return $rep;

        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Function' => __FUNCTION__, 'Stack' => $e->getTraceAsString()]);
            return $this->apiErrorResponse($e->getMessage());
        }
    }

    public function updateGuestBasicInfo(Request $request)
    {
        $bookingInfo = BookingInfo::with('user_account')->where('id', $request->booking_id)->first();

        $generalPreferencesSettings = new ClientGeneralPreferencesSettings($bookingInfo->user_account_id);
        $basicInfo = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.basicInfo'), $bookingInfo->bookingSourceForm);
        $arrival = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.arrival'), $bookingInfo->bookingSourceForm);
        $rules = [];
        $messages = [];
        if ($basicInfo) {
            $rules['email'] = 'required|email';
            $rules['phone'] = 'required|regex:^[0-9-()\s]+$^|min:5';
            $messages['phone.min'] = 'Phone field contain atleast 5 numbers';
            $messages['phone.regex'] = 'Enter valid phone number';
        }


        $this->validate($request, $rules, $messages);

        $count = GuestData::where('booking_id', $request->booking_id)->count();

        $update_data['booking_id'] = $request->booking_id;

        if ($basicInfo) {
            $update_data['email'] = $request->email;
            $update_data['phone'] = $request->phone;
            $bookingInfo->guest_email = $request->email;
            $bookingInfo->guest_phone = $request->phone;
            $bookingInfo->save();
        }

        if ($arrival) {

            $arrival_time = $request['arrival_time'];
            $country_code = isset($request->guest_country_code) ? $request->guest_country_code : '1';

            $update_data['arrivaltime'] = $arrival_time;
            $update_data['country_code'] = $country_code;
        }

        if ($count == 0) {
            GuestData::create($update_data);

        } else {
            GuestData::where('booking_id', $request->booking_id)->update($update_data);
        }

        try {

            if ($arrival || $basicInfo) {
                $propertyInfo = $bookingInfo->user_account->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();
                $remoteBookingDataUpdate = new \stdClass();

                if ($arrival) {
                    $remoteBookingDataUpdate->arrival_time = $arrival_time;
                }
                if ($basicInfo && $request->email != '') {
                    $remoteBookingDataUpdate->guest_email = $request->email;
                }
                if ($basicInfo && $request->phone != '') {
                    $remoteBookingDataUpdate->phone = $request->phone;;
                }
                BookingDetailRepository::updateBasicInfoAtBA($bookingInfo->user_account, $propertyInfo, $bookingInfo, $remoteBookingDataUpdate);
            }

            return $this->apiSuccessResponse(200, [], 'Basic information updated.');

        } catch (PmsExceptions $e) {
            Log::error($e->getTraceAsString(), ['File' => 'GuestController', 'Function' => 'FormDataSubmit']);
            report($e);
            return $this->apiErrorResponse($e->getMessage(), 500, $e->getTraceAsString());
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString(), ['File' => 'GuestController', 'Function' => 'FormDataSubmit']);
            return $this->apiErrorResponse('Fail to Update, Try Agian', 500, $e->getTraceAsString());
        }

    }


    public function FormDataSubmit(Request $request, $id)
    {

        $this->validate($request, [
            'email' => 'required|email',
            'phone' => 'required'
        ]);

        $count = GuestData::where('booking_id', $id)->count();

        $arrival_time = Carbon::parse($request['arrivaltime'])->format('g:i A');

        if ($count == 0) {
            GuestData::create([
                'booking_id' => $id,
                'email' => $request->email,
                'phone' => $request->phone,
                // 'arrivaltime' => $request->arrivaltime.' '.$request->aMPm ]);
                'arrivaltime' => $arrival_time]);

        } else {
            GuestData::where('booking_id', $id)->update([
                'booking_id' => $id,
                'email' => $request->email,
                'phone' => $request->phone,
                // 'arrivaltime' => $request->arrivaltime.' '.$request->aMPm]);
                'arrivaltime' => $arrival_time]);
        }

        try {
            $bookingInfo = BookingInfo::with('user_account')->where('id', $id)->first();
            $propertyInfo = $bookingInfo->user_account->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();

            $pms = new PMS($bookingInfo->user_account);
            $pmsOptions = new PmsOptions();
            $bookingToUpdateData = new Booking();
            $pmsOptions->propertyID = $propertyInfo->pms_property_id;
            $pmsOptions->propertyKey = $propertyInfo->property_key;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $bookingToUpdateData->id = $bookingInfo->pms_booking_id;
            $bookingToUpdateData->guestArrivalTime = $arrival_time;

            $bookingToUpdateData->notes = '';
            if ($arrival_time != '')
                $bookingToUpdateData->notes .= 'Guest will arrive  at ' . $arrival_time . "\n";
            if ($request->email != '')
                $bookingToUpdateData->notes .= 'Guest Email : ' . $request->email . "\n";
            if ($request->phone != '') {
                $bookingToUpdateData->guestMobile = $request->phone;
                $bookingToUpdateData->notes .= 'Guest Phone : ' . $request->phone . "\n";
            }

            $pms->update_booking($pmsOptions, $bookingToUpdateData);

            $data = [
                'status' => true,
                'msg' => 'updated'
            ];
            return response()->json($data);

        } catch (PmsExceptions $e) {
            Log::error($e->getTraceAsString(), ['File' => 'GuestController', 'Function' => 'FormDataSubmit']);
            report($e);
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString(), ['File' => 'GuestController', 'Function' => 'FormDataSubmit']);
            return response()->json(['status' => false, 'msg' => 'Fail to Update, Try Agian']);
        }

    }

    public function FormValues($id)
    {
        $res = GuestData::where('booking_id', $id)->first();
        return response()->json($res);
    }

    public function guestImageDelete(Request $request)
    {
        try {
            $res = GuestImage::find($request->id)->delete();
            /*$res = GuestImage::find($request->id);
            if(!empty($res->image)){
                $file_path = config('db_const.logos_directory.guest_image.img_path').$res->image;
                if(file_exists(public_path($file_path))){ unlink($file_path); }
            }
            $res->delete();*/
            if ($res) {
                return $this->apiSuccessResponse(200, GuestImage::where('booking_id', $request->booking_id)->latest()->get(), 'Image deleted.');
            } else {
                return $this->apiErrorResponse('Something went wrong during image deletion.');
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => GuestController::class, 'Function' => 'guestImageDelete()']);
            return $this->apiErrorResponse('Something went wrong during image deletion.');
        }
    }

    public function fetchGuestImages(Request $request)
    {
        $guest_images = GuestImage::where('booking_id', $request->id)->get();
        return response()->json($guest_images);
    }

    public function checkout(Request $request, $id, $type, $response_type = 'blade_view')
    {

        try {

            $bInfoId = 0;
            $amount = 0;
            $obj = null;
            $buttonText = 'Authorize ';
            $capture = false;
            $isPaid = false;
            $description = '';

            if ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_CHARGE) {
                $obj = TransactionInit::where('id', $id)->first();
                $bInfoId = $obj->booking_info_id;
                $amount = $obj->price;
                $buttonText = 'Pay Now ';
                $capture = true;
                $isPaid = $obj->payment_status == TransactionInit::PAYMENT_STATUS_SUCCESS;
                $description = 'Charges of ';

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH) {
                $obj = CreditCardAuthorization::where('id', $id)->first();
                $bInfoId = $obj->booking_info_id;
                $amount = $obj->hold_amount;
                $isPaid = $obj->status == CreditCardAuthorization::STATUS_ATTEMPTED && $obj->is_auto_re_auth == 0;
                $description = 'Authorization of ';

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH_SSD) {
                $obj = CreditCardAuthorization::where('id', $id)->first();
                $bInfoId = $obj->booking_info_id;
                $amount = $obj->hold_amount;
                $isPaid = $obj->status == CreditCardAuthorization::STATUS_ATTEMPTED && $obj->is_auto_re_auth == 0;
                $description = 'Authorization of ';

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_UPSELL) {

                $upsellRepository = new UpsellRepository();
                $upsells = $upsellRepository->getUpsellOrdersAndCart($id);
                $bInfoId = $id;
                $amount = $upsells['amount_due'];
                $buttonText = 'Pay Now ';
                $capture = true;
                $isPaid = !$upsells['amount_due'] > 0;
                //$isPaid = $obj->payment_status == TransactionInit::PAYMENT_STATUS_SUCCESS;
                $description = 'Add-on Services of ';
            } else {
                abort(403, 'Invalid Request Parameters');
            }

            /**
             * @var $bookingInfo BookingInfo
             */
            $bookingInfo = BookingInfo::find($bInfoId);

            if ($bookingInfo == null)
                abort(403, 'No, Booking Data found');

            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)
                ->where('pms_id', $bookingInfo->pms_id)
                ->where('user_account_id', $bookingInfo->user_account_id)
                ->first();

            if ($propertyInfo == null)
                abort(403, 'No, Property found for booking: ' . $bookingInfo->pms_booking_id);

            //for all other types of booking we had stored 0 as booking_source_form
            //$booking_source_form_id = BookingSources::getBookingSourceFormIdForGuestExperience($bookingInfo->channel_code);
            $bookingDetails = json_encode(new GuestBookingDetailsResource(collect(['bookingInfo' => $bookingInfo, 'propertyInfo' => $propertyInfo])));
            $generalPreferencesSettings = new ClientGeneralPreferencesSettings($bookingInfo->user_account_id);
            $guestChatStatus = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'),
                $bookingInfo->bookingSourceForm);
            $guest_name = $bookingInfo->guest_name;

            $ccInfo = CreditCardInfo::where('booking_info_id', $bookingInfo->id)->latest('id')->limit(1)->first();

            $upg = new PaymentGateways();
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

            $description .= $propertyInfo->currency_code . $amount . ' for ' . $bookingInfo->pms_booking_id . ' booking.';
            $pg = new PaymentGateway();
            $value = $pg->getTerminalForFrontEndCharge($amount, $propertyInfo->currency_code, $ccInfo->auth_token, $capture, $userPaymentGateway, $description);

            if ($type != GuestMailFor3DSecureEvent::MAIL_FOR_3DS_UPSELL) {
                $payment_intent_id = $value['payment_intent_id'];
                $obj->payment_intent_id = $payment_intent_id;
                $obj->save();
            }

            $buttonText .= $propertyInfo->currency_code . '' . $amount;

            $header = $this->getBookingAndGuestChatByBookingId($bookingInfo->id);

            $data = [
                'bookingDetails' => $bookingDetails,
                'guestChatStatus' => $guestChatStatus,
                'guest_name' => $guest_name,
                'account_id' => $value['account_id'],
                'id' => $id,
                'b_info' => $bookingInfo->id,
                'client_secret' => $value['client_secret'],
                'public_key' => $value['public-key'],
                'type' => $type,
                'button_text' => $buttonText,
                'isPaid' => $isPaid,
                'fName' => $ccInfo->f_name,
                'lName' => $ccInfo->l_name,
                'email' => empty($bookingInfo->guest_email) ? '' : $bookingInfo->guest_email,
                'phone' => empty($bookingInfo->guest_phone) ? '' : $bookingInfo->guest_phone,
                'header' => $header,
                'postal_code' => empty($bookingInfo->guest_post_code) ? '' : $bookingInfo->guest_post_code,
                'country' => empty($bookingInfo->guest_country) ? '' : get_two_letter_country_code($bookingInfo->guest_country),
                'address_line1' => empty($bookingInfo->guest_address) ? '' : $bookingInfo->guest_address,
                'city' => empty($bookingInfo->guestCity) ? '' : $bookingInfo->guestCity,
                'state' => ''
            ];

            if ($response_type == 'json') {

                $next_step = $this->getNextPageData($request->meta, $id);
                $data['meta'] = $next_step['meta'];
                $data['checkout_post_url'] = URL::temporarySignedRoute('checkout-status-update', now()->addMinutes(30), ['id' => $id, 'type' => $type]);

                return $this->apiSuccessResponse(200, $data, 'Payment credentials for 3D secure card.');
            }

            return view($value['cc-form-name'], $data);

        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'ID' => $id,
                'Type' => $type,
                'Guest-Has-Seen-This-Message' => 'Invalid Request Parameters',
                'Stack' => $e->getTraceAsString()
            ]);
        }

        abort(403, 'Invalid Request Parameters');
        return null;
    }

    public function updateStatusAfterCheckout(Request $request, $id, $type)
    {

        try {

            $paymentMethod = request('paymentMethod');
            $bookingInfoId = request('b_info');
            $fName = request('first_name');
            $lName = request('last_name');
            $email = request('email');
            $phone = request('phone');
            $intentId = request('intentId');
            $clientSecret = request('clientSecret');

            $bInfoId = 0;
            $obj = null;

            if ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_CHARGE) {
                $obj = TransactionInit::where('id', $id)->first();
                $bInfoId = $obj->booking_info_id;

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH) {
                $obj = CreditCardAuthorization::where('id', $id)->first();
                $bInfoId = $obj->booking_info_id;

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH_SSD) {
                $obj = CreditCardAuthorization::where('id', $id)->first();
                $bInfoId = $obj->booking_info_id;

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_UPSELL) {

                $bInfoId = $bookingInfoId;

            } else {
                abort(403, 'Invalid Request Parameters');
            }

            if ($bookingInfoId != $bInfoId) // Extra security check!
                abort(403, 'Invalid Request Parameters');

            $bookingInfo = BookingInfo::find($bInfoId);

            if ($bookingInfo == null)
                abort(403, 'Invalid Request Parameters');

            $propertyInfo = $bookingInfo->property_info;

            if ($propertyInfo == null)
                abort(403, 'Invalid Request Parameters');

            $ccInfo = CreditCardInfo::where('booking_info_id', $bookingInfo->id)->latest('id')->limit(1)->first();

            if ($ccInfo == null)
                abort(403, 'Invalid Request Parameters');

            $upg = new PaymentGateways();
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

            $pg = new PaymentGateway();

            $card = new Card();
            $card->token = $paymentMethod;
            $card->firstName = $fName;
            $card->lastName = $lName;
            $card->eMail = $email;
            $card->phone = $phone;
            $card->currency = $propertyInfo->currency_code;

            $customer = new Customer();
            $customer->payment_method = $paymentMethod;
            $customer->token = $ccInfo->auth_token;

            $customer = $pg->updateCustomerPaymentMethod($customer, $userPaymentGateway);

            $ccInfo->customer_object = json_encode($customer);
            $ccInfo->error_message .= "\nAdded new PaymentMethod via Terminal";
            $ccInfo->save();

            $transaction = $pg->afterAuthentication($intentId, $clientSecret, $userPaymentGateway);

            sleep(4);

            if ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_CHARGE) {
                PaymentConfirmationJob::dispatch(
                    PaymentConfirmationJob::SOURCE_CONTROLLER_CHARGE_SUCCESS,
                    null,
                    null,
                    null,
                    $transaction,
                    $obj)->delay(now()->addSeconds(5));

                CCReAuthJobRuntime::dispatch($bookingInfo->id);

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH || $type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH_SSD) {
                PaymentConfirmationJob::dispatch(
                    PaymentConfirmationJob::SOURCE_CONTROLLER_AUTH_SUCCESS,
                    null,
                    null,
                    null,
                    $transaction,
                    $obj)->delay(now()->addSeconds(5));

                BAChargeJobRuntime::dispatch($bookingInfo->id);

            } elseif ($type == GuestMailFor3DSecureEvent::MAIL_FOR_3DS_UPSELL) {
                $charge_status = $this->chargeUpsellPayment($bookingInfo->id, $ccInfo);

                /**
                 * All transactions that are "in approval" status will change into "pending/scheduled" status
                 * so that their respective jobs handle them automatically.
                 */
                TransactionInit::where('booking_info_id', $bInfoId)->where('payment_status', TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL)->update(['payment_status' => TransactionInit::PAYMENT_STATUS_PENDING]);
                CreditCardAuthorization::where('booking_info_id', $bInfoId)->where('status', CreditCardAuthorization::STATUS_WAITING_APPROVAL)->update(['status' => CreditCardAuthorization::STATUS_PENDING]);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Success'
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'ID' => $id,
                'Type' => $type,
                'Stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function precheckinClientSessionUpdate(Request $request)
    {


    }

    public function precheckinReadOnlyModeSession(Request $request)
    {

        //session()->put(['precheckin'=> ['booking_id'=> 5622, 'status'=> 1]]);
        //Session::put(['precheckin'=> [ 5622 => ['status'=> 1, 'created_at'=> now()]]]);
        //Session::put(['precheckin'=> [ 5622 => ['status'=> 0, 'created_at'=> now()]]]);
        //Session::save();
        Session::forget('precheckin');
        Session::save();
        dd([Session::all(), Session::get('precheckin')]);
    }

    public function termsConditions(Request $request)
    {

        $booking_info = BookingInfo::findOrFail($request->id);

        $terms = get_related_records(
            ['text_content', 'internal_name', 'checkbox_text', 'required'],
            PropertyInfo::TAC,
            [
                ['user_account_id', $booking_info->user_account_id],
                ['status', TermsAndCondition::STATUS_ACTIVE]
            ],
            $booking_info->property_info_id,
            $booking_info->room_info->id
        )->first();


        if (!empty($terms->text_content)) {
            $terms = convertTemplateVariablesToActualData(
                BookingInfo::class,
                $request->id,
                $terms->toArray()
            );
        }
        $page_title = $this->getPrecheckinPageTitle($booking_info);

        return view('v2.guest.guest_pre_checkin.terms', ['terms' => $terms, 'title' => $page_title]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDocumentImage(Request $request)
    {

        $mode = Session::get('precheckin');

        if (!Auth::guest() && !empty($mode[$request->booking_id]['status'])) {
            return $this->apiErrorResponse(
                'Failed to delete Verification Image. You are in View-Only mode.',
                422
            );
        }

        $deleted = GuestImage::where(
            [
                ['type', $request->image_type],
                ['id', $request->image_id],
                ['booking_id', $request->booking_id],
                ['status', '!=', GuestImage::STATUS_ACCEPTED]
            ]
        )->delete();

        if ($deleted)
            return $this->apiSuccessResponse(200, [], 'Verification Image deleted.');
        else
            return $this->apiErrorResponse('Failed to delete Verification Image.', 422);
    }
}
