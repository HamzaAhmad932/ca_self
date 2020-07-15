<?php

namespace App\Http\Requests;

use App\EmailReceiver;
use App\EmailTypeHead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailsContentStoreRequest extends FormRequest
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
            'email_type_head_id'=>['sometimes','required',Rule::in(EmailTypeHead::all()->pluck('id')->toArray())],
            'email_receiver_id'=>['sometimes','required',Rule::in(EmailReceiver::all()->pluck('id')->toArray())],
            'email_content'=>['required','array'],
            'email_content.subject'=>['required'],
            'email_content.message'=>['required'],
            'email_content.button_text'=>["sometimes",'required'],
            'email_content.show_button'=>["sometimes"],
            "id"=>["sometimes","required"]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
//    public function messages()
//    {
//        return [
//            'email_content.subject' => 'Subject is required!',
//            'email_content.message' => 'Message is required!',
//            'email_content.button_text'=>'Text On Button is required!',
//        ];
//    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'email_content.subject' => 'Subject',
            'email_content.message' => 'Message',
            'email_content.button_text'=>'Text On Button',
        ];
    }
}
