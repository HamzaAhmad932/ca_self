<?php

namespace App\Repositories\EmailComponent;


/**
 * Get and set Email content to Database
 * Parser for converting json string to class Instance and array to Json String to store in DB.
 * Class EmailContent
 * @package App\Repositories\Emails
 */
class  EmailContent {

    public $subject = '';
    public $button_text = '';
    public $message = '';
    public $show_button = true; //default active

    public function __construct(string $json = null)
    {
         if($json != null) {
            $settings = json_decode($json,true);
            $this->parse($settings);
        }
    }

    /**
     * @param array $settings
     * @return false|string
     */
    public function toJSON(array $settings){
        $this->parse($settings);
        return json_encode($this);
    }


    /**
     * set Class vars with Settings.
     * @param array $settings
     */
    private function parse(array $settings) {
        $objVars = get_object_vars($this);
        foreach ($objVars as $key=> $var) {
            if(key_exists($key, $settings)) {
                $this->$key = ($key == 'show_button')
                    ? filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN)
                    : $settings[$key];
            }
        }
    }
}
