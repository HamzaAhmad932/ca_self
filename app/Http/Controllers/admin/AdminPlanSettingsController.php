<?php

namespace App\Http\Controllers\admin;

use App\PaymentGatewayForm;
use App\PlanSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;

class AdminPlanSettingsController extends Controller
{

    public function __construct(){

        $this->middleware('auth');
    }

    public function commissionplans(){

        $plans = PlanSetting::all();

        return  view('admin.pages.commissionplans',['plans' => $plans]);

    }

    public function createnewplan(Request $request){

//    $request->type;
//    $request->title;
//    $request->period;
          //dd($request->all());
        $plans = PlanSetting::create([
            'plan_type' => $request->plan,
            'settings' => $request->all(),
            'type' => $request->type,
            'status' => '1',
            'model_type' => 'App\PlanSetting'
        ]);

        if($plans ){
            $res = array('done' => 1);
        }else{
            $res = array('done' => 2);
        }

        return json_encode($res);

    }
    public function updateplan(Request $request,$id){

        $plans = PlanSetting::find($id);
         $plans->settings = $request->all();
         $plans->type = $request->type;

        if($plans->save() == true){
            $res = array('done' => 1);
        }else{
            $res = array('done' => 2);
        }

        return json_encode($res);

    }
    public function planstatus($id,$status){

        $plans = PlanSetting::find($id);

         $plans->status = $status;

        if($plans->save() == true){
            $res = array('done' => 1);
        }else{
            $res = array('done' => 2);
        }

        return json_encode($res);

    }




}
