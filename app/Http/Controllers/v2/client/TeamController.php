<?php

namespace App\Http\Controllers\v2\client;

use App\Events\Emails\EmailEvent;
use App\RoleAndPermissions;
use App\Rules\PhoneRegxForProfileUpdate;
use App\User;
use App\Audit;
use mysql_xdevapi\Collection;
use phpDocumentor\Reflection\Types\Null_;
use Validator;
use App\UserAccount;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use App\Notifications\MemberStatusNotification;
use App\Repositories\Settings\PaymentTypeMeta;
use App\CreditCardInfo;
use App\CreditCardAuthorization;
use App\TransactionInit;
use DB;
use Image;
use Yajra\DataTables\DataTables;

class TeamController extends Controller
{
    public function __construct()
    {  
        $this->middleware('auth');
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$id = auth()->user()->id;
        $this->hasAccess(User::ROLE_ADMINISTRATOR);
        return view('v2.client.team.manageteam');
    }

    public function team_list(Request $request){
        $this->hasAccess(User::ROLE_ADMINISTRATOR);
        $id = auth()->user()->id;

        $filters = $request->filters;
        $filters['page'] = isset($request->page) ? $request->page : 1;
        $filters['constraints'][] = ['parent_user_id', '=', $id];
        return  $this->apiSuccessResponse(200, get_collection_by_applying_filters($filters, User::class), 'success');
    }

    public function GetAllRolesAndPermissions(){

        $all_Roles = Role::where('guard_name', 'client')->where('name', '!=', 'Administrator')->get();

        $allPermissionObj = Permission::where('guard_name', '=', 'client')
            ->where('name', 'not like', '%delete%')
            ->whereNotIn('name', ['chargeBooking', 'editUser', 'viewUser', 'full', 'full client'])
            ->orderBy('name', 'ASC')->get();

        $allPermission = $allPermissionObj->whereIn('name', RoleAndPermissions::$designV2ClientPermissions);

//        $transactionPermissions = $allPermissionObj->whereIn('name', ['charges', 'authorize', 'capture', 'refund']);
        $transactionPermissions = null;

        return response()->json([
            'all_Roles'=>$all_Roles,
            'all_permission'=>$allPermission,
            'transaction_permissions'=>$transactionPermissions,
        ]);

    }

    public function create(Request $request)
    {

        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Super Client
        if(Gate::check('full') || Gate::check('full client')){
            Validator::make($request->all(), [
                'fname' => 'required|string|max:255',
                'lname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
                'slctr' => 'required',
            ], [
                'fname.required' => 'The First Name field is required.',
                'lname.required' => 'The Last Name field is required.',
                'slctr.required' => 'Must assign atleast one Role to new User.'
            ])->validate();

            $parent_id = auth()->user()->id;
            $company_id = auth()->user()->user_account_id;
            $pass=$request->fname.$request->lname.$company_id.$parent_id;
            $password=str_replace(' ', '', $pass);
            $user = User::create([
                'name' => $request->fname.' '.$request->lname,
                'email' => $request->email,
                'password' => Hash::make($password),
                'user_account_id' => $company_id,
                'parent_user_id' => $parent_id,
            ]);

            if ($user){
                $user->assignRole($request->slctr);
                /*if(in_array(User::ROLE_MANAGER, $request->slctr)){
                    $user->givePermissionTo('full client');
                }*/


                event(new EmailEvent(config('db_const.emails.heads.team_member_invite.type'), $user->id));

                //send email to super client
                event(new EmailEvent(config('db_const.emails.heads.team_member_added_inform_client.type'), $user->id ));
                $res = array('status' => 200,'done' => 1, 'name' => $user->name );

            }else {
                $res = array('done' => 0, );
            }
            return json_encode($res);

        }else{
            return json_encode(['done'=> '0']);
        }
    }

