<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use App\UserAccount;
use Illuminate\Support\Facades\Log;

class DummyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
        
    }

    public function handle() {
      
        for($i = 0; $i < 4; $i++) {
            try {

                $userAccount = UserAccount::find(1149);
                $pms = new \App\System\PMS\PMS($userAccount);
                $pmsOptions = new \App\System\PMS\Models\PmsOptions();
                $pmsOptions->requestType = \App\System\PMS\Models\PmsOptions::REQUEST_TYPE_JSON;
                $pms->fetch_user_account($pmsOptions);
         
            } catch (Exception $e) {
                Log::error($e->getMessage(), [
                    'File' => __FILE__
                ]);
            }
        }
        
        try {

                    file_get_contents('https://www.google.com');
                    file_get_contents('https://api.beds24.com/json/getBookings');
                    file_get_contents('https://www.mailgun.com/');

                } catch (Exception $e) {
                    Log::error($e->getMessage(), [
                        'Block 2' => 2,
                        'File' => __FILE__
                    ]);
                }
                
                try {
                    $gateway = new \App\System\PaymentGateway\PaymentGateway();
                    $gateway->getAccount($userAccount->user_payment_gateways->first());
                }
                 catch (Exception $e) {
                    Log::error($e->getMessage(), [
                        'Block 3' => 3,
                        'File' => __FILE__
                    ]);
                }
        
        
    }
}
