<?php

namespace App\Http\Controllers\BA\Client;

use App\Http\Resources\BA\Booking\BookingListCollection;
use App\Http\Resources\BA\Dashboard\DashboardResource;
use App\Repositories\Dashboard\DashboardInterface;
use App\User;
use App\UserAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    protected $dashboard;

    public function __construct(DashboardInterface $dashboard){

        $this->middleware('auth');
        $this->dashboard = $dashboard;
    }

    /**
     * @param Request $request
     * @return BookingListCollection
     */
    public function getDashboardUpcomingArrivals(Request $request){

        $raw_record = $this->dashboard->getUpcomingArrivals();
        return new BookingListCollection($raw_record);
    }

    /**
     * @param Request $request
     * @return DashboardResource
     */
    public function getDashboardAnalyticsData(Request $request){
        $raw_record = $this->dashboard->getDashboardAnalyticsData();
        return new DashboardResource($raw_record);
    }
}
