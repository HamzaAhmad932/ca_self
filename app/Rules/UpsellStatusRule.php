<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UpsellStatusRule implements Rule
{

    /**
     * @var array
     */
    private $request;

    public function __construct($request = null)
    {
        $this->request = $request;
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
        return $this->request->status ?  !empty($this->request->selected_properties) : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Not any Rental attached. Kindly attach at least one rental to activate this upsell.';
    }
}
