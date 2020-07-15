<?php


namespace App\Services\Emails;


use App\EmailTypeHead;
use App\Repositories\EmailComponent\EmailContent;
use \Exception;
use Illuminate\Support\Facades\Log;

class ContentParser
{
    /**
     * @var EmailTypeHead
     */
    private $head;
    private $replace_extra_vars = false;


    public function __construct(EmailTypeHead $head, $replace_extra_vars = false)
    {
        $this->head = $head;
        $this->replace_extra_vars = $replace_extra_vars;
    }

    /**
     * @param int|null $model_id
     * @param int|null $user_account_id
     * @return EmailContent
     * @throws Exception
     */
    public function adminContent(int $model_id = null, int $user_account_id = null)
    {
        return $this->content($this->head, 1, $model_id, $user_account_id);
    }

    /**
     * @param int $model_id
     * @param int $user_account_id
     * @return EmailContent
     * @throws Exception
     */
    public function clientContent(int $model_id, int $user_account_id)
    {
        return $this->content($this->head, 2, $model_id, $user_account_id);
    }

    /**
     * @param int $model_id
     * @param int $user_account_id
     * @return EmailContent
     * @throws Exception
     */
    public function guestContent(int $model_id, int $user_account_id)
    {
        return $this->content($this->head, 3, $model_id, $user_account_id);
    }


    /**
     * receiver "1"=>"Admin", "2"=>"Client", "3"=>"Guest"
     * user_account_id null for Admin email's content
     * @param EmailTypeHead $head
     * @param int $receiver
     * @param int|null $model_id
     * @param int|null $user_account_id
     * @return EmailContent
     * @throws Exception
     */
    private function content(EmailTypeHead $head, int $receiver, int $model_id = null, int $user_account_id = null)
    {

        $config = config('db_const.emails');
        $column = 'to_'.strtolower($config['send_to'][$receiver]['name']);

        if (empty($head->status) || empty($head->$column))
            throw new Exception('Email Type Head in-active for ' . $config['send_to'][$receiver]['name'], 422);

        $content = EmailContentService::currentSetting($head, $receiver, $user_account_id);
        //dump($content);
        return $this->parse($content, ($config['heads'][$head->type]['model'] ?? null), $model_id);
    }


    /**
     * @param EmailContent $content
     * @param string|null $model
     * @param int|null $model_id
     * @return array | EmailContent
     */
    private function parse(EmailContent $content, string $model = null, int $model_id = null)
    {
        if (!empty($model) && !empty($model_id)) {

            $content = convertTemplateVariablesToActualData(
                $model,
                $model_id,
                json_decode(json_encode($content), true),
                $this->replace_extra_vars
            );
        }

        return $content;
    }
}