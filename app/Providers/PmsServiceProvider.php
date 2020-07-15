<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 9/27/18
 * Time: 3:27 PM
 */

namespace App\Providers;


use App\System\RequestLogger;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Support\ServiceProvider;
use App\System\PMS\BookingAutomation\Errors;
use App\System\PMS\BookingAutomation\BAClient;
use App\System\PMS\BookingAutomation\BAParser;
use App\System\PMS\BookingAutomation\BookingAutomation;
use App\System\PMS\BookingAutomation\RequestParameters;
use App\System\PMS\SiteMinder\SiteMinder;
use App\System\PMS\SiteMinder\SMX_JsonClient;
use Monolog\Logger;

class PmsServiceProvider extends ServiceProvider {

//    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->registerBookingAutomationProvider();
        $this->registerBeds24Provider();
        
        $this->registerSiteMinderProvider();
    }


    private function registerBookingAutomationProvider() {

        $this->app->bind('ba_pms_form', function ($app, array $metaData){

            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    new RequestLogger($metaData),
                    new MessageFormatter(RequestLogger::MESSAGE_SIMPLE)
                )
            );

            $guzzel = new Client(['handler' => $stack]);
            $baErrors = new Errors();
            $requestParams = new RequestParameters();

            $client = new BAClient($guzzel, $baErrors, $requestParams);
            $baParser = new BAParser();

            return new BookingAutomation($client, $baParser);

        });

    }

    private function registerBeds24Provider() {

        $this->app->bind('beds_pms_form', function ($app, array $metaData){

            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    new RequestLogger($metaData),
                    new MessageFormatter(RequestLogger::MESSAGE_SIMPLE)
                )
            );

            $guzzel = new Client(['handler' => $stack]);
            $baErrors = new Errors();
            $requestParams = new RequestParameters();

            $client = new BAClient($guzzel, $baErrors, $requestParams);
            $baParser = new BAParser();

            return new BookingAutomation($client, $baParser);

        });

    }
    
    
    private function registerSiteMinderProvider() {

        $this->app->bind('lh_pms_form', function ($app, array $metaData) {

            $guzzel = new Client();
            $smxJsonClient = new SMX_JsonClient($guzzel);
            
            return new SiteMinder($smxJsonClient);

        });

    }
     
}