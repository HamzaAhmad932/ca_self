<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Settings\PaymentTypeMeta;

class PaymentTypeMetaProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPaymentTypeMetaProvider();
    }

    private function registerPaymentTypeMetaProvider() {

        $this->app->singleton(PaymentTypeMeta::class, function ($app){

            return new PaymentTypeMeta();

        });

    }
}
