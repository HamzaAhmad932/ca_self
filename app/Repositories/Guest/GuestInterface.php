<?php


namespace App\Repositories\Guest;


interface GuestInterface
{
    public function getGuestDetail(int $booking_id);

    public function  guestDataByBookingId(int $booking_id);

    public function getGuestImagesByBookingId(int $booking_id);

    public function getCreditCardAndAuthOfBooking(int $booking_id);

    public function getGuestDataAndGuestImagesByBookingId(int $booking_id);

    public function getGuestDataAndGuestImagesAndTransactionsByBookingId(int $booking_id);

}
