<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/17/18
 * Time: 2:12 PM
 */

namespace App\System\PMS\Models;


class Account {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'id' => 'id',
                'masterId' => 'masterId',
                'timezone' => 'timezone',
                'subaccounts' => 'subAccounts'

            ),
            'json_single_elements' => array(
                'RESPONSE_KEY' => 'usage', // json key in main response
                'numProperties' => 'numberOfProperties',
                'numRooms' => 'numberOfRooms',
                'numActivities' => 'numberOfActivities',
                'numLinks' => 'numberOfLinks'
            ),
            'xmlAttributes' => array(
                'id' => 'id',
                'action' => 'action'
            ),
            'xml' => array(
                'lang' => 'language',
                'email' => 'email',
                'lastActive' => 'lastActive'
            )
        )
    );

    public $id;
    public $masterId;
    public $timezone;
    public $numberOfProperties;
    public $numberOfRooms;
    public $numberOfActivities;
    public $numberOfLinks;

    public $language;
    public $lastActive;
    public $email;

    /**
     * @var string
     */
    public $subAccounts = null;

    /**
     * This function works for json request only.
     * @return array of class Account
     */
    public function getSubAccount() {

        $accounts = array();

        $data = is_array($this->subAccounts) ? $this->subAccounts : json_decode($this->subAccounts, true);

        foreach ($data as $d)
            if(key_exists('subaccounts', $d))
                $accounts = array_merge($accounts, $this->parseSubAccount($d['subaccounts']));

        return $accounts;
    }

    private function parseSubAccount(array $subAccount) {
        $accounts = array();
        foreach($subAccount as $a) {
            $account = new Account();
            $account->id = $a['id'];
            $account->timezone = $a['timezone'];
            $accounts[] = $account;
            if(count($a['subaccounts']) > 0)
                $accounts = array_merge($accounts, $this->parseSubAccount($a['subaccounts']));
        }
        return $accounts;
    }
}