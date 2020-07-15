#! /user/bin/env bash

git pull origin master
composer install
php artisan optimize
php artisan route:cache
php artisan config:cache

php artisan queue:work
php artisan queue:work --queue=ba_new_bookings
php artisan queue:work --queue=ba_modify_bookings
php artisan queue:work --queue=ba_cancel_bookings
php artisan queue:work --queue=ba_syc_bookings
php artisan queue:work --queue=reauth
php artisan queue:work --queue=ba_charge
php artisan queue:work --queue=ba_sync_properties
php artisan queue:work --queue=reattempt
php artisan queue:work --queue=auto_refund

php artisan schedule:run
#rm .env # not recommended but can be done.

#Channel Code: 19 // As Booking Source
#PropertyId: pms (BA) property ID
#user_account_id: Your user account ID
#bookid: booking ID from pms (BA)
#status: new, cancel, modify
http://127.0.0.1:8000/api/booking_automation?channelCode=19&propertyId=44880&user_account_id=1&bookid=10371844&status=new

