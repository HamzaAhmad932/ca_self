<?php

namespace App\Listeners;

use App\Events\GatewayAddEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\BookingInfo;
use App\System\PMS\BookingSources\BS_Generic;

class GatewayAddListenerCheckBooking implements ShouldQueue {
    
    use Queueable;
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Handle the event.
     *
     * @param  GatewayAddEvent  $event
     * @return void
     */
    public function handle(GatewayAddEvent $event) {
        
        $bookingInfos = [];
        $userAccount = $event->userAccount;
        $propertyInfo = $event->propertyInfo;
        
        try {
            
            if($userAccount != null) {
                
                $query = BookingInfo::where('user_account_id', $userAccount->id)
                        ->where('is_process_able', BookingInfo::PAYMENT_GATEWAY_INACTIVE)
                        ->where('check_in_date', '>=', now()->toDateTimeString())
                        ->with(['cc_Infos' => function($cQuery) {
                            
                            $cQuery->where('status', config('db_const.credit_card_infos.status.Gateway-Missing'));
                            $cQuery->where('system_usage', '<>', "");
                            $cQuery->where('system_usage', '<>', null);
                            
                        }]);

                if($propertyInfo != null)
                    $query = $query->where('property_info_id', $propertyInfo->id);
                
                $bookingInfos = $query->get();
                
                foreach($bookingInfos as $bookingInfo) {
                    
                    foreach($bookingInfo->cc_Infos as $ccInfo) {
                        
                        if($ccInfo->is_vc == 1)
                            $status = config('db_const.credit_card_infos.status.Scheduled');
                        else
                            $status = config('db_const.credit_card_infos.status.In-Retry');
                        
                        $ccInfo->update(['status' => $status]);
                    }
                    
                    $bookingInfo->update(['is_process_able' => 1]);
                    
                }

            } else {
                Log::error('User Account was null', ['File' => __FILE__]);
            }
            
        } catch(\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 
                'UserAccount' => $userAccount, 
                'PropertyInfo' => $propertyInfo, 
                'Stack'=> $e->getTraceAsString()]);
        }
        
        
        
    }
}
