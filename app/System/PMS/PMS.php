<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/1/18
 * Time: 2:51 PM
 */

namespace App\System\PMS;

use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\Property;
use App\UserAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PMS {

    const META_PMS_NAME = 'PMS Name';
    const META_PMS_FORM_ID = 'PMS FORM ID';
    const META_USER_ACCOUNT_ID = 'User Account ID';
    const META_USER_ACCOUNT_NAME = 'User Account Name';
    const META_PMS_FUNCTION = 'PMS Function';
    const META_FUNCTION_PARAMETERS = 'Function Parameters';
    const META_API_REQUEST_TIME = 'API Request Time';
    const META_API_RESPONSE_TIME = 'API Response Time';
    const META_REQUEST_FULL_LOG = 'Request Full Log';

    const KEY_META_PMS_OPTIONS = 'PMS Options';
    const KEY_META_BOOKING_DATA = 'Booking Data';
    const KEY_META_PROPERTY_DATA = 'Property Data';

    /**
     * @var PmsInterface
     */
    private $pms;

    /**
     * @var UserAccount
     */
    private $user;

    const ERROR_UNKNOWN_ERROR = 1000; // When its not clear what error is received
    const ERROR_NOT_ALLOWED = 1001;
    const ERROR_NO_WRITE = 1002;
    const ERROR_LIMIT_EXCEED = 1003;
    const ERROR_NO_CREDIT = 1004;
    const ERROR_WHITE_LIST = 1005;
    const ERROR_UN_AUTHORIZED = 1006; // When API key is not provided.
    const ERROR_API_KEY_DISABLE = 1007; // When API key is disabled
    const ERROR_INVALID_PROP_KEY = 1008; // When property key is missing or not valid.
    const ERROR_NO_DATA = 1009; // When no data is found or given for processing.

    /**
     * PMS constructor.
     * @param UserAccount $user
     */
    public function __construct(UserAccount $user) {
        $this->user = $user;
        $this->user->refresh();
    }

    public static function getFunctionNames() {

        $functions = get_class_methods(PMS::class);
        $functions2 = array();

        foreach($functions as $key => $value) {

            /**
             * NOTE: Functions not being stored in db for logs.
             */
            switch ($value) {
                case '__construct':
                case '_resolveClassAndPrepareRequest':
                case __FUNCTION__:
//                    continue;
                break;
                default:
                    $functions2[] = $value;
            }

        }

        return $functions2;
    }

    /**
     * @param string $functionName
     * @param array $forMeta
     * @throws PmsExceptions
     */
    private function _resolveClassAndPrepareRequest(string $functionName, array $forMeta) {

        try {

            $classToMake = $this->user->pms->pms_form->backend_name;

            $meta = array(
                PMS::META_PMS_NAME => $this->user->pms->pms_form->name,
                PMS::META_PMS_FORM_ID => $this->user->pms->pms_form_id,
                PMS::META_USER_ACCOUNT_ID => $this->user->id,
                PMS::META_USER_ACCOUNT_NAME => $this->user->name,
                PMS::META_PMS_FUNCTION => $functionName,
                PMS::META_FUNCTION_PARAMETERS => $forMeta,
                PMS::META_API_REQUEST_TIME => Carbon::now('GMT')->toDateTimeString(),
                PMS::META_API_RESPONSE_TIME => '',
                PMS::META_REQUEST_FULL_LOG => '',
            );

            $this->pms = app()->make($classToMake, $meta);

        } catch (\Exception $e) {
            throw new PmsExceptions('User PMS Credentials not set. ' . $e->getMessage());
        }

    }

    /**
     * Retrieve User Properties
     *
     * @param PmsOptions $options
     * @return array|null
     * @throws PmsExceptions
     */
    public function fetch_properties(PmsOptions $options) {

        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_properties($this->user, $options);
    }

    /**
     * Retrieve User Properties
     *
     * @param PmsOptions $options
     * @return array|null
     * @throws PmsExceptions
     */
    public function fetch_properties_json_xml(PmsOptions $options) {

        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_properties_json_xml($this->user, $options);
    }

    /**
     * Retrieve User's single Property
     * Set PropertyKey and Property ID
     *
     * @param PmsOptions $options
     * @return array|null
     * @throws PmsExceptions
     */
    public function fetch_property(PmsOptions $options) {
        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_property($this->user, $options);
    }

    /**
     * @param PmsOptions $options
     * @return array
     * @throws PmsExceptions
     */
    public function fetch_Booking_Details(PmsOptions $options) {
        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_Booking_Details($this->user, $options);
    }

    /**
     * @param PmsOptions $options
     * @return array
     * @throws PmsExceptions
     */
    public function fetch_Booking_Details_json_xml(PmsOptions $options) {
        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_Booking_Details_json_xml($this->user, $options);
    }

    /**
     * @param PmsOptions $options
     * @param Booking $bookingToUpdateData
     * @return boolean|string
     * @throws PmsExceptions
     */
    public function update_booking(PmsOptions $options, Booking $bookingToUpdateData) {

        $this->_resolveClassAndPrepareRequest(__FUNCTION__,
            array(
                PMS::KEY_META_PMS_OPTIONS => $options,
                PMS::KEY_META_BOOKING_DATA => $bookingToUpdateData));

        return $this->pms->update_booking($this->user, $options, $bookingToUpdateData);
    }

    /**
     * @param PmsOptions $options
     * @return boolean|string true if success or string/json message
     * @throws PmsExceptions
     */
    public function fetch_user_account(PmsOptions $options) {
        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_user_account($this->user, $options);
    }

    /**
     * @param PmsOptions $options
     * @param array $propertiesToUpdateData
     * @return mixed
     * @throws PmsExceptions
     */
    public function update_properties(PmsOptions $options, array $propertiesToUpdateData) {

        $this->_resolveClassAndPrepareRequest(__FUNCTION__,
            array(
                PMS::KEY_META_PMS_OPTIONS => $options,
                PMS::KEY_META_PROPERTY_DATA => $propertiesToUpdateData));

        return $this->pms->update_properties($this->user, $options, $propertiesToUpdateData);
    }


    /**
     * @param PmsOptions $options
     * @return array
     * @throws PmsExceptions
     */
    public function fetch_card_for_booking(PmsOptions $options) {
        $this->_resolveClassAndPrepareRequest(__FUNCTION__, array(PMS::KEY_META_PMS_OPTIONS => $options));
        return $this->pms->fetch_card_for_booking($this->user, $options);
    }

    /**
     * Returns response of api in form of string, json or xml
     * @return null|string
     */
    function getActualResponse() {
        if(method_exists($this->pms, 'getActualResponse'))
            return $this->pms->getActualResponse();

        return null;
    }

}