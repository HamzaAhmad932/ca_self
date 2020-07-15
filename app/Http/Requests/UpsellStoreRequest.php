<?php

namespace App\Http\Requests;

use App\Repositories\Upsells\UpsellRepositoryInterface;
use App\Rules\UpsellListingInternalNameRule;
use App\Rules\UpsellStatusRule;
use App\Rules\UpsellTimeFrameRule;
use \Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpsellStoreRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @param UpsellRepositoryInterface $types
     * @return array
     */

    public function rules(UpsellRepositoryInterface $types)
    {
        return [
            "upsell_type_id" => [
                'required',
                Rule::in(
                    $types->getUpsellTypes(auth()->user()->user_account_id,true,$this->id)->pluck('id')->toArray()
                ),
            ],
            'internal_name' => ['required', new UpsellListingInternalNameRule($this->upsell_id)],
            "status" => ['required', new  UpsellStatusRule($this), 'boolean'],
            "value" => 'required|numeric|gt:0',
            "per" => [
                'required',
                Rule::in(
                    [
                        config('db_const.upsell_listing.per.per_booking.value'),
                        config('db_const.upsell_listing.per.per_person.value')
                    ]
                )
            ],
            "period" => [
                'required',
                Rule::in(
                    [
                        config('db_const.upsell_listing.period.one_time.value'),
                        config('db_const.upsell_listing.period.daily.value')
                    ]
                )
            ],
            'meta.from_time' => new UpsellTimeFrameRule($this),
            'meta.to_time' =>  new UpsellTimeFrameRule($this),
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
            'value' => 'Price ',
            'upsell_type_id' => 'Upsell Type ',
            'internal_name' => 'Internal name ',
        ];
    }
}