    //user log For js Datatable
    public function user_log(Request $request){

        $dont_show_log_for = ['password', 'remember_token'];

        $user = User::find($request->user_id);
        $audits = $user->audits;

        $i = 0;
        $final_audits = [];

        if(!empty($audits) && sizeof($audits)>0 && $audits != null){
            foreach($audits as $audit){
                foreach($audit->getModified() as $field => $value) 
                {
                    if(in_array($field, $dont_show_log_for))
                        continue;
                    
                    $final_audits[$i]['field'] = $field;
                    $final_audits[$i]['event'] = ucfirst($audit->event);
                    $final_audits[$i]['created_at'] = isset($audit->created_at) ? $audit->created_at->toDateTimeString(): '-';
                    
                    //for old value column arrange data
                    if(isset($value['old']) && !is_array($value['old']) && !is_object($value['old']))
                      $final_audits[$i]['old_value'] = (strlen($value['old'])>0 && $value['old'][0] != "{") ? $value['old']:'This is a large object.'; //if its start with { then its a aobject 
                    elseif(isset($value['old']) && is_array($value['old']))
                      $final_audits[$i]['old_value'] = isset($value['old']['date']) ? $value['old']['date'] : '-';
                    elseif(isset($value['old']) && is_object($value['old']))
                      $final_audits[$i]['old_value'] = 'This is a large object.';
                    else
                      $final_audits[$i]['old_value'] = '-';
                    
                    //for new value column arrange data
                    if(isset($value['new']) && !is_array($value['new']) && !is_object($value['new']))
                      $final_audits[$i]['new_value'] = (strlen($value['new'])>0  && $value['new'][0] != "{") ? $value['new']:'This is a large object.'; //if its start with { then its a aobject 
                    elseif(isset($value['new']) && is_array($value['new']))
                      $final_audits[$i]['new_value'] = isset($value['new']['date']) ? $value['new']['date'] : '-';
                    elseif(isset($value['new']) && is_object($value['new']))
                      $final_audits[$i]['new_value'] = 'This is a large object.';
                    else
                      $final_audits[$i]['new_value'] = '-';

                    //increment counter 
                    $i++;
                }
                
            }
        }

        return $final_audits = array_reverse($final_audits);

        /*return Datatables::of($final_audits)
                ->addIndexColumn()
                ->make(true);*/
    }

    //user log For vue pagination
    public function userLog(Request $request){

        $this->hasAccess(User::ROLE_ADMINISTRATOR);

        $filters = $request->filters;
        $filters['page'] = isset($request->page) ? $request->page : $filters['page'];
        $filters['constraints'][] = ['auditable_id', '=', $request->user_id];
        return  $this->apiSuccessResponse(200, get_collection_by_applying_filters($filters, Audit::class), 'success');
    }

    public function resendInvitation($id){
        $this->hasAccess(User::ROLE_ADMINISTRATOR);
        $user = User::find($id);
        if($user->email_verified_at === null){

            //send email to client
            event(new EmailEvent(config('db_const.emails.heads.team_member_invite.type'), $user->id));

            $res = array('status' => 1);
        } else{
            $res = array('status' => 2);
        }

        return json_encode($res);
    }

    public function memberstatus($id, $st){

        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Super Client

        $memberStatus = User::withTrashed()->find($id);
        $memberStatus->status = $st;
        $res = array('status' => $st);

        if($st == config('db_const.user.status.active.value')){
            $sts = config('db_const.user.status.active.label');
            //$memberStatus->deleted_at = null;
        }elseif ($st == config('db_const.user.status.deactive.value')){
            $sts = config('db_const.user.status.deactive.label');
            //$memberStatus->deleted_at = now()->toDateTimeString();
        }

        $memberStatus->save();
        $memberStatus->notify(new MemberStatusNotification($sts));

        return json_encode($res);

    }

    public function soft_delete($id){
        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Super Client
        $user = User::find( $id );
        $user->delete();
        return json_encode($user ? 1 : 0);
    }

    public function profile($id){
        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Supe Client
        $user = User::where('id',$id)->where('user_account_id',auth()->user()->user_account_id)->first();
        if(is_null($user)){
            return redirect()->back()->withErrors(['msg'=> 'Account is not activated yet!']);
        }
        $userPermissions = $user->permissions->pluck('name');
        $userRoles = $user->roles->pluck('name');

        $check_user_image = checkImageExists($user->user_image, $user->name, config('db_const.logos_directory.user.value'));

        return response()->json([
            'userPermissions'=>$userPermissions,
            'userRoles'=>$userRoles,
            'member'=> $user,
            'user_initial'=>$check_user_image['user_initial'],
            'user_image'=>$check_user_image['user_image'],
        ]);
    }

