<?php

namespace App\System\PMS\SiteMinder;

use App\System\PMS\Models\PmsOptions;
use App\UserAccount;

/**
 *
 * @author mmammar
 */
interface SiteMinderSpecific {
    
    
    /**
     * Retrieve Connected Publishers
     */
    public function fetch_Publisher_list();
    
}
