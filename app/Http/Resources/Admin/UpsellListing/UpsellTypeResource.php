<?php

namespace App\Http\Resources\Admin\UpsellListing;

use Illuminate\Http\Resources\Json\JsonResource;

class UpsellTypeResource extends JsonResource
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
            'priority_name' =>config('db_const.upsell_type.priority.'.$this->priority),
            'title'=>$this->title,
            'icon'=>$this->icon,
            'attached_records'=> $this->upsells->count(),
            'status'=>$this->status,
        ];
    }
}