    public function update(Request $request, $id){

        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Super Client

        Validator::make($request->all(), [
            '_fname' => 'required|string|max:95',
            '_lname' => 'required|string|max:95',
            '_email' => 'required|string|email|max:255|unique:users,email,'.$id.',id,deleted_at,NULL',
            '_phone' => 'nullable|min:12|max:25',
            //'_password' => 'sometimes|nullable|string|min:6',
            '_slctr' => 'required',

        ], [
            '_fname.required' => 'The First Name is required.',
            '_lname.required' => 'The Last Name is required.',
            '_phone.min' => 'Please type correct Phone Number.',
            '_phone.max' => 'Please type correct Phone Number.',
            '_slctr.required' => 'Must assign atleast one Role to new User.'
        ])->validate();

        $memberProfile = User::find($id);

        $email_update = false;
        if ($memberProfile->email != $request->get('_email')) {
            $email_update = true;
        }

        $memberProfile->name=$request->get('_fname').' '.$request->get('_lname');
        $memberProfile->phone=$request->get('_phone');
        $memberProfile->email=$request->get('_email');
        if(!empty($request->get('_password')) && $request->get('_password')!='******'){
            $memberProfile->password=Hash::make($request->get('_password'));
        }
        if($memberProfile->save() == true){
            if ($email_update) {
                event(new EmailEvent(config('db_const.emails.heads.team_member_invite.type'), $id));
            }
            $res = array('done' => 1, );
        }else{
            $res = array('done' => 0, );
        }
        return json_encode($res);
    }

    public function user_profile(){
        $user = auth()->user();
        $check_user_image = checkImageExists($user->user_image, $user->name, config('db_const.logos_directory.user.value'));
        $check_company_image = checkImageExists( $user->user_account->company_logo, $user->user_account->name, config('db_const.logos_directory.company.value') );

        return response()->json([
            'u_id'=>$user->id,
            'user_initial'=>$check_user_image['user_initial'],
            'user_image'=>$check_user_image['user_image'],
            'user'=> $user,
            'c_id'=>$user->user_account_id,
            'company_initial'=>$check_company_image['company_initial'],
            'company_logo' => $check_company_image['company_image'],
            'company' => $user->user_account,
        ]);
    }

    public function user_update(Request $request, $id, $c_id)
    {

        $memberProfile = User::find($id);
        if(auth()->user()->can('full client')){
            Validator::make($request->all(), [
                'u_name' => 'required|string|max:95',
                'u_phone' => ['nullable', 'max:18', 'regex:/(^[+0-9 ]+$)+/', new PhoneRegxForProfileUpdate],
                'c_name' => 'required|string|max:95|regex:/(^([a-zA-Z0-9&\'_.’\- ]+)(\d+)?$)/u',
                'c_email' => 'nullable|email',
                'c_phone' => ['nullable', 'max:18', 'regex:/(^[+0-9 ]+$)+/', new PhoneRegxForProfileUpdate],
            ], [
                'u_name.required' => 'The User Name field is required.',
                'u_phone.max' => 'The Contact Number is invalid.',
                'u_phone.regex' => 'The format of Contact Number is invalid.',
                'c_name.required' => 'The Company Name field is required.',
                'c_name.string' => 'Only alpha-numeric characters allowed',
                'c_name.regex' => 'Only alpha-numeric characters allowed',
                'c_phone.required_if' => 'Please provide any one of the phone, Company or User.',
                'c_email.email' => 'The Company Email must be a valid email address.',
                'c_phone.max' => 'The Contact Number is invalid.',
                'c_phone.regex' => 'The format of Contact Number is invalid.',
        ])->validate();
            $companyInfo = UserAccount::find($c_id);
        }
        else{
            Validator::make($request->all(), [
                'u_name' => 'required|string|max:95',
                'u_phone' =>  ['nullable', 'max:18', 'regex:/(^[+0-9 ]+$)+/', new PhoneRegxForProfileUpdate],
            ], [
                'u_name.required' => 'The User Name field is required.',
                'u_phone.max' => 'The Contact Number is invalid.',
                'u_phone.regex' => 'The format of Contact Number is invalid.',
            ])->validate();
        }


        /*if(!empty($request->get('new_password'))){
            if(!Hash::check($request['old_password'], $memberProfile->password) || empty($request['old_password'])){
                return $this->apiSuccessResponse(422, $request['new_password'], 'The Old Password does not match.');
            }
            else{
                $memberProfile->password=Hash::make($request->get('new_password'));
            }
        }*/

        //else{
            $memberProfile->name=$request->get('u_name');
            $memberProfile->phone=$request->get('u_phone');
            if(!empty($request->get('u_address'))){$memberProfile->address=$request->get('u_address');};

            if(auth()->user()->can('full client')) {
                $companyInfo->name = $request->get('c_name');
                $companyInfo->contact_number = $request->get('c_phone');
                $companyInfo->email = $request->get('c_email');

                if(!empty($request->get('c_address'))){ $companyInfo->address = $request->get('c_address'); };
                $companyInfo->save();
            }

            if($memberProfile->save() == true){
                return $this->apiSuccessResponse(200, 'success', 'Profile updated successfully.');
            }
            else{
                return $this->apiSuccessResponse(404, 'error', 'Some error while updating profile.');
            }
        //}

    }


