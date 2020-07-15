<?php

namespace App\Http\Controllers\admin;

use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\User;
use Notification;
use App\UserAccount;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\RegistersUsers;
use Image;

class CompanyController extends Controller
{
    public function __construct(){

        // $this->middleware('auth');
    }

    public function company_audit_logs($id){
        $user_account = UserAccount::find($id);
        $audits = $user_account->audits;

        $final_audits = [];

        $i = 0;
        foreach ($audits as $audit) {
          foreach($audit->getModified() as $field => $value) {
            $final_audits[$i]['field'] = $field;
            $final_audits[$i]['event'] = ucfirst($audit->event);
            $final_audits[$i]['created_at'] = isset($audit->created_at) ? $audit->created_at->toDateTimeString(): '-';
            
            //for old value column arrange data
            if(isset($value['old']) && !is_array($value['old']))
              $final_audits[$i]['old_value'] = $value['old'];
            elseif(isset($value['old']) && is_array($value['old']))
              $final_audits[$i]['old_value'] = isset($value['old']['date']) ? $value['old']['date'] : '-';
            else
              $final_audits[$i]['old_value'] = '-';
            
            //for new value column arrange data
            if(isset($value['new']) && !is_array($value['new']))
              $final_audits[$i]['new_value'] = $value['new'];
            elseif(isset($value['new']) && is_array($value['new']))
              $final_audits[$i]['new_value'] = isset($value['new']['date']) ? $value['new']['date'] : '-';
            else
              $final_audits[$i]['new_value'] = '-';

            //increment counter 
            $i++;

          }
        }
        
        return Datatables::of($final_audits)->make(true);
    }


    public function company_profile($id){
        $user = User::where('user_account_id', $id)->where('parent_user_id', 0)->with('user_account')->first();
        // echo "<pre>";print_r($user_accounts->user->toArray());exit;
        $data = array(
            'client' => $user,
            'company' => $user->user_account
        );

        // echo "<pre>";print_r($user_accounts->audits->toArray());exit;
        return view('admin.clients.companies.company_profile')->with('data', $data);
    }

    /**
     * @param Request $request
     * @param $id
     * @return false|string
     */
    public function clientprofileupdate(Request $request, $id){

        Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'phone' => 'required|max:25',

        ])->validate();

        $clientProfile = User::find($id);

        if(!empty($request->get('name'))){
            $clientProfile->name = $request->get('name');
            
        }

        if(!empty($request->get('phone'))){
            $clientProfile->phone = $request->get('phone');
        }
        if(!empty($request->get('city'))){
            $clientProfile->city = $request->get('city');
        }
        if(!empty($request->get('state'))){
            $clientProfile->state = $request->get('state');
        }
        if(!empty($request->get('address'))){
            $clientProfile->address = $request->get('address');
        }
        if(!empty($request->get('country'))){
            $clientProfile->country = $request->get('country');
        }
        if(!empty($request->get('password'))){
            $clientProfile->password = Hash::make($request->get('password'));
        }
        if(!empty($request->get('address2'))){
            $clientProfile->address2 = $request->get('address2');
        }
        if(!empty($request->get('website'))){
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
    public function companylogo(Request $request, $id)
    {

        $this->validate($request, ['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',]);

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $fileNameToStore = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('storage/uploads/companylogos');
            $img = Image::make($image->getRealPath());
            $img->resize(1080, 1080, function ($constraint) {
            $constraint->aspectRatio();
            })->save($destinationPath.'/'.$fileNameToStore);

        } else {
            $fileNameToStore = 'no_image.png';
        }
        $companyLogo = UserAccount::find($id);

        $companyLogo->company_logo = $fileNameToStore;

        if ($companyLogo->save() == true) {
            $res = array('done' => $fileNameToStore,);
        } else {
            $res = array('done' => 0,);
        }

        return json_encode($res);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

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
            //'user_account_id' =>$request->get('company')[0],
            'parent_user_id' => $parent_id,

        ]);
        
        $user->guard_name='admin';
        $user->assignRole($request->get('slctr'));
        $user->givePermissionTo($request->get('slctp'));
        $user->notify(new \App\Notifications\Users\ActivateEmailNotification($user));
            
        return back()->with('success','User Added Successfully');   ;
    } 

 

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){

         $obj = User::find($id);
        return view('admin.pages.memberprofile')->with('member', $obj);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
         
        User::find($id)->delete();
        return response()->json('User Deleted');
    }


    public function adminstatus($id, $st){

       $adminStatus = User::find($id);
       $adminStatus->status = $st;
       $adminStatus->save();
       $res = array('status' => $st);

       if($st == config('db_const.user.status.active.value')){
          $sts = config('db_const.user.status.active.label');
       }elseif ($st == config('db_const.user.status.deactive.value')){
           $sts = config('db_const.user.status.deactive.label');
       }

        return json_encode($res);
    }

    public function userpagination(){

        $data = User::paginate(4);
        return request()->json(200, $data);
    }

    public function changepassword(Request $request, $id){

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

    public function adminprofileupdate(Request $request, $id){

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

    public function notify(){

        $users = User::all();
        $letter = collect(['title'=> 'New TOS','body'=> 'We Have Updated of Tos']);
        Notification::send($users, new DatabaseNotification($letter));
        return view('admin.pages.dashboard');
    }

    public function pagi(){
        
        $user_account = UserAccount::paginate(1);
        return request()->json(200, $user_account);
    }

}


