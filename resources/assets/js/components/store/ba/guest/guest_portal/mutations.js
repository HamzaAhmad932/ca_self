let mutations = {

    GUEST_PORTAL_DATA(state, payload) {
        $.each(payload, function (key, value) {
            if (state[key] !== undefined) {
                state[key] = value;
            }
        });
        return state;
    },
    TOGGLE_BASIC_INFO_ERRORS(state, payload) {
        return state.basic_info = {...state.basic_info, ...payload};
    },
    GUIDE_BOOK_TYPE(state, payload) {
        return state.guide_book_type = payload;
    },
    BASIC_INFO_UPDATE(state, payload) {
        state.email = payload.email;
        state.phone = payload.phone;
        state.arrival_time = payload.arrival_time;
        state.show_contact_info_form = false;

        return state;
    },

    TOGGLE_CARD_ERRORS(state, payload) {
        return state.card = {...state.card, ...payload};
    },

    GUEST_CARD_UPDATE(state) {

        return state.show_update_card_form = false;
    },

    UPDATE_CONTACT_FORM_SHOW(state, value) {

        state.show_contact_info_form = value;
        state.basic_info.email = state.email;
        state.basic_info.phone = state.phone;
        state.basic_info.arrival_time = state.arrival_time;

        return state;
    },

    UPDATE_CARD_SHOW(state, value) {
        return state.show_update_card_form = value;
    },

    IMAGES_UPDATED(state, payload) {
        //to remove ATTENTION text after upload
        if(payload.image_type == 'passport_uploaded')
            state.meta.need_guest_verification = false;
        else if(payload.image_type == 'credit_card_uploaded')
            state.meta.need_credit_card_scan = false;

        state.images = payload.request_response;
        return state;
    },

    SHOW_3DS_MODAL_BOX_AT_GUEST_PORTAL(state){
        state._3ds_modal = true;
    },

    HIDE_3DS_MODAL_BOX_AT_GUEST_PORTAL(state){
        state._3ds_modal = false;
    }
};


export default mutations;
