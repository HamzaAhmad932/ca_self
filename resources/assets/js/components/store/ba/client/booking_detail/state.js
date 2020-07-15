let state = {

    booking_details: {
        pms_booking_id: '',
        property_heading: '',
        property_sub_heading: '',
        property_logo: '',
        source_link: '#',
        source_heading: '',
        booking_date: '',
        channel_reference: '',
        source: '',
        left_days: '4 days left',
        checkin_date: '',
        checkout_date: '',
        arrival_time: '',
        adults: '',
        children: '',
        guest_comments: '',
        internal_notes: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        error_message: {
            email: '',
            phone: '',
            adults: ''
        },

        error_status: {
            email: false,
            phone: false,
            adults: false
        },

    },

    payments: {

        capabilities: [],
        is_payment_gateway_found: false,
        is_credit_card_available: false,
        payment_summary: {
            show_refund: false,
            charges: '',
            extras: '',
            extras_details: [],
            sub_total: '',
            paid: '',
            amount_due: '',
        },
        pending_payments: [],
        declined_payments: [],
        accepted_payments: [],
        other_payments: [],
    },

};


export default state;