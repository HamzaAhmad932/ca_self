<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();

        $this->mapWebDevRoutes();

        $this->mapApiRoutes();

        $this->mapAdminRoutes();

        $this->mapClientRoutes();

        $this->mapGuestRoutes();

        //$this->mapv2ClientRoutes();


        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/general/web.php'));
    }

    protected function mapWebDevRoutes() {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web_dev.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
             ->middleware('admin')
             ->namespace($this->namespace)
             ->group(base_path('routes/general/admin.php'));

    }
    protected function mapClientRoutes()
    {
        Route::prefix('client')
             ->middleware('client') //General Client Routes
             ->namespace($this->namespace)
             ->group(base_path('routes/general/client.php'));

        Route::prefix('client')
             ->middleware('client') //BA Client Routes
             ->namespace('App\Http\Controllers')
             ->group(base_path('routes/ba/client.php'));


    }

    protected function mapGuestRoutes()
    {

        Route::namespace('App\Http\Controllers')
             ->group(base_path('routes/ba/guest.php'));


    }
    /*protected function mapv2ClientRoutes()
    {
        Route::prefix('v2client')
            ->namespace($this->namespace)
            ->group(base_path('routes/v2client.php'));
    }*/
}
