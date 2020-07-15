<?php

namespace App\Http\Controllers\v2\client;

use App\BookingInfo;
use App\GuestData;
use App\GuestImage;
use App\Http\Requests\BookingDetailRequest;
use App\Http\Requests\GuestExperienceTabRequest;
use App\Http\Resources\General\BookingDetail\ActivityLogResource;
use App\Http\Resources\BA\BookingDetail\BookingDetailHeaderResource;
use App\Http\Resources\BA\BookingDetail\BookingDetailTabResource;
use App\Http\Resources\General\BookingDetail\GuestDocumentCollection;
use App\Http\Resources\BookingDetail\GuestDocumentResource;
use App\Http\Resources\General\BookingDetail\GuestExperienceTabResource;
use App\Http\Resources\BA\BookingDetail\PaymentsTabResource;
use App\Http\Resources\General\BookingDetail\SentEmailResource;
use App\PropertyInfo;
use App\Repositories\BookingDetail\BookingDetailRepository;
use App\Repositories\Bookings\BookingRepositoryInterface;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class BookingDetailController extends Controller
{
    public $booking_detail;

    public function __construct(BookingDetailRepository $bookingDetailRepository)
    {
        $this->middleware('auth', ['except' => ['getActivityLog']]);
        $this->booking_detail = $bookingDetailRepository;
    }

    public function index($id)
    {
        BookingInfo::findOrFail($id);
        return view('v2.client.bookings.booking-detail-page', [
            'booking_id'=> $id,
            'pms_prefix'=> 'ba' //pms prefix will assist in dynamic component loading at vue.js side
        ]);
    }

    /**
     * PMS Wise
     * @param $booking_info_id
     * @return BookingDetailHeaderResource
     */
    public function getBookingDetailHeader($booking_info_id){

        $raw_booking = $this->booking_detail->getBookingDetailHeader($booking_info_id);

        if(!empty($raw_booking)){
            BookingDetailHeaderResource::withoutWrapping();
            return new BookingDetailHeaderResource($raw_booking);
        }
    }

    /**
     * PMS Wise
     * @param $booking_info_id
     * @return BookingDetailTabResource
     */
    public function getBookingDetail($booking_info_id){

        $booking_detail = $this->booking_detail->getBookingDetails($booking_info_id);

        BookingDetailTabResource::withoutWrapping();
        return new BookingDetailTabResource($booking_detail);

    }


    /**
     * PMS Wise
     * @param BookingDetailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveBookingDetail(BookingDetailRequest $request){

        $request->validated();

        try{

            $bookingInfo = $booking = BookingInfo::where('id', $request->booking_id)->first();
            $remoteBookingDataUpdate = new \stdClass();

            if($booking->guest_email != $request->email){
                $booking->guest_email = $request->email;
            }

            if ($booking->guest_phone != $request->phone ){
                $booking->guest_phone = $request->phone;

            }

            if($booking->internal_notes != $request->internal_notes){
                $booking->internal_notes = $request->internal_notes;
            }


            $guest_data = GuestData::where('booking_id', $request->booking_id)->first();

            if(!empty($guest_data)){

                if($guest_data->arrivaltime != $request->arrival_time){
                    $guest_data->arrivaltime = $request->arrival_time;
                }

                if($guest_data->adults != $request->adults){
                    $guest_data->adults = $booking->num_adults = $request->adults;
                }

                if($guest_data->childern != $request->children){
                    $guest_data->childern = $request->children;
                }

                $guest_data->save();

            }
            else{

                GuestData::create([
                    'arrivaltime'=> $request->arrival_time,
                    'adults'=> $request->adults,
                    'childern'=> $request->children,
                    'booking_id'=> $request->booking_id,
                    'email'=> $request->email,
                    'phone'=> $request->phone,
                    'name'=> $request->first_name.' '.$request->last_name,
                ]);
                $booking->num_adults = $request->adults;
            }
            $booking->save();
            /** --------------------- -------------------- ------------- **/
            $remoteBookingDataUpdate->guestEmail = $request->guestEmail;
            $remoteBookingDataUpdate->phone = $request->phone;
            $remoteBookingDataUpdate->arrival_time = $request->arrival_time;
            if(!empty((array) $remoteBookingDataUpdate)){
                BookingDetailRepository::updateBasicInfoAtBA($bookingInfo->user_account, $bookingInfo->property_info, $bookingInfo, $remoteBookingDataUpdate);
            }
            return $this->apiSuccessResponse(200, null, 'Booking Detail is updated.');

        }catch (\Exception $e){

            log_exception_by_exception_object($e, null);

            return $this->apiErrorResponse('Something went wrong during update!');
        }
    }


    public function getGuestExperienceTabData($booking_info_id){

        $guest_data = $this->booking_detail->getGuestExperienceTabData($booking_info_id);

        GuestExperienceTabResource::withoutWrapping();
        return new GuestExperienceTabResource($guest_data);

    }

    public function saveGuestExperienceTabData(GuestExperienceTabRequest $request){

        $this->validate($request, [
            "arriving_by" => 'nullable|alpha',
            "arrival_time" => 'nullable|date_format:H:i',
            "plane_number" => 'nullable|regex:/^[a-zA-Z0-9 _\s]+$/',
        ]);

        $response = $this->booking_detail->saveGuestExperienceData($request->all());
        $bookingInfo = BookingInfo::where('id', $request->booking_id)->first();
        $remoteBookingDataUpdate = new \stdClass();
        $remoteBookingDataUpdate->arrival_time = $request->arrival_time;
        if(!empty((array) $remoteBookingDataUpdate)){
            BookingDetailRepository::updateBasicInfoAtBA($bookingInfo->user_account, $bookingInfo->property_info, $bookingInfo, $remoteBookingDataUpdate);
        }
        if($request){

            return $this->apiSuccessResponse(200, [], 'Guest information updated successfully.');
        }else{

            return $this->apiErrorResponse('Something went wrong!');
        }
    }

    public function getPaymentsInformation($booking_info_id){

        $payment_info = $this->booking_detail->getPaymentsInformation($booking_info_id);

        PaymentsTabResource::withoutWrapping();
        return new PaymentsTabResource($payment_info);
    }

    public function getActivityLog($booking_info_id){

        $activity_log = $this->booking_detail->getActivityLog($booking_info_id);

        ActivityLogResource::withoutWrapping();
        return new ActivityLogResource($activity_log);
    }

    public function fetchGuestImages($booking_info_id){

        $documents = $this->booking_detail->getGuestDocuments($booking_info_id);
        GuestDocumentCollection::withoutWrapping();

        return new GuestDocumentCollection($documents);
    }

    public function getSentEmails($booking_info_id) {
        $sent_emails = $this->booking_detail->getSentEmailForBooking($booking_info_id);

        SentEmailResource::withoutWrapping();

        //we will fetch the property_timezone with this extra data -- timezone is same for all email
        SentEmailResource::extraData($booking_info_id);

        return SentEmailResource::collection($sent_emails);
    }
}
