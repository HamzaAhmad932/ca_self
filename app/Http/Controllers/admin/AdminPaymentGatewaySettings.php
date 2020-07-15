<?php

namespace App\Http\Controllers\admin;

use App\PaymentGatewayForm;
use App\PaymentGatewayParent;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Exceptions\GatewayException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;


class AdminPaymentGatewaySettings extends Controller
{
    public function __construct(){

        $this->middleware('auth');
    }

    /**
    *  Show All Payment Gateways 
    */
    public function paymentgateways(){

        return  view('admin.pages.paymentgateways', array(
            'parentGateways' => PaymentGatewayParent::all()->toArray()
        ));

    }

    public function getParentCredentials (Request $request) {

        $gateway = PaymentGatewayParent::where('id', $request->get('id'))->first();

        if($gateway != null) {

            $credentials = json_decode($gateway->credentials);
            $formattedCredentials = array();
            foreach ($credentials as $key => $value) {
                $label = config("db_const.{$gateway['backend_name']}.credentials.$key.label");
                $formattedCredentials[] = array('label' => $label, 'key' => $key, 'value' => $value);
            }

            $gateway->credentials = $formattedCredentials;
            return $gateway;

        } else {
            return false;
        }

    }

    public function setParentCredentials(Request $request) {

        return PaymentGatewayParent::where('id', $request->get('id'))->update(array(
            'credentials' => json_encode($request->get('data'))
        ));
    }

    public function addGatewaysFromParent(Request $request) {

        $id = $request->get('id');

        $parent = PaymentGatewayParent::where('id', $id)->first();

        if($parent != null) {
            try {
                $credentials = json_decode($parent->credentials, true);
                $backend_name = $parent->backend_name;
                $pGate = new PaymentGateway();

                $response = $pGate->listAllGateways($backend_name, $credentials);

                return $response;

            } catch (GatewayException $e) {
                report($e);
                Log::error("getGatewaysFromParent: " . $e->getMessage());
                Log::error("Stacktrace: " . $e->getTraceAsString());
            } catch (\Exception $e) {
                Log::error("getGatewaysFromParent: " . $e->getMessage());
                Log::error("Stacktrace: " . $e->getTraceAsString());
            }
        }

        return 'false';
    }

    public function pgforms(){

        $payment_gateway_forms = PaymentGatewayForm::all();
        return Datatables::of($payment_gateway_forms)->make(true);

    }

    public function addUpdateGatewayFromParent(Request $request) {

        $name = $request->get('name');
        $parentId = $request->get('id');
        $result = array('success' => 0, 'message' => 'Something went wrong try again');

        $parent = PaymentGatewayParent::where('id', $parentId)->first();

        if($parent != null) {
            try {
                $credentials = json_decode($parent->credentials, true);
                $backend_name = $parent->backend_name;
                $pGate = new PaymentGateway();

                $response = $pGate->listAllGateways($backend_name, $credentials);

                $dbGateway = PaymentGatewayForm::where('backend_name', $backend_name)
                    ->where('payment_gateway_parent_id', $parentId)
                    ->where('name', $name)->first();

                /**
                 * @var $gateway GateWay
                 * @var $gatewayFromServerResponse GateWay
                 */
                $gatewayFromServerResponse = null;

                foreach ($response as $gateway) {
                    if($gateway->name == $name) {
                        $gatewayFromServerResponse = $gateway;
                        break;
                    }
                }

                if($dbGateway == null) {
                    PaymentGatewayForm::create(['name' => $gatewayFromServerResponse->name,
                        'backend_name' => $backend_name,
                        'gateway_form' => json_encode($gatewayFromServerResponse),
                        'payment_gateway_parent_id' => $parentId,
                        'status' => 0]);
                    $result['message'] = 'Gateway Added';
                }
                else {
                    $dbGateway->name = $gateway->name;
                    $dbGateway->backend_name = $backend_name;
                    $dbGateway->gateway_form = json_encode($gateway);
                    $dbGateway->payment_gateway_parent_id = $parentId;
                    $dbGateway->save();
                    $result['message'] = 'Gateway updated';
                }

                $result['success'] = 1;

                return response()->json($result);

            } catch (GatewayException $e) {
                report($e);
                Log::error("getGatewaysFromParent: " . $e->getMessage());
                $result['message'] = $e->getMessage();
            } catch (\Exception $e) {
                Log::error("getGatewaysFromParent: " . $e->getMessage());
                $result['message'] = $e->getMessage();
            }
        }

        return response()->json($result);
    }

    /**
     * Function to run only ones and only manually
     */
    public function addAllGateways() {

        $parent = PaymentGatewayParent::where('backend_name', 'pg_form_spreedly')->first();

        if($parent != null) {
            try {
                $credentials = json_decode($parent->credentials, true);
                $backend_name = $parent->backend_name;
                $pGate = new PaymentGateway();

                $response = $pGate->listAllGateways($backend_name, $credentials);

                /**
                 * @var $gateway GateWay
                 * @var $gatewayFromServerResponse GateWay
                 */
                $gatewayFromServerResponse = null;

                foreach ($response as $gateway) {
                    $logo = strtolower($gateway->name);
                    $logo = str_replace(' ', '_', $logo);
                    $logo = str_replace('-', '_', $logo);
                    var_dump($logo);
                    echo "<br>";
                    if($gateway->name == 'Stripe')
                        continue;
                    if($gateway->name == 'Spreedly Test')
                        continue;
                    PaymentGatewayForm::create(['name' => $gateway->name,
                        'backend_name' => $backend_name,
                        'gateway_form' => json_encode($gateway),
                        'payment_gateway_parent_id' => $parent->id,
                        'logo' => $logo . '.png',
                        'status' => 1]);
                }

            } catch (GatewayException $e) {
                report($e);
                Log::error("getGatewaysFromParent: " . $e->getMessage());
                dd($e->getMessage());
            } catch (\Exception $e) {
                Log::error("getGatewaysFromParent: " . $e->getMessage());
                dd($e->getMessage());
            }
        }

    }

    public function update_pg_status(Request $request) {

        if($request->get('status') == 'true')
            $st = 1;
        else
            $st = 0;

        $payment_gateway_forms = PaymentGatewayForm::find($request->get('id'));
        $payment_gateway_forms->status = $st;

        if ($payment_gateway_forms->save() == true) {
            $res = array('done' => 1);
        } else {
            $res = array('done' => 0);
        }

        return json_encode($res);

    }

    public function newpgform(Request $request){


        $payment_gateway_forms = PaymentGatewayForm::create([
            'name' => $request->get('name'),
            'backend_name' => $request->get('backend_name'),
            'status' => $request->get('status'),
            'payment_gateway_parent_id' => $request->get('payment_gateway_parent_id'),
            'gateway_form' => $request->get('gateway_form'),
        ]);

        if($payment_gateway_forms->save() == true){
            $res = array('done' => 1, );
        }else{
            $res = array('done' => 0, );
        }


        return  json_encode($res);

    }

    public function payment_gateway_logo(Request $request, $id)
    {

        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extention = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . $id . '.' . $extention;

            $path = $request->file('file')->storeAs('public/uploads/payment_gateway_logos', $fileNameToStore);
        } else {
            $filenameToStore = 'no_image.png';
        }


        $payment_gateway_logo=PaymentGatewayForm::where('id',$id)->first();

        $payment_gateway_logo->logo = $fileNameToStore;

        if ($payment_gateway_logo->save() == true) {
            $res = array('done' => $fileNameToStore,);
        } else {
            $res = array('done' => 0,);
        }


        return json_encode($res);

    }

}