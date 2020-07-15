<?php

//------------------------------------------------------------------------------------------
//Apply Master Settings Middleware On these Routes 
//-----------------------------------------------------------------------------------------




Route::group([
    'prefix'=>'v2', 
    'middleware' => ['masterSettings'], 
    'where' => [
        'id'=>'[0-9]+', 
        'booking_info_id' => '[0-9]+', 
        'booking_id' => '[0-9]+', 
        'property_info_id' => '[0-9]+',
        'classified' => '^(true|false|1|0)$',
        'forFilters' => '^(true|false|1|0)$', 
        'propertyInfoId' => '[0-9]+']
    ], function () {

    //Dashboard Routes
    Route::get('dashboard', 'v2\client\DashboardController@index')->name('v2dashboard');

    //Email Settings Routes
//    Route::get('email-settings', function() {
//        $subject = config('db_const.sent_email.all_emails.new_chat_message_email_to_guest.subject');
//        $content = config('db_const.sent_email.all_emails.new_chat_message_email_to_guest.content');
//        return view('v2\client\settings\email-settings', ['subject' => $subject, 'content' => $content]);
//    });
//
//    Route::get('preview-email', function(\Illuminate\Http\Request $request) {
//
//        $filtered_subject = 'New Message Received | Tim Brook | Nov 24 | 1528871';
//        $content = (new \App\Mail\GuestLayoutEmail($request->content))->render();
//
//        return response()->json([
//            'filtered_subject' => $filtered_subject,
//            'email_content' => $content
//        ]);
//    });

    //Booking Routes
    Route::get('booking-detail/{id}', 'v2\client\BookingDetailController@index')->name('bookingDetailPage');
    Route::post('/booking', 'v2\client\BookingController@getBookingList')->name('get.bookings');
    Route::get('/booking/{booking_info_id}', 'v2\client\BookingController@getBookingDetail')->name('get.booking');
    Route::post('resend-pre-checkin-wizard-email', 'v2\client\BookingController@resend_pre_checkin_wizard_email')->name('resendPreCheckinWizardEmail');

    Route::get('get-booking-detail-header/{booking_info_id}', 'v2\client\BookingDetailController@getBookingDetailHeader');
    Route::get('/get-booking-detail/{booking_info_id}', 'v2\client\BookingDetailController@getBookingDetail');
    Route::get('/get-payments-information/{booking_info_id}', 'v2\client\BookingDetailController@getPaymentsInformation');
    //Route::get('/get-activity-logs/{booking_info_id}', 'v2\client\BookingDetailController@getActivityLog');
    Route::get('/get-sent-emails/{booking_info_id}', 'v2\client\BookingDetailController@getSentEmails');
    Route::get('/get-guest-documents/{booking_info_id}', 'v2\client\BookingDetailController@fetchGuestImages');
    Route::post('save-booking-detail', 'v2\client\BookingDetailController@saveBookingDetail');

    Route::get('/get-guest-experience/{booking_info_id}', 'v2\client\BookingDetailController@getGuestExperienceTabData');
    Route::post('save-guest-experience', 'v2\client\BookingDetailController@saveGuestExperienceTabData');

    //TeamController Routes--------------------------------------------------------------------------

    Route::get('manageteam', 'v2\client\TeamController@index')->name('v2manageteam');
    Route::post('v2team_list', 'v2\client\TeamController@team_list')->name('v2team_list');
    Route::get('v2GetAllRolesAndPermissions', 'v2\client\TeamController@GetAllRolesAndPermissions')->name('v2GetAllRolesAndPermissions');
    Route::post('v2member_create', 'v2\client\TeamController@create')->name('v2member_create');
    Route::post('v2resend_invite/{id}', 'v2\client\TeamController@resendInvitation')->name('v2resend_invite');
    Route::post('v2memberstatus/{id}/{st}', 'v2\client\TeamController@memberstatus')->name('v2memberstatus');
    Route::delete('soft_delete/{id}', 'v2\client\TeamController@soft_delete')->name('soft_delete');
    Route::get('v2memberprofile/{id?}', 'v2\client\TeamController@profile')->name('v2memberprofile');
    Route::post('v2memberupdate/{id}', 'v2\client\TeamController@update')->name('v2memberupdate');
    Route::post('v2attach_role', 'v2\client\TeamController@attachRole');
    Route::post('v2attach_permission', 'v2\client\TeamController@attachPermission')->name('v2attachPermission');
    Route::post('edit-team-member-role', 'v2\client\TeamController@editTeamMemberRole')->name('editTeamMemberRole');
    Route::post('edit-team-member-permission', 'v2\client\TeamController@editTeamMemberPermission')->name('editTeamMemberPermission');
    //Route::get('v2user_log', 'v2\client\TeamController@user_log');
    Route::post('v2user_log', 'v2\client\TeamController@userLog');
    Route::post('reduce-amount', 'v2\client\BookingController@reduceAmount')->name('reduceAmount');
    Route::post('mark-as-paid', 'v2\client\BookingController@markAsPaid')->name('markAsPaid');
    Route::post('manually-void-transaction', 'v2\client\BookingController@manuallyVoidTransaction')->name('manuallyVoidTransaction');
    Route::post('manually-void-auth', 'v2\client\BookingController@manuallyVoidAuth')->name('manuallyVoidAuth');

    Route::get('/all_notifications', 'v2\client\ClientController@allNotificationsV2')->name('v2allNotifications');
    Route::post('/get_all_notifications', 'v2\client\ClientController@get_all_notifications')->name('v2get_all_notifications');
    Route::post('notification_read/{id}/{st}', 'v2\client\ClientController@notificationRead')->name('v2NotificationRead');
    Route::post('mark_all_as_read', 'v2\client\ClientController@markAllAsRead')->name('mark_all_as_read');
    Route::delete('notification_destroy/{id}', 'v2\client\ClientController@notificationSoftDelete')->name('notificationDestroy');

    //SettingsController Routes--------------------------------------------------------------------------
    Route::get('settings/{page?}', 'v2\client\SettingsController@index')->name('v2settings');
    Route::get('preferences-template-var-v2', 'v2\client\SettingsController@preferencesTemplateVar')->name('v2PreferencesTemplateVar');
    Route::post('boxsettings', 'v2\client\SettingsController@boxsettings')->name('boxsettings');
    Route::post('mailsettings', 'v2\client\SettingsController@mailsettings')->name('mailsettings');
    Route::post('reattempt', 'v2\client\SettingsController@reattemptsettings')->name('reattempt');
    Route::post('/revertToDefaultSetting', 'v2\client\SettingsController@revertToDefaultSetting');
    //Route::get('/fetch-preferences', 'v2\client\SettingsController@fetchPreferences');
    Route::get('/fetch-notification-settings', 'v2\client\SettingsController@fetchNotificationSettings');
    Route::post('/save-custom-preferences', 'v2\client\SettingsController@saveCustomPreferences');
    Route::post('/preference-on-off', 'v2\client\SettingsController@preferenceOnOff');


    Route::get('online-check-in', 'v2\client\GeneralSettingsController@index')->name('v2generalSettings');//general-settings

    //Property controller Routes -----------------------------------------------
    /* Properties List View Regarding Routes */
    //Route::get('properties-v2', 'v2\client\PropertyController@index')->name('v2properties');
    Route::get('properties', 'v2\client\PropertyController@index')->name('v2properties');
    Route::post('get-properties', 'v2\client\PropertyController@getProperties')->name('get-properties');//Paginated
    Route::post('get-properties-names', 'v2\client\PropertyController@getPropertiesNames')->name('get-properties-names'); //User All Properties
    Route::post('get-properties-room-infos', 'v2\client\PropertyController@getRoomInfo')->name('get-propertyRoomInfo'); //User All Properties
    Route::post('connect-disconnect-property', 'v2\client\PropertyController@connectDisconnectProperty')->name('connectDisconnectProperty');
    Route::get('get-all-properties-cities', 'v2\client\PropertyController@getAllPropertiesCities')->name('getAllPropertiesCities');
    Route::post('export-properties-list', 'v2\client\PropertyController@exportAllProperties')->name('exportAllProperties');


    /* Property All Settings Routes */
    Route::post('use-property-bs-settings/{propertyInfoId}', 'v2\client\PropertyController@changeUsePropertyBookingSourceSettingLocalOrGlobal')->name('usePropertyBookingSourceSettingLocalOrGlobal');
    Route::post('get-property-bs-details', 'v2\client\PropertyController@getPropertyBookingSourcesWithDetail')->name('getPropertyBookingSourcesWithDetail');
    Route::post('get-client-bs-settings', 'v2\client\PropertyController@getClientBookingSourcePreviousSettings')->name('getClientBookingSourcePreviousSettings');
    Route::post('save-bs-settings', 'v2\client\PropertyController@saveClientBookingSourceSettings')->name('saveClientBookingSourceSettings');
    Route::post('use-property-pg-settings/{propertyInfoId}', 'v2\client\PropertyController@changeUsePropertyPaymentGatewaySettingLocalOrGlobal')->name('usePropertyPaymentGatewaySettingLocalOrGlobal');
    Route::post('get-property-local-payment-gateway', 'v2\client\PropertyController@getPropertyLocalPaymentGatewayWithKeys')->name('getPropertyLocalPaymentGatewayWithKeys');
    /*Route::post('get-payment-gateway-with-keys', 'v2\client\PropertyController@getPaymentGatewayWithKeys')->name('getPaymentGatewayWithKeys');
    Route::post('save-payment-gateway-keys', 'v2\client\PropertyController@savePaymentGatewayKeys')->name('savePaymentGatewayKeys');
    Route::post('pg-store-without-auth-test/{propertyInfoId}', 'v2\client\PropertyController@paymentGatewayStoreWithoutAuthTest')->name('paymentGatewayStoreWithoutAuthTestV2');
    Route::get('getResponseFromStripeConnectAndRedirect', 'v2\client\PropertyController@getResponseFromStripeConnectAndRedirect')->name('getResponseFromStripeConnectAndRedirect');*/
    Route::post('update-property-logo/{id}', 'v2\client\PropertyController@updatePropertyLogo')->name('updatePropertyLogo');
    Route::post('update-property-email/{id}', 'v2\client\PropertyController@updatePropertyEmail')->name('updatePropertyEmail');
    Route::post('get-property-info', 'v2\client\PropertyController@getPropertyInfo')->name('getPropertyInfo');
    Route::post('sync-property-info', 'v2\client\PropertyController@syncPropertyInfo')->name('syncPropertyInfo');


    //Booking Controller Routes----------------------------------------------------
    Route::get('bookings', 'v2\client\BookingController@index')->name('v2bookings');
    Route::get('bookings_list', 'v2\client\BookingController@bookings_list')->name('bookings_data');
    Route::post('bookings_short_desc', 'v2\client\BookingController@bookings_short_desc')->name('bookings_short_desc');


    Route::get('fetch-guest-cc/{booking_id}', 'v2\client\BookingController@fetchGuestCc')->name('fetch-guest-cc');

    //Route::get('guest_booking_details/{id}', 'v2\client\BookingController@guest_details')->name('guest_booking_details')->middleware('signed');

    /* Bookings effected on payment processor change */
    Route::get('{property_info_id}/effected-bookings', 'v2\client\BookingController@effectedBookings')->name('effectedBookings');
    Route::post('{property_info_id}/effected-bookings-short-desc', 'v2\client\BookingController@effectedBookings_short_desc')->name('effected_bookings_short_desc');
    Route::get('{property_info_id}/effectedBookings_list', 'v2\client\BookingController@effectedBookings_list')->name('effected_bookings_data');


    Route::post('guest_chat', 'v2\client\BookingController@communication')->name('guest_chat');
    Route::post('allmsgs', 'v2\client\BookingController@allmsgs')->name('allmsgs');

    Route::post('pay-now-uri', 'v2\client\BookingController@chargeNowBooking')->name('paynow');
    Route::post('charge-more', 'v2\client\BookingController@chargeMoreBooking')->name('client-charge-more');
    Route::post('update-card-by-client', 'v2\client\BookingController@updateCardNow')->name('update-card-by-client');

    // Booking channel Setting Routes
    Route::get('booking-channel-settings', 'v2\client\SettingsController@bookingChannelSetting')->name('bookingChannelSetting');
    Route::post('get-booking-channels-settings', 'v2\client\SettingsController@getUserFetchBookingSettings')->name('getUserFetchBookingSettings');
    Route::post('bookingSource-on-off', 'v2\client\SettingsController@fetchBookingSettingOnOff')->name('fetchBookingSettingOnOff');
    Route::post('bookingSource-on-off-all', 'v2\client\SettingsController@fetchBookingSettingOnOffAll')->name('fetchBookingSettingOnOffAll');

    Route::post('updateStatus/{classified?}' , 'v2\client\BookingController@StatusUpdate');


    Route::group(['as'=>'v2.'], function () {


        Route::post('charge-for-damages', 'v2\client\BookingController@chargeForSecurityDamages')->name('damagecharges');
        Route::post('manual-re-attempt', 'v2\client\BookingController@manualReattempt')->name('manual-reattempt');
        Route::post('refund-amount', 'v2\client\BookingController@refundAmount')->name('refund-amount');
        Route::post('refund-amount-sdd', 'v2\client\BookingController@refundAmountSDD')->name('refund-amount-sdd');
        Route::post('status-void', 'v2\client\BookingController@makeTransactionStatusVoid')->name('trans-status-void');
        Route::post('capture-auth', 'v2\client\BookingController@captureAuthAmount')->name('capture-auth');
        Route::post('pay-now-auth', 'v2\client\BookingController@paynowSecurityDamageDeposit')->name('paynow-sdd');
        Route::post('refund', 'v2\client\BookingController@refund')->name('booking.refund');
        Route::post('refund-sdd', 'v2\client\BookingController@refundSDD')->name('refund-sdd');
    });



    /** TERM & CONDITIONS ROUTES*/
    Route::get('/terms-and-conditions','v2\client\TermsAndConditionsController@index')->name("tac");//Redirect To Terms & Conditions List Page
    Route::post('/terms-and-conditions-get-all','v2\client\TermsAndConditionsController@getAll')->name("tacAll");//Load All Terms and Conditions With or Without Applying Filters
    Route::get('/terms-and-conditions-add','v2\client\TermsAndConditionsController@create')->name("tacAdd");//Redirect To Add New Terms & Conditions List Page
    Route::post('/terms-and-conditions-old','v2\client\TermsAndConditionsController@getOldData')->name("tacOld");//Load Data For Selected Term and Condition
    Route::post('/terms-and-conditions-save','v2\client\TermsAndConditionsController@save')->name("tacSave");//Add New Terms and Conditions To Record
    Route::post('/terms-and-conditions-update','v2\client\TermsAndConditionsController@update')->name("tacUpdate");//Update Selected Terms and Conditions Data
    Route::post('/terms-and-conditions-update-status','v2\client\TermsAndConditionsController@updateStatus')->name("tacUpdateStatus");//Update Selected Terms and Conditions Publish Status Or Required Status

    /** GUIDE BOOKS ROUTES*/
    Route::get('/guide-books','v2\client\GuideBookController@index')->name("guideBooks");//Redirect To List Page
    Route::post('/guide-books-all','v2\client\GuideBookController@getAll')->name("guideBooksAll");//Load All With or Without Applying Filters
    Route::get('/guide-books-add','v2\client\GuideBookController@create')->name("guideBooksAdd");//Redirect To Add New  Page
    Route::post('/guide-books-old','v2\client\GuideBookController@getOldData')->name("guideBooksOld");//Load Data For Selected
    Route::post('/guide-books-save','v2\client\GuideBookController@save')->name("guideBooksSave");//Add To Record
    Route::post('/guide-books-update','v2\client\GuideBookController@update')->name("guideBooksUpdate");//Update Selected Data
    Route::post('/guide-books-update-status','v2\client\GuideBookController@updateStatus')->name("guideBooksUpdateSome");//Update Selected  Status
    Route::get('/guide-books-get-types','v2\client\GuideBookController@getGuideBookTypes')->name("getAllGuideBookTypes");//Get All Types

    /** GUIDE BOOKS TYPES ROUTES*/
    Route::get('/guide-books-types','v2\client\GuideBookController@viewGuideBookTypesList')->name("getGuideBookTypes");//Redirect To Types List Page
    Route::post('/guide-book-types-all','v2\client\GuideBookController@getAllTypes')->name("guideBooksTypesList");//Load All For List Page With or Without Applying Filters
    Route::get('/guide-book-types-add','v2\client\GuideBookController@createType')->name("guideBooksAddType");//Redirect To Add New  Page
    Route::post('/guide-book-type-old','v2\client\GuideBookController@getTypeOldData')->name("guideBooksOldType");//Load Data For Selected
    Route::post('/guide-book-type-save','v2\client\GuideBookController@saveType')->name("guideBooksSaveType");//Add To Record
    Route::post('/guide-book-type-update','v2\client\GuideBookController@updateType')->name("guideBooksUpdateType");//Add To Record
    Route::post('/guide-book-type-delete','v2\client\GuideBookController@deleteType')->name("guideBooksDeleteType");//Delete Record


    /** UP-SELL ROUTES*/
    Route::get('/upsells','v2\client\UpsellController@index')->name("upsells");
    Route::get('/upsell-add','v2\client\UpsellController@create')->name("upsellAdd");
    Route::post('/get-upsell-form-data','v2\client\UpsellController@getUpsellFormData')->name("getUpsellFormData");
    Route::get('/get-upsell-types/{forFilters?}/{serve_id?}','v2\client\UpsellController@getUpsellTypes')->name("getUpsellTypes");
    Route::post('/upsell-store/{upsell_id?}','v2\client\UpsellController@storeUpsellListing')->name("storeUpsellListing");
    Route::post('/all-properties-with-rooms','v2\client\UpsellController@allPropertiesWithRooms')->name("allPropertiesWithRooms");
    Route::get('/get-template-variables','v2\client\UpsellController@getTemplateVariables')->name('fetchTemplateVars');
    Route::post('/get-upsell-list', 'v2\client\UpsellController@getUpsellList')->name("getUpsellList");
    Route::post('/upsell-status-change', 'v2\client\UpsellController@upsellStatusChange')->name("upsellStatusChange");
    Route::post('/get-upsell-config', 'v2\client\UpsellController@getUpsellConfig')->name("getUpsellConfig");

    /** UP-SELL TYPES ROUTES*/
    Route::get('/upsell-types','v2\client\UpsellController@viewUpsellTypesList')->name("upsellTypes");//Redirect To Types List Page
    Route::post('/upsell-types-all','v2\client\UpsellController@getAllTypes')->name("upsellTypesList");//Load All For List Page With or Without Applying Filters
    Route::get('/upsell-types-add','v2\client\UpsellController@createType')->name("upsellAddType");//Redirect To Add New  Page
    Route::post('/upsell-type-old','v2\client\UpsellController@getTypeOldData')->name("upsellOldType");//Load Data For Selected
    Route::post('/upsell-type-save','v2\client\UpsellController@saveType')->name("upsellSaveType");//Add To Record
    Route::post('/upsell-type-update','v2\client\UpsellController@updateType')->name("upsellUpdateType");//Add To Record
    Route::post('/upsell-type-update-status','v2\client\UpsellController@updateTypeStatus')->name("upsellTypeUpdateSome");//Update Selected  Status

    /** UP-SELL Orders ROUTES*/
    Route::get('/upsell-orders','v2\client\UpsellController@upsellOrders')->name("upsellOrders");
    Route::post('/get-upsell-order-list', 'v2\client\UpsellController@getUpsellOrderList')->name("getUpsellOrderList");
    Route::post('/get-booking-upsell-orders', 'v2\client\BookingController@getBookingUpsellOrders')->name("getBookingUpsellOrders");

    /** Email Setting Routes */
    Route::get('email-settings', 'v2\client\EmailsController@emailSettings')->name('v2email_settings');
    Route::get('client-get-default-emails/{type_id?}',"v2\client\EmailsController@getDefaultEmails")->name("clientFetchDefaultEmails")->where('id', '[0-9]+');
    Route::post('client-update-default-email',"v2\client\EmailsController@updateDefaultEmail")->name("clientUpdateDefaultEmails");
    Route::get('client-get-email-types',"v2\client\EmailsController@getEmailTypes")->name("clientEmailTypes");
    Route::post('revert-to-default-email',"v2\client\EmailsController@revertToDefaultEmail")->name("clientRevertToDefaultEmail");
    /** End Email Settings  */
});

