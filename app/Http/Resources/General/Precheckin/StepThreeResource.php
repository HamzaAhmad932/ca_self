<?php


namespace App\Http\Resources\General\Precheckin;


use Illuminate\Http\Resources\Json\JsonResource;

class StepThreeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
          "id"         => $this->id,
          "booking_id" => $this->booking_id,
          "image"      => $this->image,
          "type"       => $this->type,
          "status"     => $this->status,
          "created_at" => $this->created_at,
          "updated_at" => $this->updated_at,
        ];
    }

}
