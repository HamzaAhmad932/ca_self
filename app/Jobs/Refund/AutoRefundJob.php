<?php

namespace App\Jobs\Refund;

use App\ReadyToRefundTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class AutoRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutoRefundHelper;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('auto_refund');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var $record ReadyToRefundTransaction
         */
        $records = ReadyToRefundTransaction::all();
        foreach ($records as $record) {
            $this->refundNow($record);
        }
    }


}
