<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class UpsellTimeFrameRule implements Rule
{
    private $request;


    private $from_time;
    private $to_time;
    /**
     * UpsellTimeFrameRule constructor.
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request->meta;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        try {

        if ((empty($this->request['from_time']) && empty($this->request['to_time']))
            || ($this->request['from_time'] === '00:00' && $this->request['to_time']  === '00:00'))
            return true;

        if (strpos($value, ':') == false || strlen($value) != 5)
            return false;

        $time = explode( ':', $value);

        if ((count($time) == 2) && $time[0] <= 12 && $time[1] <= 59) {
            $from = explode( ':', $this->request['from_time']);
            $from = (sprintf("%02s", $from[0]).':'.sprintf("%02s",$from[1]). ' ' . $this->request['from_am_pm']);

            $to = explode( ':', $this->request['to_time']);
            $to = (sprintf("%02s", $to[0]).':'.sprintf("%02s",$to[1]). ' ' . $this->request['to_am_pm']);

           // if ($this->request['from_am_pm'] == $this->request['to_am_pm']) {
                $this->from_time = Carbon::createFromFormat('h:i a', $from);
                $this->to_time = Carbon::createFromFormat('h:i a', $to);
                return $this->to_time->greaterThan($this->from_time);
            //}

            //return true;
        }

        return false;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!empty($this->to_time) && !empty($this->from_time)) {
            return 'The "Time to" field must be greater than  "Time from" field.';
        } else {
            return 'The given data was invalid.';
        }
    }
}
