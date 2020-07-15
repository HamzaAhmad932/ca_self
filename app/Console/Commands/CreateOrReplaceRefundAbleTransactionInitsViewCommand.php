<?php

namespace App\Console\Commands;

use App\RefundDetail;
use App\TransactionInit;
use Illuminate\Console\Command;

class CreateOrReplaceRefundAbleTransactionInitsViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplaceRefundAbleTransactionInitsView';

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

        $this->line("<fg=yellow>Creating refund_able_transaction_inits VIEW.....  \n");


        \DB::statement("
            CREATE OR REPLACE VIEW refund_able_transaction_inits
            AS
            SELECT tr.id,bi.pms_booking_id, bi.id as booking_info_id, tr.last_success_trans_obj,
          
            tr.price-(select IFNULL(SUM(amount),0) from refund_details as rfd where rfd.against_charge_ref_no = tr.charge_ref_no and rfd.payment_status = ". TransactionInit::PAYMENT_STATUS_SUCCESS .") as available_amount,
            tr.price as t_price, tr.charge_ref_no
            FROM transaction_inits as tr
         
            INNER JOIN booking_infos as bi on tr.booking_info_id = bi.id            
            WHERE tr.type in ('C', 'M') and tr.payment_status = ". TransactionInit::PAYMENT_STATUS_SUCCESS ."  
            ORDER BY available_amount  DESC"
        );
        $this->line("<fg=green>Created refund_able_transaction_inits VIEW Successfully.");
    }

}