<?php

namespace App\Repositories\Settings;

use Illuminate\Http\Request;
class CancellationAmountType
{

    public $status;
    public $afterBooking;
    public $afterBookingStatus;
    public $beforeCheckIn;
    public $beforeCheckInStatus;
    public $rules;

    /**
     * if booking is non-refundable, as informed by BA then isNonRefundable would be true
     * @var $isNonRefundable
     */

    public $isNonRefundable = false;


    /*
     if not any cancellation settings saved by userin null case use default cancellation
     settings for booking_infos
    */
    const CANCELLATION_SETTINGS_DEFAULT_VALUES = '{"status":false,"afterBooking":0,"afterBookingStatus":false, "beforeCheckIn":0,"beforeCheckInStatus":false, "isNonRefundable":false, "rules":[{"canFee":0,"is_cancelled":0,"is_cancelled_value":0}]}';

    public function __construct(string $json = null)
    {
          if($json != null) {
            $settings = json_decode($json,true);
            $objVars = get_object_vars($this);
            foreach ($objVars as $key=> $var) {
                if(key_exists($key, $settings))
                    $this->$key = (($key == 'status') || ($key == 'beforeCheckInStatus') || ($key == 'afterBookingStatus') ? filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN) : $settings[$key]);
            }
        }
    }

    public function toJSON(array $data){
        $this->status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
        $this->afterBooking = abs($data['afterBooking']);
        $this->beforeCheckIn = abs($data['beforeCheckIn']);
        $this->afterBookingStatus =  filter_var($data['afterBookingStatus'], FILTER_VALIDATE_BOOLEAN);
        $this->beforeCheckInStatus = filter_var($data['beforeCheckInStatus'], FILTER_VALIDATE_BOOLEAN);
        if (isset($data['isNonRefundable']))
            $this->isNonRefundable = $data['isNonRefundable'];
        foreach ($data['rules'] as $key => $rule) {
            $data['rules'][$key]['canFee'] = ($rule['canFee'] !== 'first_night' ? abs($rule['canFee']) : $rule['canFee']);
            $data['rules'][$key]['is_cancelled'] = abs($rule['is_cancelled']);
            $data['rules'][$key]['is_cancelled_value'] = abs($rule['is_cancelled_value']);
        }
        $this->rules = $data['rules'];
        return json_encode($this);
    }


}