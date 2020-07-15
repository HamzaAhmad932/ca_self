<?php


return [

    'booking_list'=>[
        'payment_status'=>[

            'init'=> [
                'status'=> '',
                'message'=> '',
                'url'=> '',
                'url_button_text' => '',
                'class'=> '',
                'box_class'=> '',
                'icon'=> '',
            ],

            'invalid_card'=>[
                'status'=> 'Invalid Card',
                'message'=> 'Guest provided invalid credit card',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-danger',
                'box_class'=> 'payment-declined',
                'icon'=> 'fas fa-exclamation-triangle',
            ],

            'card_missing'=>[
                'status'=> 'Card Missing',
                'message'=> 'Payment method not found',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-danger',
                'box_class'=> 'payment-declined', //empty class name for pending status for box
                'icon'=> 'fas fa-ban',
            ],

            'paused'=>[
                'status'=> 'Paused',
                'message'=> 'Property is not connected',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-warning',
                'box_class'=> '',
                'icon'=> 'fas fa-ban',
            ],

            'abort'=> [
                'status'=> 'Aborted',
                'message'=> 'This booking has been partially paid on PMS',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-warning',
                'box_class'=> '',
                'icon'=> 'fas fa-ban',
            ],

            'void'=> [
                'status'=> 'Voided',
                'message'=> 'This booking maybe cancelled or charge is manually voided',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-warning',
                'box_class'=> '',
                'icon'=> 'fas fa-ban',
            ],

            'mark_as_paid'=> [
                'status'=> 'Marked As Paid',
                'message'=> '100% of reservation amount paid',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-success',
                'box_class'=> 'payment-paid',
                'icon'=> 'fas fa-check-circle',
            ],

            'declined'=> [
                'status'=> 'Declined',
                'message'=> '',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-danger',
                'box_class'=> 'payment-declined',
                'icon'=> 'fas fa-exclamation-triangle',
            ],

            'paid'=> [
                'status'=> 'Paid',
                'message'=> '100% of reservation amount paid',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-success',
                'box_class'=> 'payment-paid',
                'icon'=> 'fas fa-check-circle',
            ],

            'scheduled'=> [
                'status'=> 'Scheduled',
                'message'=> '',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-warning',
                'box_class'=> '',
                'icon'=> 'fas fa-clock',
            ],

            'not_enabled'=> [
                'status'=> 'Not Enabled',
                'message'=> '',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-secondary',
                'box_class'=> 'card-not-enabled',
                'icon'=> 'fa fa-toggle-off',
            ],

            'not_supported'=> [
                'status'=> 'Not Supported',
                'message'=> 'If you want us to support this booking source, please contact our support',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-default',
                'box_class'=> 'payment-secondary',
                'icon'=> '',
            ],

            'not_required'=> [
                'status'=> 'Not Required',
                'message'=> 'Payments collected by OTA auto payment collection.',
                'url'=> '',
                'url_button_text' => 'Settings',
                'class'=> 'badge-secondary',
                'box_class'=> 'payment-secondary',
                'icon'=> 'fas fa-times-circle',
            ],
            
            'gateway_unverified'=> [
                'status'=> 'Gateway Not Active',
                'message'=> 'You do not have active payment gateway',
                'url'=> '',
                'url_button_text' => 'Activate Now',
                'class'=> 'badge-secondary',
                'box_class'=> 'card-not-enabled',
                'icon'=> 'fa fa-toggle-off',
            ],
        ],

        'transaction_status'=>[

//            'status'=>[
//                variables..
//            ]
            "titles"=>[
                'refund'=> 'Refund',
                'charges' => 'Charges'
            ],

            'init' =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'In process',
                'status_class'=> 'badge-info',
                'box_class'=> 'info',
                'icon'=> 'fas fa-spinner',
                'classification'=> 4,
            ],

            'accepted' =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Accepted',
                'status_class'=> 'badge-success',
                'box_class'=> 'success',
                'icon'=> 'fas fa-check-circle',
                'classification'=> 1,
            ],

            'scheduled' =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Scheduled',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-clock',
                'classification'=> 2,
            ],

            'declined' =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Declined',
                'status_class'=> 'badge-danger',
                'box_class'=> 'danger',
                'icon'=> 'fas fa-exclamation-triangle',
                'classification'=> 3,
            ],

            3 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Voided',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-ban',
                'classification'=> 4,
            ],

            5 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'In Approval',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-clock',
                'classification'=> 2,
            ],

            12 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Manually Voided',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-ban',
                'classification'=> 4,
            ],

            11 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Paused',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-ban',
                'classification'=> 2,
            ],

            6 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Aborted',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-ban',
                'classification'=> 2,
            ],

            13 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> false,
                'is_auth'=> false,
                'status'=> 'Marked as Paid',
                'status_class'=> 'badge-success',
                'box_class'=> 'success',
                'icon'=> 'fas fa-check-circle',
                'classification'=> 1,
            ],


        ],

        'auth_status'=>[

            'titles'=>[
                'auth'=> 'Authorization',
                'sd' => 'Security Deposit Authorization'
            ],

            'init'=>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> '',
                'status_class'=> '',
                'box_class'=> '',
                'icon'=> '',
                'classification'=> '',
            ],

            'accepted'=>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Accepted',
                'status_class'=> 'badge-success',
                'box_class'=> 'success',
                'icon'=> 'fas fa-check-circle',
                'classification'=> 1,
            ],

            'declined'=>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Declined',
                'status_class'=> 'badge-danger',
                'box_class'=> 'danger',
                'icon'=> 'fas fa-exclamation-triangle',
                'classification'=> 3,
            ],

            'scheduled'=>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Scheduled',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-clock',
                'classification'=> 2,
            ],

            'failed'=>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Authorization Failed',
                'status_class'=> 'badge-danger',
                'box_class'=> 'danger',
                'icon'=> 'fas fa-clock',
                'classification'=> 2,
            ],

            3 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Voided',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-ban',
                'classification'=> 4,
            ],

            10 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'In-Approval',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-clock',
                'classification'=> 2,
            ],

            11 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Paused',
                'status_class'=> 'badge-warning',
                'box_class'=> 'warning',
                'icon'=> 'fas fa-ban',
                'classification'=> 4,
            ],

            6 =>[
                'id'=> '',
                'title'=> '',
                'amount'=> '',
                'date'=> '',
                'captured'=> '',
                'is_auth'=> True,
                'status'=> 'Charged',
                'status_class'=> 'badge-info',
                'box_class'=> 'info',
                'icon'=> 'fas fa-user-shield',
                'classification'=> 1,
            ],
        ]
    ]
];