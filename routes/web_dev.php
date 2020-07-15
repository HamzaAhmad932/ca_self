<?php


use App\ApiRequestDetail;
use App\Audit;
use App\AuthorizationDetails;
use App\BookingInfo;
use App\BookingInfoDetail;
use App\CreditCardAuthorization;
use App\CreditCardInfo;
use App\Events\Emails\EmailEvent;
use App\Events\NotificationsForClientEvent;
use App\Events\SendEmailEvent;
use App\GuestActivityLog;
use App\GuestCommunication;
use App\GuestData;
use App\GuestImage;
use App\Jobs\EmailJobs\EmailJob;
use App\PropertyInfo;
use App\Repositories\EmailComponent\EmailContent;
use App\Repositories\Properties\Properties;
use App\RoomInfo;
use App\Services\Settings\PropertySettings;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\SystemJob;
use App\TransactionDetail;
use App\TransactionInit;
use App\BAModels\ReadyToFirstAttemptTransaction;
use App\Unit;
use App\User;
use App\UserAccount;
use App\UserBookingSource;
use App\UserGeneralPreference;
use App\UserIntegrationTesting;
use App\UserNotificationSetting;
use App\UserPaymentGateway;
use App\UserPaymentSchedule;
use App\UserPms;
use App\UserPreference;
use App\UserPreferencesNotificationSettings;
use App\UserSecurityDeposit;
use App\UserSettingsBridge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\System\FrontEndComponents\Components;

Route::get('delete-bookings/{pmsUserName}/{userAccountId}', function(string $pmsUserName, int $userAccountId) {

    if (config('app.debug') == true) {

        $userPms = UserPms::where('user_account_id', $userAccountId)->where('unique_key', $pmsUserName)->first();

        if($userPms == null)
            dd("Not Found, Wrong data");

        $formData = json_decode($userPms->form_data, true);

        $user = $formData['credentials'][1]['value'];
        $pass = $formData['credentials'][0]['value'];

            try {

                $body = '
<request>
<auth><username>' . $user . '</username><password>' . $pass . '</password></auth>
<datefrom>' . \Carbon\Carbon::now()->subDays(365)->toDateString() . '</datefrom>
</request>';
                $url = 'https://api.beds24.com/xml/getBookings';
                $client = new \GuzzleHttp\Client();
                $options = ['headers' => ['Content-Type' => 'application/xml; charset=UTF8',], 'body' => $body,];
                $response = $client->post($url, $options);
                $content = $response->getBody()->getContents();
                $content = simplexml_load_string($content);
                $bookings = $content->xpath('/bookings/booking');
                $parser = new \App\System\PMS\BookingAutomation\BAParser();
                $bs = $parser->parseXmlResponse('BookingAutomation', \App\System\PMS\Models\Booking::class, $bookings);

                $bids = '';
                foreach ($bs as $b)
                    $bids .= '<booking action="delete" id="' . $b->id . '"></booking>';

                $body = '<request><auth><username>' . $user . '</username><password>' . $pass . '</password></auth><bookings action="delete">' . $bids . '</bookings></request>';
                $url = 'https://api.beds24.com/xml/putBookings';
                $client = new \GuzzleHttp\Client();
                $options = ['headers' => ['Content-Type' => 'application/xml; charset=UTF8',], 'body' => $body,];
                $r = $client->post($url, $options);
                $r = $r->getBody()->getContents();
                print_r($r);



            } catch (\Exception $e) {
                dd($e->getMessage());
            }

    }
    else {
        dd('Sorry, Not available');
    }
});

Route::get('template-test', function(){

    $repo = new \App\Repositories\DynamicVariableInContent();
    $content = $repo->replaceWithActualData(BookingInfo::class, 5412,
        [
            'guest_message' => 'I am {Guest_First_Name}. I booked for {Property_Name}.',
            'another_message' => 'Guest email is: {Guest_Email}.',
            'guest_data' => 'I am arriving AT: {Guest_Arrival_Time}.',
            'credit_card' => 'My name on card is {Full_Name_On_Credit_Card}. My last 4 digits are:- {Credit_Card_Last_4_Digits}',
            'user_account' => 'Company Name is {Company_Name}.'

        ]);
    echo "<pre>";
    print_r($content);
    exit;
});


Route::get('email-test', function() {
    Mail::to(['harbrinder.c@gmail.com', 'thomas@bookingautomation.com'])
        ->send(new \App\Mail\test());
    echo "sent";
});

Route::get('delete-account/{user_account_id}', function($user_account_id){

    if(config('app.url') == 'https://testapptor1a.chargeautomation.com' || config('app.url') == 'https://master.chargeautomation.com' || config('app.url') == 'http://localhost:8000') {

        $user_account = UserAccount::find($user_account_id);

        if($user_account != null) {
            if(!empty($user_account->company_logo) && $user_account->company_logo != config('db_const.logos_directory.company.default_image_name') && file_exists(public_path(config('db_const.logos_directory.company.img_path').$user_account->company_logo))){
                unlink(public_path(config('db_const.logos_directory.company.img_path').$user_account->company_logo));
            }

            foreach (User::where('user_account_id', $user_account_id)->withTrashed()->get() as $u){
                if(!empty($u->user_image) && $u->user_image != config('db_const.logos_directory.user.default_image_name') && file_exists(public_path(config('db_const.logos_directory.user.img_path').$u->user_image))){
                    unlink(public_path(config('db_const.logos_directory.user.img_path').$u->user_image));
                }
                Audit::where('user_id', $u->id)->delete();
            }

            foreach (PropertyInfo::where('user_account_id', $user_account_id)->get() as $pro) {
                if(!empty($pro->logo) && $pro->logo != config('db_const.logos_directory.property.default_image_name') && file_exists(public_path(config('db_const.logos_directory.property.img_path').$pro->logo))){
                    unlink(public_path(config('db_const.logos_directory.property.img_path').$pro->logo));
                }
                RoomInfo::where('property_info_id', $pro->id)->delete();
                $pro->delete();
            }

            $booking_ids = BookingInfo::select('id')->where('user_account_id', $user_account_id)->get()->toArray();
            GuestActivityLog::whereIn('booking_info_id', $booking_ids)->delete();
            GuestData::whereIn('booking_id', $booking_ids)->delete();

            foreach (GuestImage::select('image')->whereIn('booking_id', $booking_ids)->get() as $guest_img) {
                if(!empty($guest_img)) {
                    if(!empty($guest_img->image) && file_exists(public_path('storage/uploads/guestImages/'.$guest_img->image))){
                        unlink(public_path('storage/uploads/guestImages/'.$guest_img->image));
                    }
                }
            }
            GuestImage::select('id')->whereIn('booking_id', $booking_ids)->delete();
            BookingInfo::where('user_account_id', $user_account_id)->delete();
            ApiRequestDetail::where('user_account_id', $user_account_id)->delete();

            foreach(TransactionInit::where('user_account_id', $user_account_id)->get() as $tran) {
                TransactionDetail::where('transaction_init_id', $tran->id)->delete();
            }

            TransactionInit::where('user_account_id', $user_account_id)->delete();
            CreditCardInfo::where('user_account_id', $user_account_id)->delete();
            AuthorizationDetails::where('user_account_id', $user_account_id)->delete();
            CreditCardAuthorization::where('user_account_id', $user_account_id)->delete();

            UserPms::where('user_account_id', $user_account_id)->delete();
            UserGeneralPreference::where('user_account_id', $user_account_id)->delete();
            UserIntegrationTesting::where('user_account_id', $user_account_id)->delete();
            UserNotificationSetting::where('user_account_id', $user_account_id)->delete();
            UserPaymentSchedule::where('user_account_id', $user_account_id)->delete();
            UserPreference::where('user_account_id', $user_account_id)->delete();
            UserPreferencesNotificationSettings::where('user_account_id', $user_account_id)->delete();
            UserSecurityDeposit::where('user_account_id', $user_account_id)->delete();

            foreach (UserSettingsBridge::select('model_name', 'model_id')->where('user_account_id', $user_account_id)->get() as $userbridgedata) {
                $model_id = $userbridgedata->model_id;
                $model_name = $userbridgedata->model_name;
                $model_name::where('id', $model_id)->delete();
            }

            UserSettingsBridge::where('user_account_id', $user_account_id)->delete();
            UserPaymentGateway::where('user_account_id', $user_account_id)->delete();
            UserBookingSource::where('user_account_id', $user_account_id)->delete();

            SystemJob::where('user_account_id', $user_account_id)->delete();
            GuestCommunication::where('user_account_id', $user_account_id)->forceDelete();

            UserAccount::where('id', $user_account_id)->delete();
            User::where('user_account_id', $user_account_id)->forceDelete();
            echo "Done";
        } else {
            echo "Not Allowed";
        }

    } else {
        echo "Sorry";
    }

});




