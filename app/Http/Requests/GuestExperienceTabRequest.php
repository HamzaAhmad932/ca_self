<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestExperienceTabRequest extends FormRequest
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
            'booking_id'=> 'required',
            //'arriving_by'=> 'required',
            //'plane_number'=> 'required_if:arriving_by,Plane'
        ];
    }

    public function messages()
    {
        return [
            'plane_number.required_if'=> 'The Flight number field is required when arriving by is Plane.'
        ];
    }
}
