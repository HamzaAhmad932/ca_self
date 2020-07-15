<?php

namespace App\Http\Middleware;

use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Services\CapabilityService;
use Closure;
use App\BookingInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GuestMiddleware
{
    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return Response|mixed
     */

    public function handle($request, Closure $next)
    {
        $id = $request->route()->parameter('id');
        $booking = BookingInfo::where('id', $id)->first();
        if (!empty($booking)) {

            if (!$this->guestExperienceAvailable($booking)) {

                return response()->view('v2.guest.checkout.checkout_passed',
                    [
                        'bookingInfo' => $booking,
                        'is_cancelled' => !empty($booking->cancellationTime),
                        'property_logo' => $booking->property_info->logo,
                        'property_name' => $booking->property_info->name,
                        'website' => false //TODO Change with Company Website column fix from users to UserAccount Table.
                    ]
                );

            } elseif (Auth::guest()) {

                $booking->last_seen_of_guest = Carbon::now()->toDateTimeString();
                $booking->save();
            }
        }
        return $next($request);
    }

    /**
     * @param BookingInfo $booking
     * @return bool
     */
    private function guestExperienceAvailable(BookingInfo $booking)
    {
        /**
         * @var $preference ClientGeneralPreferencesSettings
         */

        //$general_preference = new ClientGeneralPreferencesSettings($booking->user_account_id);
        //$guest_experience = config('db_const.general_preferences_form.emailToGuest');

        return (CapabilityService::isGuestExperienceSupported($booking) // CA Support Guest Experience for this booking
            && empty($booking->cancellationTime) // Booking not Cancelled
            && (Carbon::parse($booking->check_out_date)->toDateString() > now()->toDateString()) // CheckOut Date not Passed
        );
    }
}