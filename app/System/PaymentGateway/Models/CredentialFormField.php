<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/27/18
 * Time: 10:59 AM
 */

namespace App\System\PaymentGateway\Models;


use JsonSerializable;

class CredentialFormField implements JsonSerializable {

    /*
     * To be used as HTML would
     */
    const TYPE_TEXT = 'text';
    const TYPE_BUTTON = 'button';
    const STATE_HIDDEN = 'hidden';
    const STATE_SHOW = '';

    public $name;
    public $label;
    public $safe;
    public $value;
    public $type;
    public $state;
    public $url;
    public $desc;

    /**
     * CredentialFormField constructor.
     * @param string|null $name Backend Name of input field
     * @param string|null $value Value for input field
     * @param string|null $type Type of field text or button
     * @param string|null $state
     */
    public function __construct(string $name = null, string $value = null, string $type = null, string $state = null) {

        if($name !== null && $value !== null)
            if($name != '' && $value != '') {
                $this->name = $name;
                $this->value = $value;
            }

        if( !is_null($type) )
            $this->type = $type;
        else
            $this->type = self::TYPE_TEXT;

        if(!is_null($state))
            $this->state = $state;
        else
            $this->state = self::STATE_SHOW;

    }

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
            'name' => $this->name,
            'label' => $this->label,
            'safe' => $this->safe,
            'value' => $this->value ,
            'type' => $this->type,
            'state' => $this->state,
            'url' => $this->url,
            'desc' => $this->desc);
    }
}