<?php

namespace App\Jobs\StripeCommissionBilling;

use App\Events\Emails\EmailEvent;
use App\Mail\GenericEmail;
use App\System\StripeCommissionBilling\StripeCommissionBilling;
use App\UserAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class BillingDetailsRequiredReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var UserAccount $userAccount
         */
       $userAccounts = resolve( UserAccount::class)->where([
       ['billing_card_required_reminder_due_date', '<=', Carbon::now()->toDateTimeString()],
       ['status', 1], ['billing_reminder_attempts', '<', UserAccount::TOTAL_BILLING_REMIND_EMAIL_ATTEMPTS]])->get();
        $billingCommission = resolve(StripeCommissionBilling::class);

        foreach ($userAccounts as $userAccount) {
            $informFlag = $billingCommission->isDefaultPlanAttachedToCustomer($userAccount)
                ? $userAccount->sd_activated_on != null : true;
            if ($informFlag) {
                event(new EmailEvent(config('db_const.emails.heads.missing_billing_info.type'), $userAccount->id ));

                $userAccount->billing_reminder_attempts = $userAccount->billing_reminder_attempts + 1;
            }
            $userAccount->billing_card_required_reminder_due_date = now()->addDays(UserAccount::NEXT_REMINDER_EMAIL_DAYS)->toDateTimeString();
            $userAccount->save();
        }
    }
}
