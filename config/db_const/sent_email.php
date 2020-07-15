<?php

use App\UserAccount;

return [

    "activated_email_library" => "App\Repositories\EmailComponent\EmailSendingRepository",

    "all_emails" =>[

        /*
        |--------------------------------------------------------------------------
        | Client Emails (Start)
        |--------------------------------------------------------------------------
        |
        | Client emails related config content
        |
        */

        "email_verification_new_user" => [
            "email_type" => "email_verification_new_user",
            "email_title" => "New Client Signup",
            "subject_text" => "ChargeAutomation: Verify your email address",
            "main_class" => "\App\User",
            "job_class" => "\App\Jobs\EmailJobs\Client\EmailVerificationClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user" => [ "id", "email"]
            ]
        ],
        "team_member_invite" => [
            "email_type" => "team_member_invite",
            "email_title" => "Team Member Invited",
            "subject_text" => " has invited you to join ChargeAutomation",
            "main_class" => "\App\User",
            "job_class" => "\App\Jobs\EmailJobs\Client\TeamMemberInviteClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user" => [ "id", "email"]
            ]
        ],
        "team_member_added_inform_client" => [
            "email_type" => "team_member_added_inform_client",
            "email_title" => "Team Member Notify",
            "subject_text" => "ChargeAutomation: Team Member Invited",
            "main_class" => "\App\User",
            "job_class" => "\App\Jobs\EmailJobs\Client\InformTeamMemberAddedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user" => [ "id", "email"]
            ]
        ],
        "password_reset" => [
            "email_type" => "password_reset",
            "email_title" => "Password Reset",
            "subject_text" => "Reset Your ChargeAutomation Password",
            "main_class" => "\App\User",
            "job_class" => "\App\Jobs\EmailJobs\Client\PasswordResetClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user" => [ "id", "email"]
            ]
        ],
        "missing_billing_info" => [
            "email_type" => "missing_billing_info",
            "email_title" => "Missing Billing Info",
            "subject_text" => "🔺Missing Billing Info",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\MissingBillingClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_account" => [ "id", "name", "email"]
            ]
        ],
        "new_booking_received" => [
            "email_type" => "new_booking_received",
            "email_title" => "New Booking",
            "subject_text" => "New Booking",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\NewBookingClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "booking_cancelled" => [
            "email_type" => "booking_cancelled",
            "email_title" => "Booking Cancelled",
            "subject_text" => "Booking Cancelled",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\CancelBookingClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "booking_fetch_failed" => [
            "email_type" => "booking_fetch_failed",
            "email_title" => "Booking Fetch Failed",
            "subject_text" => "🔺Booking Fetch Failed",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\BookingFetchFailedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "payment_passed_due_date" => [
            "email_type" => "payment_passed_due_date",
            "email_title" => "Payment Overdue",
            "subject_text" => "🔺Payment Past Due",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\PaymentPassedDueDateClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "payment_aborted" => [
            "email_type" => "payment_aborted",
            "email_title" => "Payment Aborted",
            "subject_text" => "🔺Payment Aborted",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Client\PaymentAbortedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "transaction_info" => [ "id", "price", "type"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "payment_successful" => [
            "email_type" => "payment_successful",
            "email_title" => "Payment Successful",
            "subject_text" => "💰Payment Successful",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Client\PaymentSuccessfulClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "transaction_info" => [ "id", "price", "type"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "payment_failed" => [
            "email_type" => "payment_failed",
            "email_title" => "Payment Failed",
            "subject_text" => "🔺Payment Failed",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Client\PaymentFailedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "transaction_info" => [ "id", "price", "type"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "payment_collected_for_cancelled_booking" => [
            "email_type" => "payment_collected_for_cancelled_booking",
            "email_title" => "Cancellation Fee Collected",
            "subject_text" => "💰Payment collected for Cancelled Booking",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Client\PaymentCollectedForCancelledBookingClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "transaction_info" => [ "id", "price", "type"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "credit_card_invalid_to_client" => [
            "email_type" => "credit_card_invalid_to_client",
            "email_title" => "Credit Card Invalid",
            "subject_text" => "🔺Reservation credit card declined",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\CreditCardInvalidClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "credit_card_missing_to_client" => [
            "email_type" => "credit_card_missing_to_client",
            "email_title" => "Credit Card Missing",
            "subject_text" => "🔺Credit Card Missing",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\CreditCardMissingClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "guest_email_missing_to_client" => [
            "email_type" => "guest_email_missing_to_client",
            "email_title" => "Guest Email Address Missing",
            "subject_text" => "🔺Guest Email Address Missing",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\GuestEmailMissingClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "credit_card_not_added_payment_gateway_error" => [
            "email_type" => "credit_card_not_added_payment_gateway_error",
            "email_title" => "Payment Gateway Error",
            "subject_text" => "🔺Payment Gateway Error",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\CreditCardPaymentGatewayErrorClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "refund_successful" => [
            "email_type" => "refund_successful",
            "email_title" => "Refund Successful",
            "subject_text" => "🔄Auto Refund Issued",
            "main_class" => "\App\RefundDetail",
            "job_class" => "\App\Jobs\EmailJobs\Client\RefundSuccessfulClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "refund_info" => [ "id", "against_charge_ref_no", "amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "manual_refund_successful" => [
            "email_type" => "manual_refund_successful",
            "email_title" => "Manual Refund Successful",
            "subject_text" => "🔄Manual Refund Successful",
            "main_class" => "\App\RefundDetail",
            "job_class" => "\App\Jobs\EmailJobs\Client\ManualRefundSuccessfulClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "refund_info" => [ "id", "against_charge_ref_no", "amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "refund_failed" => [
            "email_type" => "refund_failed",
            "email_title" => "Refund Failed",
            "subject_text" => "🔺Auto Refund Failed",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Client\RefundFailedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "refund_info" => [ "id", "against_charge_ref_no", "amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "new_message_against_booking_client_email" => [
            "email_type" => "new_message_against_booking_client_email",
            "email_title" => "New Chat Message",
            "subject_text" => "📩Message Received",
            "main_class" => "\App\GuestCommunication",
            "job_class" => "\App\Jobs\EmailJobs\Client\NewChatMessageClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "guest_communication" => [ "id", "message"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "unable_to_contact_pms" => [
            "email_type" => "unable_to_contact_pms",
            "email_title" => "Unable To Connect PMS",
            "subject_text" => "🔺PMS sync failure",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\UnableToConnectPmsClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_account" => [ "id", "name"]
            ]
        ],
        "empty_property_key_received" => [
            "email_type" => "empty_property_key_received",
            "email_title" => "Empty Property Key",
            "subject_text" => "🔺Property Key Missing",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\EmptyKeysOnActivePropertiesClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_account" => [ "id", "name"]
            ]
        ],
        "document_uploaded_by_guest" => [
            "email_type" => "document_uploaded_by_guest",
            "email_title" => "Document Uploaded",
            "subject_text" => "Documents Uploaded",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Client\DocumentUploadedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "pre_checkin_completed" => [
            "email_type" => "pre_checkin_completed",
            "email_title" => "Pre Check-in Completed",
            "subject_text" => "Pre check-in received",
            "main_class" => "\App\GuestData",
            "job_class" => "\App\Jobs\EmailJobs\Client\PreCheckinCompletedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "booking_source_activated" => [
            "email_type" => "booking_source_activated",
            "email_title" => "Payment Rules Enabled",
            "subject_text" => "Booking Source Activated",
            "main_class" => "\App\UserBookingSource",
            "job_class" => "\App\Jobs\EmailJobs\Client\BookingSourceActivatedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_booking_source" => [ "id", "property_info_id"],
                "property_info" => [ "id", "name"]
            ]
        ],
        "booking_source_deactivated" => [
            "email_type" => "booking_source_deactivated",
            "email_title" => "Payment Rules Disabled",
            "subject_text" => "🔺Booking Source Deactivated",
            "main_class" => "\App\UserBookingSource",
            "job_class" => "\App\Jobs\EmailJobs\Client\BookingSourceDeactivatedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_booking_source" => [ "id", "property_info_id"],
                "property_info" => [ "id", "name"]
            ]
        ],
        "properties_activated" => [
            "email_type" => "properties_activated",
            "email_title" => "Property Enabled",
            "subject_text" => "Property(s) Activated",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\PropertyActivatedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_account" => [ "id", "name"]
            ]
        ],
        "properties_deactivated" => [
            "email_type" => "properties_deactivated",
            "email_title" => "Property Disabled",
            "subject_text" => "🔺Property(s) Deactivated",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\PropertyDeactivatedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_account" => [ "id", "name"]
            ]
        ],
        "ca_account_status_changed" => [
            "email_type" => "ca_account_status_changed",
            "subject_text" => "Account Status Changed",
            "main_class" => "\App\UserAccount",
            "job_class" => "\App\Jobs\EmailJobs\Client\CAAccountStatusChangeClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "user_account" => [ "id", "name"]
            ]
        ],
        "upsell_purchased_client" => [
            "email_type" => "upsell_purchased_client",
            "email_title" => "Upsell Purchased",
            "subject_text" => "New order received",
            "main_class" => \App\UpsellOrder::class,
            "job_class" => "\App\Jobs\EmailJobs\Client\NewUpsellOrderReceivedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "pms_booking_id"],
                "upsell_order" => [ "id", "booking_info_id"],
            ]
        ],
        "credit_card_authorization_failed" => [
            "email_type" => "credit_card_authorization_failed",
            "email_title" => "Credit Card Validation Failed",
            "subject_text" => "🔺Credit Card Validation Failed",
            "main_class" => \App\CreditCardAuthorization::class,
            "job_class" => "\App\Jobs\EmailJobs\Client\AuthorizationFailedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "credit_card_authorization_successful" => [
            "email_type" => "credit_card_authorization_successful",
            "email_title" => "Credit Card Validated",
            "subject_text" => "🔒Credit Card Validated",
            "main_class" => \App\CreditCardAuthorization::class,
            "job_class" => "\App\Jobs\EmailJobs\Client\AuthorizationSuccessfulClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "sd_authorization_failed" => [
            "email_type" => "sd_authorization_failed",
            "email_title" => "Damage Deposit Failed",
            "subject_text" => "🔺Damage Deposit Failed",
            "main_class" => \App\CreditCardAuthorization::class,
            "job_class" => "\App\Jobs\EmailJobs\Client\SdFailedClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "sd_authorization_successful" => [
            "email_type" => "sd_authorization_successful",
            "email_title" => "Damage Deposit Authorized",
            "subject_text" => "🔒Damage Deposit Authorized",
            "main_class" => \App\CreditCardAuthorization::class,
            "job_class" => "\App\Jobs\EmailJobs\Client\SdSuccessfulClientEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],



        /*
        |--------------------------------------------------------------------------
        | Guest Emails (Start)
        |--------------------------------------------------------------------------
        |
        | Guest emails related config content
        |
        */


        "guest_reservation" => [
            "email_type" => "guest_reservation",
            "email_title" => "Please Pre Check-in",
            "subject_text" => "[Action Required] Complete Your Pre Check-In",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Guest\BookingConfirmationGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Pre-Checkin", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "payment_failed_guest" => [
            "email_type" => "payment_failed_guest",
            "email_title" => "Payment Failed",
            "subject_text" => "[Action Required] Reservation Payment Failed",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Guest\PaymentFailedGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Payment-Failed", "Email-To-Guest"],
            "data_to_save" => [
                "credit_card_info" => [ "id", "cc_last_4_digit"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "credit_card_missing_to_guest" => [
            "email_type" => "credit_card_missing_to_guest",
            "email_title" => "Credit Card Missing",
            "subject_text" => "[Action Required] Payment Information Required",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Guest\CreditCardMissingGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-CC-Missing", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "credit_card_invalid_to_guest" => [
            "email_type" => "credit_card_invalid_to_guest",
            "email_title" => "Credit Card Invalid",
            "subject_text" => "[Action Required] Invalid Credit Card",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Guest\CreditCardInvalidGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-CC-Invalid", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "new_chat_message_email_to_guest" => [
            "email_type" => "new_chat_message_email_to_guest",
            "email_title" => "New Chat Message",
            "subject_text" => "New Message Received From ",
            "main_class" => "\App\GuestCommunication",
            "job_class" => "\App\Jobs\EmailJobs\Guest\NewChatMessageGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Chat-Msg", "Email-To-Guest"],
            "data_to_save" => [
                "guest_communication" => [ "id", "message"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "auth_3ds_required_guest" => [
            "email_type" => "auth_3ds_required_guest",
            "email_title" => "Auth 3DS authentication",
            "subject_text" => "[Action Required] 3D Secure Authentication Required",
            "main_class" => "\App\CreditCardAuthorization",
            "job_class" => "\App\Jobs\EmailJobs\Guest\Auth3DSGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Auth-3DS", "Email-To-Guest"],
            "data_to_save" => [
                "authorization_info" => [ "id", "hold_amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "sd_3ds_required" => [
            "email_type" => "sd_3ds_required",
            "email_title" => "SD 3DS authentication",
            "subject_text" => "[Action Required] 3D Secure Authentication Required",
            "main_class" => "\App\CreditCardAuthorization",
            "job_class" => "\App\Jobs\EmailJobs\Guest\SD3DSGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-SD-3DS", "Email-To-Guest"],
            "data_to_save" => [
                "authorization_info" => [ "id", "hold_amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "charge_3ds_required" => [
            "email_type" => "charge_3ds_required",
            "email_title" => "Charge 3DS authentication",
            "subject_text" => "[Action Required] 3D Secure Authentication Required",
            "main_class" => "\App\TransactionInit",
            "job_class" => "\App\Jobs\EmailJobs\Guest\Charge3DSGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Charge-3DS", "Email-To-Guest"],
            "data_to_save" => [
                "transaction_info" => [ "id", "price"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "sd_required_for_vc_booking" => [
            "email_type" => "sd_required_for_vc_booking",
            "email_title" => "SD Required",
            "subject_text" => "[Action Required] Refundable Security Deposit Required",
            "main_class" => "\App\BookingInfo",
            "job_class" => "\App\Jobs\EmailJobs\Guest\SdRequiredGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-SD-Required", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "sd_failed_guest" => [
            "email_type" => "sd_failed_guest",
            "email_title" => "SD Failed",
            "subject_text" => "[Action Required] Security Deposit Authorization Failed",
            "main_class" => "\App\CreditCardAuthorization",
            "job_class" => "\App\Jobs\EmailJobs\Guest\SdFailedGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-SD-Failed", "Email-To-Guest"],
            "data_to_save" => [
                "authorization_info" => [ "id", "hold_amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "auth_failed" => [
            "email_type" => "auth_failed",
            "email_title" => "Auth Failed",
            "subject_text" => "[Action Required] Authorization Failed",
            "main_class" => "\App\CreditCardInfo",
            "job_class" => "\App\Jobs\EmailJobs\Guest\AuthFailedGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Auth-Failed", "Email-To-Guest"],
            "data_to_save" => [
                "authorization_info" => [ "id", "hold_amount"],
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "gateway_disabled_auto_to_client" => [
            "email_type" => "gateway_disabled_auto_to_client",
            "email_title" => "Gateway Integration with ChargeAutomation Requires immediate attention",
            "subject_text" => "Gateway Integration with ChargeAutomation Requires immediate attention",
            "main_class" => "\App\UserPaymentGateway",
            "job_class" => "\App\Jobs\EmailJobs\GatewayDisabledAutoEmailJob",
            "layout" => "\App\Mail\ClientLayoutEmail",
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
        "upsell_marketing_to_guest" => [
            "email_type" => "upsell_marketing_to_guest",
            "email_title" => "Upsell Available Reminder",
            "subject_text" => "Awesome Services available for your stay",
            "main_class" => \App\BookingInfo::class,
            "job_class" => \App\Jobs\EmailJobs\Guest\UpsellMarketingEmailJob::class,
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Upsell-Marketing", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "extra_data" => ['upsell_ids'],
            ]
        ],
        "upsell_purchased_guest" => [
            "email_type" => "upsell_purchased_guest",
            "email_title" => "Upsell Purchased",
            "subject_text" => "Order Received",
            "main_class" => \App\UpsellOrder::class,
            "job_class" => "\App\Jobs\EmailJobs\Guest\UpsellOrderPurchasedGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Upsell-Purchased", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "pms_booking_id"],
                "upsell_order" => [ "id", "booking_info_id"],
            ]
        ],
        "refund_successful_to_guest" => [
            "email_type" => "refund_successful_to_guest",
            "email_title" => "Refund Issued",
            "subject_text" => "Refund Issued",
            "main_class" => App\RefundDetail::class,
            "job_class" => "\App\Jobs\EmailJobs\Guest\RefundSuccessfulGuestEmailJob",
            "layout" => "\App\Mail\GuestLayoutEmail",
            "tags" => ["Guest-Refund-Success", "Email-To-Guest"],
            "data_to_save" => [
                "booking_info" => [ "id", "guest_name"],
                "user_account" => [ "id", "name"]
            ]
        ],
    ],
    "sent_to" => [
        "admin" => "1",
        "client" => "2",
        "guest" => "3",
    ],
    "sent_to_label" => [
        "1" => "Admin",
        "2" => "Host",
        "3" => "Guest",
    ],
];
