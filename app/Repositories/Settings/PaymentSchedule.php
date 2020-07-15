<?php

namespace App\Repositories\Settings;

class PaymentSchedule
{


    public $status;
    public $onlyVC = false;
    public $amountType;
    public $amountTypeValue;
    public $afterBookingDays;
    public $beforeCheckInDays;
    public $remainingBeforeCheckInDays;
    public $dayType;


    public function __construct(string $json = null)
    { 
        if($json != null) {
            $settings = json_decode($json,true);
            $objVars = get_object_vars($this);
            $this->setOnlyVc($settings);
            foreach ($objVars as $key => $var) {
                if(key_exists($key, $settings))
                    $this->$key = (($key == 'status')  ? filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN) : abs($settings[$key]));
            }
            $this->remainingBeforeCheckInDays = (((abs($this->amountTypeValue) === 100) && (abs($this->amountType) === GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE)) ? 0 : $this->remainingBeforeCheckInDays);
        }
    }

    public function toJSON(array $data){
        $this->setOnlyVc($data);
        $this->status = $data['status'];
        $this->amountType = abs($data['amountType']);
        $this->amountTypeValue = abs($data['amountTypeValue']);
        $this->afterBookingDays = abs($data['afterBookingDays']);
        $this->beforeCheckInDays = abs($data['beforeCheckInDays']);

        if (isset($data['radio']))
            $this->dayType = abs($data['radio']);
        else if (isset($data['dayType']))
            $this->dayType = abs($data['dayType']);

        $this->remainingBeforeCheckInDays = (((abs($data['amountTypeValue']) === 100) && (abs($data['amountType']) === GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE)) ? 0 : abs($data['remainingBeforeCheckInDays']));
        return json_encode($this);
    }


    /**
     * @param array $settings
     */
    private function setOnlyVc(array $settings) {
        // For Old users whose OnlyVc Settings not set.
        $this->onlyVC =  !isset($settings['onlyVC']) ? false : $settings['onlyVC'];
    }
}