Route::post('v2/v2userimage/{id}', 'v2\client\TeamController@userimage')->name('v2userimage')->where('id', '[0-9]+');
Route::post('v2/v2companylogo/{id}', 'v2\client\TeamController@companylogo')->name('v2companylogo')->where('id', '[0-9]+');
Route::get('v2/v2user_profile', 'v2\client\TeamController@user_profile')->name('v2user_profile');
Route::post('v2/v2userupdate/{id}/{c_id}', 'v2\client\TeamController@user_update')->name('v2userupdate')->where(['id' => '[0-9]+', 'c_id' => '[0-9]+']);
Route::post('v2/v2ChangePassword', 'v2\client\TeamController@ChangePassword')->name('v2ChangePassword');



/*
 * API Routes For New Template Design V2
 * */
Route::group([
    'prefix'=> 'v2', 
    'where' => ['propertyInfoId' => '[0-9]+']], function() {
    
    Route::get('/get-upcoming-arrivals', 'v2\client\DashboardController@getDashboardUpcomingArrivals')->name('v2.dashboard');
    Route::get('/get-dashboard-analytics', 'v2\client\DashboardController@getDashboardAnalyticsData')->name('v2.dashboard');

    Route::post('communicationNotifyAlerts', 'v2\client\ClientController@communicationNotifyAlerts')->name('v2.communicationNotifyAlerts');
    Route::post('alert-action-performed', 'v2\client\ClientController@alertActionPerformed')->name('v2.alert-action-performed');
    Route::get('/fetch-preferences', 'v2\client\SettingsController@fetchPreferences');

    /**__________V2 Pms Integration Routes___________________*/
    Route::get('/pmsintegration', 'v2\client\PmsIntegrationController@viewPMSSetupStep1')->name('v2pmsintegration');
    Route::get('/pms-setup-step-1', 'v2\client\PmsIntegrationController@viewPMSSetupStep1')->name('viewPMS_SetupStep1');
    Route::get('/pms-setup-step-2', 'v2\client\PmsIntegrationController@viewPMSSetupStep2')->name('viewPMS_SetupStep2');
    Route::get('/pms-setup-step-3', 'v2\client\PmsIntegrationController@viewPMSSetupStep3')->name('viewPMS_SetupStep3');
    Route::get('/pms-setup-step-4', 'v2\client\PmsIntegrationController@viewPMSSetupStep4')->name('viewPMS_SetupStep4');
    Route::get('/pms-setup-step-5', 'v2\client\PmsIntegrationController@viewPMSSetupStep5')->name('viewPMS_SetupStep5');
    Route::post('/get-user-supported-pms-list', 'v2\client\PmsIntegrationController@getUserSupportedPmsList')->name('getUserSupportedPmsList');
    Route::post('/get-pms-credential-form-along-user-saved-keys', 'v2\client\PmsIntegrationController@getPMS_CredentialFormAlongWithUserSavedKeys')->name('getPMS_CredentialFormAlongWithUserSavedKeys');
    Route::post('/save-pms-credentials', 'v2\client\PmsIntegrationController@savePMS_Credentials')->name('savePMS_Credentials');

    Route::get('general-settings-data', 'v2\client\GeneralSettingsController@getData')->name('v2generalSettings_getData');
    Route::post('general-preferences-box-settings', 'v2\client\GeneralSettingsController@enableDisableSetting')->name('v2generalPreferencesBoxSettings');

    /**__________V2 Pms Integration Routes Properties___________________*/
    Route::post('get-properties-list-for-master-settings', 'v2\client\PmsIntegrationController@getPropertiesListForMasterSettings')->name('getPropertiesListForMasterSettings');
    Route::post('pms-connect-disconnect-property', 'v2\client\PropertyController@connectDisconnectProperty')->name('pmsConnectDisconnectProperty');
    //Route::post('pms-connect-disconnect-all-properties', 'v2\client\PropertyController@connectDisconnectAllProperties')->name('pmsConnectDisconnectAllProperties');
    Route::post('generate-api-key', 'v2\client\PropertyController@generateApiKey')->name('generateApiKey');
    Route::post('pms-get-master-pg-id-or-first-form-id', 'v2\client\PmsIntegrationController@getMasterPaymentGatewayFormId')->name('getMasterPaymentGatewayFormId');
    Route::post('pms-get-steps-completed-status', 'v2\client\PmsIntegrationController@getStepsCompletedStatus')->name('getStepsCompletedStatus');
    Route::post('bulk-connect-disconnect-properties-xml', 'v2\client\PmsIntegrationController@bulkConnectDisconnectProperties')->name('bulkConnectDisconnectProperties');


    /* Property All Settings Routes */
    Route::post('use-property-bs-settings/{propertyInfoId}', 'v2\client\PropertyController@changeUsePropertyBookingSourceSettingLocalOrGlobal')->name('usePropertyBookingSourceSettingLocalOrGlobal');
    Route::post('get-property-bs-details', 'v2\client\PropertyController@getPropertyBookingSourcesWithDetail')->name('getPropertyBookingSourcesWithDetail');
    Route::post('get-client-bs-settings', 'v2\client\PropertyController@getClientBookingSourcePreviousSettings')->name('getClientBookingSourcePreviousSettings');
    Route::post('save-bs-settings', 'v2\client\PropertyController@saveClientBookingSourceSettings')->name('saveClientBookingSourceSettings');
    Route::post('use-property-pg-settings/{propertyInfoId}', 'v2\client\PropertyController@changeUsePropertyPaymentGatewaySettingLocalOrGlobal')->name('usePropertyPaymentGatewaySettingLocalOrGlobal');
    Route::post('get-property-local-payment-gateway', 'v2\client\PropertyController@getPropertyLocalPaymentGatewayWithKeys')->name('getPropertyLocalPaymentGatewayWithKeys');
    Route::post('get-payment-gateway-with-keys', 'v2\client\PropertyController@getPaymentGatewayWithKeys')->name('getPaymentGatewayWithKeys');
    Route::post('save-payment-gateway-keys', 'v2\client\PropertyController@savePaymentGatewayKeys')->name('savePaymentGatewayKeys');
    Route::post('pg-store-without-auth-test/{propertyInfoId}', 'v2\client\PropertyController@paymentGatewayStoreWithoutAuthTest')->name('paymentGatewayStoreWithoutAuthTestV2');
    Route::get('getResponseFromStripeConnectAndRedirect', 'v2\client\PropertyController@getResponseFromStripeConnectAndRedirect')->name('getResponseFromStripeConnectAndRedirect');
    Route::post('bulk-connect-disconnect-properties', 'v2\client\PropertyController@bulkConnectDisconnectProperties')->name('bulkConnectDisconnectPropertiesLocal');

    Route::post('company-status', 'v2\client\ClientController@companyStatus')->name('companyStatus');

    // Sync - Bookings Routes
    Route::post('can-sync-booking', 'v2\client\PmsIntegrationController@canSyncBookings')->name('canSyncBookings');
    Route::post('save-booking-sync-time', 'v2\client\PmsIntegrationController@setSyncBookingTime')->name('setSyncBookingTime');

});