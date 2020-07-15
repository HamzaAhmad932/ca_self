<?php

namespace App\Http\Requests;

use App\GuideBook;
use App\Rules\GuideBookListingInternalNameRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuideBooksStoreRequest extends FormRequest
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
            'type_id'=>['required'],
            'internal_name'=>[new GuideBookListingInternalNameRule($this->serve_id)],
            'icon'=>'',
            'text_content'=>['required'],
            'selected_properties'=>['array','required'],
            'selected_properties.*.attached_rooms'=>['array','required'],
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
            'type_id.required' => 'Please select a type.',
            'text_content.required'  => 'Please provide content for the Guidebook.',
            'selected_properties.required'=>'Please select al least one property to attach.',
            'selected_properties.*.attached_rooms.required'=>'Must Select Rental(s) For Selected Properties.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
//    public function attributes()
//    {
//        return [
//            'selected_properties.*.attached_rooms' => '',
//        ];
//    }
}
