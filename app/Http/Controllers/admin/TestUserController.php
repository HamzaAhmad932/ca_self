<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Exception;
use Illuminate\Http\Request;

class TestUserController extends Controller
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
        return view('admin.pages.test_user');
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {

        $json_response = '';
        $xml_response = '';
        $json_error = '';
        $xml_error = '';

        try {

            $pms = new PMS(UserAccount::find($request->user_account_id));

            if (empty($pms)) {
                return $this->errorResponse('User account is not found',404);
            }

            $options = new PmsOptions();
            $options->requestType = PmsOptions::REQUEST_TYPE_XML;

            try {
                $pms->fetch_user_account($options);
                $xml_response = $pms->getActualResponse();

                if(empty($xml_response)) {
                    $xml_error = 'XML is not found';

                }
            } catch (PmsExceptions $e) {
                $xml_error = $e->getMessage();
            }

            $options->requestType = PmsOptions::REQUEST_TYPE_JSON;

            try {
                $pms->fetch_user_account($options);
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

}
