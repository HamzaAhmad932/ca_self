<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\PropertyInfo;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index($user_account_id = 0)
    {
        return view('admin.properties.property-list')->with('user_account_id', $user_account_id);
    }

    public function getProperties(Request $request)
    {
        try {
            if ($request->filters['user_account_id'] != 0) {
                $filterArr[] = ['user_account_id', $request->filters['user_account_id']];
            }

            $filterArr[] = ['available_on_pms', 1];
            $sortColumn  = 'name';
            $sortOrder   = 'Asc';

            if ($request->has('filters')) {
                if (isset($request->filters['city']) && ($request->filters['city'] != 'all'))
                    $filterArr[] = ['city', $request->filters['city']];
                if (isset($request->filters['search']) && ($request->filters['search'] != null))
                    $searchStr = "%{$request->filters['search']}%";
                if (isset($request->filters['sortColumn']) && ($request->filters['sortColumn'] != null))
                    $sortColumn = $request->filters['sortColumn'];
                if (isset($request->filters['sortOrder']) && ($request->filters['sortOrder'] != null))
                    $sortOrder = $request->filters['sortOrder'];
                if (isset($request->filters['per_page']) && !empty($request->filters['per_page'])){
                    $per_page = $request->filters['per_page'];
                } else {
                    $per_page = 10;
                }
            } else {
                $per_page=10;
            }

            if (isset($searchStr)) {
                $properties = PropertyInfo::where($filterArr)
                    ->where( function( $query ) use ($searchStr) {$query->where('id','LIKE', $searchStr)
                        ->orWhere('name','LIKE', $searchStr)
                        ->orWhere('pms_property_id','LIKE', $searchStr)
                        ->orWhere('user_account_id','LIKE', $searchStr)
                        ->orWhere('address','LIKE', $searchStr)
                        ->orWhere('currency_code','LIKE', $searchStr);})
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            } else {
                $properties = PropertyInfo::where($filterArr)->orderBy($sortColumn, $sortOrder)->paginate($per_page);
            }

            return $this->apiSuccessResponse(200, $properties, 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @return mixed
     */
    public function getAllPropertiesCities(Request $request)
    {
        try {
            if ($request->user_account_id != 0) {
                return PropertyInfo::select('city')->where('user_account_id', $request->user_account_id)->distinct()->pluck('city')->toArray();
            } else {
                return PropertyInfo::select('city')->distinct()->pluck('city')->toArray();
            }
        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function showProperty($property_info_id)
    {
        return view('admin.properties.property-detail')->with('property_info_id', $property_info_id);
    }

    /**
     * @param $property_id
     * @param $user_account_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPropertyDetail($property_info_id)
    {
        try {

            $property_info = PropertyInfo::with(['pmsForm', 'propertyInfoAudits'])->where('id', $property_info_id)->first();
            $payment_gateways = new PaymentGateways();
            $user_payment_gateway = $payment_gateways->getPropertyPaymentGatewayFromProperty($property_info);

            if (!empty($user_payment_gateway)) {
                $user_payment_gateway = json_decode($user_payment_gateway->gateway, true);
                $property_info->user_payment_gateway_name = $user_payment_gateway['name'];
            } else {
                $property_info->user_payment_gateway_name = '';
            }

            return $this->apiSuccessResponse(200, $property_info, 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }

    }

    public function verificationDetail(Request $request)
    {
        try {
            $message = '';
            $property = PropertyInfo::find($request->property_id);
            $user_account = UserAccount::find($property->user_account_id);

            $pms = new PMS($user_account);
            $options = new PmsOptions();

            $pms_properties = $pms->fetch_properties_json_xml($options);

            if (empty($pms_properties))
                return $this->apiSuccessResponse(200, [], 'No Property Found.');


            $pms_property_collections = collect($pms_properties);
            $pms_property = $pms_property_collections->where('id', $property->pms_property_id)->first();

            $message = matchNotifyUrl($pms_property);

            if (!empty($pms_property->currencyCode) && $pms_property->currencyCode == $property->currency_code) {
                $message .= " Currency Code is verified.";
            } elseif (!empty($pms_property->currencyCode) && $pms_property->currencyCode != $property->currency_code) {
                $message .= " Currency Code is not match.";
            } elseif (empty($pms_property->currencyCode)) {
                $message .= " Currency Code is not set on PMS.";
            }

            if (!empty($pms_property->propertyKey) && $pms_property->propertyKey != -1 && $pms_property->propertyKey == $property->property_key) {
                $message .= ' Property Key is verified.';
            } elseif (!empty($pms_property->propertyKey) && $pms_property->propertyKey != -1 && $pms_property->propertyKey != $property->property_key) {
                $message .= ' Property Key is not match.';
            } elseif ((!empty($pms_property->propertyKey) && $pms_property->propertyKey = -1) || empty($pms_property->propertyKey)) {
                $message .= ' Property Key is not set on PMS.';
            }
            
            return $this->apiSuccessResponse(200, [], $message);

        } catch (PmsExceptions $e) {
            return $this->apiErrorResponse($e->getCADefineMessage() . $e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }
}
