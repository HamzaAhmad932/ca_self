<?php

namespace App\Http\Controllers\admin;

use App\BookingInfo;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Booking\AdminBookingCollection;
use App\Http\Resources\Admin\Booking\AdminBookingDetailResource;
use App\Repositories\Admin\Bookings\AdminBookingRepositoryInterface;
use Illuminate\Http\Request;
use Notification;

class BookingController extends Controller
{
    /**
     * @var AdminBookingRepository $booking
     */
    public $booking;

    public function __construct(AdminBookingRepositoryInterface $adminBookingRepository)
    {
        $this->middleware('auth', ['except' => ['cancelBdcBooking', 'cancelBdcBookingDetailPage']]);
        $this->booking = $adminBookingRepository;
    }

    public function index($user_account_id = 0)
    {
        return view('admin.bookings.booking-list')->with('user_account_id', $user_account_id);
    }

    public function getBookings(Request $request)
    {
        $raw_bookings = $this->booking->get_admin_bookings_list_filtered($request->filter);
        return new AdminBookingCollection($raw_bookings);
    }

    public function bookingDetail($booking_info_id)
    {
        return view('admin.bookings.booking-detail', ['booking_info_id' => $booking_info_id]);
    }

    public function getBookingDetails($booking_info_id)
    {
        /**
         * @var BookingInfo $raw_booking
         */
        $raw_booking = $this->booking->get_booking_detail($booking_info_id);
        return new AdminBookingDetailResource($raw_booking);
    }

}
