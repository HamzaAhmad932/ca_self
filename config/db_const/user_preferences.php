<?php 

return [
    'preferences' => [
        'PAYMENT_SUCCESS' => 1,
        'PAYMENT_FAILED' => 2,
        'BOOKINGS_THAT_WILL_BE_CHARGED_IN_FUTURE' => 3,
        'UNAVAILABLE_CARD_DETAILS' => 4,
        'BOOKINGS_THAT_WILL_NOT_BE_CHARGED' => 5,
        'PAYMENT_COLLECTION_ON_CANCELLATION' => 6,
        'ADJUSTING_ENTRIES_FOR_CANCELED_BOOKINGS' => 7,
        'SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'=> 8,
        'SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED' => 9,
        'SECURITY_DEPOSIT_REFUND_SUCCESS' => 10,
        'SECURITY_DEPOSIT_REFUND_FAILED' => 11,
        'CREDIT_CARD_VALIDATION_AUTH_SUCCESS' => 12,
        'CREDIT_CARD_VALIDATION_AUTH_FAILED' =>13,
        'VERIFICATION_DOCUMENTATION_UPLOADED_SUCCESSFULLY' =>14,
        'VERIFICATION_DOCUMENTATION_UPLOADED_FAIL' => 15,
        'PAYMENT_MANUALLY_VOIDED' => 16,
        'PAYMENT_MANUALLY_MARKED_AS_PAID' => 17,
        'UPSELL' => 18,
        'PRE_CHECKIN_COMPLETE' => 19,
        'TERMS_AND_CONDITIONS_COMPLETE' => 20,
    ],
    
    'icons' => [
        // form_id => icon_class
        1 => 'fas fa-money-check-alt',
        2 => 'fas fa-money-check-alt',
        3 => 'fas fa-calendar-alt',
        4 => 'fas fa-credit-card',
        5 => 'fas fa-calendar-times',
        6 =>'fas fa-clipboard-check',
        7 => 'fas fa-clipboard-check',
        8 => 'fas fa-hand-holding-usd',
        9 => 'fas fa-hand-holding-usd',
        10 => 'fas fa-undo-alt',
        11 => 'fas fa-undo-alt',
        12 => 'fas fa-credit-card',
        13 => 'fas fa-credit-card',
        14 => 'fas fa-passport',
        15 => 'fas fa-passport',
        16 => 'fas fa-hand-holding-usd',
        17 => 'fas fa-hand-holding-usd',
        18 => 'fas fa-cart-arrow-down',
        19 => 'fas fa-clipboard-check',
        20 => 'fas fa-clipboard-list',
    ]

];