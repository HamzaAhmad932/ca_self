let mutations = {

    FETCH_BOOKING_LIST(state, payload) {
        state.booking_list = payload;
        return state;
    },
    FETCH_BOOKING_DETAILS(state, payload) {
        state.booking_details = payload.data;
        return state;
    },
    BOOKING_TRANSACTION_DETAIL(state, payload) {
        state.transaction = payload.data;
    },
    BOOKING_PROPERTY_DETAIL(state, payload) {
        state.booking_property = payload.data.property;
        state.booking_property_history = payload.data.property_history;
        state.booking_user_account = payload.data.user_account;
    },
    BOOKING_PAYMENT_GATEWAY_DETAIL(state, payload) {
        state.payment_gateway = payload.data.payment_gateway;
        state.payment_gateway_detail = payload.data.payment_gateway_detail;
    },
    BOOKING_CREDIT_CARD_INFO_DETAIL(state, payload) {
        state.booking_cc_info = payload.data.booking_cc_info;
        state.booking_cc_info_detail = payload.data.booking_cc_info_detail;
        state.booking_cc_info_user_account = payload.data.booking_cc_info_user_account;
    }
};

export default mutations;
