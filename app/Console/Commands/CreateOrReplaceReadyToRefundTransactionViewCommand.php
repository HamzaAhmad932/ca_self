<?php

namespace App\Console\Commands;

use App\TransactionInit;
use Illuminate\Console\Command;

class CreateOrReplaceReadyToRefundTransactionViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplaceReadyToRefundTransactionView';

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

        $this->line("<fg=yellow>Creating ready_to_refund_transactions VIEW.....  \n");


        \DB::statement("
            CREATE OR REPLACE VIEW ready_to_refund_transactions
            AS
            SELECT tr.id, tr.due_date, tr.attempt, tr.lets_process,tr.payment_status, 
            
            tr.price-(select IFNULL(SUM(amount),0) from refund_details as rfd where rfd.transaction_init_id = tr.id and rfd.payment_status = ".TransactionInit::PAYMENT_STATUS_SUCCESS.") as amount_to_refund,
            
            bi.pms_booking_id, bi.id as booking_info_id, 
 
            pr.id as property_info_id, pr.pms_property_id, 

            ua.id as user_account_id,
            upg.id as user_payment_gateway_id
            FROM transaction_inits as tr
         
            INNER JOIN user_accounts as ua on tr.user_account_id = ua.id and  ua.status = 1
            INNER JOIN booking_infos as bi on tr.booking_info_id = bi.id
            INNER JOIN property_infos as pr on bi.property_info_id = pr.id and pr.status=1
            
            INNER JOIN user_payment_gateways as upg on upg.property_info_id = IF(pr.use_pg_settings = 1, pr.id, 0)
            and upg.user_account_id = tr.user_account_id and upg.is_verified = 1
                          
            WHERE tr.final_tick = 0 and tr.price > 0 
            and tr.attempt < ".TransactionInit::TOTAL_ATTEMPTS."  
            and tr.type = '". TransactionInit::TRANSACTION_TYPE_REFUND ."' and tr.lets_process = 1 
            and tr.in_processing = ". TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS ."  
            and tr.payment_status = ". TransactionInit::PAYMENT_STATUS_PENDING ." 
            order by tr.id ASC"
        );

        $this->line("<fg=green>Created ready_to_refund_transactions VIEW Successfully.");

    }

}
