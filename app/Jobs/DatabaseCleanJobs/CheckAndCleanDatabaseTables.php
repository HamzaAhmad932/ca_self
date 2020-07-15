<?php

namespace App\Jobs\DatabaseCleanJobs;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GenericEmail;
use App\ApiRequestDetail;
use App\Audit;
use App\PmsRequestLogs;


class CheckAndCleanDatabaseTables implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('clean_database_tables');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            // Following Code Will Run Once a week on 1st day of week at 01:00Am  to OPTIMIZE Some TABLES
            $now = Carbon::now();
            if($now->format('h:i') == '01:00' && $now->isDayOfWeek(1)){
                if(DB::statement("OPTIMIZE TABLE audits, pms_request_logs, api_request_details")){
                    Log::info('Tables audits, pms_request_logs, api_request_details OPTIMIZED',['File' => __FILE__]);
                };
            }

            //inform table names whose size is greater than this value
            $size_in_mb = 500;
            $db_name = config('database.connections.mysql.database');
            $all_tables = DB::select("
                        SELECT
                          TABLE_NAME AS 'name',
                          ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS 'size'
                        FROM
                          information_schema.TABLES
                        WHERE
                          TABLE_SCHEMA = '" . $db_name . "'
                        HAVING
                            size > " . $size_in_mb . "
                        ORDER BY
                          (DATA_LENGTH + INDEX_LENGTH)
                        DESC"
            );

            //filter table names to send in the email or not
            $all_tables = $this->filter_table_name($all_tables);

            //email developers if any table has size greater than 500MB
            if ($all_tables) {
                Mail::to(config('db_const.app_developers.emails.to'))->cc(
                    config('db_const.app_developers.emails.cc'))->send(
                    new GenericEmail(
                        array(
                            'subject' => 'Tables having Size More Than ' . $size_in_mb . 'MB',
                            'noReply' => true,
                            'markdown' => 'emails.tables_size',
                            'tables' => $all_tables
                        )
                    )
                );
            }

            //clearing the old records from required tables
            if ($logs_deleted = ApiRequestDetail::where('created_at', '<', DB::raw("NOW() - INTERVAL 5 MONTH"))->delete()) {
                Log::notice('5 Months Old data cleared', [
                    'table_name' => "ApiRequestDetail"
                ]);
            }
            if ($logs_deleted = Audit::where('created_at', '<', DB::raw("NOW() - INTERVAL 6 MONTH"))->delete()) {
                Log::notice('4 Months Old data cleared', [
                    'table_name' => "Audit"
                ]);
            }
            if ($logs_deleted = PmsRequestLogs::where('created_at', '<', DB::raw("NOW() - INTERVAL 2 MONTH"))->delete()) {
                Log::notice('2 Months Old data cleared', [
                    'table_name' => "PmsRequestLogs"
                ]);
            }

            /*========================================================*
             *===============Now check Disk Usage=====================*
            */

            //get the free space in bytes
            $bytes_available = disk_free_space('/');

            if ($bytes_available) {
                //1024*1024*1024 = 1073741824 (because we receives bytes and 1024 to kb, another 1024 to MB and last 1024 for GB)
                $gb_available = $bytes_available / 1073741824;
                if ($gb_available <= 5) {
                    Mail::to(config('db_const.app_developers.emails.to'))->cc(
                        config('db_const.app_developers.emails.cc'))->send(
                        new GenericEmail(
                            array(
                                'subject' => 'Disk Space is low',
                                'noReply' => true,
                                'markdown' => 'emails.disk-space-low',
                                'space_available' => $gb_available
                            )
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Stack' => $e->getTraceAsString()]);
        }
    }

    /**
     * Filter table names to send in the email.
     *
     * @return array
     */
    private function filter_table_name(array $all_tables_having_size_more)
    {
        if($all_tables_having_size_more)
        {
            //make single dimensional array only of table names
            $table_names_only = array_column($all_tables_having_size_more, 'name');

            //search for pms_request_logs table in the newly created single dimensional array
            if(in_array('pms_request_logs', $table_names_only))
            {
                //PMS_request_logs table exists so lets find index key -- This key is same for newly created and original array
                $pms_request_logs_index = array_search('pms_request_logs', $table_names_only); //array_values($list)

                //now check from original array if pms_request_logs having size less than 2 GB is so then don't send it in email
                if($all_tables_having_size_more[$pms_request_logs_index]->size < 2048)
                {
                    unset($all_tables_having_size_more[$pms_request_logs_index]);
                }
            }
        }

        return $all_tables_having_size_more;
    }

}
