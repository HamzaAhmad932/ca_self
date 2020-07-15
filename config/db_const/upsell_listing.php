<?php
 return [

     'upsell_commission'=> 10, //10 percent

     'status' => [
         'active' => [
             'label' => 'Activate',
             'value' => 1,
             'desc' => 'Active'
         ],
         'inactive' => [
             'label' => 'Inactivate',
             'value' => 0,
             'desc' => 'Inactivate'
         ],

         'get_key' => [
             0 => 'inactive',
             1 => 'active',
         ]
     ],

     'value_type' => [
         'flat' => [
             'label' => 'Flat Amount',
             'value' => 1,
             'desc' => 'Flat Amount'
         ],
         'percentage' => [
             'label' => 'Percentage',
             'value' => 2,
             'desc' => 'Percentage'
         ],

         'get_key' => [
             1 => 'flat',
             2 => 'percentage',
         ]
     ],

     'period' => [
         'one_time' => [
             'label' => 'One Time',
             'value' => 1,
             'desc' => 'One Time'
         ],
         'daily' => [
             'label' => 'Daily',
             'value' => 2,
             'desc' => 'Daily'
         ],

         'get_key' => [
             1 => 'one_time',
             2 => 'daily',
         ]
     ],

     'per' => [
         'per_booking' => [
             'label' => 'Per Booking',
             'value' => 1,
             'desc' => 'Per Booking'
         ],
         'per_person' => [
             'label' => 'Per Person',
             'value' => 2,
             'desc' => 'Per Person'
         ],

         'get_key' => [
             1 => 'per_booking',
             2 => 'per_person',
         ]
     ],

     'notify_guest' => [
             'label' => 'Remind Email Before Check-in',
             'value' => 0, // user defines days in integer, 0 => do not Notify
             'desc' => 'Send reminder emails to guest to purchase available upsells before check-in days'
     ],

 ];
