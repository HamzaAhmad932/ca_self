<?php

/*
|--------------------------------------------------------------------------
| Web Routes 
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Jobs\RunAllTestSeedersJob;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

Route::get('/logout', function () {
    return redirect('/');
});

Route::get('/', function () {
    //echo $host = request()->getHttpHost();

    if (config('app.env') == "production" && config('app.debug') === false) {
        return redirect('https://app.chargeautomation.com/login');
    } else {
        return view('welcome');
    }
});
//Load Dynamic vars for email form
Route::get('admin/template-vars', 'admin\AdminTeamController@getTemplateVariables')->name('fetchTemplateVars');

/** Not required yet, as ther were for 3DS authentication
 *
 * //payment-confirmation route is used when guest authenticate their transaction via 3D Secure protection
 * Route::get('/payment-confirmation/{userAccountID}/', 'PaymentConfirmation@afterAuthentication')
 * ->name('payment-confirmation')
 * ->where('userAccountID', '[0-9]+');
 *
 * // For Spreedly redirect_url
 * Route::get('/payment-confirmation-ca/{userAccountID}/', 'PaymentConfirmation@afterAuthenticationCA')
 * ->name('payment-confirmation-spreedly')
 * ->where('userAccountID', '[0-9]+');
 *
 */

//returns only Integration instructions views
//Route::view('pmsintegration/instructions', 'client.pms_integration.instructions')->name('pms_integration_instructions');
Route::view('pmsintegration/instructions/booking-automation', 'v2.common-pages.ba_integration_instructions')
    ->name('ba_integration_instructions');

Route::view('pmsintegration/instructions/beds24', 'v2.common-pages.beds24_integration_instructions')
    ->name('beds24_integration_instructions');

// Not sure its being used or not?
//  client/v2/v2user_log this route is working instead of blow route
//Route::get('user_log/{id}', 'v2\client\TeamController@user_log')->where('id', '[0-9]+');

Route::get('verification/{user}/{email}', 'EmailVerification@index')->name('verification')->middleware('signed')->where('user', '[0-9]+');
Route::get('activeuser', 'EmailVerification@activeuser')->name('activeuser');
Route::get('ResendEmail/{id}', 'EmailVerification@ResendEmail')->name('ResendEmail')->where('id', '[0-9]+');

// Not sure its being used or not?
//activate-user is used form same file line #86 instead of blow route
//Route::get('/activate-email/{user}', 'HomeController@verifyEmail')->name('activate-email')->where('user', '[0-9]+');

// Not sure its being used or not?
// Method Not Found -> HomeController::paymentattempt does not exist
//Route::get('/payment-chargestatus/{user}', 'HomeController@paymentattempt')->name('payment-chargestatus')->where('user', '[0-9]+');

Auth::routes(['verify' => true]);


/*----------------------------------------------------------------------
|                       Signed Routes Group
|-----------------------------------------------------------------------
*/

Route::group(['middleware' => 'signed'], function () {

    Route::get('activate-user/{id}', 'EmailVerification@activateUser')
        ->name('activate-user')
        ->where('id', '[0-9]+');


    Route::get('cancel-booking-dot-com-booking', 'v2\client\BookingController@cancelBdcBookingDetailPage')->name('cancelBdcBookingDetailPage');
    Route::get('cancel-bdc-booking', 'v2\client\BookingController@cancelBdcBooking')->name('cancelBdcBooking');

    // For Spreedly 3ds 1 checkout form, from here guest will click on button and will be redirected to off-site automatically.
    Route::get('ca-3ds-proceed/{userAccountId}/{bookingInfoId}/{transactionId}', 'PaymentConfirmation@render3dsVerificationFormForSpreedly')
        ->name('ca-3ds-proceed')
        ->where(['userAccountId' => '[0-9]+', 'bookingInfoId' => '[0-9]+', 'transactionId' => '[0-9]+']);

    Route::get('checkout/{id}/{type}', 'v2\Guest\GuestController@checkout')
        ->name('checkout')
        ->where(['id' => '[0-9]+', 'type' => '[0-9]+']);

    Route::post('checkout/{id}/{type}', 'v2\Guest\GuestController@updateStatusAfterCheckout')
        ->name('checkout-status-update')
        ->where(['id' => '[0-9]+', 'type' => '[0-9]+']);

    Route::get('commission-billing-details/{id}/{name}', 'admin\StripeCommissionBilling\CommissionBillingController@createCustomer')
        ->name('commission-billing-details')
        ->where('id', '[0-9]+');
});
Route::get('client/v2/get-activity-logs/{booking_info_id}', 'v2\client\BookingDetailController@getActivityLog');

/*----------------------------------------------------------------------
|                       End Signed Routes Group
|-----------------------------------------------------------------------
*/

Route::get('/password-reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password_reset');

Route::post('checkout/{id}/{type}/{response_type?}', 'v2\Guest\GuestController@checkout')
    ->name('checkout_json')
    ->where(['id' => '[0-9]+', 'type' => '[0-9]+', 'response_type' => 'json|blade_view']);

Route::post('create-stripe-billing-customer', 'admin\StripeCommissionBilling\CommissionBillingController@createStripeBillingCustomer')->name('createStripeBillingCustomer');
Route::view('guest_booking_details_checkout', 'guest_v2.bookings.checkout')->name('guest_checkout');

Route::post('all-msgs', 'v2\Guest\GuestController@allmsgs')->name('all-msgs');
Route::post('chat', 'v2\Guest\GuestController@communication')->name('chat');
Auth::routes();


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
| Public routes for Guest
*/

