<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TestCreditCardAuthorizationSeeder;
use TestCreditCardSeeder;
use TestGuestCommunicationSeeder;
use TestGuestDataSeeder;
use TestGuestImagesSeeder;
use TestPropertySeeder;
use TestRefundDetailsSeeder;
use TestTransactionDetailSeeder;
use TestTransactionInitSeeder;
use TestUpsellOrdersSeeder;
use TestUpsellSeeder;
use TestUpsellTypeSeeder;
use TestUserAccountSeeder;

class RunAllTestSeedersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $TestUserAccountSeeder = new TestUserAccountSeeder();
        $TestUserAccountSeeder->run();

        $TestPropertySeeder = new TestPropertySeeder();
        $TestPropertySeeder->run();

        $TestUpsellTypeSeeder = new TestUpsellTypeSeeder();
        $TestUpsellTypeSeeder->run();

        $TestUpsellSeeder = new TestUpsellSeeder();
        $TestUpsellSeeder->run();

        $TestUpsellOrdersSeeder = new TestUpsellOrdersSeeder();
        $TestUpsellOrdersSeeder->run();

        $TestTransactionInitSeeder = new TestTransactionInitSeeder();
        $TestTransactionInitSeeder->run();

        $TestTransactionDetailSeeder = new TestTransactionDetailSeeder();
        $TestTransactionDetailSeeder->run();

        $TestRefundDetailsSeeder = new TestRefundDetailsSeeder();
        $TestRefundDetailsSeeder->run();

        $TestCreditCardSeeder = new TestCreditCardSeeder();
        $TestCreditCardSeeder->run();

        $TestCreditCardAuthorizationSeeder = new TestCreditCardAuthorizationSeeder();
        $TestCreditCardAuthorizationSeeder->run();

        $TestGuestDataSeeder = new TestGuestDataSeeder();
        $TestGuestDataSeeder->run();

        $TestGuestImagesSeeder = new TestGuestImagesSeeder();
        $TestGuestImagesSeeder->run();

        $TestGuestCommunicationSeeder = new TestGuestCommunicationSeeder();
        $TestGuestCommunicationSeeder->run();
    }
}
