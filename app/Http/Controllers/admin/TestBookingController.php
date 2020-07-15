<?php

namespace App\Http\Controllers\admin;

use App\BookingInfo;
use App\Http\Controllers\Controller;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TestBookingController extends Controller
{
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
        return view('admin.pages.test_booking');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TestBooking  $testBooking
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_account_id' => 'required|numeric',
            'booking_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $json_booking = [];
        $xml_booking = '';
        $json_error = '';
        $xml_error = '';

        try {

            $pms = new PMS(UserAccount::find($request->user_account_id));

            if ($pms == null || empty($pms)) {
                return $this->errorResponse('User account are not found',404);
            }

            $pmsOptions = new PmsOptions();
            $bookingInfo = BookingInfo::where('pms_booking_id', $request->booking_id)->first();

//            if ($bookingInfo == null || empty($bookingInfo)) {
//                return $this->errorResponse('PMS booking id are not found',404);
//            }

            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
            $pmsOptions->bookingID = $request->booking_id;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;

            try {
                $pms->fetch_Booking_Details($pmsOptions);
                $xml_booking = $pms->getActualResponse();

                if($xml_booking == null || empty($xml_booking)) {
                    $xml_error = 'XML is not found';

                }
            } catch (PmsExceptions $e) {
                $xml_error = $e->getMessage();
            }

            if(!empty($bookingInfo)) {
                
                $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->first();

                if ($propertyInfo == null || empty($propertyInfo)) {
                    
                    $json_error = 'PMS property id is not found';
                    
                } else {
                    
                    $pmsOptions->propertyKey = $propertyInfo->property_key;
                    $pmsOptions->propertyID = $propertyInfo->pms_property_id;
                    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

                    try {
                        
                        $pms->fetch_Booking_Details($pmsOptions);
                        $json_booking = $pms->getActualResponse();

                        if($json_booking == null || empty($json_booking)) {
                            $json_error = 'JSON is not found';
                        }
                        
                    } catch (PmsExceptions $e) {
                        $json_error = $e->getMessage();
                    }
                }
            }

            return $this->successResponse(
                'Process has been success',
                200,
                [
                    'json_response' => count($json_booking) > 0 ? $json_booking[0] : '',
                    'json_error' => $json_error,
                    'xml_response' => $xml_booking,
                    'xml_error' => $xml_error
                ]
            );

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),404);
        }

    }

    public function getAllUserAccounts()
    {
        $allUsersAccount = UserAccount::where('account_type', 1)
            ->select('id', 'name')
            ->get();

        if ($allUsersAccount) {
            return response()->json(['data' => $allUsersAccount]);
        } else {
            return response()->json(['error' => 'Data not found']);
        }

    }

    public function writeAccess()
    {
        return view('admin.pages.check_write_access');
    }

    public function CheckWriteAccess(Request $request) {

        try {
            /**
             * @var $booking_info BookingInfo
             */
            $booking_info = BookingInfo::find($request->booking_info_id);

            if (empty($booking_info))
                return 'Booking Info Id not Valid.';

            echo 'Booking Info ID - '.$booking_info->id.'<br/>';

            if (empty($booking_info->property_info->property_key)
                || $booking_info->property_info->property_key == '-1'
                || $booking_info->property_info->property_key == -1) {
                echo "Property Key Missing. <br/>";
            }

            $pms = new PMS($booking_info->user_account);
            $pmsOptions = new PmsOptions();
            $pmsOptions->propertyID = $booking_info->property_info->pms_property_id;
            $pmsOptions->propertyKey = $booking_info->property_info->property_key;
            $pmsOptions->bookingID = $booking_info->pms_booking_id;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

            $bookingDetailsOnPMS = $pms->fetch_Booking_Details($pmsOptions);
            $booking = new Booking();
            $booking->propertyId = $booking_info->property_info->pms_property_id;    /* PMS Property ID  */
            $booking->id = $booking_info->pms_booking_id;                           /* pms_booking_id  */

            if (count($bookingDetailsOnPMS) > 0) {

                // UPDATE PMS
                $booking->notes = $bookingDetailsOnPMS[0]->notes . "\n";
                $pms->update_booking($pmsOptions, $booking);

                // Revert Changes
                $booking->notes = $bookingDetailsOnPMS[0]->notes;
                $pms->update_booking($pmsOptions, $booking);

                echo "<p style='color:green'>Write Access Assigned.</p> <\br>";

            } else {
                return "Booking not found on PMS. <\br>";
            }
        } catch (PmsExceptions $e) {
            return '<p style=\'color:red\'><br/> Response Msg --'.$e->getMessage() .'<br/>CA-Define Msg -- '. $e->getCADefineMessage() .'<br/>Code -- '.$e->getPMSCode().'</p>';
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }
}
