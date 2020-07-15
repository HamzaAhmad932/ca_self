<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class TermsAndConditionsStoreRequest extends FormRequest
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
        return [
            'internal_name'=>['required','max:100',
                //Check Internal Name Unique from Current User Terms and Conditions
                Rule::unique('terms_and_conditions')->where(function ($query) {
                return $query->where([
                    ['user_account_id', auth()->user()->user_account_id],
                    ['id','!=', $this->serve_id]
                ]);
            })],
            'checkbox_text'=>['required','max:100'],
            'text_content'=>['required'],
            'selected_properties'=>['array','required'],
            'selected_properties.*.attached_rooms'=>['array','required'],
            'required'=>['required','boolean'],
            'status'=>['required','boolean'],
            'serve_id'=>['integer'],

        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'text_content.required'  => 'Must Provide Text To Show.',
            'selected_properties.required'=>'Must Select At least One Property To Attach.',
            'selected_properties.*.attached_rooms.required'=>'Must Select Rental(s) For Selected Properties.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'checkbox_text' => 'Text With Checkbox',
        ];
    }
}
