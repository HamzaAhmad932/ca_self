<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 12/21/18
 * Time: 2:31 PM
 */

namespace App\Exceptions;

use Exception;

class ClientSettingsException extends Exception 
{

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report() {

        // TODO: send error details to client

    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request){
        return response();
    }

}