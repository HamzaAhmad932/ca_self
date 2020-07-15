<?php

namespace App\System\PMS\SiteMinder;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * Description of SMX_Reservation
 *
 * @author mmammar
 */
class SMX_Reservation {
      
    public $UniqueID = null;
    
    public $CreateDateTime = null;
    public $LastModifyDateTime = null;
    public $ResStatus = null;
    
    public $RequestorID = null;
    
    public $BookingChannelType = null;
    public $BookingChannelPrimary = false;
    public $BookingChannelCompanyName = null;
    
    public $rooms = [];
    public $guests = [];
    public $comments = [];
    
    public $End = null;
    public $Start = null;
    public $TotalAmountAfterTax = null;
    public $CurrencyCode = null;
    public $TotalTaxAmount = null;    
    public $ResID_Type = null;
    public $ResID_Source = null;
    public $ResID_Value = null;
    public $HotelCode = null;
    
    /**
     * @return string
     */
    public function getComments(bool $isGuestViewable) {
        
        $comment = "";
        
        if(!empty($this->comments)) {
            foreach($this->comments as $guestComment) {
                if($guestComment->isGuestViewable == $isGuestViewable) {
                    $comment .= $guestComment->comment . ". ";
                }
            }
        }
        
        return $comment;
    }
    
    public function getNumberOfNights() {
        
        $nights = 0;
        
        try {
            
            $tz = 'GMT';
            $end = Carbon::parse($this->End, $tz);
            $start = Carbon::parse($this->Start, $tz);
            
            return $end->diffInDays($start);
            
            
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__
            ]);
        }
        
        return $nights;
    }
    
    public function getNumberOfAdults() {
        
        $adults = 0;
        
        try {
            
            foreach ($this->rooms as $room) {
                $adults += $room->getAdult();
            }
            
        }  catch (\Exception $e) {
            
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__
            ]);
        }
        
        return $adults;
    }
    
}