Route::post('/reset-session', function () {
    if (auth()->check())
        auth()->logout();
});
Route::get('/gen-pass', function(){
    $pass = \Illuminate\Support\Facades\Hash::make('123456');
    dd($pass);
});

/*
 Route::get('/BAmodifyBooking', function(){

    \App\jobs\BANewBookingJob::dispatch(App\UserAccount::first(), 10840925, 19, 44880, 'modify')
        ->delay(now()->addSeconds(15))
        ->onQueue('ba_new_bookings');
});

Route::get('/fetch_properties_xml', function() {

    dd(config('app.url_host'));

    $userAccount = App\UserAccount::find(1079);
    $pms = new App\System\PMS\PMS($userAccount);
    $options = new App\System\PMS\Models\PmsOptions();
    $options->requestType = App\System\PMS\Models\PmsOptions::REQUEST_TYPE_XML;
    $responseXML = $pms->fetch_properties($options);
    $active_properties = $userAccount->properties_info->where('status', '1');
    foreach ($active_properties as $prop) {
        foreach ($responseXML as $xmlProperty) {
            if ($xmlProperty->id == 93172) {
                $is_url_not_changed = true;
                foreach(explode("\n", $xmlProperty->caNotifyURL) as $single_url){
                    $parsed_url = parse_url($single_url);
                    if(!empty($parsed_url['host']) && $parsed_url['host'] == "testapptor1a.chargeautomation.com"){
                        dd($single_url);
                        $is_url_not_changed = $prop->notify_url == html_entity_decode($single_url);
                    }
                }
                dd('not found');
                if (empty($xmlProperty->caNotifyURL) || !$is_url_not_changed) {
                    $urlGen = new NotificationUrlBookingAutomation();
                    $urlGen->enableChannelCode(true);
                    $urlGen->enablePropertyId(true);
                    $urlGen->enableMetaUserAccountId(true, $userAccount->id);
                    $urlGen->enableGroupId(true);
                    $notificationUrl = $urlGen->generateURL();
                    $appended = append_notify_url($xmlProperty, $notificationUrl);
                }
            }
        }
    }
    dd($responseXML);
});

Route::get('/fetch_properties', function(){

    $userAccount = App\UserAccount::find(3);
    $pms = new App\System\PMS\PMS($userAccount);
    $options = new App\System\PMS\Models\PmsOptions();
    $options->requestType = App\System\PMS\Models\PmsOptions::REQUEST_TYPE_JSON;
    $response = $pms->fetch_user_account($options);
    //$response = $pms->fetch_properties($options);
    //$response = $pms->fetch_property($options);
    dd($response);
});

Route::get('/BACancelBooking', function(){
    \App\jobs\BACancelBookingJob::dispatch(App\UserAccount::find(2), 11533892 , 19, 44880,'cancel')->onQueue('ba_cancel_bookings');
});

Route::get('/NotifySettings', function(){
    event( new App\Events\ClientBookingPaymentNotifyEvent( App\UserAccount::find(2) , App\PropertyInfo::first() , App\BookingInfo::first() , App\TransactionInit::first(), 'no exception', 'paymentSuccess' ));
    event( new App\Events\ClientBookingPaymentNotifyEvent( App\UserAccount::find(2) , App\PropertyInfo::first() , App\BookingInfo::first() , App\TransactionInit::first(), 'no exception' , 'paymentDeclined' ));
    dd('Sent...........');
    $set = new App\Repositories\Settings\ClientNotifySettings(1);
    dd($set->isActiveOnCancelBookingMail());
    if ($set->isActivePaymentSuccessfulMail()) {
        dd($set->isActivePaymentSuccessfulMail());
    }
    else{
        dd("gtrth");
    }
    dd($set->isActivePaymentSuccessfulSms());
    dd($set->isActivePaymentSuccessfulSms());
});

Route::get('/transactionInit', function(){

  $card = new App\System\PaymentGateway\Models\Card;;
                        $card->cardNumber = 424242324222;
                        $card->expiryYear = 2019;
                        $card->expiryMonth = 12;
                        $card->cvvCode = 123;
                        $card->firstName = 'test';
                        $card->lastName = '';

  $booking =new App\System\PMS\Models\Booking;
  $userAccount=App\UserAccount::first();
  $typeOfPaymentSource='CC';

  event(new App\Events\TransactionInitEvent($card, $booking, $userAccount, $typeOfPaymentSource, 1));

});
*/

Route::get('pms-test', function(){

    $user_account = UserAccount::find(4);
    $pmsOptions = new PmsOptions();
    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
//    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;

    $pms = new PMS($user_account);
    $properties = $pms->fetch_properties($pmsOptions);

    $response2 = $pms->fetch_user_account($pmsOptions);

    $repoProperties = new  Properties($user_account->id);

    $subAccountIds[$response2[0]->id] = $response2[0]->timezone;
    foreach ($response2[0]->subAccounts as $key => $subAccount) {
        $subAccountIds[$subAccount['id']] = $subAccount['timezone'];
    }

    $syncResponse = $repoProperties->fetch_update_BA_user_properties($user_account, $user_account->properties_info, $pmsOptions, $subAccountIds, $properties);

});

Route::get('tdiff', function() {

    /*
    * 24 hours is given for bookings made more than 48 hours in advance
    *
    * For bookings made within 48 hours of check-in, if the card is invalid, the customer will get 12 hours
    * (or until 15:00, whichever is earlier) to update these details (instead of the usual 24 hours).
    *
    * The customer is always given at least two hours to update these details, i.e. if the booking is made after 14:00 on the day of arrival.
    *
    * For last-minute bookings of 10 or more room nights, partners can cancel two hours after marking the credit card as invalid.
    */

    $data = [
//        ['booking_time'=>'2019-08-02 14:00:00', 'check_in_date'=>'2019-08-02 04:00:00', 'card_invalid_report_time'=>'2019-08-02 14:00:00'],
        ['booking_time'=>'2019-08-09 15:05:23', 'check_in_date'=>'2019-08-09 10:30:00', 'card_invalid_report_time'=>'2019-08-09 16:10:26'],
    ];

    $now = Carbon::parse('2019-08-09 18:31:28');

    $hours48 = (48 * 60) * 60; // making seconds
    $hours24 = (24 * 60) * 60; // making seconds
    $hours12 = (12 * 60) * 60; // making seconds
    $hours02 = (2 * 60) * 60; // making seconds

    foreach($data as $row) {

        $bookingTime = Carbon::parse($row['booking_time'], 'GMT');
        $checkInTime = Carbon::parse($row['check_in_date'], 'GMT');
        $reportTime = Carbon::parse($row['card_invalid_report_time'], 'GMT');

        // Checking if check-in is after 48 hours from booking time
        $give24Hours = $checkInTime->diffInSeconds($bookingTime) >= $hours48;

        $hoursFromTimeOfReport = $reportTime->diffInSeconds($now);

        $isCheckInToday = $checkInTime->isToday();
        $isSameDayBooking = $checkInTime->isSameDay($bookingTime);

        if($isSameDayBooking && $isCheckInToday && $bookingTime->hour >= 14)
            if($hoursFromTimeOfReport < $hours02) {
                echo "a<br>".$hoursFromTimeOfReport;
                continue;
            }

        if($give24Hours) {
            if($hoursFromTimeOfReport < $hours24) {
                echo "b<br>";
                continue;
            }

        } else {

            if($isCheckInToday) {
                if($now->hour <= 15 && $hoursFromTimeOfReport < $hours12) {
                    echo "c<br>";
                    continue;
                }

            }
//            elseif($hoursFromTimeOfReport < $hours12) {
//                echo "d<br>";
//                continue;
//            }
        }

        echo "Email Sent";
    }

});




























