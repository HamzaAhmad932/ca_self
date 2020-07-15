<?php


namespace App\System\StripeCommissionBilling;

use Illuminate\Support\ServiceProvider;

class StripeCommissionBillingServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            StripeCommissionBillingInterface::class,
            StripeCommissionBilling::class
        );
    }

}