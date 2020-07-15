<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\PropertyInfo;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestPropertyController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.pages.test_property');
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_account_id' => 'required|numeric',
            'property_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $json_response = '';
        $xml_response = '';
        $json_error = '';
        $xml_error = '';

        try {

            $user_account = UserAccount::find($request->user_account_id);
            $pms = new PMS($user_account);

            if (empty($pms)) {
                return $this->errorResponse('User account is not found',404);
            }

            $property_info = $user_account->properties_info->where('id', $request->property_id)->first();

            $options = new PmsOptions();

            $options->propertyKey = $property_info->property_key;
            $options->propertyID = $property_info->pms_property_id;
            $options->propKey = $property_info->property_key;
            $options->requestType = PmsOptions::REQUEST_TYPE_XML;

            try {
                $pms->fetch_properties($options);
                $xml_response = $pms->getActualResponse();

                if(empty($xml_response)) {
                    $xml_error = 'XML is not found';

                }
            } catch (PmsExceptions $e) {
                $xml_error = $e->getMessage();
            }

            $options->requestType = PmsOptions::REQUEST_TYPE_JSON;

            try {
                $pms->fetch_property($options);
                $json_response = $pms->getActualResponse();

                if(empty($json_response)) {
                    $xml_error = 'XML is not found';

                }
            } catch (PmsExceptions $e) {
                $xml_error = $e->getMessage();
            }

            return $this->successResponse(
                'Process has been success',
                200,
                [
                    'json_response' => $json_response,
                    'json_error' => $json_error,
                    'xml_response' => $xml_response,
                    'xml_error' => $xml_error
                ]
            );

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),404);
        }

    }

    public function getPropertiesByUserAccount(Request $request)
    {
        $property_info = PropertyInfo::where(['status' => 1, 'user_account_id' => $request->user_account_id])
            ->select('id', 'name')
            ->get();

        if ($property_info->count() > 0) {
            return $this->apiSuccessResponse(200, $property_info, 'success');
        } else {
            return $this->apiErrorResponse('Data not found', 404);
        }
    }
}
