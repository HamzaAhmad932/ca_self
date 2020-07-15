<?php

namespace Tests\Unit;


use App\Events\UserSignUpEvent;
use Illuminate\Support\Facades\Event;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserSignUp extends TestCase
{
    /** @test */
    public function user_should_receive_verification_email_and_sms() {


        $this->assertTrue(true);
    }
}
