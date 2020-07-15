<?php

Route::get('profile/{id?}', 'HomeController@profile')->name('profile')->where('id', '[0-9]+');
Route::post('profile/{id?}', 'HomeController@update')->name('update')->where('id', '[0-9]+');


/** Email Setting Routes */
Route::get('emails', 'admin\AdminTeamController@emails')->name('emails');
//Route::get('template-vars','admin\AdminTeamController@getTemplateVariables')->name('fetchTemplateVars');
Route::get('get-default-emails', "admin\EmailsController@getDefaultEmails")->name("fetchDefaultEmails");
Route::post('update-default-email', "admin\EmailsController@updateDefaultEmail")->name("updateDefaultEmails");
/** End Email Settings  */

//Admin Listing Routes Start
Route::get('admin-listing', 'admin\AdminController@index')->name('adminListing');
Route::post('get-admins', 'admin\AdminController@getAdmins')->name('adminGetAdmins');
Route::get('get-admin-roles', 'admin\AdminController@getAdminRoles')->name('adminGetAdminRoles');
Route::post('save-admin', 'admin\AdminController@store')->name('adminSave');
Route::post('change-admin-status', 'admin\AdminController@changeAdminStatus')->name('adminChangeAdminStatus');
Route::get('delete-admin/{user_id}', 'admin\AdminController@destroy')->name('adminDeleteAdmin')->where('user_id', '[0-9]+');
Route::get('get-admin/{user_id}', 'admin\AdminController@show')->name('adminGetAdmin')->where('user_id', '[0-9]+');
//Admin Listing Routes End

//User Accounts Routes Start
Route::get('user-accounts', 'admin\UserAccountController@index')->name('adminUserAccounts');
Route::post('get-user-accounts', 'admin\UserAccountController@getUserAccounts')->name('adminGetUserAccounts');//Paginated
Route::post('verify-api-key', 'admin\UserAccountController@verifyAPIKey')->name('verifyAPIKey');
Route::get('users/{user_account_id}', 'admin\UserAccountController@users')->name('adminUsers')->where('user_account_id', '[0-9]+');
Route::post('get-users', 'admin\UserAccountController@getUsers')->name('adminGetUsers');
Route::get('admin-view-client-dashboard/{id}', 'admin\UserAccountController@adminViewClientDashboard')->name('adminViewClientDashboard')->where('id', '[0-9]+');
//User Accounts Routes End

//Properties Routes Start
Route::get('properties/{user_account_id?}', 'admin\PropertyController@index')->name('admin.properties')->where('user_account_id', '[0-9]+');
Route::post('get-properties', 'admin\PropertyController@getProperties')->name('adminGetProperties');//Paginated
Route::post('get-all-properties-cities', 'admin\PropertyController@getAllPropertiesCities')->name('adminGetAllPropertiesCities');
Route::get('property/{property_info_id?}', 'admin\PropertyController@showProperty')->name('adminProperty')->where('property_info_id', '[0-9]+');
Route::get('/property_detail/{property_info_id}', 'admin\PropertyController@getPropertyDetail')->name('adminPropertyDetail')->where('property_info_id', '[0-9]+');
Route::post('/verification-detail', 'admin\PropertyController@verificationDetail')->name('verificationDetail');
//Properties Routes End


//Bookings Listings Routes Starts
Route::get('bookings/{user_account_id?}', 'admin\BookingController@index')->name('clientsbookings')->where('user_account_id', '[0-9]+');
Route::post('bookings_data', 'admin\BookingController@getBookings')->name('clients_bookings_data');
Route::get('booking-details/{booking_info_id}', 'admin\BookingController@bookingDetail')->name('admin.booking_details')->where('booking_info_id', '[0-9]+');
Route::get('/get-booking-details/{booking_info_id}', 'admin\BookingController@getBookingDetails')->name('getBookingDetails')->where('booking_info_id', '[0-9]+');
//Bookings Listings Routes End

