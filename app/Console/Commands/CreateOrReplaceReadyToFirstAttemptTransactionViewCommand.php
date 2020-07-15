<?php

namespace App\Console\Commands;

use App\TransactionInit;
use Illuminate\Console\Command;

class CreateOrReplaceReadyToFirstAttemptTransactionViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplaceReadyToFirstAttemptTransactionView';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or Replace SQL View';

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
     */
    public function handle()
    {

        $this->line("<fg=yellow>Creating ready_to_first_attempt_transactions VIEW.....  \n");

        
        \DB::statement("
            CREATE OR REPLACE VIEW ready_to_first_attempt_transactions
            AS
            SELECT tr.*, 
            
            bi.pms_booking_id, bi.guest_name, bi.guest_last_name, bi.guest_address, bi.guest_country, 
            bi.guest_post_code, bi.guest_phone, bi.guestMobile,
            bi.pms_id as pms_form_id, bi.is_vc,
 
 
            pr.id as property_info_id, pr.pms_property_id, pr.property_key, pr.currency_code,
            
            cc_info.f_name, cc_info.card_name, cc_info.l_name, cc_info.cc_last_4_digit, cc_info.cc_exp_month,
            cc_info.cc_exp_year, cc_info.customer_object, cc_info.auth_token as credit_card_auth_token,
            cc_info.id as credit_card_info_id,

            (select transaction_obj  from credit_card_authorizations as cc_auth where 
            cc_auth.booking_info_id = bi.id and cc_auth.type 
            in (
               '". config('db_const.credit_card_authorizations.credit_card_auto_authorize')."',
               '". config('db_const.credit_card_authorizations.credit_card_manual_authorize')."'
            )  and cc_auth.status = '1' limit 1) as auth_transaction_obj, 
            
           
            ubs.id as user_booking_source_id,
             
          
            upg.id as user_payment_gateway_id
            FROM transaction_inits as tr
         
            INNER JOIN user_accounts as ua on tr.user_account_id = ua.id and  ua.status = 1
            
            INNER JOIN booking_infos as bi on tr.booking_info_id = bi.id  
            
            INNER JOIN property_infos as pr on bi.property_info_id = pr.id and pr.status=1
            
            INNER JOIN user_payment_gateways as upg on upg.property_info_id = IF(pr.use_pg_settings = 1, pr.id, 0)
            and upg.user_account_id = tr.user_account_id and upg.is_verified = 1
            
            INNER JOIN user_booking_sources as ubs on ubs.property_info_id = IF(pr.use_bs_settings = 1, pr.id, 0) 
            and ubs.user_account_id = tr.user_account_id and booking_source_form_id =
            (select id from booking_source_forms where pms_form_id = bi.pms_id and channel_code = bi.channel_code)
            and ubs.status = 1
 
            INNER JOIN credit_card_infos as cc_info on cc_info.id = 
            (select id from credit_card_infos as cc where cc.booking_info_id = bi.id and is_vc = IF(bi.is_vc = 'VC',1,0) 
             and auth_token != ''  ORDER BY id desc LIMIT 1)
               
            WHERE tr.final_tick = 0 and tr.due_date <=  NOW() and tr.attempt = 0  
            and tr.type = '". TransactionInit::TRANSACTION_TYPE_CHARGE ."' and tr.lets_process = 1 
            and tr.in_processing = ". TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS ."  
            and tr.payment_status = ". TransactionInit::PAYMENT_STATUS_PENDING ." 
            order by tr.due_date ASC"
        );

        $this->line("<fg=green>Created ready_to_first_attempt_transactions VIEW Successfully.");

    }
}
