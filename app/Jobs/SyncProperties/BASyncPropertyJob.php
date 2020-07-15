<?php

namespace App\Jobs\SyncProperties;

use App\Jobs\GatewayIntegrityCheckJob;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Account;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class BASyncPropertyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BASyncPropertyJobHelper;
    /**
     * @var int
     */
    private $user_account_id;

    /**
     * BASyncPropertiesJob constructor.
     * Pass User Account Id to get SyncProperties for BA specific User Account
     * @param int $user_account_id
     */
    public function __construct(int $user_account_id = 0)
    {

        $this->user_account_id = $user_account_id;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        /**
         * @var $user_account UserAccount
         * @var $pms_account Account
         *
         */
        $user_accounts = $this->userAccounts($this->user_account_id);

        foreach ($user_accounts as $user_account) {
            try {

                GatewayIntegrityCheckJob::dispatch($user_account);

                if ($user_account->activeProperties->count() || !empty($this->user_account_id)) {

                    $pms = new PMS($user_account);
                    $pmsOptions = new PmsOptions();
                    $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

                    $pms_account = $pms->fetch_user_account($pmsOptions)[0];

                    $account_time_zones = collect($pms_account->getSubAccount())->pluck('timezone', 'id')->toArray();
                    $account_time_zones[$pms_account->id] = $pms_account->timezone;

                    $pms_properties = $pms->fetch_properties_json_xml($pmsOptions) ?? [];

                    $this->updateBA_user_properties($user_account, $account_time_zones, $pms_properties);

                    $user_account->update(
                        [
                            'time_zone' => $pms_account->timezone,
                            'count_unauthorized_property_sync' => 0, // Reset exception count
                            'user_account_id_at_pms' => $pms_account->id,
                            'last_properties_synced' => Carbon::now()->toDateTimeString(),

                        ]
                    );
                }

            } catch (PmsExceptions $e) {
                $this->handlePMSException($user_account, $e);
                log_exception_by_exception_object($e, ['UserAccount' => $user_account->id]);

                // Throw Exception If Job Custom Dispatch by Controller
                if ($this->isCustomDispatch()) {
                    throw new \Exception($e->getCADefineMessage());
                }

            } catch (Exception $e) {
                log_exception_by_exception_object($e, ['UserAccount' => $user_account->id]);
            }
        }
    }

    /**
     * @return bool
     */
    private function isCustomDispatch()
    {
        return !empty($this->user_account_id);
    }

    /**
     * @param int $user_account_id
     * @return mixed
     */
    private function userAccounts(int $user_account_id = 0)
    {
        $user_accounts = UserAccount::select('user_accounts.*')
            ->join('user_pms', 'user_pms.user_account_id', '=', 'user_accounts.id')
            ->whereIn('pms_form_id', [1, 6])
            ->where(
                [
                    ['user_pms.is_verified', 1], //PMS Verified
                    ['account_type', 1],
                ]
            )->with('activeProperties');


        if (!empty($user_account_id)) {
            return $user_accounts->where('user_accounts.id', $user_account_id)->get();
        } else {
            return $user_accounts
                ->where('integration_completed_on', '!=', null)
                ->where('status', config('db_const.user_account.status.active.value'))
                ->where('last_properties_synced', '<', Carbon::now()->subHours(config('db_const.sync_offsets.integration-sync')))
                ->orWhere('last_properties_synced', null)
                ->orderBy('last_properties_synced', 'asc')
                ->get();
        }

    }

}
