<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/9/18
 * Time: 12:04 PM
 */

namespace App\System\PMS\BookingAutomation;


use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\UserAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\SeekException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BAClient {

    /**
     * @var Client
     */
    private $guzzle;
    /**
     * @var Errors
     */
    private $baErrors;
    /**
     * @var RequestParameters
     */
    private $requestParameters;

    public function __construct(Client $guzzle, Errors $baErrors, RequestParameters $requestParameters) {
        $this->guzzle = $guzzle;
        $this->baErrors = $baErrors;
        $this->requestParameters = $requestParameters;
    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $pmsOptions
     * @param $api
     * @return \SimpleXMLElement
     * @throws PmsExceptions
     */
    function postXML(UserAccount $user, PmsOptions $pmsOptions, $api) {

        $this->requestParameters->makeAuthXML($user);
        $this->requestParameters->setXMLOptions($pmsOptions);
        $url = $this->requestParameters->makeUrl($api, PmsOptions::REQUEST_TYPE_XML);
        $body = $this->requestParameters->getXmlBody();

        try {

            $options = ['headers' => ['Content-Type' => 'application/xml; charset=UTF8',], 'body' => $body->asXML(),];

            $response = $this->guzzle->post($url, $options);
            $content = $response->getBody();
            $content->rewind();
            $content = (string) $content->getContents();
//            header('Content-type: application/xml');
//            echo $content;
//            die();
            $xml = simplexml_load_string($content);

            if($pmsOptions->getFullXmlResponse || !$this->baErrors->hasErrorXML($xml)) {
                return $xml;

            } else {
                throw  new PmsExceptions($this->baErrors->getErrorMessageFromResponseXML($xml), $this->baErrors->getErrorCodeFromXMLResponse($xml));
            }

        } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            throw new PmsExceptions($e->getMessage(), $e->getResponse() != null ? $e->getResponse()->getStatusCode(): 0);
        }

    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param string $api
     * @param $dataClassObject
     * @param string $class ::class of data object
     * @param string $pmsName
     * @param string $mainNode
     * @param string $childNode
     * @return array|mixed
     * @throws PmsExceptions
     */
    function postXML_withData(UserAccount $user, PmsOptions $options, string $api, $dataClassObject, string $class, string $pmsName, string $mainNode, string $childNode) {
        $this->requestParameters->makeXmlDataForUpdate($user, $dataClassObject, $class, $pmsName, $mainNode, $childNode);
        return $this->postXML($user, $options, $api);
    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param string $api
     * @param array $dataClassObject
     * @param string $class ::class of data object
     * @param string $pmsName
     * @param string $mainNode
     * @param string $childNode
     * @return array|mixed
     * @throws PmsExceptions
     */
    function postXML_withArrayData(UserAccount $user, PmsOptions $options, string $api, array $dataClassObject, string $class, string $pmsName, string $mainNode, string $childNode) {
        $this->requestParameters->makeXmlArrayDataForUpdate($user, $dataClassObject, $class, $pmsName, $mainNode, $childNode);
        return $this->postXML($user, $options, $api);
    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param string $api
     * @param string $dataClassObject
     * @param string $class ::class of data object
     * @param string $pmsName
     * @param null|array $whenDataUnderDifferentKey
     * @return boolean|string
     * @throws PmsExceptions
     */
    function postJSON_withData(UserAccount $user, PmsOptions $options, string $api, $dataClassObject, string $class, string $pmsName, $whenDataUnderDifferentKey = null) {

        $this->requestParameters->makeJsonDataForUpdate($user, $dataClassObject, $class, $pmsName, $whenDataUnderDifferentKey);
        $this->requestParameters->setJSONOptions($options);
        $url = $this->requestParameters->makeUrl($api, PmsOptions::REQUEST_TYPE_JSON);
        $body = $this->requestParameters->getJsonBody();

        try {
            $response = $this->guzzle->post($url, [RequestOptions::JSON => $body]);

            $content = $response->getBody();
            $content->rewind();
            $content = (string) $content->getContents();
            $content = json_decode($content, true);

            if(!$this->baErrors->hasError($content)) {

                if(key_exists('bookingcomReportCancel', $content)) {
                    if($content['bookingcomReportCancel'] != 'ok' || $content['bookingcomReportCancel'][0] != 'ok') {
                        return is_array($content['bookingcomReportCancel']) ? $content['bookingcomReportCancel'][0] : $content['bookingcomReportCancel'];
                    }
                } elseif ($options->bookingInvalidCard) {
                    // {"success":"booking modified","bookingcomInvalidCard":"No card to report"}
                    return $content;
                }

                return true;

            } else {
                throw  new PmsExceptions($this->baErrors->getErrorMessageFromResponse($content), $this->baErrors->getErrorCodeFromJSONResponse($content));
            }

        } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            throw new PmsExceptions($e->getMessage(), $e->getResponse() != null ? $e->getResponse()->getStatusCode(): 0);
        }

    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param $api
     * @return array|mixed
     * @throws PmsExceptions
     */
    function postJSON(UserAccount $user, PmsOptions $options, $api) {

        $this->requestParameters->makeAuthJSON($user);
        $this->requestParameters->setJSONOptions($options);
        $url = $this->requestParameters->makeUrl($api, PmsOptions::REQUEST_TYPE_JSON);
        $body = $this->requestParameters->getJsonBody();

            try {
                $response = $this->guzzle->post($url, [RequestOptions::JSON => $body]);

                $content = $response->getBody();
                $content->rewind();
                $content = (string) $content->getContents();
                $content = json_decode($content, true);

                if(!$this->baErrors->hasError($content)) {

                    return $content;

                } else {
                    throw  new PmsExceptions($this->baErrors->getErrorMessageFromResponse($content), $this->baErrors->getErrorCodeFromJSONResponse($content));
                }

            } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
            BadResponseException | BadRequestHttpException | ClientException | ConnectException |
            SeekException $e) {

                throw new PmsExceptions($e->getMessage(), $e->getResponse() != null ? $e->getResponse()->getStatusCode(): 0);
                
            }



    }

}
