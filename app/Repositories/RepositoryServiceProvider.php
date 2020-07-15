<?php

namespace App\Repositories;

use App\Repositories\Admin\Bookings\AdminBookingRepository;
use App\Repositories\Admin\Bookings\AdminBookingRepositoryInterface;
use App\Repositories\Bookings\BookingRepository;
use App\Repositories\Bookings\BookingRepositoryInterface;
use App\Repositories\Dashboard\DashboardInterface;
use App\Repositories\Dashboard\DashboardRepository;
use App\Repositories\Guest\GuestInterface;
use App\Repositories\Guest\GuestRepository;
use App\Repositories\TermsAndConditions\TermsAndConditionsRepository;
use App\Repositories\TermsAndConditions\TermsAndConditionsRepositoryInterface;
use App\Repositories\Upsells\UpsellRepository;
use App\Repositories\Upsells\UpsellRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            BookingRepositoryInterface::class,
            BookingRepository::class
        );
        $this->app->bind(
            AdminBookingRepositoryInterface::class,
            AdminBookingRepository::class
        );
        $this->app->bind(
            DashboardInterface::class,
            DashboardRepository::class
        );
        $this->app->bind(
            GuestInterface::class,
            GuestRepository::class
        );

//        $this->app->bind(TermsAndConditionsRepositoryInterface::class,TermsAndConditionsRepository::class);

        $this->app->bind(
            UpsellRepositoryInterface::class,
            UpsellRepository::class
        );
    }
}