Route::get('/test-pms-booking', function(){

    $obj = new stdClass();
//    $obj->guestEmail = 'sdfsd';
//    dd(empty((array) $obj));
    $ua = UserAccount::find(3);
    dd($ua == true);
    $prop = PropertyInfo::where('user_account_id', 3)->where('status', 1)->first();
    $b = BookingInfo::find(1);

    $pms = new PMS($ua);
    $pmsOptions = new PmsOptions();
    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
    $pmsOptions->includeInfoItems = true;
    $pmsOptions->includeInvoice = true;
//    $pmsOptions->propertyID = $property->pms_property_id;
    $pmsOptions->propertyKey = $prop->property_key;
//    $last24hours = Carbon::now()->subHour(24)->toDateTimeString();
//    $pmsOptions->modifiedDate = $last24hours;
//    $pmsOptions->dateFrom = $last24hours;
    //$pmsOptions->dateTo = Carbon::now()->toDateTimeString();
    $pmsOptions->includeCard = true;

    $anyException = false;
    $xml_call_success = false;
    $json_call_success = false;

    $pms = new App\System\PMS\PMS($ua);
    $result = $pms->fetch_Booking_Details($pmsOptions);
    dd($result);

    $pmsOptions = new PmsOptions();
    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
    $pmsOptions->propertyID = $prop->pms_property_id;
    $pmsOptions->bookingID = $b->pms_booking_id;
    $pmsOptions->propertyKey = $prop->property_key;

    $booking = new Booking();
    $booking->guestEmail = 'newemailfromprecheckin@sample.com';
    try{
        $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

        $updated = $pms->update_booking($pmsOptions, $booking);
    }catch (Exception $e){
        dd($e);
    }
    dd($updated);
});


Route::get('/add-admin-account', function () {
    if(config('app.url') == 'https://testapptor1a.chargeautomation.com' || config('app.url') == 'http://localhost:8000') {

    $userAccount = UserAccount::create([
        'name' => 'Admin Support',
        'company_logo' => 'no_image.png',
        'account_verified_at' => now()->toDateTimeString(),
        'status' => 1,
        'account_type' => 2
    ]);

    if ($userAccount)
        echo 'created';
    $user = User::create([
        'name' => 'Dev Administrator',
        'email' => 'support@bookingautomation.com',
        'password' => bcrypt('admin'),
        'phone' => '+10000000111',
        'user_account_id' => $userAccount->id,
        'parent_user_id' => 0,
        'email_verified_at' => now()->toDateTimeString(),
        'is_activated' => 1,
        'attempt_tour' => 1
    ]);

    DB::insert("insert into model_has_permissions (permission_id, model_type, model_id) values(:permission_id, :model, :model_id)", [":permission_id"=>31, ":model"=>User::class, ":model_id"=>$user->id]);
    DB::insert("insert into model_has_roles (role_id, model_type, model_id) values(:role_id, :model, :model_id)", [":role_id"=>1, ":model"=>User::class, ":model_id"=>$user->id]);

    $userAccountThomas = UserAccount::create([
        'name' => 'Admin (Thomas)',
        'company_logo' => 'no_image.png',
        'account_verified_at' => now()->toDateTimeString(),
        'status' => 1,
        'account_type' => 2
    ]);

    $user2 = User::create([
        'name' => 'Thomas',
        'email' => 'thomas@bookingautomation.com',
        'password' => bcrypt('admin'),
        'phone' => '+10000000111',
        'user_account_id' => $userAccountThomas->id,
        'parent_user_id' => 0,
        'email_verified_at' => now()->toDateTimeString(),
        'is_activated' => 1,
        'attempt_tour' => 1
    ]);

    DB::insert("insert into model_has_permissions (permission_id, model_type, model_id) values(:permission_id, :model, :model_id)", [":permission_id"=>31, ":model"=>User::class, ":model_id"=>$user2->id]);
    DB::insert("insert into model_has_roles (role_id, model_type, model_id) values(:role_id, :model, :model_id)", [":role_id"=>1, ":model"=>User::class, ":model_id"=>$user2->id]);

}
});

Route::get('sync-bookings/{user_account_id?}', function ($user_account_id = null){

    \App\Jobs\SyncBookingJob::dispatchNow(!empty($user_account_id), $user_account_id);

    dd('dispatched');
});

