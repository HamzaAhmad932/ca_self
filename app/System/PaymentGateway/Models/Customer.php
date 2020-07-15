<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 11/7/18
 * Time: 2:39 PM
 */

namespace App\System\PaymentGateway\Models;


class Customer {

    public function __construct(string $jsonFromDB = null) {

        if($jsonFromDB != null) {
            $res = json_decode($jsonFromDB, true);
            $this->token = $res['token'];
            $this->created_at = $res['created_at'];
            $this->updated_at = $res['updated_at'];
            $this->email = $res['email'];
            $this->data = $res['data'];
            $this->last_four_digits = $res['last_four_digits'];
            $this->first_six_digits = $res['first_six_digits'];
            $this->card_type = $res['card_type'];
            $this->first_name = $res['first_name'];
            $this->last_name = $res['last_name'];
            $this->month = $res['month'];
            $this->year = $res['year'];
            $this->fullResponse = $res['fullResponse'];
            $this->default_source = $res['default_source'];
            $this->state = $res['state'];
            $this->succeeded = $res['succeeded'];
            $this->message = $res['message'];

            if(key_exists('three_d_secure_usage', $res))
                $this->three_d_secure_usage = $res['three_d_secure_usage'];

            if(key_exists('status', $res))
                $this->status = $res['status'];

            if(key_exists('payment_method', $res))
                $this->payment_method = $res['payment_method'];
        }

    }

    public $token = '';
    public $created_at = '';
    public $updated_at = '';
    public $email = '';
    public $data = '';
    public $last_four_digits = '';
    public $first_six_digits = '';
    public $card_type = '';
    public $first_name = '';
    public $last_name = '';
    public $month = '';
    public $year = '';
    public $default_source;

    public $three_d_secure_usage = false;
    /**
     * succeeded when customer object is ready to use.
     * requires_confirmation when guest needs to authorize it.
     * @var string
     */
    public $status = 'succeeded';
    /** For stripe new API
     * @var string
     */
    public $payment_method = null;

    /**
     * @var bool $succeeded
     */
    public $succeeded = false;

    /**
     * @var string $state
     */
    public $state = '';

    /**
     * @var string $message
     */
    public $message = '';

    // Server response as json
    public $fullResponse = '';

}