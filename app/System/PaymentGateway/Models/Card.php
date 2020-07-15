<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/24/18
 * Time: 12:45 PM
 */

namespace App\System\PaymentGateway\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Log;

class Card extends CardMeta {

    public $amount;
    public $currency;
    public $cardNumber;
    public $expiryMonth;
    public $expiryYear;
    public $cvvCode = 0;
    public $firstName = '';
    public $lastName = '';

    public $order_id;
    public $token = null;
    public $type = '';

    /**
     * Set amount to be deducted from principal amount as platform fee.
     * @var float
     */
    public $applicationFee = 0.0;

    /**
     * Returns encrypted card data or empty string in case of encryption exception
     * @param Card $card
     * @return string
     */
    public static function encrypt(Card $card) {

        try {

            return encrypt(json_encode($card));

        } catch (EncryptException $e) {
            Log::error("Card Encrypt Error: " . $e->getMessage(), ['File'=>Card::class, 'stack'=>$e->getTraceAsString()]);

        } catch (\Exception $e) {
            Log::error("Card Encrypt Error: " . $e->getMessage(), ['File'=>Card::class, 'stack'=>$e->getTraceAsString()]);
        }

        return '';
    }


    /**
     * Returns decrypted card object or null in case of any decryption exception
     * @param string $encryptedCard
     * @return string|Card
     * string when there is some error or exception
     * card when successfully decrypted
     */
    public static function decrypt(string $encryptedCard) {

        if($encryptedCard == null)
            return null;

        $errorMessage = '';

        try {

            $decrypted = decrypt($encryptedCard);

            if($decrypted == null || $decrypted == '')
                return null;

            $data = json_decode($decrypted, true);

            $card = new Card();

            foreach ($data as $key => $value)
                $card->{$key} = $value;

            return $card;

        } catch (DecryptException $e) {
            Log::error("Card Decrypt Error: " . $e->getMessage(), ['File'=>Card::class, 'stack'=>$e->getTraceAsString()]);
            $errorMessage .= $e->getMessage() . "\n";

        } catch (\Exception $e) {
            Log::error("Card Decrypt Error: " . $e->getMessage(), ['File'=>Card::class, 'stack'=>$e->getTraceAsString()]);
            $errorMessage .= $e->getMessage() . "\n";
        }

        return $errorMessage;
    }

    /**
     * @return string
     */
    public function getLastFourDigits() {
        try {
            return substr($this->cardNumber, strlen($this->cardNumber)-4);
        } catch (\Exception $e) {

        }
        return '';
    }

    public function adjust_first_last_name_if_empty_any() {

        if(empty($this->firstName)) {
            if (!empty($this->lastName))
                $this->firstName = $this->lastName;

        } elseif(empty($this->lastName)) {
            if(!empty($this->firstName))
                $this->lastName = $this->firstName;
        }
    }

    public function isNameSet() {
        $this->adjust_first_last_name_if_empty_any();
        return !empty($this->firstName) && !empty($this->lastName);
    }

    public function getName() {
        $this->adjust_first_last_name_if_empty_any();
        return $this->firstName . ' ' . $this->lastName;
    }

    public function setCountry($country) {
        try {
            if (!empty($country)) {

                $countries = json_decode(file_get_contents(__DIR__ . '/../../../Json/country_names_from_abbr_code.json'), true);

                if (strlen($country) == 2 && key_exists(strtoupper($country), $countries)) {
                    $this->country = $country;
                    return;
                }

                $country = ucwords($country);
                $code = array_search($country, $countries);

                if($code !== false)
                    $this->country = $code;

            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => Card::class]);
        }
    }
}
