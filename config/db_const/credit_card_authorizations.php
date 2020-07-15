<?php 

    return [
        'status' => [
            0 => 'Scheduled', /** Auto Auth pending**/
            1 => 'Success',     /** Attempted */
            3 => 'Void',
            4 => 'Scheduled', /** Manual Auth pending */
            5 => 'Fail',
            6 => 'Charged',
            7 => 'Reattempt',
            10 => 'In-Approval',
            11 => 'Paused - Property Disabled',
        ],

        'status_icon'=> [
            0 => 'fas fa-clock',
            1 => 'fas fa-check-circle',
            //2 => 'fas fa-clock',
            3 => 'fas fa-ban',
            4 => 'fas fa-clock',
            5 => 'fas fa-exclamation-triangle',
            6 => 'fas fa-user-shield',
            7 => 'fas fa-exclamation-triangle',
            10 => 'fas fa-clock',
            //11 => 'fas fa-pause-circle',
            11 => 'fas fa-ban',
            '' => 'fas fa-spinner'
        ],

        'authorization_status_messages_for_guest' => [

            0 => 'Scheduled', /** Auto Auth pending**/
            1 => 'Accepted',     /** Attempted */
            3 => 'Voided',
            4 => 'Scheduled', /** Manual Auth pending */
            5 => 'Declined',
            6 => 'Charged',
            7 => 'Declined',
            10 => 'In-Approval',
            11 => 'Paused',
        ],

        'status_color_for_guest'=>[
            0 => 'badge-warning',
            1 => 'badge-success',
            3 => 'badge-warning',
            4 => 'badge-warning',
            5 => 'badge-danger',
            6 => 'badge-success',
            7 => 'badge-danger',
            10 => 'badge-warning',
            11 => 'badge-warning',
        ],

        'status_color'=>[
            0 => 'badge-warning',
            1 => 'badge-success',
            3 => 'badge-warning',
            4 => 'badge-warning',
            5 => 'badge-danger',
            6 => 'badge-success',
            7 => 'badge-warning',
            10 => 'badge-warning',
            11 => 'badge-warning',
        ],

        'manually_released' => [
            0 => 'Default value',
            1 => 'Auth Manually Released'
        ],

        'type' => [
            'security_damage_deposit_auto_auth'	=> 19 ,
            'security_damage_deposit_manual_auth' => 20,
            'credit_card_auto_authorize'=> 21,
            'credit_card_manual_authorize'=> 22
        ],

        'transaction_type' => [
            19 => 'security_damage_deposit_auto_auth',
            20 => 'security_damage_deposit_manual_auth',
            21 => 'credit_card_auto_authorize',
            22 => 'credit_card_manual_authorize',
        ],

        'transaction_type_button_color' => [
            19 => 'm-badge m-badge--warning m-badge--wide',
            20 => 'm-badge m-badge--warning m-badge--wide',
            21 => 'm-badge m-badge--warning m-badge--wide',
            22 => 'm-badge m-badge--warning m-badge--wide',
        ],

        'status_button_color' => [
            0 => 'm-badge  m-badge--warning m-badge--wide',
            1 => 'm-badge  m-badge--accent m-badge--wide',
            2 => 'm-badge  m-badge--danger m-badge--wide',
            3 => 'm-badge  m-badge--brand m-badge--wide',
            4 => 'm-badge  m-badge--warning m-badge--wide',
            5 => 'm-badge  m-badge--danger m-badge--wide',
            6 => 'm-badge  m-badge--danger m-badge--wide',
            7 => 'm-badge  m-badge--danger m-badge--wide',
            10 => 'm-badge  m-badge--info m-badge--wide',
            11 => 'm-badge  m-badge--warning m-badge--wide',
        ],

        'activity_log_badge'=>[
            0 => 'badge-danger',
            1 => 'badge-success',
            2 => 'badge-warning',
            3 => 'badge-warning',
            4 => 'badge-info',
            5 => 'badge-warning',
            6 => 'badge-danger',
            7 => 'badge-warning',
            8 => 'badge-warning',
            9 => 'badge-warning',
            11 => 'badge-warning',
            12 => 'badge-warning',
            13 => 'badge-success'
        ]

    ];
