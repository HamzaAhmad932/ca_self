<?php


namespace App\Services;


use App\BookingInfo;
use App\CaCapability;
use App\Repositories\BookingSources\BookingSources;

class CapabilityService
{
    /**
     * @param BookingInfo $booking_info
     * @return bool
     */
    public static function isAnyPaymentOrSecuritySupported(BookingInfo $booking_info)
    {
        $capabilities = self::allCapabilities($booking_info);
        return $capabilities[CaCapability::AUTO_PAYMENTS]
            || $capabilities[CaCapability::MANUAL_PAYMENTS]
            || $capabilities[CaCapability::SECURITY_DEPOSIT];
    }

    public static function isAutoPaymentOrSecuritySupported(BookingInfo $booking_info)
    {
        $capabilities = self::allCapabilities($booking_info);
        return $capabilities[CaCapability::AUTO_PAYMENTS]
            || $capabilities[CaCapability::SECURITY_DEPOSIT];
    }


    /**
     * @param BookingInfo $booking_info
     * @return array
     */
    public static function allCapabilities(BookingInfo $booking_info)
    {
        $capabilities = BookingSources::getBookingSourceAllCapabilitiesById($booking_info->bookingSourceForm->id);
        $auto_processing = $capabilities[CaCapability::AUTO_PAYMENTS];
        return [
            CaCapability::AUTO_PAYMENTS    => $auto_processing,
            CaCapability::SECURITY_DEPOSIT => $auto_processing ?: $capabilities[CaCapability::SECURITY_DEPOSIT],

            CaCapability::MANUAL_PAYMENTS  => $auto_processing || $capabilities[CaCapability::SECURITY_DEPOSIT]
                ? true : $capabilities[CaCapability::MANUAL_PAYMENTS],

            CaCapability::GUEST_EXPERIENCE => $auto_processing ?: $capabilities[CaCapability::GUEST_EXPERIENCE],
        ];
    }


    /**
     * @param BookingInfo $booking_info
     * @return bool
     */
    public static function isAutoPaymentSupported(BookingInfo $booking_info)
    {
        return BookingSources::isCapabilitySupportedByBookingSource(CaCapability::AUTO_PAYMENTS,
            $booking_info->bookingSourceForm->id);
    }

    /**
     * @param BookingInfo $booking_info
     * @return bool
     */
    public static function isManualPaymentSupported(BookingInfo $booking_info)
    {
        return self::isAutoPaymentSupported($booking_info)
            ?:BookingSources::isCapabilitySupportedByBookingSource(CaCapability::MANUAL_PAYMENTS,
            $booking_info->bookingSourceForm->id);
    }

    /**
     * @param BookingInfo $booking_info
     * @return bool
     */
    public static function isSecuritySupported(BookingInfo $booking_info)
    {
        return BookingSources::isCapabilitySupportedByBookingSource(CaCapability::SECURITY_DEPOSIT,
            $booking_info->bookingSourceForm->id);
    }

    /***
     * @param BookingInfo $booking_info
     * @return bool
     */
    public static function isGuestExperienceSupported(BookingInfo $booking_info)
    {
        return BookingSources::isCapabilitySupportedByBookingSource(CaCapability::GUEST_EXPERIENCE,
            $booking_info->bookingSourceForm->id);
    }

    /**
     * @param BookingInfo $booking_info
     * @return bool
     */
    public static function isFetchBookingSupported(BookingInfo $booking_info)
    {
        return BookingSources::isCapabilitySupportedByBookingSource(CaCapability::FETCH_BOOKING,
            $booking_info->bookingSourceForm->id);
    }

}
