<?php

namespace App\Http\Controllers\BA\Client;

use App\BookingInfo;
use App\GuestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingDetailRequest;
use App\Http\Resources\BA\BookingDetail\BookingDetailHeaderResource;
use App\Http\Resources\BA\BookingDetail\BookingDetailTabResource;
use App\Repositories\BookingDetail\BookingDetailRepository;

class BookingDetailController extends Controller
{
    public $booking_detail;

    public function __construct(BookingDetailRepository $bookingDetailRepository)
    {
        $this->middleware('auth', ['except' => ['getActivityLog']]);
        $this->booking_detail = $bookingDetailRepository;
    }

    /**
     * @param $booking_info_id
     * @return BookingDetailHeaderResource
     */
    public function getBookingDetailHeader($booking_info_id)
    {

        $raw_booking = $this->booking_detail->getBookingDetailHeader($booking_info_id);

        if (!empty($raw_booking)) {
            BookingDetailHeaderResource::withoutWrapping();
            return new BookingDetailHeaderResource($raw_booking);
        }
    }


    /**
     * @param $booking_info_id
     * @return BookingDetailTabResource
     */
    public function getBookingDetail($booking_info_id)
    {

        $booking_detail = $this->booking_detail->getBookingDetails($booking_info_id);

        BookingDetailTabResource::withoutWrapping();
        return new BookingDetailTabResource($booking_detail);

    }

    /**
     * PMS Wise
     * @param BookingDetailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveBookingDetail(BookingDetailRequest $request)
    {

        $request->validated();

        try {

            $bookingInfo = $booking = BookingInfo::where('id', $request->booking_id)->first();
            $remoteBookingDataUpdate = new \stdClass();

            if ($booking->guest_email != $request->email) {
                $booking->guest_email = $request->email;
            }

            if ($booking->guest_phone != $request->phone) {
                $booking->guest_phone = $request->phone;

            }

            if ($booking->internal_notes != $request->internal_notes) {
                $booking->internal_notes = $request->internal_notes;
            }


            $guest_data = GuestData::where('booking_id', $request->booking_id)->first();

            if (!empty($guest_data)) {

                if ($guest_data->arrivaltime != $request->arrival_time) {
                    $guest_data->arrivaltime = $request->arrival_time;
                }

                if ($guest_data->adults != $request->adults) {
                    $guest_data->adults = $booking->num_adults = $request->adults;
                }

                if ($guest_data->childern != $request->children) {
                    $guest_data->childern = $request->children;
                }

                $guest_data->save();

            } else {

                GuestData::create([
                    'arrivaltime' => $request->arrival_time,
                    'adults' => $request->adults,
                    'childern' => $request->children,
                    'booking_id' => $request->booking_id,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'name' => $request->first_name . ' ' . $request->last_name,
                ]);
                $booking->num_adults = $request->adults;
            }
            $booking->save();
            /** --------------------- -------------------- ------------- **/
            $remoteBookingDataUpdate->guestEmail = $request->guestEmail;
            $remoteBookingDataUpdate->phone = $request->phone;
            $remoteBookingDataUpdate->arrival_time = $request->arrival_time;
            if (!empty((array)$remoteBookingDataUpdate)) {
                BookingDetailRepository::updateBasicInfoAtBA($bookingInfo->user_account, $bookingInfo->property_info, $bookingInfo, $remoteBookingDataUpdate);
            }
            return $this->apiSuccessResponse(200, null, 'Booking Detail is updated.');

        } catch (\Exception $e) {

            log_exception_by_exception_object($e, null);

            return $this->apiErrorResponse('Something went wrong during update!');
        }
    }


}
