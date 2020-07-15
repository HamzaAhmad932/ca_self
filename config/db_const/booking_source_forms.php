<?php

return [
    'channelCode' => [
        'bookingDotCom'  => 19,
        'others' => 999,
    ],

    /*
    * Airbnb Ical - 10 and Airbnb XML 46 both channel codes are for Airbnb
    * We are using 101 internal code for Airbnb
    */
    'duplicate_channel_codes' => [10, 46],
	'internal_channel_codes' =>[
    	10 => 101,
    	46 => 101
    ],

    'type' => [
        'VC' => 1,
        'CC' => 2,
        'BankDraft'=> 3,
        'VC_CC' => 4,
        'VC_CC_BankDraft' => 5,
        'Not_Supported' => 6,

        'vc_supported_types' => [1, 4, 5],
        'cc_supported_types' => [2, 4, 5],
        'bt_supported_types' => [4, 5],
    ],


];