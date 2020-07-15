let mutations = {
    SET_TAC_FORM_DATA(state, data) {
        data = data[0];
        state.tac.formData = {
            ...state.tac.formData, ...{
                serve_id: data.id,
                status: data.status,
                required: data.required,
                checkbox_text: data.checkbox_text,
                internal_name: data.internal_name,
                text_content: data.text_content,
            }
        };
        return state;
    },
    RESET_TAC_FORM_DATA(state) {
        state.tac.formData = {
            serve_id: 0,
            status: false,
            required: false,
            checkbox_text: "",
            internal_name: "",
            text_content: "",
            selected_properties: '',
        };
        return state;
    },
    SET_TAC_LIST(state, data) {
        state.tac.paginationResponse = data;

        //update intercom data
        updateIntercomData('terms_listing_page_loaded');

        return state;
    },

    TAC_STATUS_UPDATE(state, data) {
        state.tac.paginationResponse.data[data.index][data.updateWhat] = data.updateWith;

        //update intercom data
        updateIntercomData('terms_listing_page_loaded');

        return state;
    },

};
export default mutations;