<?php

namespace App\Repositories;

class Constant
{
	const NEW_BOOKING_EMAIL = "new_booking_received";
    const CANCELLED_BOOKING_EMAIL = "booking_cancelled";
    const CHAT_MESSAGE_EMAIL_GUEST = "new_message_received_against_booking";
    const PAYMENT_SUCCESSFUL_EMAIL = "payment_successful";
    const PAYMENT_FAILED_EMAIL = "payment_failed";
    const PAYMENT_ABORTED_EMAIL = "payment_aborted";
    const CREDIT_CARD_INVALID_CLIENT = "credit_card_invalid_to_client";
    const CREDIT_CARD_MISSING_CLIENT = "credit_card_missing_to_client";
    const GUEST_EMAIL_MISSING_CLIENT = "guest_email_missing_to_client";
    const VIRTUAL_CARD_BOOKING = 1;
}