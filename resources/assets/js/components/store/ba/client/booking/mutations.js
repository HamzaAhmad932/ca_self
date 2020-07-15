let mutations = {

    FETCH_BOOKING_LIST(state, payload) {
        return state.booking.booking_list = payload;
    },
    FETCH_BOOKING_DETAIL(state, {payload, index}) {
        return state.booking.booking_detail = {...state.booking.booking_detail, [index]: payload.data.booking_detail};
    },

    UPDATE_BOOKING_LIST(state, {payload, index}) {
        $.each(state.booking.booking_list.data, function (key, booking) {
            if (booking.id == payload.data[0].id)
                state.booking.booking_list.data[key] = payload.data[0];
        });
        return state.booking.booking_list;
    },
    // GUEST_CREDIT_CARD_ID(state, payload) {
    //     state.guest_credit_card_id = payload;
    //     return state;
    // },
    // SHOW_TOAST_MESSAGE(state, payload) {
    //     if (payload.status == true) {
    //         toastr.success(payload.msg);
    //     } else {
    //         toastr.error(payload.msg);
    //     }
    // },
    // ADDITIONAL_CHARGE_ACTIVE_ID(state, payload) {
    //
    //     state.additional_charge_active_booking_id = payload;
    //     return state;
    // },
    // BOOKIN_ID_ACTION_CHAT(state, payload) {
    //     state.booking_id_action_chat = payload;
    //     return state;
    // },
    //
    // BOOKING_NEW_BALANCE_AND_TOTAL(state, payload) {
    //     state.reduce_amount_data.new_amount = payload.data.new_amount;
    //     state.reduce_amount_data.new_balance = payload.data.new_balance;
    //     return state;
    // },
    //
    // REFUND_AMOUNT_ACTIVE_ID(state, payload) {
    //
    //     state.refund_amount_active_booking_id = payload;
    //     return state;
    // },
    // REFUND_AMOUNT_ACTIVE_TRANSACTION_ID(state, payload) {
    //     state.refund_amount_active_transaction_id = payload;
    //     return state;
    // },
    // REFUND_AMOUNT_MAX_VALID_VALUE(state, payload) {
    //     state.amount_valid_to_refund = payload;
    //     return state;
    // },
    // GUEST_UPLOAD_DOC_ID(state, payload) {
    //     state.guest_upload_doc_id = payload;
    //     return state;
    // },
    // REDUCE_AMOUNT_DATA(state, payload) {
    //     state.reduce_amount_data.booking_id = payload.booking_id;
    //     state.reduce_amount_data.tran_id = payload.tran_id;
    //     state.reduce_amount_data.current_amount = payload.current_amount;
    //     return state;
    // },
    //
    // SET_GUEST_DOCUMENT(state, payload){
    //     return state.images = payload;
    // },
    //
    CAN_SYNC_BOOKING(state, payload){
        return state.can_sync_booking = payload;
    },
    //
    // CAPTURE_AMOUNT_ACTIVE_BOOKING_ID(state, payload) {
    //     state.capture_amount_active_booking_id = payload;
    //     return state;
    // },
    // CAPTURE_AMOUNT_ACTIVE_AUTH_ID(state, payload) {
    //     state.capture_amount_active_cc_auth_id = payload;
    //     return state;
    // },
    // CAPTURE_AMOUNT_MAX_VALID_VALUE(state, payload) {
    //     state.amount_valid_to_capture = payload;
    //     return state;
    // },
    // SHOW_CAPTURE_AMOUNT_ERRORS(state, {error_message, error_status}) {
    //
    //     if (error_message !== {}) {
    //         state.capture_amount.error_message = {...state.capture_amount.error_message, ...error_message};
    //     }
    //     if (error_status !== {}) {
    //         state.capture_amount.error_status = {...state.capture_amount.error_status, ...error_status};
    //     }
    //     return state;
    // },
};

export default mutations;
