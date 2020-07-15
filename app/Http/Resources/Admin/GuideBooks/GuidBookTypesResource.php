<?php

namespace App\Http\Resources\Admin\GuideBooks;

use Illuminate\Http\Resources\Json\JsonResource;

class GuidBookTypesResource extends JsonResource
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
            'priority_name' =>config('db_const.guide_book_type.priority.'.$this->priority),
            'title'=>$this->title,
            'icon'=>$this->icon,
            'attached_records'=> $this->guideBooks->count(),
        ];
    }
}