//Term And Condition Routes Starts
Route::get('terms-and-conditions', 'admin\TermsAndConditionsController@index')->name('adminTermAndConditionList');
Route::post('get-terms-and-conditions', 'admin\TermsAndConditionsController@getTermsAndConditions')->name('adminGetTermsAndConditions');
Route::post('get-properties-names', 'admin\TermsAndConditionsController@getPropertiesNames')->name('adminGetPropertiesNames');
Route::post('get-properties-room-infos', 'admin\TermsAndConditionsController@getRoomInfo')->name('adminGetPropertyRoomInfo');
//Term And Condition Routes End

//Guide Books Routes Start
Route::get('guide-books', 'admin\GuideBookController@index')->name('adminGuideBooks');
Route::post('/get-all-guide-books-types', 'admin\GuideBookController@getAllGuideBookTypes')->name("adminGetAllGuideBookTypes");
Route::post('/get-all-guide-books', 'admin\GuideBookController@getAllGuideBooks')->name("adminGetAllGuideBooks");
//Guide Books Routes End

// Guide Books Types Routes Start
Route::get('guide-books-types', 'admin\GuideBookController@guideBooksType')->name('adminGuideBookTypes');
Route::post('/get-guide-book-types-list', 'admin\GuideBookController@getGuideBookTypesList')->name("adminGetGuideBookTypesList");
// Guide Books Types Routes End

//Upsells Routes Start
Route::get('upsells', 'admin\UpsellController@index')->name('adminUpsells');
Route::get('/get-all-upsell-types/{forFilters?}/{serve_id?}/{user_account_id?}', 'admin\UpsellController@getUpsellTypes')->name("adminGetAllUpsellTypes");
Route::post('/get-all-upsell-list', 'admin\UpsellController@getUpsellList')->name("adminGetAllUpsellList");
//Upsells Routes End

//UP-Sell Type Routes Start
Route::get('upsell-type', 'admin\UpsellController@upsellType')->name('adminUpsellTypes');
Route::post('/upsell-type-list', 'admin\UpsellController@getUpsellTypeList')->name("adminGetUpsellTypesList");
//UP-Sell Type Routes End

//UP-SELL Orders Routes Start
Route::get('/upsell-orders', 'admin\UpsellController@upsellOrders')->name("adminUpsellOrders");
Route::post('/get-upsell-order-list', 'admin\UpsellController@getUpsellOrderList')->name("adminGetUpsellOrderList");
//UP-SELL Orders Routes End

// Audit Routes Start
Route::get('admin-audits', 'admin\AuditController@index')->name('adminAudits');
Route::get('get-models-name', 'admin\AuditController@getModelsName')->name('adminGetModelsName');
Route::post('get-columns-name', 'admin\AuditController@getColumnsName')->name('adminGetColumnsName');
Route::post('get-audit-data', 'admin\AuditController@getAuditData')->name('adminGetAuditData');
// Audit Routes End

//Route::get('adminteam', 'admin\AdminTeamController@index')->name('adminteam');
//Route::get('manageadmin', 'admin\AdminRegistrationController@index')->name('manageadmin');
//
//Route::post('adminmember', 'admin\AdminTeamController@store')->name('adminmember');
//Route::post('adminstore', 'admin\AdminRegistrationController@store')->name('adminstore');
//
//Route::get('memberprofile/{id?}', 'admin\AdminTeamController@show');
//
//Route::post('memberupdate/{id}', 'admin\AdminTeamController@update')->name('adminmemberupdate');


