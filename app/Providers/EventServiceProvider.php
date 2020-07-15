<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        'App\Events\BAPropertyChangeEncounteredEvent' => [
            'App\Listeners\BAPropertyChangeEncounteredListener',
        ],

        'App\Events\UserSignUpEvent' => [
            'App\Listeners\UserSignUpListener',
        ],
        'App\Events\PaymentAttemptEvent' => [
            'App\Listeners\PaymentAttemptListener',
        ],
        'Illuminate\Notifications\Events\NotificationSent' => [
            'App\Listeners\UserSignUpNotificationListener',
        ],
        'App\Events\TransactionInitEvent' => [
            'App\Listeners\TransactionInitListener',
        ],
        'App\Events\NewGatewaySelectEvent' => [
            'App\Listeners\NewGatewaySelectListener',
        ],
        'App\Events\BAModifyBookingsEvent' => [
            'App\Listeners\BAModifyBookingsListener',
        ],
        'App\Events\BACancelBookingsEvent' => [
            'App\Listeners\BACancelBookingsListener',
        ],
        'App\Events\PMSPreferencesEvent' => [
            'App\Listeners\PMSPreferencesListener',
        ],
        'App\Events\GuestCaptureAuthNotifyEvent' => [
            'App\Listeners\GuestCaptureAuthNotifyListener',
        ],
        'App\Events\RefundEmailEvent' => [
            'App\Listeners\RefundEmailListener',
        ],
        'App\Events\GuestMailFor3DSecureEvent' => [
            'App\Listeners\GuestMailFor3DSecureChargeListener',
            'App\Listeners\GuestMailFor3DSecureAuthListener',
            'App\Listeners\GuestMailFor3DSecureSDDAuthListener',
        ],
        'App\Events\PropertyDisabledEvent' => [
            'App\Listeners\PauseTransactionInitsWhenPropertyIsDisabled',
            'App\Listeners\PauseAuthWhenPropertyIsDisabled',
        ],
        'App\Events\PropertyConnectStatusChangeEvent' => [
            'App\Listeners\ResumeTransactionInitsWhenPropertyIsEnabled',
            'App\Listeners\ResumeAuthWhenPropertyIsEnabled',
        ],
        'App\Events\StripeCommissionBilling\StripeCommissionWebHookEvent' => [
            'App\Listeners\StripeCommissionBilling\StripeCommissionWebHookListener',
        ],
        'App\Events\StripeCommissionBilling\StripeCommissionUsageUpdateEvent' => [
            'App\Listeners\StripeCommissionBilling\StripeCommissionUsageUpdateListener',
        ],
        'App\Events\GatewayAddEvent' => [
            'App\Listeners\GatewayAddListenerCheckBooking',
        ],
        'App\Events\Emails\EmailEvent' => [
            'App\Listeners\Emails\EmailListener',
        ],
        'App\Events\Charge\BA\ChargeResponseEvent' => [
            'App\Listeners\Charge\BA\ChargeResponseListener',
        ],
        'App\Events\Auth\BA\AuthResponseEvent' => [
            'App\Listeners\Auth\BA\AuthResponseListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
