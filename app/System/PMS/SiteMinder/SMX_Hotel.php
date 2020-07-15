<?php

namespace App\System\PMS\SiteMinder;

/**
 * Description of SMX_Hotel
 *
 * @author mmammar
 */
class SMX_Hotel {
    
    const RESERVAIONS = 'Reservations';
    const ARI = 'ARI';


    private $code = null;
    private $name = null;
    private $currency = null;
    private $timezone = null;
    private $messageTypes = [];
    private $rooms = [];
    
    public function getName() {
        return $this->name;
    }
    
    public function getCode() {
        return $this->code;
    }
    
    public function getMessageTypes() {
        return $this->messageTypes;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setCode($code) {
        $this->code = $code;
    }
    
    public function getCurrency() {
        return $this->currency;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }
    
    public function getTimezone() {
        return $this->timezone;
    }

    public function setTimezone($timezone) {
        $this->timezone = $timezone;
    }
    
    public function setMessageTypes($messageTypes) {
        
        if(empty($messageTypes))
            return;
        
        $this->messageTypes = $messageTypes;
    }
    
    public function isMessageTypeSupported($messageType) {
        
        if(empty($messageType))
            return false;
        
        return in_array($messageType, $this->messageTypes);
        
    }
    
    public function setRooms($rooms) {
        if(!empty($rooms))
            $this->rooms = $rooms;
    }
    
    public function getRooms() {
        return $this->rooms;
    }
    
}
