<?php

namespace App\Repositories;


use App\GuestCommunication;

class NotificationAlerts
{
    /**
     * DataTables print preview view.
     *
     * @var string
     */
    protected $user_id;

    /**
     * Name of the dataTable variable.
     *
     * @var string
     */
    protected $user_account_id;

    /**
     * List of columns to be excluded from export.
     *
     * @var string|array
     */
    protected $booking_info_id;

    /**
     * List of columns to be excluded from printing.
     *
     * @var string|array
     */
    protected $is_guest;

    /**
     * List of columns to be exported.
     *
     * @var string|array
     */
    protected $alert_type;

    /**
     * List of columns to be printed.
     *
     * @var string|array
     */
    protected $pms_booking_id;

    /**
     * List of columns to be printed.
     *
     * @var string|array
     */
    protected $message;

    /**
     * List of columns to be printed.
     *
     * @var string|array
     */
    protected $message_read_by_guest = 0;

    /**
     * List of columns to be printed.
     *
     * @var string|array
     */
    protected $message_read_by_user = 0;

    /**
     * List of columns to be printed.
     *
     * @var string|array
     */
    protected $action_required;

    /**
     * DataTablesExportHandler constructor.
     *
     * @param Collection $collection
     */
    public function __construct($user_id, $user_account_id)
    {
        $this->user_id = $user_id;
        $this->user_account_id = $user_account_id;
    }

    /**
     * @return the new created object
     */
    public function create($booking_info_id, $is_guest, $alert_type, $pms_booking_id, $action_required, $message = null)
    {
        $alert = GuestCommunication::create([
            'user_id' => $this->user_id,
            'user_account_id' => $this->user_account_id,
            'booking_info_id' => $booking_info_id,
            'is_guest' => $is_guest,
            'alert_type' => $alert_type,
            'pms_booking_id' => $pms_booking_id,
            'message' => $message,
            'message_read_by_guest' => $this->message_read_by_guest,
            'message_read_by_user' => $this->message_read_by_user,
            'action_required' => $action_required
        ]);

        if($alert)
            return $alert;
        else
            return false;
    }

    /**
     * @return the all alerts to show to client
     */
    public function getNotificationForClient($maximum_notifications = 10)
    {
        // ->where('user_id', $this->user_id)
        $alerts = GuestCommunication::with(['booking_info' =>  function ($query) { $query->select('pms_booking_id', 'id'); }])
                    ->where('user_account_id', $this->user_account_id)
                    ->where(function($query) {
                        $query->where(function($q) {
                            $q->where('alert_type', 'chat')
                            ->where('is_guest', '!=', 0);
                        })
                        ->orWhere('alert_type', '!=', 'chat');
                    });

        $total_notifications = $alerts->count();
        $notifications_to_send = $alerts
                    ->orderBy('created_at', 'Desc')
                    ->skip(0)
                    ->take($maximum_notifications)
                    ->get();

        $total_unread_notifications = $alerts->where(function($query) {
                                        $query->where('action_performed', null)
                                        ->orWhere('action_performed', 0);
                                    })->count();
        
        if($total_notifications && $notifications_to_send)
            return ['total_notifications' => $total_notifications, 'notifications_to_send' => $notifications_to_send, 'total_unread_notifications' => $total_unread_notifications];
        else
            return false;
    }

    /**
     * @param alert-id which will be marked performed
     */
    public function markPerformed($alert_id, $maximum_notifications = 10)
    {

        $alert = GuestCommunication::where('user_account_id', $this->user_account_id)
                    ->where('id', $alert_id)
                    ->first();

        //update required fields
        $alert->action_performed_by = $this->user_id;
        $alert->action_performed = 1;
        $alert->message_read_by_user = 1;


        if($alert->save())
            return $this->getNotificationForClient($maximum_notifications);
        else
            return false;
    }
}