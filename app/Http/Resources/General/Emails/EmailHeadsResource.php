<?php

namespace App\Http\Resources\General\Emails;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailHeadsResource extends JsonResource
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
            "id"=>$this->id,
            "type"=>$this->type,
            "temp_vars"=>getEmailTypeTempVars($this->type),
            "title"=>$this->title,
            "icon"=>$this->icon,
            "for_whom"=>EmailContentResource::collection($this->defaultContents),
        ];
    }
}
