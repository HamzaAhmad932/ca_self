<?php

use App\Helper\ExceptionMetaData;
use Illuminate\Support\Facades\Log;

/**
 * @param Exception $e
 * @return ExceptionMetaData
 */
function log_exception(Exception $e) {
    return new ExceptionMetaData($e);
}