Route::get('test-code', function (){
    $faker = collect();
    $bk = BookingInfo::create([
        'pms_booking_id' => mt_rand(888888,99999999),
        'master_id' => -1,
        'bs_booking_id' => 'setBooking JSON',
        'user_id' => 39,
        'user_account_id' => 1530,
        'pms_id' => 1,
        'channel_code' => 1530,
        'property_id' => mt_rand(10000,99999),
        'property_info_id' => mt_rand(10000,99999),
        'room_id' => mt_rand(10000,99999),
        'guest_email' => 's@m.com',
        'guest_title' => 'Title',
        'guest_phone' => mt_rand(1000000000,9999999999),
        'guest_name' => '$faker->name',
        'guest_last_name' => '$faker->namw',
        'guest_zip_code' =>  mt_rand(1000,9999),
        'guest_post_code' =>  mt_rand(1000,9999),
        'guest_country' => '$faker->country',
        'num_adults' => rand(1, 4),
        'guest_address' => '$faker->address',
        'guest_currency_code' => 'PKR',
        'booking_time' => now()->subMonth(2),
        'pms_booking_modified_time' => now()->subMonth(2),
        'check_in_date' => now()->subMonth(2),
        'check_out_date' => now()->subMonth(1),
        'pms_booking_status' => rand(0, 1),
        'total_amount' => 303,
        'booking_older_than_24_hours' => rand(0, 1),
        'is_vc' => 'BT',
        'is_manual' => 1,
        'record_source' => rand(1, 2),
        'full_response' => '{"id":"17468888","roomId":"178255","unitId":"1","bookingStatus":1,"firstNight":"2020-02-28","lastNight":"2020-03-02","guestTitle":"Prof.","guestFirstName":"Gilbert","guestLastName":"Harber","guestEmail":"rsb94510@gmail.com","guestPhone":"(757) 285-5243 x8578","guestMobile":"(757) 285-5243 x8578","guestFax":"","guestAddress":"594 Hintz Parks Apt. 604East Jadenside, MS 80454-4032","guestCity":"North Art","guestPostcode":"00342","guestCountry":"Papua New Guinea","notes":null,"flagColor":"ffff00","flagText":"Paid","bookingStatusCode":"0","price":"2941.00","currencyCode":"USD","bookingReferer":"ch:19","refererOriginal":"setBooking JSON","bookingTime":"2020-02-28 04:18:02","bookingModifyTime":"2020-02-29 11:00:10","guestComments":"","guestArrivalTime":null,"numNight":"3","invoice":[{"id":"25866885","description":"Total Price","status":"1","quantity":"1","price":"2941.00","vatRate":"0.00","type":"199","action":""},{"id":"25866926","description":"Payment  ID  : ch_1GH0MXDRRAarpUctImmxm4EG","status":"1","quantity":"-1","price":"2941.00","vatRate":"0.00","type":"200","action":""}],"invoiceNumber":null,"invoiceDate":null,"apiMessage":"","message":null,"masterId":"","numberOfAdults":"2","channelReference":"","groupBookings":[],"propertyId":"77572","bookingIp":"142.93.148.152","channelCode":"19","action":"","balancePrice":2941,"cardType":null,"cardName":null,"cardNumber":null,"cardExpire":null,"cardCvv":null,"hostComments":" Aborting this transaction because its Paid (Partially Paid) on BookingAutomation.\n \nChargeAutomation.com Msg \nVerification Documents Uploaded Fail","infoItems":[{"code":"Payment Charged Succ","text":"Payment transaction Amount USD  2941 Charged on Fri 28 Feb 2020 09:52 ,   with Stripe"}]}',
        'is_process_able' => rand(0, 1),
        'cancellation_settings' => '{"status":true,"afterBooking":0,"afterBookingStatus":false,"beforeCheckIn":0,"beforeCheckInStatus":true,"rules":[{"canFee":0,"is_cancelled":0,"is_cancelled_value":0}],"isNonRefundable":false}',
        'payment_gateway_effected' => 0,
        'property_time_zone' => 'Asia/Karachi',
        'document_status_updated_on_pms' => rand(0, 1),
        'is_pms_reported_for_invalid_card' => rand(0, 1),
        'created_at' => now()->subMonth(2),
        'updated_at' => now()->subMonth(2),
        'pre_checkin_status' => 1,
        'guestMobile' => mt_rand(1000000000,9999999999),
        'guestCity' => '$faker->city',
        'flagColor' => '74E207',
        'flagText' => 'Paid',
        'bookingStatusCode' => 0,
        'price' => 10000,
        'bookingReferer' => 'ch:14',
        'bookingIp' => '142.93.148.152',
        'host_comments' => 'My Comments',
        'unit_id' => 1,
    ]);

    dd($bk);

    /**
 * @var $rf \App\ReadyToRefundTransaction
 *
 **/
//    $rf = \App\ReadyToRefundTransaction::get()->last();
//    //$rf = \App\RefundAbleTransactionInit::get()->last();
//    dd($rf->refund_able_transactions[0]->last_success_trans_obj);

    event(new EmailEvent(
            config('db_const.emails.heads.properties_unavailable_on_pms.type'),
            1408,
            [
                'property_status' => false,
                'properties_info_ids' => [651,652,653,654,658]
            ])
    );


    //dd(\App\Jobs\SyncProperties\BASyncPropertiesJob::userAccounts(1005));
    //$users = UserPms::join('user_accounts', 'user_pms.user_account_id', '=', 'user_accounts.id')->get();
    $users = UserAccount::join('user_pms', 'user_pms.user_account_id', '=', 'user_accounts.id')->where('pms_form_id', 1)->get();
      dd($users);
    //UserAccount::join('pms', '')

//    $products = \App\Product::join('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
//    ->where('manufacturers.name', 'like', $needle)
//    ->orWhere('products.name', 'like', $needle);
//

    $products = Product::with("manufacturer")
        ->whereHas('manufacturer',function($query) use ($needle){
            $query->where("name","like","%{$needle}%");
        })
        ->orWhere("name","like","%{$needle}%")->get();


    dd(\App\ReadyToReAttemptTransaction::with('property_info', 'booking_info', 'transaction_init', 'user_account', 'cc_info', 'user_payment_gateway')->get());
    dd(ReadyToFirstAttemptTransaction::with('property_info', 'booking_info', 'transaction_init', 'user_account', 'cc_info', 'user_payment_gateway')->get());
   // \App\Jobs\Charge\FirstAttemptTransactionChargeJob::dispatchNow();
    //dd();
    try {


        //\App\Jobs\Charge\FirstAttemptTransactionChargeJob::dispatchNow();
        dd(ReadyToFirstAttemptTransaction::with('property_info', 'booking_info', 'transaction_init', 'user_account', 'cc_info', 'user_payment_gateway')->get()->first());

        $tr = \App\ReadyToFirstAttemptTransaction::all();
        dd($tr);


        $de = BookingInfoDetail::whereIn('booking_info_id',
            BookingInfo::select('id')->where('check_out_date', '<' ,now()->subMonths(8)->toDateTimeString())->pluck('id')
        )->delete();

        dd();
        //dd(GuestData::get()->last());
        //dd(BookingInfo::find(6899)->guest_data);
        dd(EmailJob::dispatchNow('pre_checkin_completed', 'client', 790));



        $content = \App\Services\Emails\EmailContentService::content('pre_checkin_completed');
        dd($content->clientContent(6899,1094));

        event(new EmailEvent('pre_checkin_completed', 6899));




       // event(new EmailEvent('new_chat_message', GuestCommunication::first()->id));
//        event(new EmailEvent('new_booking', 6899));


        dd('sent');

//        $de = BookingInfoDetail::whereIn('booking_info_id',
//            BookingInfo::select('id')->where('check_out_date', '<' ,now()->subMonths(8)->toDateTimeString())->pluck('id')
//        )->delete();

        //dd($de);
        //        dd(TransactionDetail::find(4453)->booking_info);
    //
//    dd(Carbon::parse('2020-08-12 19:24:44')->format( 'M d Y h:i:s a'));
    $content = \App\Services\Emails\EmailContentService::content('payment_passed_due_date');
    dd($content->clientContent(3424,1079));



    $content = \App\Services\Emails\EmailContentService::content('payment_successful');
    //dump($content->adminContent(699, 3));
   dump($content->clientContent(3148,1079));
   // dump($content->clientContent(4453,1079));

    //2020-04-27 16:00:00
 /*   $booking = BookingInfo::find(3424);

    //dd($booking->property_time_zone);
    $options = new PaymentSettingsOptions();
    $options->property_info_id = $booking->property_info_id;
    $options->user_account_id = $booking->user_account_id;
    $options->booking_id = $booking->id;
    $options->booking_source_id = 1; //$this->getBsIdFromChannelCode($this->event->booking->channelCode);
    $options->totalAmount = $booking->total_amount;
    $options->timeZone = 'Asia/Karachi'; //$booking->property_time_zone;

    //Local Hotel DateTime
    $options->bookingTime = '2020-04-27 01:00:00'; //Carbon::parse($booking->booking_time, 'GMT')->setTimezone($booking->property_time_zone)->toDateTimeString(); //Local BookingTime
    $options->checkInDate = '2020-04-27 04:00:00'; //Carbon::parse($booking->check_in_date, 'GMT')->setTimezone($booking->property_time_zone)->toDateTimeString(); // Local Check-in date
    $options->checkOutDate = '2020-04-29 16:00:00'; //Carbon::parse($booking->check_out_date, 'GMT')->setTimezone($booking->property_time_zone)->toDateTimeString(); // Local Check-out date

    $paymentSetting = new PaymentSettings($options);
    $settings = $paymentSetting->transactionDetails(new Booking());

    dd($settings);*/




    //dd($content->guestContent(3148,1079));
    } catch (Exception $exception) {
        //return;

        dd($exception->getMessage(), $exception->getTraceAsString());
    } finally {
        dd('finaly');
    }

//
//
//    dd(BookingInfo::first()->toArray());

//    $content =array( 'msg' => ['orange', '{blue}'], 'green', 'red', 'pink', 'brown', 'black');
////    $vars = preg_grep('~' . preg_quote('-', '~') . '~', array_flatten($content));
////    dd($vars);
////    $a =array( 'msg' => ['orange', '{blue}'], 'green', 'red', 'pink', 'brown', 'black');
////    $input = preg_quote('{', '~'); // don't forget to quote input string!
////    $data = array_flatten($a);
////    $result = preg_grep('~' . $input . '~', $data);
////    dump($a);
////    dd($result);
////
////    $a = ['msg' => ['my', 'hello'], 'subject' => 'world'];
////    //dump(array_search('he', array_flatten($a)));
////    dd($a);

    //SendEmailsJob::dispatchNow('team_member_invite', 'guest', 1134);
    //EmailJob::dispatchNow('guest_email_missing', 'client', 4033);
    //SendEmailsJob::dispatchNow('auth_3ds_required', 'guest', 1134);
//dd(\App\EmailTypeHead::with('defaultContents')->where('to_guest', 1)->get()->toArray());
    //return (\App\EmailDefaultContent::where('email_receiver_id', 2)->pluck('content', 'email_type_head_id')->toArray());
//->where('content','like', "%Dummy Subject%")

//       SendEmailsJob::dispatchNow('payment_failed', 'client', 1142);
       //SendEmailsJob::dispatchNow('payment_failed', 'guest', 2749);

    //  dd();
//    dd(TransactionInit::first()->cc_infos);
//    dd(TransactionInit::first()->cc_info_latest);
//    SendEmailsJob::dispatschNow('credit_card_authorization_failed', 'client', 2100);
  //dd(TransactionInit::find(3152)->refund_detail->last());

       //SendEmailsJob::dispatchNow('credit_card_not_added_payment_gateway_error', 'client',  4034, ['exceptionMsg' => 'weqwe','errorCode' => 'tecdd', 'pms_booking_id' => 23223]);
       //SendEmailsJob::dispatchNow('booking_fetch_failed', 'client', 1094, ['exceptionMsg' => 'weqwe','errorCode' => 'tecdd', 'pms_booking_id' => 23223]);
       //SendEmailsJob::dispatchNow('team_member_invite', 'client', User::find(1229)->id);
       //SendEmailsJob::dispatchNow('email_verification_new_user', 'client', User::find(1229)->id);
       //SendEmailsJob::dispatchNow('password_reset', 'client', User::find(1229)->id, ['token' => 'w1r1']);
       //SendEmailsJob::dispatchNow('gateway_disabled_auto_to_client', 'client', 67);
//       SendEmailsJob::dispatchNow('properties_activated', 'client', 27,['properties_info_ids' => [20,21,22,23]]);
//       SendEmailsJob::dispatchNow('properties_deactivated', 'client', 27,['properties_info_ids' => [20,21,22,23]]);
 //      SendEmailsJob::dispatchNow('booking_source_activated', 'client', 385);
       //SendEmailsJob::dispatchNow('booking_source_deactivated', 'client', 385);
//       SendEmailsJob::dispatchNow('ca_account_status_changed', 'client', 1094);
//       SendEmailsJob::dispatchNow('ca_account_status_changed', 'client', 1094);
dd();
      EmailJob::dispatchNow('upsell_marketing', 'guest', 3424, ['upsell_ids' => [1,2,3,4,5,6,7,8,9]]);
      EmailJob::dispatchNow('upsell_purchased', 'client', 1);

      EmailJob::dispatchNow('upsell_purchased', 'guest', 1);
    //SendEmailsJob::dispatchNow('payment_failed', 'guest', 1e142);s
dd();
//    SendEmailsJob::dispatchNow('payment_failed', 'client', 1142);
//    SendEmailsJob::dispatchNow('payment_failed', 'guest', 1142);
//    dd();
    $content = \App\Services\Emails\EmailContentService::content('email_verification_new_user');
    dd($content->adminContent(699, 3));
    dd($content->clientContent(699,1001));
    dd($content->guestContent(699,1001));


dd('fe');
    $parser = new EmailContent('{"subject":"afaaw","button_text":"sdfs","content":"sgdgdsdg","show_button":false}');
   // dd($parser);
    dd($parser->toJSON(
    ['subject' => "afaaw",
    'button_text'=> "sdfs",
    'content'=> "sgdgdsdg",
    'show_button'  => false,]));

//
//    $input_array = array('name1', 'name2', 'name3', 'name4', 'name5');
//    $colors = PropertyInfo::all()->pluck('id')->toArray(); ///collect(['orange', 'blue', 'green', 'red', 'yellow', 'purple']);
//    dump(array_chunk($colors,3));
//        dd();
//
//    $colors = PropertyInfo::all()->pluck('id')->toArray(); ///collect(['orange', 'blue', 'green', 'red', 'yellow', 'purple']);
//    $chunks = $colors->chunk(3);
//
//    dump($chunks);
//dd();
//    $user_account_id = 1079; // TODO REMOVE
//    $this->property_info_ids= []; // TODO REMOVE
//    $booking_constraint = array();
//    //$user_account_id = $this->user_account_id;
//    $user_account_constraints = array(['status', config('db_const.user.status.active.value')]);
//    $last_sync = now()->subHours(config('db_const.sync_offsets.booking-sync'))->toDateTimeString(); //use-able fo default dispatch
//
//    if (!empty($user_account_id)) {
//        array_push($user_account_constraints, ['id', $user_account_id]);
//    } else {
//        array_push($user_account_constraints, ['integration_completed_on', '<',
//            now()->subHours(config('db_const.sync_offsets.booking-sync-after-integration'))->toDateTimeString()]);
//        array_push($booking_constraint, ['booking_time', '>', now()->subDays(2)->toDateTimeString()]);
//    }
//
//
//    dd(UserAccount::where($user_account_constraints)->whereNotNull('integration_completed_on')->whereIn('id', UserPms::where('name', 'Booking Automation')->get()->pluck('user_account_id')->toArray())
//        ->where(function ($query) use ($last_sync, $user_account_id)
//        {$query->where('last_booking_sync', ($user_account_id ? '!=' :  '<') , ($user_account_id ? null :  $last_sync))->orWhere('last_booking_sync', '=', null);})
//        ->with(['bookings_info' => function ($query) use ($booking_constraint) {$query->where($booking_constraint)->select('pms_booking_status', 'user_account_id', 'property_id', 'property_info_id');}])
//        ->with(['properties_info' => function ($query) {$query->where('status', 1);}])
//        ->with('pms')->orderBy('last_booking_sync', 'asc')->take(10)->get()->toArray());
//

    \App\Jobs\UpsellMarketingJob::dispatchNow();
    $account = resolve(App\UserAccount::class)
        ->with(['properties_info' => function ($query) {$query->whereIn('status', [0,1]);}])->where('id', 1079)->get();
    //dd($account->toArray());
    foreach ($account as $acc){
        //dd($acc->properties_info);
        //dd($acc->properties_info->where('status', 0));
        dd($acc->properties_info->where('status', 1));
    }



//    \App\GuideBookPropertiesBridge::truncate();
//    $create =  \App\GuideBookPropertiesBridge::create(['guide_book_listing_id' => 1, 'property_info_id' => 0, 'user_account_id' => 0, 'user_id' => 0, 'room_info_ids' =>[]]);
//    $create =  \App\GuideBookPropertiesBridge::create(['guide_book_listing_id' => 1, 'property_info_id' => 0, 'user_account_id' => 0, 'user_id' => 0, 'room_info_ids' => null]);
//    $create =  \App\GuideBookPropertiesBridge::create(['guide_book_listing_id' => 1, 'property_info_id' => 0, 'user_account_id' => 0, 'user_id' => 0, 'room_info_ids' =>[12,34,54,5]]);
//    $create =  \App\GuideBookPropertiesBridge::create(['guide_book_listing_id' => 1, 'property_info_id' => 0, 'user_account_id' => 0, 'user_id' => 0, 'room_info_ids' =>['12',34,"54",'5']]);
//    $create =  \App\GuideBookPropertiesBridge::create(['guide_book_listing_id' => 1, 'property_info_id' => 0, 'user_account_id' => 0, 'user_id' => 0, 'room_info_ids' =>['i'=> '12', 'ewr' => 34, 'wer' => "54", 'er' => '5',]]);
//    dd(\App\GuideBookPropertiesBridge::select('room_info_ids')->get()->toArray());


    dd('sent');

    //5002
    //dd('sent');

    $constraints = [];
    $filter_date = '2019-07-15 02:00:00';
    $bookings = resolve(BookingInfo::class)->where('user_account_id', 1000)->select('check_in_date', 'id', 'property_id');


    $properties = \App\PropertyInfo::whereIn('pms_property_id', $bookings->pluck('property_id')->toArray())
        ->where('user_account_id', 1000)->select('pms_property_id', 'time_zone')->get()->toArray();

    //dd($bookings->where($constraints)->get()->toArray());
    //dd($properties);

    foreach ($properties as $property) {
        $date = Carbon::parse($filter_date, 'GMT')->setTimezone($property['time_zone']);
        $constraints = [
            ['property_id', $property['pms_property_id']],
            ['check_in_date', '>=',  $date->startOfDay()->toDateTimeString()],
            ['check_in_date', '<=',  $date->endOfDay()->toDateTimeString()]
        ];
        dd($constraints);
    }

    //dd($bookings->where($constraints)->toSql());;
    //dd($bookings->where($constraints)->get()->toArray());
    dd($bookings->where($constraints)->paginate());


});

