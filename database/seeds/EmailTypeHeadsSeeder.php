<?php

use Illuminate\Database\Seeder;

class EmailTypeHeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Need To Run this Seeder on Test and Live again after Config Cache as Type names updated.
            $contents = [
               
                /** Emails For Client Only */
                "team_member_added_inform_client" => [
                    "client"=> "{\"subject\":\"Team Member Invited\",\"button_text\":\"Manage Team\",\"message\":\"<p>{Company_Name} has invited {User_Email} to join {Company_Name} team on ChargeAutomation.<\/p><p>You can manage permissions and assign different role to your team member.<\/p>\",\"show_button\":true}",
                ],
        
                "missing_billing_info" => [
                    "client"=>  "{\"subject\":\"Missing Billing Information\",\"button_text\":\"Update Billing Details\",\"message\":\"<pre class=\\\"ql-syntax\\\" spellcheck=\\\"false\\\">We are writing to let you know that your free trial for ChargeAutomation.com has expired. Over the trial period, we hope you enjoyed the peace of mind knowing that all your bookings payments are being processed automatically.\\nIn order to avoid disruption to the services, we kindly ask you to link your card.\\nThere is no subscription fee to our service, you only pay for what you use.\\n<\\/pre><p><br><\\/p>\",\"show_button\":true}",
                ],
                
                "booking_cancelled" => [
                    "client"=> "{\"subject\":\"Booking Cancelled\",\"button_text\":\"View Booking\",\"message\":\"<h3 class=\\\"ql-indent-1\\\"><strong>Booking Details<\\/strong><\\/h3><ul><li><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/li><li><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/li><li><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/li><li><strong>Property Name:<\\/strong> {Property_Name}<\\/li><li><strong>Rental:<\\/strong> {Room_Name}<\\/li><li><strong>Booking Source:<\\/strong> {BS_Name}<\\/li><\\/ul>\",\"show_button\":true}",
                ],
        
                "payment_passed_due_date" => [
                    "client"=> "{\"subject\":\"Payment Overdue\",\"button_text\":\"View Booking\",\"message\":\"<p>Payment for the following booking was not collected within 24-hours of the booking.<\\/p><p>ChargeAutomation has already marked this card as invalid on Booking.com.<\\/p><p>If one of the following is true, the reservation can be cancelled commission free.<\\/p><ol><li>Guest has not updated card details within 24 hours.<\\/li><li>It is 6 PM on the day of arrival (property timezone)<\\/li><\\/ol><p><br><\\/p><p><strong>Payment Details<\\/strong><\\/p><p><br><\\/p><ul><li><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/li><li><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/li><li><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/li><li><strong>Property Name:<\\/strong> {Property_Name}<\\/li><li><strong>Rental:<\\/strong> {Room_Name}<\\/li><li><strong>Booking Source:<\\/strong> {BS_Name}<\\/li><\\/ul>\",\"show_button\":true}",
                ],

                "payment_aborted" => [
                    "client"=>	"{\"subject\":\"\\ud83d\\udd3a Payment Aborted\",\"button_text\":\"View Booking\",\"message\":\"<p>The amount ChargeAutomation attempted to charge is higher than the outstanding amount shown on your PMS\\/Channel Manager.<\\/p><p>Please verify the correct amount that need to be charged &amp; update it in ChargeAutomation. Read more at <a href=\\\"http:\\/\\/help.chargeautomation.com\\/en\\/articles\\/3636140-how-to-fix-payment-aborted\\\" rel=\\\"noopener noreferrer\\\" target=\\\"_blank\\\">How to fix it.<\\/a><\\/p><h4><br><\\/h4><h3><strong>Transaction Details<\\/strong><\\/h3><p><br><\\/p><p><strong>Aborted Amount:<\\/strong>{Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Transaction Type:<\\/strong> {Transaction_Type}<\\/p><p><strong>Booking Amount:<\\/strong> {Property_Currency_Code}{Total_Amount}<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],

                "payment_successful" => [
                    "client"=>  "{\"subject\":\"Payment Successful\",\"button_text\":\"View Booking\",\"message\":\"<h3><strong>Transaction Details<\\/strong><\\/h3><p><br><\\/p><p><strong>Transaction Amount:<\\/strong> {Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Transaction Type:<\\/strong> {Transaction_Type}<\\/p><p><strong>Reference ID:<\\/strong> {Transaction_Reference_Number}<\\/p><p><strong>Booking Amount:<\\/strong> {Property_Currency_Code}{Total_Amount}<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],

                "payment_collected_for_cancelled_booking" => [
                    "client"=> "{\"subject\":\"Cancellation Fee Collected\",\"button_text\":\"View Booking\",\"message\":\"<p>A payment was successfully collected by ChargeAutomation for a cancelled booking based on your cancellation policy. Below are the details.<\\/p><p><br><\\/p><h3><strong>Transaction Details<\\/strong><\\/h3><p><br><\\/p><p><strong>Collected Amount:<\\/strong> {Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Transaction Type:<\\/strong> {Transaction_Type}<\\/p><p><strong>Booking Amount:<\\/strong> {Property_Currency_Code}{Total_Amount}<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p><p><strong>Booking Status:<\\/strong> Cancelled<\\/p>\",\"show_button\":true}",
                ],
        
                "manual_refund_successful" => [
                    "client"=>	"{\"subject\":\"\\ud83d\\udd04Manual Refund Successful\",\"button_text\":\"View Booking\",\"message\":\"<p>You have processed a manual refund. Below are the details.<\\/p><h3><strong>Refund Details<\\/strong><\\/h3><p><strong>Refunded Amount:<\\/strong> {Property_Currency_Code}{Refund_Amount}<\\/p><p><strong>Refund Description:<\\/strong> {Refund_Remarks} <\\/p><p><strong>Refund To:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],
            
                "refund_failed" => [
                    "client"=>	"{\"subject\":\"Refund Failed\",\"button_text\":\"View Booking\",\"message\":\"<p>An auto-refund attempt by ChargeAutomation has failed. Auto-refund refunds cancelled bookings based on the cancellation policy. Below are the details.<\\/p><p><br><\\/p><h3><strong>Refund Details<\\/strong><\\/h3><p><strong>Attempted Refund:<\\/strong> {Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Failed Reason:<\\/strong> {Transaction_Response}<\\/p><p><strong>Refund Policy:<\\/strong> {Transaction_Type}<\\/p><p><strong>Refund To:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],
            
                "unable_to_contact_pms" => [
                    "client"=> "{\"subject\":\"\\ud83d\\udd3a PMS sync failure\",\"button_text\":\"View Account Settings\",\"message\":\"<p>ChargeAutomation is unable to communicate with your Property Management System.<\\/p><p>Your bookings and properties may not sync. Please resolve the issue either by contacting your PMS or ChargeAutomation support.<\\/p><p><strong>Possible Reason<\\/strong><\\/p><ol><li>You have changed your API-Key<\\/li><li>You have changed the username<\\/li><li>You have removed our IP from the whitelist<\\/li><\\/ol>\",\"show_button\":true}",
                ],
            
                "empty_property_key_received" => [
                    "client"=> "{\"subject\":\"\ud83d\udd3a Property(s) Key Missing\",\"button_text\":\"Manage Properties\",\"message\":\"<p>It appears your following property(s) is missing property key. Please check your setting and add property key at your PMS.<\/p><a href='https://help.chargeautomation.com/en/articles/4162925-adding-property-key'>See how you can add/update property key<\/a><p><\/p><h3><strong>Affected Properties<\/strong><\/h3>\",\"show_button\":true}",
                ],
            
                "document_uploaded" => [
                    "client"=>"{\"subject\":\"Documents received \u2013 for {Guest_First_Name} at {Room_Name} {PMS_Booking_ID}\",\"button_text\":\"View Booking\",\"message\":\"<p>New document is uploaded by guest from their portal.<\/p><h3><strong>Booking Details<\/strong><\/h3><p><strong>Stay Dates:<\/strong> {Checkin_Date} - {Checkout_Date}<\/p><p><strong>Name:<\/strong> {Guest_First_Name} {Guest_Last_Name}<\/p><p><strong>Booking ID:<\/strong> {PMS_Booking_ID}<\/p><p><strong>Property Name:<\/strong> {Property_Name}<\/p><p><strong>Rental:<\/strong> {Room_Name}<\/p><p><strong>Booking Source:<\/strong> {BS_Name}<\/p>\",\"show_button\":true}",
                ],
            
                "pre_checkin_completed" => [
                    "client"=> "{\"subject\":\"Pre-checkin Completed By Guest\",\"button_text\":\"View Booking\",\"message\":\"<p>Guest has provided all the required pre-arrival information. You can check all from booking details page.<\\/p><h3><br><\\/h3><h3><strong>Pre-arrival Info<\\/strong><\\/h3><p><br><\\/p><p><strong>Email:<\\/strong> {Guest_Email}<\\/p><p><strong>Phone:<\\/strong> {Guest_Phone}<\\/p><p><strong>Number of Guests:<\\/strong> Adults {Number_Of_Adults} and Children {Number_Of_Children}<\\/p><p><strong>Arriving Time:<\\/strong> {Guest_Arrival_Time}<\\/p><p><strong>Arriving By:<\\/strong> {Guest_Arrival_By}<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],
            
                "credit_card_authorization_failed" => [
                    "client"=> "{\"subject\":\"\\ud83d\\udd3a Credit Card Validation Failed\",\"button_text\":\"View Booking\",\"message\":\"<p>The guest has already been notified with a link to update their payment method. We will notify you once Authorization is successful.<\\/p><h3><strong>Authorization Details<\\/strong><\\/h3><p><strong>Failed Authorization:<\\/strong> {Property_Currency_Code}{Authorization_Price}<\\/p><p><strong>Reason:<\\/strong> {Authorization_Response}<\\/p><p><strong>Payment Type:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],
            
                "credit_card_authorization_successful" => [
                    "client"=> "{\"subject\":\"\\ud83d\\udd12Credit Card Validated\",\"button_text\":\"View Booking\",\"message\":\"<h3><strong>Authorization Details<\\/strong><\\/h3><p><strong>Authorization Amount:<\\/strong> {Property_Currency_Code}{Authorization_Price}<\\/p><p><strong>Payment Type:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                ],
            
                "sd_authorization_successful" => [
                    "client"=> "{\"subject\":\"Security Deposit Authorized\",\"button_text\":\"View Booking\",\"message\":\"<h3><strong>Authorization Details<\/strong><\/h3><p><strong>Authorized Amount:<\/strong> {Property_Currency_Code}{Authorization_Price}<\/p><p><strong>Payment Type:<\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\/p><p><strong>Stay Dates:<\/strong> {Checkin_Date} - {Checkout_Date}<\/p><p><strong>Name:<\/strong> {Guest_First_Name} {Guest_Last_Name}<\/p><p><strong>Booking ID:<\/strong> {PMS_Booking_ID}<\/p><p><strong>Property Name:<\/strong> {Property_Name}<\/p><p><strong>Rental:<\/strong> {Room_Name}<\/p><p><strong>Booking Source:<\/strong> {BS_Name}<\/p>\",\"show_button\":true}",
                ],
        
                /** Emails For Guest Only  */
                "auth_3ds_required" => [
                    "guest"=> "{\"subject\":\"[Action Required] 3D Secure Authentication Required\",\"button_text\":\"Authenticate Now\",\"message\":\"<p><br><\\/p><p>Your provided payment card requires authentication. This must be completed for the reservation to be successful.<\\/p><p>To validate your reservation, a hold is placed on your credit card.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Action Required:<\\/strong> Authenticate Card<\\/p>\",\"show_button\":true}",
                ],
            
                "sd_3ds_required" => [
                    "guest"=> "{\"subject\":\"[Action Required] 3D Secure Authentication Required\",\"button_text\":\"Authenticate Now\",\"message\":\"<p>Your provided payment card requires authentication. This must be completed for the Refundable Security Deposit to be successful.<\\/p><p>We place a hold on your credit card. We will release the hold as soon as we have had the chance to inspect the property after your departure.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Action Required:<\\/strong> Authenticate Card<\\/p>\",\"show_button\":true}",
                ],
            
                "charge_3ds_required" => [
                    "guest"=> "{\"subject\":\"[Action Required] 3D Secure Authentication Required\",\"button_text\":\"Authenticate Now\",\"message\":\"<p>Your provided payment card requires authentication. This must be completed for the reservation to be successful.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Action Required:<\\/strong> Add Valid Card<\\/p>\",\"show_button\":true}",
                ],
            
                "sd_required_for_vc_booking" => [
                    "guest"=> "{\"subject\":\"[Action Required] Refundable Security Deposit Required\",\"button_text\":\"Add Security Deposit Now\",\"message\":\"<p>Thank you for your reservation. To confirm your reservation, we require you to add your credit card for Refundable Security Deposit.<\\/p><p>We place a hold on your credit card. We will release the hold as soon as we have had the chance to inspect the property after your departure.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Action Required:<\\/strong> Refundable Security Deposit Pending<\\/p>\",\"show_button\":true}",
                ],
            
                "auth_failed" => [
                    "guest"=> "{\"subject\":\"[Action Required] Authorization Failed\",\"button_text\":\"Fix Now\",\"message\":\"<p>The attempted Authorization on your credit card ending with **** {Credit_Card_Last_4_Digits} failed. We kindly request to update your payment details on the link below. Reservation maybe cancelled if valid credit card is not received within 12 hours.<\\/p><p>To validate your reservation, a hold is placed on your credit card.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> <span class=\\\"ql-cursor\\\">\\ufeff<\\/span>{Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Action Required:<\\/strong> Add Valid Card<\\/p>\",\"show_button\":true}",
                ],
                    
                /** Emails For Both Client and Guest  */
                "new_booking" => [
                    "client"=>  "{\"subject\":\"New Booking Received\",\"button_text\":\"View Booking\",\"message\":\"<h3><strong>Booking Details<\\/strong><\\/h3><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p><p><br><\\/p>\",\"show_button\":true}",
                    "guest"=> "{\"subject\":\"[Action Required] Complete Your Pre Check-In\",\"button_text\":\"Complete Your Pre-Checkin\",\"message\":\"<p>Thank you for your reservation. We require a few more details from you. Please take a moment to complete your reservation details.<\/p><p>You will then gain access to your personal portal and guidebook for your stay.<\/p><h2>Your Booking Details<\/h2><p><strong>Check-in Date:<\/strong> {Checkin_Date}<\/p><p><strong>Check-out Date:<\/strong> {Checkout_Date}<\/p><p><strong>Booking ID:<\/strong> {PMS_Booking_ID}<\/p><p><strong>Property Name:<\/strong> {Property_Name}<\/p><p><strong>Booking Status:<\/strong> Ready for Pre-Checkin<\/p>\",\"show_button\":true}"
                ],

                "credit_card_missing"=>[
                    "client" => "{\"subject\":\"\\ud83d\\udd3a Credit Card Missing\",\"button_text\":\"View Booking\",\"message\":\"<p>No credit card was received with the following reservation. The guest is already provided with a link to add a credit card. You may also contact the guest to let them know.<\\/p><h3><br><\\/h3><h3><strong>Booking Details<\\/strong><\\/h3><ul><li><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/li><li><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/li><li><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/li><li><strong>Property Name:<\\/strong> {Property_Name}<\\/li><li><strong>Rental:<\\/strong> {Room_Name}<\\/li><li><strong>Booking Source:<\\/strong> {BS_Name}<\\/li><\\/ul>\",\"show_button\":true}",
                    "guest" => "{\"subject\":\"[Action Required] Payment Information Required\",\"button_text\":\"Complete Booking\",\"message\":\"<p>Thank you for your reservation at {Property_Name}.<\\/p><p>To complete your reservation, please review the payment schedule and add your payment method. To guarantee your stay, please complete within 24 hours.<\\/p><h2>Your Booking Details<\\/h2><p>Check-in Date:<strong> {Checkin_Date}<\\/strong><\\/p><p>Check-out Date:<strong> {Checkout_Date}<\\/strong><\\/p><p>Booking Amount: <strong>{Property_Currency_Code}{Total_Amount}<\\/strong><\\/p><p>Booking ID:<strong> {PMS_Booking_ID}<\\/strong><\\/p><p>Property Name:<strong> {Property_Name}<\\/strong><\\/p><p>Booking Status: <span style='color: rgb(0, 138, 0);'> <strong>Ready for Pre-Checkin<\/strong><\/span><\/p>\",\"show_button\":true}",
                ],

                "credit_card_invalid"=>[
                    "client"  => "{\"subject\":\"\\ud83d\\udd3a Reservation credit card declined\",\"button_text\":\"View Booking\",\"message\":\"<p>The credit card received with the following reservation is invalid. The guest is already provided with a link to add a valid credit card. You may also contact the guest to let them know.<\\/p><h3><strong>Booking Details<\\/strong><\\/h3><p><strong>Reason:<\\/strong> {Credit_Card_Response}<\\/p><p><strong>Payment Type:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p><p><br><\\/p>\",\"show_button\":true}",
                    "guest" => "{\"subject\":\"Credit Card Invalid\",\"button_text\":\"Update Credit Card Now\",\"message\":\"<p>The credit card provided ending with **** {Credit_Card_Last_4_Digits} could not be processed. Please provide us a valid credit card to secure your reservation.<\\/p><p>To guarantee your stay, update your card information within 24 hours.<\\/p><p>Your payment information is typically used to validate your booking, collect outstanding balance or security deposit.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong>{Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p>\",\"show_button\":true}",
                ],

                "payment_failed"=>[
                        "client" => "{\"subject\":\"\\ud83d\\udd3a Payment Failed\",\"button_text\":\"View Booking\",\"message\":\"<p>The guest is already notified to update the payment card information. We will notify you once payment is received.<\\/p><p><br><\\/p><h3><strong>Transaction Details<\\/strong><\\/h3><p><br><\\/p><p><strong>Failed Amount: <\\/strong>{Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Reason: <\\/strong>{Transaction_Response}<\\/p><p><strong>Transaction Type:<\\/strong> {Transaction_Type}<\\/p><p><strong>Payment Type:<\\/strong> {Transaction_Type}<\\/p><p><strong>Outstanding Amount:<\\/strong> {Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p><p><strong>Booking Status:<\\/strong> {PMS_Booking_Status}<\\/p>\",\"show_button\":true}",
                        "guest" => "{\"subject\":\"[Action Required] Reservation Payment Failed\",\"button_text\":\"Update Credit Card Now\",\"message\":\"<p>The attempted charge of  {Property_Currency_Code}{Transaction_Price} on your credit card ending with **** {Credit_Card_Last_4_Digits}  failed. Contact your bank or provide us a valid credit card to secure your reservation.<\\/p><p>To guarantee your stay, update your card information within 24 hours.<\\/p><h2>Your Booking Details<\\/h2><p>Check-in Date: <strong>{Checkin_Date}<\\/strong><\\/p><p>Check-out Date:<strong> {Checkout_Date}<\\/strong><\\/p><p>Booking ID: <strong>{PMS_Booking_ID}<\\/strong><\\/p><p>Property Name: <strong>{Property_Name}<\\/strong><\\/p><p>Overdue Amount: <strong>{Property_Currency_Code}{Transaction_Price}<\\/strong><\\/p>\",\"show_button\":true}",
                ],
               
                "refund_successful"=>[
                        "client"  => "{\"subject\":\"\\ud83d\\udd04Auto Refund Issued\",\"button_text\":\"View Booking\",\"message\":\"<h3>A refund has been processed by ChargeAutomation for a cancelled booking based on your cancellation policy. Below are the details.<\\/h3><p><br><\\/p><h3><strong>Refund Details<\\/strong><\\/h3><p><br><\\/p><p><strong>Refund Amount:<\\/strong> {Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Refund Policy:<\\/strong> {Transaction_Type}<\\/p><p><strong>Refund To: <\\/strong> Credit Card (****{Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p><p><br><\\/p><p><br><\\/p>\",\"show_button\":true}",
                        "guest"  => "{\"subject\":\"Refund Issued\",\"button_text\":\"View Booking Details\",\"message\":\"<p>We have successfully issued a refund of  {Property_Currency_Code}{Transaction_Price} to your credit card ending with **** {Credit_Card_Last_4_Digits}. Refund should be reflected on your statement within 2 to 7 business days.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Refunded Amount:<\\/strong> {Property_Currency_Code}{Transaction_Price}<\\/p><p><strong>Refunded To:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p>\",\"show_button\":true}",
                ],
        
                "new_chat_message"=>[
                        "client"=> "{\"subject\":\"\\ud83d\\udce9Message Received\",\"button_text\":\"Reply\",\"message\":\"<p><strong>Message:<\\/strong> {Chat_Message}<\\/p><h3><strong>Booking Details<\\/strong><\\/h3><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                        "guest"=> "{\"subject\":\"New Message Received From  {Property_Name}\",\"button_text\":\"View Message\",\"message\":\"<p>You have received new message from &nbsp;<strong>{Property_Name}<\\/strong><\\/p><p><strong>Customer Service:<\\/strong> {Chat_Message}<\\/p>\",\"show_button\":true}",
                ],
        
                "sd_auth_failed"=>[
                        "client"=>"{\"subject\":\"\\ud83d\\udd3a Security Deposit Failed\",\"button_text\":\"View Booking\",\"message\":\"<p>The guest has already been notified with a link to update their payment method. We will notify you once Security Deposit has been collected.<\\/p><h3><strong>Authorization Details<\\/strong><\\/h3><p><strong>Failed Authorization:<\\/strong> {Property_Currency_Code}{Authorization_Price}<\\/p><p><strong>Reason:<\\/strong> {Authorization_Response}<\\/p><p><strong>Payment Type:<\\/strong> Credit Card (**** {Credit_Card_Last_4_Digits})<\\/p><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Name:<\\/strong> {Guest_First_Name} {Guest_Last_Name}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Rental:<\\/strong> {Room_Name}<\\/p><p><strong>Booking Source:<\\/strong> {BS_Name}<\\/p>\",\"show_button\":true}",
                        "guest"=> "{\"subject\":\"SD Auth Failed\",\"button_text\":\"Fix Now\",\"message\":\"<p>The attempted Security Deposit Authorization on your credit card ending with **** {Credit_Card_Last_4_Digits} failed. We kindly request to update your payment details on the link below. You will not be able to check-in if this is not fixed.<\\/p><p>We place a hold on your credit card. We will release the hold as soon as we have had the chance to inspect the property after your departure.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Check-in Date:<\\/strong> {Checkin_Date}<\\/p><p><strong>Check-out Date:<\\/strong> {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Action Required:<\\/strong> Refundable Security Deposit Pending<\\/p>\",\"show_button\":true}",
                ],
                "upsell_marketing"=>[
                        "guest"=> "{\"subject\":\"Awesome Services available for your stay\",\"button_text\":\"View Details\",\"message\":\"<p>In order to make your stay more enjoyable, below are some of our recommendation.<\/p>\",\"show_button\":true}",
                ],
                "upsell_purchased"=>[
                        "client"=> "{\"subject\":\"New order received\",\"button_text\":\"View Order\",\"message\":\"<p>A guest has purchased following upsell from the Guest Portal.<\/p><h2>Upsell Details<\/h2>\",\"show_button\":true}",
                        "guest"=> "{\"subject\":\"Order Received\",\"button_text\":\"View Order\",\"message\":\"<p>We have received your order! Below is a summary for your record.<\/p><h2>Summary<\/h2>\",\"show_button\":true}",
                ],
                "booking_source_activated"=> [
                        "client"=> '{"subject":"Payment Rules Enabled","button_text":"Manage Payment Rules","message":"<h2><span style=\"color: rgb(0, 138, 0);\">Payment Rules Enabled for {BS_Name}<\/span><\/h2><p>Auto payments processing Enabled for {BS_Name} . This change will only apply to new bookings.<\/p><h2>Current Payment Rules for {BS_Name}<\/h2>","show_button":true}',
                ],
                "booking_source_deactivated"=> [
                    "client"=> '{"subject":"Payment Rules Disabled","button_text":"Manage Payment Rules","message":"<h2><span style=\"color: rgb(230, 0, 0);\">Payment Rules Disabled for {BS_Name}<\/span><\/h2><p>Auto payments processing disabled for {BS_Name} . This change will only apply to new bookings.<\/p><p>All existing bookings with scheduled transaction will continued to be processed.<\/p><h2>Current Payment Rules for {BS_Name}<\/h2>","show_button":true}',
                ],
                "properties_activated"=> [
                    "client"=> '{"subject":"Properties Enabled","button_text":"Manage Properties","message":"<p>The following properties have been enabled. Paused transactions will resume processing.<\/p><h2><span style=\"color: rgb(0, 138, 0);\">Enabled Properties<\/span><\/h2>","show_button":true}',
                ],
                "properties_deactivated"=> [
                    "client"=> '{"subject":"Properties Disabled","button_text":"Manage Properties","message":"<h2><span style=\"color: rgb(230, 0, 0);\">Properties Disabled<\/span><\/h2><p>The following properties have been disabled. All scheduled transactions will be paused. The paused transactions will be resumed if enabled.<\/p><h2>Disabled Properties<\/h2>","show_button":true}',
                ],
                "password_reset"=> [
                    "client"=> '{"subject":"Password Reset","button_text":"Reset Now","message":"<p>You are receiving this email because we received a password reset request for your account.<\/p><p>This password reset link will expire in <strong>60 <\/strong>minutes.<\/p>","show_button":true}',
                ],
                "email_verification_new_user"=> [
                    "client"=> '{"subject":"Welcome To ChargeAutomation","button_text":"Verify Now","message":"<p>You have successfully created an account with ChargeAutomation.<\/p><p>To get started please verify your email by clicking below button.<\/p>","show_button":true}',
                ],
                "booking_fetch_failed"=> [
                    "client"=> '{"subject":"Booking Fetch Failed","button_text":"View Account Settings","message":"<p>ChargeAutomation was unable to fetch booking details from your PMS.<\/p><p>Please resolve the issue either by contacting your PMS or ChargeAutomation support.<\/p><h2>Booking Details<\/h2><p><br><\/p>","show_button":true}',
                ],
                "credit_card_not_added_payment_gateway_error"=> [
                    "client"=> '{"subject":"\ud83d\udd3a Payment Gateway Error","button_text":"View Booking","message":"<p>Your connected payment gateway through error while adding guest credit card.<\/p><h2>Booking Details<\/h2><p><strong>Stay Dates:<\/strong> {Checkin_Date} - {Checkout_Date}<\/p><p><strong>Name:<\/strong> {Guest_First_Name} {Guest_Last_Name}<\/p><p><strong>Booking ID:<\/strong> {PMS_Booking_ID}<\/p><p><strong>Property Name:<\/strong> {Property_Name}<\/p><p><strong>Rental:<\/strong> {Room_Name}<\/p><p><strong>Booking Source:<\/strong> {BS_Name}<\/p><p><br><\/p>","show_button":true}',
                ],
                "guest_email_missing"=> [
                    "client"=> '{"subject":"\ud83d\udd3a Guest Email Address Missing","button_text":"View Booking","message":"<p>No email address was received with the following reservation. You may also contact the guest to let them know.<\/p><h3><strong>Booking Details<\/strong><\/h3><p><strong>Stay Dates:<\/strong> {Checkin_Date} - {Checkout_Date}<\/p><p><strong>Name:<\/strong> {Guest_First_Name} {Guest_Last_Name}<\/p><p><strong>Booking ID:<\/strong> {PMS_Booking_ID}<\/p><p><strong>Property Name:<\/strong> {Property_Name}<\/p><p><strong>Rental:<\/strong> {Room_Name}<\/p><p><strong>Booking Source:<\/strong> {BS_Name}<\/p>","show_button":true}',
                ],

                "guest_document_rejected"=>[
                    "guest" => "{\"subject\":\"[Action Required] Document Rejected\",\"button_text\":\"Submit Document\",\"message\":\"<p>The document you submitted for your reservation was not accepted. Please submit a valid document.<\\/p><h2>Your Booking Details<\\/h2><p><strong>Stay Dates:<\\/strong> {Checkin_Date} - {Checkout_Date}<\\/p><p><strong>Booking ID:<\\/strong> {PMS_Booking_ID}<\\/p><p><strong>Property Name:<\\/strong> {Property_Name}<\\/p><p><strong>Document Type:<\\/strong> {Guest_Document_type}<\\/p><p><strong>Status:<\\/strong> <strong style='color: #FF5630'>Document Rejected {Document_Rejected_Description}<\\/strong><\\/p>\",\"show_button\":true}",
                ],
                "properties_unavailable_on_pms"=> [
                    "client"=> '{"subject":"Properties Disabled","button_text":"Manage Properties","message":"<p>ChargeAutomation has disabled your following properties, Because we could not found it on your PMS.<\/p><p>Any future bookings related to these properties will not be processed.<\/p><h2><span style=\"color: rgb(230, 0, 0);\">Disabled Properties<\/span><\/h2>","show_button":true}',
                ],
                "ca_account_status_changed" => [], // System Default Content.
                "gateway_disabled_auto_to_client"=> [], // System Default Content.
                "team_member_invite"=> [], // System Default Content.

            ];


        \App\EmailTypeHead::truncate();
        \App\EmailDefaultContent::truncate();

        $email_types = config('db_const.emails.heads');
        $now = now()->toDateTimeString();

        foreach ($email_types as $email_type) {

            $email_head = \App\EmailTypeHead::create(
                [
                    'id' => $email_type['form_id'],
                    'title' => $email_type['title'],
                    'type' => $email_type['type'],
                    'to_admin' => array_key_exists('admin', $email_type['send_to']),
                    'to_client' => array_key_exists('client', $email_type['send_to']),
                    'to_guest' => array_key_exists('guest', $email_type['send_to']),
                    'icon' => 'fa fa-envelope',
                    'customizable' => (isset($email_type['customizable']) && $email_type['customizable'] == false) ? false : true,
                    'system_email' => !empty($email_type['system_default']),
                    'status' => true,
                ]
            );

            echo 'Email Type Head ' . $email_type['title'] ." Seed. \n";

            if (empty($email_type['system_default'])) {
                foreach ($email_type['send_to'] as $key => $receiver) {
                    \App\EmailDefaultContent::create([
                        'email_type_head_id' => $email_type['form_id'],
                        'email_receiver_id' => $receiver['id'],
                        'content' => json_decode($contents[$email_type['type']][$key], true), //json_decode($receiver['default_content'], true),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                echo 'Email Type Head ' . $email_type['title'] . " Content Seed \n";
            } else {
                echo 'Email Type Head ' . $email_type['title'] . " System default Email \n";
            }
        }
    }
}
