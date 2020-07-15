<?php

namespace App\Repositories\Admin\Bookings;

interface AdminBookingRepositoryInterface
{

    public function get_bookings_list();

    public function get_booking_detail($id);

    public function get_admin_bookings_list_filtered($filter);
}