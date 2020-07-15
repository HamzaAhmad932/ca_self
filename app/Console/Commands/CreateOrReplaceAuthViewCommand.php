<?php

namespace App\Console\Commands;

use App\TransactionInit;
use Illuminate\Console\Command;
use App\CreditCardAuthorization;
use Illuminate\Support\Facades\DB;
use App\Repositories\Settings\PaymentTypeMeta;

class CreateOrReplaceAuthViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplaceAuthView';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create or replace DB view for auth job';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("<fg=yellow>creating or replacing credit card reauthorizing view\n");

        $payment_type_meta =  new PaymentTypeMeta();
        $sd_auto_auth_type = $payment_type_meta->getSecurityDepositAutoAuthorize();
        $cc_auto_auth_type = $payment_type_meta->getCreditCardAutoAuthorize();

        DB::statement('
        CREATE OR REPLACE VIEW auth_view 
        AS
        select 
            cca.*, 
            cc_info.id as credit_card_info_id, cc_info.customer_object, bi.pms_id as pms_form_id, pr.status as property_status, 
            ubs.booking_source_form_id as user_booking_source_id, upg.id as user_payment_gateway_id,
            CASE WHEN EXISTS (select id from transaction_inits where type = "'.TransactionInit::TRANSACTION_TYPE_CHARGE.'" and booking_info_id = bi.id and payment_status = "'.TransactionInit::PAYMENT_STATUS_SUCCESS.'" LIMIT 1) THEN 1 ELSE 0 END as is_any_paid_transaction_found
        from 
            credit_card_authorizations cca
            
            INNER JOIN user_accounts as ua on cca.user_account_id = ua.id and  ua.status = 1
            
            INNER JOIN booking_infos as bi on cca.booking_info_id = bi.id
            
            INNER JOIN property_infos as pr on bi.property_info_id = pr.id and pr.status=1
            
            INNER JOIN credit_card_infos as cc_info on cc_info.id = (select id from credit_card_infos as cc where cc.booking_info_id = bi.id and is_vc = IF(bi.is_vc = "VC",1,0) and auth_token != ""  ORDER BY id desc LIMIT 1)
            
            INNER JOIN user_payment_gateways as upg on upg.property_info_id = IF(pr.use_pg_settings = 1, pr.id, 0)
            and upg.user_account_id = cca.user_account_id and upg.is_verified = 1
            
            INNER JOIN user_booking_sources as ubs on ubs.property_info_id = IF(pr.use_bs_settings = 1, pr.id, 0) and 
            ubs.user_account_id = cca.user_account_id and booking_source_form_id = (select id from booking_source_forms where pms_form_id = bi.pms_id and channel_code = bi.channel_code) and 
            ubs.status = 1
        WHERE
            cca.attempts < '.CreditCardAuthorization::TOTAL_ATTEMPTS.' AND
            cca.type in ('.$sd_auto_auth_type.', '.$cc_auto_auth_type.') AND
            cc_info.auth_token != "" AND
            (cca.status = '.CreditCardAuthorization::STATUS_PENDING.' AND cca.due_date <= NOW()) OR
            (cca.status = '.CreditCardAuthorization::STATUS_REATTEMPT.' AND cca.next_due_date <= NOW()) OR
            (cca.status = '.CreditCardAuthorization::STATUS_ATTEMPTED.' AND cca.is_auto_re_auth = 1 AND cca.next_due_date <= NOW()) 
        ');
    }
}
