<?php

namespace App\System\PMS\SiteMinder;

/**
 * Description of SMX_Publishers
 *
 * @author mmammar
 */
class SMX_Publisher {
    
    private $name = null;
    private $code = null;
    
    private $messageTypes = [];
    
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
    
}


