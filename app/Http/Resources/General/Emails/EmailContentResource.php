<?php

namespace App\Http\Resources\General\Emails;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //parent::toArray($request);
        return [
            "id"=>$this->receiver->id,
            "name"=>$this->receiver->name,
            "receiver_id"=>$this->receiver->receiver_id,
            "content"=>[
                "id"=>$this->id,
                "email_content"=>$this->content,
                "email_type_head_id"=>$this->email_type_head_id,
                "email_receiver_id"=>$this->email_receiver_id,
            ]
        ];
    }
}
