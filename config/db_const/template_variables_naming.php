<?php

/*
 * To add new model(table) to handle dynamic templates use following pattern--
 * \App\ModelName::class => [
 *      'variables' => [
 *          'actual_column_name_in_db' => '{Unique_Template_Name}' //make sure this template name should not exist under any other model
 *          //add many more here
 *      ],
 *       'relationships' => [
 *          '\App\ModelNameRelationshipWith::class' => [
 *              'relationship_name' => 'actual_relationship_name_you_declared_in_above_main_model',
 *              'results' => 'single',   //only 2 values single/plural use plural for hasMany relation and single for other
 *              'first_or_last' => 'first', //optional -- only 2 values first/last -- if not passed we assume it as last -- Use this column only in case of plural result type. Meaning of this column is we need to inform in case of multiple result which record we need to use for dynamic data, either very latest or very old.
 *              'support_eager_loading' => false  //this is optional --if not passed we assume it as true -- So if relation don't support eager loading please pass this key as false
 *          ],
 *          //add many more relation here whose template variable we need to use via main model
 *      ]
 * ]
 */


/*
 * Note:- first_or_last column under relationships can only have values first OR last
 * Note:- results column under relationships can only have values single OR plural
 * Note:- support_eager_loading column under relationships is true by default. If any model don't support it declare it as false. For example check BookingInfo relation with PropertyInfo below
 */
