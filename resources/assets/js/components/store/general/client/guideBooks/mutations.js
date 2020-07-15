let mutations = {
    SET_FORM_DATA: function (state, data) {
        data = data[0];
        state.guideBook.formData = {
            ...state.guideBook.formData, ...{
                serve_id: data.id,
                status: data.status,
                icon: data.icon,
                type_id: data.guide_book_type_id,
                internal_name: data.internal_name,
                text_content: data.text_content,
            }
        };
        return state;
    },
    RESET_FORM_DATA: function (state) {
        state.guideBook.formData = {
            serve_id: 0,
            status: false,
            internal_name: '',
            text_content: '',
            icon: '',
            type_id:'',
            selected_properties: [],
        };
        return state;
    },
    SET_GUIDE_BOOK_TYPES: function (state, data) {
        state.guideBook.types = data;
        return state;
    },
    SET_LIST: function (state, data) {
        state.guideBook.paginationResponse = data;

        //update intercom data
        updateIntercomData('guidebook_listing_page_loaded');

        return state;
    },

    UPDATE_GUIDE_BOOK_STATUS: function (state, data) {
        state.guideBook.paginationResponse.data[data.index][data.updateWhat] = data.updateWith;

        //update intercom data
        updateIntercomData('guidebook_listing_page_loaded');

        return state;
    },

};
export default mutations;