Route::group(['middleware' => ['signed', 'guest_experience']], function () {

    Route::get('guest-portal/{id}', 'v2\Guest\GuestController@guestPortal')->name('guest_portal')->where('id', '[0-9]+');
    Route::get('pre-checkin/{id}', 'v2\Guest\GuestController@preCheckin')->name('step_0')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-1/{id}', 'v2\Guest\GuestController@preCheckinStep1')->name('step_1')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-2/{id}', 'v2\Guest\GuestController@preCheckinStep2')->name('step_2')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-3/{id}', 'v2\Guest\GuestController@preCheckinStep3')->name('step_3')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-4/{id}', 'v2\Guest\GuestController@preCheckinAddOnStep')->name('step_4')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-5/{id}', 'v2\Guest\GuestController@preCheckinCreditCardStep')->name('step_5')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-6/{id}', 'v2\Guest\GuestController@preCheckinSelfieStep')->name('step_6')->where('id', '[0-9]+');
    Route::get('pre-checkin-step-7/{id}', 'v2\Guest\GuestController@preCheckinSummaryStep')->name('step_7')->where('id', '[0-9]+');
    Route::get('pre-checkin-complete/{id}', 'v2\Guest\GuestController@preCheckinThankYou')->name('step_8')->where('id', '[0-9]+');
    Route::get('terms-&-conditions/{id}', 'v2\Guest\GuestController@termsConditions')->name('pre_checkin_terms_conditions')->where('id', '[0-9]+');

    Route::get('fetch-guest-cc/{booking_id}', 'v2\client\BookingController@fetchGuestCc')->name('guest-cc-detail-fetch')->where('booking_id', '[0-9]+');
    Route::post('update-card-by-guest', 'v2\client\BookingController@updateCardNow')->name('guest-cc-detail-update');
});


Route::group(['prefix' => 'v2'], function () {


    Route::get('guest-detail/{id}', 'v2\Guest\GuestController@guestDetail')->where('id', '[0-9]+');
    Route::get('guest-data-step-1/{id}', 'v2\Guest\GuestController@guestDataStep1')->where('id', '[0-9]+');
    Route::get('guest-data-step-2/{id}', 'v2\Guest\GuestController@guestDataStep2')->where('id', '[0-9]+');
    Route::get('guest-data-step-3/{id}', 'v2\Guest\GuestController@guestDataStep3')->where('id', '[0-9]+');
    Route::get('guest-data-step-4/{id}', 'v2\Guest\GuestController@guestDataStep4')->where('id', '[0-9]+');
    Route::get('get-add-on-services/{id}', 'v2\Guest\GuestController@getAddOnServices')->where('id', '[0-9]+');
    Route::get('guest-data-step-5/{id}', 'v2\Guest\GuestController@getGuestDataAndGuestImagesByBookingId')->where('id', '[0-9]+');
    Route::post('pre-checkin-complete', 'v2\Guest\GuestController@precheckinComplete');
    Route::post('update-guest-data', 'v2\Guest\GuestController@updateGuestData')->name('update-guest-data');
    Route::post('guest-images', 'v2\Guest\GuestController@guestImages');
    Route::post('update-guest-card', 'v2\Guest\GuestController@updateCardByGuest')->name('update-guest-card');
    Route::get('guest-portal/{id}', 'v2\Guest\GuestController@fetchGuestPortalData')->where('id', '[0-9]+');
    Route::post('fetch-add-card-terminal-data', 'v2\Guest\GuestController@fetchAddCardTerminalData')->name('fetch-add-card-terminal-data');
    Route::post('update-basic-info', 'v2\Guest\GuestController@updateGuestBasicInfo');
    //Route::post('guest-image-delete' , 'v2\Guest\GuestController@guestImageDelete');
    Route::post('guest-image-delete', 'v2\client\BookingController@guestImageDelete');
    Route::post('previous-step-meta', 'v2\Guest\GuestController@getPreviousStepMeta');
    Route::post('next-step-meta', 'v2\Guest\GuestController@getNextPageMeta');
    Route::get('guest-data-step-7/{id}', 'v2\Guest\GuestController@guestDataStep7')->where('id', '[0-9]+');
    Route::post('guest-digital-images', 'v2\Guest\GuestController@guestEncodedImages');
    Route::post('save-addons-cart', 'v2\Guest\GuestController@saveAddonsCart');
    Route::post('purchase-add-on-service', 'v2\Guest\GuestController@purchaseAddOnService');
    Route::post('change-mode', 'v2\Guest\GuestController@changeMode');
    Route::post('pre-checkin-delete-document-image', 'v2\Guest\GuestController@deleteDocumentImage');

});


/*
|-----------------------------------------------------------------------------------------------------------------------
| End Guest Routes
|-----------------------------------------------------------------------------------------------------------------------
| Public routes for Guest
*/


Route::get('create-coupon-on-stripe', 'admin\StripeCommissionBilling\CommissionBillingController@createCoupon');

Route::get('test-auth-job', function () {
    $b = \App\BookingInfo::find(5938);
    dd($b->cc_infos);
    //\App\Jobs\Auth\BA\ReAuthJob::dispatchNow();
    //\App\Jobs\Auth\BA\AuthJob::dispatchNow();
});

Route::fallback(function () {

    $rd = Role::where('guard_name', 'admin')->get();

    if (Auth::check()) {
        if (!Auth::user()->hasAnyRole($rd)) {
            return redirect('/client/v2/dashboard');
        } else {
            return redirect('/admin/user-accounts');
        }
    } else {
        return redirect('/');
    }
});

Route::get('run-all-test-seeders', function () {
    RunAllTestSeedersJob::dispatch();
});
