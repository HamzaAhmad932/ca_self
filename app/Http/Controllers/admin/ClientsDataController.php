<?php

namespace App\Http\Controllers\admin;


use App\Audit;
use App\BookingInfo;
use App\GlobalNotification;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Booking\AdminBookingCollection;
use App\Http\Resources\Admin\Booking\AdminBookingDetailResource;
use App\PaymentGatewayForm;
use App\PropertyInfo;
use App\Repositories\Admin\Bookings\AdminBookingRepositoryInterface;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Services\CapabilityService;
use App\User;
use App\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Notification;
use Yajra\DataTables\DataTables;

class ClientsDataController extends Controller
{
    /**
     * @var AdminBookingRepository $booking
     */
    public $booking;

    public function __construct(AdminBookingRepositoryInterface $adminBookingRepository)
    {
        $this->middleware('auth', ['except' => ['cancelBdcBooking', 'cancelBdcBookingDetailPage']]);
        $this->booking = $adminBookingRepository;
    }

    public function showBookings($user_account_id = 0)
    {
        return view('admin.clients.bookings.bookings')->with('user_account_id', $user_account_id);
    }

    public function getBookingList(Request $request)
    {
        $raw_bookings = $this->booking->get_admin_bookings_list_filtered($request->filter);
        return new AdminBookingCollection($raw_bookings);
    }

    public function getBookingDetails($booking_info_id)
    {
        /**
         * @var BookingInfo $raw_booking
         */
        $raw_booking = $this->booking->get_booking_detail($booking_info_id);
        $raw_booking->other_type_booking = CapabilityService::isAnyPaymentOrSecuritySupported($raw_booking);
        return new AdminBookingDetailResource($raw_booking);
    }

    public function bookings_data()
    {

        // $data = app()->make('Bookings', ['user_account_id' => $user_account_id]);
        // return  Datatables::of($data->booking_data())->make(true);
        set_sql_mode('ONLY_FULL_GROUP_BY');
        $bookings = BookingInfo::with(['credit_card_authorizations' => function ($query) {
            $query->select('booking_info_id', 'type', 'hold_amount', 'status');
        }])
            ->with(['success_charge_transaction_init' => function ($query) {
                $query->select('booking_info_id', 'payment_status');
            }])
            ->with(['schedule_charge_transaction_init' => function ($query) {
                $query->select('booking_info_id', 'payment_status');
            }])
            ->with(['failed_charge_transaction_init' => function ($query) {
                $query->select('booking_info_id', 'payment_status');
            }])
            //->with( ['booking_source'                   =>  function ($query) { $query->select('channel_code', 'name'); }])
            ->with(['guest_images' => function ($query) {
                $query->select('booking_id', 'status');
            }])
            ->with(['cc_Infos' => function ($query) {
                $query->select('booking_info_id', 'customer_object')->orderBy('id', 'DESC');
            }])
            ->select('id', 'is_vc', 'guest_country', 'pms_booking_status', 'total_amount', 'guest_currency_code', 'pms_booking_id', 'guest_name', 'property_id', 'channel_code', 'pms_id', 'user_account_id', 'guest_email', 'booking_time', 'check_in_date', 'check_out_date')
            ->groupBy('pms_booking_id')->get();

        return Datatables::of($bookings)->make(true);
    }

    public function bookingDetails($booking_info_id)
    {
        return view('admin.clients.bookings.booking_details', ['booking_info_id' => $booking_info_id]);
    }

    public function booking_short_desc(Request $request)
    {

        $data = app()->make('Bookings', ['user_account_id' => $request->user_account_id]);
        return $data->bookings_short_description($request->booking_id);
    }


    public function account_users($id)
    {

        return view('admin.clients.users.account_users')->with('id', $id);
    }

    public function account_users_data($id)
    {
        $super_user = User::where('user_account_id', $id)->get();
        return Datatables::of($super_user)->make(true);
    }

    public function account_user_profile($user_id, $u_account_id)
    {

        $user_account = UserAccount::find($u_account_id);

        $data = User::where('id', $user_id)->where('user_account_id', $u_account_id)->first();

        return view('admin.clients.users.account_user_profile', ['data' => $data]);
    }

    public function team_member_audit_logs($id)
    {
        $user = User::find($id);
        $audits = $user->audits;

        $final_audits = [];

        $i = 0;
        foreach ($audits as $audit) {
            foreach ($audit->getModified() as $field => $value) {
                $final_audits[$i]['field'] = $field;
                $final_audits[$i]['event'] = ucfirst($audit->event);
                $final_audits[$i]['created_at'] = isset($audit->created_at) ? $audit->created_at->toDateTimeString() : '-';

                //for old value column arrange data
                if (isset($value['old']) && !is_array($value['old']))
                    $final_audits[$i]['old_value'] = $value['old'];
                elseif (isset($value['old']) && is_array($value['old']))
                    $final_audits[$i]['old_value'] = isset($value['old']['date']) ? $value['old']['date'] : '-';
                else
                    $final_audits[$i]['old_value'] = '-';

                //for new value column arrange data
                if (isset($value['new']) && !is_array($value['new']))
                    $final_audits[$i]['new_value'] = $value['new'];
                elseif (isset($value['new']) && is_array($value['new']))
                    $final_audits[$i]['new_value'] = isset($value['new']['date']) ? $value['new']['date'] : '-';
                else
                    $final_audits[$i]['new_value'] = '-';

                //increment counter
                $i++;

            }
        }

        return Datatables::of($final_audits)->make(true);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $user_account = UserAccount::find($id);
        $user_id = auth()->user()->id;
        $global = GlobalNotification::find(1);
        $message = $global->message;
        session()->flash('alerts', ['message' => $message, 'cls' => 'primary']);

        return view('admin.pages.client.client_dashboard', ['user_account' => $user_account]);
    }

