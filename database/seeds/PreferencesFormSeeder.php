<?php

use App\PreferencesForm;
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: Suleman Afzal
 * Date: 29/03/19
 * Time: 11:22 PM
 */

class PreferencesFormSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // Truncate table to seed.
        PreferencesForm::truncate();

        PreferencesForm::create([
            'name' =>'PAYMENT SUCCESS',
            'form_id'=>config('db_const.user_preferences.preferences.PAYMENT_SUCCESS'),
            'form_data' => '{"flag_text":"Paid","flag_color":"#74E207","notes":"ChargeAutomation.com Msg - [chargedAtDateTime]\n[transactionType] successful\nTransaction ID: [transactionID]\nCard # [cardNumber] Exp: [cardExpiry]","invoice_discription":"[paymentSplitType] [chargeDescription] ID  : [transactionID]","information_title":"[paymentSplitType] Charged Successfully","information_detail":"[paymentSplitType] transaction Amount [currencySymbol]  [transactionAmount] Charged on [chargedAtDateTime] ,   with [paymentProcessorName]","booking_status":"Unchanged"}',
            'status' => 1
            ]);

        PreferencesForm::create([
            'name' =>'PAYMENT FAILED',
            'form_id'=>config('db_const.user_preferences.preferences.PAYMENT_FAILED'),
            'form_data' => '{"flag_text":"[paymentSplitType] Charge Failed","flag_color":"#E3DD58","notes":"ChargeAutomation.com Msg - [chargedAtDateTime]\n[transactionType] Failed\nReason: [paymentFailedReason]\nCard # [cardNumber] Exp: [cardExpiry]","invoice_discription":"[paymentSplitType] transaction Amount [currencySymbol]  [transactionAmount] Charg failed on [chargedAtDateTime],  Transaction ID  : [transactionID]","information_title":"[paymentSplitType] Charge Fail","information_detail":"[paymentSplitType] transaction Amount [currencySymbol]  [transactionAmount] failed on [chargedAtDateTime],  with [paymentProcessorName]","booking_status":"Unchanged"}',
            'status' => 1
            ]);

        PreferencesForm::create([
            'name' =>'BOOKINGS THAT WILL BE CHARGED IN FUTURE',
            'form_id'=>config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_BE_CHARGED_IN_FUTURE'),
            'form_data' => '{"flag_text":"Future Charge","flag_color":"#36c5c9","notes":null,"invoice_discription":"[paymentSplitType] will be charge on next dates","information_title":"Future Charge Booking","information_detail":"[paymentSplitType] will be charge on next dates","booking_status":"Unchanged"}',
            'status' => 0
        ]);

        PreferencesForm::create([
            'name' =>'UNAVAILABLE CARD DETAILS',
            'form_id'=>config('db_const.user_preferences.preferences.UNAVAILABLE_CARD_DETAILS'),
            'form_data' => '{"flag_text":"Card not Valid or Unavailable","flag_color":"#ba3288","notes":"Card not Valid or Unavailable check - in date : [checkInDate]","invoice_discription":"Card not Valid or Unavailable check - in date : [checkInDate]","information_title":"Card not Valid or Unavailable check - in date : [checkInDate]","information_detail":"Card not Valid or Unavailable check - in date : [checkInDate]","booking_status":"Unchanged"}',
            'status' => 0
        ]);

        PreferencesForm::create([
            'name' =>'BOOKINGS THAT WILL NOT BE CHARGED',
            'form_id'=>config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_NOT_BE_CHARGED'),
            'form_data' => '{"flag_text":"Not Chargeable by Automation","flag_color":"#e32b6b","notes":"Not Chargeable by Automation Booking total amount [bookingTotalAmount] check-in Date [checkInDate]","invoice_discription":"Not Chargeable by Automation Booking total amount [bookingTotalAmount] check-in Date [checkInDate]","information_title":"Not Chargeable by Automation Booking total amount [bookingTotalAmount] check-in Date [checkInDate]","information_detail":"Not Chargeable by Automation Booking total amount [bookingTotalAmount] check-in Date [checkInDate]","booking_status":"Unchanged"}',
            'status' => 0
        ]);

        PreferencesForm::create([
            'name' =>'PAYMENT COLLECTION ON CANCELLATION',
            'form_id'=>config('db_const.user_preferences.preferences.PAYMENT_COLLECTION_ON_CANCELLATION'),
            'form_data' => '{"flag_text":"Cancellation Fee Collected","flag_color":"#85A6B6","notes":"ChargeAutomation.com Msg -[chargedAtDateTime]\nCancellation Fee Collected:Transaction ID: [transactionID]\nCard # [cardNumber] Exp: [cardExpiry]","invoice_discription":"Cancellation Fee Amount [currencySymbol]  [transactionAmount] Charged on [chargedAtDateTime],  Transaction ID  : [transactionID]","information_title":"[paymentSplitType] Charged Successfully","information_detail":"[paymentSplitType] transaction Amount [currencySymbol]  [transactionAmount] Charged on [chargedAtDateTime] ,   with [paymentProcessorName]","booking_status":"Cancelled"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'ADJUSTMENT ENTRIES FOR CANCELED BOOKINGS',
            'form_id'=>config('db_const.user_preferences.preferences.ADJUSTING_ENTRIES_FOR_CANCELED_BOOKINGS'),
            'form_data' => '{"flag_text":"Adjustment entry upon cancellation","flag_color":"#f0b967","notes":"Adjustment amount [currencySymbol][transactionAmount]","invoice_discription":"Adjustment amount [currencySymbol][transactionAmount] will be processed at [chargedAtDateTime] for [paymentSplitType]","information_title":"Adjustment entry upon cancellation","information_detail":"Adjustment amount [currencySymbol][transactionAmount] will be processed at [chargedAtDateTime] for [paymentSplitType]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'SECURITY DEPOSIT AUTH CAPTURE SUCCESS',
            'form_id'=>config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'),
            'form_data' => '{"flag_text":"[authType] Success","flag_color":"#74E207","notes":"ChargeAutomation.com Msg - [authDatetime]\n[authType] [currencySymbol][authAmount] success","invoice_discription":"[authType] [currencySymbol][authAmount] success on [authDatetime]","information_title":"[authType] success","information_detail":"[authType] [currencySymbol][authAmount] success on [authDatetime]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'SECURITY DEPOSIT AUTH CAPTURE FAILED',
            'form_id'=>config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'),
            'form_data' => '{"flag_text":"[authType] Failed","flag_color":"#E3DD58","notes":"ChargeAutomation.com Msg - [authDatetime]\n[authType] [currencySymbol][authAmount] failed","invoice_discription":"[authType] [currencySymbol][authAmount] failed on [authDatetime]","information_title":"[authType] failed","information_detail":"[authType] [currencySymbol][authAmount]  failed on [authDatetime]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'SECURITY DEPOSIT AUTH REFUND SUCCESS',
            'form_id'=>config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_SUCCESS'),
            'form_data' => '{"flag_text":"[authType] Success","flag_color":"#74E207","notes":"ChargeAutomation.com Msg - [authDatetime]\n[authType] [currencySymbol][authAmount] success","invoice_discription":"[authType] [currencySymbol][authAmount] success on [authDatetime]","information_title":"[authType] success","information_detail":"[authType] [currencySymbol][authAmount] success on [authDatetime]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'SECURITY DEPOSIT AUTH REFUND FAILED',
            'form_id'=>config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_FAILED'),
            'form_data' => '{"flag_text":"[authType] Failed","flag_color":"#E3DD58","notes":"ChargeAutomation.com Msg - [authDatetime]\n[authType] [currencySymbol][authAmount] failed","invoice_discription":"[authType] [currencySymbol][authAmount] failed on [authDatetime]","information_title":"[authType] failed","information_detail":"[authType] [currencySymbol][authAmount]  failed on [authDatetime]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'CREDIT CARD VALIDATION AUTH SUCCESS',
            'form_id'=>config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'),
            'form_data' => '{"flag_text":"[authType] Success","flag_color":"#74E207","notes":"ChargeAutomation.com Msg - [authDatetime]\n[authType] [currencySymbol][authAmount] success","invoice_discription":"[authType] [currencySymbol][authAmount] success on [authDatetime]","information_title":"[authType] success","information_detail":"[authType] [currencySymbol][authAmount] success on [authDatetime]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'CREDIT CARD VALIDATION AUTH FAILED',
            'form_id'=>config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'),
            'form_data' => '{"flag_text":"[authType] Failed","flag_color":"#E3DD58","notes":"ChargeAutomation.com Msg - [authDatetime]\n[authType] [currencySymbol][authAmount] failed","invoice_discription":"[authType] [currencySymbol][authAmount] failed on [authDatetime]","information_title":"[authType] failed","information_detail":"[authType] [currencySymbol][authAmount]  failed on [authDatetime]","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'VERIFICATION DOCUMENTATION UPLOADED SUCCESSFULLY',
            'form_id'=>config('db_const.user_preferences.preferences.VERIFICATION_DOCUMENTATION_UPLOADED_SUCCESSFULLY'),
            'form_data' => '{"flag_text":"Verification Documents Uploaded Successfully","flag_color":"#00ff40","notes":"ChargeAutomation.com Msg \nVerification Documents Uploaded Successfully","invoice_discription":null,"information_title":"Verification Documents Uploaded Successfully","information_detail":null,"booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' =>'VERIFICATION DOCUMENTATION UPLOADED FAIL',
            'form_id'=>config('db_const.user_preferences.preferences.VERIFICATION_DOCUMENTATION_UPLOADED_FAIL'),
            'form_data' => '{"flag_text":"Verification Documents Uploaded Fail","flag_color":"#ffff00","notes":"ChargeAutomation.com Msg \nVerification Documents Uploaded Fail","invoice_discription":null,"information_title":"Verification Documents Uploaded Fail","information_detail":null,"booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' => 'PAYMENT MANUALLY VOIDED',
            'form_id' => config('db_const.user_preferences.preferences.PAYMENT_MANUALLY_VOIDED'),
            'form_data' => '{"flag_text":"[paymentSplitType] Manually Voided","flag_color":"#E3DD58","notes":"ChargeAutomation.com Msg - [chargedAtDateTime]\n[transactionType] Manually Voided on CA Interface","invoice_discription":"[paymentSplitType]  Manually Voided","information_title":"[paymentSplitType] Manually Voided","information_detail":"[paymentSplitType] transaction Amount [currencySymbol]  [transactionAmount] Manually Voided on [chargedAtDateTime],","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' => 'PAYMENT MANUALLY MARKED AS PAID',
            'form_id' => config('db_const.user_preferences.preferences.PAYMENT_MANUALLY_MARKED_AS_PAID'),
            'form_data' => '{"flag_text":"[paymentSplitType] Manually Marked as Paid","flag_color":"#1be485","notes":"ChargeAutomation.com Msg - [chargedAtDateTime]\n[transactionType]  Manually Marked as Paid on CA Interface","invoice_discription":"[paymentSplitType] Manually Marked as Paid","information_title":"[paymentSplitType]  Manually Marked as Paid","information_detail":"[paymentSplitType] transaction Amount [currencySymbol]  [transactionAmount]  Manually Marked as Paid on [chargedAtDateTime],","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' => 'ADD-ON SERVICE PURCHASED',
            'form_id' => config('db_const.user_preferences.preferences.UPSELL'),
            'form_data' => '{"flag_text":"Add - on Service Purchased","flag_color":"#1be485","notes":"Add - on Service Purchased","invoice_discription":"Add - on Service Purchased","information_title":"Add - on Service Purchased","information_detail":"Add - on Service Purchased","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' => 'PRE CHECKIN COMPLETE',
            'form_id' => config('db_const.user_preferences.preferences.PRE_CHECKIN_COMPLETE'),
            'form_data' => '{"flag_text":"Pre checkin complete","flag_color":"#1be485","notes":"Pre checkin complete","invoice_discription":"","information_title":"","information_detail":"","booking_status":"Unchanged"}',
            'status' => 0
            ]);

        PreferencesForm::create([
            'name' => 'TERMS AND CONDITIONS COMPLETE',
            'form_id' => config('db_const.user_preferences.preferences.TERMS_AND_CONDITIONS_COMPLETE'),
            'form_data' => '{"flag_text":"Terms and Conditions complete","flag_color":"#1be485","notes":"Terms and Conditions complete","invoice_discription":"","information_title":"","information_detail":"","booking_status":"Unchanged"}',
            'status' => 0
            ]);
    }
}