Route::get('paymentgateways', 'admin\AdminPaymentGatewaySettings@paymentgateways')->name('paymentgateways');
Route::get('pgforms', 'admin\AdminPaymentGatewaySettings@pgforms')->name('pgforms');
Route::get('getParentCredentials', 'admin\AdminPaymentGatewaySettings@getParentCredentials')->name('getParentCredentials');
Route::post('setParentCredentials', 'admin\AdminPaymentGatewaySettings@setParentCredentials')->name('setParentCredentials');
Route::get('addGatewaysFromParent', 'admin\AdminPaymentGatewaySettings@addGatewaysFromParent')->name('addGatewaysFromParent');
Route::post('addUpdateGatewayFromParent', 'admin\AdminPaymentGatewaySettings@addUpdateGatewayFromParent')->name('addUpdateGatewayFromParent');
Route::post('newpgform', 'admin\AdminPaymentGatewaySettings@newpgform')->name('newpgform');
Route::post('update_pg_status', 'admin\AdminPaymentGatewaySettings@update_pg_status')->name('update_pg_status');
Route::get('add_ca_gateways', 'admin\AdminPaymentGatewaySettings@addAllGateways');

//Plan settings route are start here
Route::get('commissionplans', 'admin\AdminPlanSettingsController@commissionplans')->name('commissionplans');
Route::post('createnewplan', 'admin\AdminPlanSettingsController@createnewplan')->name('createnewplan');
Route::post('updateplan/{id}', 'admin\AdminPlanSettingsController@updateplan')->name('updateplan')->where('id', '[0-9]+');
Route::post('planstatus/{id}/{status}', 'admin\AdminPlanSettingsController@planstatus')->name('planstatus')->where(['id' => '[0-9]+', 'status' => '[0-9]+']);
//Plan settings route are end here


//Route::delete('admindelete/{id}', 'admin\AdminTeamController@destroy')->name('admindelete');
/*Route::delete('admindelete/{id}', 'admin\AdminTeamController@destroy')->name('admindelete')->where('id', '[0-9]+');
Route::get('viewCompany/{id}', 'admin\CompanyController@viewCompany')->name('viewCompany')->where('id', '[0-9]+');
Route::get('company_audit_logs/{id}', 'admin\CompanyController@company_audit_logs')->name('company_audit_logs')->where('id', '[0-9]+');
Route::get('company_profile/{id}', 'admin\CompanyController@company_profile')->name('company_profile')->where('id', '[0-9]+');
Route::post('clientprofileupdate/{id}', 'admin\CompanyController@clientprofileupdate')->name('admin.clientprofileupdate')->where('id', '[0-9]+');
Route::post('companylogo/{id}', 'admin\CompanyController@companylogo')->name('admin.companylogo')->where('id', '[0-9]+');*/


/* Routes responsible for transactions init and transaction details */
Route::get('bookings/{booking_info_id}/transactions', 'admin\TransactionController@transactionInits')
    ->name('booking_transactions')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/transactions-listing/{booking_info_id}', 'admin\TransactionController@transactionInitsListing')
    ->name('booking_transactions_listing')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/booking-property-listing/{booking_info_id}', 'admin\ClientsDataController@bookingPropertyDetail')
    ->name('bookingPropertyDetail')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/booking-payment-gateway-detail/{booking_info_id}', 'admin\ClientsDataController@bookingPaymentGatewayDetail')
    ->name('bookingPaymentGatewayDetail')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/booking-ccinfo-detail/{booking_info_id}', 'admin\ClientsDataController@bookingCCInfoDetail')
    ->name('bookingCCInfoDetail')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/transactions/audit_logs/{transaction_init_id}', 'admin\TransactionController@transaction_audit_logs')
    ->name('transaction_init_audit_logs')
    ->where('transaction_init_id', '[0-9]+');

Route::get('bookings/transactions/{booking_info_id}/transactions-details/{transaction_init_id}', 'admin\TransactionController@transactionDetails')
    ->name('transaction_details')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/transaction-details-listing/{booking_info_id}/{transaction_init_id}', 'admin\TransactionController@transactionDetailsListing')
    ->name('transaction_details_listing')
    ->where('booking_info_id', '[0-9]+');

