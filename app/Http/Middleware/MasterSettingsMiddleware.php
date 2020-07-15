<?php

namespace App\Http\Middleware;

use Closure;
use \Illuminate\Support\Facades\Auth;
use App\UserPms;
use App\UserAccount;
use App\UserPaymentGateway;
use App\UserSettingsBridge;
use Illuminate\Support\Facades\Log;

class MasterSettingsMiddleware
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
  public function handle($request, Closure $next) {

    if (!$this->valid_master_settings($request)) {
      /*if ((strtotime(auth()->user()->user_account->account_verified_at) + 120) > strtotime(now())) {
          return redirect('client/v2/pmsintegration');
      }*/
      return redirect('client/v2/pmsintegration')->with('message', "Please Complete Integration Process First!");
    }

    return $next($request);
  }

    /**
     * @param $request
     * @return bool
     */
  private function valid_master_settings($request)
  {
     try{
         $stepsCompleted = getUserPMSStepsCompletedStatus(auth()->user()->user_account_id);
         return ($stepsCompleted['step1'] && $stepsCompleted['step5']); //IF Properties Synced and PMS Integrated.
     } catch (\Exception $e) {
         Log::error($e->getMessage(),
             ['File'=> __FILE__, 'User Account ID' => auth()->user()->user_account_id, 'StackTrace' => $e->getTraceAsString()]);
         return false;
     }
  }

}
