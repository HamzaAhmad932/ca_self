<?php

namespace App\Jobs\EmailJobs;

use App\AuthorizationDetails;
use App\BookingInfo;
use App\CreditCardAuthorization;
use App\CreditCardInfo;
use App\GuestCommunication;
use App\GuestData;
use App\GuestImage;
use App\GuestImageDetail;
use App\RefundDetail;
use App\Repositories\EmailComponent\EmailTrait;
use App\TransactionDetail;
use App\TransactionInit;
use App\Upsell;
use App\UpsellOrder;
use App\User;
use App\UserAccount;
use App\UserBookingSource;
use App\UserPaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, EmailTrait;

    const MAIL_FOR_3DS_CHARGE = 1;
    const MAIL_FOR_3DS_AUTH = 2;
    const MAIL_FOR_3DS_AUTH_SSD = 3;

    /**
     * @var int
     */
    public $tries = 1;
    /**
     * @var
     */
    private $instance;


    /**
     * SendEmailsJob constructor.
     * @param $email_type_name 'Email_Head_Type'
     * @param $to_whom_name 'Whom To Send Email ie admin,client,guest'
     * @param $model_id 'Model Key'
     * @param array $extras
     */
    public function __construct($email_type_name, $to_whom_name, $model_id, $extras = [])
    {
        $this->email_type_name = $email_type_name;
        $this->send_to_whom_name = $to_whom_name;
        $this->model_id = $model_id;
        $this->extra_data = $extras;
        $this->email_type = config("db_const.emails.heads." . $email_type_name);
        $this->to_whom = $this->email_type["send_to"][$to_whom_name];
    }


    public function handle()
    {
        try {

            $this->sendEmail();

        } catch (\Exception $e) {
            log_exception_by_exception_object($e,
                [
                    'email_type' => $this->email_type_name,
                    'model_id' => $this->model_id,
                    'sent_to_whom' => $this->send_to_whom_name,
                    'extra_data' => $this->extra_data,
                ]
            );
        }
    }

    /** Following Function Will Get Data From Model As Given
     * In Emails Config file and Set Required Data for Email
     * i.e Booking info, User Account , Property info etc
     */
    private function set_required_data()
    {
        switch ($this->email_type['model']) {

            case User::class:
                $this->user = User::find($this->model_id);
                $this->user_account = $this->user->user_account;
                break;

            case UserAccount::class:
                $this->user_account = UserAccount::find($this->model_id);

                if (!empty($this->extra_data['properties_info_ids'])
                    && count($this->extra_data['properties_info_ids']) == 1) {
                    $this->property_info = $this->user_account->properties_info->whereIn('id',
                        $this->extra_data['properties_info_ids']
                    )->first();
                }

                break;

            case UserBookingSource::class:
                $this->user_booking_source = UserBookingSource::find($this->model_id);
                $this->user_account = $this->user_booking_source->User_account;
                $this->property_info = $this->user_booking_source->property_info;
                break;

            case UserPaymentGateway::class:
                $this->user_payment_gateway = UserPaymentGateway::find($this->model_id);
                $this->user_account = $this->user_payment_gateway->user_account;
                $this->property_info = $this->user_payment_gateway->property_info;
                break;

            case CreditCardInfo::class:
                $this->credit_card_info = CreditCardInfo::find($this->model_id);
                $this->set_required_data_from_booking_info($this->credit_card_info->booking_info_id);
                break;

            case CreditCardAuthorization::class:
                $this->authorization_info = CreditCardAuthorization::find($this->model_id);
                $this->set_required_data_from_booking_info($this->authorization_info->booking_info_id);
                break;

            case AuthorizationDetails::class:
                $this->authorization_info = AuthorizationDetails::find($this->model_id)->cc_auth;
                $this->set_required_data_from_booking_info($this->authorization_info->booking_info_id);
                break;

            case TransactionInit::class :
                $this->transaction_info = TransactionInit::find($this->model_id);
                $this->refund_info = $this->transaction_info->refund_detail->last();
                $this->set_required_data_from_booking_info($this->transaction_info->booking_info_id);
                break;

            case TransactionDetail::class :
                $this->transaction_info = TransactionDetail::find($this->model_id)->transaction_init;
                $this->set_required_data_from_booking_info($this->transaction_info->booking_info_id);
                break;

            case BookingInfo::class:

                if (!empty($this->extra_data['upsell_ids']))
                    $this->upsells = Upsell::with('upsellType')->whereIn('id', $this->extra_data['upsell_ids'])->get();

                $this->set_required_data_from_booking_info($this->model_id);
                break;

            case GuestCommunication::class:
                $this->guest_communication = GuestCommunication::find($this->model_id);
                $this->set_required_data_from_booking_info($this->guest_communication->booking_info_id);
                break;

            case RefundDetail::class:
                $this->refund_info = RefundDetail::find($this->model_id);
                $this->set_required_data_from_booking_info($this->refund_info->booking_info_id);
                break;

            case UpsellOrder::class:
                $this->upsell_order = UpsellOrder::find($this->model_id);
                $this->set_required_data_from_booking_info($this->upsell_order->booking_info_id);
                break;

            case GuestData::class:
                $this->guest_data = GuestData::find($this->model_id);
                $this->set_required_data_from_booking_info($this->guest_data->booking_id);

                break;

            case GuestImage::class:
                $this->guest_image = GuestImage::find($this->model_id);
                if (!empty($this->guest_image)) {
                    $this->set_required_data_from_booking_info($this->guest_image->booking_id);
                } else { // for Deleted images
                    $this->guest_image = GuestImageDetail::where('guest_image_id', $this->model_id)->first();
                    $this->set_required_data_from_booking_info($this->guest_image->booking_info_id);
                }

                break;
        }
    }

}
