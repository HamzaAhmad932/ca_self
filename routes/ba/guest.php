<?php


Route::group(['prefix' => 'v2'], function () {

    Route::get('ba-payment-summary/{id}', 'v2\Guest\GuestController@getBaPaymentSummary');

});