Route::get('bookings/transactions/transaction-details/audit_logs/{transaction_detail_id}', 'admin\TransactionController@transaction_detail_audit_logs')
    ->name('transaction_detail_audit_logs')
    ->where('transaction_detail_id', '[0-9]+');

Route::get('account_users/{id}', 'admin\ClientsDataController@account_users')
    ->name('account_users')
    ->where('id', '[0-9]+');

Route::get('account_users_data/{id}', 'admin\ClientsDataController@account_users_data')
    ->name('account_users_data')
    ->where('id', '[0-9]+');

Route::get('account_user_profile/{user_id}/{u_account_id}', 'admin\ClientsDataController@account_user_profile')
    ->name('account_user_profile')
    ->where(['user_id' => '[0-9]+', 'u_account_id' => '[0-9]+']);

Route::get('team_member_audit_logs/{id}', 'admin\ClientsDataController@team_member_audit_logs')
    ->name('team_member_audit_logs')
    ->where('id', '[0-9]+');

Route::get('clientproperty/{id}', 'admin\PropertyController@showclientproperty')
    ->name('clientproperty')
    ->where('id', '[0-9]+');

//Route::get('paagy', 'admin\CompanyController@pagy')->name('paagy');
//Route::get('/ajax-pagination', array('as'=>'pagination','uses'=>'admin\CompanyController@ajaxPagination'));
//Route::delete('companydel/{id}', 'admin\CompanyController@destroy')->name('companydel')->where('id', '[0-9]+');
//Route::post('addcompany', 'admin\CompanyController@store')->name('addcompany');
//Route::get('showcompanyinfo/{id?}', 'admin\CompanyController@show')->name('showcompanyinfo')->where('id', '[0-9]+');
//Route::post('companyupdate/{id}', 'admin\CompanyController@update')->name('companyupdate')->where('id', '[0-9]+');
//Route::post('admincompanylogo/{id}', 'HomeController@admincompanylogo')->name('admincompanylogo')->where('id', '[0-9]+');
//Route::post('adminstatus/{id}/{st}', 'admin\AdminTeamController@adminstatus')->name('adminstatus')->where(['id' => '[0-9]+', 'st' => '[0-9]+']);
//Route::post('adminupdatepass/{id}', 'admin\AdminTeamController@changepassword')->name('adminupdatepass')->where('id', '[0-9]+');
//Route::post('adminupdate/{id}', 'admin\AdminTeamController@adminprofileupdate')->name('adminupdate')->where('id', '[0-9]+');
//Route::get('/notify', 'admin\AdminTeamController@notify')->name('notify');
//Route::get('notifications', 'admin\AdminTeamController@allnotifications')->name('notifications');

Route::get('create-test-bookings', 'admin\CreateTestBooking@index')->name('create-test-bookings');
Route::post('create-test-bookings', 'admin\CreateTestBooking@store')->name('create-test-bookings');
Route::get('get-user-properties/{user_account_id}', 'admin\CreateTestBooking@getUserProperties')->name('adminGetUserProperties');
Route::get('get-property-rooms/{property_info_id}', 'admin\CreateTestBooking@getPropertyRooms')->name('adminGetPropertyRooms');

Route::get('report-exception-log', 'admin\ReportExceptionLog@index')->name('report-exception-log');
Route::post('report-exception-log-fetch', 'admin\ReportExceptionLog@getExceptions')->name('report-exception-log-fetch');
Route::post('report-exception-log-fetch-id', 'admin\ReportExceptionLog@getException')->name('report-exception-log-fetch-id');
Route::get('report-pms-request-count', 'admin\ReportPmsRequests@index')->name('report-pms-request-count');

//Booking Testing Routes Start
Route::get('test-booking', 'admin\TestBookingController@index')->name('testBooking');
Route::post('get-booking-xml-json-request', 'admin\TestBookingController@show')->name('getBookingXmlJsonRequest');
Route::get('get-all-user-accounts', 'admin\TestBookingController@getAllUserAccounts')->name('getAllUserAccounts');
//Booking Testing Routes End

