<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use OpenExRt\Client;

class OpenExchangeRatesSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Client
     */
    private  $client;


    /**
     * OpenExchangeRatesSyncJob constructor.
     */
    public function __construct()
    {
        $this->client = resolve(Client::class);
        $this->client->setAppId(config('db_const.open_exchange_rates.app_id'));
    }

    /**
     * Update Currencies Rates to File.
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        try {
            $apiResponse = $this->client->getLatest();

            if (!isset($apiResponse->error) && isset($apiResponse->rates)) {
                $file = base_path('app/Json/currencies.json');
                file_put_contents($file, json_encode($apiResponse));

            } else {
                Log::notice('Error while updating Currencies', ['error' => $apiResponse]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Stack' => $e->getTraceAsString()]);
        }
    }
}
