<?php

namespace App\Http\Resources\General\PaymentGateway;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllSupportedPaymentGatewaysResource extends ResourceCollection
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
            'data' => $this->collection->transform(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'logo' => asset('storage/uploads/payment_gateway_logos/').'/'.$item->logo,
                    ];
            })
        ];
    }
}
