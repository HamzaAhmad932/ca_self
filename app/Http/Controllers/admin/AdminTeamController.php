<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Notifications\DatabaseNotification;
use App\User;
use App\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminTeamController extends Controller
{
    public function __construct(){

        $this->middleware('auth');
    }
    public function emails(){
        return view('admin.emails.email_settings');
    }
    /**
     * @return JsonResponse
     */
    public function getTemplateVariables(){
        $variables = config('db_const.template_variables_naming');
        $data = array();
        foreach ($variables as $model =>$variable){
            foreach ($variable['variables'] as $key=>$var ){
                $data[]=$var;
            }
        }
        return $this->apiSuccessResponse('200',$data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->id;
        
        // TODO:: permission check show user or not 
        // also check permission in view 

        $team = User::paginate(4);
        $adminPermission = Permission::where('guard_name', 'admin')->get();
        $adminRole = Role::where('guard_name', 'admin')->get();
        $company =  UserAccount::all();
        $data = array(
            'teamList' => $team,
            'adminPermission' => $adminPermission,
            'adminRole' => $adminRole,
            'company' => $company

        );
        return view('admin.pages.manageteam')->with('data', $data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        if(auth::user()->hasRole('SuperAdmin'))    
        {   
            Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:6',
                'phone' => 'required|max:25',

            ])->validate();

            $parent_id = auth()->user()->id;
            $company_id = auth()->user()->user_account_id;


                    
                $user = User::create([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'username' => $request->get('username'),
                    'phone' => $request->get('phone'),
                    'password' => Hash::make($request->get('password')),
                    'user_account_id' =>$request->get('company')[0],
                    'parent_user_id' => $parent_id,

                ]);
            
                $user->guard_name='admin';
                $user->assignRole($request->get('slctr'));
                return back()->with('success','User Added Successfully');
        
        } 
            return response("Can't perform this action.", 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
     if(auth::user()->hasRole('SuperAdmin') || auth::user()->hasRole('Admin')){

          $obj = User::find($id);
          return view('admin.pages.memberprofile')->with('member', $obj);
        }
       
        return response("Can't perform this action.", 401);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(auth::user()->hasRole('SuperAdmin') || auth::user()->id==$id){   
               
            $memberProfile = User::find($id);
            $memberProfile->name=$request->get('name');
            $memberProfile->email=$request->get('email');
            $memberProfile->phone=$request->get('phone');
            $memberProfile->city=$request->get('city');
            $memberProfile->state=$request->get('state');
            $memberProfile->address=$request->get('address');
            $memberProfile->country=$request->get('country');
            $memberProfile->password=Hash::make($request->get('password'));
            $memberProfile->address2=$request->get('address2');
            $memberProfile->website=$request->get('website');
            $memberProfile->username=$request->get('username');
            
            $memberProfile->save();

            return back()->with('success', 'Profile is updated successfully!' );
        }

        return response("Can't perform this action.", 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
     
        if(auth::user()->hasRole('SuperAdmin')){   

            User::find($id)->delete();
            return response()->json('User Deleted');
        }
        
        return response("Can't perform this action.", 401);
    }

    public function adminstatus($id, $st){
        if(auth::user()->hasRole('SuperAdmin')){   

           $adminStatus = User::find($id);
           $adminStatus->status = $st;
           $adminStatus->save();

           $res = array('status' => $st, );
           if($st == config('db_const.user.status.active.value')){
              $sts = config('db_const.user.status.active.label');
           }elseif ($st == config('db_const.user.status.deactive.value')){
               $sts = config('db_const.user.status.deactive.label');
           }

            return json_encode($res);
        }
       
        return response("Can't perform this action.", 401);
    }

    public function userpagination(){

        $data = User::paginate(4);
        return request()->json(200, $data);

    }

    public function changepassword(Request $request, $id){

        if(auth::user()->hasRole('SuperAdmin') || auth::user()->id==$id){  

            Validator::make($request->all(), [
                'password' => 'required|string|min:6',
            ])->validate();
            $memberPass = User::find($id);
            $memberPass->password=Hash::make($request->get('password'));
            if($memberPass->save() == true){
                $res = array('done' => 1, );
            }else{
                $res = array('done' => 0, );

            }
            return json_encode($res);

        }
        return response("Can't perform this action.", 401);
    }


    public function adminprofileupdate(Request $request, $id){

        if(auth::user()->hasRole('SuperAdmin') || auth::user()->id==$id){   

            Validator::make($request->all(), [
                'name' => 'required|string|max:191',
                'phone' => 'required|max:25',

            ])->validate();

            $memberProfile = User::find($id);
            $memberProfile->name=$request->get('name');
            
            $memberProfile->phone=$request->get('phone');
            $memberProfile->city=$request->get('city');
            $memberProfile->state=$request->get('state');
            $memberProfile->address=$request->get('address');
            $memberProfile->country=$request->get('country');
            
            $memberProfile->address2=$request->get('address2');
            $memberProfile->website=$request->get('website');
        
            if($memberProfile->save() == true){
                $res = array('done' => 1, );
            }else{
                $res = array('done' => 0, );

            }

            return json_encode($res);
        }
        return response("Can't perform this action.", 401);
    }

    public function notify(){

        if(auth::user()->hasRole('SuperAdmin') || auth::user()->hasRole('Admin')){

            $users = User::all();
            $letter = collect(['title'=> 'New TOS','body'=> 'We Have Updated of Tos']);
            Notification::send($users, new DatabaseNotification($letter));
            return view('admin.pages.dashboard');
        }
        return response("Can't perform this action.", 401);
    }

    public function allnotifications(){
        if(auth::user()->hasRole('SuperAdmin') || auth::user()->hasRole('Admin')){

            auth()->user()->unreadNotifications->markAsRead();
            return view('admin.notifications.notifications');
        }

    }
}


