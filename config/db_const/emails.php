<?php
return [
    /** Details About For Whom To Send Email*/

    "send_to" => [
        1 => [
            "id"=>1,
            "name"=>"Admin",
            "job"=>"\App\Jobs\EmailJobs\EmailsJob",
            "queue"=>"send_email",
            "template"=>""
        ],
        2 => [
            "id"=>2,
            "name"=>"Client",
            "job"=>"\App\Jobs\EmailJobs\EmailJob",
            "queue"=>"send_email",
            "template"=>"\App\Mail\ClientLayoutEmail"
        ],
        3 => [
            "id"=>3,
            "name"=>"Guest",
            "job"=>"\App\Jobs\EmailJobs\EmailJob",
            "queue"=>"send_email",
            "template"=>"\App\Mail\GuestLayoutEmail"
        ],
    ],

    /** Email Type Heads */

    // 'customizable' => false "Client will not have any UI option to Edit this email content",
    // 'system_default' => true, "This Index will be used to filter and to avoid seeding db content for this email"

    "heads"=>[

        /** Emails From Client Only */
        "team_member_added_inform_client" => [
            'form_id' => 1,
            "type" => "team_member_added_inform_client",
            "title" => "Team Member Invited",
            "model" => \App\User::class,
            "skip_variables"=>["{User_Phone}"],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"newTeamMember",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "missing_billing_info" => [
            'form_id' => 2,
            "type" => "missing_billing_info",
            "title" => "Missing Billing Info",
            "model" => \App\User::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "booking_cancelled" => [
            'form_id' => 3,
            "type" => "booking_cancelled",
            "title" => "Booking Cancelled",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"cancelBooking",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "payment_passed_due_date" => [
            'form_id' => 4,
            "type" => "payment_passed_due_date",
            "title" => "Payment Overdue",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"paymentDecline",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_aborted" => [
            'form_id' => 5,
            "type" => "payment_aborted",
            "title" => "Payment Aborted",
            "model" => \App\TransactionDetail::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"paymentDecline",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_successful" => [
            'form_id' => 6,
            "type" => "payment_successful",
            "title" => "Payment Successful",
            "model" => \App\TransactionDetail::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"paymentSuccessful",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "payment_collected_for_cancelled_booking" => [
            'form_id' => 7,
            "type" => "payment_collected_for_cancelled_booking",
            "title" => "Cancellation Fee Collected",
            "model" => \App\TransactionDetail::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"cancelBooking",
                    "data_to_save" => [
                        "transaction_info" => ["id", "price", "type"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "manual_refund_successful" => [
            'form_id' => 8,
            "type" => "manual_refund_successful",
            "title" => "Manual Refund Successful",
            "model" => \App\RefundDetail::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"refundSuccessful",
                    "data_to_save" => [
                        "refund_info" => ["id", "against_charge_ref_no", "amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "refund_failed" => [
            'form_id' => 9,
            "type" => "refund_failed",
            "title" => "Refund Failed",
            "model" => \App\RefundDetail::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"refundDecline",
                    "data_to_save" => [
                        "refund_info" => ["id", "against_charge_ref_no", "amount"],
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "unable_to_contact_pms" => [
            'form_id' => 10,
            "type" => "unable_to_contact_pms",
            "title" => "Unable To Connect PMS",
            "model" => \App\UserAccount::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "empty_property_key_received" => [
            'form_id' => 11,
            "type" => "empty_property_key_received",
            "title" => "Empty Property Key",
            "model" => \App\UserAccount::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "document_uploaded" => [
            'form_id' => 12,
            "type" => "document_uploaded",
            "title" => "Document Uploaded",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"OnGuestDocumentsUpload",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "pre_checkin_completed" => [
            'form_id' => 13,
            "type" => "pre_checkin_completed",
            "title" => "Pre Check-in Completed",
            "model" =>\App\GuestData::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"OnGuestDocumentsUpload",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "credit_card_authorization_failed" => [
            'form_id' => 14,
            "type" => "credit_card_authorization_failed",
            "title" => "Credit Card Validation Failed",
            "model" => \App\AuthorizationDetails::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"paymentDecline",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "credit_card_authorization_successful" => [
            'form_id' => 15,
            "type" => "credit_card_authorization_successful",
            "title" => "Credit Card Validated",
            "model" => \App\AuthorizationDetails::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"paymentSuccessful",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "sd_authorization_successful" => [
            'form_id' => 16,
            "type" => "sd_authorization_successful",
            "title" => "Damage Deposit Authorized",
            "model" => \App\AuthorizationDetails::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"paymentSuccessful",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "gateway_disabled_auto_to_client" => [
            'form_id' => 36,
            "type" => "gateway_disabled_auto_to_client",
            "title" => "Auto Payment Gateway Disabled",
            "model" => \App\UserPaymentGateway::class,
            'customizable' => false,
            'system_default' => true,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "booking_source_activated" => [
            'form_id' => 31,
            "type" => "booking_source_activated",
            "title" => "Payment Rules Enabled",
            "model" => \App\UserBookingSource::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "user_booking_source" => ["id", "property_info_id"],
                        "property_info" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "booking_source_deactivated" => [
            'form_id' => 32,
            "type" => "booking_source_deactivated",
            "title" => "Payment Rules Disabled",
            "model" => \App\UserBookingSource::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "user_booking_source" => ["id", "property_info_id"],
                        "property_info" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "ca_account_status_changed" => [
            'form_id' => 33,
            "type" => "ca_account_status_changed",
            "title"=> "CA Account Status Changed",
            "model" => \App\UserAccount::class,
            'customizable' => false,
            'system_default' => true,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name", 'status']
                    ]
                ]
            ]
        ],
        "properties_activated" => [
            'form_id' => 34,
            "type" => "properties_activated",
            "title" => "Property Enabled",
            "model" => \App\UserAccount::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "properties_deactivated" => [
            'form_id' => 35,
            "type" => "properties_deactivated",
            "title" => "Property Disabled",
            "model" => \App\UserAccount::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "password_reset" => [
            'form_id' => 37,
            "type" => "password_reset",
            "title" => "Password Reset",
            "model" => \App\User::class,
            'customizable' => false,
            //'system_default' => true,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "email_verification_new_user" => [
            'form_id' => 38,
            "type" => "email_verification_new_user",
            "title" =>'New Client Sign-up',
            "model" => \App\User::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "team_member_invite" => [
            'form_id' => 39,
            "type" => "team_member_invite",
            "title" => "Team Member Invitation",
            "model" => \App\User::class,
            'customizable' => false,
            'system_default' => true,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => ["user" => [ "id", "email"]],
                ]
            ]
        ],
        "booking_fetch_failed" => [
            'form_id' => 40,
            "type" => "booking_fetch_failed",
            "title" => "Booking Fetch Failed",
            "model" => \App\UserAccount::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],
        "credit_card_not_added_payment_gateway_error" => [
            'form_id' => 41,
            "type" => "credit_card_not_added_payment_gateway_error",
            "title" => "Payment Gateway Error",
            "model" => \App\BookingInfo::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"bookingCardDetailsMissing",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],
        "guest_email_missing" => [
            'form_id' => 42,
            "type" => "guest_email_missing",
            "title" => "Guest Email Address Missing",
            "model" => \App\BookingInfo::class,
            "skip_variables"=>["{Guest_Email}"],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ],
                ]
            ]
        ],

        "properties_unavailable_on_pms" => [
            'form_id' => 44,
            "type" => "properties_unavailable_on_pms",
            "title" => "Property(s) Disabled",
            "model" => \App\UserAccount::class,
            'customizable' => false,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"accountStatus",
                    "data_to_save" => [
                        "user_account" => ["id", "name"]
                    ]
                ]
            ]
        ],


        /** Emails For Guest Only  */

        "auth_3ds_required" => [
            'form_id' => 17,
            "type" => "auth_3ds_required",
            "title" => "Auth 3DS authentication",
            "model" => \App\CreditCardAuthorization::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
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
        "sd_3ds_required" => [
            'form_id' => 18,
            "type" => "sd_3ds_required",
            "title" => "SD 3DS authentication",
            "model" => \App\CreditCardAuthorization::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
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
            'form_id' => 19,
            "type" => "charge_3ds_required",
            "title" => "Charge 3DS authentication",
            "model" => \App\TransactionInit::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
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
            'form_id' => 20,
            "type" => "sd_required_for_vc_booking",
            "title" => "SD Required",
            "model" => \App\CreditCardAuthorization::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
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
            'form_id' => 21,
            "type" => "auth_failed",
            "title" => "Auth Failed",
            "model" => \App\AuthorizationDetails::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
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
            'form_id' => 29,
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
            'form_id' => 22,
            "type" => "new_booking",
            "title" => "New Booking",
            "model" => \App\BookingInfo::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"newBooking",
                    "data_to_save" => [
                        "booking_info" => [ "id", "guest_name"],
                        "user_account" => [ "id", "name"]
                    ]
                ],
                "guest"=>[
                    "id"=>3,
                    "check_guest_experience_settings"=>"emailToGuest",
                    "data_to_save" => [
                        "booking_info" => ["id", "guest_name"],
                        "user_account" => ["id", "name"]
                    ]
                ]
            ],
        ],
        "credit_card_missing"=>[
            'form_id' => 23,
            "type" => "credit_card_missing",
            "title"=> "Credit Card Missing",
            "model"=> \App\CreditCardInfo::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client" => [
                    "id"=>2,
                    "check_notify_settings"=>"bookingCardDetailsMissing",
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
            'form_id' => 24,
            "type" => "credit_card_invalid",
            "title"=> "Credit Card Invalid",
            "model"=> \App\CreditCardInfo::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
               "client" => [
                   "id"=>2,
                   "check_notify_settings"=>"bookingCardDetailsMissing",
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
            'form_id' => 25,
            "type" => "payment_failed",
            "title"=> "Payment Failed",
            "model" => \App\TransactionInit::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client" => [
                    "id"=>2,
                    "check_notify_settings"=>"paymentDecline",
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
            'form_id' => 26,
            "type" => "refund_successful",
            "title"=> "Refund Successful",
            "model" => \App\RefundDetail::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client" =>  [
                    "id"=>2,
                    "check_notify_settings"=>"refundSuccessful",
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
            'form_id' => 27,
            "type"=> "new_chat_message",
            "title"=> "New Chat Message",
            "model" => \App\GuestCommunication::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"OnGuestCommunicationMessage",
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
        "sd_auth_failed"=>[
            'form_id' => 28,
            "type"=> "sd_auth_failed",
            "title"=> "SD Auth Failed",
            "model" => \App\CreditCardAuthorization::class,
            "extra_model_vars"=>[\App\PropertyInfo::class],
            "send_to"=>[
              "client"=>[
                  "id"=>2,
                  "check_notify_settings"=>"paymentDecline",
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
        "upsell_purchased"=>[
            'form_id' => 30,
            "type"=>"upsell_purchased",
            "title"=> "Upsell Purchased",
            "model" => \App\UpsellOrder::class,
            "send_to"=>[
                "client"=>[
                    "id"=>2,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "booking_info" => ["id", "pms_booking_id"],
                        "upsell_order" => ["id", "booking_info_id"],
                    ]
                ],
                "guest"=>[
                    "id"=>3,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "booking_info" => ["id", "pms_booking_id"],
                        "upsell_order" => ["id", "booking_info_id"],
                    ]
                ],
            ]
        ],

        "guest_document_rejected"=>[
            'form_id' => 43,
            "type"=>"guest_document_rejected",
            "title"=> "Document Rejected",
            "model" => \App\GuestImage::class,
            "send_to"=>[
                "guest"=>[
                    "id"=>3,
                    "check_notify_settings"=>"",
                    "data_to_save" => [
                        "booking_info" => ["id", "pms_booking_id"],
                    ]
                ],
            ]
        ],
    ]
];