return [
    /*
     * Model Named BookingInfo
     * List All Required relationships whose column we need to use for template variables
     */
    \App\BookingInfo::class => [
        'variables' => [
            'pms_booking_id' => '{PMS_Booking_ID}',
            'pms_booking_status' => '{PMS_Booking_Status}',
            'channel_code' => '{Booking_Channel_Code}',
            'guest_email' => '{Guest_Email}',
            'guest_title' => '{Guest_Title}',
            'guest_name' => '{Guest_First_Name}',
            'guest_last_name' => '{Guest_Last_Name}',
            'booking_time' => '{Booking_Time}',
            'check_in_date' => '{Checkin_Date}',
            'check_out_date' => '{Checkout_Date}',
            'total_amount' => '{Total_Amount}',
            'pms_id' => '{PMS_ID}',
            //'room_id' => '{Room_Id}',
            'num_adults' => '{Number_Of_Adults}',
            'guest_phone' => '{Guest_Phone}',
            'full_name' => '{Guest_Name}', //Accessors
        ],
        'relationships' => [
            \App\PropertyInfo::class => [
                'relationship_name' => 'property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\CreditCardInfo::class => [
                'relationship_name' => 'cc_Infos',
                'results' => 'plural',
                'first_or_last' => 'first' //because we have multiple records
            ],
            \App\UserAccount::class => [
                'relationship_name' => 'user_account',
                'results' => 'single'
            ],
            \App\User::class => [
                'relationship_name' => 'user',
                'results' => 'single'
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\GuestData::class => [
                'relationship_name' => 'guest_data',
                'results' => 'single'
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],
        ]
    ],

    /*
     * Model Named PropertyInfo
     * List All Required relationships whose column we need to use for template variables
     */
    \App\PropertyInfo::class => [
        'variables' => [
            'name' => '{Property_Name}',
            'pms_property_id' => '{Property_PMS_ID}',
            'property_key' => '{Property_PMS_Key}',
            'currency_code' => '{Property_Currency_Code}',
            'property_email' => '{Property_Email}',
            'time_zone' => '{Property_Timezone}',
            'address' => '{Property_Address}',
            'city' => '{Property_City}',
            'country' => '{Property_Country}'

        ],
        'relationships' => [
            \App\UserAccount::class => [
                'relationship_name' => 'user_account',
                'results' => 'single'
            ],
            \App\User::class => [
                'relationship_name' => 'user',
                'results' => 'single'
            ]
        ]
    ],

    /*
     * Another Model Named CreditCardInfo
     * List All Required relationships whose column we need to use for template variables
     */
    \App\CreditCardInfo::class => [
        'variables' => [
            'cc_last_4_digit' => '{Credit_Card_Last_4_Digits}',
            'card_name' => '{Full_Name_On_Credit_Card}',
            'f_name' => '{First_Name_On_Credit_Card}',
            'l_name' => '{Last_Name_On_Credit_Card}',
            'error_message' => '{Credit_Card_Response}',
        ],
        'relationships' => [
            \App\UserAccount::class => [
                'relationship_name' => 'userAccount',
                'results' => 'single'
            ],
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single'
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],

        ]
    ],

    /*
     * Another Model Named UserAccount
     * List All Required relationships whose column we need to use for template variables
     */
    \App\UserAccount::class => [
        'variables' => [
            'name' => '{Company_Name}',
            'email' => '{Company_Email}'
        ]
    ],

    /*
     * Another Model Named User
     * List All Required relationships whose column we need to use for template variables
     */
    \App\User::class => [
        'variables' => [
            'name' => '{User_Name}',
            'email' => '{User_Email}',
            'phone' => '{User_Phone}'
        ],
        'relationships' => [
            \App\UserAccount::class => [
                'relationship_name' => 'user_account',
                'results' => 'single'
            ]
        ]
    ],

    /*
     * Another Model Named GuestData
     * List All Required relationships whose column we need to use for template variables
     */
    \App\GuestData::class => [
        'variables' => [
            'name' => '{Guest_Name}',
            'arrivaltime' => '{Guest_Arrival_Time}',
            'arriving_by' => '{Guest_Arrival_By}',
            'plane_number' => '{Guest_Plane_Number}',
            'email' => '{Guest_Email}',
            'full_phone' => '{Guest_Phone}',
            'adults' => '{Number_Of_Adults}',
            'childern' => '{Number_Of_Children}',
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single'
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
        ]
    ],

    /*
         * Another Model Named GuestImage
         * List All Required relationships whose column we need to use for template variables
         */
    \App\GuestImage::class => [
        'variables' => [
            'readable_type' => '{Guest_Document_type}',
            'document_rejected_description' => '{Document_Rejected_Description}',
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single'
            ]
        ]
    ],

    \App\GuestImageDetail::class => [
        'variables' => [
            'readable_type' => '{Guest_Document_type}',
            'document_rejected_description' => '{Document_Rejected_Description}',
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single'
            ]
        ]
    ],

    \App\TransactionDetail::class => [
        'variables' => [
            'payment_processor_response' => '{Transaction_Response}',

        ],
        'relationships' => [
            \App\TransactionInit::class => [
                'relationship_name' => 'transaction_init',
                'results' => 'single',
            ],
            \App\UserAccount::class => [
                'relationship_name' => 'user_account',
                'results' => 'single',
            ],
            /*\App\PaymentGatewayForm::class => [
                'relationship_name' => 'payment_gateway_form',
                'results' => 'single',
            ],*/
            \App\CreditCardInfo::class => [
                'relationship_name' => 'ccinfo',
                'results' => 'single',
            ],
            \App\BookingInfo::class => [
                'relationship_name' => 'transaction_init.booking_info',
                'results' => 'single',
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'transaction_init.booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'transaction_init.booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'transaction_init.booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
        ]
    ],

    /*w
     * Another Model Named GuestData
     * List All Required relationships whose column we need to use for template variables
     */
    \App\TransactionInit::class => [
        'variables' => [
            'due_date' => '{Transaction_Due_Date}',
            'next_attempt_time' => '{Transaction_Next_Attempt_Date}',
            'price' => '{Transaction_Price}',
            'charge_ref_no' => '{Transaction_Reference_Number}',
            'transaction_type' => '{Transaction_Type}', //auto full | Partial etc
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single'
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\UserAccount::class => [
                'relationship_name' => 'user_account',
                'results' => 'single'
            ],
            \App\CreditCardInfo::class => [
                'relationship_name' => 'cc_info_latest',
                'results' => 'plural',
                'first_or_last' => 'last', //because we have multiple records
                'support_eager_loading' => false,
            ],
        ]
    ],

    /*
    * Another Model Named RefundDetail
    *
    */
    \App\RefundDetail::class => [
        'variables' => [
            'client_remarks' => '{Refund_Remarks}',
            'amount' => '{Refund_Amount}'
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single',
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\TransactionInit::class => [
                'relationship_name' => 'transaction_init',
                'results' => 'single',
            ],
            /*\App\TransactionDetail::class => [
                'relationship_name' => 'transaction_init.transactions_detail', //Sub relation
                'results' => 'single',
            ],*/
            /*\App\UserPaymentGateway::class => [
                'relationship_name' => 'user_payment_gateway',
                'results' => 'single',
            ],*/
            \App\UserAccount::class => [
                'relationship_name' => 'user_account',
                'results' => 'single',
            ],
        ]
    ],

    /*
    * Another Model Named GuestData
    * List All Required relationships whose column we need to use for template variables
    */
    \App\CreditCardAuthorization::class => [
        'variables' => [
            'due_date' => '{Authorization_Due_Date}',
            'next_due_date' => '{Authorization_Next_Attempt_Date}',
            'hold_amount' => '{Authorization_Price}',
            'token' => '{Authorization_Reference_Number}',
            'type' => '{Authorization_Type}', //auto full | Partial etc
        ],

        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\UserAccount::class => [
                'relationship_name' => 'userAccount',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\CreditCardInfo::class => [
                'relationship_name' => 'ccinfo',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
        ]
    ],


    /*
     * Another Model Named AuthorizationDetails
     * List All Required relationships whose column we need to use for template variables
     */
    \App\AuthorizationDetails::class => [
        'variables' => [
            'error_msg' => '{Authorization_Response}',
        ],
        'relationships' => [
            \App\CreditCardAuthorization::class => [
                'relationship_name' => 'cc_auth',
                'results' => 'single'
            ],
            \App\BookingInfo::class => [
                'relationship_name' => 'cc_auth.booking_info',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'cc_auth.booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'cc_auth.booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'cc_auth.booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\CreditCardInfo::class => [
                'relationship_name' => 'cc_auth.ccinfo',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
        ]
    ],

    /*
     * Another Model Named RoomInfo
     * List All Required relationships whose column we need to use for template variables
     */
    \App\RoomInfo::class => [
        'variables' => [
            'name' => '{Room_Name}',
            'pms_room_id' => '{Room_PMS_ID}'
        ],
        'relationships' => [
            \App\PropertyInfo::class => [
                'relationship_name' => 'property_info',
                'results' => 'single'
            ]
        ]
    ],

    /*
     * Another Model Named GuestCommunication
     * List All Required relationships whose column we need to use for template variables
     */
    \App\GuestCommunication::class => [
        'variables' => [
            'message' => '{Chat_Message}',
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'booking_info',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\RoomInfo::class => [
                'relationship_name' => 'booking_info.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_info.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'booking_info.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
        ]

    ],

    /*
     * Another Model Booking Source
     * List All Required relationships whose column we need to use for template variables
     */
    \App\BookingSourceForm::class => [
        'variables' => [
            'name' => '{BS_Name}',
        ],
    ],
    /*
     * Another Model Booking Source
     * List All Required relationships whose column we need to use for template variables
     */
    \App\UserBookingSource::class => [
        'variables' => [
            //'status' => '{BS_Status}'
        ],
        'relationships' => [
            \App\BookingSourceForm::class => [
                'relationship_name' => 'booking_source_form',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\PropertyInfo::class => [
                'relationship_name' => 'property_info',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\UserAccount::class => [
                'relationship_name' => 'User_account',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
        ],
    ],

    \App\UpsellOrder::class => [
        'variables' => [
            'final_amount' => '{Upsell_Order_Amount}',
            'charge_ref_no' => '{Upsell_Order_Charge_Reference}',
        ],
        'relationships' => [
            \App\BookingInfo::class => [
                'relationship_name' => 'bookingInfo',
                'results' => 'single',
                'support_eager_loading' => true,
            ],

            \App\RoomInfo::class => [
                'relationship_name' => 'bookingInfo.room_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],
            \App\BookingSourceForm::class => [
                'relationship_name' => 'bookingInfo.booking_source',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\PropertyInfo::class => [
                'relationship_name' => 'bookingInfo.property_info',
                'results' => 'single',
                'support_eager_loading' => false
            ],

            \App\CreditCardInfo::class => [
                'relationship_name' => 'ccInfo',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
            \App\UserAccount::class => [
                'relationship_name' => 'userAccount',
                'results' => 'single',
                'support_eager_loading' => true,
            ],
        ],
    ],
];
