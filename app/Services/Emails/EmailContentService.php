<?php


namespace App\Services\Emails;


use App\EmailTypeHead;
use App\Repositories\EmailComponent\EmailContent;
use \Exception;

class EmailContentService
{

    /**
     * Get Parsed Content for admin , Guest and Client
     * @param string $email_type
     * @param bool $replace_extra_vars
     * @return ContentParser
     * @throws Exception
     */
    public static function content(string $email_type, $replace_extra_vars = false) {

        $head = EmailTypeHead::where('type', $email_type)->first();

        self::validateType($email_type, $head);

        return new ContentParser($head, $replace_extra_vars);
    }

    /**
     * @param $email_type
     * @param $head
     * @throws Exception
     */
    public static function validateType($email_type, $head)
    {
        /**
         * @var $head EmailTypeHead
         */
        if (empty($head) || empty($head->status))
            throw new \Exception('Email type '.$email_type.' not Valid', 422);
    }


    /**
     * user_account_id null for Admin email's content
     * @param EmailTypeHead $head
     * @param $receiver 'client | admin | guest'
     * @param $user_account_id
     * @return EmailContent
     */
    public static function currentSetting(EmailTypeHead $head, $receiver, int $user_account_id = null)
    {
        /**
         * @var $content EmailContent
         */
        $receiver_type = config('db_const.emails.send_to.'.$receiver.'.name');

        switch (strtolower($receiver_type)) {
            case 'admin':
                // Default settings for admin.
                $content = $head->defaultContents->where('email_receiver_id', $receiver)->first()->content;
                break;

            default:

                $custom = $head->customContents
                    ->where('user_account_id', $user_account_id)
                    ->where('email_receiver_id', $receiver)->first();

                $content = !empty($custom)
                    ? $custom->content
                    : $head->defaultContents->where('email_receiver_id', $receiver)->first()->content;

                break;
        }

        return $content;
    }
}