<?php

//------------------------------------------------------------------------------------------
//Apply Master Settings Middleware On these Routes 
//-----------------------------------------------------------------------------------------


Route::group([
    'prefix' => 'v2/ba/',
    'middleware' => ['masterSettings'],
    'where' => [
        'id' => '[0-9]+',
        'booking_info_id' => '[0-9]+',
        'booking_id' => '[0-9]+',
        'property_info_id' => '[0-9]+',
        'classified' => '^(true|false|1|0)$',
        'forFilters' => '^(true|false|1|0)$',
        'propertyInfoId' => '[0-9]+']
], function () {

    Route::get('/get-upcoming-arrivals', 'BA\Client\DashboardController@getDashboardUpcomingArrivals')->name('ba.v2.dashboard');
    Route::get('/get-dashboard-analytics', 'BA\Client\DashboardController@getDashboardAnalyticsData')->name('ba.v2.dashboard');

    Route::get('/booking/{booking_info_id}', 'BA\Client\BookingController@getBookingDetail')->name('ba.get.booking');
    Route::post('/booking', 'BA\Client\BookingController@getBookingList')->name('ba.get.bookings');


    Route::get('get-booking-detail-header/{booking_info_id}', 'BA\Client\BookingDetailController@getBookingDetailHeader');
    Route::get('/get-booking-detail/{booking_info_id}', 'BA\Client\BookingDetailController@getBookingDetail');
    Route::post('save-booking-detail', 'BA\Client\BookingDetailController@saveBookingDetail');


    Route::post('bulk-connect-disconnect-properties', 'BA\Client\PropertyController@bulkConnectDisconnectProperties')->name('ba.bulkConnectDisconnectPropertiesLocal');
    Route::post('pms-connect-disconnect-property', 'BA\Client\PropertyController@connectDisconnectProperty')->name('ba.pmsConnectDisconnectProperty');
    Route::post('connect-disconnect-property', 'BA\Client\PropertyController@connectDisconnectProperty')->name('ba.connectDisconnectProperty');
    Route::post('sync-property-info', 'BA\Client\PropertyController@syncPropertyInfo')->name('ba.syncPropertyInfo');
    Route::post('get-property-info', 'BA\Client\PropertyController@getPropertyInfo')->name('ba.getPropertyInfo');
    Route::post('get-properties', 'BA\Client\PropertyController@getProperties')->name('ba.get-properties');//Paginated


    Route::post('get-properties-list-for-master-settings', 'BA\Client\PmsIntegrationController@getPropertiesListForMasterSettings')->name('ba.getPropertiesListForMasterSettings');
    Route::post('bulk-connect-disconnect-properties-xml', 'BA\Client\PmsIntegrationController@bulkConnectDisconnectProperties')->name('bulkConnectDisconnectProperties');


    Route::post('/save-custom-preferences', 'BA\Client\SettingsController@saveCustomPreferences');
    Route::get('/fetch-preferences', 'BA\Client\SettingsController@fetchPreferences');

});