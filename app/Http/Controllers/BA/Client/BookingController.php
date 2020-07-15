<?php

namespace App\Http\Controllers\BA\Client;

use App\BookingInfo;
use App\Http\Controllers\Controller;
use App\Http\Resources\BA\Booking\BookingListCollection;
use App\Http\Resources\BA\Booking\BookingListDetailResource;
use App\Repositories\Bookings\BookingRepository;
use App\Repositories\Bookings\BookingRepositoryInterface;
use App\Repositories\Upsells\UpsellRepository;
use App\Services\UpdateCard;
use Exception;
use Illuminate\Http\Request;

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
     * @param Request $request
     * @return BookingListCollection|\Illuminate\Http\JsonResponse
     */
    public function getBookingList(Request $request)
    {
        $this->isPermissioned('bookings');
        try {
            $raw_bookings = $this->booking->get_bookings_list_filtered($request->filter);
            if (!empty($raw_bookings)) {
                return new BookingListCollection($raw_bookings);
            }
        } catch (Exception $e) {
            log_exception_by_exception_object($e, json_encode(['Class' => __CLASS__, 'method' => __FUNCTION__]), 'error');
        }

        return $this->apiErrorResponse('Something went wrong!');

    }


    /**
     * @param $booking_info_id
     * @return BookingListDetailResource
     * @throws Exception
     */
    public function getBookingDetail($booking_info_id)
    {
        /**
         * @var BookingInfo $raw_booking
         */
        $this->isPermissioned('bookings');

        $filter = [
            'columns' => ["*"],
            'constraints' => [
                ['id', $booking_info_id]
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
}
