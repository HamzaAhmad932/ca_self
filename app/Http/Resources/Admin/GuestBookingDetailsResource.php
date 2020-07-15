<?php

namespace App\Http\Resources;

use App\Repositories\Settings\PaymentTypeMeta;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\URL;

class GuestBookingDetailsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $paymentTypeMeta = new PaymentTypeMeta();
        $ccAuth = $this->collection['bookingInfo']->credit_card_authorization->whereIn('type', [$paymentTypeMeta->getCreditCardAutoAuthorize(), $paymentTypeMeta->getCreditCardManualAuthorize()])->first();
        $securityAuth = $this->collection['bookingInfo']->credit_card_authorization->whereIn('type', [$paymentTypeMeta->getAuthTypeSecurityDamageValidation(), $paymentTypeMeta->getSecurityDepositManualAuthorize()])->first();

        return [
            'bookingInfo' => $this->collection['bookingInfo'],
            'propertyInfo' => $this->collection['propertyInfo'],
            'creditCardInfo' => $this->collection['bookingInfo']->cc_Infos->last(),
            'paymentDetails' => [
                'transactionInits' =>  $this->collection['bookingInfo']->transaction_init,
                'ccAuth' => $ccAuth,
                'securityAuth' => $securityAuth,
            ],

        ];

    }
}
