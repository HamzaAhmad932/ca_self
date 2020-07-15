<?php

namespace App\Http\Resources;

use App\Http\Resources\BookingCreditCardAuthDetailsResource;
use App\Http\Resources\ClientBookingTransactionInitsWithDetailResource;
use App\Repositories\Settings\PaymentTypeMeta;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;


class ClientBookingDetailsResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
        $paymentTypeMeta = new PaymentTypeMeta();
        $ccAuth = $this->collection['bookingInfo']->credit_card_authorization->whereIn('type', [$paymentTypeMeta->getCreditCardAutoAuthorize(), $paymentTypeMeta->getCreditCardManualAuthorize()])->first();
        $securityAuth = $this->collection['bookingInfo']->credit_card_authorization->whereIn('type', [$paymentTypeMeta->getAuthTypeSecurityDamageValidation(), $paymentTypeMeta->getSecurityDepositManualAuthorize()])->first();

        return [
            'booking_info_id' =>$this->collection['bookingInfo']->id,
            'creditCardInfo' => $this->collection['bookingInfo']->cc_Infos->last(),
            'paymentDetails' =>[
                'transactionInits' =>  new ClientBookingTransactionInitsWithDetailResource($this->collection['transactionInit']),
                'ccAuth' => $ccAuth,
                'ccAuthDetails' => ($ccAuth != null ? $ccAuth->authorization_details : []),
                'securityAuth' => $securityAuth,
                'securityAuthDetails' => ($securityAuth != null ? $securityAuth->authorization_details : []),
            ],
        ];
    }
}