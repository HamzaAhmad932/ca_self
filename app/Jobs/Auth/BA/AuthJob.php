<?php

namespace App\Jobs\Auth\BA;

use App\BAModels\AuthView;
use Illuminate\Bus\Queueable;
use App\CreditCardAuthorization;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Services\Settings\PaymentRules;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AuthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AuthJobsHelperTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('reauth');
        $this->init_helper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        AuthView::whereIn('pms_form_id', [1, 6]) //PMS_FORM_ID for BookingAutomation and Beds24
        ->where('status', CreditCardAuthorization::STATUS_PENDING)
        ->chunk(15, function ($records) {

            foreach ($records as $record) {

                try {

                    if(!$record->is_auto_re_auth) {
                        // Filtering out security damage deposit auth, only they are performed even payments had been made.
                        if ($record->type != $this->SDAutoAuthType && $record->type != $this->SDManualAuthType) {
                            if ($record->is_any_paid_transaction_found == 1) {
                                $this->voidAuthRecord($record->credit_card_authorization);
                                continue;
                            }
                        }
                    }

                    if($this->isCheckoutDatePassed($record->booking_info, $record, $record->user_payment_gateway)) {
                        continue;
                    }

                    /** Set Card instance to auth */
                    $card = $this->getCard($record);

                    /** Attempt to Authorize */
                    $this->authorizeNow($record, $card);


                }catch (\Exception $e){

                    log_exception_by_exception_object($e);
                }
            }
        });
    }
}
