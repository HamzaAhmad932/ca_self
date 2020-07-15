<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// http://127.0.0.1:8000/api/booking_automation?
//bookid=9312534&
//status=new&
//channelCode=19&
//propertyId=44880&
//meta=eyJpdiI6IkE2b1JmeXErZjFzYVlWQ3B2ZUh0eXc9PSIsInZhbHVlIjoiYlk3Nk1DOTR1TTJLZld1Y3o4cmozNDRZdFwvb0NZUURJNThLZkJMZkdQc0tGS05XQXo2c0JcL1ZPZlRocFBwRk1ZIiwibWFjIjoiNzBiYTljOTNmMjhlMTNiOGM5OTliOWFhMTFhMWViNWU0ZTA1MmIwNzNjNmFhZmU1YmQwMzViODAzYjI3ODQ3NiJ9

// http://127.0.0.1:8000/api/booking_automation?bookid=9312534&status=new&channelCode=19&propertyId=44880&meta=eyJpdiI6IkE2b1JmeXErZjFzYVlWQ3B2ZUh0eXc9PSIsInZhbHVlIjoiYlk3Nk1DOTR1TTJLZld1Y3o4cmozNDRZdFwvb0NZUURJNThLZkJMZkdQc0tGS05XQXo2c0JcL1ZPZlRocFBwRk1ZIiwibWFjIjoiNzBiYTljOTNmMjhlMTNiOGM5OTliOWFhMTFhMWViNWU0ZTA1MmIwNzNjNmFhZmU1YmQwMzViODAzYjI3ODQ3NiJ9
// Property_info: property key -> 5a5fa9bd0e2b04XMcAh9025100736175

Route::any('/siteminder', 'ApiPmsSiteMinder@receiveReservation');

Route::any('/booking_automation', 'ApiPmsBookingAutomation@receiveBooking');
Route::get('/get-payment-gateways/{status?}', 'ApiPmsBookingAutomation@allSupportedPaymentGateways')->where('status', 'active');

//Route::any('/booking_automation', 'ApiPmsBookingAutomation@receiveBooking');
//Route::post('/booking_automation', 'ApiPmsBookingAutomation@receiveBooking');
//Route::get('/booking_automation', 'ApiPmsBookingAutomation@receiveBooking');
//Route::get('/decrypt', function () {
//    print_r(decrypt('eyJpdiI6IkE2b1JmeXErZjFzYVlWQ3B2ZUh0eXc9PSIsInZhbHVlIjoiYlk3Nk1DOTR1TTJLZld1Y3o4cmozNDRZdFwvb0NZUURJNThLZkJMZkdQc0tGS05XQXo2c0JcL1ZPZlRocFBwRk1ZIiwibWFjIjoiNzBiYTljOTNmMjhlMTNiOGM5OTliOWFhMTFhMWViNWU0ZTA1MmIwNzNjNmFhZmU1YmQwMzViODAzYjI3ODQ3NiJ9'));
//});

Route::any('stripe-hook', 'PaymentConfirmation@stripeWebHook');
//Route::any('spreedly-hook', 'PaymentConfirmation@spreedlyWebHook')->name('spreedly-hook');
Route::any('stripe-commission-hook', 'StripeCommissionBilling\StripeCommissionBillingController@stripeWebHook');
//https://testapptor1a.chargeautomation.com/api/booking_automation?bookid=17138399&channelCode=19&propertyId=96380&status=modify&user_account_id=1090

