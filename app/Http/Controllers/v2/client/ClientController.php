<?php

namespace App\Http\Controllers\v2\client;

use App\Events\Emails\EmailEvent;
use App\Events\SendEmailEvent;
use App\GuestCommunication;
use App\Http\Controllers\Controller;
use App\Repositories\GenericEmailSMSWithContent\GenericEmailWithContent;
use App\Repositories\GenericEmailSMSWithContent\GenericSMSWithContent;
use App\Repositories\NotificationAlerts;
use App\User;
use App\UserAccount;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Image;
use Validator;

//use Spatie\Activitylog\Models\Activity;

class ClientController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth');
    }

    public function noti()
    {
        auth()->user()->notify(new GenericNotify());
        return;
    }

    public function allnotifications()
    {

        //auth()->user()->unreadNotifications->markAsRead();

        try {

            $msgs = [];

            $msgs = DB::table('guest_communications')
                ->leftJoin('booking_infos', 'guest_communications.booking_info_id', '=', 'booking_infos.id')
                ->where([
                    ['guest_communications.user_account_id', auth()->user()->user_account_id],
                    ['guest_communications.is_guest', 1],
                ])
                ->select(
                    'guest_communications.id',
                    'guest_communications.booking_info_id',
                    'guest_communications.message',
                    'guest_communications.created_at',
                    'guest_communications.user_account_id',
                    'booking_infos.pms_booking_id',
                    'booking_infos.guest_title',
                    'booking_infos.guest_name')
                ->orderBy('id', 'DESC')->paginate(6);

        } catch (\Exception $e) {
            //dd($e->getMessage());
        }

        //return json_encode(array('msgs'=> $msgs));
        return view('client.notifications.notifications', ['msgs' => $msgs]);
    }

    public function allNotificationsV2()
    {
        return view('v2.client.notifications.all_notifications');
    }

    public function get_all_notifications(Request $request)
    {

        $notifications_type_text = config("db_const.notifications");
        $unread_notifications = 0;

        $recordsPerPage = $request->filters['recordsPerPage'];
        $pageNumber = $request->filters['page'];
        $alerts = notificationsPagination();

        if (!empty($alerts)) {
            $unread_notifications = $alerts->where(function ($query) {
                $query->where('action_performed', null)
                    ->orWhere('action_performed', 0);
            })->count();
        }

        $all_notifications = notificationsPagination()
            ->orderBy('created_at', 'DESC')
            ->paginate($recordsPerPage, $columns = ['*'], 'page', $pageNumber);

        $data = ['all_notifications' => $all_notifications, 'unread_notifications' => $unread_notifications, 'notifications_type_text' => $notifications_type_text, 'is_manager' => auth()->user()->hasRole(User::ROLE_MANAGER)];
        return $this->apiSuccessResponse(200, $data, 'success');
    }

    public function notificationRead($id, $st)
    {

        $this->userHasAnyoneRole([User::ROLE_MANAGER, User::ROLE_ADMINISTRATOR]);

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $alert = GuestCommunication::where('user_account_id', $user_account_id)
            ->where('id', $id)
            ->first();

        $alert->action_performed_by = ($st == 1 ? $user_id : '');
        $alert->action_performed = ($st == 1 ? 1 : '');
        $alert->message_read_by_user = $st;

        if ($alert->save()) {
            return $this->apiSuccessResponse(200, $user_id, 'success');
        } else {
            return false;
        }
    }

    public function markAllAsRead(Request $request)
    {

        $this->userHasAnyoneRole([User::ROLE_MANAGER, User::ROLE_ADMINISTRATOR]);

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $alerts = GuestCommunication::where('user_account_id', $user_account_id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('alert_type', 'chat')
                        ->where('is_guest', '!=', 0);
                })
                    ->orWhere('alert_type', '!=', 'chat')
                    ->where('action_performed', null)
                    ->orWhere('action_performed', 0);
            })
            ->update(['action_performed' => 1, 'message_read_by_user' => 1, 'action_performed_by' => $user_id]);
        return $this->apiSuccessResponse(200, $user_id, 'success');
    }

    public function notificationSoftDelete($id)
    {

        $this->userHasAnyoneRole([User::ROLE_MANAGER, User::ROLE_ADMINISTRATOR]);

        $notification = GuestCommunication::find($id);
        $notification->delete();
        return $this->apiSuccessResponse(200, 'deleted', 'success');
    }

    public function profile()
    {
        $obj = auth()->user();
        $company = UserAccount::find($obj->user_account_id);
//        $user_log = Activity::all();
        $data = array(
            'client' => $obj,
            'company' => $company,
//            'user_log'=> $user_log,
            'user_log' => []
        );
        return view('client.company_profile.profile')->with('data', $data);
    }


    public function update(Request $request, $id)
    {

        Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'phone' => 'required|max:25',

        ])->validate();

        $clientProfile = User::find($id);

        if (!empty($request->get('name'))) {
            $clientProfile->name = $request->get('name');

        }

        if (!empty($request->get('phone'))) {
            $clientProfile->phone = $request->get('phone');
        }
        if (!empty($request->get('city'))) {
            $clientProfile->city = $request->get('city');
        }
        if (!empty($request->get('state'))) {
            $clientProfile->state = $request->get('state');
        }
        if (!empty($request->get('address'))) {
            $clientProfile->address = $request->get('address');
        }
        if (!empty($request->get('country'))) {
            $clientProfile->country = $request->get('country');
        }
        if (!empty($request->get('password'))) {
            $clientProfile->password = Hash::make($request->get('password'));
        }
        if (!empty($request->get('address2'))) {
            $clientProfile->address2 = $request->get('address2');
        }
        if (!empty($request->get('website'))) {
            $clientProfile->website = $request->get('website');
        }


        if ($clientProfile->save()) {
            $res = array('done' => 1,);
        } else {
            $res = array('done' => 0,);
        }
        return json_encode($res);
    }

    /**
     * @param Request $request
     * @param $id
     * @return false|string
     */
    public function changepassword(Request $request, $id)
    {

        Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ])->validate();

        $clientPass = User::find($id);
        $clientPass->password = Hash::make($request->get('password'));

        if ($clientPass->save() == true) {
            $res = array('done' => 1,);
        } else {
            $res = array('done' => 0,);
        }
        return json_encode($res);
    }

    /**
     * @param Request $request
     * @param $id
     * @return false|string
     */
    public function companyprofileupdate(Request $request, $id)
    {

        Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'companyEmail' => 'email',

        ])->validate();

        $companyProfile = UserAccount::find($id);
        $companyProfile->name = $request->get('name');
        $companyProfile->email = $request->get('companyEmail');
        $companyProfile->contact_number = $request->get('phone');
        $companyProfile->city = $request->get('city');
        $companyProfile->state = $request->get('state');
        $companyProfile->address = $request->get('address');
        $companyProfile->country = $request->get('country');

        if ($companyProfile->save() == true) {
            $res = array('done' => 1,);
        } else {
            $res = array('done' => 0,);
        }
        return json_encode($res);

    }

    public function companyStatus(Request $request)
    {
//        dd($request->all());
        Validator::make($request->all(), [
            'status' => 'required'
        ])->validate();

        $status = $request->status;
        $companyStatus = auth()->user()->user_account;
        $stepsCompleted = getUserPMSStepsCompletedStatus(auth()->user()->user_account_id);
        $setup_complete = ($stepsCompleted['step1'] && $stepsCompleted['step5']);

        if ($companyStatus->status == config('db_const.user_account.status.suspendedbyadmin.value')) {
            return $this->errorResponse('Your account was suspended by Admin, please contact support.', 422);
        } else if ((empty($companyStatus->integration_completed_on)
                || $companyStatus->status == config('db_const.user_account.status.pending.value')) && !$setup_complete) {
            $template = 'Please complete <a style="font-size: 16px;" href="/client/v2/pmsintegration">Account Setup</a> first.';
            return $this->errorResponse($template, 422);
        } else {
            if ($status) {
                $companyStatus->integration_completed_on = $companyStatus->integration_completed_on ?: now()->toDateTimeString();
                $companyStatus->last_booking_sync = Carbon::now()->addDays(1);
                $companyStatus->status = 1;
                $companyStatus->last_properties_sync_exception = null;
                $companyStatus->count_unauthorized_property_sync = 0;
                $return = $this->successResponse('Account Activated Successfully.', 200);
            } else {
                $companyStatus->status = 2;
                $return = $this->successResponse('Account de-activated Successfully.', 422);
            }
            $saved = $companyStatus->save();
            if ($saved) {
                event(new EmailEvent(config('db_const.emails.heads.ca_account_status_changed.type'), auth()->user()->user_account_id));
            }
            return $return;
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return false|string
     */


    public function latestReadedMsgs()
    {
        try {

            $msgs = [];

            $msgs = DB::table('guest_communications')
                ->leftJoin('booking_infos', 'guest_communications.booking_info_id', '=', 'booking_infos.id')
                ->where([
                    ['guest_communications.user_account_id', auth()->user()->user_account_id],
                    ['guest_communications.message_read_by_user', 1],
                    ['guest_communications.is_guest', 1],
                ])
                ->select(
                    'guest_communications.id',
                    'guest_communications.booking_info_id',
                    'guest_communications.created_at',
                    'guest_communications.user_account_id',
                    'booking_infos.pms_booking_id',
                    'booking_infos.guest_title',
                    'booking_infos.guest_name')
                ->orderBy('guest_communications.created_at', 'Asc')->take(10)->get()->toArray();

        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return json_encode(array('msgs' => $msgs));
    }


    public function communicationNotifyAlerts(Request $request)
    {
        $current_showing_notification_count = 0;
        $total_available_notifications = 0;
        $total_unread_notifications = 0;
        $status = false;
        $notifications = ['notifications_to_send' => ''];

        $reqId = $request->get('notification_shown', null);

        if (!empty($reqId) && (is_int($reqId) || is_numeric($reqId))) {

            $reqId = filter_var($reqId, FILTER_VALIDATE_INT);

            try {

                //use common repo to create alert
                $notificationRepo = new NotificationAlerts(auth()->user()->id, auth()->user()->user_account_id);
                $notifications = $notificationRepo->getNotificationForClient($reqId);

                $status = true;

                if ($notifications['notifications_to_send'])
                    $current_showing_notification_count = $notifications['notifications_to_send']->count();

                if ($notifications['total_notifications'])
                    $total_available_notifications = $notifications['total_notifications'];

                if ($notifications['total_unread_notifications'])
                    $total_unread_notifications = $notifications['total_unread_notifications'];

            } catch (\Exception $e) {

                $status = false;

                Log::error($e->getMessage(), [
                    'File' => __FILE__,
                    'Function' => __FUNCTION__,
                    'Trace' => $e->getTraceAsString()]);
            }
        } else {
            $status = false;
        }

        $data = (array('status' => $status,
            'notifications' => $notifications['notifications_to_send'],
            'total_available_notifications' => $total_available_notifications,
            'total_unread_notifications' => $total_unread_notifications,
            'current_showing_notification_count' => $current_showing_notification_count));

        return $this->apiSuccessResponse(200, $data);
    }

    public function alertActionPerformed(Request $request)
    {
        $current_showing_notification_count = 0;
        $total_available_notifications = 0;
        $total_unread_notifications = 0;
        $status = false;
        $notifications = [];

        try {

            //use common repo to mark alert as read
            $notificationRepo = new NotificationAlerts(auth()->user()->id, auth()->user()->user_account_id);
            $notifications = $notificationRepo->markPerformed($request->get('alert_id'), $request->get('notification_shown'));

            $status = true;
            if ($notifications['notifications_to_send'])
                $current_showing_notification_count = $notifications['notifications_to_send']->count();

            if ($notifications['total_notifications'])
                $total_available_notifications = $notifications['total_notifications'];

            if ($notifications['total_unread_notifications'])
                $total_unread_notifications = $notifications['total_unread_notifications'];

        } catch (\Exception $e) {
            $status = false;
        }

        $data = array(
            'status' => $status,
            'notifications' => $notifications['notifications_to_send'],
            'total_available_notifications' => $total_available_notifications,
            'total_unread_notifications' => $total_unread_notifications,
            'current_showing_notification_count' => $current_showing_notification_count);

        return $this->apiSuccessResponse(200, $data);
    }


    public function communicationNotifyAlertsReaded(Request $request)
    {
        GuestCommunication::where([['user_account_id', auth()->user()->user_account_id], ['id', '>=', $request->id]])->update(['message_read_by_user' => 1]);
        return \Response::json(['Read' => true], 200);
    }


    public function companylogo(Request $request, $id)
    {
        $this->isSuperClient(); //to Check Is Login User is Supe Client
        $company = UserAccount::find($id);

        $this->validate($request, ['company_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',]);
        if ($request->hasFile('company_img')) {
            $image = $request->file('company_img');
            $fileNameToStore = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/uploads/companylogos');
            $img = Image::make($image->getRealPath());
            $img->resize(1080, 1080, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileNameToStore);
            if ($company->company_logo != 'no_image.png' && file_exists('storage/uploads/companylogos/' . $company->company_logo)) {
                unlink('storage/uploads/companylogos/' . $company->company_logo);
            }
        } else {
            $fileNameToStore = 'no_image.png';
        }

        $company->company_logo = $fileNameToStore;

        if ($company->save() == true) {
            $res = array('done' => $fileNameToStore,);
        } else {
            $res = array('done' => 0,);
        }
        return json_encode($res);

    }

    public function userimage(Request $request, $id)
    {
        $user = User::find($id);

        $this->validate($request, ['user_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',]);
        if ($request->hasFile('user_img')) {
            $image = $request->file('user_img');
            $fileNameToStore = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/uploads/user_images');
            $img = Image::make($image->getRealPath());
            $img->resize(1080, 1080, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileNameToStore);
            if ($user->user_image != 'no_image.png' && file_exists('storage/uploads/user_images/' . $user->user_image)) {
                unlink('storage/uploads/user_images/' . $user->user_image);
            }
        } else {
            $fileNameToStore = 'no_image.png';
        }

        $user->user_image = $fileNameToStore;

        if ($user->save() == true) {
            $res = array('done' => $fileNameToStore,);
        } else {
            $res = array('done' => 0,);
        }
        return json_encode($res);

    }

    public function user_profile()
    {
        $user = auth()->user();
        return response()->json([
            'u_id' => $user->id,
            'user' => $user,
            'c_id' => $user->user_account_id,
            'company' => $user->user_account,
        ]);
    }

    public function user_update(Request $request, $id, $c_id)
    {
        $memberProfile = User::find($id);
        if (auth()->user()->can('full client')) {
            Validator::make($request->all(), [
                'u_name' => 'required|string|max:95',
                'u_phone' => 'required|string|max:25',
                'u_password' => 'string|min:6',
                'u_address' => 'required|max:95',
                'c_name' => 'required|string|max:95',
                'c_phone' => 'required|string|max:25',
                'c_address' => 'required|max:95',
            ])->validate();
            $companyInfo = UserAccount::find($c_id);
        } else {
            Validator::make($request->all(), [
                'u_name' => 'required|string|max:95',
                'u_phone' => 'required|string|max:25',
                'u_password' => 'string|min:6',
                'u_address' => 'required|max:95',
            ])->validate();
        }

        $memberProfile->name = $request->get('u_name');
        $memberProfile->phone = $request->get('u_phone');
        $memberProfile->address = $request->get('u_address');
        if (!empty($request->get('u_password')) && $request->get('u_password') != '******') {
            $memberProfile->password = Hash::make($request->get('u_password'));
        }

        if (auth()->user()->can('full client')) {
            $companyInfo->name = $request->get('c_name');
            $companyInfo->contact_number = $request->get('c_phone');
            $companyInfo->address = $request->get('c_address');
            $companyInfo->save();
        }

        if ($memberProfile->save() == true) {
            $res = array('done' => 1,);
        } else {
            $res = array('done' => 0,);
        }
        return json_encode($res);

    }

}

