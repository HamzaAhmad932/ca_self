<?php

namespace App\Listeners\Emails;

use App\Events\Emails\EmailEvent;
use App\Exceptions\EmailComponentException;
use App\GuestCommunication;
use App\Jobs\EmailJobs\EmailJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class EmailListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  EmailEvent  $event
     * @return void
     */
    public function handle(EmailEvent $event)
    {
        try {

            //if validation passed then dispatch job
            $this->validateTypeAndId($event->email_type, $event->model_id);


            //get job name by matching email type from DB_CONST config file
            $email = config('db_const.emails.heads.'.$event->email_type);

            foreach ($email["send_to"] as $key=>$whom){
                $send_to = config("db_const.emails.send_to.".$whom['id']);
                if ($this->validToDispatch($event->email_type, $event->model_id, $key)) {
                    $send_to["job"]::dispatch($event->email_type,$key, $event->model_id, $event->extras)
                        ->delay(now())->onQueue($send_to["queue"]);
                }
            }

        } catch(\Exception $e) {
            Log::critical($e->getMessage(),[
                'file' => __FILE__,
                'Trace' => $e->getTraceAsString(),
                'email_type_provided' => $event->email_type,
                'mode_primary_key_provided' => $event->model_id,
            ]);
        }
    }

    /**
     * Chat Message email to guest if sent from guest else to guest
     * @param $email_type
     * @param $model_id
     * @param $receiver
     * @return bool
     */
    private function validToDispatch($email_type, $model_id, $receiver)
    {
        if ($email_type == 'new_chat_message') {
            return GuestCommunication::where('id', $model_id)->where('is_guest', $receiver=='client'?1:0)->count() > 0;
        }

        return true;
    }

    /**
     * @param $email_type
     * @param $model_id
     * @throws \Exception
     */
    private function validateTypeAndId($email_type, $model_id)
    {
        $emails = config('db_const.emails.heads');
        if(array_key_exists($email_type,$emails)) {
            $valid_model_id = $emails[$email_type]["model"]::find($model_id);
            if(!$valid_model_id) {
                throw new \Exception("Invalid table primary key provided to Email Plugin");
            }
        } else {
            throw new \Exception("Invalid email_type provided to Email Plugin");
        }
    }
}
