<?php

namespace App\Http\Resources\Admin\TermsAndConditions;

use Illuminate\Http\Resources\Json\JsonResource;

class TermsAndConditionsListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'internal_name'=>$this->internal_name,
            'text_content'=>strip_tags($this->text_content),
            'status'=>$this->status,
            'required'=>$this->required,
        ];
    }
}
