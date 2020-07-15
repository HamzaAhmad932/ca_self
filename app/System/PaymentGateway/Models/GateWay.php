<?php


namespace App\System\PaymentGateway\Models;

use App\System\PaymentGateway\Spreedly\ParseSpreedly;
use JsonSerializable;

class GateWay implements JsonSerializable {

    const CHARACTERISTICS_3DS_PURCHASE = '3dsecure_purchase';
    const CHARACTERISTICS_3DS_AUTHORIZE = '3dsecure_authorize';

    /**
     * GateWay constructor.
     * @param string|null $serializedJson
     */
    public function __construct(string $serializedJson = null) {

        if($serializedJson !== null && is_string($serializedJson)) {

            $gw = json_decode($serializedJson, true);
            $parse = new ParseSpreedly();
            $parse->parseSingleGateway($gw, $this);

        }

    }

    /**
     * @var null|string
     */
    public $statement_descriptor = null;

    /**
     * @var string
     */
    public $gatewayType;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $authModeType;

    /**
     * @var string
     */
    public $authModeName;

    /**
     * @var string
     */
    public $homepage;

    /**
     * Also as Business Name
     * @var string
     */
    public $companyName;

    /**
     * @var array CredentialFormField
     */
    public $credentials = array();

    /**
     * @var array of string
     */
    public $characteristics = array();

    /**
     * @var array of string
     */
    public $paymentMethods = array();

    /**
     * @var array of string
     */
    public $gatewaySpecificFields = array();

    /**
     * @var array of string
     */
    public $supportedCountries = array();

    /**
     * @var array of string
     */
    public $supportedCardTypes = array();

    /**
     * @var array of string
     */
    public $regions = array();

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string datetime
     */
    public $created_at;

    /**
     * @var string datetime
     */
    public $updated_at;

    /**
     * @var bool
     */
    public $redacted = true;

    /**
     * @var bool
     */
    public $isStripeConnect = false;

    /**
     * @var bool
     */
    public $isStripeExpress = false;

    /**
     * @var string
     */
    public $country = '';
    
    /**
     * @var string
     */
    public $displayName;

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array(
            'gateway_type' => $this->gatewayType,
            'name' => $this->name,
            'auth_modes' => array([
                'auth_mode_type' => $this->authModeType,
                'name' => $this->authModeName,
                'credentials' => $this->credentials,
                ]
            ),
            'characteristics' => $this->characteristics,
            'payment_methods' => $this->paymentMethods,
            'gateway_specific_fields' => $this->gatewaySpecificFields,
            'supported_countries' => $this->supportedCountries,
            'supported_cardtypes' => $this->supportedCardTypes,
            'regions' => $this->regions,
            'homepage' => $this->homepage,
            'company_name' =>$this->companyName,
            'token' => $this->token,
            'description' => $this->description,
            'state' => $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'redacted' => $this->redacted,
            'isStripeConnect' => $this->isStripeConnect,
            'isStripeExpress' => $this->isStripeExpress,
            'statement_descriptor' => $this->statement_descriptor,
            'country' => $this->country,
            'display_name' => $this->displayName
        );
    }

}