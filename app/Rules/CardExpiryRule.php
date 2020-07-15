<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CardExpiryRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        //
        $month_year = explode('/', $value);

        $current_year = date('y');
        $current_month = date('m');

        if(empty($month_year[0]) || $month_year[0] == '00'){
            return false;
        }
        if(empty($month_year[1]) || $month_year[1] == '00'){
            return false;
        }

        if(count($month_year) > 0){
            $month = $month_year[0] | 0;
            $year = $month_year[1] | 0;
            if($month === 0 || $month > 12){
                return false;
            }elseif ($year === 0 || $year < $current_year){
                return false;
            }elseif ($month < $current_month && $year <= $current_year){
                return false;
            }else{
                return true;
            }
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please enter valid expiry month and year in MM/YY format.';
    }
}
