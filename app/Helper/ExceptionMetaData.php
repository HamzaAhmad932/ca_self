<?php


namespace App\Helper;

use App\ExceptionLog;
use Exception;
use Illuminate\Support\Facades\Log;

class ExceptionMetaData {

    private $data = array();
    /**
     * @var Exception
     */
    private $exception;

    private $credit_card_auth_id = 0;
    private $credit_card_info_id = 0;
    private $transaction_init_id = 0;
    private $booking_pms_id = 0;
    private $booking_info_id = 0;
    private $user_account_id = 0;
    private $user_id = 0;

    public function __construct(Exception $exception) {
        $this->exception = $exception;
    }

    public function save() {
        try {
            ExceptionLog::create([
                'message' => $this->exception->getMessage(),
                'stack_trace' => $this->exception->getTraceAsString(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'user_id' => $this->user_id,
                'user_account_id' => $this->user_account_id,
                'booking_info_id' => $this->booking_info_id,
                'booking_pms_id' => $this->booking_pms_id,
                'meta_data' => json_encode($this->data),
                ]);
        } catch (Exception $ee) {
            Log::notice('Exception in Exception Logger: ' . $ee->getMessage());
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setMetaData(string $key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId) {
        $this->user_id = $userId;
        $this->data['user_id'] = $userId;
        return $this;
    }

    /**
     * @param int $userAccountId
     * @return $this
     */
    public function setUserAccountId(int $userAccountId) {
        $this->user_account_id = $userAccountId;
        $this->data['user_account_id'] = $userAccountId;
        return $this;
    }

    /**
     * @param int $bookingInfoId
     * @return $this
     */
    public function setBookingInfoId(int $bookingInfoId) {
        $this->booking_info_id = $bookingInfoId;
        $this->data['booking_info_id'] = $bookingInfoId;
        return $this;
    }

    /**
     * @param int $bookingPmsId
     * @return $this
     */
    public function setBookingPmsId(int $bookingPmsId) {
        $this->booking_pms_id = $bookingPmsId;
        $this->data['booking_pms_id'] = $bookingPmsId;
        return $this;
    }

    /**
     * @param int $transactionInitId
     * @return $this
     */
    public function setTransactionInitId(int $transactionInitId) {
        $this->transaction_init_id = $transactionInitId;
        $this->data['transaction_init_id'] = $transactionInitId;
        return $this;
    }

    /**
     * @param int $creditCardInfoId
     * @return $this
     */
    public function setCreditCardInfoId(int $creditCardInfoId) {
        $this->credit_card_info_id = $creditCardInfoId;
        $this->data['credit_card_info_id'] = $creditCardInfoId;
        return $this;
    }

    /**
     * @param int $creditCardAuthId
     * @return $this
     */
    public function setCreditCardAuthId(int $creditCardAuthId) {
        $this->credit_card_auth_id = $creditCardAuthId;
        $this->data['credit_card_auth_id'] = $creditCardAuthId;
        return $this;
    }

}