<?php

namespace App\Providers;


use App\System\PaymentGateway\Stripe3D\PG_Stripe3D;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Support\ServiceProvider;
use App\System\PaymentGateway\Stripe\PG_Stripe;
use App\System\PaymentGateway\Spreedly\PG_Spreedly;
use App\System\PaymentGateway\Spreedly\SpreedlyClient;
use Monolog\Logger;

class PGServiceProvider extends ServiceProvider {

    protected $defer = true;

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register() {

        $this->registerSpreedlyProvider();
        $this->registerStripe();
        $this->registerStripe3D();

    }

    private function registerSpreedlyProvider() {

        $this->app->bind(PG_Spreedly::class, function ($app, $credentials){

            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    new Logger('Logger'),
                    new MessageFormatter('{req_body} - {res_body}')
                )
            );

            $guzzel = new Client(['handler' => $stack]);
            return new PG_Spreedly($guzzel, $credentials);

        });
    }

    private function registerStripe() {
        $this->app->bind(PG_Stripe::class, function ($app, $credentials) {

            return new PG_Stripe($credentials);

        });
    }

    private function registerStripe3D() {
        $this->app->bind(PG_Stripe3D::class, function ($app, $credentials) {

            return new PG_Stripe3D($credentials);

        });
    }

}
