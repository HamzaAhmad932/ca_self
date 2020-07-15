<?php

namespace App\Repositories\Settings;


use Illuminate\Http\Request;

class CreditCardValidation {

    public $status;
    public $amountType;
    public $amountTypeValue;
    public $authorizeAfterDays;
    public $autoReauthorize;
    public static $autoReauthorizeDays = 7;


    public function __construct(string $json = null)
    {
         if($json != null) {
            $settings = json_decode($json,true);
            $objVars = get_object_vars($this);
            foreach ($objVars as $key=> $var) {
                if(key_exists($key, $settings))
                    $this->$key = (($key == 'status') || ($key == 'autoReauthorize') ? filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN) : abs($settings[$key]));
            }
        }
    }

    public function toJSON(array $data){

        $this->status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
        $this->amountType = abs($data['amountType']);
        $this->amountTypeValue = abs($data['amountTypeValue']);
        $this->authorizeAfterDays = abs($data['authorizeAfterDays']);
        $this->autoReauthorize = filter_var($data['autoReauthorize'], FILTER_VALIDATE_BOOLEAN);
        return json_encode($this);
    }

}