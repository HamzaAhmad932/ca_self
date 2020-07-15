<?php
return [
    /** Details About For Whom To Send Email*/

    "send_to" => [
        1 => [
            "id"=>1,
            "name"=>"Admin",
            "job"=>"\App\Jobs\EmailJobs\SendEmailsJob",
            "queue"=>"send_emails",
            "template"=>""
        ],
        2 => [
            "id"=>2,
            "name"=>"Client",
            "job"=>"\App\Jobs\EmailJobs\SendEmailsJob",
            "queue"=>"send_emails",
            "template"=>"\App\Mail\ClientLayoutEmail"
        ],
        3 => [
            "id"=>3,
            "name"=>"Guest",
            "job"=>"\App\Jobs\EmailJobs\SendEmailsJob",
            "queue"=>"send_emails",
            "template"=>"\App\Mail\GuestLayoutEmail"
        ],
    ],

    /** Email Type Heads */
    "heads"=>[

        /** Emails From Client Only */

        "email_verification_new_user" => [
            "type" => "email_verification_new_user",
            "title" =>'New Client Signup',
            "model" => \App\User::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "team_member_invite" => [
            "type" => "team_member_invite",
            "title" => "Team Member Invited",
            "model" => \App\User::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "team_member_added_inform_client" => [
            "type" => "team_member_added_inform_client",
            "title" => "Team Member Notify",
            "model" => \App\User::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"newTeamMember",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "password_reset" => [
            "type" => "password_reset",
            "title" => "Password Reset",
            "model" => \App\User::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "missing_billing_info" => [
            "type" => "missing_billing_info",
            "title" => "Missing Billing Info",
            "model" => \App\User::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "booking_cancelled" => [
            "type" => "booking_cancelled",
            "title" => "Booking Cancelled",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"cancelBooking",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "booking_fetch_failed" => [
            "type" => "booking_fetch_failed",
            "title" => "Booking Fetch Failed",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"newBooking",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_passed_due_date" => [
            "type" => "payment_passed_due_date",
            "title" => "Payment Overdue",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"paymentDecline",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_aborted" => [
            "type" => "payment_aborted",
            "title" => "Payment Aborted",
            "model" => \App\TransactionInit::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"paymentDecline",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_successful" => [
            "type" => "payment_successful",
            "title" => "Payment Successful",
            "model" => \App\TransactionInit::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"paymentSuccessful",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_collected_for_cancelled_booking" => [
            "type" => "payment_collected_for_cancelled_booking",
            "title" => "Cancellation Fee Collected",
            "model" => \App\TransactionInit::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"cancelBooking",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "credit_card_not_added_payment_gateway_error" => [
            "type" => "credit_card_not_added_payment_gateway_error",
            "title" => "Payment Gateway Error",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"bookingCardDetailsMissing",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "gateway_disabled_auto_to_client" => [
            "type" => "gateway_disabled_auto_to_client",
            "title" => "Auto Payment Gateway Disabled",
            "model" => \App\UserPaymentGateway::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"accountStatus",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "manual_refund_successful" => [
            "type" => "manual_refund_successful",
            "title" => "Manual Refund Successful",
            "model" => \App\RefundDetail::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"refundSuccessful",
                    "data_to_save" => [
                        "refund_info" => ["id", "against_charge_ref_no", "amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "refund_failed" => [
            "type" => "refund_failed",
            "title" => "Refund Failed",
            "model" => \App\TransactionInit::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"refundDecline",
                    "data_to_save" => [
                        "refund_info" => ["id", "against_charge_ref_no", "amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "unable_to_contact_pms" => [
            "type" => "unable_to_contact_pms",
            "title" => "Unable To Connect PMS",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "empty_property_key_received" => [
            "type" => "empty_property_key_received",
            "title" => "Empty Property Key",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "document_uploaded_by_guest" => [
            "type" => "document_uploaded_by_guest",
            "title" => "Document Uploaded",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"OnGuestDocumentsUpload",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "pre_checkin_completed" => [
            "type" => "pre_checkin_completed",
            "title" => "Pre Check-in Completed",
            "model" =>\App\GuestData::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"OnGuestDocumentsUpload",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "booking_source_activated" => [
            "type" => "booking_source_activated",
            "title" => "Payment Rules Enabled",
            "model" => \App\UserBookingSource::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"accountStatus",
                    "data_to_save" => [
                        "user_booking_source" => ["id", "property_info_id"],
                        "property_info" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "booking_source_deactivated" => [
            "type" => "booking_source_deactivated",
            "title" => "Payment Rules Disabled",
            "model" => \App\UserBookingSource::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"accountStatus",
                    "data_to_save" => [
                        "user_booking_source" => ["id", "property_info_id"],
                        "property_info" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "properties_activated" => [
            "type" => "properties_activated",
            "title" => "Property Enabled",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "properties_deactivated" => [
            "type" => "properties_deactivated",
            "title" => "Property Disabled",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "ca_account_status_changed" => [
            "type" => "ca_account_status_changed",
            "title"=> "CA Account Status Changed",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "credit_card_authorization_failed" => [
            "type" => "credit_card_authorization_failed",
            "title" => "Credit Card Validation Failed",
            "model" => \App\CreditCardAuthorization::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"paymentDecline",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "credit_card_authorization_successful" => [
            "type" => "credit_card_authorization_successful",
            "title" => "Credit Card Validated",
            "model" => \App\CreditCardAuthorization::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"paymentSuccessful",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "sd_authorization_successful" => [
            "type" => "sd_authorization_successful",
            "title" => "Damage Deposit Authorized",
            "model" => \App\CreditCardAuthorization::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"paymentSuccessful",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],

        /** Emails For Guest Only  */

        "auth_3ds_required_guest" => [
            "type" => "auth_3ds_required_guest",
            "title" => "Auth 3DS authentication",
            "model" => \App\CreditCardAuthorization::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "authorization_info" => ["id", "hold_amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "sd_3ds_required_guest" => [
            "type" => "sd_3ds_required_guest",
            "title" => "SD 3DS authentication",
            "model" => \App\CreditCardAuthorization::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "authorization_info" => ["id", "hold_amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                    ]
            ]
        ],
        "charge_3ds_required" => [
            "type" => "charge_3ds_required",
            "title" => "Charge 3DS authentication",
            "model" => \App\TransactionInit::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "transaction_info" => ["id", "price"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "sd_required_for_vc_booking" => [
            "type" => "sd_required_for_vc_booking",
            "title" => "SD Required",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "auth_failed" => [
            "type" => "auth_failed",
            "title" => "Auth Failed",
            "model" => \App\CreditCardInfo::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "authorization_info" => ["id", "hold_amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "upsell_marketing" => [
            "type" => "upsell_marketing",
            "title" => "Upsell Available Reminder",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "extra_data" => ['upsell_ids'],
                    ]
                ]
            ]
        ],


        /** Emails For Both Client and Guest  */


        "new_booking" => [
            "type" => "new_booking",
            "title" => "New Booking",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"newBooking",
                    "data_to_save" => [
                        "booking_info" => [ "id", "guest_name"],
                        "user_account" => [ "id", "name"]
                    ]
                ],
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ],
        ],
        "credit_card_missing"=>[
            "type" => "credit_card_missing",
            "title"=> "Credit Card Missing",
            "model"=> \App\BookingInfo::class,
            "send_to"=>[
                "client" => [
                    "id"=>2,
                    "check_notify_sittings"=>"bookingCardDetailsMissing",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ],
                "guest" => [
                    "id"=>3,
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "credit_card_invalid"=>[
            "type" => "credit_card_invalid",
            "title"=> "Cardit Card Invalid",
            "model"=> \App\BookingInfo::class,
            "send_to"=>[
               "client" => [
                   "id"=>2,
                    "check_notify_sittings"=>"bookingCardDetailsMissing",
                   "data_to_save" => [
                       "booking_info" => ["id", "guest_name"],
                       "user_account" => ["id", "name"]
                   ]
               ],
               "guest" => [
                   "id"=>3,
                   "data_to_save" => [
                       "booking_info" => ["id", "guest_name"],
                       "user_account" => ["id", "name"]
                   ]
               ],
           ]
        ],
        "payment_failed"=>[
            "type" => "payment_failed",
            "title"=> "Payment Failed",
            "model" => \App\TransactionInit::class,
            "send_to"=>[
                "client" => [
                    "id"=>2,
                    "check_notify_sittings"=>"paymentDecline",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ],
                "guest" => [
                    "id"=>3,
                    "data_to_save" => [
                        "credit_card_info" => ["id", "cc_last_4_digit"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "refund_successful"=>[
            "type" => "refund_successful",
            "title"=> "Refund Successful",
            "model" => \App\RefundDetail::class,
            "send_to"=>[
                "client" =>  [
                    "id"=>2,
                    "check_notify_sittings"=>"refundSuccessful",
                    "data_to_save" => [
                        "refund_info" => ["id", "against_charge_ref_no", "amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ] ,
                "guest"  => [
                    "id"=>3,
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ],
        ],
        "new_chat_message"=>[
            "type"=> "new_chat_message",
            "title"=> "New Chat Message",
            "model" => \App\GuestCommunication::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"OnGuestCommunicationMessage",
                    "data_to_save" => [
                        "guest_communication" => ["id", "message"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ],
                "guest"=>[
                    "id"=>3,
                    "data_to_save" => [
                        "guest_communication" => ["id", "message"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "upsell_purchased"=>[
            "type"=>"upsell_purchased",
            "title"=> "Upsell Purchased",
            "model" => \App\UpsellOrder::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"OnGuestCommunicationMessage",
                    "data_to_save" => [
                        "booking_info" => ["id", "pms_booking_id"],
                        "upsell_order" => ["id", "booking_info_id"],
                    ]
                ],
                "guest"=>[
                    "id"=>2,
                    "check_notify_sittings"=>"",
                    "data_to_save" => [
                        "booking_info" => ["id", "pms_booking_id"],
                        "upsell_order" => ["id", "booking_info_id"],
                    ]
                ],
            ]
        ],
        "sd_auth_failed"=>[
            "type"=> "sd_auth_failed",
            "title"=> "SD Auth Failed",
            "model" => \App\CreditCardAuthorization::class,
            "send_to"=>[
              "client"=>[
                  "id"=>2,
                  "check_notify_sittings"=>"paymentDecline",
                  "data_to_save" => [
                      "booking_info" => ["id", "guest_name"],
                      "user_account" => ["id", "name"]
                  ]
              ],
              "guest"=>[
                  "id"=>"3",
                  "data_to_save" => [
                      "authorization_info" => ["id", "hold_amount"],
                      "booking_info" => ["id", "guest_name"],
                      "user_account" => ["id", "name"]
                  ]
              ]
          ],
        ],
    ]
];
