<?php


namespace App\PMS\BAEssentials;


use App\PMS\General;
use Illuminate\Http\Request;

class ApiRequestEssentials extends General
{
    /**
     * Validate Request IP Before Processing Request
     * @param Request $request
     * @return bool
     */
    public function validateApiPMSRequestIP(Request $request)
    {
        if(config('app.env') == 'production' && config('app.debug') == false)
            return ($request->ip() !== '176.9.52.204') && ($request->ip() !== '195.201.74.20')
                && ($request->ip() !== '77.104.162.129');
        else
            return true;
    }


}
