<?php


namespace App\Repositories\Dashboard;

use App\BookingInfo;
use App\BookingSourceForm;
use App\UserAccount;
use DB;
use Carbon\Carbon;
use App\Repositories\Bookings\Bookings;
use Illuminate\Support\Facades\Log;

class DashboardRepository implements DashboardInterface
{

    public function __construct()
    {

    }

    public function getUpcomingArrivals()
    {
        try {
            $user = auth()->user();

            return BookingInfo::with(
                [
                    "transaction_init_charged",
                    "credit_card_authorization_sd_cc",
                    "credit_card_authorization_sd_cc.ccinfo",
                    "guest_images",
                    "cc_Infos",
                    'upsellOrders',
                    'upsellOrders.upsellOrderDetails',
                    'upsellOrders.upsellOrderDetails.upsell',
                    'upsellOrders.upsellOrderDetails.upsell.upsellType',
                ]
            )->where('user_account_id', $user->user_account_id)
                ->where('check_in_date','>=',Carbon::today())
                ->where('pms_booking_status', '!=', 0)
                ->orderBy('check_in_date','asc')
                ->take(20)->latest()->get();

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Stack' => $e->getTraceAsString()]);
        }
        return null;

    }

    public function getDashboardAnalyticsData(){

        try{

        $user = auth()->user();
        $sub_15_days = Carbon::now()->subDays(15);
        //take any property to get time_zone of useraccount
        $property = $user->user_account->activeProperties->first();
        if(!empty($property)){
            $today = Carbon::today()->timezone($property->time_zone)->toDateString();
        }else{
            $today = Carbon::today()->addDay(1)->toDateString();
        }

        return UserAccount::where('id', $user->user_account_id)
                ->with([
                'bookings_info'=> function($query) use($sub_15_days, $today){
                    $query->whereBetween('booking_time', [
                        $sub_15_days->toDateString(),
                        $today
                    ]);
                },
                'user_bookings_source' => function ($query) {
                    $query->where('status', 1);
                }])
                ->withCount(['properties_info','properties_info as active_properties'=>function($query){
                    $query->where('status',1);
                }])
                ->withCount('properties_info')
                ->first();

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Stack' => $e->getTraceAsString()]);
        }
        return null;
    }
}
