<?php

return [
    "alert_type" => [
        "payment_failed" => "payment_failed",
        "payment_due" => "payment_past_due",
        "message" => "chat",
        "guest_documents_uploaded" => "id_uploaded",
        "cc_uploaded" => "credit_card_uploaded",
        "passport_uploaded" => "passport_uploaded",
        "selfie" => "selfie",
        "signature" => "signature",
        "upsell_purchased" => "upsell_purchased",
    ],
    "alert_for"=>[
        "client"=>"0",
        "guest"=>"1",
    ],
    "alert_type_text" => [
        "payment_failed" => "Payment failed",
        "payment_past_due" => "Payment past due",
        "chat" => "New message received",
        "id_uploaded" => "ID uploaded",
        "credit_card_uploaded" => "Credit card scan uploaded",
        "passport_uploaded" => "Passport scan uploaded",
        "selfie" => "Selfie uploaded",
        "signature" => "Digital signature",
        "upsell_purchased" => "Upsell Purchased",
    ],
    "fa_class" => [
        "payment_failed" => "fas fa-credit-card",
        "payment_past_due" => "fas fa-exclamation",
        "chat" => "fas fa-comments",
        "id_uploaded" => "fas fa-check",
        "credit_card_uploaded" => "fas fa-cloud-upload-alt",
        "passport_uploaded" => "fas fa-cloud-upload-alt",
        "selfie" => "fas fa-image",
        "signature" => "fas fa-file-signature",
        "upsell_purchased" => "fas fa-hand-holding-usd",
        //fas fa-signature
    ],
    "icon_color_class" => [
        "payment_failed" => "danger",
        "payment_past_due" => "warning",
        "chat" => "success",
        "id_uploaded" => "primary",
        "credit_card_uploaded" => "success",
        "passport_uploaded" => "success",
        "selfie" => "primaryOld",
        "signature" => "info",
        "upsell_purchased" => "success",
    ],
    "redirect_to_tab" => [
        "chat" => "m_tabs_7_4",
        "payment_failed" => "m_tabs_7_2",
        "id_uploaded" => "m_tabs_7_5",
        "payment_past_due" => "m_tabs_7_2",
    ],
    "alert_type_messages" => [
        "payment_failed" => "Payment failed for booking ID ",
        "payment_past_due" => "Payment past due for booking ID ",
        "chat" => "New message received for booking ID ",
        "id_uploaded" => "ID uploaded for booking ID ",
        "credit_card_uploaded" => "Guest uploaded a Credit card scan for booking ID ",
        "passport_uploaded" => "Guest uploaded a passport scan for booking ID ",
        "selfie" => "Guest uploaded a selfie for booking ID ",
        "signature" => "Guest uploaded own digital signature for booking ID ",
        "upsell_purchased" => "Upsell Purchased for booking ID ",
    ],
];