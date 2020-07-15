let mutations = {

    BOOKING_DETAIL_HEADER(state, payload) {
        return state.booking_detail.header = payload;
    },
    //
    // BOOKING_DETAILS(state, payload) {
    //     return state.booking_details = {...state.booking_details, ...payload};
    // },
    //
    // SHOW_BOOKING_DETAILS_ERRORS(state, payload) {
    //
    //     return state.booking_details = {...state.booking_details, ...payload};
    // },
    //
    // SHOW_UPLOAD_DOCUMENT_ERRORS(state, payload) {
    //
    //     return state.document_upload = {...state.document_upload, ...payload};
    // },

    GUEST_EXPERIENCE_TAB_DATA(state, payload) {

        return state.booking_detail.guest_experience = {...state.booking_detail.guest_experience, ...payload};
    },

    SHOW_GUEST_EXPERIENCE_ERRORS(state, payload) {

        return state.booking_detail.guest_experience = {...state.booking_detail.guest_experience, ...payload};
    },

    // PAYMENT_TAB_DATA(state, payload) {
    //     return state.payments = {...state.payments, ...payload};
    // },

    ACTIVITY_LOG_TAB_DATA(state, payload) {
        state.booking_detail.activity_log = payload.activity_log;
        state.booking_detail.payment_status = payload.payment_status;

        return state;
    },

    SENT_EMAILS_TAB_DATA(state, payload) {
        return state.booking_detail.sent_emails = payload;
    },

    // GUEST_DOCUMENTS_TAB_DATA(state, payload) {
    //     return state.documents = payload;
    // },

    UPSELL_TAB_DATA(state, payload){
        return state.booking_detail.upsells = payload;
    },

    // OPEN_UPSELL_TAB(state){
    //     return state.tab_section.current_tab =  {
    //         'component_name': 'Upsell',
    //         'icon': 'fas fa-cart-arrow-down'
    //     };
    // },
    //
    // SET_DOCUMENT_DESCRIPTION_DATA(state, payload){
    //     return state.document_description = payload;
    // }
};


export default mutations;