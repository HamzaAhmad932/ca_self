<?php

namespace App\Jobs;

use App\User;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Twilio\Exceptions\TwilioException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenericTwilioSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to;
    public $message;
    public $accountSid;
    public $authToken;
    public $twilioNumber;

    /**
     * Create a new job instance.
     * @param $to
     * @param $message
     */
    public function __construct($to, $message)
    {
        $this->to = $to;
        $this->message = $message;
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->twilioNumber = config('services.twilio.from');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $client = new Client($this->accountSid, $this->authToken);
            $client->messages->create($this->to,
                [
                    "body" => $this->message,
                    "from" => $this->twilioNumber
                    //   On US phone numbers, you could send an image as well!
                    //  'mediaUrl' => $imageUrl
                ]
            );


        } catch (TwilioException $e) {
            Log::error($e->getMessage(), ['File'=>GenericTwilioSmsJob::class]);
            Log::error($e->getTraceAsString(), ['File'=>GenericTwilioSmsJob::class]);
        }

    }
}
