<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/11/18
 * Time: 9:27 AM
 */

namespace App\System\PMS\Models;


class UtilClass
{
    /**
     * @return string|null
     */
    public function toJson() {
        $array = array();

        foreach (get_object_vars($this) as $key => $value)
            if($value != null)
                $array[$key] = $value;

        if(isset($array))
            return json_encode($array);

        return null;
    }

}