<?php

namespace App\Rules;

use App\Upsell;
use Illuminate\Contracts\Validation\Rule;

class UpsellListingInternalNameRule implements Rule
{
    private $upsell_id = 0;

    /**
     * UpsellListingInternalNameRule constructor.
     * @param $upsell_id
     */
    public function __construct($upsell_id)
    {
        $this->upsell_id = $upsell_id;
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
        return Upsell::where([
            ['user_account_id', auth()->user()->user_account_id],
            ['internal_name', $value],
            ['id','!=', $this->upsell_id]
        ])->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The internal name not valid & must be unique.';
    }
}
