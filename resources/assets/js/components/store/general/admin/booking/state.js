let state = {
    booking_list: {
        data: []
    },
    routes: {},
    booking_details: {
        check_in: {
            month: '',
            day: '',
            year: ''
        },
        check_out: {
            month: '',
            day: '',
            year: ''
        },
        deposit: '',
        deposit_status: '',
        amount: '',
        payment_status: '',
        booking_status: '',
        arrival_time: '',
        guests: '',
        last_seen_of_guest: '',
        payment_type: '',
        id: '',
        pms_booking_id: '',
        user_account_id: '',
        property_id: '',
        booking_time: '',
        guest_name: '',
        guest_phone: '',
        guest_email: '',

        user_account: {},
        cc_infos: {},
        credit_card_authorization: {},
        transaction_init: {},
        property_info: {},
        payment_gateway: {},
    },
    additional_charge_active_booking_id: 0,
    refund_amount_active_booking_id: 0,
    amount_valid_to_refund: 0,
    guest_upload_doc_id: 0,
    guest_credit_card_id: 0,
    booking_id_action_chat: {
        data: []
    },
    reduce_amount_data: {
        booking_id: '',
        tran_id: '',
        current_amount: 0
    },
    transaction: {},
    booking_property: {},
    booking_property_history: {},
    booking_user_account: {},
    payment_gateway: {},
    payment_gateway_detail: {},
    booking_cc_info: {},
    booking_cc_info_detail: {},
    booking_cc_info_user_account: {},
};

export default state;
