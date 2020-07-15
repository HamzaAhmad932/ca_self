<?php


namespace App\Services;


use App\PropertyInfo;
use App\Services\Settings\PaymentRules;
use App\UserPaymentGateway;


class PropertySettings
{
    /**
     * @var PropertyInfo
     */
    private $property_info;

    public function __construct(PropertyInfo $property_info)
    {
        $this->property_info = $property_info;
    }


    /**
     *  Get Property's Current Booking Source Payment Rule Settings although local or global what-ever property using.
     * @param int $booking_source_id
     * @return PaymentRules
     */
    public function paymentRules(int $booking_source_id) {
        return new PaymentRules($this->property_info, $booking_source_id);
    }

    /**
     * Property Current Payment Gateway Local or Global.
     * @return UserPaymentGateway | null
     */
    public function paymentGateway() {
        return UserPaymentGateway::where('property_info_id', $this->property_info->pg_setting_property_id)->first();
    }
}
