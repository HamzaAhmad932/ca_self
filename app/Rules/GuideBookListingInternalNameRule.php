<?php

namespace App\Rules;

use App\GuideBook;
use Illuminate\Contracts\Validation\Rule;

class GuideBookListingInternalNameRule implements Rule
{
    private $guide_book_id = 0;

    /**
     * GuideBookListingInternalNameRule constructor.
     * @param $guide_book_id
     */
    public function __construct($guide_book_id)
    {
        $this->guide_book_id = $guide_book_id;
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
        return GuideBook::where([
            ['user_account_id', auth()->user()->user_account_id],
            ['internal_name', $value],
            ['id','!=', $this->guide_book_id]
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
