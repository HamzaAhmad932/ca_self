<?php

namespace App\Repositories\Bookings;

interface BookingRepositoryInterface
{

    public function get_bookings_list();

    public function get_booking_detail($id);

    public function get_bookings_list_filtered($filter);
}