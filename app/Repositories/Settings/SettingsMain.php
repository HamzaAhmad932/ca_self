<?php

namespace App\Repositories\Settings;


class SettingsMain
{

    private $pms_type;

    public function __construct($pms_type)
    {

        $this->pms_type = $pms_type;
    }

}