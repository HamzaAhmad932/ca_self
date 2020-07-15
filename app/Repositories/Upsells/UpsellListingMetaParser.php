<?php


namespace App\Repositories\Upsells;


class UpsellListingMetaParser
{

    public $description;
    public $from_time;
    public $from_am_pm;
    public $to_time;
    public $to_am_pm;
    public $rules = []; //[[ 'title' => null, 'icon' => 'fas fa-info', 'description' => null, 'isHighlighted'=> false ]];

    /**
     * UpsellListingMetaParser constructor.
     * @param string|null $json
     */
    public function __construct(string $json = null)
    {
        if(!empty($json)) {
            $settings = json_decode($json,true);
            $objVars = get_object_vars($this);
            foreach ($objVars as $key=> $var) {
                if(key_exists($key, $settings)) {
                    $this->removeEmptyRules($settings[$key], $key);
                    $this->$key = $settings[$key];
                }
            }
        }
    }

    /**
     * @param array $data
     * @return false|string
     */
    public function toJSON(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists(UpsellListingMetaParser::class, $key)){
                $this->removeEmptyRules($value, $key);
                $this->$key = $value;
            }
        }
        return json_encode($this);
    }

    /**
     * @param $rules
     * @param string|null $key
     */
    private function removeEmptyRules(&$rules, string $key = null)
    {
        if ($key == 'rules' && count($rules)) {
            foreach ($rules as $index => $value) {
             if(empty($value['title'])  && empty($value['description']))
                 unset($rules[$index]);
            }
            $rules = array_values($rules);
        }
    }
}