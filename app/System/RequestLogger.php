<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 3/20/19
 * Time: 9:55 AM
 */

namespace App\System;


use App\PmsRequestLogs;
use App\System\PMS\PMS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Psy\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class RequestLogger implements LoggerInterface {

    /**
     * Log message keys and there description
     */
    const KEYS = [
        "request"           => "Full HTTP request message",
        "response"          => "Full HTTP response message",
        "ts"                => "ISO 8601 date in GMT",
        "date_common_log"   => "Apache common log date using the configured timezone.",
        "host"              => "Host of the request",
        "method"            => "Method of the request",
        "uri"               => "URI of the request",
        "version"           => "Protocol version",
        "target"            => "Request target of the request (path + query + fragment)",
        "hostname"          => "Hostname of the machine that sent the request",
        "code"              => "Status code of the response (if available)",
        "phrase"            => "Reason phrase of the response  (if available)",
        "error"             => "Any error messages (if available)",
        "req_headers"       => "Request headers",
        "res_headers"       => "Response headers",
        "req_body"          => "Request body",
        "res_body"          => "Response body",
    ];

    const MESSAGE_SIMPLE = 'req_headers: {req_headers} <br><br> req_body: {req_body} <br><br> res_headers: {res_headers} <br><br> res_body: {res_body}';

    /**
     * @var array
     */
    private $metaData;

    public function __construct(array $metaData) {
        $this->metaData = $metaData;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        $this->_log($message, $context, 'EMERGENCY');
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        $this->_log($message, $context, 'ALERT');
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        $this->_log($message, $context, 'CRITICAL');
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        $this->_log($message, $context, 'ERROR');
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        $this->_log($message, $context, 'WARNING');
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        $this->_log($message, $context, 'NOTICE');
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->_log($message, $context, 'INFO');
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        $this->_log($message, $context, 'DEBUG');
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->_log($message, $context, $level);
    }

    private function _log($message, array $context = array(), $level = '') {

        try {

            $this->metaData[PMS::META_API_RESPONSE_TIME] = Carbon::now('GMT')->toDateTimeString();

            if($this->metaData[PMS::META_PMS_FUNCTION] == 'fetch_card_for_booking')
                $this->metaData[PMS::META_REQUEST_FULL_LOG] = encrypt($message);
            else
                $this->metaData[PMS::META_REQUEST_FULL_LOG] = $message;

            PmsRequestLogs::create([
                'user_account_id' => $this->metaData[PMS::META_USER_ACCOUNT_ID],
                'pms_form_id' => $this->metaData[PMS::META_PMS_FORM_ID],
                'user_name' => $this->metaData[PMS::META_USER_ACCOUNT_NAME],
                'pms_function' => $this->metaData[PMS::META_PMS_FUNCTION],
                'meta_data' => json_encode($this->metaData)
            ]);

        } catch (FatalThrowableError $e) {
            Log::notice("Request Logger: " . $e->getMessage());
        } catch (FatalErrorException $e) {
            Log::notice("Request Logger: " . $e->getMessage());
        } catch (\Symfony\Component\Debug\Exception\FatalErrorException $e) {
            Log::notice("Request Logger: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::notice("Request Logger: " . $e->getMessage());
        }

    }
}