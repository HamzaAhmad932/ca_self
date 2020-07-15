<?php


namespace App\Repositories\EmailComponent;

use App\System\PaymentGateway\Models\GateWay;
use Illuminate\Support\Facades\URL;

/**
 *  Use this Trait Only for SendEmail Trait
 * Trait SystemEmailContents
 * @package App\Repositories\EmailComponent
 */
trait SystemEmailContents
{

    /**
     * @return array|mixed
     */
    private function ca_account_status_changed()
    {
        $content = get_config_column_values('user_account', 'status', $this->user_account->status)['email'] ?? [];
        $content['button_text'] = 'View Settings';
        $this->subject = $content['subject'] ?? $this->email_type['title'];

        return $content;
    }


    /**
     * @return array
     */
    private function gateway_disabled_auto_to_client()
    {
        $this->subject = 'Gateway Integration with ChargeAutomation Requires immediate attention';
        $gateway = new GateWay($this->user_payment_gateway->gateway);
        $gatewayId = null;

        foreach ($gateway->credentials as $c) {
            if($c->name == 'stripe_user_id') {
                $gatewayId = $c->value;
                break;
            }
        }

        return [
            'top_paragraph' => 'ChargeAutomation cannot verify integration of your payment gateway '.
                ($gatewayId != null?'with account id of ('.$gatewayId.')':''). ' which is configured '.
                (!empty($this->property_info) ? 'against your property ('.$this->property_info->name.').' : 'as Global)'),
            'top_paragraph_second_line' => 'All pending and scheduled booking processing will be effected.',
            'button_text' => 'View Account Settings'
        ];
    }


    private function team_member_invite() {

        $password = str_replace(' ', '', (
            trim($this->user->name).
            trim($this->user->user_account_id).
            trim($this->user->parent_user_id))
        );

        $this->subject = $this->email_type['title'] ?? 'Team Member Invitation';

        return [
            'email_title' => [ 'text' => 'Invitation From '.$this->user_account->name, 'text_color' => '#1EAF24'],
            'button_text' => 'Join Team',
            'top_paragraph' => $this->user_account->users->first()->name.' has invited you to join the '.$this->user_account->name.' team on ChargeAutomation.',
            'top_paragraph_second_line' => 'Your temporary password is: <b>'.$password.'</b>',
            'top_paragraph_third_line' => 'To get started please verify your email by clicking below button.',
        ];
    }

}