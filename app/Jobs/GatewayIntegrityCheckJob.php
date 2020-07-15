<?php

namespace App\Jobs;

use App\Events\Emails\EmailEvent;
use App\PropertyInfo;
use App\Repositories\GenericEmailSMSWithContent\GenericEmailWithContent;
use App\UserPaymentGateway;
use Exception;
use App\UserAccount;
use App\System\PMS\PMS;
use App\Mail\GenericEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\System\PMS\Models\Property;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\System\PMS\Models\PmsOptions;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PaymentGateway\Models\Account;
use App\Repositories\Properties\Properties;
use App\System\PaymentGateway\Exceptions\GatewayException;


class GatewayIntegrityCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userAccount = null;
    private $propertyInfo = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserAccount $userAccount, PropertyInfo $propertyInfo = null)
    {
        self::onQueue('reauth');
        $this->userAccount = $userAccount;
        $this->propertyInfo = $propertyInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->checkGatewayConnectionIntegrity($this->userAccount, $this->propertyInfo);
    }

    public function checkGatewayConnectionIntegrity(UserAccount $userAccount, PropertyInfo $propertyInfo = null) {

        try {

            if($userAccount->id == 2) // Micasa Account.
                return;

            /**
             * @var $userPaymentGateway UserPaymentGateway
             */
            $userPaymentGateway = null;

            if($userAccount->user_payment_gateways == null)
                return;

            if($propertyInfo == null) {
                $userPaymentGateway = $userAccount->user_payment_gateways->where('is_verified', 1)
                ->where('status', 1)
                ->where('property_info_id', 0)
                ->where('last_sync', '<', Carbon::now()->subHours(config('db_const.sync_offsets.gateway-integrity-sync'))->toDateTimeString())
                ->first();

            } else {
                $userPaymentGateway = $userAccount->user_payment_gateways->where('is_verified', 1)
                ->where('status', 1)
                ->where('property_info_id', $propertyInfo->id)
                ->where('last_sync', '<', Carbon::now()->subHours(config('db_const.sync_offsets.gateway-integrity-sync'))->toDateTimeString())
                ->first();
            }

            if($userPaymentGateway == null)
                return;

            $account = null;

            try {

                $paymentGateway = new PaymentGateway();
                /**
                 * @var $account Account
                 */
                $account = $paymentGateway->getAccount($userPaymentGateway);

                $gateway = $userPaymentGateway->getGatewayObject();

                $gateway->displayName = $account->display_name;
                $gateway->companyName = $account->business_name;
                
                if($gateway->country != $account->country) {
                    $gateway->country = $account->country;
                    $userPaymentGateway->gateway = json_encode($gateway);
                }

            } catch(GatewayException $e) {
                Log::error($e->getMessage(), 
                    [
                        'user_account_id' => $userAccount->id, 
                        'File' => __FILE__, 
                        'function' => __FUNCTION__, 
                        'property_info' => $propertyInfo == null ? 'null' : $propertyInfo->id
                    ]);
            }
            
            $userPaymentGateway->last_sync = Carbon::now()->toDateTimeString();
            
            if($account == null) {
                $userPaymentGateway->is_verified = 0;
                event(new EmailEvent(config('db_const.emails.heads.gateway_disabled_auto_to_client.type'), $userPaymentGateway->id));
            }
            
            $userPaymentGateway->save();


        } catch(Exception $e) {
            Log::error($e->getMessage(), 
                [
                    'user_account_id' => $userAccount->id, 
                    'File' => __FILE__, 
                    'function' => __FUNCTION__, 
                    'property_info' => $propertyInfo == null ? 'null' : $propertyInfo->id,
                    'Stack' => $e->getTraceAsString()
                ]);
        }

    }

}
