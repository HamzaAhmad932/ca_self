<?php

namespace App\System\PMS\SiteMinder;

/**
 * Description of SMX_Room
 *
 * @author mmammar
 */
class SMX_Room {
    
    public function __construct() {
        
        $this->GuestCounts = [
            '1' => ['value' => 0, 'name' => 'Over 21'],
            '2' => ['value' => 0, 'name' => 'Over 65'],
            '3' => ['value' => 0, 'name' => 'Under 2'],
            '4' => ['value' => 0, 'name' => 'Under 12'],
            '5' => ['value' => 0, 'name' => 'Under 17'],
            '6' => ['value' => 0, 'name' => 'Under 21'],
            '7' => ['value' => 0, 'name' => 'Infant'],
            '8' => ['value' => 0, 'name' => 'Child'],
            '9' => ['value' => 0, 'name' => 'Teenager'],
            '10' => ['value' => 0, 'name' => 'Adult'],
            '11' => ['value' => 0, 'name' => 'Senior'],
            '12' => ['value' => 0, 'name' => 'Additional occupant with adult'],
            '13' => ['value' => 0, 'name' => 'Additional occupant without adult'],
            '14' => ['value' => 0, 'name' => 'Free child'],
            '15' => ['value' => 0, 'name' => 'Free adult'],
            '16' => ['value' => 0, 'name' => 'Young driver'],
            '17' => ['value' => 0, 'name' => 'Younger driver'],
            '18' => ['value' => 0, 'name' => 'Under 10'],
            '19' => ['value' => 0, 'name' => 'Junior']
        ];
    }


    public $RoomType = null;
    public $RoomTypeCode = null;
    public $RoomTypeDescription = null;
    
    public $RatePlanName = null;
    public $RatePlanCode = null;
    public $RatePlanEffectiveDate = null;
    public $RatePlanExpireDate = null;
    public $RatePlanDescription = null;
    
    public $RoomRateNumberOfUnits = 0;
    public $RoomRateUnitMultiplier = 0;
    public $RoomRateAmountAfterTax = 0;
    public $RoomRateCurrencyCode = null;
    
    public $Start = null;
    public $End = null;

    public $TotalAmountAfterTax = 0;
    
    /**
     * Associative array with Age Qualifying Code as KEY
     * @var array 
     */
    private $GuestCounts = [];
    
    public function setGuestCount($ageQualifyingCode, $count) {
        if(key_exists($ageQualifyingCode, $this->GuestCounts))
                $this->GuestCounts[$ageQualifyingCode]['value'] = $count;
    }

    public function getOver21 () {
        return (int) $this->GuestCounts['1']['value'];
    }
    public function getOver65 () {
        return (int) $this->GuestCounts['2']['value'];
    }
    public function getUnder2 () {
        return (int) $this->GuestCounts['3']['value'];
    }
    public function getUnder12 () {
        return (int) $this->GuestCounts['4']['value'];
    }
    public function getUnder17 () {
        return (int) $this->GuestCounts['5']['value'];
    }
    public function getUnder21 () {
        return (int) $this->GuestCounts['6']['value'];
    }
    public function getInfant () {
        return (int) $this->GuestCounts['7']['value'];
    }
    public function getChild () {
        return (int) $this->GuestCounts['8']['value'];
    }
    public function getTeenager () {
        return (int) $this->GuestCounts['9']['value'];
    }
    public function getAdult () {
        return (int) $this->GuestCounts['10']['value'];
    }
    public function getSenior () {
        return (int) $this->GuestCounts['11']['value'];
    }
    public function getAdditionalOccupantWithAdult () {
        return (int) $this->GuestCounts['12']['value'];
    }
    public function getAdditionalOccupantWithoutAdult () {
        return (int) $this->GuestCounts['13']['value'];
    }
    public function getFreeChild () {
        return (int) $this->GuestCounts['14']['value'];
    }
    public function getFreeAdult () {
        return (int) $this->GuestCounts['15']['value'];
    }
    public function getYoungDriver () {
        return (int) $this->GuestCounts['16']['value'];
    }
    public function getYoungerDriver () {
        return (int) $this->GuestCounts['17']['value'];
    }
    public function getUnder10 () {
        return (int) $this->GuestCounts['18']['value'];
    }
    public function getJunior () {
        return (int) $this->GuestCounts['19']['value'];
    }
    
}
