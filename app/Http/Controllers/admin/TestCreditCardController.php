<?php

namespace App\Http\Controllers\admin;

use App\BookingInfo;
use App\CreditCardInfo;
use App\Http\Controllers\Controller;
use App\PropertyInfo;
use App\Services\CreditCardInfoService;
use App\System\PaymentGateway\Models\Card;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Exception;
use http\Env\Response;
use Illuminate\Http\Request;

class TestCreditCardController extends Controller
{
    use CreditCardInfoService;

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.pages.test_credit_card');
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {

        try {

            if (!empty($request->credit_card_token) && !empty($request->pms_booking_id) && !empty($request->user_account_id)) {

                return $this->getCardWithToken($request->user_account_id, $request->pms_booking_id, $request->credit_card_token);

            } elseif (!empty($request->pms_booking_id) && !empty($request->user_account_id) && !empty($request->credit_card_info_id)) {

                return $this->getCardWithBookingId($request->user_account_id, $request->pms_booking_id, $request->credit_card_info_id);

            } elseif (empty($request->pms_booking_id) && !empty($request->credit_card_info_id)) {

                return $this->getCardWithCardId($request->credit_card_info_id);

            } elseif (!empty($request->pms_booking_id) && !empty($request->user_account_id) && empty($request->credit_card_info_id)) {

                return $this->getCardWithBookingId($request->user_account_id, $request->pms_booking_id);

            }

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),404);
        }

    }

    /**
     * @param $user_account_id
     * @param $pms_booking_id
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCardWithToken($user_account_id, $pms_booking_id, $token)
    {
        $booking_info = BookingInfo::where('pms_booking_id', $pms_booking_id)
            ->where('user_account_id', $user_account_id)
            ->first();

        if (!empty($booking_info)) {

            $property_info = $booking_info->property_info;

            if (!empty($property_info)) {

                $user_account = $booking_info->user_account;

                try {
                    $pms = new PMS($user_account);

                    if (empty($pms)) {
                        return $this->errorResponse('User account is not found',404);
                    }

                    $options = new PmsOptions();

                    $options->bookingID = $booking_info->pms_booking_id;
                    $options->propertyID = $property_info->pms_property_id;
                    $options->propertyKey = $property_info->property_key;
                    $options->bookingToken = $token;
                    $options->cardCvv = '';
                    $options->requestType = $options::REQUEST_TYPE_JSON;

                    $card = $pms->fetch_card_for_booking($options);

                    return $this->successResponse(
                        'Process has been success',
                        200,
                        [
                            'actual_response' => 'Actual Response',
                            'credit_card' => $card
                        ]
                    );
                } catch (PmsExceptions $e) {
                    return $this->errorResponse($e->getMessage(),404);
                }

            } else {
                return $this->errorResponse('Property is not found',404);
            }

        } else {
            return $this->errorResponse('Booking is not found',404);
        }
    }

    /**
     * @param $user_account_id
     * @param $pms_booking_id
     * @param int $credit_card_info_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCardWithBookingId($user_account_id, $pms_booking_id, $credit_card_info_id = 0)
    {
        $booking_info = BookingInfo::where('pms_booking_id', $pms_booking_id)
            ->where('user_account_id', $user_account_id)
            ->first();

        if (empty($booking_info)) {
            return $this->errorResponse('Booking is not found',404);
        } else {
            if ($credit_card_info_id != 0) {
                $cc_infos = CreditCardInfo::where('booking_info_id', $booking_info->id)
                    ->where('id', $credit_card_info_id)
                    ->get();
            } else {
                $cc_infos = CreditCardInfo::where('booking_info_id', $booking_info->id)->get();
            }

        }

        if ($cc_infos->count() > 0) {
            foreach ($cc_infos as $key => $cc_info) {
                $cc_info->system_usage = $cc_info->system_usage != '' ? json_decode(decrypt($cc_info->system_usage), true) : 'System Usage does not exist';
                $cc_info_data[$key] = $cc_info;
            }

            return $this->successResponse(
                'Process has been success',
                200,
                [
                    'credit_card' => $cc_info_data
                ]
            );
        } else {
            return $this->errorResponse('Credit Card is not found',404);
        }

    }

    /**
     * @param $credit_card_info_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCardWithCardId($credit_card_info_id)
    {
        $cc_infos = CreditCardInfo::where('id', $credit_card_info_id)->first();
        if (!empty($cc_infos)) {
            return $this->successResponse(
                'Process has been success',
                200,
                [
                    'credit_card' => $cc_infos
                ]
            );
        } else {
            return $this->errorResponse('Credit Card is not found',404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveNewCreditCard(Request $request)
    {
        $booking_info = BookingInfo::where('pms_booking_id', $request->pms_booking_id)
            ->where('user_account_id', $request->user_account_id)
            ->first();

        if (empty($booking_info)) {
            return $this->errorResponse('Booking is not found',404);
        }

        $property_info = $booking_info->property_info;
        $user_account = $booking_info->user_account;
        $pms_booking = new Booking();
        $card = new Card();

        $is_vc = 0;

        if ($booking_info->is_vc == 'CC') {
            $is_vc = 0;
        } elseif ($booking_info->is_vc == 'VC') {
            $is_vc = 1;
        } elseif ($booking_info->is_vc == 'BT') {
            $is_vc = 2;
        }

        $meta_data = $this->setCardMetaData($user_account, $booking_info, $property_info, $pms_booking, $card, $request->card[0]);

        $insert_credit_card = $this->addCcInfoEntry($is_vc, $meta_data);

        if ($insert_credit_card) {
            return $this->provisionResponse('Card has been saved successfully', 200);
        }
    }

    /**
     * @param UserAccount $user_account
     * @param BookingInfo $booking_info
     * @param PropertyInfo $property_info
     * @param Booking $pms_booking
     * @param Card $card
     * @param array $card_data
     */
    public function setCardMetaData(UserAccount $user_account, BookingInfo $booking_info, PropertyInfo $property_info, Booking $pms_booking, Card $card, array $card_data)
    {
        $pms_booking->cardName = $card_data['cardName'];
        $card->type = $card_data['cardType'];
        $card->cardNumber = $card_data['cardNumber'];
        $card->expiryMonth = $card_data['derivedExpireMonth'];
        $card->expiryYear = $card_data['derivedExpireYear'];
        $card->firstName = $booking_info->guest_name;
        $card->lastName = $booking_info->guest_last_name;

        $meta_data = [
            'booking'=> $pms_booking,
            'booking_info'=> $booking_info,
            'user_account'=> $user_account,
            'card'=> $card,
            'property_info'=> $property_info
        ];

        return $meta_data;
    }

}