//User Testing Routes Start
Route::get('test-user', 'admin\TestUserController@index')->name('testUser');
Route::post('get-user-xml-json-request', 'admin\TestUserController@show')->name('getUserXmlJsonRequest');
//User Testing Routes End

//Property Testing Routes Start
Route::get('test-property', 'admin\TestPropertyController@index')->name('testProperty');
Route::post('get-properties-by-user-account', 'admin\TestPropertyController@getPropertiesByUserAccount')->name('getPropertiesByUserAccount');
Route::post('get-property-xml-json-request', 'admin\TestPropertyController@show')->name('getPropertyXmlJsonRequest');
//Property Testing Routes End

//Credit Card Testing Routes Start
Route::get('test-credit-card', 'admin\TestCreditCardController@index')->name('testCreditCard');
Route::post('get-credit-card-system-usage', 'admin\TestCreditCardController@show')->name('getCreditCardSystemUsage');
Route::post('save-new-credit-card', 'admin\TestCreditCardController@saveNewCreditCard')->name('saveNewCreditCard');
//Credit Card Testing Routes End

Route::get('assign-plans', function () {
    return view('admin.stripe_commission_billing.assign_plans_to_users');
})->name('assignPlans');
Route::get('pagi', 'admin\CompanyController@pagi')->name('pagi');
Route::get('stripe-commission-plans-list', 'admin\StripeCommissionBilling\CommissionBillingController@stripeCommissionPlansList')->name('stripeCommissionPlansList');

Route::post('get-all-billing-plans-with-user-subscribed-plans', 'admin\StripeCommissionBilling\CommissionBillingController@stripeAllBillingPlansWithUserSubscribedPlans')->name('getAllBillingPlansWithUserSubscribedPlans');
Route::post('get-all-user-accounts', 'admin\StripeCommissionBilling\CommissionBillingController@getAllUserAccountsForCommissionPlan')->name('getAllUserAccountsForCommissionPlan');
Route::post('list-user-subscriptions', 'admin\StripeCommissionBilling\CommissionBillingController@listUserSubscriptions')->name('listUserSubscriptions');
Route::post('get-subscription-details', 'admin\StripeCommissionBilling\CommissionBillingController@getSubscriptionDetails')->name('getSubscriptionDetails');
Route::post('de-attach-user-subscription-plan', 'admin\StripeCommissionBilling\CommissionBillingController@deAttachUserSubscriptionPlan')->name('deAttachUserSubscriptionPlan');
Route::post('get-all-billing-plans', 'admin\StripeCommissionBilling\CommissionBillingController@getAllBillingPlan')->name('getAllBillingPlan');
Route::post('attach-plan-to-subscription', 'admin\StripeCommissionBilling\CommissionBillingController@attachPlansToSubscription')->name('attachPlansToSubscription');
Route::post('save-subscription', 'admin\StripeCommissionBilling\CommissionBillingController@saveSubscription')->name('saveSubscription');
Route::post('add-subscription/{trial_days}', 'admin\StripeCommissionBilling\CommissionBillingController@addSubscription')->name('addSubscription')->where('trial_days', '[0-9]+');
Route::post('cancel-subscription', 'admin\StripeCommissionBilling\CommissionBillingController@removeSubscription')->name('removeSubscription');
Route::post('create-stripe-billing-customer', 'admin\StripeCommissionBilling\CommissionBillingController@createStripeBillingCustomer')->name('createStripeBillingCustomer');
Route::post('create-customer-with-out-card', 'admin\StripeCommissionBilling\CommissionBillingController@createStripeBillingCustomerWithOutCard')->name('createStripeBillingCustomerWithOutCard');

Route::get('write-access', 'admin\TestBookingController@writeAccess')->name('testWriteAccess');
Route::get('write-access-check', 'admin\TestBookingController@CheckWriteAccess')->name('CheckWriteAccess');