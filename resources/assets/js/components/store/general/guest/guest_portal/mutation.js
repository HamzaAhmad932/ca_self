let mutations = {

    GUEST_PORTAL_DATA(state, payload) {
        $.each(payload, function (key, value) {
            if (state.guest_portal[key] !== undefined) {
                state.guest_portal[key] = value;
            }
        });
        return state;
    },
    TOGGLE_BASIC_INFO_ERRORS(state, payload) {
        return state.guest_portal.basic_info = {...state.basic_info, ...payload};
    },
    GUIDE_BOOK_TYPE(state, payload) {
        return state.guest_portal.guide_book_type = payload;
    },
    BASIC_INFO_UPDATE(state, payload) {
        state.guest_portal.email = payload.email;
        state.guest_portal.phone = payload.phone;
        state.guest_portal.arrival_time = payload.arrival_time;
        state.guest_portal.show_contact_info_form = false;

        return state;
    },

    TOGGLE_CARD_ERRORS(state, payload) {
        return state.guest_portal.card = {...state.card, ...payload};
    },

    GUEST_CARD_UPDATE(state) {

        return state.guest_portal.show_update_card_form = false;
    },

    UPDATE_CONTACT_FORM_SHOW(state, value) {

        state.guest_portal.show_contact_info_form = value;
        state.guest_portal.basic_info.email = state.guest_portal.email;
        state.guest_portal.basic_info.phone = state.guest_portal.phone;
        state.guest_portal.basic_info.arrival_time = state.guest_portal.arrival_time;

        return state;
    },

    UPDATE_CARD_SHOW(state, value) {
        return state.guest_portal.show_update_card_form = value;
    },

    IMAGES_UPDATED(state, payload) {
        //to remove ATTENTION text after upload
        if(payload.image_type == 'passport_uploaded')
            state.guest_portal.meta.need_guest_verification = false;
        else if(payload.image_type == 'credit_card_uploaded')
            state.guest_portal.meta.need_credit_card_scan = false;

        state.guest_portal.images = payload.request_response;
        return state;
    },

    SHOW_3DS_MODAL_BOX_AT_GUEST_PORTAL(state){
        state.guest_portal._3ds_modal = true;
    },

    HIDE_3DS_MODAL_BOX_AT_GUEST_PORTAL(state){
        state.guest_portal._3ds_modal = false;
    }
};


export default mutations;
