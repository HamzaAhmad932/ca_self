<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/3/18
 * Time: 10:38 AM
 */

namespace App\System\PMS\BookingAutomation;


use App\System\PMS\PMS;
use SimpleXMLElement;

class Errors {

    public function getErrorMessage($errorCode) {
        switch ($errorCode) {
            case '1009':
                return 'Not allowed for this role';
            case '1010':
                return 'No write access';
            case '1016':
                return 'Usage limit exceeded in last 5 minutes';
            case '1020':
                return 'Usage limit exceeded in last 5 minutes';
            case '1021':
                return 'Account has no credit';
            case '1022':
                return 'Not whitelisted';
            default:
                return 'Unknown error from booking automation';
        }
    }

    public function hasError(array $content) {
        if(is_array($content)) {
            if(key_exists('error', $content) || key_exists('errorCode', $content)) {
                return true;
            }
            return false;
        }
        return true;
    }

    public function getErrorMessageFromResponse(array $content) {
        if(is_array($content)) {
            $errorMessage = '';
            if(key_exists('error', $content))
                $errorMessage .= $content['error'];
            else if(key_exists('errorCode', $content))
                $errorMessage .= 'PMS Error Code: ' . $content['errorCode'];
            else
                $errorMessage .= 'PMS Error, No valid information to show.';
            return $errorMessage;
        }
        return 'Unknown Response for error';
    }

    public function getErrorCodeFromJSONResponse(array $content) {
        if(is_array($content)) {
            if(key_exists('errorCode', $content))
                return (int) $content['errorCode'];
        }
        return PMS::ERROR_UNKNOWN_ERROR;
    }

    public function hasErrorXML(SimpleXMLElement $xml) {

        if($xml === null)
            return true;

        $searchResult = $xml->xpath('//error');

        if(count($searchResult) > 0)
            return true;

        return false;
    }

    public function getErrorMessageFromResponseXML(SimpleXMLElement $content) {
        if($content === null)
            return 'Null response';

        $searchResult = $content->xpath('//error');
        $message = '';

        foreach($searchResult as $error)
                $message .= $error . '. ';

        return $message;
    }


    public function getErrorCodeFromXMLResponse(SimpleXMLElement $content) {

        if($content === null)
            return PMS::ERROR_UNKNOWN_ERROR;

        $searchResult = $content->xpath('//error');
        $message = '';

        foreach($searchResult as $error)
            foreach($error->attributes() as $key => $value)
                if($key == 'code')
                    return (int) $value;

        return PMS::ERROR_UNKNOWN_ERROR;
    }

}