Route::get('test-api', function () {
//    dd( \Carbon\Carbon::createFromTimestamp(1572848739)->toDateTimeString());
    //dd(resolve(StripeCommissionBilling::class)->createBillingCustomerWithNoCardAndAddDefaultBillingPlan(UserAccount::find(3)));
    //dd(now()->addHours(4)->timestamp);

    //\Stripe\Stripe::setApiKey(config('db_const.stripe_commission_billing.secret_key'));
   // dd(json_encode(Customer::retrieve('cus_GSF4BrPP4EgKT2')->subscriptions->data));

    //dd(json_encode(Subscription::all(['limit' => 'all', 'plan' => 'Tier_granual_percent_of_each_cent_amount_transaction_volume'])));

});

Route::get('test-codes', function () {
    event(new EmailEvent('new_booking',6028));
//    $templateVars = getEmailTypeTempVars('guest_email_missing');
//    dump($templateVars);
//    event(new EmailEvent('refund_successful',406));
//    event(new EmailEvent('manual_refund_successful',406));
//    EmailJob::dispatchNow('pre_checkin_completed', 'client', 728);
//$extras=[
//    'error_msgss'=>'Bla Bla Bla...',
//    'reason'=>'What Ever Reason',
//];
//    $data_from_extras=[
//        '{Transaction_Response}'=>'reason',
//        '{Transaction_Price}'=>'amount_to_refund',
//        '{Authorization_Response}'=>'error_msg',
//    ];

//    $tid = TransactionDetail::find(6829);
//    dump($tid->transaction_init);
//    dump($tid->transaction_init->booking_info);
//    dd($tid->booking_info);
//    $content =  EmailContentService::content('payment_aborted',true)
//        ->clientContent(5246,1112);
//    dump($content);
//    $content = EmailContentService::content('sd_auth_failed',true)
//        ->clientContent(3490,1112);
//    dump($content);
//    foreach ($content as $key => $data){
//        foreach ($data_from_extras as $var=>$extra_data_key){
//            $extra_data = (!empty($extras[$extra_data_key])?$extras[$extra_data_key]:"N/A");
//            $content[$key] = str_replace($var,$extra_data,$data);
//        }
//    }
//    dd(array_flatten($content));
//    $content = EmailContentService::content('new_booking',false)
//        ->clientContent(6022,1112);
//    dump($content);
//
//    event(new EmailEvent('sd_auth_failed','3490',$extras));
//    event(new EmailEvent('refund_failed','5246',$extras));
//EmailJob::dispatchNow('sd_auth_failed', 'client', 3490,$extras);
//EmailJob::dispatchNow('refund_failed','client',5246,$extras);
//EmailJob::dispatchNow('new_booking','client',5317);

//    try {
//
//
//        $emails = config('db_const.emails.heads');
//        foreach ($emails as $key => $type) {
//            dump($type);
//            $model = $type["model"];
//            if ($model == UserAccount::class) {
//                $model_data = $model::find(1112);
//            } else if ($model == GuestData::class) {
//                $bi = BookingInfo::where('user_account_id', '1112')->latest()->first();
//                dump($bi);
//                $model_data = $bi->guest_data;
//            } else {
//                $model_data = $model::where('user_account_id', '1112')->latest()->first();
//            }
//            if (!empty($model_data)) {
//                dump($model_data->id);
//                event(new \App\Events\Emails\EmailEvent($key, $model_data->id));
//            } else {
//                dump("empty data");
//            }
//        }
//    } catch (\Exception $e) {
//        dd($e->getMessage(), ['Trace' => $e->getTraceAsString()]);
//    }
});


