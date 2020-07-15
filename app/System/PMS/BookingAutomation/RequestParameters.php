<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/9/18
 * Time: 10:54 AM
 */

namespace App\System\PMS\BookingAutomation;


use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\UserAccount;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class RequestParameters {

    private $baseUrl = 'https://api.beds24.com/';
    /**
     * @var null|array
     */
    private $pmsCredentialsJSON = null;
    /**
     * @var \SimpleXMLElement
     */
    private $pmsCredentialsXML = null;

    /**
     * @param UserAccount $user
     * @return array|null
     * @throws PmsExceptions
     */
    public function makeAuthJSON(UserAccount $user) {
        if($this->pmsCredentialsJSON === null) {
            if ($user->pms !== null) {

                $pmsC = json_decode($user->pms->form_data, true);
                if ($pmsC) {
                    if(count($pmsC['credentials']) > 0) {
                        foreach($pmsC['credentials'] as $credentials) {
                            if(key_exists('name', $credentials)) {
                                if($credentials['name'] == 'api-key')
                                $this->pmsCredentialsJSON = array(
                                    'authentication' => array(
                                        'apiKey' => $credentials['value']));
                                return $this->pmsCredentialsJSON;
                            }
                        }
                    }

                }
            }

            throw new PmsExceptions('User has not configured PMS settings');
        }
        return $this->pmsCredentialsJSON;
    }

    /**
     * @param UserAccount $user
     * @return \SimpleXMLElement
     * @throws PmsExceptions
     */
    public function makeAuthXML(UserAccount $user) {
        if($this->pmsCredentialsXML === null) {
            if ($user->pms !== null) {

                $pmsC = json_decode($user->pms->form_data, true);
                if ($pmsC) {

                    if(count($pmsC['credentials']) > 0) {

                        $dom = new \DOMDocument();
                        $dom->appendChild($dom->createElement('request'));
                        $sxe = simplexml_import_dom($dom);
                        $auth = $sxe->addChild('auth');

                        foreach ($pmsC['credentials'] as $credentials) {
                            if (key_exists('name', $credentials)) {
                                if ($credentials['name'] == 'username') {
                                    $auth->addChild('username', $credentials['value']);
                                } elseif($credentials['name'] == 'api-key') {
                                    $auth->addChild('password', $credentials['value']);
                                }
                            }
                        }

                        $this->pmsCredentialsXML = $sxe;
                        return $this->pmsCredentialsXML;
                    }

                }
            }

            throw new PmsExceptions('User has not configured PMS settings');
        }
        return $this->pmsCredentialsXML;
    }

    protected function xmlRequestWithPropertyID($propertyID) {
        $this->pmsCredentialsXML->addChild('propid', $propertyID);
    }

    protected function xmlRequestWithRoomID($roomID) {
        $this->pmsCredentialsXML->addChild('roomid', $roomID);
    }

    protected function xmlRequestWithMasterID($masterID) {
        $this->pmsCredentialsXML->addChild('masterid', $masterID);
    }

    protected function xmlRequestWithBookingID($bookingID) {
        $this->pmsCredentialsXML->addChild('bookid', $bookingID);
    }

    protected function xmlRequestWithModifiedDate($date) {
        $this->pmsCredentialsXML->addChild('modified', $date);
    }

    protected function xmlRequestWithDateFrom($date) {
        $this->pmsCredentialsXML->addChild('datefrom', $date);
    }

    protected function xmlRequestWithDateTo($date) {
        $this->pmsCredentialsXML->addChild('dateto', $date);
    }

    protected function xmlRequestForCard() {
        $this->pmsCredentialsXML->addChild('includecard', 1);
    }

    public function makeUrl($api, $type) {
        switch ($type) {
            case PmsOptions::REQUEST_TYPE_JSON:
                return $this->baseUrl . PmsOptions::REQUEST_TYPE_JSON . $api;
            case PmsOptions::REQUEST_TYPE_XML:
                return $this->baseUrl . PmsOptions::REQUEST_TYPE_XML . $api;
        }
        return '';
    }

    protected function jsonRequestWithRoomID($roomId) {
        $this->pmsCredentialsJSON['roomId'] = $roomId;
    }

    protected function jsonRequestWithBookingId($bookingID) {
        $this->pmsCredentialsJSON['bookId'] = $bookingID;
    }

    protected function jsonRequestWithMasterId($masterId) {
        $this->pmsCredentialsJSON['masterId'] = $masterId;
    }

    protected function jsonRequestWithArrivalFrom($date) {
        $this->pmsCredentialsJSON['arrivalFrom'] = $date;
    }

    protected function jsonRequestWithArrivalTo($date) {
        $this->pmsCredentialsJSON['arrivalTo'] = $date;
    }

    protected function jsonRequestWithDepartureFrom($date) {
        $this->pmsCredentialsJSON['departureFrom'] = $date;
    }

    protected function jsonRequestWithDepartureTo($date) {
        $this->pmsCredentialsJSON['departureTo'] = $date;
    }

    protected function jsonRequestWithModifiedSince($date) {
        $this->pmsCredentialsJSON['modifiedSince'] = $date;
    }

    /**
     * @param $shouldInclude boolean
     */
    protected function jsonRequestWithIncludeInvoice($shouldInclude) {
        $this->pmsCredentialsJSON['includeInvoice'] = $shouldInclude;
    }

    /**
     * @param $shouldInclude boolean
     */
    protected function jsonRequestWithIncludeInfoItems($shouldInclude) {
        $this->pmsCredentialsJSON['includeInfoItems'] = $shouldInclude;
    }

    protected function jsonRequestWithPropertyKey($key) {
        if($key != -1)
        $this->pmsCredentialsJSON['authentication']['propKey'] = $key;
    }

    protected function jsonRequestWithPropertyID($id) {
//        $this->pmsCredentialsJSON['authentication']['propId'] = $id;
    }

    private function jsonRequestWithInvalidCard(bool $bookingInvalidCard) {
        $this->pmsCredentialsJSON['bookingcomInvalidCard'] = $bookingInvalidCard;
    }

    private function jsonRequestWithBookingNoShow(bool $bookingNoShow) {
        $this->pmsCredentialsJSON['bookingcomNoShow'] = $bookingNoShow;
    }

    private function jsonRequestWithReportCancel(bool $bookingReportCancel) {
        $this->pmsCredentialsJSON['bookingcomReportCancel'] = $bookingReportCancel;
    }

    private function jsonRequestWithToken(string $token) {
        $this->pmsCredentialsJSON['token'] = $token;
    }

    /**
     * @param PmsOptions $options
     * @return SimpleXMLElement
     */
    public function setXMLOptions(PmsOptions $options) {

        if($options->includeCard)
            $this->xmlRequestForCard();
        if($options->bookingID)
            $this->xmlRequestWithBookingID($options->bookingID);
        if($options->masterID)
            $this->xmlRequestWithMasterID($options->masterID);
        if($options->propertyID)
            $this->xmlRequestWithPropertyID($options->propertyID);
        if($options->roomID)
            $this->xmlRequestWithRoomID($options->roomID);
        if($options->modifiedDate)
            $this->xmlRequestWithModifiedDate($options->modifiedDate);
        if($options->dateFrom)
            $this->xmlRequestWithDateFrom($options->dateFrom);
        if($options->dateTo)
            $this->xmlRequestWithDateTo($options->dateTo);

        return $this->pmsCredentialsXML;

    }

    /**
     * @param PmsOptions $options
     * @return array|null
     */
    public function setJSONOptions(PmsOptions $options) {

        if($options->roomID)
            $this->jsonRequestWithRoomID($options->roomID);
        if($options->bookingID)
            $this->jsonRequestWithBookingId($options->bookingID);
        if($options->masterID)
            $this->jsonRequestWithMasterId($options->masterID);
        if($options->arrivalFrom)
            $this->jsonRequestWithArrivalFrom($options->arrivalFrom);
        if($options->arrivalTo)
            $this->jsonRequestWithArrivalTo($options->arrivalTo);
        if($options->departureFrom)
            $this->jsonRequestWithDepartureFrom($options->departureFrom);
        if($options->departureTo)
            $this->jsonRequestWithDepartureTo($options->departureTo);
        if($options->modifiedSince)
            $this->jsonRequestWithModifiedSince($options->modifiedSince);
        if($options->includeInvoice)
            $this->jsonRequestWithIncludeInvoice($options->includeInvoice);
        if($options->includeInfoItems)
            $this->jsonRequestWithIncludeInfoItems($options->includeInfoItems);
        if($options->propertyKey)
            $this->jsonRequestWithPropertyKey($options->propertyKey);
        if($options->propertyID)
            $this->jsonRequestWithPropertyID($options->propertyID);
        if($options->bookingInvalidCard)
            $this->jsonRequestWithInvalidCard($options->bookingInvalidCard);
        if($options->bookingNoShow)
            $this->jsonRequestWithBookingNoShow($options->bookingNoShow);
        if($options->bookingReportCancel)
            $this->jsonRequestWithReportCancel($options->bookingReportCancel);
        if(isset($options->bookingToken))
            $this->jsonRequestWithToken($options->bookingToken);

        return $this->pmsCredentialsJSON;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getXmlBody() {
        return $this->pmsCredentialsXML;
    }

    /**
     * @return null|array
     */
    public function getJsonBody() {
        return $this->pmsCredentialsJSON;
    }

    /**
     * @param UserAccount $user
     * @param $dataClassObject
     * @param string $class
     * @param string $pmsName
     * @param null|array $whenDataUnderDifferentKey
     * @throws PmsExceptions
     */
    public function makeJsonDataForUpdate(UserAccount $user, $dataClassObject, string $class, string $pmsName, $whenDataUnderDifferentKey = null) {


        $this->makeAuthJSON($user);

        if($class === null)
            throw new PmsExceptions('Class path is null');

        if(!isset($pmsName))
            throw new PmsExceptions('PMS name not provided for parsing');

        if(!$dataClassObject instanceof $class)
            throw new PmsExceptions('Invalid dataObject type or wrong Class');

        if($dataClassObject == null)
            throw new PmsExceptions('Data object is null');

        $data = array();

        if(key_exists('json', $class::$M_KEYS[$pmsName])) {
            foreach ($class::$M_KEYS[$pmsName]['json'] as $key2Send => $cVar) {
                if (isset($dataClassObject->$cVar))
                    $data[$key2Send] = $dataClassObject->$cVar;
            }
        }

        if(key_exists('json_sub', $class::$M_KEYS[$pmsName])) {

            foreach($class::$M_KEYS[$pmsName]['json_sub'] as $subKey2Send => $subVar) {

                if(isset($dataClassObject->$subKey2Send) && is_array($dataClassObject->$subKey2Send)) {

                    for($i = 0; $i < count($dataClassObject->$subKey2Send); $i++) {

                        $sub = array();                       

                        foreach ($subVar['type']::$M_KEYS[$pmsName]['json'] as $key2Send => $cVar) {

                            if(!empty($dataClassObject->$subKey2Send[$i]) && is_object($dataClassObject->$subKey2Send[$i]) && !is_array($dataClassObject->$subKey2Send[$i])) {

                                if(isset($dataClassObject->$subKey2Send[$i]->$cVar))
                                    $sub[$key2Send] = $dataClassObject->$subKey2Send[$i]->$cVar;

                            } elseif(isset($dataClassObject->$subKey2Send[$i][$cVar])) {
                                $sub[$key2Send] = $dataClassObject->$subKey2Send[$i][$cVar];
                            }
                        
                        }

                        if(count($sub) > 0)
                            $data[$subKey2Send][] = $sub;

                    }
                }
            }
        }

        if($whenDataUnderDifferentKey != null) {
            foreach ($whenDataUnderDifferentKey as $key => $value)
                if($value == 'array')
                    $this->pmsCredentialsJSON[$key] = array($data);
                else
                    $this->pmsCredentialsJSON[$key] = $data;
        }
        else
            $this->pmsCredentialsJSON = array_merge($this->pmsCredentialsJSON, $data);

    }

    /**
     * @param UserAccount $user
     * @param $dataClassObject
     * @param string $class
     * @param string $pmsName
     * @param string $mainNode
     * @param string $childNode
     * @throws PmsExceptions
     */
    public function makeXmlDataForUpdate(UserAccount $user, $dataClassObject, string $class, string $pmsName, string $mainNode, string $childNode) {
        
        $this->makeAuthXML($user);

        if($class === null)
            throw new PmsExceptions('Class path is null');

        if(!isset($pmsName))
            throw new PmsExceptions('PMS name not provided for parsing');

        if(!$dataClassObject instanceof $class)
            throw new PmsExceptions('Invalid dataObject type or wrong Class');

        if($dataClassObject == null)
            throw new PmsExceptions('Data object is null');

        $xmlParent = $this->pmsCredentialsXML->addChild($mainNode);
        $xmlChild = $xmlParent->addChild($childNode);

        $this->setAttibutes($class, $pmsName, $dataClassObject, $xmlChild);
        $this->setChildren($class, $pmsName, $dataClassObject, $xmlChild);


        if(key_exists('xml_sub', $class::$M_KEYS[$pmsName])) {
            foreach ($class::$M_KEYS[$pmsName]['xml_sub'] as $key2Send => $subArrayChildren) {

                $classVar = $subArrayChildren['var'];

                if(isset($dataClassObject->$classVar) && is_array($dataClassObject->$classVar)) {

                    $subMain = $xmlChild->addChild($key2Send);

                    for($x = 0; $x < count($dataClassObject->$classVar); $x++) {
                        $subChild = $subMain->addChild($subArrayChildren['child']);
                        $this->setAttibutes($subArrayChildren['type'], $pmsName, $dataClassObject->$classVar[$x], $subChild);
                        $this->setChildren($subArrayChildren['type'], $pmsName, $dataClassObject->$classVar[$x], $subChild);
                    }

                }

                if(key_exists('single_elements', $subArrayChildren)) {
                    foreach($subArrayChildren['single_elements'] as $key2SendSE => $cVarSE) {
                        if(isset($dataClassObject->$cVarSE))
                            $subMain->addChild($key2SendSE, $dataClassObject->$cVarSE);
                    }
                }

            }
        }
    }

    /**
     * @param UserAccount $user
     * @param array $arrayOfObject
     * @param string $class
     * @param string $pmsName
     * @param string $mainNode
     * @param string $childNode
     * @throws PmsExceptions
     */
    public function makeXmlArrayDataForUpdate(UserAccount $user, array $arrayOfObject, string $class, string $pmsName, string $mainNode, string $childNode) {

        $this->makeAuthXML($user);

        if($class === null)
            throw new PmsExceptions('Class path is null');

        if(!isset($pmsName))
            throw new PmsExceptions('PMS name not provided for parsing');

        if($arrayOfObject == null)
            throw new PmsExceptions('Data object is null');

        if(count($arrayOfObject) == 0)
            throw new PmsExceptions('Data array is empty');

        $xmlParent = $this->pmsCredentialsXML->addChild($mainNode);

        foreach ($arrayOfObject as $dataClassObject) {

            $xmlChild = $xmlParent->addChild($childNode);
            $this->setAttibutes($class, $pmsName, $dataClassObject, $xmlChild);

            $this->setChildren($class, $pmsName, $dataClassObject, $xmlChild);


            if (key_exists('xml_sub', $class::$M_KEYS[$pmsName])) {
                foreach ($class::$M_KEYS[$pmsName]['xml_sub'] as $key2Send => $subArrayChildren) {

                    $classVar = $subArrayChildren['var'];

                    if (isset($dataClassObject->$classVar) && is_array($dataClassObject->$classVar)) {

                        $subMain = $xmlChild->addChild($key2Send);

                        for ($x = 0; $x < count($dataClassObject->$classVar); $x++) {
                            $subChild = $subMain->addChild($subArrayChildren['child']);
                            $this->setAttibutes($subArrayChildren['type'], $pmsName, $dataClassObject->$classVar[$x], $subChild);
                            $this->setChildren($subArrayChildren['type'], $pmsName, $dataClassObject->$classVar[$x], $subChild);
                        }

                    }

                    if (key_exists('single_elements', $subArrayChildren)) {
                        foreach ($subArrayChildren['single_elements'] as $key2SendSE => $cVarSE) {
                            if (isset($dataClassObject->$cVarSE))
                                $subMain->addChild($key2SendSE, $dataClassObject->$cVarSE);
                        }
                    }

                }
            }
        }
    }

    private function setChildren($class, $pmsName, $dataClassObject, &$xmlChild) {
        if(key_exists('xml', $class::$M_KEYS[$pmsName])) {
            foreach ($class::$M_KEYS[$pmsName]['xml'] as $key2Send => $cVar) {
                if (isset($dataClassObject->$cVar))
                    $xmlChild->addChild($key2Send, $dataClassObject->$cVar);
            }
        }
    }

    private function setAttibutes($class, $pmsName, $dataClassObject, &$xmlChild) {
        if(key_exists('xmlAttributes', $class::$M_KEYS[$pmsName])) {
            foreach ($class::$M_KEYS[$pmsName]['xmlAttributes'] as $key2Send => $cVar) {
                if (isset($dataClassObject->$cVar))
                    $xmlChild->addAttribute($key2Send, $dataClassObject->$cVar);
            }
        }
    }
}