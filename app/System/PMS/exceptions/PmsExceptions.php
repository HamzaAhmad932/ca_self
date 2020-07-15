<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/3/18
 * Time: 12:49 PM
 */

namespace App\System\PMS\exceptions;

use App\System\PMS\PMS;
use Exception;
use Illuminate\Support\Facades\Log;

class PmsExceptions extends Exception {

    private $errorType = 0; // OTA or SMX related for error reporting.

    const BA_NOT_ALLOWED = 1009; // Not allowed for this role
    const BA_NO_WRITE = 1010; // No write access
    const BA_LIMIT_EXCEED = 1016; // Usage limit exceeded in last 5 minutes
    const BA_LIMIT_EXCEED_2 = 1020; // Usage limit exceeded in last 5 minutes
    const BA_NO_CREDIT = 1021; // Account has no credit
    const BA_WHITE_LIST = 1022; // Not whitelisted
    const BA_UNAUTHORIZED = 1002; // un authorized
    const BA_API_KEY_ACCESS_DISABLED = 1003; // When Api-key-access is set to disable in account API Key 1 section.
    const BA_INVALID_PROP_KEY = 2000;

    /*
     * Site-minder Errors & Types
     * SMX_EWT : error type
     * SMX_ERR : error code
     */
    const SMX_EWT_Unknown = 1;
    const SMX_EWT_No_implementation = 2;
    const SMX_EWT_Biz_rule = 3;
    const SMX_EWT_Authentication = 4;
    const SMX_EWT_Authentication_timeout = 5;
    const SMX_EWT_Authorization = 6;
    const SMX_EWT_Protocol_violation = 7;
    const SMX_EWT_Transaction_model = 8;
    const SMX_EWT_Authentical_model = 9;
    const SMX_EWT_Required_field_missing = 10;
    const SMX_EWT_Advisory = 11;
    const SMX_EWT_Processing_exception = 12;
    const SMX_EWT_Application_error = 13;

    const SMX_ERR_System_currently_unavailable = 187;
    const SMX_ERR_Invalid_property_code = 400;
    const SMX_ERR_System_error = 448;
    const SMX_ERR_Unable_to_process = 450;
    const SMX_ERR_Authorization_error = 497;
    const SMX_ERR_Invalid_date = 16;
    const SMX_ERR_missing_last_name = 310;
    const SMX_ERR_missing_first_name = 311;
    const SMX_ERR_missing_phone_number = 316;
    const SMX_ERR_Required_field_missing = 321; // Used when a required field is missing that does not have a dedicated error code (ie: Room ID required)
    const SMX_ERR_Hotel_not_active = 375;
    const SMX_ERR_Invalid_hotel_code = 392;


    public function setErrorType(int $errorType) {
        $this->errorType = $errorType;
    }

    public function getErrorType() {
        return $this->errorType;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report() {

        if($this->getCode() === 404)
            $this->message = "API not Found 404";

    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request) {
        return response();
    }

    /**
     * @return int
     */
    public function getPMSCode()
    {
        switch ($this->code) {
            case self::BA_NOT_ALLOWED:
                return PMS::ERROR_NOT_ALLOWED;
            case self::BA_NO_WRITE:
                return PMS::ERROR_NO_WRITE;
            case self::BA_LIMIT_EXCEED:
            case self::BA_LIMIT_EXCEED_2:
                return PMS::ERROR_LIMIT_EXCEED;
            case self::BA_NO_CREDIT:
                return PMS::ERROR_NO_CREDIT;
            case self::BA_WHITE_LIST:
                return PMS::ERROR_WHITE_LIST;
            case self::BA_UNAUTHORIZED:
                return PMS::ERROR_UN_AUTHORIZED;
            case self::BA_API_KEY_ACCESS_DISABLED:
                return PMS::ERROR_API_KEY_DISABLE;
            case self::BA_INVALID_PROP_KEY:
                return PMS::ERROR_INVALID_PROP_KEY;
            default:
                return PMS::ERROR_UNKNOWN_ERROR;
        }
    }

    public function getCADefineMessage()
    {
        $error_code = $this->getPMSCode();
        $unknown_message = 'Unknown Error occurred. Contact developer(s).';
        $exception_error_message = [
            PMS::ERROR_UNKNOWN_ERROR => $unknown_message,
            PMS::ERROR_NOT_ALLOWED => 'This action is not allowed.', // Not verified yet
            PMS::ERROR_NO_WRITE => 'User is not allowed to make changes.',
            PMS::ERROR_LIMIT_EXCEED => 'PMS API call limit exceed. Try few minutes later.',
            PMS::ERROR_NO_CREDIT => 'User had not payed bill.',
            PMS::ERROR_WHITE_LIST => 'CA IP is not white listed',
            PMS::ERROR_UN_AUTHORIZED => 'API key is not provided',
            PMS::ERROR_API_KEY_DISABLE => 'API key access is disabled. Under Account > Account Access > API Key 1',
            PMS::ERROR_INVALID_PROP_KEY => 'Property Key is missing or invalid, Please update on your PMS and reload page.'
        ];

        if( key_exists($error_code, $exception_error_message))
            return $exception_error_message[$error_code];
        else
            return  $unknown_message;


    }

}