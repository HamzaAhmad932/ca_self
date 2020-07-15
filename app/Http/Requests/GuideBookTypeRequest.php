<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuideBookTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return  [
            'priority'=>['required','integer'],
            'title'=>['required','max:100',
                //Check Title Should Be Unique from Current User
                Rule::unique('guide_book_types')->where(function ($query) {
                    return $query->where('id','!=', $this->serve_id)->whereNull('deleted_at')
                                 ->where('user_account_id',auth()->user()->user_account_id)
                                 ->orWhere('is_user_defined',0);
                })],
            'icon'=>['required','string'],
            'serve_id'=>['integer'],
        ];
    }
}
