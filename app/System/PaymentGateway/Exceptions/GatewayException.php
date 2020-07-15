<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/29/18
 * Time: 12:40 PM
 */

namespace App\System\PaymentGateway\Exceptions;


use Exception;

class GatewayException extends Exception {

    private $_declineCode = '';
    private $_description = '';
    private $_nextStep = '';
    private $_exceptionType = '';
    private $_httpStatus = 0;
    private $_generalCode = '';
    private $retryAble = true;
    private $reportToGuest = true;

    const ERROR_INSUFFICIENT_FUNDS = 'insufficient_funds';

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report() {

//        if($this->getCode() === 404)
//            $this->message = "API not Found 404";

        // dd($this->getMessage());
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
     * @return string
     */
    public function getDeclineCode()
    {
        return $this->_declineCode;
    }

    /**
     * @param string $declineCode
     */
    public function setDeclineCode($declineCode)
    {
        $this->_declineCode = $declineCode;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if(empty($this->_description))
            return $this->getMessage();
        return $this->_description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {
        $this->_description = $Description;
    }

    /**
     * @return string
     */
    public function getNextStep()
    {
        return $this->_nextStep;
    }

    /**
     * @param string $nextStep
     */
    public function setNextStep($nextStep)
    {
        $this->_nextStep = $nextStep;
    }

    /**
     * @return string
     */
    public function getExceptionType()
    {
        return $this->_exceptionType;
    }

    /**
     * @param string $exceptionType
     */
    public function setExceptionType($exceptionType)
    {
        $this->_exceptionType = $exceptionType;
    }

    /**
     * @return int
     */
    public function getHttpStatus()
    {
        return $this->_httpStatus;
    }

    /**
     * @param int $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->_httpStatus = $httpStatus;
    }

    /**
     * @return string
     */
    public function getGeneralCode()
    {
        return $this->_generalCode;
    }

    /**
     * @param string $generalCode
     */
    public function setGeneralCode($generalCode)
    {
        $this->_generalCode = $generalCode;
    }

    /**
     * @return bool
     */
    public function isRetryAble()
    {
        return $this->retryAble;
    }

    /**
     * @param bool $retryAble
     */
    public function setRetryAble($retryAble)
    {
        $this->retryAble = $retryAble;
    }

    /**
     * @return bool
     */
    public function isReportToGuest()
    {
        return $this->reportToGuest;
    }

    /**
     * @param bool $reportToGuest
     */
    public function setReportToGuest($reportToGuest)
    {
        $this->reportToGuest = $reportToGuest;
    }



}