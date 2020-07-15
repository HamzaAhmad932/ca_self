<?php

namespace App\Http\Resources\Admin\GuideBooks;

use Illuminate\Http\Resources\Json\JsonResource;

class GuideBooksListingResource extends JsonResource
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
            'type'=>$this->guideBookType->title,
            'icon'=>(!empty($this->icon) ? $this->icon : $this->guideBookType->icon),
            'status'=>$this->status,
        ];
    }
}
