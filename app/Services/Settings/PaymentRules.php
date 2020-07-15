<?php


namespace App\Services\Settings;


use App\PropertyInfo;
use App\Repositories\Settings\CancellationAmountType;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\Settings\PaymentSchedule;
use App\Repositories\Settings\SecurityDamageDeposit;
use App\UserBookingSource;

class PaymentRules
{

    /**
     * @var UserBookingSource
     */
    private $user_booking_source;

    public function __construct(PropertyInfo $property_info, int $booking_source_id) {

        $this->user_booking_source = UserBookingSource::where(
            [
                ['user_account_id', $property_info->user_account_id],
                ['property_info_id', $property_info->bs_setting_property_id],
                ['booking_source_form_id', $booking_source_id]
            ])
            ->with(
                'credit_card_validation_setting',
                'payment_schedule_setting',
                'security_damage_deposit_setting',
                'cancellation_setting'
            )->first();
    }

    /**
     * @param bool $json_decoded
     * @return PaymentSchedule|string
     */
    public function paymentScheduleSetting($json_decoded = false) {

        $setting = !empty($this->user_booking_source->payment_schedule_setting[0]->settings)
            ? $this->user_booking_source->payment_schedule_setting[0]->settings
            : UserBookingSource::DEFAULT_PAYMENT_SETTING;

        return $json_decoded ? new PaymentSchedule($setting) : $setting;
    }


    /**
     * @param bool $json_decoded
     * @return CreditCardValidation|string
     */
    public function creditCardValidationSetting($json_decoded = false) {

        $setting = !empty($this->user_booking_source->credit_card_validation_setting[0]->settings)
            ? $this->user_booking_source->credit_card_validation_setting[0]->settings
            : UserBookingSource::DEFAULT_CREDIT_CARD_SETTING;

        return $json_decoded ? new CreditCardValidation($setting) : $setting;
    }


    /**
     * @param bool $json_decoded
     * @return SecurityDamageDeposit|string
     */
    public function securityDepositSetting($json_decoded = false){

        $setting = !empty($this->user_booking_source->security_damage_deposit_setting[0]->settings)
            ? $this->user_booking_source->security_damage_deposit_setting[0]->settings
            : UserBookingSource::DEFAULT_SECURITY_DEPOSIT_SETTING;

        return $json_decoded ? new SecurityDamageDeposit($setting) : $setting;
    }

    /**
     * @param bool $json_decoded
     * @return CancellationAmountType|string
     */
    public function cancellationSetting($json_decoded = false) {

        $setting = !empty($this->user_booking_source->cancellation_setting[0]->settings)
            ? $this->user_booking_source->cancellation_setting[0]->settings
            : UserBookingSource::DEFAULT_CANCELLATION_SETTING;

        return $json_decoded ? new CancellationAmountType($setting) : $setting;
    }


    /**
     * @return bool
     */
    public function autoPayments() {
        return !empty($this->user_booking_source->status);
    }
}