Route::get('3ds', function() {
    try {

        /**
         * @var $card Card
         */
        $card = new Card();
        $card->firstName = 'Ammar';
        $card->lastName = 'Muhammad';
        $card->expiryYear = 2022;
        $card->expiryMonth = 12;
        $card->cvvCode = 123;
        $card->eMail = 'mr.dummydevil@gmail.com';
//        $card->cardNumber = '4000002500003155'; // Required on setup or first transaction
//        $card->cardNumber = '4000000000000077'; // Charge succeeds and funds will be added directly to your available balance (bypassing your pending balance).
//        $card->cardNumber = '4000000000003220'; // Your card was declined. This transaction requires authentication.
//         $card->cardNumber = '4242424242424242';
//        $card->cardNumber = '4000000000000010'; // The address_line1_check and address_zip_check verifications fail. If your account is blocking payments that fail ZIP code validation, the charge is declined.
//        $card->cardNumber = '4000000000000002'; //Charge is declined with a card_declined code.
//        $card->cardNumber = '4000000000009995'; //Charge is declined with a card_declined code. The decline_code attribute is insufficient_funds.
        $card->cardNumber = '4000000000009987'; //Charge is declined with a card_declined code. The decline_code attribute is lost_card.
        $card->general_description = 'Its just general description';
        $card->amount = 205;
        $card->currency = 'EUR';
        $card->order_id = (int) round(microtime(true) * 1000);
//        $card->phone = '+923002438120';
//        $card->postalCode = 38000;
//        $card->city = 'Faisalabad';
//        $card->country = 'PK';
//        $card->state = 'Punjab';
//        $card->address1 = 'House# p-222, St# 10, Jack Block';
//        $card->address1 = 'Samundari Road';

        $userAccount = UserAccount::find(1017);
        $userPaymentGateway = $userAccount->user_payment_gateways->first();
        $paymentGateway = new PaymentGateway();

        $o = $paymentGateway->chargeWithCard($card, $userPaymentGateway);

//        $o = $paymentGateway->addAsCustomer($card, $userPaymentGateway);
//        $o = $paymentGateway->chargeWithCustomer($o, $card, $userPaymentGateway);

//        $o->isPartial = true;
//        $o->amount = 500;
//        $o = $paymentGateway->refund($o, $userPaymentGateway);
//        $o = $paymentGateway->authorizeWithCard($card, $userPaymentGateway);
//        $o = $paymentGateway->authorizeWithCustomer($o, $card, $userPaymentGateway);
//        $o = new \App\System\PaymentGateway\Models\Transaction();
//        $o->paymentIntentId = 'pi_1F1q7OCPTRIPJ8TAgEF1s2Oy';
//        $o->isPartial = true;
//        $o->amount = 500;
//        $o = $paymentGateway->capture($o, $userPaymentGateway);
//        $o = $paymentGateway->cancelAuthorization($o, $userPaymentGateway);

//        $o = $paymentGateway->getTerminal($userPaymentGateway);

        if($o->status == false && $o->state == \App\System\PaymentGateway\Models\Transaction::STATE_REQUIRE_ACTION && $o->authenticationUrl != null)
            echo '<a href="'.$o->authenticationUrl.'">Authenticate Here</a>';

        dd($o);

    } catch (\App\System\PaymentGateway\Exceptions\GatewayException $e) {
        die("Exception: " . $e->getMessage() .  '<br><br>Code: ' . $e->getCode() . '<br><br>Decline Code: ' . $e->getDeclineCode() . '<br><br>Description: ' . $e->getDescription() . '<br><br>NextStep: ' . $e->getNextStep() . '<br><br>Stripe General code: ' . $e->getGeneralCode() . '<br><br>Http Status Code: ' . $e->getHttpStatus());
    }

});

Route::get('test-card-fetch', function () {
    try {
        $pms = new PMS(UserAccount::find(1070));
        $pmsOptions = new PmsOptions();
        $pmsOptions->includeCard = true;
        $pmsOptions->includeInvoice = true;
        $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
        $pmsOptions->bookingToken = '7097a46e';
        $pmsOptions->bookingID = '15201425';
        $pmsOptions->propertyKey = 'abcabcammar00abcabcammar00abcabcammar00';
        $pmsOptions->propertyID = '93040';
        $getCard = $pms->fetch_card_for_booking($pmsOptions);
        dd($getCard);
    } catch (Exception $e) {
        dd($e->getMessage());
    }

});


Route::get('create-signed-route', function(){

//    dd(URL::signedRoute('guest_booking_details', ['id'=> 3556]));

    $db_units = Unit::where('property_info_id', 436)
        ->where('pms_room_id', 220045)->get();
    $s = $db_units->pluck('unit_name')->toArray();
    dd($s);
});

Route::get('query-test-case', function (){

    $query = \App\RoomInfo::where('property_info_id', 721)->get()->keyBy('pms_room_id');
    dd($query);
});

