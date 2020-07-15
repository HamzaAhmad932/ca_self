<?php 

return [
     'tableDetails' => [
        'system_usage ' => 'Card details including (number, cvc, expiry) are saved in this column after encryption using encryptCard function of Card Class, to Decrypt this string use decryptCard function of Card Class',
    ],
    'status' => [
        'Created' => 1,
        'Scheduled' => 2,
        'In-Retry' => 3,
        'Failed' => 4,
        'Void' => 5, // In case of BT when we do not create customer object by default unless guest/client add's new card.
        'Gateway-Missing' => 10 // When User Payment Gateway is not set
    ],
    'status-description' => [
        'Created' => 1,
        'Scheduled' => 2,
        'In-Retry' => 3,
        'Failed' => 4,
        'Void' => 5,
        'Gateway-Missing' => 10
    ],
    'type'=>[
        'customer_obj_created'=> 1, //customer object created
        'customer_obj_not_created'=> 2, //customer object not created
        'token_received'=> 3, // booking token found
        'token_not_received'=> 4, // booking token not found
        'credit_card_found'=>5, // credit card found in get card response or booking response
        'missing_card_number'=> 6, //card number is missing in get card response & booking response
        'empty'=> 7, //get card remote response not received
        'gateway_missing' => 10
    ],
    'is_default'=>[
        'default_card'=> 1,
        'old_card'=> 0
    ]
];
