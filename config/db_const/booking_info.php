<?php


    return [
        'pms_booking_status' => [
            'Cancelled' => 0,
            'Confirmed' => 1,
            'New' => 2,
            'Request' => 3,
            'Black' => 4,
        ],

        'pms_booking_status_badge_color'=>[
            0 => 'badge badge-danger', //cancelled
            1 => 'badge badge-success',
            2 => 'badge badge-info',
            3 => 'badge badge-info',
            4 => 'badge badge-danger',
        ],

        'booking_older_than_24_hours' => [
            0 => 'Not Older',
            1 => 'Older',
        ],

        'is_manual' => [
            0 => 'Notification',
            1 => 'Manual',
        ],

        'record_source' => [
            1 => 'Upon Notification',
            2 => 'Via Scheduled Job',
        ],

        'is_process_able' => [
            0 => 'Booking Not Processable or Invalid',
            1 => 'Booking Processable or Valid',
            2 => 'payment Gateway not verified or not attached yet for its property'
        ],

        'payment_gateway_effected' => [
            0 => 'Valid Booking, Payment Gateway Settings are not changed after Booking Received',
            1 => 'Not Valid Booking, Payment Gateway Settings are changed after Booking Received its transactions effected or void due to change',
        ],

        'is_vc' => [
            'BT' => 'Bank Transfer',
            'CC' => 'Credit Card',
            'VC' => 'Virtual Card',
        ],

    ];