Route::get('sync-property-test-case', function (){

    $ua = UserAccount::find(1090);

    $pms = new PMS($ua);
    $pmsOptions = new PmsOptions();
    $repoProperties = new  Properties($ua->id);
    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;


    //user account all properties
    $active_properties = $ua->properties_info->where('status', '1');

    foreach ($active_properties as $prop) {
        try {

            $response2 = $pms->fetch_user_account($pmsOptions);

            $ua->user_account_id_at_pms = $response2[0]->id;
            $ua->save();
            $subAccountIds[$response2[0]->id] = $response2[0]->timezone;
            foreach ($response2[0]->subAccounts as $key => $subAccount) {
                $subAccountIds[$subAccount['id']] = $subAccount['timezone'];
            }

            /*
             * Sync New Properties From PMS with  REQUEST_TYPE_JSON to
             * Update previous or  add new  in DB
             */

            $responseJSON = $pms->fetch_properties($pmsOptions);
            $syncResponse = $repoProperties->fetch_update_BA_user_properties($ua, $ua->properties_info, $pmsOptions, $subAccountIds, $responseJSON);

        }catch (Exception $e){
            dd($e);
        }
    }
});

//Route::get('email', function() {
//
//    $userAccount = UserAccount::find(1096);
//    $bookingInfo = BookingInfo::find(4297); //4297
//    $propertyInfo = $bookingInfo->property_info;
//    $tran = $bookingInfo->transaction_init->first();
//
//    $msg = "Dummy dummy";
//    event(new ClientBookingPaymentNotifyEvent($userAccount, $propertyInfo, $bookingInfo, $tran, $msg, 'paymentDeclined'));
//});


Route::get('pad', function() {
    return view('dummy.dummy-signature-pad');
});

Route::get('photo-booth', function() {
    return view('dummy.dummy-photo-booth');
});


Route::get('audit-query', function (){

    $starttime = microtime(true);
    $audit = Audit::select('old_values', 'new_values')->get();
    $endtime = microtime(true);
    $duration = $endtime - $starttime;

    $starttime = microtime(true);
    $audit = Audit::select('user_type', 'user_id', 'user_type',
'user_id',
'event',
'auditable_id',
'url',
'ip_address',
'user_agent',
'tags')->get();
    $endtime = microtime(true);
    $duration2 = $endtime - $starttime;
    dd([$duration, $duration2]);
});
Route::get('/search-test', function (){
    $data=resolve(\App\Repositories\TermsAndConditions\TermsAndConditionsRepository::class)->getATermAndCondition(1);
    dd($data);
//    $room = '"316":316';
//    $record = \App\TermsAndConditionJson::where('attached_rentals','Like', '%'.$room.'%')->get();
//
//    dd($record);
});


Route::get('add-upsell', 'v2\client\UpsellController@addUpsell')->name('addUpsell');

Route::get('pre-checkin-step-4_1', 'v2\Guest\GuestController@preCheckinStep4_1')->name('step_4_1');

Route::get('sa', function() {
    $jobs = new App\Jobs\BASyncPropertyJob();

    $ua = UserAccount::find(1121);
    $pro = App\PropertyInfo::find(545);

    $jobs->checkGatewayConnectionIntegrity($ua, $pro);
});

Route::get('sa1', function() {

    // $gateway = new App\System\PaymentGateway\Models\GateWay(App\UserPaymentGateway::find(95)->gateway);
    //             dd($gateway);

    event(new EmailEvent(config('db_const.emails.heads.gateway_disabled_auto_to_client.type'), 101));
});

Route::get('test-upsell-listing', function(){

    $booking = BookingInfo::where('id', 4919)->with('room_info')->first();

    dd($booking);
});

Route::get('master-json', function() {

    $jsonData = file_get_contents('../responses/bookings/booking-com-json-master.json');
    $baParser = new \App\System\PMS\BookingAutomation\BAParser();
    $booking = $baParser->parseJsonResponse('BookingAutomation', App\System\PMS\Models\Booking::class, json_decode($jsonData, true));
    dd($booking);
});

Route::get('master-xml', function() {

    $xmlData = file_get_contents('../responses/bookings/booking-com-xml-master.xml');
    $xml = simplexml_load_string($xmlData);
    $bookings = $xml->xpath('/bookings/booking');
    $baParser = new \App\System\PMS\BookingAutomation\BAParser();
    $booking = $baParser->parseXmlResponse('BookingAutomation', App\System\PMS\Models\Booking::class, $bookings);
    dd($booking);
});


Route::get('card-info/{bid}/{token}', function(Request $request){

    try {


        $token = $request->token;
        $bookingInfo = BookingInfo::where('pms_booking_id',$request->bid)->first();
        $property = $bookingInfo->property_info;
        $userAccount = $bookingInfo->user_account;

        $pms = new PMS($userAccount);

        $pmsOption = new PmsOptions();

        $pmsOption->bookingID = $bookingInfo->pms_booking_id;
        $pmsOption->propertyID = $property->pms_property_id;
        $pmsOption->propertyKey = $property->property_key;
        $pmsOption->bookingToken = $token;
        $pmsOption->cardCvv = '';
        $pmsOption->requestType = $pmsOption::REQUEST_TYPE_JSON;

        $card = $pms->fetch_card_for_booking($pmsOption)[0];
        dd($card);


    } catch (Exception $e) {

        dd( [$e],[ $e->getMessage()] );

    }

});


Route::get('/config-test', function (){
    dd(config('db_const.sent_email.all_emails.upsell_purchased_guest.type'));
});

Route::get('fix-booking-full-response', function() {

    $bookings = BookingInfo::where('user_account_id', 2)->where('full_response', 'NOT LIKE', '%"firstNight":"%')->get();
    foreach ($bookings as $booking) {
        $booking_response = json_decode($booking->full_response, true);

        if (is_array($booking_response['firstNight']))
            $booking_response['firstNight'] = Carbon::parse($booking_response['firstNight']['date'])->toDateTimeString();

        if (is_array($booking_response['lastNight']))
            $booking_response['lastNight'] = Carbon::parse($booking_response['lastNight']['date'])->toDateTimeString();

        $booking->full_response = json_encode($booking_response);
        $booking->save();
        echo  'Booking Id '. $booking->id .' Updated <br>';
    }

});

Route::get('invoice', function() {

    $userAccount = UserAccount::find(1149);

    $bookingInfo = $userAccount->bookings_info->where('id', 5561)->first();
    $propertyInfo = $userAccount->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();

    $pms = new PMS($userAccount);

    $pmsOptions = new PmsOptions();
    $pmsOptions->propertyID = $propertyInfo->pms_property_id;
    $pmsOptions->propertyKey = $propertyInfo->property_key;
    $pmsOptions->bookingID = $bookingInfo->pms_booking_id;
    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

    $b = new \App\System\PMS\Models\Booking();

    $i = new \App\System\PMS\Models\InvoiceItem();
    $i->description = "Has to Pay";
    $i->type = "199";
    $i->price = "700";
    $i->status = "1";
    $i->quantity = "1";

    $i1 = new \App\System\PMS\Models\InvoiceItem();
    $i1->description = 'Now Paid';
    $i1->type = 200;
    $i1->price = 700;
    $i1->status = 1;
    $i1->quantity = -1;

//    $b->invoice = ['description' => 'dd ss', 'price' => "311", "quantity" => "1", 'type' => "199", 'status' => '1'];
    $b->invoice = [$i, $i1];

    $o = $pms->update_booking($pmsOptions, $b);

    var_dump($o);

});
//Route::get('/test-email-job', function (){
//
//    \App\Jobs\EmailJobs\Guest\BookingConfirmationGuestEmailJob::dispatchNow(config('db_const.sent_email.all_emails.guest_reservation.type'), 14758);
//});

Route::get('time-test', function() {

    $bookingTime = '2020-03-03 04:45:01';
    $integrationTimeStr = '2019-05-22 09:53:21';
    $integrationTime = Illuminate\Support\Carbon::parse($integrationTimeStr);
    //integration_completed_on

    echo "Integration ::::: " . $integrationTimeStr;
    echo "<br>";
    echo "BookingTime:: " . $bookingTime;
    echo "<br>";
    echo "Now Time:::::: " . now('GMT')->toDateTimeString();
    echo "<br>";
    echo "Now TIme-24: " . now("GMT")->subHours(24)->toDateTimeString();
    echo "<br>";

    $bookingTime = \Illuminate\Support\Carbon::parse($bookingTime);

    if($bookingTime->isAfter($integrationTime)) {
        echo 'Yes After Integration<br>';
        if($bookingTime->isBefore(now("GMT")->subHours(24)))
                    echo 'old';
                else
                    echo 'new';
    } else {
        echo 'Before Integration';
    }

});