    public function showclientproperty($id)
    {

        $user_id = auth()->user()->id;
        $user_account = UserAccount::find($id);
        return view('admin.pages.client.property.properties', ['user_account' => $user_account]);
    }

    public function clientpropertypagination($id)
    {

        $user_id = auth()->user()->id;
        $user_account = UserAccount::find($id)->paginate(2);

        return json_encode($user_account);
    }

    public function accountstatus($id, $st)
    {

        $companystatus = UserAccount::find($id);
        $companystatus->status = $st;
        $companystatus->save();
        $res = array('status' => $st,);
        if ($st == config('db_const.user.status.active.value')) {
            $sts = config('db_const.user.status.active.label');
        } elseif ($st == config('db_const.user.status.deactive.value')) {
            $sts = config('db_const.user.status.deactive.label');
        }

        return json_encode($res);
    }

    public function clientpropertystatus($id, $st)
    {

        $propertystatus = PropertyInfo::find($id);
        $propertystatus->status = $st;
        $propertystatus->save();
        $res = array('status' => $st,);
        if ($st == config('db_const.user.status.active.value')) {
            $sts = config('db_const.user.status.active.label');
        } elseif ($st == config('db_const.user.status.deactive.value')) {
            $sts = config('db_const.user.status.deactive.label');
        }

        return json_encode($res);
    }

    public function bookingPropertyDetail($booking_info_id)
    {
        $booking = BookingInfo::find($booking_info_id);
        $property = '';
        $property_history = '';
        $user_account = '';
        if (!empty($booking)) {

            $user_account = $booking->user_account;
            $property = $booking->property_info;

            if (!empty($property)) {

                $property_history = Audit::where('auditable_type', 'App\PropertyInfo')
                    ->where('auditable_id', $property->id)
                    ->where('created_at', '>=', Carbon::parse($booking->created_at)->toDateTimeString())
                    ->where('created_at', '<=', Carbon::parse($booking->check_out_date)->toDateTimeString())
                    ->get();

            }

        }

        return $this->apiSuccessResponse(200, ['property' => $property, 'property_history' => $property_history, 'user_account' => $user_account], 'success');
    }

    public function bookingPaymentGatewayDetail($booking_info_id)
    {
        $booking_payment_gateway = "";
        $booking = BookingInfo::with('user_account')->where('id', $booking_info_id)->first();

        if (!empty($booking)) {

            $payment_gateways = new PaymentGateways();
            $booking_payment_gateway = $payment_gateways->getPropertyPaymentGatewayFromBooking($booking);

            if (!empty($booking_payment_gateway)) {

                $payment_gateway_form = PaymentGatewayForm::where('id', $booking_payment_gateway->payment_gateway_form_id)->select('name')->first();

                $booking_payment_gateway['payment_gateway_name'] = '';
                $booking_payment_gateway['user_account_name'] = $booking->user_account->name;

                if (!empty($payment_gateway_form)) {
                    $booking_payment_gateway['payment_gateway_name'] = $payment_gateway_form->name;
                }

                $booking_payment_gateway['property_name'] = '';
                $booking_payment_gateway['pms_property_id'] = '';

                if ($booking_payment_gateway->property_info_id != 0) {

                    $booking_property = PropertyInfo::where('id', $booking_payment_gateway->property_info_id)->first();

                    if (!empty($booking_property)) {

                        $payment_gateway['property_name'] = $booking_property->name;
                        $payment_gateway['pms_property_id'] = $booking_property->pms_property_id;

                    }

                }

                $payment_gateway_detail = Audit::where('auditable_type', 'App\UserPaymentGateway')
                    ->where('auditable_id', $booking_payment_gateway->id)
                    ->where('created_at', '>=', Carbon::parse($booking->created_at)->toDateTimeString())
                    ->where('created_at', '<=', Carbon::parse($booking->check_out_date)->toDateTimeString())
                    ->get();
            }

        }

        return $this->apiSuccessResponse(200, ['payment_gateway' => $booking_payment_gateway, 'payment_gateway_detail' => $payment_gateway_detail], 'success');
    }

    public function bookingCCInfoDetail($booking_info_id)
    {
        $booking = BookingInfo::with(['cc_infos', 'user_account'])->where('id', $booking_info_id)->first();
        $booking_cc_info = '';
        $booking_cc_info_detail = '';
        if (!empty($booking)) {
            $booking_cc_info = $booking->cc_Infos;
            $cc_cards_id = $booking->cc_infos->pluck('id');

            if (!empty($cc_cards_id)) {
                $booking_cc_info_detail = Audit::where('auditable_type', 'App\CreditCardInfo')
                    ->whereIn('auditable_id', $cc_cards_id)
                    ->where('created_at', '>=', Carbon::parse($booking->created_at)->toDateTimeString())
                    ->where('created_at', '<=', Carbon::parse($booking->check_out_date)->toDateTimeString())
                    ->get();
            }

        }

        return $this->apiSuccessResponse(200, ['booking_cc_info' => $booking_cc_info, 'booking_cc_info_detail' => $booking_cc_info_detail, 'booking_cc_info_user_account' => $booking->user_account], 'success');
    }


}
