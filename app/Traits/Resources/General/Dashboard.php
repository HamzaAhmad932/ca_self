<?php


namespace App\Traits\Resources\General;


use Illuminate\Support\Facades\Log;
use App\Http\Resources\BA\Booking\BookingListResource;
use App\TransactionInit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait Dashboard
{

    public function lineGraphDaily(){
        $user = $user = auth()->user();
        $previous_month = Carbon::now()->subDays(15);

        $sale = TransactionInit::where('user_account_id', $user->user_account_id)
                    ->whereBetween('updated_at', [
                        date($previous_month->toDateString()),
                        date(Carbon::today()->addDay(1)->toDateString())
                    ])
                    ->whereIn('type', ['C', 'M', 'CS'])
                    ->where('payment_status', '1')
                    ->select(DB::raw("CEIL(sum(price)) as price"), DB::raw('date(updated_at) as up_dated_at'), DB::raw("DATE_FORMAT(date(updated_at), '%e %b') as label"))
                    ->groupBy(DB::raw('2, 3'))
                    ->orderBy(DB::raw('2, 3'))
                    ->get();

        return $sale;
    }

    public function getTodayTomorrowCheckin($bookings){
        $dated = new \stdClass();
        $dated->today = [];
        $dated->tomorrow = [];
        foreach($bookings as $booking){
            if ($booking->check_in_date >= Carbon::today() && $booking->check_in_date < Carbon::tomorrow()) {
                array_push($dated->today, new BookingListResource($booking));
            }
            if($booking->check_in_date >= Carbon::tomorrow() && $booking->check_in_date < Carbon::tomorrow()->addDay(1)){
                array_push($dated->tomorrow, new BookingListResource($booking));
            }
        }
        return $dated;
    }

    public function pieGraphBookingSource($all_booking_sources, $bookings_info){

        $pie = new \stdClass();
        $pie->labels = [];
        $pie->values = [];
        $sub_15_days = Carbon::now()->subDays(15);

        foreach ($all_booking_sources as $bs){
            $booking_count = $bookings_info->where('channel_code', $bs->booking_source_form->channel_code)->count();
            if($booking_count > 0){
                array_push($pie->labels , $bs->booking_source_form->name);
                array_push($pie->values, $booking_count);
            }
        }

        return $pie;
    }

    public function totalSale(){
        $user = $user = auth()->user();
        $previous_month = Carbon::now()->subMonth();
        $query = "select p.currency_code, format(sum(t.price), 2) as price from transaction_inits t join booking_infos b on(b.id = t.booking_info_id) left join property_infos p on(b.property_id = p.pms_property_id) where t.user_account_id =".$user->id." and t.updated_at between '".$previous_month->toDateTimeString()."' and '".Carbon::today()->addDay(1)->toDateTimeString()."' group by p.currency_code;";
        $sales = DB::select($query);
        foreach ($sales as $k => $sale){
            $sales[$k]->currency_code = get_currency_symbol($sale->currency_code);
        }
        return $sales;
    }

}