//Route::get('/v2/login', function(){
//    return view('v2.auth.login');
//})->name('v2-login');
//
//Route::get('/v2/register', function(){
//    return view('v2.auth.register');
//})->name('v2-register');
//
//Route::get('/v2/password_reset', function(){
//    return view('v2.auth.password_reset');
//})->name('v2-password_reset');

Route::get('l', function() {

    $u = UserAccount::where('id', 1149)->first();
    $p = PropertyInfo::where('id', 612)->first();

//    $gae = new \App\Events\GatewayAddEvent($u, $p);

//    $galcb = new \App\Listeners\GatewayAddListenerCheckBooking();
//    $galcb->handle($gae);

    event(new \App\Events\GatewayAddEvent($u, $p));

});

Route::get('has_role', function() {
    //$user = auth()->user();
    //$r = !$user->hasAnyRole([User::ROLE_ADMINISTRATOR, User::ROLE_MANAGER]);
    //dd($r);

    //$r = Auth::user()->hasRole(User::ROLE_ADMINISTRATOR);
    //dd($r);

    //$r = Auth::user()->getRoleNames();
    //dd($r->count());

});
Route::get('/cancel-booking-url/{ua_id}/{b_id}', function ($ua_id, $b_id){
    $url = URL::signedRoute('cancelBdcBookingDetailPage',
        ['user_account_id' => $ua_id, 'booking_info_id' => $b_id]);

    dd($url);
});

//Route::get('cancel-booking-dot-com-booking', 'v2\client\BookingController@cancelBdcBookingDetailPage')->name('cancelBdcBookingDetailPage');
//Route::get('cancel-bdc-booking', 'v2\client\BookingController@cancelBdcBooking')->name('cancelBdcBooking');


Route::get('bdc/{bookingPmsId}', function($bookingPmsId) {

    try {

    $b = BookingInfo::where('pms_booking_id', $bookingPmsId)->first();

    if($b == null)
        dd("record Not Found");

    $bdc = new \App\Jobs\SearchFailed_BDC_CC_bookingsJob();

    echo "<table><tr><th>BookingTime</th><th>CheckInTime</th><th>BT - CT</th><th>ReporteTime</th><th>CurrentTime</th><th>Result</th></tr>";

    $tz = 'GMT';
    $propertyTimezone = $b->property_time_zone;

    $length = 1;
    $isMailSent = false;
    for($i = 0; $i < $length; $i += 5) {

        $bookingTime = Carbon::parse($b->booking_time, $tz)->setTimezone($propertyTimezone);
        $checkInTime = Carbon::parse($b->check_in_date, $tz)->setTimezone($propertyTimezone);
        $bt_ct = $bookingTime->diffInHours($checkInTime) . 'h';
        $reportTime = Carbon::parse($b->card_invalid_report_time, $tz)->setTimezone($propertyTimezone);
        $now = Carbon::parse($reportTime->toDateTimeString(), $propertyTimezone)->addMinutes($i);


        if($length == 1) {
            $length = $checkInTime->diffInHours($reportTime);
            $length += 320;
        }


        $result = $bdc->shouldSendMailByCheckingTimeLogicTest($bookingTime, $checkInTime, $reportTime, $now);

        if($result === true) {
            $result = '<span style="color: #0b4c8c; font-family: sans-serif;">Send Mail</span>';
            $isMailSent = true;
        } else {
            $result = '<span style="color: #cd2553; font-family: sans-serif;">No (' . $result . ')</span>';
        }


        if($i > 0) {
            $bt_ct = $bookingTime = $checkInTime = $reportTime = '';
        }

        echo "<tr>"
                . "<td> $bookingTime &nbsp;&nbsp;&nbsp;</td>"
                . "<td> $checkInTime &nbsp;&nbsp;&nbsp;</td>"
                . "<td> &nbsp;&nbsp;&nbsp;&nbsp;$bt_ct&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>"
                . "<td> $reportTime &nbsp;&nbsp;&nbsp;</td>"
                . "<td> $now &nbsp;&nbsp;&nbsp;</td>"
                . "<td> $result </td></tr>";

        if($isMailSent)
            break;

    }

    echo "</table>";
    } catch(\Exception $e) {
        dd($e->getMessage());
    }

});

Route::get('bdc-result', function() {

    $bdc = new \App\Jobs\SearchFailed_BDC_CC_bookingsJob();

    echo "<h3>SQL</h3>";
    $rows = $bdc->getBookingDataDB();
    foreach($rows as $r) {
        echo $r->pms_booking_id . ", ";
    }

    echo "<br><br>";

    echo "<h3>Filtered</h3>";
    $bookings = $bdc->filterBookingsForEmail();
    foreach($bookings as $key => $value) {
        echo $value->pms_booking_id . ", ";
    }

});

Route::get('siteminder/{id}', function($id) {

    try {

        $rec = DB::table('siteminders')->where('id', '=', $id)->select()->first();
//        $r = new App\System\PMS\SiteMinder\SMX_Parser();
//        $bookings = $r->handelReservation($rec->request_data);
//        dd($bookings);
//        echo $r->getNotificationTime();

        $user = UserAccount::find(1180);
        $pms = new PMS($user);
        $options = new PmsOptions();

        $options->dump = $rec->request_data;
//        $bookings = $pms->fetch_Booking_Details($options);

        $bookings = $pms->fetch_property($options);
        dd($bookings);

//            $r->successResponseForReservationNotification($rec->request_data, $bookings);

    } catch (Exception $e) {

        dd("Exception catch: " . $e->getMessage());
    }


});

Route::get('sitemider-pms', function(){

    try{
    $smx = app()->make('lh_pms_form');
    $op = $smx->fetch_Publisher_list();
    dd($op);
    } catch (\Exception $e) {
        dd($e->getMessage());
    }

});

Route::get('sitemider-bookinginfo-test/{id}', function ($id) {

    /**
     * Note: Changed data-types
     * PropertyInfo: pms_property_id
     * BookingInfo: pms_booking_id, property_id
     * db triggers for audit tables as well
     */

    try {

        $rec = DB::table('siteminders')->where('id', '=', $id)->select()->first();

        if(empty($rec))
            dd("Record not found");

        $apiPmsSiteMiderController = new \App\Http\Controllers\ApiPmsSiteMinder();
        $parser = new App\System\PMS\SiteMinder\SMX_Parser();
        $bookings = $parser->parseReservation($rec->request_data);

        $output = $apiPmsSiteMiderController->handelReservation($parser, $rec->request_data, $bookings);

        dd($output->getStatusCode());

    } catch (Exception $e) {
        dd('Exception:::: ' . $e->getMessage(), $e->getTrace());
    }


});

Route::get('jobs', function() {

    $b = BookingInfo::where('pms_booking_id', 'LH2005215269889')->first();
    if($b != null) {
        TransactionInit::where('booking_info_id', $b->id)->delete();
        CreditCardInfo::where('booking_info_id', $b->id)->delete();
        CreditCardAuthorization::where('booking_info_id', $b->id)->delete();
        $b->delete();
    }
    DB::select('TRUNCATE jobs');
    DB::select('TRUNCATE failed_jobs');
    DB::select('TRUNCATE system_jobs');
});

Route::get('/test-missing-prop-key-email', function (){
    $user_account = auth()->user()->user_account;
    $property_info_ids = $user_account->activeProperties()->take(3)->pluck('id')->toArray();
    event(
        new EmailEvent(
            config('db_const.emails.heads.empty_property_key_received.type'),
            $user_account->id,
            ['properties_info_ids' => $property_info_ids]
        )
    );
});
Route::get('page', function() {
    $p = new Components();
    $userPms = UserPms::where('user_account_id', 1180)->first();
    $pageComponent = $p->getPageComponentClientSide(SIDE_CLIENT,Client_Dashboard, $userPms);
    dd($pageComponent);
});
Route::get('test-email', function (){
    $data = URL::signedRoute('checkout',
        [
            'id' => 4210,
            'type' => 2
        ]
    );

    dd($data);
});