    public function ChangePassword(Request $request)
    {
        $user = auth()->user();

        Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required',
        ], [
            'old_password.required' => 'The Old Password must be required.',
            'new_password.required' => 'The New Password must be required.',
            'new_password.min' => 'The New Password must be at least 6 characters.',
            'new_password.confirmed' => 'The New Password & Confirm Password does not match.',
            'new_password_confirmation.required' => 'The Confirm Password must be required.',
        ])->validate();

        if(!Hash::check($request['old_password'], $user->password)){
            return $this->apiSuccessResponse(422, $request['new_password'], 'The Old Password does not match.');
        }
        else{
            $user->password=Hash::make($request->get('new_password'));

            if($user->save() == true){
                return $this->apiSuccessResponse(200, 'success', 'Password successfully updated.');
            }
            else{
                return $this->apiSuccessResponse(404, 'error', 'Some error while updating password.');
            }
        }
    }


    public function companylogo(Request $request, $id)
    {
        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Supe Client
        $company = UserAccount::find($id);

        $this->validate($request, ['company_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',]);
        if ($request->hasFile('company_img')) {
            $image = $request->file('company_img');
            $fileNameToStore = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('storage/uploads/companylogos');
            $img = Image::make($image->getRealPath());
            $img->resize(1080, 1080, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$fileNameToStore);
            if($company->company_logo!='no_image.png' && file_exists('storage/uploads/companylogos/'.$company->company_logo)){
                unlink('storage/uploads/companylogos/'.$company->company_logo);
            }
        }
        else {
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

    public function attachRole(Request $request){
      $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Supe Client
      $user = User::find($request->userid);
      if(count($request->roleid)>0){
        $attached_roles = $user->roles->pluck('name');
        if(!empty($attached_roles) && $attached_roles !== Null){
          foreach($attached_roles as $name){
            $user->removeRole($name);
          }
        }
        foreach($request->roleid as $name){
          $user->assignRole($name);
        }
      }
      return response()->json([
        'status_code'=>200,
        'status'=>'success',
        'message'=>"Role assigned Successfully."
      ]);
    }

    public function attachPermission(Request $request){
        $this->hasAccess(User::ROLE_ADMINISTRATOR);
        $user = User::find($request->userid);

        if(count($request->permissionid)>0){
          $attached_permission = $user->permissions->pluck('name');
          if(!empty($attached_permission) && $attached_permission !== null){
            foreach($attached_permission as $name){
              $user->revokePermissionTo($name);
            }
          }
          $res=array();
          foreach($request->permissionid as $name){
            validateAttachedPermission($user, $name);
            //$res[]=$user->hasPermissionTo($name);
            //if (!$user->hasPermissionTo($name))
            $user->givePermissionTo($name);
          }
        }
        return response()->json(['status_code'=>200, 'status'=>'success', 'message'=>"Permission assigned Successfully.","Response"=>$res]);
    }

    public function editTeamMemberRole(Request $request)
    {
        $this->hasAccess(User::ROLE_ADMINISTRATOR);
        $user = User::find($request->user_id);

        if ($request->status == 1) {
            $update = $user->assignRole($request->role_id);

            if ($update) {
                return  $this->apiSuccessResponse(200, '', 'Role assigned Successfully.');
            } else {
                return $this->apiErrorResponse('Role was not assigned. Please try again.', 500, $data=null);
            }

        } else if ($request->status == 0) {

            $roles = $user->getRoleNames();
            if($roles->count()<2){
                return  $this->apiSuccessResponse(422, '', "Please keep at least one role assigned to the user.");
            }
            else{
                $update = $user->removeRole($request->role_id);
                if ($update) {
                    return  $this->apiSuccessResponse(200, '', 'Role removed Successfully.');
                }
                else {
                    return $this->apiErrorResponse('Role was not removed. Please try again.', 500, $data=null);
                }
            }
        }
    }

    public function editTeamMemberPermission(Request $request)
    {
        $this->hasAccess(User::ROLE_ADMINISTRATOR);
        $user = User::find($request->user_id);

        $allPermissionObj = Permission::where('guard_name', '=', 'client')
            ->where('name', 'not like', '%delete%')
            ->whereNotIn('name', ['chargeBooking', 'full', 'full client'])
            ->orderBy('name', 'ASC')->get();

//        $allPermission = $allPermissionObj->whereNotIn('name', ['charges', 'authorize', 'capture', 'refund']);
        $allPermission = $allPermissionObj->whereIn('name', RoleAndPermissions::$designV2ClientPermissions);

//        $transactionPermissions = $allPermissionObj->whereIn('name', ['charges', 'authorize', 'capture', 'refund']);
        $transactionPermissions = null;

        if ($request->status == 1) {
            validateAttachedPermission($user, $request->permission_name);
            $update = $user->givePermissionTo($request->permission_id);

            if ($update) {
                $user_permissions = $user->permissions->pluck('name');
                return  $this->apiSuccessResponse(
                    200,
                    [
                        'user_permissions' => $user_permissions,
                        'all_permission'=>$allPermission,
                        'transaction_permissions'=>$transactionPermissions,
                    ],
                    'Permission assigned Successfully.'
                );
            } else {
                return $this->apiErrorResponse('Permission was not assigned. Please try again.', 500, $data=null);
            }

        } else if ($request->status == 0) {
            validateDetachedPermission($user, $request->permission_name);
            $update = $user->revokePermissionTo($request->permission_id);

            if ($update) {
                $user_permissions = $user->permissions->pluck('name');
                return  $this->apiSuccessResponse(
                    200,
                    [
                        'user_permissions' => $user_permissions,
                        'all_permission'=>$allPermission,
                        'transaction_permissions'=>$transactionPermissions,
                    ],
                    'Permission assigned Successfully.'
                );
            } else {
                return $this->apiErrorResponse('Permission was not removed. Please try again.', 500, $data=null);
            }
        }
    }

    public function userimage(Request $request, $id){
        $user = User::find($id);

        $this->validate($request, ['user_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',]);
        if ($request->hasFile('user_img')) {
            $image = $request->file('user_img');
            $fileNameToStore = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('storage/uploads/user_images');
            $img = Image::make($image->getRealPath());
            $img->resize(1080, 1080, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$fileNameToStore);
            if($user->user_image!='no_image.png' && file_exists('storage/uploads/user_images/'.$user->user_image)){
                unlink('storage/uploads/user_images/'.$user->user_image);
            }
        }
        else {
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

    public function destroy($id){
        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Super Client
        $destroy = User::withTrashed()->where('id', $id)->first()->forceDelete();
        return json_encode($destroy ? 1 : 0);
    }

    public function notenableduser(){
        $this->hasAccess(User::ROLE_ADMINISTRATOR); //to Check Is Login User is Supe Client
        view('not_enabled.not_enabled_user');

    }

    /*private function isSuperClient(){
     if(!auth()->user()->hasRole('writer');)
        $userRoles = $obj->roles->pluck('name');
       abort(401, "User can't perform this actions");
    }*/


}
