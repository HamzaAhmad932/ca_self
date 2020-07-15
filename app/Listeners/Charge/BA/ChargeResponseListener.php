<?php

namespace App\Listeners\Charge\BA;

use App\Events\Charge\BA\ChargeResponseEvent;
use App\Events\Emails\EmailEvent;
use App\Events\PMSPreferencesEvent;
use App\Events\StripeCommissionBilling\StripeCommissionUsageUpdateEvent;
use App\Jobs\EmailJobs\EmailJob;
use App\Repositories\Bookings\Bookings;
use App\Repositories\NotificationAlerts;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\StripeCommissionBilling\StripeCommissionBillingBase;

class ChargeResponseListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ChargeResponseEvent $event
     * @return void
     */
    public function handle(ChargeResponseEvent $event)
    {
        switch ($event->type) {
            case ChargeResponseEvent::CHARGE_SUCCESS_CASE:
                $this->successCase($event);
                break;
            case ChargeResponseEvent::CHARGE_FAILED_CASE:
                $this->chargeFailedCase($event);
                break;
            case ChargeResponseEvent::CHARGE_NETWORK_FAILURE_CASE:
                $this->networkFailureCase($event);
                break;
            case ChargeResponseEvent::CHARGE_3DS_REQUIRED_CASE:
                /* Inform 3DS Charge Authentication Required */
                event(new EmailEvent(config('db_const.emails.heads.charge_3ds_required.type'), $event->transaction_init->id));
                break;
        }
    }


    public function successCase(ChargeResponseEvent $event)
    {
        /**
         * Below code block send email to client on successful charge.
         * But we are also sending separate email on successful charge of adjustment entry in case of cancellation.
         * So to avoid duplication we are checking if transaction is not of cancellation collection.
         */

        if ($event->transaction_init->transaction_type == 18) { // Cancellation Adjustment Collection

            event(new PMSPreferencesEvent(
                    $event->transaction_init->user_account,
                    $event->transaction_init->booking_info,
                    $event->transaction_init->id,
                    config('db_const.user_preferences.preferences.PAYMENT_COLLECTION_ON_CANCELLATION'))
            );

            event(
                new EmailEvent(
                    config('db_const.emails.heads.payment_collected_for_cancelled_booking.type'),
                    $event->transaction_detail->id
                )
            );

        } else {
            event(
                new PMSPreferencesEvent(
                    $event->transaction_init->user_account,
                    $event->transaction_init->booking_info,
                    $event->transaction_init->id,
                    config('db_const.user_preferences.preferences.PAYMENT_SUCCESS')
                )
            );

            event(
                new EmailEvent(
                    config('db_const.emails.heads.payment_successful.type'),
                    $event->transaction_detail->id
                )
            );
        }

        /*Dispatch Event To Update Stripe Usage Volume*/
        StripeCommissionUsageUpdateEvent::dispatch(
            StripeCommissionBillingBase::TRANSACTION_INIT_MODEL,
            $event->transaction_init->id,
            StripeCommissionBillingBase::ACTION_NUMBER_OF_TRANSACTION_UPDATE
        );

    }

    /**
     * @param ChargeResponseEvent $event
     */
    private function networkFailureCase(ChargeResponseEvent $event)
    {
        if ($event->transaction_init->attempts_for_500 >= 3) {
            sendMailToAppDevelopers(
                'Network Failure',
                'Network Failure',
                json_encode($event->transaction_init, JSON_PRETTY_PRINT)
            );
        }
    }


    /**
     * @param ChargeResponseEvent $event
     */
    public function chargeFailedCase(ChargeResponseEvent $event)
    {

        if ($event->transaction_init->decline_email_sent == 0) {

            $this->notify($event, 'payment_failed');

            $reason = !empty($event->response->exceptionMessage)
                ? $event->response->exceptionMessage
                : $event->response->message;

            if ($event->transaction_init->booking_info->is_vc != BS_Generic::PS_VIRTUAL_CARD) {
                // Mail to Client & Guest
                event(new EmailEvent(
                        config('db_const.emails.heads.payment_failed.type'),
                        $event->transaction_init->id,
                        ['reason' => $reason])
                );

            } else {

                // Mail to Client Only
                EmailJob::dispatch(
                    config('db_const.emails.heads.payment_failed.type'),
                    'client',
                    $event->transaction_init->id,
                    ['reason' => $reason]
                )->onQueue('send_email');


                sendMailToAppDevelopers(
                    'VC Payment Declined',
                    'Vc Booking Payment Declined in ' . __CLASS__ . ', 
                    Booking_info_id' . $event->transaction_init->booking_info_id,
                    json_encode($event->transaction_detail, JSON_PRETTY_PRINT)
                );
            }

            $event->transaction_init->update(['decline_email_sent' => 1]);
        }

        Bookings::BA_reportInvalidCardForBDCChannel($event->transaction_init->booking_info);

        event(
            new PMSPreferencesEvent(
                $event->transaction_init->user_account,
                $event->transaction_init->booking_info,
                $event->transaction_init->id,
                config('db_const.user_preferences.preferences.PAYMENT_FAILED')
            )
        );
    }


    /**
     * @param ChargeResponseEvent $event
     * @param string $alert_type
     */
    public function notify(ChargeResponseEvent $event, string $alert_type)
    {
        $notify = new NotificationAlerts(0, $event->transaction_init->user_account_id);
        $notify->create(
            $event->transaction_init->booking_info_id,
            0,
            $alert_type,
            $event->transaction_init->booking_info->pms_booking_id,
            1
        );
    }
}
