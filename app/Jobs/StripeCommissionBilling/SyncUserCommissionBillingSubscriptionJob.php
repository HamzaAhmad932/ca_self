<?php

namespace App\Jobs\StripeCommissionBilling;


use App\UserAccount;
use \Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\System\StripeCommissionBilling\StripeCommissionBillingTrait;


class SyncUserCommissionBillingSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use StripeCommissionBillingTrait;

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
        /**
         * @var UserAccount $user_accounts
         */
        $user_accounts = resolve(UserAccount::class)
        ->where('plan_attached_status_last_sync', '<', now()->subHours(4)->toDateTimeString())
        ->where('stripe_customer_id', '!=', null)->where('integration_completed_on', '!=', null)
        ->orderby('plan_attached_status_last_sync', 'asc')->take(15)->get();

        foreach ($user_accounts as $user_account) {
            try {
                $this->getUserSubscribedPlans($user_account);
            } catch (Exception $exception) {
                Log::error($exception->getMessage(), ['userAccountId' => $user_account->id,
                    'stackTrace' => $exception->getTraceAsString()]);
            }
        }
    }
}
