<?php

namespace App\Providers;

use App\System\StripeCommissionBilling\StripeCommissionBillingServiceProvider;
use App\Repositories\RepositoryServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Settings\GenericAmountType;
use App\System\PaymentGateway\Models\CredentialFormField;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $amountType = array ( 'fixed' => GenericAmountType::AMOUNT_TYPE_FIXED,
                              'percentage' => GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE,
                              'first_night' => GenericAmountType::AMOUNT_TYPE_FIRST_NIGHT );
        
        $formFieldType = array ( 'text' => CredentialFormField::TYPE_TEXT ,
                                 'button' => CredentialFormField::TYPE_BUTTON );


        view::share( 'amount_type' , $amountType );
        view::share( 'formFieldType', $formFieldType );

        if(config('app.debug')){

//            DB::listen(function($query) {
//                Log::info(
//                    $query->sql,
//                    $query->bindings,
//                    $query->time
//                );
//            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RepositoryServiceProvider::class);
        $this->app->register(StripeCommissionBillingServiceProvider::class);
    }
}
