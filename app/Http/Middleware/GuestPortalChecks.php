<?php

namespace App\Http\Middleware;

use \App\Http\Controllers\Guest\GuestController;
use App\BookingInfo;
use App\PropertyInfo;
use Closure;
use Illuminate\Support\Carbon;

class GuestPortalChecks
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $bookingInfo = BookingInfo::findOrFail($request->id);

        //check if checkout date is passed or booking is cancelled then return Goodbye page
        if(isset($bookingInfo) && $bookingInfo->cancellationTime != null) 
        {
            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->where('pms_id', $bookingInfo->pms_id)->first();

            $website = $bookingInfo->user;
            if($website)
                $website = $website->website;

            return response()->view('guest_v2.bookings.checkout',[
                'bookingInfo' => $bookingInfo,
                'is_cancelled' => true,
                'property_logo' => $propertyInfo->logo,
                'property_name' => $propertyInfo->name,
                'website' => $website
            ]);
        } 

        elseif(isset($bookingInfo) && $bookingInfo->check_out_date && Carbon::parse($bookingInfo->check_out_date)->toDateString() < Carbon::today('UTC')->toDateString()) 
        {
            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)->where('pms_id', $bookingInfo->pms_id)->first();

            $website = $bookingInfo->user;
            if($website)
                $website = $website->website;
            
            return response()->view('guest_v2.bookings.checkout',[
                'bookingInfo' => $bookingInfo,
                'is_cancelled' => false,
                'property_logo' => $propertyInfo->logo,
                'property_name' => $propertyInfo->name,
                'website' => $website
            ]);
        } 

        else 
        {

            //if not cancelled or expired continue to guest portal
            return $next($request);
        }
    }
}
