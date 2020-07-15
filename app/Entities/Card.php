<?php


namespace App\Entities;


class Card
{
        public $number = '';
        public $expiry = ''; /* Format:  05/22 (String)*/
        public $email = '';
        public $full_name = '';
        public $first_name = '';
        public $last_name = '';
        public $cvv = '';
        public $token = null;
}
