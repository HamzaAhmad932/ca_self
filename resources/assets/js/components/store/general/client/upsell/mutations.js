import {init_state} from './state';
let mutations = {
    GET_UPSELL_TYPES(state, data) {
        return state.upsell.upsell_types = data.data;
    },
    LOAD_UPSELL_FORM_DATA(state, data) {
        state.upsell.form_data = {...state.upsell.form_data, ...data};
        return state;
    },
    RESET_UPSELL_FORM_DATA(state) {
        return state.upsell.form_data = {...state.form_data, ...init_state()};
    },

    SET_UPSELL_LIST(state, data) {
        state.upsell.paginationResponse = data;

        //update intercom data
        updateIntercomData('upsell_listing_page_loaded');

        return state;
    },

    UPSELL_LIST_STATUS_UPDATE(state, data) {
        state.upsell.paginationResponse.data[data.index].status.value = data.status;

        //update intercom data
        updateIntercomData('upsell_listing_page_loaded');

        return state;
    },
    SET_UPSELL_CONFIG(state, data) {
        state.upsell.upsell_config = data;
        return state;
    },

    SHOW_ADD_UPSELL_ERRORS(state, {error_message, error_status}){
        state.upsell.form_data.error_message = {...state.upsell.form_data.error_message, ...error_message };
        state.upsell.form_data.error_status = { ...state.upsell.form_data.error_status, ...error_status };
        return state;
    }
};

export default mutations;
