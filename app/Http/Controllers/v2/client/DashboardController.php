<?php

namespace App\Http\Controllers\v2\client;

use App\Http\Resources\BA\Booking\BookingListCollection;
use App\Http\Resources\BA\Dashboard\DashboardCollection;
use App\Http\Resources\BA\Dashboard\DashboardResource;
use App\Repositories\Dashboard\DashboardInterface;
use DB;
use App\Repositories\Bookings\Bookings;
use App\User;
use App\Activity;
use App\UserAccount;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use App\Notifications\DatabaseNotification;

class DashboardController extends Controller
{
    protected $dashboard;

    public function __construct(DashboardInterface $dashboard){

        $this->middleware('auth');
        $this->dashboard = $dashboard;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->user_account_id;
        $user_account = UserAccount::find($id);

        $attempt_tour = auth()->user()->attempt_tour;
        if($attempt_tour != '1'){
            $user = User::find(auth()->user()->id);
            $user->attempt_tour = 1;
            $user->save();
        }

        return view('v2.client.dashboard.dashboard',['user_account' => $user_account, 'attempt_tour'=>$attempt_tour]);
    }

    public function getDashboardUpcomingArrivals(Request $request){

        $raw_record = $this->dashboard->getUpcomingArrivals();
        return new BookingListCollection($raw_record);
    }

    public function getDashboardAnalyticsData(Request $request){

        $raw_record = $this->dashboard->getDashboardAnalyticsData();
//        dd($raw_record->toArray());
        return new DashboardResource($raw_record);
    }
}
