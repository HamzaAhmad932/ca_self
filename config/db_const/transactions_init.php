<?php

//===============================================================
// This file is used for transcations's status text
// we can change the only values in case of requirments
//                  NOT KEYS OF THE ARRAYS
// it may harmfull for the project...
//===============================================================


    return [
        'is_modified' => [

            0 => 'No',
            1 => 'Yes'
        ],
        'payment_status' => [
            0 => 'Fail',
            1 => 'Success',
            2 => 'Scheduled',
            3 => 'Void',
            4 => 'Re-attempt',
            5 => 'In-Approval',
            6 => 'Aborted',
            11 => 'Paused - Property Disabled',
            12 => 'Manually Void',
            13 => 'Payment Marked As Paid',
            ''=> 'In Process'
        ],
        'payment_status_messages_for_guest'=>[
            0 => 'Failed',  //Fail
            1 => 'Paid', //Success
            2 => 'Scheduled',
            3 => 'Voided',
            4 => 'Scheduled',  //Re-attempt
            5 => 'In-Approval',
            6 => 'Aborted',
            11 => 'Paused',
            12 => 'Voided',   //Manually Void
            13 => 'Paid',     //Payment Marked As Paid
            '' => 'In Process'
        ],
        'status_icon'=> [
            0 => 'fas fa-exclamation-triangle',
            1 => 'fas fa-check-circle',
            2 => 'fas fa-clock',
            3 => 'fas fa-ban',
            4 => 'fas fa-clock',
            5 => 'fas fa-clock',
            6 => 'fas fa-pause-circle',
            11 => 'fas fa-pause-circle',
            12 => 'fas fa-clock',
            13 => 'fas fa-check-circle',
            '' => 'fas fa-spinner'
        ],
        'lets_process' => [

            0 => 'No',
            1 => 'Yes'
        ],
        'final_tick' => [

            0 => 'No',
            1 => 'Yes'
        ],
        'status' => [

            0 => 'No',
            1 => 'Yes'
        ],

        'transaction_type' => [

            1 => 'Payment',
            2 => 'Payment #1',
            3 => 'Payment #2',
            4 => 'Refund',
            5 => 'Refund',
            6 => 'Payment (Manual)',
            8 => 'Payment #1 (Manual)',
            9 => 'Payment #2 (Manual)',
            10 => 'Refund (Manual)',
            11 => 'Refund (Manual)',
            12 => 'Auto Security Deposit Collection Full',
            13 => 'Auto Security Deposit Refund Full',
            14 => 'Manual Security Deposit Collection', /**Manual Security Deposit Collection */
            15 => 'Manual Security Deposit Refund', /**Manual Security Deposit Refund Full */
            16 => 'Manual Security Deposit Refund Partial',
            17 => 'Additional Charge',
            18 => 'Cancellation Fee',
            23 => 'Refund'
        ],

        'transaction_type_db_name' => [

            1 => '100% Payment of Booking Amount (Auto Collect)',
            2 => '1 of 2 Partial Payment of Booking (Auto Collect)',
            3 => '2 of 2 Partial Payment of Booking (Auto Collect)',
            4 => '100% Refund of Booking Amount (Auto Refund)',
            5 => 'Partial Refund of Booking Amount (Auto Refund)',
            6 => '100%  of Booking Payment (Manual Collect)',
            7 => 'Partial Payment of Booking (Manual Collect)',
            8 => '1 of 2 Partial Booking Payment (Manual Collect)',
            9 => '2 of 2 Partial Booking Payment (Manual Collect)',
            10 => '100% Refund of Booking Amount (Manual Refund)',
            11 => 'Partial Refund of Booking Amount (Manual Refund)',
            12 => 'Security Deposit Capture (Auto)',
            13 => '100% Refund of Security Deposit (Auto Refund)',
            14 => 'Security Deposit Capture (Manual)', /**Manual Security Deposit Collection */
            15 => '100% Refund of Security Deposit (Manual Refund)', /**Manual Security Deposit Refund Full */
            16 => 'Partial Refund of Security Deposit (Manual Refund)',
            17 => 'Additional Charge (Manual)',
            18 => 'Cancellation Fee Collection (Auto)',
            19 => 'Security Deposit (Auto Authorize)',
            20 => 'Security Deposit (Manual Authorize)',
            21 => 'Credit Card Authorization (Auto)',
            22 => 'Credit Card Authorization (Manual)',
            23 => 'Auto Refund'

        ],
        'type' =>[
            'C' => 'Charge',
            'R' => 'Refund',
            'M' => 'Additional Charge (Charge more)',
            'S' => 'Security Damage Deposit',
            'SR'=> 'Security Damage Deposit Refund',
            'CS'=> 'Security Damage Capture'
        ],
        'transaction_type_button_color' =>[
            1  => 'm-badge m-badge--info m-badge--wide',
            2  => 'm-badge m-badge--info m-badge--wide',
            3  => 'm-badge m-badge--info m-badge--wide',
            14 => 'm-badge  m-badge--success m-badge--wide',
            15 => 'm-badge  m-badge--info m-badge--wide',
            17 => 'm-badge m-badge--secondary m-badge--wide',
            23 => 'm-badge m-badge--warning m-badge--wide',
            10 => 'm-badge m-badge--warning m-badge--wide',
            18 => 'm-badge m-badge--warning m-badge--wide',
        ],
        'transaction_type_status_color'=> [

            0 => 'badge-danger',
            1 => 'badge-success',
            2=> 'badge-warning',
            3=> 'badge-warning',
            4 => 'badge-warning',
            5 => 'badge-info',
            11 => 'badge-warning',
            13 => 'badge-success',
            '' => 'badge-warning',
        ],
        'status_button_color' => [
            0 => 'm-badge  m-badge--danger m-badge--wide',
            1 => 'm-badge  m-badge--accent m-badge--wide',
            2=> 'm-badge  m-badge--warning m-badge--wide',
            3=> 'm-badge  m-badge--brand m-badge--wide',
            4 => 'm-badge  m-badge--warning m-badge--wide',
            5 => 'm-badge  m-badge--info m-badge--wide',
            11 => 'm-badge  m-badge--warning m-badge--wide',
            'empty' => 'm-badge  m-badge--danger m-badge--wide',
        ],
        'securityDepositTypes' => ['S','SR','CS'],
        'in_processing' => [
            'TRANSACTION_AVAILABLE_TO_PROCESS' => 0,            /* Transaction Init Available to charge or Reattempt | Not Being Processing in Queue  */
            'TRANSACTION_ADDED_IN_QUEUE_PROCESSING' => 1,       /*  Transaction Init Being Processing in Queue  & Not Available to charge Manually    */
            'TRANSACTION_ADDED_IN_MANUAL_PROCESSING' => 2,       /*   Transaction Init Being Processing  Manually & Not Available to charge By Queue  */
        ],

    ];
